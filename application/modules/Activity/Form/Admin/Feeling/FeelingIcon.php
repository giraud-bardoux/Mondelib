<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: FeelingIcon.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Form_Admin_Feeling_FeelingIcon extends Engine_Form {

  public function init() {
  
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type', 1);
    
    if($type == 1) { 
      $this->setTitle('Add Feeling/Activity List Item')
              ->setDescription('');
    } else {
      $this->setTitle('Add Modules for Feeling/Activity')
              ->setDescription('');
    }
    
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    
    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type', 1);
    
    $feeling_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('feeling_id', 0);
    
    if($type == 1) {
      $this->addElement('Text', 'title', array(
        'label' => 'Feeling/Activity Title',
        'required' => true,
        'allowEmpty' => false,
        'description' => '',
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
          'label' => 'Feeling/Activity Icon',
          'description' => 'Upload a feeling/activity icon [Note: Icons with extension: "jpg, png, jpeg and gif" only. Recommended dimension is 32*32 px.]',
      ));
      $this->file->addValidator('Extension', false, 'jpg,png,jpeg,gif,GIF,PNG,JPG,JPEG');
    } elseif($type == 2) {
    
      $this->addElement('Text', 'title', array(
        'label' => 'Title (This is for indicative purpose in the admin panel only.)',
        'required' => true,
        'allowEmpty' => false,
        'description' => '',
      ));

      $integrateModules =  Engine_Api::_()->getDbTable('integratemodules', 'core')->integrateModules();
      $moduleArray = array();
      if (engine_count($integrateModules) > 0) {
        foreach ($integrateModules as $integrateModule) {
          $moduleArray[$integrateModule['content_type']] = $integrateModule['module_title'];
        }
      }

      $this->addElement('Select', 'resource_type', array(
        'label' => 'Select Module',
        'description' => 'Select the module from below which you want to add in this category. When users will select this category then it will show all contents of this module.',
        'multiOptions' => $moduleArray,
        'required' => true,
        'allowEmpty' => false,
      ));
    }
    
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
