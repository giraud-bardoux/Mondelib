<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Warning
 * @package    Warning
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id AdminPrivateController.php 2017-05-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Warning_AdminPrivateController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('warning_admin_main', array(), 'warning_admin_main_private');

    $this->view->form = $form = new Warning_Form_Admin_Private_Settings();

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
