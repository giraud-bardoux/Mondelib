<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Orderdetails.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Model_Verification extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  
  protected $_modifiedTriggers = false;
  
  protected $_user;
  
  protected $_statusChanged;
  
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

  public function onPaymentSuccess() {
  
    $this->_statusChanged = false;
    
    $transaction = $this->getTransaction();
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
    $transaction = $this->getTransaction();
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

  public function onCancel() {
  
    $this->_statusChanged = false;
    $transaction = $this->getTransaction();
    if ($transaction && ( engine_in_array($transaction->state, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled', 'okay')) )) {
      if ($transaction->state != 'cancelled') {
        $transaction->state = 'cancelled';
        $this->_statusChanged = true;
        $transaction->save();
        
        $user = Engine_Api::_()->getItem('user', $transaction->user_id);
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

  public function getTransaction() {
    return Engine_Api::_()->getItem('payment_transaction', $this->transaction_id);
  }

  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onVerificationTransactionReturn(Payment_Model_Order $order, array $params = array()) {

    // Get related info
    $user = $order->getUser();
    $subscription = $order->getSource();
    $subscriptionItem = Engine_Api::_()->getItem('payment_subscription', $params['subscription_id']);
    
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
    $transaction_id = $transactionsTable->createTransaction($order, $subscription, $user, $params);
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);

    $giveBenefit = true; // Need this

    // Enable now
    if( $giveBenefit ) {

      //Update transaction_id in payment verification table
      $subscription->transaction_id = $transaction_id;
      $subscription->save();
      
      // Payment success
      $subscription->onPaymentSuccess();
      
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
}
