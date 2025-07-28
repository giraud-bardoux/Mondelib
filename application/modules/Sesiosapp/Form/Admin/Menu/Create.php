<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Create.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Form_Admin_Menu_Create extends Engine_Form {
  public function init() {
    $this
            ->setTitle('Add New “Menu Item” or “Category”')
            ->setDescription("Here you can create menu items or categories. If you wish you can place the menu items added from this form under suitable category using drag and drop as per your requirement.")
            ->setAttrib('id', 'form-create-menu')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
    $this->setMethod('post');
    $this->addElement('Select', 'type', array(
        'label' => 'Content Type',
        'description' => 'Choose the content type to be created from this form.',
        'onchange'=>'hideFun(this.value)',
        'allowEmpty' => true,
        'required' => false,
        'multiOptions'=>array('0'=>'Category',1=>'Menu Item'),
        'value'=>1
    ));
    $this->addElement('Text', 'label', array(
        'label' => 'Content Title',
        'description' => 'Enter the title of the content.',
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->addElement('Text', 'module', array(
        'label' => 'Module Name',
        'description' => 'Enter the module name with which this menu item is related. (This is for your indicative purpose only.)',
        'allowEmpty' => true,
        'required' => false,
    ));
    $this->addElement('Select', 'visibility', array(
        'label' => 'Content Visibility',
        'description' => 'Choose from below options, to whom you want to display this menu item.” => “Choose the visibility of this content.',
        'allowEmpty' => false,
        'required' => true,
        'multiOptions'=>array('1'=>'Only logged-in users','2'=>'Only non-logged in users','0'=>'Both logged-in and non logged-in users'),
        'value'=>0 
    ));
    
    $this->addElement('Select', 'status', array(
        'label' => 'Status',
        'description' => 'Choose the status of this content.',
        'allowEmpty' => true,
        'required' => false,
        'multiOptions'=>array('0'=>'Disable',1=>'Enable'),
        'value'=>1
    ));    
    $menu_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
    if (!$menu_id) {
      $required = false;
      $allowEmpty = true;
    } else {
      $required = false;
      $allowEmpty = true;
    }
    $this->addElement('File', 'file', array(
        'label' => 'Menu Item Icon',
        'description' => 'Upload an icon for this menu item.',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
    ));
    $this->file->addValidator('Extension', false, 'jpg,png,jpeg');
		
    if (!engine_count($_POST) && empty($_POST['type'])) {
      $required = false;
      $allowEmpty = true;
    } else {
      $required = false;
      $allowEmpty = true;
    }
    
    //login button code
    $this->addElement('Text', 'url', array(
        'label' => 'Menu Item URL',
        "description" => "The menu items created by you will be opened in the webview. So please mention complete URL of the page here, on which this menu item will redirect.",
        'allowEmpty'=>$allowEmpty,
        'required'=>$required,
        'description' => 'The menu items created by you will be opened in the webview. So, please mention complete URL of the page on which this menu item will redirect.',
    ));
    
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
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
