<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Testing.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Plugin_Gateway_Verification_Cash extends Engine_Payment_Plugin_Abstract
{
  protected $_gatewayInfo;

  protected $_gateway;



  // General

  /**
   * Constructor
   */
  public function __construct(Zend_Db_Table_Row_Abstract $gatewayInfo)
  {
    $this->_gatewayInfo = $gatewayInfo;
  }

  /**
   * Get the service API
   *
   * @return Engine_Service_Testing
   */
  public function getService()
  {
    return $this->getGateway()->getService();
  }

  /**
   * Get the gateway object
   *
   * @return Engine_Payment_Gateway_Testing
   */
  public function getGateway()
  {
    if( null === $this->_gateway ) {
      $class = 'Engine_Payment_Gateway_Testing';
      Engine_Loader::loadClass($class);
      $gateway = new $class(array(
        'config' => (array) $this->_gatewayInfo->config,
        'testMode' =>  true, //$this->_gatewayInfo->test_mode,
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
    @$transaction->process($this->getGateway());
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

  public function detectIpn(array $params)
  {
    return false; // Never detect this as an IPN, or it will break real IPNs
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

  // SE Specific

  /**
   * Create a transaction for a subscription
   *
   * @param User_Model_User $user
   * @param Zend_Db_Table_Row_Abstract $subscription
   * @param Zend_Db_Table_Row_Abstract $package
   * @param array $params
   * @return Engine_Payment_Gateway_Transaction
   */
  public function createVerificationTransaction(User_Model_User $user, array $params = array())
  {
    // Create transaction
    @$transaction = $this->createTransaction($params);
    return $transaction;
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
  public function onVerificationTransactionReturn(Payment_Model_Order $order, array $params = array()) {
  
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
    $price = $subscriptionParams->price;
    
    $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
    $currencyChangeRate = 1;
    if ($currentCurrency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
      $currencyChangeRate = $currencyData->change_rate;
    }
    //$price = @round(($price * $currencyChangeRate), 2);
    
    // description
    $desc = Engine_Api::_()->payment()->getPackageDescription($recurrence, $price);
    
    // expiration_date
    $expiration_date = date('Y-m-d H:i:s', Engine_Api::_()->payment()->getExpirationDate($recurrence));
		if($expiration_date == '1970-01-01 00:00:00') {
			$expiration_date = '2099-12-30 00:00:00';
		}
		
    // Let's log it
    $this->getGateway()->getLog()->log('Return: '
        . print_r($params, true), Zend_Log::INFO);

    // Should we accept this?

    // Update order with profile info and complete status?
    $order->state = 'complete';
    $order->gateway_order_id = 0; // Hack
    $order->save();
    
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');

    $params['gateway_transaction_id'] = crc32(microtime() . $order->order_id); // Hack
    $params['currency'] = $this->getGateway()->getCurrency();
    $params['state'] = "pending";
    $params['amount'] = $price;
    $params['type'] = "payment verification";
    $params['expiration_date'] = $expiration_date;
    $params['change_rate'] = $currencyChangeRate;
    $params['current_currency'] = $currentCurrency;
    
    // Insert transaction
    $transaction_id = $transactionsTable->createTransaction($order, $subscription, $user, $params);
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);

    if(isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
      $transaction->setPhoto($_FILES['file']);
    }
    // Get benefit setting
    $giveBenefit = Engine_Api::_()->getDbtable('transactions', 'payment')
        ->getBenefitStatus($user);

    $giveBenefit = true; // Need this

    // Enable now
    if( $giveBenefit ) {
    
      //Send notification to super admin
      $getAllAdmin = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
      if(engine_count($getAllAdmin) > 0) {
        $translate = Zend_Registry::get('Zend_Translate');
        
        $adminLink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'index', 'action' => 'index'), 'admin_default', true);
        $adminSideLink = '<a href="'.$adminLink.'" >'.$translate->translate("admin panel").'</a>';
        foreach($getAllAdmin as $admin) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $user, $user, 'payment_manual_verification', array('payment_method' => 'Cash','adminsidelink' => $adminSideLink));
          
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin, 'payment_manual_verification', array(
            'payment_method' => 'Cash',
            'sender_name' => $user->getTitle(),
            'admin_link' => $adminLink,
          ));
        }
      }

      // Update subscription
      //$subscription->gateway_id = $this->_gatewayInfo->gateway_id;
      //$subscription->gateway_profile_id = crc32(time() . $order->order_id); // Hack
      
      $subscription->onPaymentPending();

      // send notification
      //if( $subscription->didStatusChange() ) {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_verification_pending', array(
          'subscription_terms' => $desc,
          'object_link' => 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => 'payment', 'controller' => "settings", "action" => "verification"), 'default', true),
        ));
      //}

      return 'pending';
    }

    // Enable later
    else {

      // Update subscription
      //$subscription->gateway_id = $this->_gatewayInfo->gateway_id;
      //$subscription->gateway_profile_id = crc32(time() . $order->order_id); // Hack
      $subscription->onPaymentPending();

      // send notification
      //if( $subscription->didStatusChange() ) {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_verification_pending', array(
          'subscription_terms' => $desc,
          'object_link' => 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => 'payment', 'controller' => "settings", "action" => "verification"), 'default', true),
        ));
      //}

      return 'pending';
    }
  }

  /**
   * Process ipn of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param Engine_Payment_Ipn $ipn
   */
  public function onSubscriptionTransactionIpn(
      Payment_Model_Order $order,
      Engine_Payment_Ipn $ipn)
  {
    throw new Engine_Payment_Plugin_Exception('Not implemented');
  }

  /**
   * Cancel a subscription (i.e. disable the recurring payment profile)
   *
   * @params $transactionId
   * @return Engine_Payment_Plugin_Abstract
   */
  public function cancelSubscription($transactionId)
  {
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
    return false;
  }

  /**
   * Generate href to a page detailing the transaction
   *
   * @param string $transactionId
   * @return string
   */
  public function getTransactionDetailLink($transactionId)
  {
    return false;
  }

  /**
   * Get raw data about an order or recurring payment profile
   *
   * @param string $orderId
   * @return array
   */
  public function getOrderDetails($orderId)
  {
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
    return false;
  }



  // IPN

  /**
   * Process an IPN
   *
   * @param Engine_Payment_Ipn $ipn
   * @return Engine_Payment_Plugin_Abstract
   */
  public function onIpn(Engine_Payment_Ipn $ipn)
  {
    throw new Engine_Payment_Plugin_Exception('Not implemented');
  }

  public function getGatewayUserForm(){
    $form = new Payment_Form_Gateway_Cash(array('settings'=>$this->_gatewayInfo->config));
    $form->setDescription("Make the payment for the Verification Subscription by Cash and then upload the receipt of your transaction.");
    $form->populate((array) $this->_gatewayInfo->config);
    return $form;
  }
  // Forms
  /**
   * Get the admin form for editing the gateway info
   *
   * @return Engine_Form
   */
  public function getAdminGatewayForm()
  {
    return new Payment_Form_Admin_Gateway_Cash();
  }

  public function processAdminGatewayForm(array $values)
  {
    return $values;
  }
}
