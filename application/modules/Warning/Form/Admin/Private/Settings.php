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

class Warning_Form_Admin_Private_Settings extends Engine_Form {

  public function init() {
		
		$settings = Engine_Api::_()->getApi('settings', 'core');
		$files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
		
    $this->setDescription('Below are the pre configured design templates for the Private page on your website. Here, you can change the image for the active template. Also, the color of design templates are for indicative purpose only. The actual colors will be theme based.');

    $this->addElement('Radio', 'warning_privateenable', array(
      'label' => 'Enable Design Template',
      'description' => "Do you want to enable the design template for the Private Page on your website? If you choose 'Yes', then the design of Private Page will come from this plugin, otherwise default SocialEngine design will come.",
      'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
      ),
      'value' => $settings->getSetting('warning.privateenable', 1),
    ));

    if (engine_count($files) > 1) {
      $description = $this->getTranslator()->translate('Choose an image for the active template. The image will only display in the templates which supports it. [Note: You can add a new image from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section.');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'warning_privatepagephotoID', array(
        'label' => "Choose Image",
        'description' => $description,
        'multiOptions' => $files,
        'value' => $settings->getSetting('warning.privatepagephotoID', ""),
      ));
      $this->warning_privatepagephotoID->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'warning_privatepagephotoID', array(
        'label' => "Choose Image",
        'description' => $description,
      ));
      $this->warning_privatepagephotoID->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $this->addElement('Radio', 'warning_private_pageactivate', array(
			'label' => 'Choose Design Template',
      'multiOptions' => array(
          1 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/1.png" alt="" />',
          2 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/2.png" alt="" />',
          3 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/3.png" alt="" />',
          4 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/4.png" alt="" />',
          5 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/5.png" alt="" />',
          6 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/6.png" alt="" />',
          7 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/7.png" alt="" />',
          8 => '<img src="./application/modules/Warning/externals/images/page-scheme/private/8.png" alt="" />',
      ),
      'escape' => false,
      'value' => $settings->getSetting('warning.private.pageactivate', 1),
    ));

    $this->addElement('Button', 'execute', array(
        'label' => 'Save Settings',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));
  }
}
