<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Invite_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {
    
    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_invites', array(), 'invite_admin_settings');

    $this->view->form = $form = new Invite_Form_Admin_Settings_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      unset($values['invite_facebook']);
      if (isset($values['invite_socialmediaoptions']))
        $values['invite_socialmediaoptions'] = serialize($values['invite_socialmediaoptions']);
      else
        $values['invite_socialmediaoptions'] = serialize(array());
        
      if (isset($values['invite_allowlevels']))
        $values['invite_allowlevels'] = serialize($values['invite_allowlevels']);
        else
        $values['invite_allowlevels'] = serialize(array());

      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }
}
