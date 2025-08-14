<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: ErrorController.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_ErrorController extends Core_Controller_Action_Standard {

  public function indexAction() {
  
		$settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->showsearch = $this->view->showhomebutton = $this->view->showbackbutton = 1;
    $this->view->privatepagephotoID = $settings->getSetting('warning.privatepagephotoID', 0);
    $this->view->default_activate = $settings->getSetting('warning.private.pageactivate', 1);
  }

  public function viewAction() {
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$this->view->showsearch = $this->view->showbackbutton = 1;
    $this->view->pagenotfoundphotoID = $settings->getSetting('warning.pagenotfoundphotoID', 0);
    $this->view->default_activate = $settings->getSetting('warning.pagenotfound.pageactivate', 1);
  }

  public function comingsoonAction() {
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $comingsoon_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('warning.comingsoonenable', 0);
    if(empty($comingsoon_enable))
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      
    $comingsoondate = Engine_Api::_()->getApi('settings', 'core')->getSetting('warning.comingsoondate', "");
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $time = time();
    if(!$comingsoondate || $time > strtotime($comingsoondate)) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    $this->view->showcontactbutton = $settings->getSetting('warning.comingsooncontactenable', 1);
    $this->view->showsocialshare = $settings->getSetting('warning.comingsoonenablesocial', 1);
    $this->view->socialShareNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_social_sites');
    $this->view->comingsoonphotoID = $settings->getSetting('warning.comingsoonphotoID', 0);
    $this->view->site_title = $settings->getSetting('core.general.site.title', "My Community");

    $this->view->default_activate = $settings->getSetting('warning.comingsoon.pageactivate', 1);
    $this->view->logo = $settings->getSetting("warning.comingsoonlogo", '');
    $this->view->warning_comingsoondate = $settings->getSetting('warning.comingsoondate', "");
  }
}
