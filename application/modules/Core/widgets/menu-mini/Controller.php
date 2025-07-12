<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_MenuMiniController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    
    $modulesTable = Engine_Api::_()->getDbTable('modules', 'core');
    $settingsApi = Engine_Api::_()->getApi('settings', 'core');
    
    if($modulesTable->isModuleEnabled('serenity')) {
      $viewer_id = $viewer->getIdentity();
      $headerloggedinoptions = unserialize($settingsApi->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize($settingsApi->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        (empty($headerloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headerloggedinoptions)) ? $this->setNoRender() : ''));
      else 
        (empty($headernonloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headernonloggedinoptions)) ? $this->setNoRender() : ''));
    }
    
    if($modulesTable->isModuleEnabled('elpis')) {
      $viewer_id = $viewer->getIdentity();
      $headerloggedinoptions = unserialize($settingsApi->getSetting('elpis.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize($settingsApi->getSetting('elpis.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        (empty($headerloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headerloggedinoptions)) ? $this->setNoRender() : ''));
      else 
        (empty($headernonloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headernonloggedinoptions)) ? $this->setNoRender() : ''));
    }
    
    if($modulesTable->isModuleEnabled('prism')) {
      $viewer_id = $viewer->getIdentity();
      $headerloggedinoptions = unserialize($settingsApi->getSetting('prism.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize($settingsApi->getSetting('prism.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        (empty($headerloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headerloggedinoptions)) ? $this->setNoRender() : ''));
      else 
        (empty($headernonloggedinoptions) ? $this->setNoRender() : ((!engine_in_array('miniMenu', $headernonloggedinoptions)) ? $this->setNoRender() : ''));
    }
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_mini');
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->notificationOnly = $request->getParam('notificationOnly', false);
    $this->view->updateSettings = 120000;
    $this->view->showIcons = 1;
    $this->view->message_count = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
    $this->view->requestCount = Engine_Api::_()->getDbTable('notifications', 'activity')->hasFriendNotifications($viewer);
    $this->view->currencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));
    $this->view->contrast_mode = $settingsApi->getSetting('contrast.mode', 'dark_mode');
    $this->view->accessibility = $settingsApi->getSetting("accessibility.options",1);
    
    $this->view->core_minimenuquick = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_minimenuquick');
    //Languages
    $this->view->languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();
    //Location
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
      $this->view->cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
    }
  }
}
