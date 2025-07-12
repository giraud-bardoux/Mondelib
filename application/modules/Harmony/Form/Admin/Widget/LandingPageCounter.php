<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: LandingPageCounter.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Widget_LandingPageCounter extends Engine_Form {

  public function init() {
  
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();

    $this->addElement('Text', 'title', array(
      'label' => 'Enter title of this widget.',
    ));
    $this->addElement('Text', 'btntext', array(
      'label' => 'Enter CTA button text',
    ));
     $this->addElement('Text', 'btntextlink', array(
      'label' => 'Enter CTA button link',
    ));   
    for($i=1;$i<=5;$i++) {
      $this->addElement('Dummy', "dummy".$i, array(
          'label' => "<span style='font-weight:bold;'>Counter ". $i ."</span>",
      ));
      $this->getElement('dummy'.$i)->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
      $this->addElement('Text', "icon".$i, array(
        'label' => 'Enter font icon class for this counter.',
      )); 
      $this->addElement('Text', "count".$i, array(
        'label' => 'Enter count for this counter.',
      )); 
      $this->addElement('Text', "text".$i, array(
        'label' => 'Enter caption for this counter.',
      )); 
    }
  } 
}
