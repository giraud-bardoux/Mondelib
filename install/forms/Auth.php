<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Auth.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Install_Form_Auth extends Engine_Form
{
  public function init()
  {
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('Password', 'password', array(
      'label' => 'Password',
      'required' => true,
      'allowEmpty' => false,
    ));
  
		//Work For Show and Hide Password
    $this->addElement('dummy', 'showhidepassword', array(
      'content' => '<div class="user_showhidepassword"><i class="fa fa-eye" id="togglePassword"></i></div>',
    ));
		//Work For Show and Hide Password

    // Submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Login',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
