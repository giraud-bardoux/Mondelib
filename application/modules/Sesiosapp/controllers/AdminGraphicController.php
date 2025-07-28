<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminGraphicController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_AdminGraphicController extends Core_Controller_Action_Admin {
  public function indexAction() {
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $slide = Engine_Api::_()->getItem('sesiosapp_graphic', $value);
          if ($slide->file_id) {
            $item = Engine_Api::_()->getItem('storage_file', $slide->file_id);
            if ($item->storage_path) {
              @unlink($item->storage_path);
              $item->remove();
            }
          }
          $slide->delete();
        }
      }
    }
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main', array(), 'sesiosapp_admin_main_graphic');
    $this->view->paginator = $paginator = Engine_Api::_()->getDbtable('graphics', 'sesiosapp')->getGraphics(false,array('showAll'=>1));
    $page = $this->_getParam('page', 1);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($page);
  }
  public function createGraphicAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main', array(), 'sesiosapp_admin_main_graphic');
    $this->view->graphic_id = $graphic_id = $this->_getParam('graphic_id', false);
    $this->view->form = $form = new Sesiosapp_Form_Admin_Creategraphic();
    if ($graphic_id) {
      //$form->setTitle("");
      $form->submit->setLabel('Save Changes');
      $form->setTitle("Edit Graphic");
      $form->setDescription("Below, edit the details for the graphic.");
      $slide = Engine_Api::_()->getItem('sesiosapp_graphic', $graphic_id);
      $form->populate($slide->toArray());
    }
    if ($this->getRequest()->isPost()) {
      if (!$form->isValid($this->getRequest()->getPost()))
        return;
      $db = Engine_Api::_()->getDbtable('graphics', 'sesiosapp')->getAdapter();
      $db->beginTransaction();
      try {
        $table = Engine_Api::_()->getDbtable('graphics', 'sesiosapp');
        $values = $form->getValues();
        if (!isset($slide))
          $slide = $table->createRow();
				$slide->status = '1';
        $slide->setFromArray($values);
				$slide->save();
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
          $storage = Engine_Api::_()->getItemTable('storage_file');
          $filename = $storage->createFile($form->file, array(
              'parent_id' => $slide->graphic_id,
              'parent_type' => 'sesiosapp_graphic',
              'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          ));
          // Remove temporary file
          @unlink($file['tmp_name']);
          $slide->file_id = $filename->file_id;
        }
        $slide->save();
        $db->commit();
        $url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesiosapp', 'controller' => 'graphic', 'action' => 'index'), 'admin_default', true);
        header("Location:" . $url);
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function deleteGraphicAction() {
    $this->view->type = $this->_getParam('type', null);
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->item_id = $id;
    // Check post
    if ($this->getRequest()->isPost()) {
      $slide = Engine_Api::_()->getItem('sesiosapp_graphic', $id);
      if ($slide->file_id) {
        $item = Engine_Api::_()->getItem('storage_file', $slide->file_id);
        if ($item->storage_path) {
          @unlink($item->storage_path);
          $item->remove();
        }
      }
      $slide->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Graphic Deleted Successfully.')
      ));
    }
    // Output
  }

  public function orderAction() {
    if (!$this->getRequest()->isPost())
      return;
    $slidesTable = Engine_Api::_()->getDbtable('graphics', 'sesiosapp');
    $slides = $slidesTable->fetchAll($slidesTable->select());
    foreach ($slides as $slide) {
      $order = $this->getRequest()->getParam('slide_' . $slide->graphic_id);
      if (!$order)
        $order = 999;
      $slide->order = $order;
      $slide->save();
    }
    return;
  }
  public function enabledAction() {
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sesiosapp_graphic', $id);      
      $item->status = !$item->status;
      $item->save();
    }
    
    $this->_redirect('admin/sesiosapp/graphic');
  }

}
