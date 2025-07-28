<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Account.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Form_Signup_Account extends Engine_Form_Email
{  
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->_emailAntispamEnabled = ($settings
        ->getSetting('core.spam.email.antispam.signup', 1) == 1) &&
      empty($_SESSION['facebook_signup']) &&
      empty($_SESSION['twitter_signup']);
    
    $inviteSession = new Zend_Session_Namespace('invite');
    $tabIndex = 1;
    
    // Init form
    $this->setTitle('Create Account');
    $this->setAttrib('id', 'signup_account_form');

    // Element: name (trap)
    /*$this->addElement('Text', 'name', array(
      'class' => 'signup-name',
      'label' => 'Name',
      'validators' => array(
	      array('StringLength', true, array('max' => 0)))));

    $this->name->getValidator('StringLength')->setMessage('An error has occured, please try again later.');*/

    // Element: email
    $emailElement = $this->addEmailElement(array(
      'label' => 'Email Address',
      'description' => 'You will use your email address to login.',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true),
        array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'email'))
      ),
      'filters' => array(
        'StringTrim'
      ),
      // fancy stuff
      'inputType' => 'email',
      'autofocus' => 'autofocus',
      'tabindex' => $tabIndex++,
    ));
    $emailElement->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    $emailElement->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    $emailElement->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');
    $emailElement->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    // Add banned email validator
    $bannedEmailValidator = new Engine_Validate_Callback(array($this, 'checkBannedEmail'), $emailElement);
    $bannedEmailValidator->setMessage("This email address is not available, please use another one.");
    $emailElement->addValidator($bannedEmailValidator);
    
    if( !empty($inviteSession->invite_email) ) {
      $emailElement->setValue($inviteSession->invite_email);
    }

    //if( $settings->getSetting('user.signup.verifyemail', 0) > 0 && $settings->getSetting('user.signup.checkemail', 0) == 1 ) {
    //  $this->email->addValidator('Identical', true, array($inviteSession->invite_email));
    //  $this->email->getValidator('Identical')->setMessage('Your email address must match the address that was invited.', 'notSame');
    //}
    
    // Element: code
    if( $settings->getSetting('user.signup.inviteonly') > 0 ) {
      $codeValidator = new Engine_Validate_Callback(array($this, 'checkInviteCode'), $emailElement);
      $codeValidator->setMessage("This invite code is invalid or does not match the selected email address");
      $this->addElement('Text', 'code', array(
        'label' => 'Invite Code',
        'required' => true
      ));
      $this->code->addValidator($codeValidator);

      if( !empty($inviteSession->invite_code) ) {
        $this->code->setValue($inviteSession->invite_code);
      }
    }

    if( $settings->getSetting('user.signup.random', 0) == 0 && 
        empty($_SESSION['facebook_signup']) && 
        empty($_SESSION['twitter_signup'])) {

        // Element: password
        $this->addElement('Password', 'password', array(
            'label' => 'Password',
            'description' => 'Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.',
            'required' => true,
            'allowEmpty' => false,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(6, 32)),
                array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),

            ),
            'tabindex' => $tabIndex++,
        ));
        $this->password->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
        $this->password->getValidator('Regex')->setMessage('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.');
        $this->password->getValidator('NotEmpty')->setMessage('Please enter a valid password.', 'isEmpty');

        $regexCheck = new Engine_Validate_Callback(array($this, 'regexCheck'), $this->password);
        $regexCheck->setMessage("Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.");
        $this->password->addValidator($regexCheck);

        // Element: passconf
        $this->addElement('Password', 'passconf', array(
            'label' => 'Password Again',
            'description' => 'Enter your password again for confirmation.',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
            ),
            'tabindex' => $tabIndex++,
        ));
        $this->passconf->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
        $this->passconf->getValidator('NotEmpty')->setMessage('Please make sure the "password" and "password again" fields match.', 'isEmpty');

        $specialValidator = new Engine_Validate_Callback(array($this, 'checkPasswordConfirm'), $this->password);
        $specialValidator->setMessage('Password did not match', 'invalid');
        $this->passconf->addValidator($specialValidator);
    }

    // Element: username
    if( $settings->getSetting('user.signup.username', 1) > 0 ) {
      $description = Zend_Registry::get('Zend_Translate')
          ->_('This will be the end of your profile link, for example: <br /> ' .
              '<span id="profile_address">http://%s</span>');
      $description = sprintf($description, $_SERVER['HTTP_HOST']
          . Zend_Controller_Front::getInstance()->getRouter()
          ->assemble(array('id' => 'yourname'), 'user_profile'));

      $this->addElement('Text', 'username', array(
        'label' => 'Profile Address',
        'description' => $description,
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('Alnum', true),
          array('StringLength', true, array(4, 64)),
          array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'username'))
        ),
        'tabindex' => $tabIndex++,
          //'onblur' => 'var el = this; en4.user.checkUsernameTaken(this.value, function(taken){ el.style.marginBottom = taken * 100 + "px" });'
      ));
      $this->username->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid profile address.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this profile address, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Profile addresses must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Profile addresses must be alphanumeric.', 'notAlnum');

      // Add banned username validator
      $bannedUsernameValidator = new Engine_Validate_Callback(array($this, 'checkBannedUsername'), $this->username);
      $bannedUsernameValidator->setMessage("This profile address is not available, please use another one.");
      $this->username->addValidator($bannedUsernameValidator);
    }
    
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
      if( engine_count($options['options']['multiOptions']) > 1 ) { 
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
              'required' => true,
              'allowEmpty' => false,
              'tabindex' => $tabIndex++,
            )));
      } else if( engine_count($options['options']['multiOptions']) == 1 ) {
        $this->addElement('Hidden', 'profile_type', array(
          'value' => $optionsIds[0]->option_id,
          'order' => 1001
        ));
      }
    }
    
    //Location field work
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.enable.location', 1)) {
      $this->addElement('Text', 'ses_location', array(
          'label' => 'Location',
          'placeholder' => 'Enter a location',
          'required' => false,
          'allowEmpty' => true,
          'filters' => array(
              new Engine_Filter_Censor(),
              new Engine_Filter_HtmlSpecialChars(),
          ),
      ));
      $this->addElement('Hidden', 'ses_lat', array(
          'order' => 9995,
      ));
      $this->addElement('Hidden', 'ses_lng', array(
          'order' => 9996,
      ));
      $this->addElement('Hidden', 'ses_zip', array(
          'order' => 9997,
      ));
      $this->addElement('Hidden', 'ses_city', array(
          'order' => 9998,
      ));
      $this->addElement('Hidden', 'ses_state', array(
          'order' => 9999,
      ));
      $this->addElement('Hidden', 'ses_country', array(
          'order' => 10000,
      ));
    }

    // Element: timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Timezone',
      'value' => $settings->getSetting('core.locale.timezone'),
      'multiOptions' => array(
        'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
        'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
        'US/Central' => '(UTC-6) Central Time (US & Canada)',
        'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
        'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
        'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
        'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
        'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
        'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
        'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
        'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
        'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
        'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
        'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
        'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
        'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
        'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
        'Iran' => '(UTC+3:30) Tehran',
        'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
        'Asia/Kabul' => '(UTC+4:30) Kabul',
        'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
        'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
        'Asia/Katmandu' => '(UTC+5:45) Nepal',
        'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
        'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
        'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
        'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
        'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
        'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
        'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
        'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
        'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
      ),
      'tabindex' => $tabIndex++,
    ));
    $this->timezone->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    // Element: language

    // Languages
    $translate = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    //$currentLocale = Zend_Registry::get('Locale')->__toString();
    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( !engine_in_array($defaultLanguage, $languageList) ) {
      if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = '';
      }
    }

    // Prepare language name list
    $localeObject = Zend_Registry::get('Locale');
    
    $languageNameList = array();
    $languageDataList = Zend_Locale_Data::getList($localeObject, 'language');
    $territoryDataList = Zend_Locale_Data::getList($localeObject, 'territory');

    foreach( $languageList as $localeCode ) {
      $languageNameList[$localeCode] = Zend_Locale::getTranslation($localeCode, 'language', $localeCode);
      if( empty($languageNameList[$localeCode]) ) {
        list($locale, $territory) = explode('_', $localeCode);
        $languageNameList[$localeCode] = "{$territoryDataList[$territory]} {$languageDataList[$locale]}";
      }
    }
    $languageNameList = array_merge(array(
      $defaultLanguage => $defaultLanguage
    ), $languageNameList);

    if(is_countable($languageNameList) && engine_count($languageNameList)>1){
      $this->addElement('Select', 'language', array(
        'label' => 'Language',
        'value' => $defaultLanguage,
        'multiOptions' => $languageNameList,
        'tabindex' => $tabIndex++,
      ));
      $this->language->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    }
    else{
      $this->addElement('Hidden', 'language', array(
        'value' => current((array)$languageNameList) 
      ));
    }
    
    if( $settings->getSetting('user.signup.terms', 1) == 1 ) {
      // Element: terms
      $description = Zend_Registry::get('Zend_Translate')->_('I have read and agree to the terms of service.');
      $description = sprintf($description, Zend_Controller_Front::getInstance()->getBaseUrl());

      $this->addElement('Checkbox', 'terms', array(
        'label' => $description,
        //'description' => $description,
        'required' => true,
        'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
        ),
        'tabindex' => $tabIndex++,
      ));
      $this->terms->getValidator('GreaterThan')->setMessage('You must agree to the terms of service to continue.', 'notGreaterThan');
      //$this->terms->getDecorator('Label')->setOption('escape', false);
      $this->terms->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::APPEND, 'tag' => 'label', 'class' => 'null', 'escape' => false, 'for' => 'terms'))
          ->addDecorator('DivDivDivWrapper');

      //$this->terms->setDisableTranslator(true);
    }
    
    // Otp Work 
    // version check
    if($this->checkVersion(2.7,1.7)){
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('otpsms') && Engine_Api::_()->otpsms()->isServiceEnable()){
        $settings = Engine_Api::_()->getApi('settings', 'core');   
        $orderOrginal = 2;
        $countries = Engine_Api::_()->otpsms()->getCountryCodes();
        $allowedCountries = $settings->getSetting('otpsms_allowed_countries');
        $countriesArray = array();
        $otpsms_signup_phonenumber  = $settings->getSetting('otpsms_signup_phonenumber',1);
        $otpsms_choose_phonenumber = $settings->getSetting('otpsms_choose_phonenumber',0);
        $otpsms_required_phonenumber = $settings->getSetting('otpsms_required_phonenumber',1);
        if($otpsms_signup_phonenumber) {
          foreach ($countries as $code => $country) {
            $countryName = ucwords(strtolower($country["name"]));
            if($code == $defaultCountry)
              $defaultCountry = $country['code'];
            if(engine_count($allowedCountries) && !engine_in_array($code,$allowedCountries))
              continue;
            $countriesArray[$country["code"]] = '"+'.$country["code"].'"';
          }
          
          if(!$otpsms_choose_phonenumber && $otpsms_required_phonenumber){
              $required = true;
              $allowEmpty = false;
              $requiredClass = ' required';
          } else {
              $required = false;
              $allowEmpty = true;
              $requiredClass = '';
          }
          
          $this->addElement('Select','country_code',array(
            'value'=>$defaultCountry,
            'label'=>'Country Code',
            'required'=>$required,
            'allowEmpty' => $allowEmpty,
            'multiOptions'=>$countriesArray,
            'tabindex' => $tabIndex++,
          ));
          $this->addElement('Text','phone_number',array(
            'placeholder'=>'Phone Number',
            'label' => 'Phone Number',
            'tabindex' => $tabIndex++,
            'required'=>$required,
            'allowEmpty' => $allowEmpty,
            'value' => $defaultPhoneNumber,
            'validators' => array(
              array('NotEmpty', empty($required) ? false : true),
              array('Regex', true, array('/^[1-9][0-9]{4,15}$/')),
              array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'phone_number'))
            ),
          ));
          //$this->addElement('Hidden','otp_field_type',array('order'=>87678,'value'=>!empty($_POST['otp_field_type']) ? $_POST['otp_field_type'] : "email"));
          $this->phone_number->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this phone number, please use another one.', 'recordFound');
          $this->phone_number->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));
          $this->phone_number->getValidator('Regex')->setMessage('Please enter a valid phone number.', 'regexNotMatch');
          
          $this->addDisplayGroup(array('phone_number', 'country_code'), 'otp_phone_number',array('order'=>$orderOrginal));
          $button_group = $this->getDisplayGroup('otp_phone_number');
          $button_group->setDescription('Phone Number');
          $button_group->setDecorators(array(
              'FormElements',
              array('Description', array('placement' => 'PREPEND', 'tag' => 'div', 'class' => 'form-label'.$requiredClass)),
              array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper', 'id' => 'otp_phone_number','style'=>'display:'.$display.';'))
          ));
        }
      }
    }
    // Otp Work 

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabIndex++,
    ));
  }
  
	public function checkVersion($android,$ios){
    if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
        return  true;
    if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
        return true;
    return false;
	}

  public function checkPasswordConfirm($value, $passwordElement)
  {
    return ( $value == $passwordElement->getValue() );
  }
    public function regexCheck($value)
    {
        if(preg_match("/([\\\\:\/])/", $value))
        {
            return false;
        }
        return true;
    }
  public function checkInviteCode($value, $emailElement)
  {
    $inviteTable = Engine_Api::_()->getDbTable('invites', 'invite');
    $select = $inviteTable->select()
      ->from($inviteTable->info('name'), 'COUNT(*)')
      ->where('code = ?', $value)
      ;
      
    if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.checkemail') ) {
      $select->where('recipient LIKE ?', $emailElement->getValue());
    }
    
    return (bool) $select->query()->fetchColumn(0);
  }

  public function checkBannedEmail($value, $emailElement)
  {
    $bannedEmailsTable = Engine_Api::_()->getDbTable('BannedEmails', 'core');
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
    $bannedUsernamesTable = Engine_Api::_()->getDbTable('BannedUsernames', 'core');
    return !$bannedUsernamesTable->isUsernameBanned($value);
  }
}
