<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminManagerFeedbgController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_AdminManageFeedbgController extends Core_Controller_Action_Admin {

  public function indexAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $background = Engine_Api::_()->getItem('activity_background', $value);
          if($background)
          $background->delete();
        }
      }
    }
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'activity_admin_main_febgsettings');

    $this->view->paginator = Engine_Api::_()->getDbTable('backgrounds','activity')->getPaginator(array('admin' => 0));
    $this->view->paginator->setItemCountPerPage(60);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }

  public function enabledAction() {
    if (!_ENGINE_ADMIN_NEUTER) {
      $id = $this->_getParam('id');
      if (!empty($id)) {
        $item = Engine_Api::_()->getItem('activity_background', $id);
        $item->enabled = !$item->enabled;
        $item->save();
      }
    }
    $this->_redirect('admin/activity/manage-feedbg');
  }

  public function featuredAction() {
    if (_ENGINE_ADMIN_NEUTER) {
      $id = $this->_getParam('id');
      if (!empty($id)) {
        $item = Engine_Api::_()->getItem('activity_background', $id);
        $item->featured = !$item->featured;
        $item->save();
      }
    }
    $this->_redirect('admin/activity/manage-feedbg');
  }

  public function orderAction() {
    if (_ENGINE_ADMIN_NEUTER) {
      return;
    }
    $backgroundsTable = Engine_Api::_()->getDbtable('backgrounds', 'activity');
    $backgrounds = $backgroundsTable->fetchAll($backgroundsTable->select());
    $orders = $this->getRequest()->getParam('order');

    foreach ($backgrounds as $background) {
      $key = array_search ('managebackgrounds_'.$background->background_id, $orders);
      $background->order = $key+1;
      $background->save();
    }
    return;
  }

  public function createAction() {

    $id = $this->_getParam('id',false);

    $this->view->upload_max_size = $upload_max_size = ini_get('upload_max_filesize');
    $this->view->max_file_upload_in_bytes = $max_file_upload_in_bytes = Engine_Api::_()->core()->convertPHPSizeToBytes(ini_get('upload_max_filesize'));

    $this->view->form = $form = new Activity_Form_Admin_Background_Create();
    if($id){
      $item = Engine_Api::_()->getItem('activity_background',$id);
      $form->populate($item->toArray());
      $form->setTitle('Edit this Background Image');
      $form->submit->setLabel('Save Changes');
      $this->view->enableenddate = $item->enableenddate;
    } else {
      $this->view->enableenddate = 0;
    }

    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    if($this->getRequest()->isPost() && !(empty($_FILES['file']['size']) || (int)$_FILES['file']['size'] > (int)$max_file_upload_in_bytes)){
      $form->file->addError('Image was not uploaded and size not more than '.$upload_max_size);
    }

    if(!$form->isValid($this->getRequest()->getPost()) && !$id) {
      $this->view->enableenddate = $enableenddate = $form->getValue('enableenddate') ? 1 : 0;
      if($enableenddate){
        $form->endtime->setRequired(true);
        $form->endtime->setAllowEmpty(false); 
      }
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    if($form->getValue('enableenddate')=="1" && ($form->getValue('endtime') == '0000-00-00 00:00:00' || empty($form->getValue('endtime')))){
      $this->view->enableenddate = 1;
      $form->endtime->setRequired(true);
      $form->endtime->setAllowEmpty(false);
      $form->endtime->addError('Please select a date from the calendar.');
      return;
    }

    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;
    try {

      $backgroundsTable = Engine_Api::_()->getDbtable('backgrounds', 'activity');
      $values['starttime'] = date('Y-m-d',  strtotime($values['starttime']));
      if($values['enableenddate'] && $values['endtime'] != '0000-00-00') {
        $values['endtime'] = date('Y-m-d', strtotime($values['endtime']));
      } else {
        $values['endtime'] = null;
      }
      unset($values['file']);

      if(empty($id))
        $item = $backgroundsTable->createRow();

      $item->setFromArray($values);
      $item->save();

      if(!empty($_FILES['file']['name'])) {
        $file_ext = pathinfo($_FILES['file']['name']);
        $file_ext = $file_ext['extension'];
        $storage = Engine_Api::_()->getItemTable('storage_file');
        $storageObject = $storage->createFile($form->file, array(
          'parent_id' => $item->background_id,
          'parent_type' => 'activity_background',
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));
        // Remove temporary file
        //@unlink($file['tmp_name']);
        $item->file_id = $storageObject->file_id;
        $item->save();
      }
      $item->order = $item->background_id;
      $item->save();

      $db->commit();
    } catch(Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh'=> true,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Feed Background Image Uploaded Successfully.'))
    ));
  }

  public function deleteAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Core_Form_Admin_Delete();

    $form->setTitle('Delete This Background Image');
    $form->setDescription('Are you sure that you want to delete this background image? It will not be recoverable after being deleted.');

    $form->submit->setLabel('Delete');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $background = Engine_Api::_()->getItem('activity_background', $id);
      $background->delete();
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Feed Background Image Deleted Successfully.'))
      ));
    }
  }
}
