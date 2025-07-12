<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: landingPageSevice.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Widget_LandingPageService extends Engine_Form {

  public function init() {
    // Get available files
    $this->addElement('Text', 'title', array(
      'label' => 'Enter title of this widget.',
    ));
    for($i=1;$i<=8;$i++) {
      $this->addElement('Dummy', "dummy".$i, array(
          'label' => "<span style='font-weight:bold;'>Service ". $i ."</span>",
      ));
      $this->getElement('dummy'.$i)->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false)); 
      $this->addElement('Text', "icon".$i, array(
        'label' => 'Enter font icon class for this service.',
      ));     
      $this->addElement('Text', "featuresheading".$i, array(
        'label' => 'Enter caption of this service.',
      )); 
      $this->addElement('Text', "description".$i, array(
        'label' => 'Enter description of this service.',
      ));
      $this->addElement('Text', "link" . $i, array(
        'label' => 'Enter link of this service.',
      ));
    }
  } 
}
