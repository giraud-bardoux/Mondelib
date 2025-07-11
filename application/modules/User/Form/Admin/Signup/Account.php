<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Signup_Account extends Engine_Form {

  public function init() {

    // Custom
    $this->setTitle('Step 1: Create Account');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->addElement('Select', 'enabletwostep', array(
      'label' => 'Enable Two Step Authentication?',
      'description' => 'If you have selected YES, members will receive a code on their registered mail id and have to enter that code for verify their email address during signup process. If you have selected NO, then they can directly signup by filling the required form. If Facebook login is enabled, this setting is bypassed for Facebook registrations.',
      'multiOptions' => array(
        '1' => 'Yes, allow to receive code for verification',
        '0' => 'No, do not enable two step authentication',
      ),
      'value' => 0,
    ));
    
    // Element: approve
    $this->addElement('Select', 'approve', array(
      'label' => 'Auto-approve Members',
      'description' => 'USER_FORM_ADMIN_SIGNUP_APPROVE_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, enable members upon signup.',
        0 => 'No, do not enable members upon signup.'
      ),
      'value' => 1,
    ));

    $this->addElement('MultiCheckbox', 'enablesigupfields', array(
      'label' => 'Enable Signup Form Fields',
      'description' => "Choose from below the fields that you want to enable in the signup form on your website.",
      'multiOptions' => array(
        'confirmpassword' => "Confirm Password",
        //'dob' => "Birthday",
        //'gender' => "Gender",
        'profiletype' => "Profile Type",
        'timezone' => "Timezone",
        'language' => "Language",
        'terms' => "Terms of Service (Require members to agree to your terms of service? You can modifying your terms of service by editing the _CORE_TERMS_OF_SERVICE language variable in the application/languages/en/core.csv file.)",
        'location' => "Location",
      ),
      'value' => json_decode($settings->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language", "location"]')),
    ));
    
    // Element: username
    $this->addElement('Select', 'username', array(
      'label' => 'Enable Username?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_USERNAME_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, allow members to choose a username.',
        0 => 'No, do not allow username.'
      ),
      'value' => 1,
      'onchange' => "showUserName(this.value)",
    ));
    
    // Element: username
    $this->addElement('Select', 'showusername', array(
      'label' => 'Show Username as Display Name',
      'description' => 'Do you want to show the username as the display name of users instead of their first name and last name? If you choose Yes, this username will be displayed for the user everyplace the user\'s name is shown. If you choose No, the First Name and Last Name configured for their profile will display everyplace the user\'s name is shown.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => 0,
    ));

    // Element: Admin Email Notification
    $this->addElement('Select', 'adminemail', array(
      'label' => 'Notify Admin by email when user signs up?',
      'description' => 'USER_FORM_ADMIN_SIGNUP_NOTIFYEMAIL_DESCRIPTION',
      'multiOptions' => array(
        1 => 'Yes, notify admin by email.',
        0 => 'No, do not notify admin by email.',
      ),
      'onchange' => 'showHideEmail(this.value);',
      'value' => $settings->getSetting('user.signup.adminemail', 0),
    ));
    
    $this->addElement('Text', 'adminemailaddress', array(
      'label' => 'Receive New Signup Alerts',
      'description' => 'Enter the email in the box below on which you want to receive emails whenever a new signup is created on your website.',
    ));
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
    $this->populate($settings->getSetting('user_signup'));
    $this->enablesigupfields->setValue(json_decode($settings->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language", "location"]')));
  }
}
