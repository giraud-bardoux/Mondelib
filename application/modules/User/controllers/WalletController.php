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

include_once APPLICATION_PATH . "/application/libraries/Engine/Service/Stripe/init.php";

class User_WalletController extends Core_Controller_Action_Standard {

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
//     if( Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0 ) {
//       return $this->_helper->redirector->gotoRoute(array(), 'default', true);
//     }
    
    // Get user and session
    $this->_user = Engine_Api::_()->user()->getViewer();
    if(!empty($_SESSION["Payment_Subscription"]) && empty($this->_user)){
      $this->_user = Engine_Api::_()->getItem("user",$_SESSION["Payment_Subscription"]["user_id"]);
    }
    $this->_session = new Zend_Session_Namespace('Payment_Wallet');
    $this->_session->gateway_id = $this->_getParam('gateway_id', 0);
    $this->_session->user_id = $user_id = $this->_getParam('user_id', !empty($_SESSION["Payment_Subscription"]) ? $_SESSION["Payment_Subscription"]["user_id"] : 0);
    
    // Check viewer and user
    if( !$this->_user || !$this->_user->getIdentity() ) {
      if( !empty($this->_session->user_id) ) {
        $this->_user = Engine_Api::_()->getItem('user', $this->_session->user_id);
      }
      // If no user, redirect to home?
      if( !$this->_user || !$this->_user->getIdentity() ) {
        $this->_session->unsetAll();
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      }
    }
    
