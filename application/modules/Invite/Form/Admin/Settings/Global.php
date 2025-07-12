<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Invite_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setDescription('These settings affect all members in your community.');

    $this->addElement('Select', 'invite_enable', array(
      'label' => 'Enable Invite Feature',
      'description' => "Do you want to enable the invite feature on your website? If you choose 'No,' then no one can invite other members on your website.",
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'onchange' => "enableSignup(this.value);",
      'value' => $settings->getSetting('invite.enable', 1),
    ));
    
    $member_levels = array();
    $public_level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
    foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $row) {
      if ($public_level->level_id != $row->level_id) {
        $member_count = $row->getMembershipCount();
        if (null !== ($translate = $this->getTranslator())) {
          $title = $translate->translate($row->title);
        } else {
          $title = $row->title;
        }
        $member_levels[$row->level_id] = $title;
      }
    }

    $levels = $settings->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
    $levelsvalue = unserialize($levels);

    $this->addElement('Multiselect', 'invite_allowlevels', array(
      'label' => 'Member Levels',
      'description' => 'Select member level you want to enable "Invite Feature" on your website. Hold down the CTRL key to select or de-select specific Member Levels.',
      'required' => false,
      'allowEmpty' => true,
      'multiOptions' => $member_levels,
      'value' => $levelsvalue,
    ));
    
    $this->addElement('Radio', 'invite_signupenable', array(
      'label' => 'Enable Invite Code For Signup',
      'description' => "Would you like to activate the invite code feature during the website signup process?",
      'multiOptions' => array(
        2 => 'Yes, enable invite code and make it optional.',
        1 => 'Yes, enable invite code and make it mandatory.',
        0 => 'No, do not enable the invite code during website signup.',
      ),
      'value' => $settings->getSetting('invite.signupenable', 0),
    ));

    $this->addElement('MultiCheckbox', 'invite_socialmediaoptions', array(
      'label' => 'Enable External Invite Options',
      'description' => 'Select from the external site options below, where you like your site members to invite their friends to this website.',
      'multiOptions' => array(
        'facebook' => 'Facebook',
        'csv' => 'CSV',
        'emailinvite' => 'Direct Email',
      ),
      'value' => unserialize($settings->getSetting('invite.socialmediaoptions', '')),
    ));
    
    //Facebook api key
    $fbCallback = 'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'default', true);
    
    $translate = Zend_Registry::get('Zend_Translate');
    
    $description = $translate->translate('To enable the invite feature through Facebook, you will need to create an application via the <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers</a> page. Additionally, please utilize the provided Callback URL when creating an application on Facebook. Callback URL: ');

    $this->addElement('Dummy', 'invite_facebook', array(
      'label' => 'Facebook',
      'content' => $description . $fbCallback,
    ));

    $this->addElement('Text', 'invite_facebookclientid', array(
      'label' => 'Facebook App ID',
      'description' => '',
      'value' => $settings->getSetting('invite.facebookclientid', ''),
    ));
    $this->addElement('Text', 'invite_facebookclientsecret', array(
      'label' => 'Facebook App Secret',
      'description' => '',
      'value' => $settings->getSetting('invite.facebookclientsecret', ''),
    ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
