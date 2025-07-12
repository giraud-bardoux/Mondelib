<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Transactions.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_DbTable_Transactions extends Engine_Db_Table
{
  protected $_rowClass = 'Payment_Model_Transaction';

  public function getBenefitStatus(User_Model_User $user = null)
  {
    // Get benefit setting
    $benefitSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.benefit');
    if( !engine_in_array($benefitSetting, array('all', 'some', 'none')) ) {
      $benefitSetting = 'all';
    }

    switch( $benefitSetting ) {
      default:
      case 'all':
        return true;
        break;

      case 'some':
        if( !$user ) {
          return false;
        }
        return (bool) $this->select()
          ->from($this, new Zend_Db_Expr('TRUE'))
          ->where('user_id = ?', $user->getIdentity())
          ->where('type = ?', 'payment')
          ->where('status = ?', 'okay')
          ->limit(1);
        break;

      case 'none':
        return false;
        break;
    }

    return false;
  }

  public function getTransaction($params = array()) {
    $tableName = $this->info('name');
    $select = $this->select()
          ->from($tableName)
          ->where('user_id <> ?', 0)
          ->where('user_id =?', $params['user_id'])
          // ->where('expiration_date >= ?', date("Y-m-d H:i:s"))
          //->where('gateway_transaction_id !=?', '')
          ->where('state = "complete" || state = "okay" || state = "active" || state = "pending" ')
          ->order('transaction_id DESC')
          ->limit(1);




    if(isset($params['type']) && !empty($params['type'])) {
      $select->where('type =?', $params['type']);
    }
    if(empty($params['fromMenu'])) {
      $select->where('expiration_date >=?', date("Y-m-d H:i:s"));
    }
    return $this->fetchRow($select);
  }
  
  public function createTransaction($order, $subscription, $user, $params = array()) {
  
    $this->insert(array(
      'user_id' => $order->user_id,
      'gateway_id' => $order->gateway_id, //$this->_gatewayInfo->gateway_id,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'order_id' => $order->order_id,
      'type' => $params['type'], //'payment verification', //'payment',
      'state' => $params['state'], //$paymentStatus,
      'gateway_transaction_id' => $params['gateway_transaction_id'] ? $params['gateway_transaction_id'] : $subscription->gateway_profile_id, //$rdata['PAYMENTINFO'][0]['TRANSACTIONID'],
      'amount' => $params['amount'],  //$rdata['AMT'], // @todo use this or gross (-fee)?
      'currency' => $params['currency'], //$rdata['PAYMENTINFO'][0]['CURRENCYCODE'],
      'expiration_date' => $params['expiration_date'],
      'change_rate' => !empty($params['change_rate']) ? $params['change_rate'] : 1,
      'current_currency' => !empty($params['current_currency']) ? $params['current_currency'] : $params['currency'],
    ));
    return $this->getAdapter()->lastInsertId();
  }
  
  public function getSubscriptionTransaction($params = array()) {
    $tableName = $this->info('name');
    $select = $this->select()
          ->from($tableName)
          ->where('user_id =?', $params['user_id'])
          ->where('subscription_id = ?', $params['subscription_id'])
          ->where('state = "complete" || state = "okay" || state = "active" ')
          ->order('transaction_id DESC')
          ->limit(1);
    return $this->fetchRow($select);
  }
}
