<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SubscriptionController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_VerificationController extends Core_Controller_Action_Standard {

  /**
   * @var User_Model_User
   */
  protected $_user;

  /**
   * @var Zend_Session_Namespace
   */
  protected $_session;

  /**
   * @var Payment_Model_Order
   */
  protected $_order;

  /**
   * @var Payment_Model_Gateway
   */
  protected $_gateway;

  public function init() {
    
    // If there are no enabled gateways or packages, disable
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Wallet is not enabled.")));die;
    }
    
    // Get user and session
    $this->_user = $user = Engine_Api::_()->user()->getViewer();
    $this->_session = new Zend_Session_Namespace('Payment_Verification');
    $this->_session->gateway_id = $this->_getParam('gateway_id', 3000);
    $this->_session->user_id = $user_id = $this->_getParam('user_id', 0);
    
    // Check viewer and user
    if( !$user || !$user->getIdentity() ) {
      if( !empty($this->_session->user_id) ) {
        $this->_user = $user = Engine_Api::_()->getItem('user', $this->_session->user_id);
      }
      // If no user, redirect to home?
      if( !$user || !$user->getIdentity() ) {
        $this->_session->unsetAll();
        echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Member.")));die;
      }
    }
    $this->_session->user_id = $user->getIdentity();
  }

  public function indexAction() {
    return $this->_forward('gateway');
  }
  
  public function completeAction() {
  
    // Get gateway
    $user = $this->_user;
    $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
		$user_id = $this->_getParam('user_id', $this->_session->user_id);
		
    if (!$gatewayId) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method.")));die;
    }
    
    $verificationpackage = Engine_Api::_()->getDbTable('verificationpackages', 'payment')->getPackage(array('level_id' => $user->level_id));
    if($verificationpackage->price > $user->wallet_amount) { 
      $message = $this->view->translate("You don't have enough balance for verification subscription, please first recharge your ") . '<a target="_blank" href="'.$this->view->url(array("module" => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true).'">'.$this->view->translate(" wallet").'.</a>';
      echo json_encode(array('status' => false, 'message' => $message));die;
    }
		
    //Process
    $ordersTable = Engine_Api::_()->getDbTable('orders', 'payment');
    if (!empty($this->_session->order_id)) {
      $previousOrder = $ordersTable->find($this->_session->order_id)->current();
      if ($previousOrder && $previousOrder->state == 'pending') {
        $previousOrder->state = 'incomplete';
        $previousOrder->save();
      }
    }

    // Insert the new temporary subscription
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $db = $subscriptionsTable->getAdapter();
    $db->beginTransaction();

    try {
      $subscription = $subscriptionsTable->createRow();
      $subscription->setFromArray(array(
        'package_id' => $verificationpackage->getIdentity(),
        'user_id' => $user->getIdentity(),
        'status' => 'initial',
        'active' => false, // Will set to active on payment success
        'creation_date' => new Zend_Db_Expr('NOW()'),
        'gateway_id' => $gatewayId,
        'resource_id' => $verificationpackage->getIdentity(),
        'resource_type' => $verificationpackage->getType(),
      ));
      $subscription->save();
      $subscription_id = $subscription->subscription_id;

      $db->commit();
    } catch( Exception $e ) {
      //$db->rollBack();
      //throw $e;
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method.")));die;
    }
    $this->_session->subscription_id = $subscription_id;

    // Create order
    $ordersTable->insert(array(
			'user_id' => $user->getIdentity(),
			'gateway_id' => $gatewayId, //$gateway->gateway_id,
			'state' => 'pending',
			'creation_date' => new Zend_Db_Expr('NOW()'),
			'source_type' => $verificationpackage->getType(),
			'source_id' => $verificationpackage->getIdentity(),
    ));
    $this->_session->order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
    
    // Unset certain keys
    unset($this->_session->gateway_id);

    // Prepare transaction
    $params = array();
    $params['language'] = $user->language;
    $localeParts = explode('_', $user->language);
    if( engine_count($localeParts) > 1 ) {
      $params['region'] = $localeParts[1];
    }
    $params['vendor_order_id'] = $order_id;
    $params['price'] = $verificationpackage->price;
    $params['recurrence'] = $verificationpackage->recurrence;
    $params['subscription_id'] = $subscription_id;

    // Get order
    if( !$user ||
        !($orderId = $this->_getParam('order_id', $this->_session->order_id)) ||
        !($order = Engine_Api::_()->getItem('payment_order', $orderId)) ||
        $order->user_id != $user->getIdentity() ||
        $order->source_type != 'payment_verificationpackage' ||
        !($verificationpackage = $order->getSource()) ) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method.")));die;
    }

    // Process return
    unset($this->_session->errorMessage);
    try {
      $status = $verificationpackage->onSubscriptionTransactionReturn($order, $params);

      if(($status == 'active' || $status == 'free')) {
        $admins = Engine_Api::_()->user()->getSuperAdmins();
        $user = Engine_Api::_()->getItem('user', $order->user_id);

        $adminLink = 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'index', 'action' => 'index'), 'admin_default', true);

        foreach($admins as $admin){
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,'payment_manual_verification', array(
            'payment_method' => $gateway->title,
            'sender_name' => $user->getTitle(),
            'admin_link' => $adminLink,
          ));
        }
      }
    } catch( Payment_Model_Exception $e ) {
      $status = 'failure';
      $this->_session->errorMessage = $e->getMessage();
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Payment Failed.")));die;
    }
    return $this->_finishPayment($status);
  }

  protected function _finishPayment($state = 'active') {
  
    $user = $this->_user;

    // No user?
    if( !$user ) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Member.")));die;
    }

    // Clear session
    $errorMessage = $this->_session->errorMessage;
    $userIdentity = $this->_session->user_id;
    $this->_session->unsetAll();
    $this->_session->user_id = $userIdentity;
    $this->_session->errorMessage = $errorMessage;

    // Redirect
    if( $state == 'free' ) {
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully.")));die;
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else {
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully.")));die;
      //return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state, 'user_id' => $user->getIdentity()));
    }
  }
  
  public function finishAction()
  {
    $this->view->status = $status = $this->_getParam('state');
    $this->view->error = $this->_session->errorMessage;
    $this->view->user_id = $user_id = $this->_getParam('user_id', null);
    $this->view->url = $this->view->url(array(), 'default', true);
  }
  
  public function cancelAction() {
  
		$subscriptionId = $this->_getParam('subscription_id', null);
    $subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId);
    $order = Engine_Api::_()->getItem('payment_order', $subscription->order_id);
    $verificationsubscription = $order->getSource();

		if(!$subscriptionId || !$subscription)
			return $this->_forward('notfound', 'error', 'core');
			
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new Payment_Form_Payment_Cancel();
    $form->setTitle('Cancel This Subscription?');
    $form->setDescription('Are you sure you want to cancel your verification subscription? You\'ll need to reapply if canceled.');
    $form->submit->setLabel('Yes');

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    $gateway = Engine_Api::_()->getItem('payment_verificationgateway', $subscription->gateway_id);
    try {
      //Cancel for Manual Payment gateway
      $verificationsubscription->onCancel($subscription);
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Verification Subscription cancelled successfully.');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      throw $e;
    }
  }
  
  public function sendVerificationRequestAction() {
  
		$user_id = $this->_getParam('user_id', null);
    $viewer = Engine_Api::_()->getItem('user', $user_id);
    
		if(!$user_id || !$viewer)
			return $this->_forward('notfound', 'error', 'core');
			
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new User_Form_Verification_Send();

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    
    $values = $form->getValues();
    $table = Engine_Api::_()->getDbTable('verificationrequests', 'user');
    
    $db = $table->getAdapter();
    $db->beginTransaction();
    
    try {

      $row = $table->createRow();
      $row->user_id = $user_id;
      $row->message = $values['message'];
      $row->creation_date = date('Y-m-d H:i:s');
      $row->save();
      
      //Send notification and mail to all admins
      $allAdmins = Engine_Api::_()->getItemTable('user')->getAllAdmin();
      foreach ($allAdmins as $admin) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $viewer, $admin, 'user_verirequestto_superadmin');
      }
      
      $db->commit();
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your verification request has been successfully submitted.');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      throw $e;
    }
  }
  
  public function cancelVerificationRequestAction() {
  
		$user_id = $this->_getParam('user_id', null);
    $user = Engine_Api::_()->getItem('user', $user_id);
    
    $verificationrequest_id = $this->_getParam('verificationrequest_id', null);
    $verificationrequest = Engine_Api::_()->getItem('user_verificationrequest', $verificationrequest_id);
    
		if(!$user_id || !$user)
			return $this->_forward('notfound', 'error', 'core');
			
    if(!$verificationrequest_id || !$verificationrequest)
			return $this->_forward('notfound', 'error', 'core');
			
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    // Make form
    $this->view->form = $form = new User_Form_Verification_Cancel();

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    try {
      $verificationrequest->delete();
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Verification request cancelled successfully.');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      throw $e;
    }
  }
}
