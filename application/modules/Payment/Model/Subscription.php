<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Subscription.php 10098 2013-10-19 00:01:38Z jung $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_Subscription extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    protected $_modifiedTriggers = false;

    protected $_user;

    protected $_gateway;

    protected $_package;

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

    public function getGateway()
    {
        if( empty($this->gateway_id) ) {
        return null;
        }
        if( null === $this->_gateway ) {
        $this->_gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
        }
        return $this->_gateway;
    }

    public function getPackage()
    {
        if( empty($this->package_id) ) {
        return null;
        }
        if( null === $this->_package ) {
        $this->_package = Engine_Api::_()->getItem('payment_package', $this->package_id);
        }
        return $this->_package;
    }

  // Actions

    public function upgradeUser()
    {
        $user = $this->getUser();
        if( !$user ||
            !isset($user->level_id) ||
            !isset($user->enabled) ) {
            return $this;
        }
        $level = $this->getPackage()->getLevel();
        if( !$level ||
            !isset($level->level_id) ) {
        return $this;
        }
        if( $user->level_id != $level->level_id ) {
        $user->level_id = $level->level_id;
        }
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        return $this;
    }

    public function downgradeUser()
    {
        $user = $this->getUser();
        if( !$user ||
            !isset($user->level_id) ||
            !isset($user->enabled) ) {
        return $this;
        }
        $package = $this->getPackage();
        if( !$package ||
            !isset($package->downgrade_level_id) ) {
            return $this;
        }
        if(!Engine_Api::_()->getItem('authorization_level', $package->downgrade_level_id))
            return $this;
        if($user->level_id != $package->downgrade_level_id && (!$this->expiration_date || strtotime($this->expiration_date) <= time())) {
            $user->level_id = $package->downgrade_level_id;
        }
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        return $this;
    }

