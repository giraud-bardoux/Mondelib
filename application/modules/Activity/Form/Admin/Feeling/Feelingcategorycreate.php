<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Feelingcategorycreate.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Form_Admin_Feeling_Feelingcategorycreate extends Engine_Form {

  public function init() {
  
    $this->setTitle('Create New Category')
            ->setDescription('Here, you can create a new Feeling/Activity category which will be displayed when users will click on the Feeling/Activity option.');
            
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
      
    $this->addElement('Text', 'title', array(
      'label' => 'Category Title',
      'required'=>true,
      'allowEmpty'=>false,
      'description' => '',
    ));
    
    $this->addElement('Select', "type", array(
      'label' => 'Feeling/Activity Type',
      'description' => 'Choose the type for this Feeling/Activity category.',
      'allowEmpty' => false,
      'required' => true,
      'multiOptions' => array(
        '1' => 'List Type',
        '2' => "Module Type",
      ),
    ));

    if(!$id){
      $re = true;
      $all = false;  
    }else{
      $re = false;
      $all = true;
    }
    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'accept'=>"image/*",
        'label' => 'Category Icon',
        'description' => 'Upload a category icon [Note: Icons with extension: "jpg, png, jpeg and gif" only. Recommended dimension is 32*32 px.]',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');
    
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
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
