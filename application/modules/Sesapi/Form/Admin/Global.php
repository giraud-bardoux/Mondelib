<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Global.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Form_Admin_Global extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/support/create-new-ticket/" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    // $this->addElement('Text', "sesapi_licensekey", array(
    //     'label' => 'Enter License key',
    //     'description' => $descriptionLicense,
    //     'allowEmpty' => false,
    //     'required' => true,
    //         'value' => 'XXXX-XXXX-XXXX-XXXX',
    // ));
    // $this->getElement('sesapi_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
		if ($settings->getSetting('sesapi.pluginactivated')) {

      $this->addElement('Radio', 'sesapi_headerfooter_enable', array(
        'label' => 'Disable Header & Footer in Webview ',
        'description' => 'Do you want to disable the header and footer of website in Webview of the app?',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No',
        ),
        'value' => $settings->getSetting('sesapi.headerfooter.enable', 1),
      ));


     /* $this->addElement('Radio', 'sesapi_update_enable', array(
        'label' => 'Configure "index.php" File',
        'description' => 'To enable Rest API calling to make the native mobile apps work, the index.php file at the root requires some changes. If somehow the code in this file does not have the API calling, then you need to add the code again into this file, and for that please use any of the 2 methods mentioned below:<br />
        Method 1: Automatic Updation of code into the files - <a href="javascript:;" onClick="openURL(\'automatic\');return false;" >Click Here</a> to do it automatically.<br />
        Method 2:  Manual Updation of code - If you want to manually write the code, then <a href="javascript:;" onClick="openURL(\'manual\');return false;" >Click Here</a> to follow the steps.',

        'value' => $settings->getSetting('sesapi.update.enable', 0),
      ));
     $this->getElement('sesapi_update_enable')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
      */
     //tip message
     //$androidEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesandoidapp');
     //$iosEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesiosapp');
     //$moduleEnable = $androidEnable || $iosEnable;
     /*if($moduleEnable){
        $this->addElement('Radio', 'sesapi_tip_enable', array(
          'label' => 'Enable App Installation Reminder',
          'description' => 'Do you want to enable the App Installation reminder when someone opens your website in mobile browser? If you choose Yes, then a tip will be shown on the top of your website in mobile browser with the download link to the iOS / Android app depending on the phone of the user. The link of the apps will only show if you have the valid apps in the respective app stores.',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
          ),
          'onclick'=>'tipMessage(this.value);',
          'value' => $settings->getSetting('sesapi.tip.enable', 1),
        ));
        $this->getElement('sesapi_tip_enable')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        $this->addElement('Text', "sesapi_tip_title", array(
          'label' => 'Tip Title',
          'description' => "Enter the title for the tip which will be shown to the users when they view your website in mobile browser.",
          'allowEmpty' => true,
          'required' => false,
          'value' => $settings->getSetting('sesapi.tip.title'),
        ));
        $this->getElement('sesapi_tip_title')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        $this->addElement('Text', "sesapi_tip_description", array(
          'label' => 'Tip Description',
          'description' => "Enter the description for the tip which will be shown to the users when they view your website in mobile browser.",
          'allowEmpty' => true,
          'required' => false,
          'value' => $settings->getSetting('sesapi.tip.description'),
        ));
        $this->getElement('sesapi_tip_description')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        if($iosEnable){
          $this->addElement('Text', "sesapi_tip_iosid", array(
            'label' => 'iOS App Id',
            'description' => "Enter the id of the iOS App in the iTunes store.",
            'allowEmpty' => true,
            'required' => false,
            'value' => $settings->getSetting('sesapi.tip.iosid'),
          ));
          $this->getElement('sesapi_tip_iosid')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
        }

        if($androidEnable){
          $this->addElement('Text', "sesapi_tip_androidid", array(
            'label' => 'Android App Id',
            'description' => "Enter the id of the Android App in the iTunes store.",
            'allowEmpty' => true,
            'required' => false,
            'value' => $settings->getSetting('sesapi.tip.androidid'),
          ));
          $this->getElement('sesapi_tip_androidid')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
        }
        $this->addElement('Text', "sesapi_tip_buttoninstall", array(
          'label' => 'Install Button',
          'description' => "Text for the install button",
          'allowEmpty' => true,
          'required' => false,
          'value' => $settings->getSetting('sesapi.tip.buttoninstall','INSTALL'),
        ));
        $this->getElement('sesapi_tip_buttoninstall')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        $this->addElement('Text', "sesapi_tip_daysHidden", array(
          'label' => 'Closed Duration',
          'description' => "Duration to hide the tip after being closed",
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sesapi.tip.daysHidden','15'),
        ));
        $this->getElement('sesapi_tip_daysHidden')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        $this->addElement('Text', "sesapi_tip_daysReminder", array(
          'label' => '"View" Duration',
          'description' => "Duration to hide the tip after \"INSTALL\" is clicked",
          'allowEmpty' => false,
          'required' => true,
          'value' => $settings->getSetting('sesapi.tip.daysReminder','90'),
        ));
        $this->getElement('sesapi_tip_daysReminder')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

        $default_photos_main = array();
        $path = new DirectoryIterator(APPLICATION_PATH . '/public/admin/');
        foreach ($path as $file) {
          if ($file->isDot() || !$file->isFile())
            continue;
          $base_name = basename($file->getFilename());
          if (!($pos = strrpos($base_name, '.')))
            continue;
          $extension = strtolower(ltrim(substr($base_name, $pos), '.'));
          if (!engine_in_array($extension, array('gif', 'jpg', 'jpeg', 'png')))
            continue;
          $default_photos_main['public/admin/' . $base_name] = $base_name;
        }
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $fileLink = $view->baseUrl() . '/admin/files/';

        if (engine_count($default_photos_main) > 0) {
          $default_photos = array_merge(array(''=>''),$default_photos_main);
          $this->addElement('Select', 'sesapi_tip_image', array(
              'label' => 'App Icon',
              'description' => 'Choose app icon for the tip on your website. [Note: You can add a new icon from the "File & Media Manager" section from here: <a target="_blank" href="' . $fileLink . '">File & Media Manager</a>. Leave the field blank if you do not want to change app icon.]',
              'multiOptions' => $default_photos,
              'value' => $settings->getSetting('sesapi.tip.image'),
          ));
        } else {
          $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no icon for  tip on your website. Icon to be chosen for tip on your website should be first uploaded from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section. => There are currently no icon in the File & Media Manager for the  tip on your website. Please upload the Icon to be chosen for tip on your website from the "Layout" >> "<a target="_blank" href="' . $fileLink . '">File & Media Manager</a>" section.') . "</span></div>";
          //Add Element: Dummy
          $this->addElement('Dummy', 'sesapi_tip_image', array(
              'label' => 'App Icon',
              'description' => $description,
          ));
        }
        $this->sesapi_tip_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

     }*/


     //Add submit button
     $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
     ));
    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
        'label' => 'Activate This Plugin',
        'type' => 'submit',
        'ignore' => true
      ));
    }
  }
}
