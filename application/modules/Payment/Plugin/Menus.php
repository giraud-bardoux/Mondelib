<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Plugin_Menus {

  public function onMenuInitialize_CoreMiniCurrency($row) {
  
    // Have any gateways or packages been added yet?
    if(Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0)
      return false;
    
    $enabledCurrencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));
    if(engine_in_array(engine_count($enabledCurrencies), array(0,1)))
      return false;

    return true;
  }
  
  public function onMenuInitialize_UserSettingsPayment($row)
  {
    // Have any gateways or packages been added yet?
    if(Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0) {
      return false;
    }

    if(Engine_Api::_()->getDbtable('packages', 'payment')->getEnabledAfterSignupPackageCount() <= 0)
      return false;
    
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $user = Engine_Api::_()->user()->getViewer();
    $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $user->getIdentity(),
      'active = ?' => true,
    ));
    
    if(!$currentSubscription) {
      return false;
    }
    return true;
  }
  
  public function onMenuInitialize_UserSettingsVerification($row) {
    $user = Engine_Api::_()->user()->getViewer();
    $package = Engine_Api::_()->getDbTable('verificationpackages', 'payment')->getPackage(array('level_id' => $user->level_id));
    if(engine_in_array($package->verified, array(0,1)))
      return false;
    return true;
  }
    
  public function onMenuInitialize_UserSettingsWallet($row) {

    // Have any gateways or packages been added yet?
    if(Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0) {
      return false;
    }

    $user = Engine_Api::_()->user()->getViewer();
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1))
      return false;
    return true;
  }
  
  public function onMenuInitialize_UserSettingsTransaction($row)
  {
    $user = Engine_Api::_()->user()->getViewer();
    
    // Have any gateways or packages been added yet?
    if(Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0) {
      return false;
    }

    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.enablewallet",1))
      return false;
    
    //$verified = Engine_Api::_()->authorization()->getPermission($user, 'user', 'verified');
    
    $transaction = Engine_Api::_()->getDbTable('transactions', 'payment')->getTransaction(array('user_id' => $user->getIdentity(),'fromMenu'=>true));

    // if(Engine_Api::_()->getDbtable('packages', 'payment')->getEnabledPackageCount() <= 0 && (empty($transaction) && $verified != 4)) {
    //   return false;
    // }

    if(empty($transaction)) {
      return false;
    }
    return true;
  }
}
