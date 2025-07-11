<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Reset.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Auth_Reset extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Reset password?');

    // init password
    $this->addElement('Password', 'password', array(
      'label' => 'New password',
      'description' => !empty($_GET['restApi']) ? "Enter your password" : '',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(6, 32)),
        array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),
      ),
      'tabindex' => 1,
    ));
    $this->password->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    $this->password->getValidator('Regex')->setMessage('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.');
    $this->password->getValidator('NotEmpty')->setMessage('Please enter a valid password.', 'isEmpty');

    $regexCheck = new Engine_Validate_Callback(array($this, 'regexCheck'), $this->password);
    $regexCheck->setMessage("Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.");
    $this->password->addValidator($regexCheck);
    
    if(empty($_GET['restApi'])) {
      //Work For Show and Hide Password
      $this->addElement('dummy', 'showhidepassword', array(
        'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/User/views/scripts/_showhidepassword.tpl',
        ))),
        
      ));
      //Work For Show and Hide Password
      $this->addDisplayGroup(array('password', 'showhidepassword'), 'password_settings_group');
    }
    
    // init passconf
    $this->addElement('Password', 'passconf', array(
      'label' => 'Confirm new password',
      //'description' => 'Enter your password again for confirmation.',
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => 2,
    ));
    //$this->passconf->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    
    if(empty($_GET['restApi'])) {
      //Work For Show and Hide Password
      $this->addElement('dummy', 'showhideconfirmpassword', array(
        'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/User/views/scripts/_showhideconfirmpassword.tpl',
        ))),
        
      ));
      //Work For Show and Hide Password
      $this->addDisplayGroup(array('passconf', 'showhideconfirmpassword'), 'password_confirm_settings_group');
    }
    
    $this->addElement('Checkbox', 'resetalldevice', array(
      'label' => 'Do you want to logout from all other devices.',
    ));
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Reset Password',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => 3,
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'class' => 'back_login_button' ,
      'link' => true,
      'prependText' => Zend_Registry::get('Zend_Translate')->_(' <span class="authpage_or"> or </span> '),
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("module" => "user", 'controller' => "auth", "action" => "forgot"), 'default', true),
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array(
      'submit',
      'cancel'
    ), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      ),
    ));
  }
  public function regexCheck($value)
  {
    if(preg_match("/([\\\\:\/])/", $value))
    {
      return false;
    }
    return true;
  }
}
