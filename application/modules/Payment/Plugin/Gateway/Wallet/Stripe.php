<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Stripe.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

include_once APPLICATION_PATH . "/application/libraries/Engine/Service/Stripe/init.php";

class Payment_Plugin_Gateway_Wallet_Stripe extends Engine_Payment_Plugin_Abstract {

  protected $_gatewayInfo;
  protected $_gateway;
  
  // General

  /**
   * Constructor
   */
  public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo)
  {
      $this->_gatewayInfo = $gatewayInfo;
      if($gatewayInfo->config)
      \Stripe\Stripe::setApiKey($this->_gatewayInfo->config['secret']);
  }

  /**
   * Get the service API
   *
   * @return Engine_Service_PayPal
   */
  public function getService()
  {
    return $this->getGateway()->getService();
  }

  /**
   * Get the gateway object
   *
   * @return Engine_Payment_Gateway
   */
  public function getGateway()
  {
    if( null === $this->_gateway ) {
      $class = 'Engine_Payment_Gateway_Stripe';
      Engine_Loader::loadClass($class);
      $gateway = new $class(array(
        'config' => (array) $this->_gatewayInfo->config,
        'testMode' => $this->_gatewayInfo->test_mode,
        'currency' => Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD'),
      ));
      if( !($gateway instanceof Engine_Payment_Gateway) ) {
        throw new Engine_Exception('Plugin class not instance of Engine_Payment_Gateway');
      }
      $this->_gateway = $gateway;
    }

    return $this->_gateway;
  }

  // Actions
  /**
   * Create a transaction object from specified parameters
   *
   * @return Engine_Payment_Transaction
   */
  public function createTransaction(array $params)
  {
    $transaction = new Engine_Payment_Transaction($params);
    $transaction->process($this->getGateway());
    return $transaction;
  }

  /**
   * Create an ipn object from specified parameters
   *
   * @return Engine_Payment_Ipn
   */
  public function createIpn(array $params)
  {
    $ipn = new Engine_Payment_Ipn($params);
    $ipn->process($this->getGateway());
    return $ipn;
  }

  // SEv4 Specific
  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Zend_Db_Table_Row_Abstract $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
  public function createWalletTransaction(User_Model_User $user, array $params = array()) {
  
    $recurrence = $params['recurrence'];
    $price = $params['price'];

    // Process description
    $desc = Engine_Api::_()->payment()->getPackageDescription(json_decode($recurrence), $price, 'wallet');
    if( strlen($desc) > 127 ) {
      $desc = substr($desc, 0, 124) . '...';
    } else if( !$desc || strlen($desc) <= 0 ) {
      $desc = 'N/A';
    }
    if( function_exists('iconv') && strlen($desc) != iconv_strlen($desc) ) {
      // PayPal requires that DESC be single-byte characters
      $desc = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $desc);
    }
    
    //Multiple currency
    $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
    $currencyChangeRate = 1;
    if ($currentCurrency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
      $currencyChangeRate = $currencyData->change_rate;
    }
//     $price = @round(($package->price * $currencyChangeRate), 2);
    $price = @round(($price), 2);

    \Stripe\Stripe::setApiKey($this->_gatewayInfo->config['secret']);

    // This is a one-time fee
    if( Engine_Api::_()->payment()->isOneTime(json_decode($recurrence)) ) {
      return \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
					'price_data' => [
						'currency' => $currentCurrency,
						'unit_amount' => intval($price * 100),
						'product_data' => [
							'name' => $user->getTitle(false). " ", //$package->title. " ",
							'description' => $desc. " ",
						],
					],
          'quantity' => 1,
        ]],
				'mode' => 'payment',
        'metadata' => ['gateway_id' => $this->_gatewayInfo->gateway_id, 'order_id' => $params['vendor_order_id'],'change_rate' => $currencyChangeRate],
        'success_url' => $params['return_url'].'&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $params['cancel_url'].'&session_id={CHECKOUT_SESSION_ID}',
      ]);
    }
