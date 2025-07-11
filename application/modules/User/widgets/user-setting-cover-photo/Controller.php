<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Widget_UserSettingCoverPhotoController extends Engine_Content_Widget_Abstract{
  
  public function indexAction() {
  
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('user');

    //Payment information show
    // Check if they are an admin or moderator (don't require subscriptions from them)
    $this->view->showPaymentInfo = false;
    $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $subject->level_id);
    if(engine_in_array($level->type, array('admin', 'moderator')) ) {
      $this->view->showPaymentInfo = true;
    }
    $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
    $currentSubscription = array();
    // Get current subscription and package
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    $this->view->currentSubscription = $currentSubscription = $subscriptionsTable->fetchRow(array(
      'user_id = ?' => $subject->getIdentity(),
      'active = ?' => true,
    ));
    if( $currentSubscription ) {
      $gateway = Engine_Api::_()->getDbtable('gateways', 'payment')->find($currentSubscription->gateway_id)->current();
      $this->view->isGatewayEnabled = 0;
      if($gateway){
        if($gateway->enabled)
          $this->view->isGatewayEnabled = 1;
      }
      // Get current package
      $this->view->currentPackage = $currentPackage = $packagesTable->fetchRow(array(
          'package_id = ?' => $currentSubscription->package_id,
      ));
    }
  }
}
