<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminMenuController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sesiosapp_AdminMenuController extends Core_Controller_Action_Admin {
 public function indexAction() {
  $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesiosapp_admin_main', array(), 'sesiosapp_admin_main_menu');
  $menus = Engine_Api::_()->getDbTable('menus','sesapi')->getMenus(array('device'=>1));   
  $this->view->menus = $menus;
 }
 
 
 public function createAction(){
   $this->_helper->layout->setLayout('admin-simple');
  $id = $this->_getParam('id','');
  $this->view->form = $form = new Sesiosapp_Form_Admin_Menu_Create();
  
  if($id){
    $item = Engine_Api::_()->getItem('sesapi_menu',$id);  
    $form->populate($item->toArray());
    $form->submit->setLabel('Save Changes');
    $form->setTitle("Edit ". $item->label);
    $form->setDescription("Edit this content here.");
    if($item->is_delete == 0){
      $form->removeElement('url');  
    }
  }    
  
  if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
  {
    $db = Engine_Api::_()->getDbtable('menus', 'sesapi')->getAdapter();
    $db->beginTransaction();
    try
    {
      if(empty($item)){
        $itemTable = Engine_Api::_()->getItemTable('sesapi_menu');
        $item = $itemTable->createRow();
      }
      $values = $form->getValues();
      $item->setFromArray($values);
      if(!$item->class)
        $item->is_delete = 1;
      $item->device = 1;
      $item->save();
      if(!$item->order)
       $item->order = $item->getIdentity();
      $item->save();
      if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != ''){
        $file_id = $this->setPhoto($form,$item);
        $item->file_id = $file_id;
        $item->save();
      }
      $db->commit();
    }catch(Exception $e){
      $db->rollBack();
      throw $e;
    }
    $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Menu created Successfully.')
      ));
  }
     
 }
 public function setPhoto($form,$item){
    $storage = Engine_Api::_()->getItemTable('storage_file');
    $filename = $storage->createFile($form->file, array(
        'parent_id' => $item->getIdentity(),
        'parent_type' => 'sesapi_menu',
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
    ));
    $item->file_id = $filename->file_id;
    $item->save();
    return $filename->file_id;    
 }

  public function orderAction() {
    if (_ENGINE_ADMIN_NEUTER) {
      return;
    }
    $table = Engine_Api::_()->getDbTable('menus', 'sesapi');
    $results = $table->fetchAll($table->select()->where('device =?',1));
    $orders = $this->getRequest()->getParam('order');
    foreach ($results as $result) {
      $key = array_search ('slide_'.$result->getIdentity(), $orders);
      $result->order = $key+1;
      $result->save();
    }
    return;
  }

 public function statusAction(){
    if (!_ENGINE_ADMIN_NEUTER) {
      $id = $this->_getParam('id');
      if (!empty($id)) {
        
        $item = Engine_Api::_()->getItem('sesapi_menu', $id);
        $item->status = !$item->status;
        $item->save();
      }
    }
    $this->_redirect('admin/sesiosapp/menu');
 }
 public function infoAction(){
   $id = $this->_getParam('id');
   $this->view->item = Engine_Api::_()->getItem('sesapi_menu', $id);
 }
 public function deleteAction(){
    
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Sesapi_Form_Admin_Delete();
    $form->setTitle('Delete This Menu?');
    $form->setDescription('Are you sure that you want to delete this Menu? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');

    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $chanel = Engine_Api::_()->getItem('sesapi_menu', $id)->delete();
      $db = Engine_Db_Table::getDefaultAdapter();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Menu Deleted Successfully.')
      ));
    }
 }
}
