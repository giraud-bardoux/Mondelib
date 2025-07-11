<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Message91.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Otp_Message91 extends Engine_Form {

  public function init()
  {
    parent::init();

    $this->setTitle('Integration with MSG91')
        ->setDescription('Fill up the form below to integrate MSG91 services to enable OTP on your site.');

    $this->addElement('Text','clientId',array(
      'label'=>'Authorization Key',
      'description'=> 'Enter the Authorization Key below.',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    $this->addElement('Text','clientSecret',array(
      'label'=>'Template ID',
      'description'=> 'Enter the Template ID below.',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    
    $this->addElement('Text','senderId',array(
      'label'=>'Sender Id',
      'description' => 'Enter the Sender ID below.',
      'required'=>true,
      'allowEmpty'=>false,
    ));
    
    $this->addElement('Select','enabled',array(
      'label'=>'Enable',
      'description'=> 'Do you want to enable MSG91 Services?',
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
