<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Harmony_Widget_HeaderController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $setting = Engine_Api::_()->getApi('settings', 'core');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

    if($viewer->getIdentity()) {
      $this->view->options = unserialize($setting->getSetting("harmony.headerloggedinoptions",'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
    } else {
      $this->view->options = unserialize($setting->getSetting("harmony.headernonloggedinoptions",'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
    }
    
    $this->view->accessibility_option = $setting->getSetting("accessibility_options");
    $this->view->logo = $setting->getSetting("harmony_logo");
    $this->view->logocontrast = $setting->getSetting("harmony_logocontrast");
    
    // Main Menu
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_main');
    
    $requireCheck = $setting->getSetting('core.general.portal', 1);
    if( !$requireCheck && !$viewer->getIdentity() ) {
      $navigation->removePage($navigation->findOneBy('route', 'user_general'));
    }

    $this->view->menuCount = $setting->getSetting('menu.count', 5);
    $require_check = Engine_Api::_()->getApi('settings', 'core')->core_general_portal;
    if(!$require_check) {
      if( $viewer->getIdentity()) 
        $this->view->search_check = true;
      else 
        $this->view->search_check = false;
    }
    else $this->view->search_check = true;
  }
}
