<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Background.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Form_Admin_Background extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('App Background Images Settings')
            ->setDescription("");

    //New File System Code
    $default_photos_main = array();
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $default_photos_main[$file->storage_path] = $file->name;
    }
    
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    if (engine_count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesiosapp/externals/images/login.jpeg'=> ''),$default_photos_main);
      $this->addElement('Select', 'sesiosapp_login_background_image', array(
        'label' => 'Login',
        'description' => 'Choose login screen background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
        'multiOptions' => $default_photos,
        'value' => $settings->getSetting('sesiosapp.login.background.image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the login screen. Please upload the Photo to be chosen for login screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesiosapp_login_background_image', array(
          'label' => 'Login',
          'description' => $description,
      ));
    }
    $this->sesiosapp_login_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
		
    
    if (engine_count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesiosapp/externals/images/forgot.jpeg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesiosapp_forgot_background_image', array(
          'label' => 'Forgot Password',
          'description' => 'Choose forgot password background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesiosapp_forgot_background_image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the forgot password screen. Please upload the Photo to be chosen for forgot password screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesiosapp_forgot_background_image', array(
          'label' => 'Forgot Password',
          'description' => $description,
      ));
    }
    $this->sesiosapp_forgot_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
 
    if (engine_count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesiosapp/externals/images/rateus.jpg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesiosapp_rateus_background_image', array(
          'label' => 'Rate Us',
          'description' => 'Choose rate us background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
          'multiOptions' => $default_photos,
          'value' => $settings->getSetting('sesiosapp_rateus_background_image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the rate us screen. Please upload the Photo to be chosen for rate us screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesiosapp_rateus_background_image', array(
          'label' => 'Rate Us',
          'description' => $description,
      ));
    }
    $this->sesiosapp_rateus_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    if (engine_count($default_photos_main) > 0) {
			$default_photos = array_merge(array('application/modules/Sesiosapp/externals/images/dashboardmenu.jpg'=>''),$default_photos_main);
      $this->addElement('Select', 'sesiosapp_dashboardmenu_background_image', array(
        'label' => 'Dashboard Menu',
        'description' => 'Choose dashboard menu background image for your app. [Note: You can add a new photo from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>.]',
        'multiOptions' => $default_photos,
        'value' => $settings->getSetting('sesiosapp_dashboardmenu_background_image'),
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo in the File & Media Manager for the dashboard menu screen. Please upload the Photo to be chosen for dashboard menu screen from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'sesiosapp_dashboardmenu_background_image', array(
          'label' => 'Dashboard Menu',
          'description' => $description,
      ));
    }
    $this->sesiosapp_dashboardmenu_background_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    
    //Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
