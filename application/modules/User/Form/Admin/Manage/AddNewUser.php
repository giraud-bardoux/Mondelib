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

class User_Form_Admin_Manage_AddNewUser extends Engine_Form {

  protected $_defaultProfileId;
  public function getDefaultProfileId() {
    return $this->_defaultProfileId;
  }

  public function setDefaultProfileId($default_profile_id) {
    $this->_defaultProfileId = $default_profile_id;
    return $this;
  }

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $enablesigupfields = (array) json_decode($settings->getSetting('user.signup.enablesigupfields', '["confirmpassword","profiletype","timezone","language"]'));

    $translate = Zend_Registry::get('Zend_Translate');
    $inviteSession = new Zend_Session_Namespace('invite');

    // Init form
    $this->setTitle('Add New User');
    $this->setdescription('Here, you can add a new user to your site.');
    $this->setAttrib('id', 'signup_account_form');

    // Element: name (trap)
    $this->addElement('Text', 'name', array(
      'class' => 'signup-name',
      'label' => 'Name',
      'validators' => array(
	      array('StringLength', true, array('max' => 0)))));

    $this->name->getValidator('StringLength')->setMessage('An error has occured, please try again later.');

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

    // // Element: email
    // $emailElement = $this->addEmailElement(array(
    //   'label' => 'Email Address',

    //   'description' => 'Enter the email address of the member. This email will be used to login.',

    //   'required' => true,
    //   'allowEmpty' => false,
    //   'validators' => array(
    //     array('NotEmpty', true),
    //     array('EmailAddress', true),
    //     array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'email'))
    //   ),
    //   'filters' => array(
    //     'StringTrim'
    //   ),
    //   // fancy stuff
    //   'inputType' => 'text',
    //   'autofocus' => 'autofocus',
      
    // ));

    // $emailElement->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    // $emailElement->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');
    // $emailElement->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    // // Add banned email validator
    // $bannedEmailValidator = new Engine_Validate_Callback(array($this, 'checkBannedEmail'), $emailElement);
    // $bannedEmailValidator->setMessage("This email address is not available, please use another one.");
    // $emailElement->addValidator($bannedEmailValidator);

    // if( !empty($inviteSession->invite_email) ) {
    //   $emailElement->setValue($inviteSession->invite_email);
    // }
    
    //Work For Show and Hide Password
    $this->addElement('dummy', 'showpassword', array(
      'label' => 'Password',
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/User/views/scripts/admin-manage/_generatePassword.tpl',
      ))),
    ));

    // Element: password
    $this->addElement('Password', 'password', array(
      'label' => 'Password',
      'id' => 'signup_password',
      "autocomplete" => "off",
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

    //Work For Show and Hide Password
    $this->addElement('dummy', 'showhidepassword', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/User/views/scripts/_showhidepassword.tpl',
      ))),
      
    ));
    //Work For Show and Hide Password

    $this->addElement('dummy', 'copyPasswordLink', array(
      'content' => '<a href="javascript:;"  data-bs-toggle="tooltip" data-bs-placement="top" title=""data-bs-original-title="'.$translate->translate("Copy Password").'" class="copy_password" id="copy_password" title="Copy Password"><i class="far fa-copy"></i></a>',
    ));
    $this->addDisplayGroup(array('password', 'showhidepassword', 'copyPasswordLink'), 'password_settings_group');

    // Element: username
    if( $settings->getSetting('user.signup.username', 1) > 0 ) {
      $description = Zend_Registry::get('Zend_Translate')->_('Enter the username for this member. This will be the end of profile link for this member.');

      $this->addElement('Text', 'username', array(
        'label' => 'Username',
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
        
          //'onblur' => 'var el = this; en4.user.checkUsernameTaken(this.value, function(taken){ el.style.marginBottom = taken * 100 + "px" });'
      ));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid username.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this username, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Username must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Username must be alphanumeric.', 'notAlnum');

      // Add banned username validator
      $bannedUsernameValidator = new Engine_Validate_Callback(array($this, 'checkBannedUsername'), $this->username);
      $bannedUsernameValidator->setMessage("This username is not available, please use another one.");
      $this->username->addValidator($bannedUsernameValidator);
    }

    if(isset($enablesigupfields) && engine_in_array('profiletype', $enablesigupfields)) {
      //Profile Type Work
      $defaultProfileId = "0_0_" . $this->getDefaultProfileId();
      $customFields = new Fields_Form_Standard(array(
          'item' => Engine_Api::_()->user()->getUser(null),
          'isCreation' => true,
          'decorators' => array(
          'FormElements'
      )));
      $customFields->removeElement('submit');
      if ($customFields->getElement($defaultProfileId)) {
        $customFields->getElement($defaultProfileId)
                ->clearValidators()
                ->setRequired(true)
                ->setAllowEmpty(false);
      }
      $this->addSubForms(array(
          'fields' => $customFields
      ));
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

    $this->addElement('File', 'photo', array(
      'label' => 'Profile Photo',
      'description' => 'Upload profile photo for this member.',
      'multiFile' => 1,
      'validators' => array(
        array('Count', false, 1),
        array('Extension', false, 'jpg,jpeg,png,gif,webp'),
      ),
      
    ));

    // Languages
    $languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();

    if(isset($enablesigupfields) && engine_in_array('language', $enablesigupfields) && engine_count($languageNameList) > 1) {
      $this->addElement('Select', 'language', array(
        'label' => 'Language',
        'multiOptions' => $languageNameList,
      ));
      $this->language->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    } else {
      $this->addElement('Hidden', 'language', array(
        'value' => key($languageNameList),
        'order' => 1002
      ));
    }

    //Element member level
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
    if(engine_count($levels) > 0) {
      $levelMultiOptions = array();
      foreach( $levels as $row ) {
        $levelMultiOptions[$row->level_id] = $row->getTitle();
      }
      $defaultLevelId = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel()->level_id;
      $this->addElement('Select', 'level_id',array(
          'label'  => 'Select Member Level',
          'Description'  => 'Select the member level of this member.',
          'required'  => true,
          'multiOptions'  => $levelMultiOptions,
          'value' => $defaultLevelId,
      ));
    }

    // Init level
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
      $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
      if(engine_count($networks) > 0) {
        $networkMultiOptions = array();
        foreach( $networks as $row ) {
          $networkMultiOptions[$row->network_id] = $row->getTitle();
        }
        $this->addElement('Multiselect', 'network_id', array(
          'label' => 'Networks',
    	    'description'  => 'Select the networks which will be joined by this member.',
          'multiOptions' => $networkMultiOptions,
        ));
      }
    }

    $this->addElement('Checkbox', 'approved', array(
      'label' => 'Approved?',
      'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
      ),
    ));

    $this->addElement('Checkbox', 'verified', array(
      'label' => 'Is Email Verified?',
      'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
      ),
    ));

    $this->addElement('Checkbox', 'enabled', array(
      'label' => 'Enabled?',
      'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
      ),
    ));
    
    $this->addElement('Checkbox', 'is_verified', array(
      'label' => 'Verified?',
      'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
      ),
    ));
    
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Add',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => 'admin/user/manage',
      'decorators' => array(
        'ViewHelper'
      ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
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
