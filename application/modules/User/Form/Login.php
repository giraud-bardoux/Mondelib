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
class User_Form_Login extends Engine_Form
{
  protected $_mode;

  public function setMode($mode)
  {
    $this->_mode = $mode;
    return $this;
  }

  public function getMode()
  {
    if( null === $this->_mode ) {
      $this->_mode = 'page';
    }
    return $this->_mode;
  }

  public function init()
  {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    // Used to redirect users to the correct page after login with Facebook
    $_SESSION['redirectURL'] = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
		
		if(isset($_GET['format']) && $_GET['format'] == 'smoothbox') {
			$description = Zend_Registry::get('Zend_Translate')->_("If you already have an account, please enter your details below. If you don't have one yet, please <a href='%s' target='_blank'>sign up</a> first.");
    } else {
			$description = Zend_Registry::get('Zend_Translate')->_("If you already have an account, please enter your details below. If you don't have one yet, please <a href='%s'>sign up</a> first.");
    }
    $description= sprintf($description, Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));

    // Init form
    $this->setTitle('Enter Details to Login');
    $this->setDescription($description);
    $this->setAttrib('id', 'user_form_login');
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $otpsms_signup_phonenumber = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0);
    if(!empty($otpsms_signup_phonenumber) && $settings->getSetting('user.signup.username', 1)) {
      $email = Zend_Registry::get('Zend_Translate')->_('Email address, username, or phone number');
    } 
    else if($settings->getSetting('user.signup.username', 1)) {
      $email = Zend_Registry::get('Zend_Translate')->_('Email address or username');
    } 
    else if(!empty($otpsms_signup_phonenumber)) {
      $email = Zend_Registry::get('Zend_Translate')->_('Email address or phone number');
    }
    else {
      $email = Zend_Registry::get('Zend_Translate')->_('Email address');
    }

    // Init password
    $this->addElement('Text', 'email', array(
      'label' => $email,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'autofocus' => 'autofocus',
      'class' => 'text',
    ));

    $password = Zend_Registry::get('Zend_Translate')->_('Password');
    // Init password
    $this->addElement('Password', 'password', array(
      'label' => $password,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    $this->addElement('Hidden', 'return_url', array(

    ));
		
		if(empty($_GET['restApi'])) {
      //Work For Show and Hide Password
      $this->addElement('dummy', 'showhidepassword', array(
        'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/User/views/scripts/_showhidepassword.tpl',
        ))),
        
      ));
      //Work For Show and Hide Password
		}
		
    $otpsms_login_options = $settings->getSetting('otpsms_login_options',0);
    if($otpsms_signup_phonenumber && $otpsms_login_options == 1) {
      $this->addElement('Button', 'login_via_otp', array(
        'label' => 'Sign In with OTP',
        'onClick' => 'sendotpCode();',
        'order' => 2,
        'decorators' => array(array('ViewScript', array(
          'viewScript' => 'application/modules/User/views/scripts/_otpLogin.tpl',
          'class' => 'form element',
          'emailFieldName' => 'email',
        )))
      ));

      $this->addDisplayGroup(array(
        'password',
        'showhidepassword',
        'login_via_otp'
        ), 'password_buttons', array(
        'order' => 1,
      ));
    } else if(empty($_GET['restApi'])) {
      $this->addDisplayGroup(array(
        'password',
        'showhidepassword',
        ), 'password_buttons', array(
        'order' => 1,
      ));
    }

    if( $settings->core_spam_signup && empty($_GET['restApi'])) {
      $this->addElement('captcha', 'captcha', Engine_Api::_()->core()->getCaptchaOptions(array(
        
        'size' => ($this->getMode() == 'column') ? 'compact' : 'normal',
      )));
    }
    
    if(!empty($_GET['restApi'])) {
      if(isset($_GET['format']) && $_GET['format'] == 'smoothbox') {
        $content = Zend_Registry::get('Zend_Translate')->_("<span><a href='%s' target='_blank'>Forgot Password?</a></span>");
      } else {
        $content = Zend_Registry::get('Zend_Translate')->_("<span><a href='%s'>Forgot Password?</a></span>");
      }
      $content= sprintf($content, Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'forgot'), 'default', true));

      // Init forgot password link
      $this->addElement('Dummy', 'forgot', array(
        'label' => "Forgot Password?",
        'content' => $content,
      ));
    }

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Sign In',
      'type' => 'submit',
      'ignore' => true,
    ));
    
    if(empty($_GET['restApi'])) {
      if(isset($_GET['format']) && $_GET['format'] == 'smoothbox') {
        $content = Zend_Registry::get('Zend_Translate')->_("<span><a href='%s' target='_blank'>Forgot Password?</a></span>");
      } else {
        $content = Zend_Registry::get('Zend_Translate')->_("<span><a href='%s'>Forgot Password?</a></span>");
      }
      $content= sprintf($content, Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'forgot'), 'default', true));

      // Init forgot password link
      $this->addElement('Dummy', 'forgot', array(
        'content' => $content,
      ));
    }

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Sign In',
      'type' => 'submit',
      'ignore' => true,
      
    ));

    // Init facebook login link
    if($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret) {
      $this->addElement('Dummy', 'facebook', array(
        'content' => User_Model_DbTable_Facebook::loginButton(),
      ));
    }

    // Init twitter login link
    if($settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret) {
      $this->addElement('Dummy', 'twitter', array(
        'content' => User_Model_DbTable_Twitter::loginButton(),
      ));
    }
    
    // Init google login link
    if($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) {
      $this->addElement('Dummy', 'google', array(
        'content' => User_Model_DbTable_Google::loginButton(),
      ));
    }
    
    // Init linkedin login link
    if($settings->core_linkedin_enable == 'login' && $settings->core_linkedin_access && $settings->core_linkedin_secret) {
    
      $this->addElement('Dummy', 'linkedin', array(
        'content' => User_Model_DbTable_Linkedin::loginButton(),
      ));
    }
    
      // Init google login link
      if($settings->telegram_enable == 1 && $settings->telegram_username && $settings->telegram_token) {
        $this->addElement('Dummy', 'telegram', array(
          'content' => User_Model_DbTable_Telegram::loginButton(),
        ));
      }

    if(($settings->telegram_enable == 1 && $settings->telegram_username && $settings->telegram_token) || ($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret) || ($settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret)  || ($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) || ($settings->core_linkedin_enable == 'login' && $settings->core_linkedin_access && $settings->core_linkedin_secret)) {
      $this->addDisplayGroup(array('facebook', 'twitter', 'google', 'linkedin','telegram'), 'sociallinks');
    }
    
    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login'));
  }
}
