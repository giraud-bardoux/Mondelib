<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageCommentsController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminManageCommentsController extends Core_Controller_Action_Admin {

  public function indexAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_comments', array(), 'core_admin_main_contentcomments');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_ManageComments_Filter();
    $page = $this->_getParam('page', 1);
    
    $manifest = Zend_Registry::get('Engine_Manifest');
    if (null === $manifest) {
      throw new Engine_Api_Exception('Manifest data not loaded!');
    }
    $itemTypes = [];
    foreach ($manifest as $module => $config) {
      if (!isset($config['items'])) {
          continue;
      }
      $itemTypes = array_merge($itemTypes, $config['items']);
    }

    $table = Engine_Api::_()->getDbTable('comments', 'core');
    $tableName = $table->info('name');
    
    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    
    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($tableName)
            ->joinLeft($userTableName, "$tableName.poster_id = $userTableName.user_id", 'displayname')
            ->where('resource_type IN (?)', $itemTypes);

    // Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'comment_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);
    
    if (!empty($_GET['commented_by']))
      $select->where($userTableName . '.displayname LIKE ?', $_GET['commented_by'] . '%');

    if (!empty($_GET['body']))
      $select->where($tableName . ".body LIKE ?", $_GET['body'] . '%');
      
    if(!empty($_GET['comment_id']))
      $select->where($tableName . ".comment_id = ?", $_GET['comment_id']);
      
    if(!empty($_GET['resource_type']))
      $select->where($tableName . ".resource_type LIKE ?", $_GET['resource_type'] . '%');
      
    $date_from = !empty($_GET['date']['date_from']) ? date("Y-m-d", strtotime($_GET['date']['date_from'])) : '';
    $date_to = !empty($_GET['date']['date_to']) ? date("Y-m-d", strtotime($_GET['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(".$tableName.".creation_date) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $select->where("DATE(".$tableName.".creation_date) >=?", $date_to);
			if (!empty($date_from))
        $select->where("DATE(".$tableName.".creation_date) <=?", $date_from);	
		}
		
// 		if (!empty($_GET['resource_type'])) {
//       // Check file
//       $basePath  = APPLICATION_PATH . '/application/modules/' . ucfirst($_GET['resource_type']);
//       $manifestData = include $basePath . '/settings/manifest.php';
//       if(!empty($manifestData['items']) && engine_count($manifestData['items'])) 
//         $select->where('resource_type IN (?)', $manifestData['items']);
// 		}
		
    $select->order($tableName.'.comment_id DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;
  }
  
  public function editAction() {

    $this->_helper->layout->setLayout('admin-simple');
		
		$id = $this->_getParam('id', null);
		$resource_type = $this->_getParam('resource_type', 'core_comment');
    $comment = Engine_Api::_()->getItem($resource_type, (int) $id);
    
    //Generate and assign form
    $this->view->form = $form = new Core_Form_Admin_ManageComments_Edit();
    $form->setTitle('Edit Comment');
    $form->body->setLabel('Comment');
    
    $form->populate($comment->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
				$comment->body = $values['body'];
				$comment->save();
        $db->commit();
      } catch (Exception $e) {
				$db->rollBack();
				throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully edited comment.')
      ));
    }
  }

  public function multiModifyAction() {
  
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      
      foreach ($values as $key=>$value) {
        if( $key == 'modify_' . $value ) {
          $comment = Engine_Api::_()->getItem('core_comment', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $comment->delete();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $resource_type = $this->_getParam('resource_type', 'core_comment');
    $comment = Engine_Api::_()->getItem($resource_type, (int) $id);
    $this->view->form = $form = new Core_Form_Admin_ManageComments_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('comments', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $comment->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This comment has been successfully deleted.')
      ));
    }
  }

  public function activityAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_comments', array(), 'core_admin_main_activitycomments');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_ManageComments_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('comments', 'activity');
    $tableName = $table->info('name');
    
    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    
    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($tableName)
            ->joinLeft($userTableName, "$tableName.poster_id = $userTableName.user_id", 'displayname');

    // Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'comment_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);
    
    if (!empty($_GET['commented_by']))
      $select->where($userTableName . '.displayname LIKE ?', $_GET['commented_by'] . '%');

    if (!empty($_GET['body']))
      $select->where($tableName . ".body LIKE ?", $_GET['body'] . '%');
      
    if(!empty($_GET['comment_id']))
      $select->where($tableName . ".comment_id = ?", $_GET['comment_id']);
      
    $date_from = !empty($_GET['date']['date_from']) ? date("Y-m-d", strtotime($_GET['date']['date_from'])) : '';
    $date_to = !empty($_GET['date']['date_to']) ? date("Y-m-d", strtotime($_GET['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(".$tableName.".creation_date) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $select->where("DATE(".$tableName.".creation_date) >=?", $date_to);
			if (!empty($date_from))
        $select->where("DATE(".$tableName.".creation_date) <=?", $date_from);	
		}
		
    $select->order($tableName.'.comment_id DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;
  }
  
  public function multiActivitycommentdeleteAction() {
  
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key=>$value) {
        if( $key == 'modify_' . $value ) {
          $comment = Engine_Api::_()->getItem('activity_comment', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $comment->delete();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'activity'));
  }
  
  public function readCommentAction() {
    $id = $this->_getParam('id', null);
    $resource_type = $this->_getParam('resource_type', 'core_comment');
    $this->view->comment = Engine_Api::_()->getItem($resource_type, (int) $id);
  }
}