//     // This is a recurring subscription
//     else  {
//       try {
//         $stripePlan = \Stripe\Plan::retrieve("package_user_".$package->getIdentity().'_'.$currentCurrency);
//       } catch(Exception $e){
//         $stripePlan = 0;
//       }
//       try {
//         if(!$stripePlan) {
//           $stripePlan = \Stripe\Plan::create(array(
//             "id" => "package_user_".$package->getIdentity().'_'.$currentCurrency,
//             "amount_decimal" => intval($price * 100),
//             "interval" => $package->recurrence_type,
//             "interval_count" => $package->recurrence,
//             "currency" => $currentCurrency,
//             "product" => [
//               "name" => $package->title,
//               "type" => "service"
//             ],
//             'metadata'=>['gateway_id' => $this->_gatewayInfo->gateway_id, 'package_id' => $package->getIdentity()]
//           ));
//         }
//         
//         return \Stripe\Checkout\Session::create([
//           'payment_method_types' => ['card'],
//           'subscription_data' => [
//             'items' => [[
//               'plan' => $stripePlan->id,
//             ]],
//             'metadata'=> ['order_id' => $params['vendor_order_id'], 'type' => $params['type'], 'gateway' => $this->_gatewayInfo->gateway_id, 'change_rate' => $currencyChangeRate],
//             //'trial_settings' => ['end_behavior' => ['missing_payment_method' => 'cancel']],
//             //'trial_period_days' => !empty($package->trial_duration) ? $package->trial_duration : 0,
//           ],
//           'success_url' => $params['return_url'].'&session_id={CHECKOUT_SESSION_ID}',
//           'cancel_url' => $params['cancel_url'].'&session_id={CHECKOUT_SESSION_ID}',
//         ]);
//       } catch(Exception $e){
//         throw $e;
//       }
//     }
  }

  // SEv4 Specific
  
  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Zend_Db_Table_Row_Abstract $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
  public function createSubscriptionTransaction(User_Model_User $user, Zend_Db_Table_Row_Abstract $subscription, Payment_Model_Package $package, array $params = array()) {

  }
  
  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onSubscriptionTransactionReturn(
  Payment_Model_Order $order, array $params = array()) {

  }
  
  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onWalletTransactionReturn(Payment_Model_Order $order, array $params = array()) {

    // Check that gateways match
    if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
      throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    
    // Get related info
    $user = $order->getUser();
    $subscription = $order->getSource();
    //$package = $subscription->getPackage();
    
    $subscriptionParams = json_decode($subscription->params);
    $recurrence = $subscriptionParams->recurrence;
    
    //Change rate according to default currency and selected currency by member
    
    $currencyChangeRate = 1;
    $current_currency = Engine_Api::_()->payment()->getCurrentCurrency();
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
    if ($current_currency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($current_currency);
      $currencyChangeRate = $currencyData->change_rate;
    }
    $price = @round((($subscriptionParams->price) / $currencyChangeRate), 2);
    
    // description
    $desc = Engine_Api::_()->payment()->getPackageDescription($recurrence, $subscriptionParams->price, 'wallet');
    if( strlen($desc) > 127 ) {
      $desc = substr($desc, 0, 124) . '...';
    } else if( !$desc || strlen($desc) <= 0 ) {
      $desc = 'N/A';
    }
    if( function_exists('iconv') && strlen($desc) != iconv_strlen($desc) ) {
      // PayPal requires that DESC be single-byte characters
      $desc = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $desc);
    }
    
    // expiration_date
    $expiration_date = date('Y-m-d H:i:s', Engine_Api::_()->payment()->getExpirationDate($recurrence));
		if($expiration_date == '1970-01-01 00:00:00') {
			$expiration_date = '2099-12-30 00:00:00';
		}

    $session = \Stripe\Checkout\Session::retrieve($params['session_id']);
    
    //Check subscription state
    $paymentStatus = 'okay';
    $orderStatus = 'complete';

