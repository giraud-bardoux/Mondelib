<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: EditCurrency.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Form_Admin_Settings_EditCurrency extends Engine_Form {
	
  public function init() {
  
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if(!empty($id)) {
      $currency = Engine_Api::_()->getItem('payment_currency', $id);
    }
   
    if(!empty($id)) {
      $this->setTitle('Edit Currency')
          ->setAttrib('name', 'currency_edit');
    } else {
      $this->setTitle('Add New Currency')
          ->setAttrib('name', 'currency_create');
    }
    
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'allowEmpty' => false,
      'required' => true,
    ));
    
    if(!empty($id)) {
      $this->addElement('Text', 'code', array(
        'label' => 'Currency Code',
        'allowEmpty' => true,
        'required' => false,
        'disable' => true,
        'maxlength' => 3,
      ));
    } else { 
      $this->addElement('Text', 'code', array(
        'label' => 'Currency Code',
        'allowEmpty' => false,
        'required' => true,
        'maxlength' => 3,
      ));
    }
    
    if(!empty($id) && $currency && $currency->code == Engine_Api::_()->payment()->defaultCurrency()) {
      $this->addElement('Text', 'change_rate', array(
        'label' => 'Change Rate',
        'allowEmpty' => true,
        'required' => false,
        'disable' => true,
        'validators' => array(
          array('Float', true),
          new Engine_Validate_AtLeast(1),
        ),
      ));
    } else {
      $this->addElement('Text', 'change_rate', array(
        'label' => 'Change Rate',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
          array('Float', true),
          //new Engine_Validate_AtLeast(1),
        ),
      ));
    }
    
    $this->addElement('Text', 'symbol', array(
      'label' => 'Currency Symbol',
      'allowEmpty' => false,
      'required' => true,
    ));

    $this->addElement('Select', 'seprator', array(
      'label' => 'Thousand Seprator',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions' => array(
        ',' => "Comma (,)",
        "." => "Decimal (.)",
      ),
    ));
    
    $this->addElement('Select', 'placement', array(
      'label' => 'Symbol Placement',
      'description' => 'Note: This setting doesn\'t work with RTL languages.',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions' => array(
        'pre' => "Pre (for eg: $5)",
        "post" => "Post (for eg: 5$)",
      ),
    ));
    
    // Gateways
    $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $gatewaySelect = $gatewayTable->select()
                                ->where('enabled = ?', 1);
    $gateways = $gatewayTable->fetchAll($gatewaySelect);

    $gatewayPlugins = array();
    foreach( $gateways as $gateway ) {
      $gatewayPlugins[$gateway->getIdentity()] = $gateway->title;
    }
    
    if(engine_count($gatewayPlugins) > 0) {
      $this->addElement('MultiCheckbox', 'gateways', array(
        'label' => 'Gateways',
        'allowEmpty' => true,
        'required' => false,
        'multiOptions' => $gatewayPlugins,
      ));
    }

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    $fileOptions = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $fileOptions[$file->storage_path] = $file->name;
    }
    if (engine_count($fileOptions) > 1) {
      $description = $this->getTranslator()->translate('Choose an icon to show with this currency. This icon will show in both user panel and admin panel of your site. [Note: You can add a new icon from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section. If you leave the field blank then nothing will show.]');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'icon', array(
        'label' => "Currency Icon (This will show up in the currency dropdown next to the currency code.)",
        'description' => $description,
        'multiOptions' => $fileOptions,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no icons in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an icon to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'icon', array(
        'label' => "Currency Icon (This will show up in the currency dropdown next to the currency code.)",
        'description' => $description,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    // Enabled
    $this->addElement('Checkbox', 'enabled', array(
      'label' => 'Do you want enable this currency?',
      'disable' => ($currency->code == Engine_Api::_()->payment()->defaultCurrency()) ? 'disabled': '',
    ));
    
    if(!empty($id)) {
      $submitLabel = 'Edit';
    } else {
      $submitLabel = 'Add';
    }
    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => $submitLabel,
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onClick'=> 'javascript:parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
		
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}
