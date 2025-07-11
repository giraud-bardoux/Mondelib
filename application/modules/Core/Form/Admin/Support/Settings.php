<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Location.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Form_Admin_Support_Settings extends Engine_Form {

  public function init() {

    $this->setTitle('Global Settings')
          ->setDescription('These settings affect all members in your community.');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Select', "core_enablesupport", array(
      'label' => 'Enable Support Inbox',
      'description' => 'Do you want to enable the support inbox feature on your website?',
      'allowEmpty' => true,
      'required' => false,
      'multiOptions'=> array(
        1 => 'Yes',
        0 => 'No'
      ),
      'onchange' => 'enablesupport(this.value);',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.enablesupport', 1),
    ));

    $this->addElement('Select', 'core_supportcreate', array(
      'label' => 'Allow Creation of Support Tickets',
      'description' => 'Do you want to allow users of your site to create support tickets? If you choose No, then users will not be able to create support ticket on your website, meanwhile they can view and reply to the tickets are opened from admin panel.',
      'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.supportcreate', 1),
    ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
