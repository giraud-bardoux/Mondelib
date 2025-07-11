<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Filtercreate.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Comment_Form_Admin_Emotion_Filecreate extends Engine_Form {

  public function init() {
  
    $this->setTitle('Add Sticker')
            ->setDescription('');
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if(empty($id)) {
      $re = true;
      $all = false;
    } else {
      $re = false;
      $all = true;
    }
    
    $this->addElement('File', 'file', array(
        'allowEmpty' => $all,
        'required' => $re,
        'accept'=>"image/*",
        'label' => 'Upload Sticker',
        'description' => 'Upload a sticker [Note: sticker (photos) with extension: "jpg, png, jpeg and gif" only.]',
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');
    
    //Search options
    $this->addElement('Text', 'tags',array(
      'label'=>'Sticker Tags',
      'autocomplete' => 'off',
      'description' => 'Enter the tags for this sticker and separate them by commas. (Note: These tags will be used when users search stickers in the sticker dropdown.)',
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
      ),
    ));
    $this->tags->getDecorator("Description")->setOption("placement", "append");

    
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
