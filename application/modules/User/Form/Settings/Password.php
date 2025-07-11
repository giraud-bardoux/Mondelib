<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Password.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Password extends Engine_Form
{
  public function init()
  {
    // @todo fix form CSS/decorators
    // @todo replace fake values with real values
    $this->setTitle('Change Password')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setAttrib('class', 'global_form form_submit_ajax')
      ;

    // Init old password
    $this->addElement('Password', 'oldPassword', array(
      'label' => 'Old password',
      //'description' => !empty($_GET['restApi']) ? "Enter your password." : "",
      'required' => true,
      'allowEmpty' => false,
    ));
    
    //Work For Show and Hide Password
    $this->addElement('dummy', 'showhideoldpassword', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/User/views/scripts/_showhideoldpassword.tpl',
      ))),
    ));
		$this->addDisplayGroup(array('oldPassword', 'showhideoldpassword'), 'oldpassword_settings_group');

    // Init password
    $this->addElement('Password', 'password', array(
      'label' => 'New password',
      'description' => !empty($_GET['restApi']) ? "Enter your password." : '',
      //'description' => 'Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('stringLength', false, array(6, 32)),
          array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),
    )));
    //$this->password->getDecorator('Description')->setOption('placement', 'APPEND');
    $this->password->getValidator('Regex')->setMessage('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.');

    $regexCheck = new Engine_Validate_Callback(array($this, 'regexCheck'), $this->password);
    $regexCheck->setMessage("Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.");
    $this->password->addValidator($regexCheck);
    $translate = Zend_Registry::get('Zend_Translate');
    
    //Work For Show and Hide Password
    $this->addElement('dummy', 'showhidepassword', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/User/views/scripts/_showhidepassword.tpl',
      ))),
    ));
    $this->addDisplayGroup(array('password', 'showhidepassword'), 'password_settings_group');

    // Init password confirm
    $this->addElement('Password', 'passwordConfirm', array(
      'label' => 'New password (again)',
      'description' => 'Enter your password again for confirmation.',
      'required' => true,
      'allowEmpty' => false
    ));
    $this->passwordConfirm->getDecorator('Description')->setOption('placement', 'APPEND');
    
    //Work For Show and Hide Password
    $this->addElement('dummy', 'showhideconfirmpassword', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/User/views/scripts/_showhideconfirmpassword.tpl',
      ))),
    ));
    $this->addDisplayGroup(array('passwordConfirm', 'showhideconfirmpassword'), 'password_confirm_settings_group');
		
    $this->addElement('Hidden','require_password',array('order'=>999,'value'=>0));

    $this->addElement('Checkbox', 'resetalldevice', array(
      'label' => 'Do you want to logout from all other devices.',
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Change Password',
      'type' => 'submit',
      'ignore' => true
    ));

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
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
