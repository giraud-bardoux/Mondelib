<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Createslide.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Createslide extends Engine_Form {
  public function init() {
    $this
            ->setTitle('Upload New Photo')
            ->setDescription("Below, upload new photo slide for the welcome slideshow and configure the settings for the slide.")
            ->setAttrib('id', 'form-create-slide')
            ->setAttrib('name', 'sesandroidapp_create_slide')
            ->setAttrib('enctype', 'multipart/form-data')
            //->setAttrib('onsubmit', 'return checkValidation();')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->setMethod('post');
    $slide_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('slide_id', 0);
    if($slide_id)
      $slide = Engine_Api::_()->getItem('sesandroidapp_slide',$slide_id);
      $this->addElement('Hidden', 'type', array(
          'value'=>0
      ));
    if(empty($slide) || !$slide->type == 1){
      $this->addElement('Text', 'title', array(
          'label' => 'Title',
          'description' => ' Enter the title for this photo slide.',
          'allowEmpty' => true,
          'required' => false,
      ));
      $this->addElement('Textarea', 'description', array(
          'label' => 'Description',
          'description' => 'Enter the description for this photo slide.',
          'style'=>'min-height:40px',
          'allowEmpty' => true,
          'required' => false,
      ));
    }
    if (!$slide_id) {
      $required = false;
      $allowEmpty = true;
    } else {
      $required = false;
      $allowEmpty = true;
    }
    $this->addElement('File', 'file', array(
        'label' => 'Choose Photo',
        'description' => 'Choose the photo. [Note: only the photos with extension: “.jpg, .png and .jpeg are allowed. Recommended dimension is : 1536 x 2048 pixels. Recommended extension is: .png"]',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,JPG,JPEG,PNG');
    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Create',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index')),
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
