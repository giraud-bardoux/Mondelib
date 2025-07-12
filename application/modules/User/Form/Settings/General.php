<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: General.php 10042 2013-04-26 23:18:38Z jung $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_General extends Engine_Form
{
  protected $_item;

  public function setItem(User_Model_User $item)
  {
    $this->_item = $item;
  }

  public function getItem()
  {
    if( null === $this->_item ) {
      throw new User_Model_Exception('No item set in ' . get_class($this));
    }

    return $this->_item;
  }

  public function init()
  {
    // @todo fix form CSS/decorators
    // @todo replace fake values with real values
    $this->setTitle('General Settings')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
    $this->setAttrib('id', 'general_account_form');
    $this->setAttrib('class', 'global_form form_submit_ajax');
    //$this->addElement('Hash', 'token');

    $changeEmail = Engine_Api::_()->authorization()->getPermission($this->getItem(),'user', 'changeEmail');
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    //if(!empty($changeEmail)) {
      // Init email
      $this->addElement('Text', 'email', array(
        'label' => 'Email Address',
        'required' => true,
				'readonly' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('EmailAddress', true),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix().'users', 'email', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
        ),
        'filters' => array(
          'StringTrim'
        )
      ));
      $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
      $this->email->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');
      $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    //}
    
    $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);

    if(!empty($otpsms_signup_phonenumber)) {
      $this->addElement('Text', 'phone_number', array(
        //'description' => 'Please enter your mobile number.',
        'label' => 'Phone Number',
        'required' => false,
        'allowEmpty' => true,
        'validators' => array(
          array('NotEmpty',true),
          array('Regex', true, array("/^[0-9][0-9]{4,15}$/")),
        ),
      ));
      $this->phone_number->getValidator('Regex')->setMessage('Please enter a valid phone number.', 'regexNotMatch');
      $this->phone_number->getDecorator('Description')->setOptions(array('placement' => 'APPEND', 'escape' => false));

      if($settings->getSetting('otpsms.login.options', 0) == 2) {
        $this->addElement('Checkbox','enable_verification',array(
          'label' => 'Enable two step verification on login. Note: (This setting only works with Phone Number.)'
        ));
      }
    }

    // Init username
    if( $settings->getSetting('user.signup.username', 1)) {
      $this->addElement('Text', 'username', array(
        'label' => 'Username',
        'required' => true,
        'allowEmpty' => false,
        'validators' => array(
          array('NotEmpty', true),
          array('Alnum', true),
          array('StringLength', true, array(4, 64)),
          array('Regex', true, array('/^[a-z][a-z0-9]*$/i')),
          array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix().'users', 'username', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
        ),
      ));
      $this->username->getValidator('NotEmpty')->setMessage('Please enter a valid username.', 'isEmpty');
      $this->username->getValidator('Db_NoRecordExists')->setMessage('Someone has already picked this username, please use another one.', 'recordFound');
      $this->username->getValidator('Regex')->setMessage('Username must start with a letter.', 'regexNotMatch');
      $this->username->getValidator('Alnum')->setMessage('Username must be alphanumeric.', 'notAlnum');
    }
    
    // Init type
    $this->addElement('Select', 'accountType', array(
      'label' => 'Account Type',
    ));

    // Used to redirect users to the correct page after login with Facebook
    $_SESSION['redirectURL'] = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

