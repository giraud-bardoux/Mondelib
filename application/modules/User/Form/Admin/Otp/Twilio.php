<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Twilio.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Otp_Twilio extends Engine_Form {

  public function init() {
  
    parent::init();

    $this->setTitle('Integration with Twilio')
        ->setDescription('Fill up the form below to integrate twilio services to enable OTP on your site.');

    $this->addElement('Text','clientId',array(
      'label'=>'Account SID',
      'description'=> 'Enter the Account SID below.',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    
    $this->addElement('Text','clientSecret',array(
      'label'=>'Auth Token',
      'description'=> 'Enter the Auth Token below.',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    
    $this->addElement('Text','phoneNumber',array(
      'label'=>'Phone Number',
      'description' => 'Enter the phone number that you have purchased from Twilio. [Note: The number should start with country code & should not have any space or any other special character anywhere in the number. For Example: +3456XXXXXXX.]',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    
    $this->addElement('Select','enabled',array(
      'label'=>'Enable',
      'description'=> 'Do you want to enable Twilio Services?',
      'multiOptions'=>array('1'=>'Yes','0'=>'No'),
      'required'=>true,
      'allowEmpty'=>false,
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.integration') ? 1 : 0,
    ));
    
    $this->addElement('Button','submit',array(
      'label'=>'Save Changes',
      'type'=>'submit',
      'ignore'=>true
    ));
  }
}
