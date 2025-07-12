<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Otpsms extends Engine_Form {

  public function init() {
  
    parent::init();

    $this->setAttrib('id', 'otpsms_signup_verify')
        ->setTitle('Validate OTP (One Time Password)');

    // init password
    $this->addElement('Text', 'code', array(
      'label' => 'OTP',
      'placeholder' => Zend_Registry::get('Zend_Translate')->_('Enter OTP'),
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
      ),
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Verify',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array(
        'ViewHelper',
      ),
    ));
    
    $this->addElement('Hidden','email_data',array('order'=>'886'));
    $this->addElement('Hidden','email',array('order'=>'887'));
    $this->addElement('Hidden','country_code',array('order'=>'888'));
  }
}
