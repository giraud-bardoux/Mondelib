<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: LandingPageFeatures.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Widget_LandingPageFeatures extends Engine_Form {

  public function init() {

    $files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();

    $this->addElement('Text', 'title', array(
      'label' => 'Enter title of this widget.',
    ));
    
    $this->addElement('Select', "leftphoto", array(
      'label' => 'Choose the image to be shown in the left side of this widget. Note: You can add a new image from the "File & Media Manager" section.',
      'multiOptions' => $files,
    ));

    for($i=1;$i<=4;$i++) {
      $this->addElement('Dummy', "dummy".$i, array(
          'label' => "<span style='font-weight:bold;'>Feature ". $i ." 	</span>",
      ));
      $this->getElement('dummy'.$i)->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
      
      $this->addElement('Select', "photo".$i, array(
          'label' => 'Choose Icon (image) for this feature. Note: You can add a new image from the "File & Media Manager" section.',
          'multiOptions' => $files,
      ));
      $this->addElement('Text', "featuresheading".$i, array(
        'label' => 'Enter caption for this feature.',
      )); 
      $this->addElement('Text', "description".$i, array(
        'label' => 'Enter description for this feature.',
      ));
      $this->addElement('Text', "link" . $i, array(
        'label' => 'Enter link for this feature.',
      ));
    }
  } 
}
