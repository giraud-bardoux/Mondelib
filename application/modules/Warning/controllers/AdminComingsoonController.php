<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminComingsoonController.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_AdminComingsoonController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('warning_admin_main', array(), 'warning_admin_main_comingsoon');

    $this->view->form = $form = new Warning_Form_Admin_Comingsoon_Settings();
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $comingsoonDate = $settings->getSetting('warning.comingsoondate', 0);
    if(!empty($comingsoonDate)) {
      // Convert times
      $comingsoonDate = strtotime($comingsoonDate);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($settings->getSetting('warning.comingsoontimezone'));
      $comingsoonDate = date('Y-m-d H:i:s', $comingsoonDate);
      date_default_timezone_set($oldTz);
      $form->populate(array('start_time' => $comingsoonDate));
    } else {
      $comingsoonDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' +1 day'));
      $form->populate(array('start_time' => $comingsoonDate));
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      if(!empty($values['start_time'])) {
        $values['warning_comingsoondate'] = $values['start_time'];
        unset($values['start_time']);
      }
      
      // Convert times
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($this->view->viewer()->timezone);
      $warning_comingsoondate = strtotime($values['warning_comingsoondate']);
      $values['warning_comingsoontimezone'] = $this->view->viewer()->timezone;
      date_default_timezone_set($oldTz);
      $values['warning_comingsoondate'] = date('Y-m-d H:i:s', $warning_comingsoondate);

      foreach ($values as $key => $value) {
        if ($settings->hasSetting($key, $value))
          $settings->removeSetting($key);
        if (!$value && strlen($value) == 0)
          continue;
        $settings->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }
}
