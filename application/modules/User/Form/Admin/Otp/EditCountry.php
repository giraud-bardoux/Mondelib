<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: EditCountry.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Otp_EditCountry extends Engine_Form {
	
  public function init() {
  
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if(!empty($id)) {
      $item = Engine_Api::_()->getItem('core_country', $id);
    }
   
    $this->setTitle('Edit Country')
          ->setAttrib('name', 'country_edit');
    
    $this->addElement('Text', 'name', array(
      'label' => 'Name',
      'allowEmpty' => false,
      'required' => true,
    ));
    
    $this->addElement('Text', 'phonecode', array(
      'label' => 'Phone Code',
      'allowEmpty' => true,
      'required' => false,
      'disable' => true,
    ));

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    $fileOptions = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $fileOptions[$file->storage_path] = $file->name;
    }
    if (engine_count($fileOptions) > 1) {
      $description = $this->getTranslator()->translate('Choose an icon to show with this country. This icon will show in both user panel and admin panel of your site. [Note: You can add a new icon from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section. If you leave the field blank then nothing will show.]');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'icon', array(
        'label' => "Country Icon (This will show up in the country code dropdown with the country names.)",
        'description' => $description,
        'multiOptions' => $fileOptions,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no icons in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an icon to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'icon', array(
        'label' => "Country Icon (This will show up in the country code dropdown with the country names.)",
        'description' => $description,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
    
    // Default
    $this->addElement('Checkbox', 'default', array(
      'label' => 'Do you want to make this country default for your site?',
      'disable' => ($item->iso2 == Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) ? 'disabled': '',
    ));
    
    // Enabled
    $this->addElement('Checkbox', 'enabled', array(
      'label' => 'Do you want enable this country?',
      'disable' => ($item->iso2 == Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) ? 'disabled': '',
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => "Edit",
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
