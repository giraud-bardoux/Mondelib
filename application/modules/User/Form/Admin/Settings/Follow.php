<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Follow.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Settings_Follow extends Engine_Form {

  public function init() {
    
    $this->setTitle('Follow Settings')->setDescription('These settings affect all members in your community.');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->addElement('Select', 'core_followenable', array(
      'label' => 'Enable User Follow',
      'description' => 'Do you want to enable user follow functionality on your website?',
      'onchange' => 'enableFollow(this.value)',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => $settings->getSetting('core.followenable', 1),
    ));
    
    $this->addElement('Select', 'core_allowuserverfication', array(
      'label' => 'Allow user to choose follow verification',
      'description' => 'Do you want to allow site users to choose follow verification on your website? If you choose Yes then users can select how they want to be followed themseleve, directly or with a follow request. Users can manage this setting on their privacy settings page.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'onchange' => 'allowuser(this.value);',
      'value' => $settings->getSetting('core.allowuserverfication', 0),
    ));

    $this->addElement('Select', 'core_autofollow', array(
      'label' => 'Auto Follow / Approvals',
      'description' => 'Do you want to enable auto follow / approvals functionality on your website? If you choose Yes then users can follow anyone directly without sending a follow request.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => $settings->getSetting('core.autofollow', 1),
    ));
    
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
