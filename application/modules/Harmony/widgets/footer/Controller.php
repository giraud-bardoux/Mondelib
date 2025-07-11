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
class Harmony_Widget_FooterController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $setting = Engine_Api::_()->getApi('settings', 'core');
    $menuApi = Engine_Api::_()->getApi('menus', 'core');
    
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();
    
    //Aboutus Links
    $this->view->aboutLinksMenu = $menuApi->getNavigation('harmony_aboutlinks_footer');

    //Quick Links
    $this->view->quickLinksMenu = $menuApi->getNavigation('harmony_quicklinks_footer');
    
    //Footer Links
    $this->view->navigation = $menuApi->getNavigation('core_footer');

    //Languages
    $this->view->languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();
    
    // Social Links
    $this->view->socialnavigation = $menuApi->getNavigation('core_social_sites');
    // Footer Contrast
    $this->view->accessibility_option = $setting->getSetting("accessibility_options");
    $this->view->footerlogocontrast = $setting->getSetting("harmony_footer_logocontrast");
    
    $this->view->footerbgimage = $setting->getSetting("harmony.footer.bgimage");
    $this->view->footerbgphotocontrast = $setting->getSetting("harmony.footer.bgphotocontrast");

  }
}
