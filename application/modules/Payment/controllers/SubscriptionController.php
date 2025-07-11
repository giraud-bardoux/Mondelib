<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SubscriptionController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
 
include_once APPLICATION_PATH . "/application/libraries/Engine/Service/Stripe/init.php";

class Payment_SubscriptionController extends Core_Controller_Action_Standard
{
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

  /**
   * @var Payment_Model_Subscription
   */
  protected $_subscription;

  /**
   * @var Payment_Model_Package
   */
  protected $_package;

  public function init()
  {
    if(!empty($_GET['sesapi_platform'])) {
      $_SESSION['subscriptionStepsEnable'] = 1;
    }
    
    // If there are no enabled gateways or packages, disable
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Wallet is not enabled.")));die;
    }

    // Get user and session
    $this->_user = Engine_Api::_()->user()->getViewer();
    $this->_session = new Zend_Session_Namespace('Payment_Subscription');
    
    if(!empty($_GET['sesapi_platform']) && isset($_GET['user_subscription_id'])) {
      $this->_session->user_id = $_GET['user_subscription_id'];
    } 
    
    // Check viewer and user
    if( !$this->_user || !$this->_user->getIdentity() ) {
      if( !empty($this->_session->user_id) ) {
        $this->_user = Engine_Api::_()->getItem('user', $this->_session->user_id);
      }
      // If no user, redirect to home?
      if( !$this->_user || !$this->_user->getIdentity() ) {
        $this->_session->unsetAll();
        
        $this->view->url = $this->view->url(array(), 'default', true);
        echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Member.")));die;
        //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
    }
  }

  public function indexAction()
  {
    return $this->_forward('choose');
  }

  public function chooseAction()
  {
    // Check subscription status
    //if( $this->_checkSubscriptionStatus() ) {
    //  return;
    //}

    // Unset certain keys
    unset($this->_session->package_id);
    unset($this->_session->subscription_id);
    unset($this->_session->gateway_id);
    unset($this->_session->order_id);
    unset($this->_session->errorMessage);

    // Check for default plan
    $this->_checkDefaultPaymentPlan();

    // Make form
    $this->view->form = $form = new Payment_Form_Signup_Subscription(array(
      'isSignup' => false,
      'action' => $this->view->url(),
    ));
    
    // Process
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $this->view->user = $user = $this->_user;
    if($user) {
      $this->view->currentSubscription = $currentSubscription = $subscriptionsTable->fetchRow(array(
        'user_id = ?' => $user->getIdentity(),
        'active = ?' => true,
      ));
      
      // Get current package
      if( $currentSubscription ) {
        $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
        $this->view->currentPackage = $currentPackage = $packagesTable->fetchRow(array(
          'package_id = ?' => $currentSubscription->package_id,
        ));
      }
      
      // Get packages
      $this->view->packages = $packages = Engine_Api::_()->getDbtable('packages', 'payment')->getEnabledAfterSignupPackageCount();
      
    } else {
      // Get packages
      $this->view->packages = $packages = Engine_Api::_()->getDbtable('packages', 'payment')->getEnabledPackageCount();
    }
    if($packages == 0) {
      $this->view->url = $this->view->url(array(), 'default', true);
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method."), 'url' => $this->view->url));die;
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Get package
    if( !($packageId = $this->_getParam('package_id', $this->_session->package_id)) || !($package = Engine_Api::_()->getItem('payment_package', $packageId)) ) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan.")));die;
    }
    $this->view->package = $package;

    // If wallet is disabled
    if($package->price > 0  && !Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Wallet is not enabled.")));die;
    }
    
    if($package->price > 0 && $package->price > $user->wallet_amount) {
      $message = $this->view->translate("You don't have enough balance to subscribe this plan, please first recharge your ") . '<a target="_blank" href="'.$this->view->url(array("module" => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true).'">'.$this->view->translate(" wallet").'.</a>';
      echo json_encode(array('status' => false, 'message' => $message));die;
    }

    // Cancel any other existing subscriptions
    Engine_Api::_()->getDbTable('subscriptions', 'payment')->cancelAll($user, 'User cancelled the subscription.', $currentSubscription);

    // Insert the new temporary subscription
    $db = $subscriptionsTable->getAdapter();
    $db->beginTransaction();

    try {
      $subscription = $subscriptionsTable->createRow();
      $subscription->setFromArray(array(
        'package_id' => $package->package_id,
        'user_id' => $user->getIdentity(),
        'status' => 'initial',
        'active' => false, // Will set to active on payment success
        'creation_date' => new Zend_Db_Expr('NOW()'),
      ));
      $subscription->save();

      // If the package is free, let's set it active now and cancel the other
      if( $package->isFree() ) {
        $subscription->setActive(true);
        $subscription->onPaymentSuccess();
        if( $currentSubscription ) {
          $currentSubscription->cancel();
        }
      }

      $subscription_id = $subscription->subscription_id;

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $this->_session->subscription_id = $subscription_id;

    // Check if the user is good (this will happen if they choose a free plan)
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    if( $package->isFree() && $subscriptionsTable->check($this->_user) ) {
      $this->_finishPayment($package->isFree() ? 'free' : 'active');
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you for subscribing to the free plan!")));die;
    }

    // Otherwise redirect to the payment page
    $this->gateway();
    //return $this->_helper->redirector->gotoRoute(array('action' => 'gateway'));
  }

  public function gateway()
  {
    // Get subscription
    $subscriptionId = $this->_getParam('subscription_id', $this->_session->subscription_id);
    if( !$subscriptionId ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId))  ) {
      //return $this->_helper->redirector->gotoRoute(array('action' => 'choose'));
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan.")));die;
    }
    $this->view->subscription = $subscription;

    // Check subscription status
    if( $this->_checkSubscriptionStatus($subscription) ) {
      //return;
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Request.")));die;
    }

    // Get subscription
    if( !$this->_user ||
        !($subscriptionId = $this->_getParam('subscription_id', $this->_session->subscription_id)) ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId)) ||
        $subscription->user_id != $this->_user->getIdentity() ||
        !($package = Engine_Api::_()->getItem('payment_package', $subscription->package_id)) ) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan.")));die;
      //return $this->_helper->redirector->gotoRoute(array('action' => 'choose'));
    }
    $this->view->subscription = $subscription;
    $this->view->package = $package;

    // Unset certain keys
    unset($this->_session->gateway_id);
    unset($this->_session->order_id);

    $this->process();
  }

  public function process()
  {
    $this->_session->gateway_id = $this->_getParam('gateway_id', 3000);
    
    // Get gateway
    $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
    if (!$gatewayId) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method.")));die;
    }

    // Get subscription
    $subscriptionId = $this->_getParam('subscription_id', $this->_session->subscription_id);

    if( !$subscriptionId || !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId))  ) {
      $this->view->url = $this->view->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'choose'), 'default', true);
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan."), 'url' => $this->view->url));die;
    }
    $this->view->subscription = $subscription;

    // Get package
    $package = $subscription->getPackage();
    if( !$package || $package->isFree() ) {
      $this->view->url = $this->view->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'choose'), 'default', true);
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan."), 'url' => $this->view->url));die;
    }
    $this->view->package = $package;

    // Check subscription?
