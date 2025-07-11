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
class User_Form_Signup_Account extends Engine_Form_Email
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $enablesigupfields = (array) json_decode($settings->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language","location"]'));
    
    $translate = Zend_Registry::get('Zend_Translate');
    
    $this->_emailAntispamEnabled = 0 &&
      empty($_SESSION['facebook_signup']) &&
      empty($_SESSION['twitter_signup']) &&
      empty($_SESSION['google_signup']) &&
      empty($_SESSION['telegram_signup']) &&
      empty($_SESSION['linkedin_signup']);

    $inviteSession = new Zend_Session_Namespace('invite');
    $tabIndex = 1;

    // Init form
    $this->setTitle('Create Account');
    $this->setAttrib('id', 'signup_account_form');
    
    
    // Element: profile_type
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();

      $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
      
      $options = $profileTypeField->getElementParams('user');

      unset($options['options']['order']);
      unset($options['options']['multiOptions']['']);
      if($options['type'] == 'ProfileType') {
        unset($options['options']['multiOptions']['5']);
        unset($options['options']['multiOptions']['9']);
      }
      if(isset($enablesigupfields) && engine_in_array('profiletype', $enablesigupfields) && engine_count($options['options']['multiOptions']) > 1 ) { 
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        unset($options['options']['multiOptions']['']);
        $this->addElement('Radio', 'profile_type', array_merge($options['options'], array(
          'required' => true,
          'allowEmpty' => false,
        )));
      } else if( engine_count($options['options']['multiOptions']) == 1 ) {
        $this->addElement('Hidden', 'profile_type', array(
          'label' => "Profile Type",
          'required' => true,
          'allowEmpty' => false,
          'value' => $optionsIds[0]->option_id,
          'order' => 1001
        ));
      }
    }

    if(empty($_GET['restApi'])) {
      // Element: name (trap)
      $this->addElement('Text', 'name', array(
        'class' => 'signup-name',
        'label' => 'Name',
        'validators' => array(
          array('StringLength', true, array('max' => 0)))));

      $this->name->getValidator('StringLength')->setMessage('An error has occured, please try again later.');
    }
    
    $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);

    if(!empty($otpsms_signup_phonenumber)) {
      $label = 'Phone Number or email address';
      $description = 'You will use your phone number or email address to login.';
      $Db_NoRecordExists = 'Someone has already registered this email address / phone number, please use another one.';
      $NotEmpty = "Please enter a valid phone number or email address.";
    } else {
      $label = 'Email Address';
      $description = 'You will use your email address to login.';
      $Db_NoRecordExists = 'Someone has already registered this email address, please use another one.';
      $NotEmpty = "Please enter a valid email address.";
    }
    
    if(!empty($_POST['email'])) {
      if(is_numeric($_POST['email']) && !empty($otpsms_signup_phonenumber)) {
        $validator = array(
          array('NotEmpty', true),
          array('Regex', true, array('/^[0-9][0-9]{4,15}$/')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'phone_number'))
        );
      } else {
        $validator = array(
          array('NotEmpty', true),
          array('EmailAddress', true),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'email'))
        );
      }
    } else {
      $validator = array(
        array('NotEmpty', true),
      );
    }
    
    $this->addElement('Text', 'email',array(
      'label' => $label,
      //'description' => $description,
      'required' => true,
      'allowEmpty' => false,
      'validators' => $validator,
      'filters' => array(
        'StringTrim'
      ),
    ));
    $this->email->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    $this->email->getValidator('NotEmpty')->setMessage($NotEmpty, 'isEmpty');
    if(!empty($_POST['email'])) {
      $this->email->getValidator('Db_NoRecordExists')->setMessage($Db_NoRecordExists, 'recordFound');
      if(!is_numeric($_POST['email']))
      $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    }
    
    // Add banned email validator
    $bannedEmailValidator = new Engine_Validate_Callback(array($this, 'checkBannedEmail'), $this);
    $bannedEmailValidator->setMessage("This email address is not available, please use another one.");
    $this->email->addValidator($bannedEmailValidator);
    
    if( !empty($inviteSession->invite_email) ) {
      $this->email->setValue($inviteSession->invite_email);
    }

    // Element: code
    if( $settings->getSetting('invite.enable', 1) &&  $settings->getSetting('invite.signupenable', 0)) {
      $codeValidator = new Engine_Validate_Callback(array($this, 'checkInviteCode'), $this->email);
      $codeValidator->setMessage("This invite code is invalid or does not match the selected email address");
      $this->addElement('Text', 'code', array(
        'label' => $settings->getSetting('invite.signupenable', 0) == 2 ? 'Invite Code (Optional)' : "Invite Code",
        'required' => $settings->getSetting('invite.signupenable', 0) == 1 ? true : false,
      ));
      $this->code->addValidator($codeValidator);

      if( !empty($inviteSession->invite_code) ) {
        $this->code->setValue($inviteSession->invite_code);
      }
      
      $invitereferralSession = new Zend_Session_Namespace('invite_referral_signup');
      if(!empty($invitereferralSession->referral_code))
        $this->code->setValue($invitereferralSession->referral_code);
    }

    if((empty($_SESSION['twitter_signup']) || empty($_SESSION['google_signup']) || empty($_SESSION['telegram_signup']) || empty($_SESSION['linkedin_signup']))) {

      // Element: password
      $this->addElement('Password', 'password', array(
        'label' => 'Password',
        'description' => !empty($_GET['restApi']) ? 'Enter your password.' : '',
        'required' => true,
        'allowEmpty' => false,
        'id' => 'signup_password',
        'validators' => array(
          array('NotEmpty', true),
          array('StringLength', false, array(6, 32)),
          array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),
        ),
      ));
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
      
      if(isset($enablesigupfields) && engine_in_array('confirmpassword', $enablesigupfields)) {
        // Element: passconf
        $this->addElement('Password', 'passconf', array(
          'label' => 'Password Again',
          'description' => 'Enter your password again for confirmation.',
          'required' => true,
          'validators' => array(
            array('NotEmpty', true),
          ),
        ));
        $this->passconf->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
        $this->passconf->getValidator('NotEmpty')->setMessage('Please make sure the "password" and "password again" fields match.', 'isEmpty');

        $specialValidator = new Engine_Validate_Callback(array($this, 'checkPasswordConfirm'), $this->password);
        $specialValidator->setMessage('Password did not match', 'invalid');
        $this->passconf->addValidator($specialValidator);
        
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
			}
    }

    // Element: username
    if( $settings->getSetting('user.signup.username', 1) > 0 ) {
      $this->addElement('Text', 'username', array(
        'label' => 'Username',
        'description' => "This will be the end of your username, for example: ",
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('Alnum', true),
          array('StringLength', true, array(4, 64)),
          array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'username'))
        ),
      ));
      $this->username->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid username.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this username, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Username must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Username must be alphanumeric.', 'notAlnum');

      // Add banned username validator
      $bannedUsernameValidator = new Engine_Validate_Callback(array($this, 'checkBannedUsername'), $this->username);
      $bannedUsernameValidator->setMessage("This username is not available, please use another one.");
      $this->username->addValidator($bannedUsernameValidator);
    }

    if(isset($enablesigupfields) && engine_in_array('location', $enablesigupfields) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
      //Location
      $this->addElement('Text', 'location', array(
        'label' => 'Location',
        'filters' => array(
          new Engine_Filter_Censor(),
          new Engine_Filter_HtmlSpecialChars(),
        ),
      ));
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
        $this->addElement('Hidden', 'lat', array('order' => 3000, 'value' => ''));
        $this->addElement('Hidden', 'lng', array('order' => 3001, 'value' => ''));
        $this->addElement('Hidden', 'city', array('order' => 3002, 'value' => ''));
        $this->addElement('Hidden', 'state', array('order' => 3003, 'value' => ''));
        $this->addElement('Hidden', 'country', array('order' => 3004, 'value' => ''));
        $this->addElement('Hidden', 'zip', array('order' => 3005, 'value' => ''));
      }
    }
    
    if(isset($enablesigupfields) && engine_in_array('timezone', $enablesigupfields)) {
      // Element: timezone
      $this->addElement('Select', 'timezone', array(
        'label' => 'Timezone',
        'required' => true,
        'allowEmpty' => false,
        'value' => $settings->getSetting('core.locale.timezone'),
        'multiOptions' => Engine_Api::_()->core()->timeZone(),
      ));
      $this->timezone->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    } else {
      $this->addElement('Hidden', 'timezone', array(
        'label' => 'Timezone',
        'required' => true,
        'allowEmpty' => false,
        'value' => $settings->getSetting('core.locale.timezone'),
        'order' => 1999
      ));
    }
    
    // Element: language
    $languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();
    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if ($defaultLanguage == 'auto') {
      $defaultLanguage = 'en';
    }

    if(isset($enablesigupfields) && engine_in_array('language', $enablesigupfields) && engine_count($languageNameList) > 1) {
      $this->addElement('Select', 'language', array(
        'label' => 'Language',
        'multiOptions' => $languageNameList,
        'value' => $defaultLanguage,
      ));
      $this->language->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    } else {
      $this->addElement('Hidden', 'language', array(
        'value' => $defaultLanguage,
        'order' => 1002
      ));
    }

    // Element: captcha
    if( $settings->core_spam_signup && empty($_GET['restApi'])) {
      $this->addElement('captcha', 'captcha', Engine_Api::_()->core()->getCaptchaOptions(array(
        
      )));
    }

    if(isset($enablesigupfields) && engine_in_array('terms', $enablesigupfields)) {
      // Element: terms
      $description = Zend_Registry::get('Zend_Translate')->_('I have read and agree to the <a target="_blank" href="%s/help/terms">terms of service</a>.');
      $description = sprintf($description, Zend_Controller_Front::getInstance()->getBaseUrl());
      
      if(empty($_GET['restApi'])) {
        $label = 'Terms of Service';
      } else {
        $label = Zend_Registry::get('Zend_Translate')->_('I have read and agree to the terms of service.');
      }
      $this->addElement('Checkbox', 'terms', array(
        'label' => $label,
        'description' => $description,
        'required' => true,
        'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
        ),
      ));
      $this->terms->getValidator('GreaterThan')->setMessage('You must agree to the terms of service to continue.', 'notGreaterThan');
      $this->terms->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::APPEND, 'tag' => 'label', 'class' => 'null', 'escape' => false, 'for' => 'terms'))
          ->addDecorator('DivDivDivWrapper');
    } else {
      // Element: terms
      $description = Zend_Registry::get('Zend_Translate')->_('I have read and agree to the <a target="_blank" href="%s/help/terms">terms of service</a>.');
      $description = sprintf($description, Zend_Controller_Front::getInstance()->getBaseUrl());
      
      $this->addElement('Dummy', 'terms', array(
        'label' => 'Terms of Service',
        'description' => $description,
      ));
      $this->terms->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::APPEND, 'tag' => 'label', 'class' => 'null', 'escape' => false, 'for' => 'terms'))
          ->addDecorator('DivDivDivWrapper');
    }
    
    $this->addElement('Hidden', 'countrycode', array(
      'value' => '',
      'order' => 1020
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Sign Up',
      'type' => 'submit',
      'ignore' => true,
    ));
    
    if(empty($_GET['restApi'])) {
      $this->addElement('Button', 'submit_signup', array(
        'label' => 'Sign Up',
        'type' => 'submit',
        'ignore' => true,
      ));
    }

    if( empty($_SESSION['facebook_signup']) ){
      // Init facebook login link
      if($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret ) {
        $this->addElement('Dummy', 'facebook', array(
          'content' => User_Model_DbTable_Facebook::loginButton(),
          'ignore' => true,
        ));
      }
    }

    if( empty($_SESSION['twitter_signup']) ){
      // Init twitter login link
      if( $settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret ) {
        $this->addElement('Dummy', 'twitter', array(
          'content' => User_Model_DbTable_Twitter::loginButton(),
          'ignore' => true,
        ));
      }
    }
    
    if( empty($_SESSION['google_signup']) ) {
      // Init google login link
      if($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) {
        $this->addElement('Dummy', 'google', array(
          'content' => User_Model_DbTable_Google::loginButton(),
        ));
      }
    }

    if( empty($_SESSION['telegram_signup']) ) {
      // Init google login link
      if($settings->telegram_enable == 1 && $settings->telegram_username && $settings->telegram_token) {
        $this->addElement('Dummy', 'telegram', array(
          'content' => User_Model_DbTable_Telegram::loginButton(),
        ));
      }
    }
    
    if( empty($_SESSION['linkedin_signup']) ) {
      // Init linkedin login link
      if($settings->core_linkedin_enable == 'login' && $settings->core_linkedin_access && $settings->core_linkedin_secret) {
        $this->addElement('Dummy', 'linkedin', array(
          'content' => User_Model_DbTable_Linkedin::loginButton(),
        ));
      }
    }

    if( empty($_SESSION['facebook_signup']) || empty($_SESSION['twitter_signup'])  || empty($_SESSION['google_signup']) || empty($_SESSION['linkedin_signup']) || empty($_SESSION['telegram_signup'])) {
      if(($settings->telegram_enable == 1 && $settings->telegram_username && $settings->telegram_token) || ($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret) || ($settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret) || ($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) || ($settings->core_linkedin_enable == 'login' && $settings->core_linkedin_access && $settings->core_linkedin_secret)) {
        $this->addDisplayGroup(array('facebook', 'twitter', 'google', 'linkedin', 'telegram'), 'sociallinks');
      }
    }
    
    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));
  }

  public function checkPasswordConfirm($value, $passwordElement)
  {
    return ( $value == $passwordElement->getValue() );
  }

  public function checkInviteCode($value, $emailElement)
  {
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $select = $inviteTable->select()
      ->from($inviteTable->info('name'), 'COUNT(*)')
      ->where('code = ?', $value)
      ;

    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.checkemail') ) {
      $select->where('recipient LIKE ?', $emailElement->getValue());
    }
    $checkInviteCode = (bool) $select->query()->fetchColumn(0);
    if(empty($checkInviteCode)) {
      $referral = Engine_Api::_()->getDbTable('users', 'user')->getUserExist('', $value);
      if($referral->user_id)
        return true;
    }
    return (bool) $select->query()->fetchColumn(0);
  }
  public function regexCheck($value)
  {
    if(preg_match("/([\\\\:\/])/", $value))
    {
        return false;
    }
    return true;
  }
  public function checkBannedEmail($value, $emailElement)
  {
    $bannedEmailsTable = Engine_Api::_()->getDbtable('BannedEmails', 'core');
    if ($bannedEmailsTable->isEmailBanned($value)) {
      return false;
    }
    $isValidEmail = true;
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onCheckBannedEmail', $value);
    foreach ((array)$event->getResponses() as $response) {
      if ($response) {
        $isValidEmail = false;
        break;
      }
    }
    return $isValidEmail;
  }

  public function checkBannedUsername($value, $usernameElement)
  {
    $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
    return !$bannedUsernamesTable->isUsernameBanned($value);
  }
}
