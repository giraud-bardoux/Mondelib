<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Settings.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_Form_Admin_Comingsoon_Settings extends Engine_Form {

  public function init() {
		
		$settings = Engine_Api::_()->getApi('settings', 'core');
		
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();
		
    $this->setDescription('Below are the pre-configured design templates for the Coming Soon page on your website. Here, you can change the image for the active template.');

    $enableLink = 'https://' . $_SERVER['HTTP_HOST'] . $view->baseUrl() . '/admin/';

    $descriptionEnable = $this->getTranslator()->translate('Do you want to enable the Coming Soon functionality on your website? If you choose Yes, then you will see Coming Soon page whenever you try to open your website. To login into the admin panel or user panel as admin, go to ') . '<a href="%1$s" target="_blank">'.$enableLink.'</a>';
    $descriptionEnable = vsprintf($descriptionEnable, array($enableLink));

    $this->addElement('Radio', 'warning_comingsoonenable', array(
      'label' => 'Enable Coming Soon',
      'description' => $descriptionEnable,
      'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
      ),
      'value' => $settings->getSetting('warning.comingsoonenable', 0),
    ));
    $this->warning_comingsoonenable->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $start_time = new Engine_Form_Element_CalendarDateTime('start_time');
    $start_time->setLabel("End Date for Coming Soon Page");
    $start_time->setDescription("Choose an end date on which the coming soon page will automatically end. After the selected date, your website will be visible to all.");
    $this->addElement($start_time);

    if(engine_count($files) > 1) {
      $description = $this->getTranslator()->translate('Choose from below the logo for the Coming Soon page of your website. [Note: You can add a new logo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager.</a>]');
      $description = vsprintf($description, array($fileLink));
        
      $this->addElement('Select', 'warning_comingsoonlogo', array(
        'label' => 'Logo',
        'description' => $description,
        'escape' => false,
        'multiOptions' => $files,
        'value'=> $settings->getSetting("warning.comingsoonlogo", '')
      ));
      $this->warning_comingsoonlogo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'warning_comingsoonlogo', array(
        'label' => "Logo",
        'description' => $description,
      ));
      $this->warning_comingsoonlogo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
    
    $this->addElement('Radio', 'warning_comingsooncontactenable', array(
      'label' => 'Enable Contact Us',
      'description' => 'Do you want to enable users to contact you via the Contact Us option on the coming soon page?',
      'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
      ),
      'value' => $settings->getSetting('warning.comingsooncontactenable', 1),
    ));
    
    $description = $this->getTranslator()->translate('Do you want to enable users Social Sites Link Menu options on the coming soon page? You can configure the links from the <a href="%1$s" target="_blank">Menu Editor</a>.');
    $description = vsprintf($description, array("admin/menus?name=core_social_sites"));
    
		$this->addElement('Radio', 'warning_comingsoonenablesocial', array(
			'label' => 'Enable Social Sites Link Menu',
			'description' => $description,
			'multiOptions' => array(
					'1' => 'Yes',
					'0' => 'No',
			),
			'value' => $settings->getSetting('warning.comingsoonenablesocial', 1),
		));
		$this->warning_comingsoonenablesocial->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
		
    if (engine_count($files) > 1) {
      $description = $this->getTranslator()->translate('Choose an image for the active template. The image will only display in the templates which supports it. [Note: You can add a new image from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section.');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'warning_comingsoonphotoID', array(
        'label' => "Choose Image",
        'description' => $description,
        'multiOptions' => $files,
        'value' => $settings->getSetting('warning.comingsoonphotoID', ""),
      ));
      $this->warning_comingsoonphotoID->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'warning_comingsoonphotoID', array(
        'label' => "Choose Image",
        'description' => $description,
      ));
      $this->warning_comingsoonphotoID->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $this->addElement('Radio', 'warning_comingsoon_pageactivate', array(
      'label' => 'Choose Design Template',
      'multiOptions' => array(
          1 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/1.png" alt="" />',
          2 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/2.png" alt="" />',
          3 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/3.png" alt="" />',
          4 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/4.png" alt="" />',
          5 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/5.png" alt="" />',
          6 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/6.png" alt="" />',
          7 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/7.png" alt="" />',
          8 => '<img src="./application/modules/Warning/externals/images/page-scheme/comingsoon/8.png" alt="" />',
      ),
      'escape' => false,
      'value' => $settings->getSetting('warning.comingsoon.pageactivate', 1),
    ));

    $this->addElement('Button', 'execute', array(
        'label' => 'Save Settings',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));
  }
}
