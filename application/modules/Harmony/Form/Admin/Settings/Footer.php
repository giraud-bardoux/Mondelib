<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Footer.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Settings_Footer extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Manage Footer Settings')
            ->setDescription('You can configure the features for the footer of your website via the settings below.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    // Get available files
    $logoOptions = array('' => 'Text-only (No logo)');
    $bgphoto = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $logoOptions[$file->storage_path] = $file->name;
      $bgphoto[$file->storage_path] = $file->name;
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    
    $this->addElement('Select', 'harmony_footer_enablelogo', array(
      'label' => 'Enable Logo In Footer',
      'description' => 'Do you want to show logo in the footer of your website?',
      'multiOptions' => array(
        1 => "Yes",
        0 => "No",
      ),
      'onchange' => "showFooterLogo(this.value);",
      'value' =>  $settings->getSetting('harmony.footer.enablelogo', 1),
    ));

    $description = $this->getTranslator()->translate('Choose from below the logo to be displayed in the footer of your website. [Note: You can add a new logo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
    
    $this->addElement('Select', 'harmony_footer_logo', array(
        'label' => 'Logo In Footer',
        'description' => $description,
        'multiOptions' => $logoOptions,
        'escape' => false,
        'value' =>  $settings->getSetting('harmony.footer.logo'),
    ));
    $this->harmony_footer_logo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $description = $this->getTranslator()->translate('Choose from below the logo to be displayed in contrast mode in the footer of your website. [Note: You can add a new logo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
    $this->addElement('Select', 'harmony_footer_logocontrast', array(
        'label' => 'Logo In Footer (Contrast Mode)',
        'description' => $description,
        'multiOptions' => $logoOptions,
        'escape' => false,
        'value' =>  $settings->getSetting('harmony.footer.logocontrast'),
    ));
    $this->harmony_footer_logocontrast->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $description = $this->getTranslator()->translate('Choose from below the footer background photo for your website. [Note: You can add a new background photo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
    
    $this->addElement('Select', 'harmony_footer_bgimage', array(
      'label' => 'Footer Background Photo',
      'description' => $description,
      'multiOptions' => $bgphoto,
      'escape' => false,
      'value' =>  $settings->getSetting('harmony.footer.bgimage'),
    ));
    $this->harmony_footer_bgimage->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $description = $this->getTranslator()->translate('Choose from below the footer background photo for contrast mode your website. [Note: You can add a new background photo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
    
    $this->addElement('Select', 'harmony_footer_bgphotocontrast', array(
      'label' => 'Footer Background Photo (Contrast Mode)',
      'description' => $description,
      'multiOptions' => $bgphoto,
      'escape' => false,
      'value' =>  $settings->getSetting('harmony.footer.bgphotocontrast'),
    ));
    $this->harmony_footer_bgphotocontrast->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    
    $this->addElement('Textarea', "harmony_description", array(
        'label' => 'Description In Footer',
        'description' => "Enter the description to displayed in the footer of your website. (It will be displayed in the left side.)",
        'value' => $settings->getSetting('harmony.description', 'Inspire creativity, community, and awareness! SocialEngine PHP - the best choice for community social networking software.'),
    ));

    $aboutLink = $view->baseUrl() . '/admin/menus?name=harmony_aboutlinks_footer';
    $description = $this->getTranslator()->translate('Do you want to enable links for Explore section in the footer of your website? If you choose Yes, then you can configure its menu items from <a href="%1$s" target="_blank">Here</a>.');
    $description = vsprintf($description, array($aboutLink));
    $this->addElement('Radio',
      'harmony_aboutlinksenable',
      array(
        'label' => 'Enable Explore Links',
        'description' => $description,
        'multiOptions' => array('1'=>'Yes','0'=>'No'),
        'value'=> $settings->getSetting('harmony.aboutlinksenable', '1'),
    ));
    $this->harmony_aboutlinksenable->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $fileLink = $view->baseUrl() . '/admin/menus?name=harmony_quicklinks_footer';
    
    $description = $this->getTranslator()->translate('Do you want to enable links for Quick links section in the footer of your website? If you choose Yes, then you can configure its menus from <a href="%1$s" target="_blank">Here</a>.');
    $description = vsprintf($description, array($fileLink));
    
    $this->addElement('Radio',
      'harmony_quicklinksenable',
      array(
        'label' => 'Enable Quick Links',
        'description' => $description,
        'multiOptions' => array('1'=>'Yes','0'=>'No'),
        'value'=> $settings->getSetting('harmony.quicklinksenable', '1'),
    ));
    $this->harmony_quicklinksenable->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));


    $this->addElement('Radio',
      'harmony_helpenable',
      array(
          'label' => 'Enable Footer Menu Links',
          'description' => 'Do you want to enable the default <a href="admin/menus?name=core_footer" target="_blank">Footer Menu Links</a> in the footer of your website?',
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
          'value'=>$settings->getSetting('harmony.helpenable', '1'),
    ));
    $this->harmony_helpenable->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $this->addElement('Radio',
      'harmony_socialenable',
      array(
          'label' => 'Enable Social Site Links Menu',
          'description' => 'Do you want to enable the default <a href="admin/menus?name=core_social_sites" target="_blank">Social Site Links Menu</a> in the footer of your website?',
          'multiOptions' => array('1'=>'Yes','0'=>'No'),
          'value'=>$settings->getSetting('harmony.socialenable', '1'),
    ));
    $this->harmony_socialenable->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Text', "harmony_rightcolhdinglocation", array(
        'label' => 'About Us Location',
        'description' => "Enter the Location to be displayed in About Us section in the footer of your website.",
        'value' => $settings->getSetting('harmony.rightcolhdinglocation', 'Los Angeles, USA'),
    ));
    $this->addElement('Text', "harmony_rightcolhdingemail", array(
        'label' => 'About Us Email',
        'description' => "Enter the Email to be displayed in About Us section in the footer of your website.",
        'value' => $settings->getSetting('harmony.rightcolhdingemail', 'info@abc.com'),
    ));
    $this->addElement('Text', "harmony_rightcolhdingphone", array(
        'label' => 'About Us Phone Number',
        'description' => "Enter the Phone Number to be displayed in About Us section in the footer of your website.",
        'value' => $settings->getSetting('harmony.rightcolhdingphone', '1234567890'),
    ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
