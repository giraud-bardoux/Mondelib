<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Form_Admin_Widget_BackgroundImage extends Engine_Form {

  public function init() {
  
		$files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    
    if (engine_count($files) > 1) {
      $description = $this->getTranslator()->translate('Choose an image for the page background. The image will only display in the page background. [Note: You can add a new image from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section.');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'bgimage', array(
        'label' => "Choose Background Image",
        'description' => $description,
        'multiOptions' => $files,
      ));
      $this->bgimage->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'bgimage', array(
        'label' => "Choose Background Image",
        'description' => $description,
      ));
      $this->bgimage->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
  }
}
