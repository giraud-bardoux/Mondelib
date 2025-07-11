<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminEmojiController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_AdminEmojiController extends Core_Controller_Action_Admin {

  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'activity_admin_main_emojisettings');

    $this->view->paginator = Engine_Api::_()->getDbTable('emojis','activity')->getPaginator(array('admin' => 1));
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }
  
  public function orderManageEmojiiconsAction() {
    $table = Engine_Api::_()->getDbTable('emojiicons', 'activity');
    $results = $table->fetchAll($table->select());
    $orders = $this->getRequest()->getParam('order');

    foreach ($results as $result) {
      $key = array_search ('manageemojiicons_'.$result->emojiicon_id, $orders);
      $result->order = $key+1;
      $result->save();
    }
    return;
  }
  
  public function orderManageEmojiAction() {
    $table = Engine_Api::_()->getDbTable('emojis', 'activity');
    $results = $table->fetchAll($table->select());
    $orders = $this->getRequest()->getParam('order');
    foreach ($results as $result) {
      $key = array_search ('manageemojis_'.$result->getIdentity(), $orders);
      $result->order = $key+1;
      $result->save();
    }
    return;
  }
  
  public function createEmojicategoryAction() {
  
    $id = $this->_getParam('id',false);
    
    $this->view->form = $form = new Activity_Form_Admin_Emoji_Emojicategorycreate();
    if($id){
      $item = Engine_Api::_()->getItem('activity_emoji',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit This Emoji Category');
      $form->submit->setLabel('Edit');
    }
    
    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
    
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {
    
      $catgeoryTable = Engine_Api::_()->getDbtable('emojis', 'activity');
      $values = $form->getValues();

      unset($values['file']); 
      
      if(empty($id))
        $item = $catgeoryTable->createRow();
        
      $item->setFromArray($values);
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
    }catch(Exception $e){
      $db->rollBack();
      throw $e;  
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('Emoji Category Created Successfully.')
    ));
  }
  
  public function emojiiconsAction() {
    $this->view->emoji_id =  $emoji_id = $this->_getParam('emoji_id',false);
    if(!$emoji_id)
      return  $this->_forward('notfound', 'error', 'core');
      
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'activity_admin_main_emojisettings');

    $page = $this->_getParam('page',1);
    $this->view->paginator = Engine_Api::_()->getDbTable('emojiicons','activity')->getPaginator(array('emoji_id' => $emoji_id));
    $this->view->paginator->setItemCountPerPage(300);
    $this->view->paginator->setCurrentPageNumber($page);
  }
}