    $this->_session->user_id = $this->_user->getIdentity();
  }

  public function indexAction() {
    return $this->_forward('gateway');
  }

  //This is for first time signup when plan is enabled.
  public function gatewayAction()
  {
    $this->view->user_id = $this->_user->getIdentity();

    $currentSubscriptionFirstPlan = Engine_Api::_()->getDbTable('subscriptions', 'payment')->currentSubscriptionFirstPlan($this->_user); 
    if($currentSubscriptionFirstPlan) {
      $this->_session->subscription_id = $currentSubscriptionFirstPlan->subscription_id;

      $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
      $currentFirstPackage = $packagesTable->fetchRow(array('package_id = ?' => $currentSubscriptionFirstPlan->package_id));
    }

    // Get subscription
    $subscriptionId = $this->_getParam('subscription_id', $this->_session->subscription_id);
    if( !$subscriptionId ||
        !($subscription = Engine_Api::_()->getItem('payment_subscription', $subscriptionId))  ) {
      //return $this->_helper->redirector->gotoRoute(array('action' => 'choose'));
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please choose plan.")));die;
    }
    $this->view->subscription = $subscription;

    // Check subscription status
    // if( $this->_checkSubscriptionStatus($subscription) ) {
    //   //return;
    //   echo json_encode(array('status' => false, 'message' => $this->view->translate("Invalid Request.")));die;
    // }

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
    // unset($this->_session->gateway_id);
    // unset($this->_session->order_id);

    // Gateways
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gatewaySelect = $gatewayTable->select()
      ->where('enabled = ?', 1)
      ;
    $gateways = $gatewayTable->fetchAll($gatewaySelect);

    $gatewayPlugins = array();
    foreach( $gateways as $gateway ) {
      // Check billing cycle support
      if( !$package->isOneTime() ) {
        $sbc = $gateway->getGateway()->getSupportedBillingCycles();
        if( !engine_in_array($package->recurrence_type, array_map('strtolower', $sbc)) ) {
          continue;
        }
      }
      $gatewayPlugins[] = array(
        'gateway' => $gateway,
        'plugin' => $gateway->getGateway(),
      );
    }
    $this->view->gateways = $gatewayPlugins;

    //return $this->_forward('process');
  }

  public function processAction() {
    
    $currentSubscriptionFirstPlan = Engine_Api::_()->getDbTable('subscriptions', 'payment')->currentSubscriptionFirstPlan($this->_user); 
    if($currentSubscriptionFirstPlan) {
      $this->_session->subscription_id = $currentSubscriptionFirstPlan->subscription_id;

      $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
      $currentFirstPackage = $packagesTable->fetchRow(array('package_id = ?' => $currentSubscriptionFirstPlan->package_id));

      $price = $currentFirstPackage->price;

      $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
      $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
      $currencyChangeRate = 1;
      if ($currentCurrency != $defaultCurrency) {
        $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
        $currencyChangeRate = $currencyData->change_rate;
      }
      $price = @round(($price * $currencyChangeRate), 2);
    }

    //When click on the back button in browser
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      $url = $this->view->baseUrl();
      return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
    }
    
    // Get gateway
    $gatewayId = $this->_getParam('gateway_id', $this->_session->gateway_id);
		$user_id = $this->_getParam('user_id', $this->_session->user_id);

    if (!$gatewayId || !($gateway = Engine_Api::_()->getDbtable('walletgateways', 'payment')->find($gatewayId)->current()) || !($gateway->enabled)) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'gateway'));
    }
    $this->view->gateway = $gateway;

    //Process
    // Create order
    $ordersTable = Engine_Api::_()->getDbtable('orders', 'payment');
    if (!empty($this->_session->order_id)) {
      $previousOrder = $ordersTable->find($this->_session->order_id)->current();
      if ($previousOrder && $previousOrder->state == 'pending') {
        $previousOrder->state = 'incomplete';
        $previousOrder->save();
      }
    }

    $recurrence = 0; //Engine_Api::_()->authorization()->getPermission($this->_user, 'user', 'recurrence');
    if(!empty($_POST['price_wallet'])) {
      $price = $_POST['price_wallet'];
    }

    //Order table for wallet
    $walletsTable = Engine_Api::_()->getDbTable('wallets', 'payment');
    $db = $walletsTable->getAdapter();
    $db->beginTransaction();
    try {
      $wallets = $walletsTable->createRow();
      $wallets->user_id = $this->_user->getIdentity();
      $wallets->params = json_encode(array('recurrence' => json_decode($recurrence), 'price' => $price, 'subscription_id' => $this->_session->subscription_id));
      $wallets->save();
      // Commit
      $db->commit();
      $walletsId = $wallets->getIdentity();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $ordersTable->insert(array(
			'user_id' => $this->_user->getIdentity(),
			'gateway_id' => $gateway->gateway_id,
			'state' => 'pending',
			'creation_date' => new Zend_Db_Expr('NOW()'),
			'source_type' => 'payment_wallet',
			'source_id' => $walletsId,
    ));
    $this->_session->order_id = $order_id = $ordersTable->getAdapter()->lastInsertId();
    
    // Unset certain keys
    unset($this->_session->gateway_id);
    
    // Get gateway plugin
    $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
    $plugin = $gateway->getPlugin();

    // Prepare host info
    $schema = _ENGINE_SSL ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Prepare transaction
    $params = array();
    $params['language'] = $this->_user->language;
    $localeParts = explode('_', $this->_user->language);
    if( engine_count($localeParts) > 1 ) {
      $params['region'] = $localeParts[1];
    }
    $params['vendor_order_id'] = $order_id;
    $this->view->returnUrl = $params['return_url'] = $schema . $host
      . $this->view->url(array('action' => 'return'))
      . '?order_id=' . $order_id
      //. '?gateway_id=' . $this->_gateway->gateway_id
      //. '&subscription_id=' . $this->_subscription->subscription_id
      . '&state=' . 'return';
    $params['cancel_url'] = $schema . $host
      . $this->view->url(array('action' => 'return'))
      . '?order_id=' . $order_id
      //. '?gateway_id=' . $this->_gateway->gateway_id
      //. '&subscription_id=' . $this->_subscription->subscription_id
      . '&state=' . 'cancel';
    $params['ipn_url'] = $schema . $host
      . $this->view->url(array('action' => 'index', 'controller' => 'ipn'))
      . '?order_id=' . $order_id;
      //. '?gateway_id=' . $this->_gateway->gateway_id
      //. '&subscription_id=' . $this->_subscription->subscription_id;
    
    $this->view->gateway_id = $gateway_id = $gateway->getIdentity();

    $params['price'] = $price;
    $params['recurrence'] = $recurrence;

    if($gateway->plugin == "Payment_Plugin_Gateway_Stripe") {
    
      $params['order_id'] = $order_id;
      $params['amount'] = $price; //$package->price;
      $params['type'] = 'user';
      $params['currency'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
      
      $this->view->publishKey = $publishKey = $gateway->config['publish'];

      $secretKey = $gateway->config['secret'];
      \Stripe\Stripe::setApiKey($secretKey);

      $this->view->session = $plugin->createWalletTransaction($this->_user, $params);
    }else if($gateway->plugin == "Coinpayments_Plugin_Gateway_Wallet_Coinpayment") {
      $params['order_id'] = $order_id;
      $params['amount'] = $price;
      // Process transaction
      $transaction = $plugin->createWalletTransaction($this->_user, $params);
      $this->view->transactionUrl = $transaction["result"]["checkout_url"];
      $this->view->transactionData = false;
    }else if($gateway->plugin == "Authorizepmnt_Plugin_Gateway_Wallet_Authorize") {
      $params['order_id'] = $order_id;
      $params['amount'] = $price;
      // Process transaction
      $params["email"] = $this->_user->email;
      $params["amountRecharge"] = Engine_Api::_()->payment()->getCurrencyPrice($params['amount'],Engine_Api::_()->payment()->getCurrentCurrency(),1);
      $this->view->params = $params;

      

      $this->renderScript('/application/modules/Authorizepmnt/views/scripts/wallet/index.tpl');
    } else {

      // Process transaction
      $transaction = $plugin->createWalletTransaction($this->_user, $params);

      // Pull transaction params
      $this->view->transactionUrl = $transactionUrl = $gatewayPlugin->getGatewayUrl();
      $this->view->transactionMethod = $transactionMethod = $gatewayPlugin->getGatewayMethod();
      $this->view->transactionData = $transactionData = $transaction->getData();

      // Handle redirection
      if( $transactionMethod == 'GET' ) {
        $transactionUrl .= '?' . http_build_query($transactionData);
        return $this->_helper->redirector->gotoUrl($transactionUrl, array('prependBase' => false));
      }
    }

    // Post will be handled by the view script
  }
  
  public function returnAction() {
  
    // Get order
    if( !$this->_user ||
        !($orderId = $this->_getParam('order_id', $this->_session->order_id)) ||
        !($order = Engine_Api::_()->getItem('payment_order', $orderId)) ||
        $order->user_id != $this->_user->getIdentity() ||
        $order->source_type != 'payment_wallet' ||
        !($wallets = $order->getSource()) ||
        !($gateway = Engine_Api::_()->getItem('payment_walletgateway', $order->gateway_id)) ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    //$this->_subscription = $subscription;
    // Get gateway plugin
    $this->view->gatewayPlugin = $gatewayPlugin = $gateway->getGateway();
    $plugin = $gateway->getPlugin();

    // Process return
    unset($this->_session->errorMessage);
    try {
      $status = $plugin->onWalletTransactionReturn($order, $this->_getAllParams());

      if(($status == 'active' || $status == 'free')) {
//         $admins = Engine_Api::_()->user()->getSuperAdmins();
//         $user = Engine_Api::_()->getItem('user', $order->user_id);
// 
//         $adminLink = 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'index', 'action' => 'index'), 'admin_default', true);
//         foreach($admins as $admin) {
//           Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,'payment_manual_wallet', array(
//             'payment_method' => $gateway->title,
//             'sender_name' => $user->getTitle(),
//             'admin_link' => $adminLink,
//           ));
//         }

        //Subscriptin work for first time signup using paid plan
        $user = $this->_user;
        $signup = false;
        if(isset($_SESSION['Payment_Subscription']) && $user && Engine_Api::_()->getDbTable('subscriptions', 'payment')->currentSubscriptionFirstPlan($user)) {
          Engine_Api::_()->payment()->firsttimeSignupSubscription($user);
          $signup = true;
        }
        //Subscriptin work for first time signup using paid plan
      }
    } catch( Payment_Model_Exception $e ) {
      $status = 'failure';
      if($gateway->plugin == "Authorizepmnt_Plugin_Gateway_Wallet_Authorize") {
        echo json_encode(array("status" => 1,"message"=> $e->getMessage()));die;
      }
      $this->_session->errorMessage = $e->getMessage();
    }
   
    if($gateway->plugin == "Authorizepmnt_Plugin_Gateway_Wallet_Authorize") {
      echo json_encode(array("status" => 1,"url"=>$this->view->url(array('action' => 'finish', 'state' => $status, 'user_id' => $user->getIdentity(), 'signup' => $signup))));die;
    }

    return $this->_finishPayment($status, $signup);
  }
  
  protected function _finishPayment($state = 'active', $signup = false)
  {
    $viewer = Engine_Api::_()->user()->getViewer() ?? $this->_user;
    $user = $this->_user;

    // No user?
    if( !$this->_user ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Clear session
    $this->view->signup = $signup;
    $errorMessage = $this->_session->errorMessage;
    $userIdentity = $this->_session->user_id;
    $this->_session->unsetAll();
    $this->_session->user_id = $userIdentity;
    $this->_session->errorMessage = $errorMessage;

    // Redirect
    if( $state == 'free' ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else {
      return $this->_helper->redirector->gotoRoute(array('action' => 'finish', 'state' => $state, 'user_id' => $user->getIdentity(), 'signup' => $signup));
    }
  }
  
  public function finishAction()
  {
    
    $this->view->signup = $signup = $this->_getParam('signup');
    $this->view->status = $status = $this->_getParam('state');
    $this->view->error = $this->_session->errorMessage;
    $this->view->user_id = $user_id = $this->_getParam('user_id', null);
    $this->view->url = $this->view->url(array(), 'default', true);
  }
}
