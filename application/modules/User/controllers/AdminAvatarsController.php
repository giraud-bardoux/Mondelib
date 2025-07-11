<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_User
 * @package    User
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminManageController.php  2018-09-29 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class User_AdminAvatarsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $avatar = Engine_Api::_()->getItem('user_avatar', $value);
          if($avatar)
          $avatar->delete();
        }
      }
    }

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_signup', array(), 'core_admin_manageavatars');

    $avatarsTable = Engine_Api::_()->getDbTable('avatars','user');
    $rName = $avatarsTable->info('name');

    $select = $avatarsTable->select()
            ->from($rName)
            ->order('order ASC' );
    $this->view->paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }

  public function createAction() {

    $id = $this->_getParam('id',false);

    $this->view->form = $form = new User_Form_Admin_Avatar_Create();
    if($id){
      $item = Engine_Api::_()->getItem('user_avatar',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit this Avatar Avatar');
      $form->submit->setLabel('Save Changes');
    }

    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    if(!$form->isValid($this->getRequest()->getPost()) && !$id) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {

      $avatarsTable = Engine_Api::_()->getDbtable('avatars', 'user');

      unset($values['file']);
      if(empty($id))
        $item = $avatarsTable->createRow();
      $item->setFromArray($values);
      $item->save();
      $item->order = $item->avatar_id;
      $item->save();

      if(!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($form->file, array(
          'parent_id' => $item->getIdentity(),
          'parent_type' => $item->getType(),
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));
        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }

      $db->commit();
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Avatar Image Uploaded Successfully.');
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh'=> true,
      'messages' => array($message)
    ));
  }

  public function enabledAction() {

    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('user_avatar', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect('admin/user/avatars');
  }
  
  
  public function orderAction() {
    $table = Engine_Api::_()->getDbTable('avatars', 'user');
    $results = $table->fetchAll($table->select());
    $orders = $this->getRequest()->getParam('order');

    foreach ($results as $result) {
      $key = array_search ('manageimages_'.$result->getIdentity(), $orders);
      $result->order = $key+1;
      $result->save();
    }
    return;
  }

  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new User_Form_Admin_Avatar_Delete();

    $form->setTitle('Delete This Avatar Image');
    $form->setDescription('Are you sure that you want to delete this avatar image? It will not be recoverable after being deleted.');

    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->query('UPDATE `engine4_users` SET `photo_id` = "0", `avatar_id` = "0" WHERE `engine4_users`.`avatar_id` = "'.$id.'";');
      
      $avatar = Engine_Api::_()->getItem('user_avatar', $id);
      $avatar->delete();
      $message = Zend_Registry::get('Zend_Translate')->_('Avatar Image Deleted Successfully.');
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array($message)
      ));
    }
  }
}
