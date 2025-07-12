<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Invite_Form_Invite_Filter extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->clearDecorators()
        ->addDecorator('FormElements')
        ->addDecorator('Form')
        ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
        ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    // Element: query
    $this->addElement('Text', 'recipient', array(
      'label' => 'Recipient Email',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    if($settings->getSetting('otpsms.signup.phonenumber', 0)) {
      //get countries
      $countriesData = Engine_Api::_()->getDbTable('countries', 'core')->getCountries();
      $countries = array('' => '');
      foreach($countriesData as $country) {
        $countries[$country->phonecode] = $country->name.' (+'.str_replace('+', '', $country->phonecode).')';
      }

      $this->addElement('Select', 'country_code', array(
        'label' => 'Country Code',
        'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div')),
        ),
        'multiOptions' => $countries,
      ));
      
      $this->addElement('Text', 'phone_number', array(
        'label' => 'Phone Number',
        'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div')),
        ),
      ));
    }

    // Element: query
    $this->addElement('Select', 'import_method', array(
      'label' => 'Invitation Method',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
      'multiOptions' => array(
        '-1' => '',
        'csv' => 'CSV',
        'invite' => 'Invite',
        'referral' => 'Referral',
      ),
    ));

		$subform = new Engine_Form(array(
			'description' => 'Invitation Date',
			'elementsBelongTo'=> 'date',
			'decorators' => array(
				'FormElements',
				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
				array('HtmlTag', array('tag' => 'div', 'id' =>'integer-wrapper'))
			)
		));
		$subform->addElement('Text', 'date_from', array('placeholder'=>'from'));
    $subform->addElement('Text', 'date_to', array('placeholder'=>'to'));
		$this->addSubForm($subform, 'date');

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Search',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
        array('HtmlTag2', array('tag' => 'div')),
      ),
    ));
  }
}
