<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Global.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    // Get available files
    $logoOptions = array('' => 'Text-only (No logo)');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $logoOptions[$file->storage_path] = $file->name;
    }

    $this->addElement('Radio', 'harmony_changelanding', array(
      'label' => 'Set Landing Page of Harmony Theme',
      'description' => 'Do you want to set the Landing Page from this theme and replace the current Landing page with one of the landing page design from this theme? (If you choose Yes and save changes, then later you can manually make changes in the Landing page from Layout Editor. Back up page of your current landing page will get created with the name "Backup - Landing Page".)',
      'onclick' => 'confirmChangeLandingPage(this.value)',
      'multiOptions' => array(
        '1' => 'Yes, set the Landing Page',
        '0' => 'No',
      ),
      'value' => Engine_Api::_()->getDbTable('settings', 'core')->getDbSettings('harmony.changelanding') ? Engine_Api::_()->getDbTable('settings', 'core')->getDbSettings('harmony.changelanding'): 0,
    ));

    $this->addElement('MultiCheckbox', 'harmony_headerloggedinoptions', array(
      'label' => 'Header Options for Logged in Members',
      'description' => 'Choose from the below options to be available in the header to logged in members on your website.',
      'multiOptions' => array(
          'search' => 'Search',
          'miniMenu' => 'Mini Menu',
          'mainMenu' =>'Main Menu',
          'logo' =>'Logo',
      ),
      'value' => unserialize($settings->getSetting('harmony.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}')),
    ));

    $this->addElement('MultiCheckbox', 'harmony_headernonloggedinoptions', array(
      'label' => 'Header Options for Non-Logged in Members',
      'description' => 'Choose from the below options to be available in the header to non-logged in members on your website.',
      'multiOptions' => array(
          'search' => 'Search',
          'miniMenu' => 'Mini Menu',
          'mainMenu' =>'Main Menu',
          'logo' =>'Logo',
      ),
      'value' => unserialize($settings->getSetting('harmony.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}')),
    ));

    $description = $this->getTranslator()->translate('Choose from below the logo to be displayed in the header of your website. [Note: You can add a new logo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
      
    $this->addElement('Select', 'harmony_logo', array(
      'label' => 'Logo In Header',
      'description' => $description,
      'multiOptions' => $logoOptions,
      'value'=>$settings->getSetting("harmony_logo",'')
    ));
    $this->harmony_logo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    $description = $this->getTranslator()->translate('Choose from below the logo to be displayed in contrast mode in the header of your website. [Note: You can add a new logo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>.]');
    $description = vsprintf($description, array($fileLink));
    $this->addElement('Select', 'harmony_logocontrast', array(
      'label' => 'Logo In Header (Contrast Mode)',
      'description' => $description,
      'multiOptions' => $logoOptions,
      'value' => $settings->getSetting("harmony.logocontrast",'')
    )); 
    $this->harmony_logocontrast->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Text', 'themewidget_radius', array(
      'label' => 'Widget Corner Radius',
      'description' => 'Enter the corner radius of widgets on your website in px.',
      'value' => $settings->getSetting('themewidget.radius', 10),
    ));
    
    $this->addElement('Select', 'accessibility_options', array(
      'label' => 'Show Accessibility Options',
      'multiOptions' => array('1'=>'Yes','0'=>'No'),
      'value'=> $settings->getSetting("accessibility_options",1)
    ));
    
    $this->addElement('Text', 'menu_count', array(
      'label' => 'Main Menus Count',
      'requried' => true,
      'allowEmpty' => false,
      'validators' => array(
        new Engine_Validate_AtLeast(1),
      ),
      'description' => 'Enter number of main menu items to be displayed before "More" dropdown menu occurs.',
      'value' => $settings->getSetting('menu.count', 4),
    ));
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
