<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Manage_Edit extends Engine_Form
{
    protected $_userIdentity;

    public function setUserIdentity($userIdentity)
    {
        $this->_userIdentity = (int) $userIdentity;
        return $this;
    }

    public function init()
    {   
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $this
            ->setAttrib('id', 'admin_members_edit')
            ->setTitle('Edit Member')
            ->setDescription('You can change the details of this member\'s account here.')
            ->setAction($_SERVER['REQUEST_URI']);

        // init email
        $this->addElement('Text', 'email', array(
            'label' => 'Email Address',
            //'required' => true,
            //'allowEmpty' => false,
            'validators' => array(
                array('NotEmpty', true),
                array('EmailAddress', true),
                array('Db_NoRecordExists', true, array(
                    Engine_Db_Table::getTablePrefix() . 'users', 'email', array(
                        'field' => 'user_id',
                        'value' => (int) $this->_userIdentity
                    )))
            ),
            'filters' => array(
                'StringTrim'
            )
        ));
        $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
        
        $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
        
        if(!empty($otpsms_signup_phonenumber)) {
          $this->addElement('Text', 'phone_number', array(
            'label' => 'Phone Number',
            //'required' => false,
            //'allowEmpty' => true,
            'validators' => array(
              array('NotEmpty',true),
              array('Regex', true, array("/^[0-9][0-9]{4,15}$/")),
            ),
          ));
          $this->phone_number->getValidator('Regex')->setMessage('Please enter a valid phone number.', 'regexNotMatch');
          $this->phone_number->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));

          $this->addElement('Hidden', 'isMobileChange', array(
            'order' => 2000,
            'value' => 0,
          ));
        }
        
        
        // init username
        if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) > 0 ) {
            $this->addElement('Text', 'username', array(
              'label' => 'Username',
              'required' => true,
              'allowEmpty' => false,
              'validators' => array(
                array('NotEmpty', true),
                array('Alnum', true),
                array('StringLength', true, array(4, 64)),
                array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
                //array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix() . 'users', 'username'))
              ),
            ));
            $this->username->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));
            $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid username.', 'isEmpty');
            $this->username->getValidator('Regex')->setMessage('Username must start with a letter.', 'regexNotMatch');
            $this->username->getValidator('Alnum')->setMessage('Username must be alphanumeric.', 'notAlnum');

            // Add banned username validator
            $bannedUsernameValidator = new Engine_Validate_Callback(array($this, 'checkBannedUsername'), $this->username);
            $bannedUsernameValidator->setMessage("This username is not available, please use another one.");
            $this->username->addValidator($bannedUsernameValidator);
        }

        // init password
        $this->addElement('Password', 'password', array(
            'label' => 'Password',
            'autocomplete' => 'off',
        ));
        
        //Work For Show and Hide Password
        $this->addElement('dummy', 'showhidepassword', array(
          'decorators' => array(array('ViewScript', array(
            'viewScript' => 'application/modules/User/views/scripts/_showhidepassword.tpl',
          ))),
        ));
        $this->addDisplayGroup(array('password', 'showhidepassword'), 'password_settings_group');
        
        $this->addElement('Password', 'passconf', array(
            'label' => 'Password Again',
            'autocomplete' => 'off',
        ));
        
        //Work For Show and Hide Password
        $this->addElement('dummy', 'showhideconfirmpassword', array(
          'decorators' => array(array('ViewScript', array(
            'viewScript' => 'application/modules/User/views/scripts/_showhideconfirmpassword.tpl',
          ))),
        ));
        $this->addDisplayGroup(array('passconf', 'showhideconfirmpassword'), 'password_confirm_settings_group');

        // Init level
        $levelMultiOptions = array(); //0 => ' ');
        $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
        foreach( $levels as $row ) {
            $levelMultiOptions[$row->level_id] = $row->getTitle();
        }
        $this->addElement('Select', 'level_id', array(
            'label' => 'Member Level',
            'multiOptions' => $levelMultiOptions
        ));

        // Init level
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
          $networkMultiOptions = array(); //0 => ' ');
          $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
          if(engine_count($networks) > 0) {
            foreach( $networks as $row ) {
                $networkMultiOptions[$row->network_id] = $row->getTitle();
            }
            $this->addElement('Multiselect', 'network_id', array(
                'label' => 'Networks',
                'multiOptions' => $networkMultiOptions
            ));
          }
        }

        // Init approved
        $this->addElement('Checkbox', 'approved', array(
            'label' => 'Approved?',
        ));

        // Init verified
        $this->addElement('Checkbox', 'verified', array(
            'label' => 'Is Email Verified?'
        ));

        // Init enabled
        $this->addElement('Checkbox', 'enabled', array(
            'label' => 'Enabled?',
        ));
        
        // Init verified
        $this->addElement('Checkbox', 'is_verified', array(
            'label' => 'Verified?'
        ));
        
        $this->addElement('Checkbox', 'donotsellinfo', array(
            'label' => 'Do Not Sell My Personal Information',
        ));
        // Init disable email
        $this->addElement('Checkbox', 'disable_email', array(
            'label' => 'Disable all site emails?',
        ));

        // Element: token
        $this->addElement('Hash', 'token');

        // Buttons
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            )
        ));

        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $this->getDisplayGroup('buttons');
        $button_group->addDecorator('DivDivDivWrapper');
    }
    
    public function checkBannedUsername($value, $usernameElement)
    {
      $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
      return !$bannedUsernamesTable->isUsernameBanned($value);
    }
}
