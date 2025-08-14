<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminPagenotfoundController.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_AdminPagenotfoundController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('warning_admin_main', array(), 'warning_admin_main_pagenotfound');

    $this->view->form = $form = new Warning_Form_Admin_Pagenotfound_Settings();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {

      $values = $form->getValues();
      foreach ($values as $key => $value) {
        if (Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
            Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
        if (!$value && strlen($value) == 0)
            continue;
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      
      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }
}
