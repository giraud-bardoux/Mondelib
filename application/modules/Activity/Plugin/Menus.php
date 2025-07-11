<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Menus.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Plugin_Menus
{
  // core_main
  public function onMenuInitialize_CoreMiniUpdate($row)
  {
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    $notificationCount = Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);
    $view = Zend_Registry::get('Zend_View');
    $label = $view->translate(array('%s Update', '%s Updates', $notificationCount), $view->locale()->toNumber($notificationCount));
    return array(
      'label' => $label,
      'class' => 'updates_toggle ' . ( $notificationCount ? 'new_updates' : ''),
      'uri' => 'javascript:void(0);this.blur();',
    );
  }
  
  public function enableonthisday() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$viewer->getIdentity()){
			return false;	
		}    
    return true;
  }
}
