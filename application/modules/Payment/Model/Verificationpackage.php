<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Verificationpackage.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Model_Verificationpackage extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  
  protected $_modifiedTriggers = false;
  
  protected $_user;
  
  protected $_statusChanged;
  
  protected $_package;
  
  public function getUser()
  {
    if( empty($this->user_id) ) {
      return null;
    }
    if( null === $this->_user ) {
      $this->_user = Engine_Api::_()->getItem('user', $this->user_id);
    }
    return $this->_user;
  }

  public function getPackage()
  {
    if( empty($this->verificationpackage_id) ) {
      return null;
    }
    if( null === $this->_package ) {
      $this->_package = Engine_Api::_()->getItem('payment_verificationpackage', $this->verificationpackage_id);
    }
    return $this->_package;
  }
  
  public function getPackageDescription($param = false) {
  
    $translate = Zend_Registry::get('Zend_Translate');
    $view = Zend_Registry::get('Zend_View');
    
    //Check for admin panel
    if($param) {
      $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
      $priceStr =  Engine_Api::_()->payment()->getCurrencyPrice($this->price,$currency,'','');
    } else {
      $currency = Engine_Api::_()->payment()->getCurrentCurrency();
      $priceStr =  Engine_Api::_()->payment()->getCurrencyPrice($this->price,'','','');
    }
    
    // Plan is free
    if( $this->price == 0 ) {
      $str = $translate->translate('Free');
    }

    // Plan is recurring
    else if( $this->recurrence > 0 && $this->recurrence_type != 'forever' ) {
      // Make full string
      if( $this->recurrence == 1 ) { // (Week|Month|Year)ly
        if( $this->recurrence_type == 'day' ) {
          $typeStr = $translate->translate('daily');
        } else {
          $typeStr = $translate->translate($this->recurrence_type . 'ly');
        }
        $str = sprintf($translate->translate('%1$s %2$s'), $priceStr, $typeStr);
      } else { // per x (Week|Month|Year)s
        $typeStr = $translate->translate(array($this->recurrence_type, $this->recurrence_type . 's', $this->recurrence));
        $str = sprintf($translate->translate('%1$s per %2$s %3$s'), $priceStr,
        $this->recurrence, $typeStr); // @todo currency
      }
    }
    // Plan is one-time
    else {
      $str = sprintf($translate->translate('One-time fee of %1$s'), $priceStr);
    }
    
    // Add duration, if not forever
    if( $this->duration > 0 && $this->duration_type != 'forever' ) {
      $typeStr = $translate->translate(array($this->duration_type, $this->duration_type . 's', $this->duration));
      $str = sprintf($translate->translate('%1$s for %2$s %3$s'), $str, $this->duration, $typeStr);
    }

    return $str;
  }
  
  // Events
  public function clearStatusChanged()
  {
    $this->_statusChanged = null;
    return $this;
  }

  public function didStatusChange()
  {
    return (bool) $this->_statusChanged;
  }

  public function onPaymentSuccess($transaction_id) {
  
    $this->_statusChanged = false;
    
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
    if ($transaction) {
      if (engine_in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'okay'))) {
      
        // Change status
        if( $transaction->state != 'active' ) {
          $transaction->state = 'active';
          $this->_statusChanged = true;
          $transaction->save();
        }

        $user = Engine_Api::_()->getItem('user', $transaction->user_id);
        $user->is_verified = 1;
        $user->save();
      }
    } 
    return $transaction;
  }

  public function onPaymentPending() {
  
    $this->_statusChanged = false;
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
    if ($transaction && ( engine_in_array($transaction->state, array('initial', 'trial', 'pending', 'active')))) {
      if ($transaction->state != 'pending') {
        $transaction->state = 'pending';
        $this->_statusChanged = true;
        $transaction->save();
      }
    }
    return $this;
  }

  public function onPaymentFailure() {
  
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();

    if ($transaction && engine_in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'overdue'))) {
      // Change status
      if ($transaction->state != 'overdue') {
        $transaction->state = 'overdue';
        $this->_statusChanged = true;
        $transaction->save();
      }
    }
    return $this;
  }

  public function onCancel($subscription) {
  
    $this->_statusChanged = false;
    if ($subscription && ( engine_in_array($subscription->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled', 'okay')) )) {
      if ($subscription->status != 'cancelled') {
        $subscription->status = 'cancelled';
        $this->_statusChanged = true;
        $subscription->save();
        
        $user = Engine_Api::_()->getItem('user', $subscription->user_id);
        $user->is_verified = 0;
        $user->save();
      }
    }
    return $this;
  }

  public function onExpiration() {
  
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();
    if ($transaction && ( engine_in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'overdue', 'okay')) )) {
      if ($transaction->state != 'expired') {
        $transaction->state = 'expired';
        $this->_statusChanged = true;
        $transaction->save();
        
        $user = Engine_Api::_()->getItem('user', $transaction->user_id);
        $user->is_verified = 0;
        $user->save();
      }
    }
    return $this;
  }
  
	/**
		* Process ipn of qrcode transaction
		*
		* @param Payment_Model_Order $order
		* @param Engine_Payment_Ipn $ipn
  */
	public function onPaymentIpn(Payment_Model_Order $order, Engine_Payment_Ipn $ipn) {
		$gateway = Engine_Api::_()->getItem('payment_verificationgateway', $order->gateway_id);
		$gateway->getPlugin()->onVerificationTransactionIpn($order, $ipn);
		return true;
	}

  public function getTransaction($transaction_id) {
    return Engine_Api::_()->getItem('payment_transaction', $transaction_id);
  }

  public function hasDuration()
  {
      return ( $this->duration > 0 && $this->duration_type != 'forever' );
  }

  public function isFree()
  {
      return ( $this->price <= 0 );
  }
    
  public function isOneTime()
  {
      return ( $this->recurrence <= 0 || $this->recurrence_type == 'forever' );
  }
  
  public function getExpirationDate($rel = null)
  {
    if( null === $rel ) {
        $rel = time();
    }

    // If it's a one-time payment or a free package with no duration, there
    // is no expiration
    if( ($this->isOneTime() || $this->isFree()) && !$this->hasDuration() ) {
      return false;
    }

    // If this is a free or one-time package, the expiration is based on the
    // duration, otherwise the expirations is based on the recurrence
    $interval = null;
    $interval_type = null;
    if( $this->isOneTime() || $this->isFree() ) {
    $interval = $this->duration;
    $interval_type = $this->duration_type;
    } else {
    $interval = $this->recurrence;
    $interval_type = $this->recurrence_type;
    }

    // This is weird, it should have been handled by the statement at the top
    if( $interval == 'forever' ) {
    return false;
    }

    // Calculate when the next payment should be due
    switch( $interval_type ) {
    case 'day':
        $part = Zend_Date::DAY;
        break;
    case 'week':
        $part = Zend_Date::WEEK;
        break;
    case 'month':
        $part = Zend_Date::MONTH;
        break;
    case 'year':
        $part = Zend_Date::YEAR;
        break;
    default:
        throw new Engine_Payment_Exception('Invalid recurrence_type');
        break;
    }

    $relDate = new Zend_Date($rel);
    $relDate->add((int) $interval, $part);

    return $relDate->toValue();
  }

  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onSubscriptionTransactionReturn(Payment_Model_Order $order, array $params = array()) {

    // Get related info
    $user = $order->getUser();
    $verificationpackage = $order->getSource();
    $subscriptionItem = Engine_Api::_()->getItem('payment_subscription', $params['subscription_id']);

    $recurrence = $verificationpackage->recurrence;
    $recurrence_type = $verificationpackage->recurrence_type;
    $price = $verificationpackage->price;
    
    $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
    $currencyChangeRate = 1;
    if ($currentCurrency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
      $currencyChangeRate = $currencyData->change_rate;
    }
    //$price = @round(($price * $currencyChangeRate), 2);
    
    // description
    $desc = $verificationpackage->getPackageDescription();
    
    // expiration_date
    $expiration_date = date('Y-m-d H:i:s', $verificationpackage->getExpirationDate());
		if($expiration_date == '1970-01-01 00:00:00') {
			$expiration_date = '2099-12-30 00:00:00';
		}

    // Update order with profile info and complete status?
    $order->state = 'complete';
    $order->gateway_order_id = $gateway_id; // Hack
    $order->save();

    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');

    $gateway_transaction_id = crc32(microtime() . $order->order_id);
    $params['gateway_transaction_id'] = $gateway_transaction_id; // Hack
    $params['currency'] = $defaultCurrency;
    $params['state'] = "okay";
    $params['amount'] = $price;
    $params['type'] = "payment verification";
    $params['expiration_date'] = $expiration_date;
    $params['change_rate'] = $currencyChangeRate;
    $params['current_currency'] = $currentCurrency;
    
    // Insert transaction
    $transaction_id = $transactionsTable->createTransaction($order, $verificationpackage, $user, $params);
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);

    $giveBenefit = true; // Need this

    // Enable now
    if( $giveBenefit ) {

      //Update transaction_id in payment verification table
//       $subscription->transaction_id = $transaction_id;
//       $subscription->save();
      
      // Payment success
      $verificationpackage->onPaymentSuccess($transaction_id);
      
      $transaction->subscription_id = $subscriptionItem->getIdentity();
      $transaction->save();

      // Update subscription info
      $subscriptionItem->status = 'active';
      $subscriptionItem->active = 1;
      $subscriptionItem->expiration_date = $expiration_date;
      $subscriptionItem->gateway_profile_id = $gateway_transaction_id;
      $subscriptionItem->order_id = $order->order_id;
      $subscriptionItem->resource_type = $order->source_type;
      $subscriptionItem->resource_id = $order->source_id;
      $subscriptionItem->save();

      $user->wallet_amount = ($user->wallet_amount - $price);
      $user->save();

      // send email
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_verification_active', array(
        'subscription_terms' => $desc,
        'object_link' => 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => 'payment', 'controller' => "settings", "action" => "verification"), 'default', true),
      ));
      return 'active';
    }
    // Enable later
    else {

      // Update subscription
      //$subscription->gateway_id = $this->_gatewayInfo->gateway_id;
      //$subscription->gateway_profile_id = crc32(time() . $order->order_id); // Hack
      $verificationpackage->onPaymentPending($transaction);

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
  
  //call using cron charged by wallet
  public function onSubscriptionCharged($subscription) {

    $user = Engine_Api::_()->getItem('user', $subscription->user_id);
    if ($user->wallet_amount > 0) {
      $package = $this->getPackage();
      if($package->price > 0 && $user->wallet_amount >= $package->price) {
        try {
          $order = Engine_Api::_()->getItem('payment_order', $subscription->order_id);
          $status = $this->onSubscriptionTransactionReturn($order, array('subscription_id' => $subscription->getIdentity()));
          if(($status == 'active' || $status == 'free')) {
            $admins = Engine_Api::_()->user()->getSuperAdmins();
            foreach($admins as $admin){
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,'payment_verification_transaction', array('gateway_type' => "Wallet", 'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module'=>'payment'), 'admin_default', true)));
            }
          }
        } catch( Payment_Model_Exception $e ) {
          throw $e->getMessage();
        }
      } else {
        $this->onCancel();
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_verification_cancelled', array(
          'subscription_title' => $package->title,
          'queue'=>false,
          'subscription_description' => $package->description,
          'subscription_terms' => $package->getPackageDescription(),
          'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
          Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }
    } else if($subscription->didStatusChange()) {
      $this->onCancel();
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_verification_cancelled', array(
        'subscription_title' => $package->title,
        'queue'=>false,
        'subscription_description' => $package->description,
        'subscription_terms' => $package->getPackageDescription(),
        'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
        Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
      ));
    }
  }
}
