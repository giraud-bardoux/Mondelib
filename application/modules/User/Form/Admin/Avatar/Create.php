<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_User
 * @package    User
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php  2018-09-29 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class User_Form_Admin_Avatar_Create extends Engine_Form {

  public function init() {
  
    $this->setTitle('Upload New Avatar Image')
            ->setDescription('');
            
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);

    if(!$id){
      $re = true;
      $all = false;  
    }else{
      $re = false;
      $all = true;
    }
    
    if(empty($id)) {
      $this->addElement('File', 'file', array(
          'allowEmpty' => $all,
          'required' => $re,
          'label' => 'Avatar Image',
          'description' => 'Choose the Avatar image you want to upload on your website.',
          'accept' => 'image/*',
      ));
      $this->file->addValidator('Extension', false, 'gif, GIF, png, PNG, jpg, JPG, JPEG, jpeg, webp');
    }
    $this->addElement('Button', 'submit', array(
      'label' => 'Upload',
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
