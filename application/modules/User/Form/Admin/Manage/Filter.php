<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Manage_Filter extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');
      
    $ID = new Zend_Form_Element_Text('user_id');
    $ID
      ->setLabel('ID')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

    $displayname = new Zend_Form_Element_Text('displayname');
    $displayname
      ->setLabel('Display Name')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

    if($settings->getSetting('user.signup.username', 1)) {
      $username = new Zend_Form_Element_Text('username');
      $username
        ->setLabel('Username')
        ->clearDecorators()
        ->addDecorator('ViewHelper')
        ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
        ->addDecorator('HtmlTag', array('tag' => 'div'));
    }

    $email = new Zend_Form_Element_Text('email');
    $email
      ->setLabel('Email')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

    if($settings->getSetting('otpsms.signup.phonenumber', 0)) {
      //get countries
      $countriesData = Engine_Api::_()->getDbTable('countries', 'core')->getCountries();
      $countries = array('' => '');
      foreach($countriesData as $country) {
        $countries[$country->phonecode] = $country->name.' (+'.str_replace('+', '', $country->phonecode).')';
      }
      
      $country_code = new Zend_Form_Element_Select('country_code');
      $country_code
          ->setLabel('Country Code')
          ->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
          ->addDecorator('HtmlTag', array('tag' => 'div'))
          ->setMultiOptions($countries);
        
      $phone_number = new Zend_Form_Element_Text('phone_number');
      $phone_number
        ->setLabel('Phone Number')
        ->clearDecorators()
        ->addDecorator('ViewHelper')
        ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
        ->addDecorator('HtmlTag', array('tag' => 'div'));
    }
    
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();
    $levelMultiOptions = array(0 => ' ');
    foreach ($levels as $key => $value) {
      $levelMultiOptions[$key] = $value;
    }
    $level_id = new Zend_Form_Element_Select('level_id');
    $level_id
      ->setLabel('Level')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions($levelMultiOptions);

    $enabled = new Zend_Form_Element_Select('enabled');
    $enabled
      ->setLabel('Approved')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '-1' => '',
        '0' => 'Not Approved',
        '1' => 'Approved',
      ))
      ->setValue('-1');

    $is_verified = new Zend_Form_Element_Select('is_verified');
    $is_verified
      ->setLabel('Verified')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '-1' => '',
        '0' => 'Not Verified',
        '1' => 'Verified',
      ))
      ->setValue('-1');

    $lastlogin_date = new Zend_Form_Element_Select('lastlogin_date');
    $lastlogin_date
      ->setLabel('Logged In')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '-1' => '',
        '0' => 'No',
        '1' => 'Yes',
      ))
      ->setValue('-1');

    $donotsellinfo = new Zend_Form_Element_Select('donotsellinfo');
    $donotsellinfo
      ->setLabel('Do Not Sell My Info')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '-1' => '',
        '0' => 'No',
        '1' => 'Yes',
      ))
      ->setValue('-1');
      
    $this->addElement('Hidden', 'order', array(
      'order' => 10001,
    ));

    $this->addElement('Hidden', 'order_direction', array(
      'order' => 10002,
    ));

    $this->addElement('Hidden', 'user_id', array(
      'order' => 10003,
    ));

    $this->addElements(array(
      $ID,
      $displayname,
      $username,
      $email,
      @$country_code,
      @$phone_number,
      $level_id,
      $enabled,
      $is_verified,
      $lastlogin_date,
      $donotsellinfo,
    ));
    
// 		$subform = new Engine_Form(array(
// 			'description' => 'Signup Date',
// 			'elementsBelongTo'=> 'date',
// 			'decorators' => array(
// 				'FormElements',
// 				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
// 				array('HtmlTag', array('tag' => 'div', 'id' =>'integer-wrapper'))
// 			)
// 		));
// 		$subform->addElement('Text', 'date_from', array('placeholder'=>'from'));
//     $subform->addElement('Text', 'date_to', array('placeholder'=>'to'));
// 		$this->addSubForm($subform, 'date');
		
    $this->addElement('Button', 'search', array(
      'label' => 'Search',
      'type' => 'submit',
      'ignore' => true,
    ));

    // Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }
}
