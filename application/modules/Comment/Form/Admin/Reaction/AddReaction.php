<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AddReaction.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Comment_Form_Admin_Reaction_AddReaction extends Engine_Form {

  public function init() {
  
    $this->setTitle('Add a New Reaction')
            ->setDescription('Here, you can add new reactions which will show to users when they mouse over on Like button.');

    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if(empty($id)) {
      $re = true;
      $all = false;
    } else {
      $re = false;
      $all = true;
    }
      
    $this->addElement('Text', 'title', array(
      'label' => 'Reaction Name',
      'description' => 'Enter the name for the reaction. (This name will also come when users mouse over the reaction icon.)',
      'required'=>true,
      'allowEmpty'=>false,
    ));     

    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'accept' => 'image/*',
        'label' => 'Reaction Photo',
        'description' => 'Upload a photo for this reaction. [Note: photos with extension: "jpg, png, jpeg and gif" only.]',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
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