//     // Init Facebook
//     $facebook_enable = $settings
//         ->getSetting('core_facebook_enable', 'none');
//     if( 'none' != $facebook_enable ) {
//       $desc = 'Linking your Facebook account will let you login with Facebook.';
//       $this->addElement('Dummy', 'facebook', array(
//         'label' => 'Facebook Integration',
//         'description' => $desc,
//         'content' => User_Model_DbTable_Facebook::loginButton('Integrate with my Facebook'),
//       ));
//       $this->addElement('Checkbox', 'facebook_id', array(
//         'label' => 'Integrate with my Facebook',
//         'description' => 'Facebook Integration',
//       ));
//     }
// 
//     
//     // Init X
//     $twitter_enable = $settings
//         ->getSetting('core_twitter_enable', 'none');
//     if( 'none' != $twitter_enable ) {
//       $desc = 'Linking your X account will let you login with X';
//       if( 'publish' == $twitter_enable ) {
//         $desc .= ' and publish content to your X feed.';
//       } else {
//         $desc .= '.';
//       }
//       $this->addElement('Dummy', 'twitter', array(
//         'label' => 'X Integration',
//         'description' => $desc,
//         'content' => User_Model_DbTable_Twitter::loginButton('Integrate with my X'),
//       ));
//       $this->addElement('Checkbox', 'twitter_id', array(
//         'label' => 'Integrate with my X',
//         'description' => 'X Integration',
//       ));
//     }
//     
//     // Init Google
//     $google_enable = $settings->getSetting('core_google_enable', 'none');
//     if( 'none' != $google_enable ) {
//       $desc = 'Linking your Google account will let you login with Google.';
//       $this->addElement('Dummy', 'google', array(
//         'label' => 'Google Integration',
//         'description' => $desc,
//         'content' => User_Model_DbTable_Google::loginButton('Integrate with my Google'),
//       ));
//       $this->addElement('Checkbox', 'google_id', array(
//         'label' => 'Integrate with my Google',
//         'description' => 'Google Integration',
//       ));
//     }
//     
//     // Init Linkedin
//     $linkedin_enable = $settings->getSetting('core_linkedin_enable', 'none');
//     if( 'none' != $linkedin_enable ) {
//       $desc = 'Linking your Linkedin account will let you login with Linkedin.';
//       $this->addElement('Dummy', 'linkedin', array(
//         'label' => 'Linkedin Integration',
//         'description' => $desc,
//         'content' => User_Model_DbTable_Linkedin::loginButton('Integrate with my Linkedin'),
//       ));
//       $this->addElement('Checkbox', 'linkedin_id', array(
//         'label' => 'Integrate with my Linkedin',
//         'description' => 'Linkedin Integration',
//       ));
//     }
$enablesigupfields = (array) json_decode($settings->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language","location"]'));

  if(isset($enablesigupfields) && engine_in_array('location', $enablesigupfields) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
    //Location
    $this->addElement('Text', 'location', array(
      'label' => 'Location',
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
    $this->addElement('Hidden', 'lat', array('order' => 3000, 'value' => ''));
    $this->addElement('Hidden', 'lng', array('order' => 3001, 'value' => ''));
    $this->addElement('Hidden', 'city', array('order' => 3002, 'value' => ''));
    $this->addElement('Hidden', 'state', array('order' => 3003, 'value' => ''));
    $this->addElement('Hidden', 'country', array('order' => 3004, 'value' => ''));
    $this->addElement('Hidden', 'zip', array('order' => 3005, 'value' => ''));
  }

    // Init timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Timezone',
      'description' => 'Select the city closest to you that shares your same timezone.',
      'multiOptions' => Engine_Api::_()->core()->timeZone(),
    ));

    // Init default locale
    $locale = Zend_Registry::get('Locale');

    $localeMultiKeys = array_merge(
      array_keys(Zend_Locale::getLocaleList())
    );
    $localeMultiOptions = array();
    $languages = Zend_Locale::getTranslationList('language', $locale);
    $territories = Zend_Locale::getTranslationList('territory', $locale);
    foreach($localeMultiKeys as $key)
    {     
       if (!empty($languages[$key])) 
       {
         $localeMultiOptions[$key] = $languages[$key];
       }
       else
       {
         $locale = new Zend_Locale($key);
         $region = $locale->getRegion();
         $language = $locale->getLanguage(); 
         if ((!empty($languages[$language]) && (!empty($territories[$region])))) {
           $localeMultiOptions[$key] =  $languages[$language] . ' (' . $territories[$region] . ')';
         }
       }
    }
    $localeMultiOptions = array_merge(array('auto'=>'[Automatic]'), $localeMultiOptions);
    
    $this->addElement('Select', 'locale', array(
      'label' => 'Locale',
      'description' => 'Dates, times, and other settings will be displayed using this locale setting.',
      'multiOptions' => $localeMultiOptions
    ));

    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
    
    // Create display group for buttons
    #$this->addDisplayGroup($emailAlerts, 'checkboxes');

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
       'module' => 'user',
       'controller' => 'settings',
       'action' => 'general',
    ), 'default'));
  }
}
