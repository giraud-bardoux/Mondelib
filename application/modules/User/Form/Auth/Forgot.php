<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Forgot.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Auth_Forgot extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
    
    if(!empty($otpsms_signup_phonenumber)) {
      $label = 'Phone Number or email address';
      $NotEmpty = "Please enter a valid phone number or email address.";
    } else {
      $label = 'Email Address';
      $NotEmpty = "Please enter a valid email address.";
    }
	$description = 'If you cannot login because you have forgotten your password, please enter the details below to reset your password.';

    $this
      ->setTitle('Forgot Password')
      ->setDescription($description)
      ->setAttrib('id', 'user_form_auth_forgot')
      ;

    if(!empty($_POST['email'])) {
      if(is_numeric($_POST['email'])) {
        $validator = array(
          array('NotEmpty', true),
          array('Regex', true, array('/^[0-9][0-9]{4,15}$/')),
        );
      } else {
        $validator = array(
          array('NotEmpty', true),
          array('EmailAddress', true),
        );
      }
    } else {
      $validator = array(
        array('NotEmpty', true),
      );
    }
    
    // init email
    $this->addElement('Text', 'email', array(
      'label' => $label,
      'required' => true,
      'allowEmpty' => false,
      'validators' => $validator,
    ));
    $this->email->getValidator('NotEmpty')->setMessage($NotEmpty, 'isEmpty');

    if(!empty($_POST['email']) && !is_numeric($_POST['email']))
      $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);

    // Init submit
    $this->addElement('Button', 'core_submit_forgot', array(
      'label' => 'Send Code',
      'type' => 'submit',
      'ignore' => true,
      // 'data-bs-target' => "#user_signup_email",
      // 'data-bs-toggle' => 'modal',
      'decorators' => array(
        'ViewHelper',
      ),
    ));
    
    if(empty($_GET['restApi'])) {
      $this->addElement('Cancel', 'cancel', array(
        'label' => 'Back to Login',
        'class' => 'back_login_button',
        'link' => true,
        'prependText' => Zend_Registry::get('Zend_Translate')->_('<span class="authpage_or"> or </span> '),
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        'decorators' => array(
          'ViewHelper',
        ),
      ));
      
      $this->addDisplayGroup(array(
        'core_submit_forgot',
        'cancel'
      ), 'buttons', array(
        'decorators' => array(
          'FormElements',
          'DivDivDivWrapper',
        ),
      ));
      
      $this->addElement('Button', 'submit_signup', array(
        'label' => 'Continue',
        'type' => 'submit',
        'ignore' => true,
      ));
    }
  }
}
