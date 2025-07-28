<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminSubscribersController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sesandroidapp_AdminSubscribersController extends Core_Controller_Action_Admin {
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesandroidapp_admin_main', array(), 'sesandroidapp_admin_main_subscriber');
    $this->view->form = $form = new Sesandroidapp_Form_Admin_Subscribersearch();
    $form->populate($_REQUEST);
     $table = Engine_Api::_()->getDbTable('aouthtokens','sesapi');
     $tableName = $table->info('name');
     if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'modify_' . $value) {
          $token = Engine_Api::_()->getItem('sesapi_aouthtoken', $value);
          if($token)
            $token->delete();
        }
      }
    }
     $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
      $select = $table->select()
                          ->where('platform =?',2)
													->from($tableName,'*')
                          ->setIntegrityCheck(false)
												 ->joinLeft($tableUserName, "$tableUserName.user_id = $tableName.user_id", array('username','displayname'));
     // Set up select info
		if( isset($_GET['user']) && $_GET['user'] != "")
      $select->where('displayname LIKE ?', '%' . $_GET['user'] . '%');
    
    if( isset($_GET['revoked']) && $_GET['revoked'] != 0)
      $select->where('revoked = ?', $_GET['revoked'] );
      
    if( isset($_GET['email']) && $_GET['email'] != "")
      $select->where('email LIKE ?', '%' . $_GET['email'] . '%');
    
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber( $this->_getParam('page',1) );    
  }
  function revokedAction(){
    $id = $this->_getParam('id','');
    $token = Engine_Api::_()->getItem('sesapi_aouthtoken',$id);
    
    $this->view->form = $form = new Sesandroidapp_Form_Admin_Revoked();  
    $form->id->setValue($id);
    if ($this->getRequest()->isPost()) {
      $token->revoked = !$token->revoked;
      $token->save();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('Action performed Successfully.')
        ));
    }
  }
  
  public function deleteAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $id = $this->_getParam('id',false);
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesapi_Form_Delete();
    $form->setTitle('Delete Token?');
    $form->setDescription('Are you sure that you want to delete this token? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $item = Engine_Api::_()->getItem('sesapi_aouthtoken',$id);
    if (!$item) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Token doesn't exists to delete");
      return;
    }
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    $db = $item->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $item->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Token has been deleted.');
    return $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh'=> 10,
                'messages' => Array($this->view->message)
    ));  
  }
}
