<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_Form_Admin_Manage_Filter extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->clearDecorators()
        ->addDecorator('FormElements')
        ->addDecorator('Form')
        ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
        ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');
      
    $ID = new Zend_Form_Element_Text('id');
    $ID->setLabel('ID')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));

    $recipient = new Zend_Form_Element_Text('recipient');
    $recipient->setLabel('Recipient Email')
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

    $code = new Zend_Form_Element_Text('code');
    $code->setLabel('Invite Code')
          ->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
          ->addDecorator('HtmlTag', array('tag' => 'div'));
          
    $import_method = new Zend_Form_Element_Select('import_method');
    $import_method
      ->setLabel('Invitation Method')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '-1' => '',
        'csv' => 'CSV',
        'invite' => 'Invite',
        'referral' => 'Referral',
      ))
      ->setValue('-1');

    $submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit->setLabel('Search')
          ->clearDecorators()
          ->addDecorator('ViewHelper')
          ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
          ->addDecorator('HtmlTag2', array('tag' => 'div'));

    $this->addElement('Hidden', 'id', array(
      'order' => 10003,
    ));

    
    $this->addElements(array(
      $ID,
      $recipient,
      @$country_code,
      @$phone_number,
      $code,
      $import_method,
      $submit,
    ));

    // Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }
}