//     if( $this->_checkSubscriptionStatus($subscription) ) {
//       return;
//     }

    // Process
    // Create order
    $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    if( !empty($this->_session->order_id) ) {
      $previousOrder = $ordersTable->find($this->_session->order_id)->current();
      if( $previousOrder && $previousOrder->state == 'pending' ) {
        $previousOrder->state = 'incomplete';
        $previousOrder->save();
      }
    }
    $ordersTable->insert(array(
      'user_id' => $this->_user->getIdentity(),
      'gateway_id' => $gatewayId, //$gateway->gateway_id,
      'state' => 'pending',
      'creation_date' => new Zend_Db_Expr('NOW()'),
      'source_type' => 'payment_subscription',
      'source_id' => $subscription->subscription_id,
    ));
    $this->_session->order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
    $this->_session->current_currency = $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
    $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
    $this->_session->change_rate = $currencyData->change_rate;

    // Unset certain keys
    unset($this->_session->package_id);
    unset($this->_session->subscription_id);
    unset($this->_session->gateway_id);

    $this->_session->gateway_id = $gatewayId;

    // Prepare transaction
    $params = array();
    $params['language'] = $this->_user->language;
    $localeParts = explode('_', $this->_user->language);
    if( engine_count($localeParts) > 1 ) {
      $params['region'] = $localeParts[1];
    }
    $params['vendor_order_id'] = $order_id;

    // Post will be handled by the view script
    $this->returnsubscription();
  }

  public function returnsubscription()
  {
    // Get order
    if( !$this->_user ||
        !($orderId = $this->_getParam('order_id', $this->_session->order_id)) ||
        !($order = Engine_Api::_()->getItem('payment_order', $orderId)) ||
        $order->user_id != $this->_user->getIdentity() ||
        $order->source_type != 'payment_subscription' ||
        !($subscription = $order->getSource()) ||
        !($package = $subscription->getPackage())) {
      $this->view->url = $this->view->url(array(), 'default', true);
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Method."), 'url' => $this->view->url));die;
    }
    $this->_subscription = $subscription;

    // Process return
    unset($this->_session->errorMessage);
    try {
      $status = $subscription->onSubscriptionTransactionReturn($order, $this->_getAllParams());
      if(($status == 'active' || $status == 'free')) {
        $admins = Engine_Api::_()->user()->getSuperAdmins();
        foreach($admins as $admin){
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,'payment_subscription_transaction', array('gateway_type' => $gateway->title, 'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module'=>'payment'), 'admin_default', true)));
        }
      }
    } catch( Payment_Model_Exception $e ) {
      $status = 'failure';
      $this->_session->errorMessage = $e->getMessage();
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Payment Failed.")));die;
    }

    return $this->_finishPayment($status);
  }

  public function finish($state)
  {
    $this->view->status = $state;
    $this->view->error = $this->_session->errorMessage;
    
    // If user's member level changed then redirect to edit profile page.
    if (Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($this->_user)) {
      Engine_Api::_()->getDbtable('values', 'authorization')->resetProfileValues($this->_user);
      $this->view->url = $this->view->url(array('action' => 'profile', 'controller' => 'edit'), 'user_extended');
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully."), 'url' => $this->view->url));die;
    } else {
      //$this->view->url = $this->view->url(array(), 'default', true);
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully.")));die;
    }
  }

  protected function _checkSubscriptionStatus(Zend_Db_Table_Row_Abstract $subscription = null)
  {
    if( !$this->_user ) {
      return false;
    }

    if( null === $subscription ) {
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      $subscription = $subscriptionsTable->fetchRow(array(
        'user_id = ?' => $this->_user->getIdentity(),
        'active = ?' => true,
      ));
    }

    if( !$subscription ) {
      return false;
    }

    if( $subscription->status == 'active' ||
        $subscription->status == 'trial' ) {
      if( !$subscription->getPackage()->isFree() ) {
        $this->_finishPayment('active');
      } else {
        $this->_finishPayment('free');
      }
      return true;
    } else if( $subscription->status == 'pending' ) {
      $this->_finishPayment('pending');
      return true;
    }

    return false;
  }

  protected function _finishPayment($state = 'active')
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $user = $this->_user;

    // No user?
    if( !$this->_user ) {
      $this->view->url = $this->view->url(array(), 'default', true);
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Member."), 'url' => $this->view->url));die;
    }

    if( null === $this->_subscription ) {
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      $this->_subscription = $subscriptionsTable->fetchRow(array(
        'user_id = ?' => $this->_user->getIdentity(),
        'active = ?' => true,
      ));
    }

    // Log the user in, if they aren't already
    if( ($state == 'active' || $state == 'free' || ($this->_subscription && $this->_subscription->status == 'active')) &&
        $this->_user &&
        !$this->_user->isSelf($viewer) &&
        !$viewer->getIdentity() ) {
      Zend_Auth::getInstance()->getStorage()->write($this->_user->getIdentity());
      Engine_Api::_()->user()->setViewer();
      $viewer = $this->_user;
    }

    // Handle email verification or pending approval
    if( $viewer->getIdentity() && !$viewer->enabled ) {
      Engine_Api::_()->user()->setViewer(null);
      Engine_Api::_()->user()->getAuth()->getStorage()->clear();

      $confirmSession = new Zend_Session_Namespace('Signup_Confirm');
      $confirmSession->approved = $viewer->approved;
      $confirmSession->verified = $viewer->verified;
      $confirmSession->enabled  = $viewer->enabled;
      
      $this->view->url = $this->view->url(array('action' => 'confirm'), 'user_signup', true);
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully."), 'url' => $this->view->url));die;
    }

    // Clear session
    $errorMessage = $this->_session->errorMessage;
    $userIdentity = $this->_session->user_id;
    $this->_session->unsetAll();
    $this->_session->user_id = $userIdentity;
    $this->_session->errorMessage = $errorMessage;

    // Redirect
    if( $state == 'free' ) {
      //$this->view->url = $this->view->url(array(), 'default', true);
      //echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully."), 'url' => $this->view->url));die;
      return 'active';
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else {
      $this->finish($state);
      //return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state));
    }
  }

  protected function _checkDefaultPaymentPlan()
  {
    // No user?
    if( !$this->_user ) {
      $this->view->url = $this->view->url(array(), 'default', true);
      echo json_encode(array('status' => true, 'message' => $this->view->translate("Thank you! Your payment has completed successfully.s"), 'url' => $this->view->url));die;
      
      //return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Handle default payment plan
    try {
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
      if( $subscriptionsTable ) {
        $subscription = $subscriptionsTable->activateDefaultPlan($this->_user);
        if( $subscription ) {
          return $this->_finishPayment('free');
        }
      }
    } catch( Exception $e ) {
      // Silence
    }

    // Fall-through
  }
}
