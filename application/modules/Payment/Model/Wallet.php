<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Wallet.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Model_Wallet extends Core_Model_Item_Abstract {

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
        //Save wallet amount in user wallet
        $user = Engine_Api::_()->getItem('user', $transaction->user_id);
        $user->wallet_amount += $transaction->amount;
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
		$gateway = Engine_Api::_()->getItem('payment_walletgateway', $order->gateway_id);
		$gateway->getPlugin()->onwalletTransactionIpn($order, $ipn);
		return true;
	}

  public function getTransaction() {
    return Engine_Api::_()->getItem('payment_transaction', $this->transaction_id);
  }
}
