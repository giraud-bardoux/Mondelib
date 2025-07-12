<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Facebook.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Otp_Global extends Engine_Form {
  
  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setDescription('These settings affect all members in your community.');

    $this->addElement('Select','otpsms_signup_phonenumber',array(
      'label' => 'Display Phone Number During Signup / Login / Forgot Password',
      'description' => 'Do you want to display the phone number field on your website? If you choose Yes, then users on your website will see phone number field on your site.',
      'allowEmpty'=>false,
      'required' => true,
      'value'=>$settings->getSetting('otpsms.signup.phonenumber', 0),
      'multiOptions'=>array(
        '1'=>'Yes',
        '0'=>'No'
      ),
      'onchange' => 'hideShow(this.value);',
    ));

    $this->addElement('Select','otpsms_login_options',array(
      'label' => 'Login Security Check',
      'description' => 'Select the check that you want to secure login on your website.',
      'allowEmpty'=>false,
      'required' => true,
      'value'=>$settings->getSetting('otpsms.login.options',0),
      'multiOptions'=>array(
        '0'=>'Default (Only Password)',
        '1'=>'Either with OTP or Password',
        '2'=>'Two Factor Login Verification (Both OTP & Password)'
      ),
    ));

    //Test Details
    $this->addElement('Text', 'otpsms_test_mobilenumber', array(
      'label' => 'Test User for Login via OTP',
      'description' => 'Select user from below auto suggest box which you want to make test user to login via OTP on your site. Note: Only those users will show here who have entered phone numbers in their account.',
      'allowEmpty' => true,
      'required' => false,
      'value'=>$settings->getSetting('otpsms.test.mobilenumber'),
    ));
    
    $this->addElement('Text', 'otpsms_test_code', array(
      'label' => 'Test OTP Code',
      'description' => 'Enter the 6 digit OTP which will be used by the test user to login via OTP on your site.',
      'maxlength' => '6',
      'allowEmpty' => true,
      'required' => false,
      'validators' => array(
        array('Int', true),
      ),
      'value'=>$settings->getSetting('otpsms.test.code'),
    ));

    $this->addElement('Hidden','otpsms_test_user_id');

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