//     // Check subscription state
//     if( $subscription->status == 'trial') {
//       return 'active';
//     } else if( $subscription->status == 'pending' ) {
//       return 'pending';
//     }
    // Check for cancel state - the user cancelled the transaction
    if( $params['state'] == 'cancel' ) {
      // Cancel order and subscription?
      $order->onCancel();
      $subscription->onPaymentFailure();
      // Error
      throw new Payment_Model_Exception('Your payment has been cancelled and ' .
          'not been charged. If this is not correct, please try again later.');
    }

    // One-time
    if( Engine_Api::_()->payment()->isOneTime($recurrence) ) {
    
      $transaction = \Stripe\PaymentIntent::retrieve($session['payment_intent']);
      
      // Update order with profile info and complete status?
      $order->state = $orderStatus;
      $order->gateway_transaction_id = $transaction->id;
      $order->save();

      // Insert transaction
      $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
      $transactionsTable->insert(array(
        'user_id' => $order->user_id,
        'gateway_id' => $this->_gatewayInfo->gateway_id,
        'timestamp' => new Zend_Db_Expr('NOW()'),
        'order_id' => $order->order_id,
        'type' => 'wallet recharge',
        'state' => $paymentStatus,
        'gateway_transaction_id' => $transaction->id,
        'amount' => $price, // @todo use this or gross (-fee)?
        'currency' => Engine_Api::_()->payment()->defaultCurrency(), //strtoupper($transaction->currency),
        'change_rate' => $currencyChangeRate, //currency change rate according to default currency
        'current_currency' => strtoupper($transaction->currency), //currency which is user paid
      ));
      $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
      
      // Get benefit setting
      $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
          ->getBenefitStatus($user);
          
      // Check payment status
      if( $paymentStatus == 'okay' ||
          ($paymentStatus == 'pending' && $giveBenefit) ) {
     
        // Update subscription info
        //$subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        //$subscription->gateway_profile_id = $rdata['PAYMENTINFO'][0]['TRANSACTIONID'];

        $subscription->transaction_id = $transaction_id;
        $subscription->save();

        // Payment success
        $subscription->onPaymentSuccess();

        // send notification
        if( $subscription->didStatusChange() ) {

          //Notification Work
          $translate = Zend_Registry::get('Zend_Translate');
          $walletlink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'settings', 'action' => 'wallet'), 'default', true);
          $walletlink = '<a href="'.$walletlink.'" >'.$translate->translate("wallet").'</a>';

          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $user, $user, 'payment_wallet_active', array('payment_method' => 'Stripe','walletlink' => $walletlink));

          //Email work
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_active', array(
            'wallet_terms' => $desc,
            'object_link' => 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => 'payment', 'controller' => "settings", "action" => "wallet"), 'default', true),
          ));
        }
        return 'active';
      }
      else if( $paymentStatus == 'pending' ) {

        // Update subscription info
        $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
        $subscription->gateway_profile_id = $transaction->id;
        
        // Payment pending
        $subscription->onPaymentPending();
        
        // send notification
        if( $subscription->didStatusChange() ) {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_pending', array(
            'wallet_terms' => $desc,
            'object_link' => 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => 'payment', 'controller' => "settings", "action" => "wallet"), 'default', true),
          ));
        }

        return 'pending';
      }
      else if( $paymentStatus == 'failed' ) {
        // Cancel order and subscription?
        $order->onFailure();
        $subscription->onPaymentFailure();
        // Payment failed
        throw new Payment_Model_Exception('Your payment could not be ' .
            'completed. Please ensure there are sufficient available funds ' .
            'in your account.');
      }
      else {
        // This is a sanity error and cannot produce information a user could use
        // to correct the problem.
        throw new Payment_Model_Exception('There was an error processing your ' .
            'transaction. Please try again later.');
      }
    }
    
