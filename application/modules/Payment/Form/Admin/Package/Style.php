<?php

class Payment_Form_Admin_Package_Style extends Engine_Form {

  public function init() {
  
    $this->setTitle('Edit Style of This Plan')
        ->setDescription('From here you can change the display style of this subscription plan.');
    
    $this->addElement('Text', 'column_title', array(
      'label' => 'Plan Name',
      'description' => 'This name is for your indication only and will not be shown at the user side.',
      'disabled' => true,
    ));
    $this->column_title->getDecorator("Description")->setOption("placement", "append");
    
    $this->addElement('Text', 'column_width', array(
      'label' => 'Column Width (in pixels)',
    ));
  
    $this->addElement('Text', 'column_margin', array(
      'label' => 'Column Space',
      'description' => 'Enter the margin space to the right of this column in pixels.'
    ));
    $this->column_margin->getDecorator("Description")->setOption("placement", "append");
    
    $this->addElement('Text', 'row_height', array(
      'label' => 'Features Rows Height (in pixels)',
    ));
    
    $this->addElement('Text', 'row_border_color', array(
      'label' => 'Features Rows Border Color',
      'class' => 'SEcolor',
       'value' => '#e2e4e6'
    ));

    $this->addElement('Text', 'column_row_color', array(
      'label' => 'Features Rows Background Color',
      'class' => 'SEcolor',
      'value' => '#ffffff'
    ));
    
    $this->addElement('Text', 'column_row_text_color', array(
      'label' => 'Features Rows Font Color',
      'class' => 'SEcolor',
      'value' => '#5f727f'
    ));
    
    $this->addElement('Select', 'icon_position', array(
      'label' => 'Select Text Alignment',
      'multioptions' => array('1' => 'Center', '0' => 'Left'),
      'value' => '0'
    ));
    
    $this->addElement('Checkbox', 'show_highlight', array(
      'label' => "Do you want to highlight this column?",
      'description'=> 'Highlight This Column',
      'value' => '',
    ));

    $this->addElement('Select', 'show_label', array(
      'label' => 'Show Side Label',
      'description' => 'Do you want to show side label for this plan? If you choose “Yes”, then you will be able to configure details for the highlight side label.',
      'multioptions' => array('1' => 'Yes', '0' => 'No'),
      'value'=>0,
      'onclick' => 'showLabel(this.value);'
    ));

    $this->addDisplayGroup(array('column_title', 'column_width', 'column_margin','row_height', 'row_border_color', 'column_row_color', 'column_row_text_color', 'icon_position', 'show_highlight', 'show_label'), 'features_content', array('disableLoadDefaultDecorators' => true, 'legend' => 'General'));
    $features_content = $this->getDisplayGroup('features_content');
    $features_content->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'features_content'))));



    $this->addElement('Text', 'label_text', array(
      'label' => 'Label Text',
      'maxlength' => '9',
      'description' => 'Enter the text for the label which will be shown as titled strip.'
    ));
    $this->label_text->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('Text', 'label_color', array(
      'label' => 'Label Background Color',
      'class' => 'SEcolor',
      'value' => '#2491eb'
    ));

    $this->addElement('Text', 'label_text_color', array(
      'label' => 'Label Font Color',
      'class' => 'SEcolor',
      'value' => '#ffffff'
    ));

    $this->addElement('Select', 'label_position', array(
      'label' => 'Label Alignment',
      'multioptions' => array('1' => 'Right', '0' => 'Left'),
      'value' => '1',
    ));
    
    $this->addDisplayGroup(array('label_text', 'label_color', 'label_text_color','label_position'), 'title_labeled', array('disableLoadDefaultDecorators' => true));
    $title_labeled = $this->getDisplayGroup('title_labeled');
    $title_labeled->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'title_labeled'))));

    $this->addElement('Text', 'column_descr_height', array(
      'label' => 'Plan Description Height (in pixels)',
    ));
    
    $this->addElement('Text', 'column_color', array(
      'label' => 'Column Header Background Color',
      'class' => 'SEcolor',
      'value' => '#f2f2f2'
    ));
    
    $this->addElement('Text', 'column_text_color', array(
      'label' => 'Column Header Font Color',
      'class' => 'SEcolor',
      'value' => '#5f727f'
    ));
    
    $this->addDisplayGroup(array('column_descr_height', 'column_color', 'column_text_color'), 'column_header', array('disableLoadDefaultDecorators' => true, 'legend' => 'Column Header of This Plan'));
    $column_header = $this->getDisplayGroup('column_header');
    $column_header->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'column_header'))));

    $this->addElement('Text', 'footer_bg_color', array(
      'label' => 'Button Background Color',
      'class' => 'SEcolor',
      'value' => '#2491eb'
    ));
    
    $this->addElement('Text', 'footer_text_color', array(
      'label' => 'Button Font Color',
      'class' => 'SEcolor',
      'value' => '#ffffff'
    ));
    
    $this->addDisplayGroup(array('footer_bg_color', 'footer_text_color'), 'signup_upgrade_button', array('disableLoadDefaultDecorators' => true, 'legend' => 'Signup / Upgrade Button'));
    $signup_upgrade_button = $this->getDisplayGroup('signup_upgrade_button');
    $signup_upgrade_button->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'signup_upgrade_button'))));
    
    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
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
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
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
