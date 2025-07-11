<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesatoz
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Icon.php 2018-10-05  00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Core_Form_Admin_Language_EditIcon extends Engine_Form {

  public function init() {

    $this->setTitle('Choose an Image');
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    $fileOptions = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $fileOptions[$file->storage_path] = $file->name;
    }
    
    if (engine_count($fileOptions) > 1) {
      
      $description = $this->getTranslator()->translate('Choose an image to show with this language. This image will show in both user panel and admin panel of your site. [Note: You can add a new image from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section. If you leave the field blank then nothing will show.]');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'icon', array(
        'description' => $description,
        'multiOptions' => $fileOptions,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

      $this->addElement('Button', 'submit', array(
        'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
      ));

      $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'onclick' => 'javascript:parent.Smoothbox.close()',
          'link' => true,
          'prependText' => ' or ',
          'decorators' => array(
              'ViewHelper',
          ),
      ));

      $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
          'decorators' => array(
              'FormElements',
              'DivDivDivWrapper',
          ),
      ));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'icon', array(
        'description' => $description,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
      
      $this->addElement('Cancel', 'cancel', array(
          'label' => 'Cancel',
          'onclick' => 'javascript:parent.Smoothbox.close()',
          'link' => true,
          'decorators' => array(
              'ViewHelper',
          ),
      ));
    }
  }
}