//     public function downgradeExpirdUser()
//     {
//         $user = $this->getUser();
//         if(!$user ||
//             !isset($user->level_id) ||
//             !isset($user->enabled) ) {
//             return $this;
//         }
//         $package = $this->getPackage();
//         if( !$package ||
//             !isset($package->downgrade_level_id) ) {
//             return $this;
//         }
//         if($user->level_id != $package->downgrade_level_id ) {
//             $user->level_id = $package->downgrade_level_id;
//         }
//         $user->enabled = true; // This will get set correctly in the update hook
//         $user->save();
//         return $this;
//     }

    public function cancel()
    {
        // Try to cancel recurring payments in the gateway
        if( !empty($this->gateway_id) && !empty($this->gateway_profile_id) ) {
        try {
            $gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
            if( $gateway ) {
                $gatewayPlugin = $gateway->getPlugin();
                if( method_exists($gatewayPlugin, 'cancelSubscription') ) {
                    $gatewayPlugin->cancelSubscription($this->gateway_profile_id);
                }
            }
        } catch( Exception $e ) {
            // Silence?
        }
        }
        // Cancel this row
        $this->active = false; // Need to do this to prevent clearing the user's session
        $this->onCancel();
        return $this;
    }


    // Active

    public function setActive($flag = true, $deactivateOthers = null)
    {
        $this->active = true;

        if( (true === $flag && null === $deactivateOthers) ||
            $deactivateOthers === true ) {
        $table = $this->getTable();
        $select = $table->select()
            ->where('user_id = ?', $this->user_id)
            ->where('active = ?', true)
            ;
        foreach( $table->fetchAll($select) as $otherSubscription ) {
            $otherSubscription->setActive(false);
        }
        }

        $this->save();
        return $this;
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

    public function onPaymentSuccess($param = '')
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {

            // If the subscription is in initial or pending, set as active and
            // cancel any other active subscriptions
            if( engine_in_array($this->status, array('initial', 'pending')) ) {
                $this->setActive(true);
                Engine_Api::_()->getDbtable('subscriptions', 'payment')
                ->cancelAll($this->getUser(), 'User cancelled the subscription.', $this);
            }
            if($this->main_package_id){
                $this->package_id = $this->main_package_id;
                $this->save();
            }
            // Update expiration to expiration + recurrence or to now + recurrence?
            $package = $this->getPackage();
            // It will only work for admin
            if($param == 'fromadmin') {
              $expiration = $package->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            } else {
              $expiration = $package->getExpirationDate();
            }
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
                //This is for subscription plan is expiring reminder email.
                $this->email_reminder = 0;
            }

            // Change status
            if( $this->status != 'active' ) {
                $this->status = 'active';
                $this->_statusChanged = true;
            }

            // Update user if active
            if( $this->active ) {
                $this->upgradeUser();
            }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }

    public function onPaymentPending()
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {
        // Change status
        if( $this->status != 'pending' ) {
            $this->status = 'pending';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // @todo should we do this?
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            //Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();
        $levelIdChanged = 0;
        $user = $this->getUser();
        $defaultPackage = Engine_Api::_()->getDbtable('packages', 'payment')->getDefaultPackage();
        if(!empty($defaultPackage) && (int)$defaultPackage->price == 0 && $defaultPackage->package_id != $this->package_id){

            $this->main_package_id = $this->package_id;
            $this->save();
            $package = $this->getPackage();
            if($defaultPackage->level_id) {
                $user->level_id = $defaultPackage->level_id;
                $user->save();  
                $levelIdChanged = 1;
            }

            $this->active = 1;
            $this->status = 'active';
            $this->package_id = $defaultPackage->package_id;
            $this->save();

            // Update expiration to expiration + recurrence or to now + recurrence?
            $expiration = $defaultPackage->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
            }
        }

        // Check if the member should be enabled
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        if(!$levelIdChanged){
            $this->downgradeUser();
            $this->save();
        }
        return $this;
    }

    public function onPaymentFailure()
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue')) ) {
            // Change status
            if( $this->status != 'overdue' ) {
                $this->status = 'overdue';
                $this->_statusChanged = true;
            }

            // Downgrade and log out user if active
            if( $this->active ) {
                // Downgrade user
                $this->downgradeUser();

                // Remove active sessions?
                Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
            }
        }
        $levelIdChanged = 0;
        // Check if the member should be enabled
        $user = $this->getUser();
        $defaultPackage = Engine_Api::_()->getDbtable('packages', 'payment')->getDefaultPackage();
        if(!empty($defaultPackage) && (int)$defaultPackage->price == 0 && $defaultPackage->package_id != $this->package_id){

            $this->main_package_id = $this->package_id;
            $this->save();
            if($defaultPackage->level_id) {
                $user->level_id = $defaultPackage->level_id;
                $user->save();  
                $levelIdChanged = 1;
            }

            $this->active = 1;
            $this->status = 'active';
            $this->package_id = $defaultPackage->package_id;
            $this->save();

            // Update expiration to expiration + recurrence or to now + recurrence?
            $expiration = $defaultPackage->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
            }
        }

        $user->enabled = 1; // This will get set correctly in the update hook
        $user->save();
        
        if(!$levelIdChanged){
            $this->downgradeUser();
            $this->save();
        }

        return $this;
    }

    public function onCancel()
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled')) ) {
        // Change status
        if( $this->status != 'cancelled' ) {
            $this->status = 'cancelled';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }

    public function onExpiration()
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active', 'expired', 'overdue')) ) {
        // Change status
        if( $this->status != 'expired' ) {
            $this->status = 'expired';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        if( $user && isset($user->enabled) ) { // Fix for deleted members
            $user->enabled = true; // This will get set correctly in the update hook
            $user->save();
        }

        return $this;
    }

    public function onRefund()
    {
        $this->_statusChanged = false;
        if( engine_in_array($this->status, array('initial', 'trial', 'pending', 'active', 'refunded')) ) {
        // Change status
        if( $this->status != 'refunded' ) {
            $this->status = 'refunded';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }

  /**
   * Process return of subscription transaction
   *
   * @param Payment_Model_Order $order
   * @param array $params
   */
  public function onSubscriptionTransactionReturn(Payment_Model_Order $order, array $params = array()) {
    // Check that gateways match
//     if( $order->gateway_id != $this->_gatewayInfo->gateway_id ) {
//       throw new Engine_Payment_Plugin_Exception('Gateways do not match');
//     }

    // Get related info
    $user = $order->getUser();
    $subscription = $order->getSource();
    $package = $subscription->getPackage();

    //Change rate according to default currency and selected currency by member
    $session = new Zend_Session_Namespace('Payment_Subscription');
    $current_currency = $session->current_currency;
    
    if(empty($current_currency)) {
      $transaction = Engine_Api::_()->getDbTable('transactions', 'payment')->getSubscriptionTransaction(array('user_id' => $subscription->user_id, 'subscription_id' => $subscription->getIdentity()));
      if($transaction) {
        $current_currency = $transaction->current_currency;
      }
    } else {
      $currencyChangeRate = $session->change_rate;
    }
    if (empty($currencyChangeRate))
      $currencyChangeRate = 1;
    $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();

    if ($current_currency != $defaultCurrency) {
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($current_currency);
      $currencyChangeRate = $currencyData->change_rate;
    }
    $price = @round(($package->price * $currencyChangeRate), 2);

    // Check subscription state
    if($subscription->status == 'trial') {
      return 'active';
    } else if( $subscription->status == 'pending' ) {
      return 'pending';
    }

    // Get payment state
    $paymentStatus = 'okay';
    $orderStatus = 'complete';

    // Update order with profile info and complete status?
    $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
    $gateway_transaction_id = crc32(microtime() . $order->order_id);
    
    $order->state = $orderStatus;
    $order->gateway_transaction_id = $gateway_transaction_id; //$rdata['PAYMENTINFO'][0]['TRANSACTIONID'];
    $order->save();

    // Insert transaction
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $transactionsTable->insert(array(
      'user_id' => $order->user_id,
      'gateway_id' => $order->gateway_id,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'order_id' => $order->order_id,
      'type' => 'payment',
      'state' => $paymentStatus,
      'gateway_transaction_id' => $gateway_transaction_id,
      'amount' => $package->price, // @todo use this or gross (-fee)?
      'currency' => $defaultCurrency, //this is default currency set by admin
      'change_rate' => $currencyChangeRate, //currency change rate according to default currency
      'current_currency' => $currentCurrency, //currency which is user paid
    ));
    $transaction_id = $transactionsTable->getAdapter()->lastInsertId();
    $transaction = Engine_Api::_()->getItem('payment_transaction', $transaction_id);
      
    // Get benefit setting
    $giveBenefit = true;
    // Check payment status
    if( $paymentStatus == 'okay' || ($paymentStatus == 'pending' && $giveBenefit) ) {

      // Update subscription info
      $subscription->gateway_id = $order->gateway_id;
      $subscription->gateway_profile_id = $gateway_transaction_id;

      // Payment success
      $subscription->onPaymentSuccess();
      
      //Save subscription id in transaction id
      $transaction->expiration_date = $subscription->expiration_date;
      $transaction->subscription_id = $subscription->subscription_id;
      $transaction->save();
      
      //save in subscription table table
      if($order) {
        $subscription->order_id = $order->order_id;
        $subscription->resource_type = $order->source_type;
        $subscription->resource_id = $order->source_id;
        $subscription->save();
      }
      $user->wallet_amount = ($user->wallet_amount - $package->price);
      $user->save();

      // send notification
      if( $subscription->didStatusChange() ) {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_active', array(
          'subscription_title' => $package->title,
          'subscription_description' => $package->description,
          'subscription_terms' => $package->getPackageDescription(),
          'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }
      return 'active';
    }
    else if( $paymentStatus == 'pending' ) {

      // Update subscription info
      $subscription->gateway_id = $this->_session->gateway_id;
      $subscription->gateway_profile_id = $gateway_transaction_id;

      // Payment pending
      $subscription->onPaymentPending();

      // send notification
      if( $subscription->didStatusChange() ) {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_pending', array(
          'subscription_title' => $package->title,
          'subscription_description' => $package->description,
          'subscription_terms' => $package->getPackageDescription(),
          'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }

      return 'pending';
    }
    else if( $paymentStatus == 'failed' ) {
      // Cancel order and subscription?
      $order->onFailure();
      $subscription->onPaymentFailure();
      // Payment failed
      echo json_encode(array('status' => false, 'message' => $this->view->translate('Your payment could not be completed. Please ensure there are sufficient available funds in your account.')));die;
      //throw new Payment_Model_Exception('Your payment could not be completed. Please ensure there are sufficient available funds in your account.');
    }
    else {
      // This is a sanity error and cannot produce information a user could use
      // to correct the problem.
      echo json_encode(array('status' => false, 'message' => $this->view->translate('There was an error processing your transaction. Please try again later.')));die;
      //throw new Payment_Model_Exception('There was an error processing your transaction. Please try again later.');
    }
  }

  //call using cron charged by wallet
  public function onSubscriptionCharged($subscription) {

    $user = Engine_Api::_()->getItem('user', $subscription->user_id);
    if ($user->wallet_amount > 0) {
      $package = $this->getPackage();
      if($package->price > 0 && $user->wallet_amount > $package->price) {
        try {
          $order = Engine_Api::_()->getItem('payment_order', $subscription->order_id);
          $status = $this->onSubscriptionTransactionReturn($order, array('subscription_id' => $subscription->getIdentity()));
          if(($status == 'active' || $status == 'free')) {
            $admins = Engine_Api::_()->user()->getSuperAdmins();
            foreach($admins as $admin){
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,'payment_subscription_transaction', array('gateway_type' => "Wallet", 'object_link' => 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module'=>'payment'), 'admin_default', true)));
            }
          }
        } catch( Payment_Model_Exception $e ) {
          throw $e->getMessage();
        }
      } else {
        $this->onCancel();
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_cancelled', array(
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
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'payment_subscription_cancelled', array(
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