//     // Recurring
//     else {
//     
//       $transaction = \Stripe\Subscription::retrieve($session['subscription']);
//       
//       // Create recurring payments profile
//       $desc = $package->getPackageDescription();
//       if( strlen($desc) > 127 ) {
//         $desc = substr($desc, 0, 124) . '...';
//       } else if( !$desc || strlen($desc) <= 0 ) {
//         $desc = 'N/A';
//       }
//       if( function_exists('iconv') && strlen($desc) != iconv_strlen($desc) ) {
//         // PayPal requires that DESC be single-byte characters
//         $desc = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $desc);
//       }
//       
//       $order->state = 'complete';
//       $order->gateway_order_id = $transaction->id;
//       $order->gateway_transaction_id = $transaction->id;
//       $order->save();
// 
//       // Get benefit setting
//       $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
//           ->getBenefitStatus($user);
// 
//       // Check profile status
//       if($paymentStatus == 'okay' ||
//           ($paymentStatus == 'pending' && $giveBenefit)) {
//           
//         // Enable now
//         $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
//         $subscription->gateway_profile_id = $transaction->id;
//         $subscription->onPaymentSuccess();
//         
//         if(!empty($package->trial_duration)) {
//           $subscription->status = 'trial';
// 
//           // expiration_date
//           $expiration_date = date('Y-m-d H:i:s', strtotime("+".$package->trial_duration." days"));
//           $subscription->expiration_date = $expiration_date;
//           $subscription->save();
//         }
//         
//         // send notification
//         if( $subscription->didStatusChange()) {
//           Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_active', array(
//             'subscription_title' => $package->title,
//             'subscription_description' => $package->description,
//             'subscription_terms' => $package->getPackageDescription(),
//             'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
//                 Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
//           ));
//         }
// 
//         return 'active';
// 
//       } else if($paymentStatus == 'pending') {
//       
//         // Enable later
//         $subscription->gateway_id = $this->_gatewayInfo->gateway_id;
//         $subscription->gateway_profile_id = $transaction->id;
//         $subscription->onPaymentPending();
//         
//         // send notification
//         //if( $subscription->didStatusChange() ) {
//           Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_pending', array(
//             'subscription_title' => $package->title,
//             'subscription_description' => $package->description,
//             'subscription_terms' => $package->getPackageDescription(),
//             'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
//                 Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
//           ));
//         //}
//         return 'pending';
// 
//       } else {
//         // Cancel order and subscription?
//         $order->onFailure();
//         $subscription->onPaymentFailure();
//         // This is a sanity error and cannot produce information a user could use
//         // to correct the problem.
//         throw new Payment_Model_Exception('There was an error processing your ' .
//             'transaction. Please try again later.');
//       }
//     }
  }
  
  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onWalletTransactionIpn(Payment_Model_Order $order,Engine_Payment_Ipn $ipn){ }
  
  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onSubscriptionTransactionIpn(
  Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {

  }
  

  /**
   * Cancel a subscription (i.e. disable the recurring payment profile)
   *
   * @params $transactionId
   * @return Engine_Payment_Plugin_Abstract
   */
  public function cancelSubscription($transactionId, $note = null)
  {
    $profileId = null;
    if( $transactionId instanceof Payment_Model_Wallet ) {
      $package = $transactionId->getPackage();
      if( $package->isOneTime() ) {
        return $this;
      }
      $profileId = $transactionId->gateway_profile_id;
    }
    else if(is_string($transactionId) ) {
      $profileId = $transactionId;
    } else {
      // Should we throw?
      return $this;
    }
    
    $secretKey = $this->_gatewayInfo->config['secret'];
    \Stripe\Stripe::setApiKey($secretKey);
    $sub = \Stripe\Subscription::retrieve($profileId);
    $cancel = $sub->cancel();
    return $this;
  }
  
  /**
   * Generate href to a page detailing the order
   *
   * @param string $transactionId
   * @return string
   */
  public function getOrderDetailLink($orderId)
  {
    if( $this->getGateway()->getTestMode() ) {
      // Note: it doesn't work in test mode
      return 'https://dashboard.stripe.com/test/search?query' . $orderId;
    } else {
      return 'https://dashboard.stripe.com/search?query' . $orderId;
    }
  }

  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  public function getTransactionDetailLink($transactionId)
  {
    if( $this->getGateway()->getTestMode() ) {
      // Note: it doesn't work in test mode
      return 'https://dashboard.stripe.com/test/search?query' . $transactionId;
    } else {
      return 'https://dashboard.stripe.com/search?query' . $transactionId;
    }
  }

  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  public function getOrderDetails($orderId)
  {
    try {
      return $this->getService()->detailRecurringPaymentsProfile($orderId);
    } catch( Exception $e ) {
      echo $e;
    }

    try {
      return $this->getTransactionDetails($orderId);
    } catch( Exception $e ) {
      echo $e;
    }

    return false;
  }

  /**
   * Get raw data about a transaction
   *
   * @param $transactionId
   * @return array
   */
  public function getTransactionDetails($transactionId)
  {
    return $this->getService()->detailTransaction($transactionId);
  }
  
  // Forms

  /**
   * Get the admin form for editing the gateway info
   *
   * @return Engine_Form
   */
  public function getAdminGatewayForm(){
    return new Payment_Form_Admin_Gateway_Stripe();
  }

  public function processAdminGatewayForm(array $values){
    return $values;
  }
  
  // IPN
  /**
   * Process an IPN
   *
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onIpn(Engine_Payment_Ipn $ipn) {}
  
  public function cancelWalletOnExpiry($subscription, $package) {
    
    $secretKey = $this->_gatewayInfo->config['secret'];
    
    if($package->duration_type != "forever") {
      $durationTime = (($package->duration > 1 || $package->duration == 0) ? ("+".$package->duration." ".$package->duration_type."s") : ("+".$package->duration." ".$package->duration_type));
      $subscriptionDate = strtotime($subscription->creation_date);
      $date = date($subscriptionDate,strtotime($durationTime));
      if(strtotime("now") >= $date ) {
        \Stripe\Stripe::setApiKey($secretKey);
        $sub = \Stripe\Subscription::retrieve($subscription->gateway_profile_id);
        $sub->cancel();
        echo "Wallet canceled";
      }
    }
    echo "Wallet Continue";
  }
  
  // IPN
  /**
   * Process an IPN
   *
   * @param Payment_Model_Order $order
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onTransactionIpn(Payment_Model_Order $order,  $rawData) {
  
    // Check that gateways match
    if ($order->gateway_id != $this->_gatewayInfo->gateway_id) {
        throw new Engine_Payment_Plugin_Exception('Gateways do not match');
    }
    
    // Get related info
    $user = $order->getUser();
    $subscription = $order->getSource();
    $package = $subscription->getPackage();
    
    $current_currency = strtoupper($rawData['data']['object']['currency']);
    $currencyChangeRate = 1;
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
    if ($current_currency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($current_currency);
      $currencyChangeRate = $currencyData->change_rate;
    }
    $price = @round(($package->price * $currencyChangeRate), 2);

    //Trial
//     if(!empty($package->trial_duration)) {
//       // expiration_date
//       $expiration_date = date('Y-m-d H:i:s', strtotime($subscription->creation_date . " + ".$package->trial_duration." days"));
//       if(strtotime($expiration_date) > time()) {
//         return false;
//       }
//     }

    // expiration_date
    $expiration = $package->getExpirationDate();
    $expiration_date = date('Y-m-d H:i:s', $expiration);
		if($expiration_date == '1970-01-01 00:00:00') {
			$expiration_date = '2099-12-30 00:00:00';
		}

    $moduleName = explode("_", $package->getType());
    $moduleName = $moduleName['0'];
    
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    
    // switch message_type
    switch ($rawData['type']) {
      case 'account.updated':
      case 'account.application.deauthorized':
      case 'account.external_account.created':
      case 'account.external_account.deleted':
      case 'account.external_account.updated':
      case 'application_fee.created':
      case 'application_fee.refunded':
      case 'application_fee.refund.updated':
      case 'balance.available':
      case 'bitcoin.receiver.created':
      case 'bitcoin.receiver.filled':
      case 'bitcoin.receiver.updated':
      case 'bitcoin.receiver.transaction.created':
      case 'charge.captured':
      case 'charge.failed':
        return false; 
        break;
      case 'charge.refunded':
        // Payment Refunded
        $subscription->onRefund();
        // send notification
        //if ($subscription->didStatusChange()) {
          if ($moduleName == 'payment') {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_refunded', array(
              'subscription_title' => $package->title,
              'subscription_description' => $package->description,
              'subscription_terms' => $package->getPackageDescription(),
              'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          } else {
            Engine_Api::_()->$moduleName()->sendMail("REFUNDED", $subscription->getIdentity());
          }
        //}
        return true;
        break;
      case 'charge.succeeded':
      case 'charge.updated':
      case 'charge.dispute.closed':
      case 'charge.dispute.created':
      case 'charge.dispute.funds_reinstated':
      case 'charge.dispute.funds_withdrawn':
      case 'charge.dispute.updated':
      case 'coupon.created':
      case 'coupon.deleted':
      case 'coupon.updated':
      case 'customer.created':
      case 'customer.deleted':
      case 'customer.updated':
      case 'customer.bank_account.deleted':
      case 'customer.discount.created':
      case 'customer.discount.deleted':
      case 'customer.discount.updated':
      case 'customer.source.created':
      case 'customer.source.deleted':
      case 'customer.source.updated':
      case 'customer.subscription.created': return false; break;
      case 'customer.subscription.deleted':
        $subscription->onCancel();
        // send notification
        //if ($subscription->didStatusChange()) {
          if ($moduleName == 'payment') {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_cancelled', array(
              'subscription_title' => $package->title,
              'subscription_description' => $package->description,
              'subscription_terms' => $package->getPackageDescription(),
              'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          } else {
            Engine_Api::_()->$moduleName()->sendMail("CANCELLED", $subscription->getIdentity());
          }
        //}
        return true;
        break;
      case 'customer.subscription.trial_will_end':return false; break;
      case 'customer.subscription.updated':
        $subscription->onPaymentSuccess();
        //if ($subscription->didStatusChange()) {
          if ($moduleName == 'payment') {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_active', array(
              'subscription_title' => $package->title,
              'subscription_description' => $package->description,
              'subscription_terms' => $package->getPackageDescription(),
              'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          } else {
              Engine_Api::_()->$moduleName()->sendMail("RECURRENCE", $subscription->getIdentity());
          }
        //}
        //$this->cancelWalletOnExpiry($subscription, $package);
        return true;
        break;
      case 'invoice.created':break;
      case 'invoice.payment_failed':
        $subscription->onPaymentFailure();
        if ($moduleName == 'payment') {
        
          $params['currency'] = $defaultCurrency;  //this is default currency set by admin
          $params['amount'] = $package->price; // @todo use this or gross (-fee)?
          $params['change_rate'] = $currencyChangeRate; //currency change rate according to default currency
          $params['current_currency'] = $current_currency; //currency which is user paid
          $params['gateway_transaction_id'] = $rawData['data']['object']['charge'];
          $params['state'] = 'failed';
          $params['type'] = "payment";
          $params['expiration_date'] = $expiration_date;
          
          $transaction_id = $transactionsTable->createTransaction($order, $subscription, $user, $params);
          $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
          try {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_overdue', array(
              'subscription_title' => $package->title,
              'subscription_description' => $package->description,
              'subscription_terms' => $package->getPackageDescription(),
              'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          } 
          catch (Exception $e) {
            //silnce
          }
        } else {
          Engine_Api::_()->$moduleName()->sendMail("OVERDUE", $subscription->getIdentity());
        }
        return true;
        break;
      case 'invoice.payment_succeeded':
        $subscription->onPaymentSuccess();
        if ($moduleName == 'payment') {
          
          //Trial
//           if(empty($rawData['data']['object']['charge']))
//             return false;

          $params['currency'] = $defaultCurrency;  //this is default currency set by admin
          $params['amount'] = $package->price; // @todo use this or gross (-fee)?
          $params['change_rate'] = $currencyChangeRate; //currency change rate according to default currency
          $params['current_currency'] = $current_currency; //currency which is user paid
          $params['gateway_transaction_id'] = $rawData['data']['object']['charge'];
          $params['state'] = 'okay';
          $params['type'] = "payment";
          $params['expiration_date'] = $expiration_date;
          
          $transaction_id = $transactionsTable->createTransaction($order, $subscription, $user, $params);
          $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
          
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_wallet_recurrence', array(
            'subscription_title' => $package->title,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
          ));
        } else {
          Engine_Api::_()->$moduleName()->sendMail("RECURRENCE", $subscription->getIdentity());
        }
        //$this->cancelWalletOnExpiry($subscription, $package);
        return true;
        break;
      case 'invoice.updated':
      case 'invoiceitem.created':
      case 'invoiceitem.deleted':
      case 'invoiceitem.updated':
      case 'plan.created':
      case 'plan.deleted':
      case 'plan.updated':
      case 'recipient.created':
      case 'recipient.deleted':
      case 'recipient.updated':
      case 'transfer.created':
      case 'transfer.failed':
      case 'transfer.paid':
      case 'transfer.reversed':
      case 'transfer.updated': return false; break;
      default:
        throw new Engine_Payment_Plugin_Exception(sprintf('Unknown IPN ' .
            'type %1$s', $rawData['type']));
        break;
    }
    return $this;
  }
  
  function setConfig($params) {
    \Stripe\Stripe::setApiKey($params['secret']);
  }
  
  function test() {
  
    try {
      $stripePlan = \Stripe\Plan::retrieve("test");
      print_r($stripePlan);die;
    } catch(Exception $e) {
    echo "sf";die;
      $stripePlan = 0;
    }
  }
}
