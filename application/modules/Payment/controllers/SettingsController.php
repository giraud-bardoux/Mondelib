<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SettingsController.php 10123 2013-12-11 17:29:35Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_SettingsController extends Core_Controller_Action_User
{
  public function init()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;
    if( null === $id ) {
      $subject = Engine_Api::_()->user()->getViewer();
      Engine_Api::_()->core()->setSubject($subject);
    } else {
      $subject = Engine_Api::_()->getItem('user', $id);
      Engine_Api::_()->core()->setSubject($subject);
    }

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setAuthParams(
      $subject,
      null,
      'edit'
    );

    // Set up navigation
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));
  }
  
  public function indexAction() {
    
    $user = Engine_Api::_()->core()->getSubject('user');

    // Check if they are an admin or moderator (don't require subscriptions from them)
    $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    if( engine_in_array($level->type, array('admin', 'moderator')) ) {
      $this->view->isAdmin = true;
      return;
    }
    
    // Get packages
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $select = $packagesTable->select()
              ->where('enabled = ?', true)
              ->where('after_signup = ?', true)
              ->order('order ASC');
    $this->view->packages = $packages = $packagesTable->fetchAll($select);
    if(engine_count($packages) == 0) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    // Get current subscription and package
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $this->view->currentSubscription = $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $user->getIdentity(),
      'active = ?' => true,
    ));

    // Get current package
    if( $currentSubscription ) {
      $this->view->currentPackage = $currentPackage = $packagesTable->fetchRow(array(
        'package_id = ?' => $currentSubscription->package_id,
      ));
    } else {
      if(engine_count($packages) == 0) {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }

    // Get current gateway?
  }
  
  public function transactionAction()
  {
    $user = Engine_Api::_()->core()->getSubject('user');

    // Make form
    $this->view->formFilter = $formFilter = new Payment_Form_Subscription_TransactionFilter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array();
    }
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'transaction_id';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'DESC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];
    
    // Initialize select
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $transactionSelect = $transactionsTable->select()
                                        ->where('user_id =?', $user->getIdentity());

    // Add filter values
    if( !empty($filterValues['gateway_id']) ) {
      $transactionSelect->where('gateway_id = ?', $filterValues['gateway_id']);
    }
    if( !empty($filterValues['type']) ) {
      $transactionSelect->where('type = ?', $filterValues['type']);
    }
    if( !empty($filterValues['state']) ) {
      $transactionSelect->where('state = ?', $filterValues['state']);
    }
    
    if( !empty($filterValues['order_id']) ) {
      $transactionSelect->where('order_id = ?', $filterValues['order_id']);
    }
    
    if( !empty($filterValues['amount']) ) {
      $transactionSelect->where('amount = ?', $filterValues['amount']);
    }

    $date_from = !empty($filterValues['date']['date_from']) ? date("Y-m-d", strtotime($filterValues['date']['date_from'])) : '';
    $date_to = !empty($filterValues['date']['date_to']) ? date("Y-m-d", strtotime($filterValues['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$transactionSelect->where("DATE(timestamp) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $transactionSelect->where("DATE(timestamp) >=?", $date_to);
			if (!empty($date_from))
        $transactionSelect->where("DATE(timestamp) <=?", $date_from);	
		}
		
		//Do not show wallet entry if wallet is not enabled
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) {
      $transactionSelect->where('type <> ?', "wallet recharge");
		}
    
    if( !empty($filterValues['order']) ) {
      if( empty($filterValues['direction']) ) {
        $filterValues['direction'] = 'DESC';
      }
      $transactionSelect->order($filterValues['order'] . ' ' . $filterValues['direction']);
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($transactionSelect);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Preload info
    $gatewayIds = array();
    $userIds = array();
    $orderIds = array();
    foreach( $paginator as $transaction ) {
      if( !empty($transaction->gateway_id) ) {
        $gatewayIds[] = $transaction->gateway_id;
      }
      if( !empty($transaction->user_id) ) {
        $userIds[] = $transaction->user_id;
      }
      if( !empty($transaction->order_id) ) {
        $orderIds[] = $transaction->order_id;
      }
    }
    $gatewayIds = array_unique($gatewayIds);
    $userIds = array_unique($userIds);
    $orderIds = array_unique($orderIds);

    // Preload gateways
    $gateways = array();
    if( !empty($gatewayIds) ) {
      foreach( Engine_Api::_()->getDbtable('gateways', 'payment')->find($gatewayIds) as $gateway ) {
        $gateways[$gateway->gateway_id] = $gateway;
      }
    }
    $this->view->gateways = $gateways;

    // Preload users
    $users = array();
    if( !empty($userIds) ) {
      foreach( Engine_Api::_()->getItemTable('user')->find($userIds) as $user ) {
        $users[$user->user_id] = $user;
      }
    }
    $this->view->users = $users;

    // Preload orders
    $orders = array();
    if( !empty($orderIds) ) {
      foreach( Engine_Api::_()->getDbtable('orders', 'payment')->find($orderIds) as $order ) {
        $orders[$order->order_id] = $order;
      }
    }
    $this->view->orders = $orders;
  }
  
  public function detailAction() {
    // Missing transaction
    if( !($transaction_id = $this->_getParam('transaction_id')) ||
        !($transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id)) ) {
      return;
    }

    $this->view->transaction = $transaction;
    $this->view->gateway = Engine_Api::_()->getItem('payment_gateway', $transaction->gateway_id);
    $this->view->order = Engine_Api::_()->getItem('payment_order', $transaction->order_id);
    $this->view->user = Engine_Api::_()->getItem('user', $transaction->user_id);
  }
  
  public function receiptAction() {
    $transaction_id = $this->_getParam('transaction_id');
    $this->view->transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
  }

  public function confirmAction() {

    // Process
    $user = Engine_Api::_()->core()->getSubject('user');

    // Get packages
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $this->view->package = $package = $packagesTable->fetchRow(array(
      'enabled = ?' => 1,
      'package_id = ?' => (int) $this->_getParam('package_id'),
    ));

    // Check if it exists
    if( !$package ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Get current subscription and package
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $user->getIdentity(),
      'active = ?' => true,
    ));

    // Get current package
    $currentPackage = null;
    if( $currentSubscription ) {
      $currentPackage = $packagesTable->fetchRow(array(
        'package_id = ?' => $currentSubscription->package_id,
      ));
    }

    // Check if they are the same
    if( $package->package_id == $currentPackage->package_id ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }


    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Cancel any other existing subscriptions
    Engine_Api::_()->getDbtable('subscriptions', 'payment')
      ->cancelAll($user, 'User cancelled the subscription.', $currentSubscription);
    

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

    // Check if the subscription is ok
    if( $package->isFree() && $subscriptionsTable->check($user) ) {
      $user = Engine_Api::_()->getItem('user', $user->user_id);
      // If user's member level changed then redirect to edit profile page.
      if (Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($user)) {
        Engine_Api::_()->getDbtable('values', 'authorization')->resetProfileValues($user);
        $this->_helper->redirector->gotoRoute(array('action' => 'profile', 'controller' => 'edit'), 'user_extended');
      }

      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    // Prepare subscription session
    $session = new Zend_Session_Namespace('Payment_Subscription');
    $session->is_change = true;
    $session->user_id = $user->getIdentity();
    $session->subscription_id = $subscription_id;

    // Redirect to subscription handler
    return $this->_helper->redirector->gotoRoute(array('controller' => 'subscription',
      'action' => 'gateway'));
  }
  
  public function verificationAction() {
  
    $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');

    // Check if they are an admin or moderator (don't require subscriptions from them)
    $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    if( engine_in_array($level->type, array('admin', 'moderator')) ) {
      $this->view->isAdmin = true;
      return;
    }
    
    $this->view->package = $package = Engine_Api::_()->getDbTable('verificationpackages', 'payment')->getPackage(array('level_id' => $user->level_id));
    
    $this->view->verified = $verified = $package->verified;
    if(engine_in_array($verified, array(0,1))) {
      return $this->_forward('notfound', 'error', 'core');
    }
    
    $this->view->price_verified = $package->price;;
    $this->view->recurrence = $package->recurrence;
    $this->view->recurrence_type = $package->recurrence_type;
    
    $this->view->subscription = Engine_Api::_()->getDbTable('subscriptions', 'payment')->userCurrentSubscriptionPlan(array('user_id' => $user->getIdentity(), 'resource_type' => $package->getType(), 'resource_id' => $package->getIdentity()));
  }
  
  public function walletAction() {
  
    $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');

    // Check if they are an admin or moderator (don't require subscriptions from them)
    $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    // if( engine_in_array($level->type, array('admin', 'moderator')) ) {
    //   $this->view->isAdmin = true;
    //   return;
    // }

    if(!empty($_SESSION['Payment_Subscription'])) {
      unset($_SESSION['Payment_Subscription']);
    }

    $this->view->wallet = $wallet = 1;
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    // Have any gateways or packages been added yet?
    if(Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0) {
      return $this->_forward('notfound', 'error', 'core');
    }

		$gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gatewaySelect = $gatewayTable->select()->where('enabled = ?', 1);
    $gateways = $gatewayTable->fetchAll($gatewaySelect);
    $gatewayPlugins = array();
    foreach ($gateways as $gateway) {
      $gatewayPlugins[] = array(
				'gateway' => $gateway,
				'plugin' => $gateway->getGateway(),
      );
    }
    $this->view->gateways = $gatewayPlugins;
    
    $this->view->transaction = Engine_Api::_()->getDbTable('transactions', 'payment')->getTransaction(array('user_id' => $user->getIdentity(), 'type' => 'wallet recharge'));
  }
}
