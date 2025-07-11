<?php

class Payment_Form_Admin_Package_Features extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setDescription('From here, you can manage content for all the features of this subscription plans on your website.');

    $rowCount = 15;

    for ($i = 1; $i <= $rowCount; $i++) {
      $id = "row".$i;
      $iconId = 'row' . $i . '_file_id';
      $text = $id . '_text';
      $description = $id . '_description';
      
      $this->addElement('Text', $text, array(
        'label' => "Description",
        'description' => "Enter the description of this feature.",
        'maxlength' => '120',
      ));
      $this->$text->getDecorator("Description")->setOption("placement", "append");
      
      $this->addElement('Text', $description, array(
        'label' => "Hint",
        'description' => "Enter the hint text. [A question-mark icon will be shown to display this text on mouse-over of the icon.]",
        'maxlength' => '120',
      ));
      $this->$description->getDecorator("Description")->setOption("placement", "append");

      $this->addElement('Text', $iconId, array(
        'label' => "Font Icon",
        'description' => "Enter the font icon.",
        'maxlength' => '120',
      ));
      $this->$iconId->getDecorator("Description")->setOption("placement", "append");
      
      $this->addDisplayGroup(array($text, $description, $iconId), $id, array('disableLoadDefaultDecorators' => true,'legend' => 'Feature '.$i));
      $this->setDisplayGroupDecorators(array('FormElements', 'Fieldset'));
    }
    
    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index', 'package_id' => null)),
      'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      )
    ));
  }
}
