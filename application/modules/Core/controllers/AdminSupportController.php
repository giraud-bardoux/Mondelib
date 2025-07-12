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

class Core_AdminSupportController extends Core_Controller_Action_Admin {

  public function settingsAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_tickets', array(), 'core_admin_manage_settings');

    $this->view->form = $form = new Core_Form_Admin_Support_Settings();

    // Check post/valid
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    
    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function indexAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_tickets', array(), 'core_admin_manage_tickets');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_Support_Filter();
    $page = $this->_getParam('page', 1);
    
    $replyTable = Engine_Api::_()->getItemTable('core_ticketreply');
    
    // Process form
    $values = array();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values['selectedItems'] as $value) {
        $item = Engine_Api::_()->getItem('core_ticket', (int) $value);
        if($_POST['delete'] == 'delete') {
          // Delete all child ticket reply
          $replySelect = $replyTable->select()->where('ticket_id = ?', (int) $value);
          foreach( $replyTable->fetchAll($replySelect) as $reply ) {
            $reply->delete();
          }
          $item->delete();
        }
      }
    }
    
    $this->view->categories = Engine_Api::_()->getDbTable('categories', 'core')->getCategory(array('column_name' => '*'));

    $table = Engine_Api::_()->getDbTable('tickets', 'core');
    $tableName = $table->info('name');
    
    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    
    $select = $table->select()
                  ->setIntegrityCheck(false)
                  ->from($tableName)
                  ->joinLeft($userTableName, "$tableName.user_id = $userTableName.user_id", 'displayname');

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
      'order' => 'ticket_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);
    
    if(!empty($_GET['ticket_id']))
      $select->where($tableName . ".ticket_id = ?", $_GET['ticket_id']);
    
    if (!empty($_GET['posted_by']))
      $select->where($userTableName . '.displayname LIKE ?', $_GET['posted_by'] . '%');

    if (!empty($_GET['subject']))
      $select->where($tableName . ".subject LIKE ?", $_GET['subject'] . '%');
      
    $date_from = !empty($_GET['date']['date_from']) ? date("Y-m-d", strtotime($_GET['date']['date_from'])) : '';
    $date_to = !empty($_GET['date']['date_to']) ? date("Y-m-d", strtotime($_GET['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(".$tableName.".creation_date) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
      if(!empty($date_to)){
				$select->where($tableName.".creation_date LIKE ?", "{$date_to}%");
			}
			if(!empty($date_from)){
				$select->where($tableName.".creation_date LIKE ?", "{$date_from}%");
			}
		}
		
		if(!empty($_GET['category_id']))
      $select->where($tableName . ".category_id = ?", $_GET['category_id']);
      
    if(!empty($_GET['subcat_id']))
      $select->where($tableName . ".subcat_id = ?", $_GET['subcat_id']);
      
    if(!empty($_GET['subsubcat_id']))
      $select->where($tableName . ".subsubcat_id = ?", $_GET['subsubcat_id']);
		
    $select->order($tableName.'.ticket_id DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;
  }
  
  public function manageAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_tickets', array(), 'core_admin_manage_tickets');
    
    $this->view->form = new Core_Form_Admin_Support_Reply();
    
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('ticketreplies', 'core');
    $tableName = $table->info('name');
    
    $this->view->ticket_id = $ticket_id = $this->_getParam('ticket_id', null);
    $this->view->ticket = $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
    
    if(!empty($ticket->resource_type) && !empty($ticket->resource_id)) {
      $this->view->resource = $resource = Engine_Api::_()->getItem($ticket->resource_type, $ticket->resource_id);
    }

    $select = $table->select()
                  ->setIntegrityCheck(false)
                  ->from($tableName)
                  ->where('ticket_id = ?', $ticket_id)
                  ->order('ticketreply_id ASC');

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber( $page );
  }
  
  public function replyAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $ticket_id = $_POST['ticket_id'];

    $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
    
    //Ticket Replies
    $ticketReplyTable = Engine_Api::_()->getItemTable('core_ticketreply');

    $valuesReply['ticket_id'] = $ticket->getIdentity();
    $valuesReply['user_id'] = $viewer->getIdentity();
    $valuesReply['description'] = $_POST['description'];
    
    $ticketReply = $ticketReplyTable->createRow();
    $ticketReply->setFromArray($valuesReply);
    $ticketReply->save();
    
    $ticket->lastreply_date = date('Y-m-d H:i:s');
    $ticket->save();
    
    if(!empty($ticket->resource_type) && !empty($ticket->resource_id)) {
      $resource = Engine_Api::_()->getItem($ticket->resource_type, $ticket->resource_id);
      if($_POST['approved'] == 1) {
        $resource->approved = !$resource->approved;
        $resource->save();
        
        if(!empty($resource->approved)) {
          $ticket->status = 'Closed';
          $ticket->save();
        }
        
        // Re-index
        Engine_Api::_()->getApi('search', 'core')->index($resource);
        
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($resource->getOwner(), $resource->getOwner(), $resource, 'content_approvedbyadmin', array('content_text' => $resource->getShortType(), 'object_title' => $resource->getTitle(), 'owner_title' => $resource->getOwner()->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        
      } else if($_POST['approved'] == 2) {
      
        $resource->resubmit = 1;
        $resource->save();
        
        $resource->approved = !$resource->approved;
        $resource->save();
        
        // Re-index
        Engine_Api::_()->getApi('search', 'core')->index($resource);

        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($resource->getOwner(), $resource->getOwner(), $resource, 'content_rejectedbyadmin', array('content_text' => $resource->getShortType(), 'object_title' => $resource->getTitle(), 'owner_title' => $resource->getOwner()->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      }
    }
    
    //Send to ticket creator
    $ticketOwner = Engine_Api::_()->getItem('user', $ticket->user_id);
    $ticket_link = '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'user_support', true).'">'.$ticket->subject.'</a>';
    
    $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'user_support', true);
    
    Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($ticketOwner, $viewer, $ticket, 'content_ticketreply', array('ticket_link' => $ticket_link, 'ticket_subject' => $ticket->subject, 'object_link' => $object_link, 'host' => $_SERVER['HTTP_HOST'], 'ticket_description' => $_POST['description']));
    
    echo json_encode(array('status' => true));die;
  }
  
  public function createAction() {
    
    $this->_helper->layout->setLayout('admin-simple');
    
    $this->view->category_id = (isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0;
    $this->view->subcat_id = (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0;
    $this->view->subsubcat_id = (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0;

    $this->view->form = $form = new Core_Form_Admin_Support_Create();

		if (!$this->getRequest()->isPost()) {
      return;
		}

		if (!$form->isValid($this->getRequest()->getPost())) {
      return;
		}
		
    if (empty($_POST['user_id'])) {
      $form->addError($this->view->translate("Please choose member from the autosuggest."));
      return;
    }

    // Check post
    if( $this->getRequest()->isPost() ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      // Process
      $ticketTable = Engine_Api::_()->getItemTable('core_ticket');
      $db = $ticketTable->getAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();

        //Start Ticket work
        if(!empty($values['description'])) {

          $ticket = $ticketTable->createRow();
          
          if (isset($values['subcat_id']) && is_null($values['subcat_id']))
            $values['subcat_id'] = 0;
            
          if (isset($values['subsubcat_id']) && is_null($values['subsubcat_id']))
            $values['subsubcat_id'] = 0;
            
          $ticket->setFromArray($values);
          $ticket->save();

          //Ticket Replies
          $ticketReplyTable = Engine_Api::_()->getItemTable('core_ticketreply');
          $valuesReply = array_merge($values, array(
            'ticket_id' => $ticket->getIdentity()
          ));
          $ticketReply = $ticketReplyTable->createRow();
          $ticketReply->setFromArray($valuesReply);
          $ticketReply->save();
          
          $ticket->lastreply_date = date('Y-m-d H:i:s');
          $ticket->save();

          //Send to ticket owner
          $ticketOwner = Engine_Api::_()->getItem('user', $ticket->user_id);
          $ticket_link = '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'user_support', true).'">'.$ticket->subject.'</a>';
          
          $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'user_support', true);
          
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($ticketOwner, $viewer, $ticket, 'content_newticketcreate', array('ticket_link' => $ticket_link, 'ticket_id' => $ticket->getIdentity(), 'object_link' => $object_link, 'host' => $_SERVER['HTTP_HOST'], 'ticket_description' => $_POST['description'], 'ticket_subject' => $ticket->subject));
        }
        //Ticket and ticket reply work

        $db->commit();
      } catch(Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }
  }
  
  public function editTicketAction() {
    
    $this->_helper->layout->setLayout('admin-simple');
    
    $ticket_id = $this->_getParam('ticket_id', null);
    $item = Engine_Api::_()->getItem('core_ticket', (int) $ticket_id);
    
    $this->view->category_id = (isset($item->category_id) && $item->category_id != 0) ? $item->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
    $this->view->subcat_id = (isset($item->subcat_id) && $item->subcat_id != 0) ? $item->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
    $this->view->subsubcat_id = (isset($item->subsubcat_id) && $item->subsubcat_id != 0) ? $item->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

    $this->view->form = $form = new Core_Form_Admin_Support_Create();
    $form->setTitle("Edit Ticket");
    $form->submit->setLabel("Save Changes");
    $form->removeElement('name');
    $form->removeElement('description');
    $form->populate($item->toArray());

		if (!$this->getRequest()->isPost()) {
      return;
		}

		if (!$form->isValid($this->getRequest()->getPost())) {
      return;
		}
		
    // Check post
    if( $this->getRequest()->isPost() ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      // Process
      $ticketTable = Engine_Api::_()->getItemTable('core_ticket');
      $db = $ticketTable->getAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();
        $item->setFromArray($values);
        $item->save();

        $db->commit();
      } catch(Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('This support ticket has been successfully updated.')
      ));
    }
  }
  
  public function editReplyAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $ticket_id = $this->_getParam('ticket_id', null);
    $ticketreply_id = $this->_getParam('ticketreply_id', null);
    $item = Engine_Api::_()->getItem('core_ticket', (int) $ticket_id);
    $ticketReply = Engine_Api::_()->getItem('core_ticketreply', (int) $ticketreply_id);
    $this->view->form = $form = new Core_Form_Admin_Support_EditReply();
    $form->populate($ticketReply->toArray());
    
		if (!$this->getRequest()->isPost()) {
      return;
		}

		if (!$form->isValid($this->getRequest()->getPost())) {
      return;
		}
    
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('tickets', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $ticketReply->description = $_POST['description'];
        $ticketReply->save();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This support ticket reply has been successfully updated.')
      ));
    }
  }
  
  public function deleteReplyAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    $ticket_id = $this->_getParam('ticket_id', null);
    $ticketreply_id = $this->_getParam('ticketreply_id', null);
    $item = Engine_Api::_()->getItem('core_ticket', (int) $ticket_id);
    $ticketReply = Engine_Api::_()->getItem('core_ticketreply', (int) $ticketreply_id);
    $this->view->form = new Core_Form_Admin_Support_DeleteReply();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('tickets', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $ticketReply->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This support ticket reply has been successfully deleted.')
      ));
    }
  }
  
  public function deleteAction() {
  
    $ticket_id = $this->_getParam('ticket_id', null);
    $item = Engine_Api::_()->getItem('core_ticket', (int) $ticket_id);
    $this->view->form = new Core_Form_Admin_Support_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('tickets', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        // Delete all child ticket reply
        $replyTable = Engine_Api::_()->getItemTable('core_ticketreply');
        $replySelect = $replyTable->select()->where('ticket_id = ?', $ticket_id);
        foreach( $replyTable->fetchAll($replySelect) as $reply ) {
          $reply->delete();
        }
        $item->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => true,
        'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', 'action' => 'index'), 'admin_default', true),
        'format'=> 'smoothbox',
        'messages' => array('This support ticket has been successfully deleted.')
      ));
    }
  }
  
  public function categoriesAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_tickets', array(), 'core_admin_manage_categories');
    Engine_Api::_()->getApi('categories', 'core')->categories(array('module' => 'core', 'type' => 'tickets', 'controller' => "support"));
  }
  
  public function editCategoryAction() {

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $category_id = $this->_getParam('id');
    $type = $this->_getParam('type');

    $categoriesTable = Engine_Api::_()->getDbtable('categories', 'core');
    $category = $categoriesTable->find($category_id)->current();
    
    if( !$category ) {
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh'=> 10,
        'messages' => array('')
      ));
    } else {
      $category_id = $category->getIdentity();
    }
    
    $form = $this->view->form = new Core_Form_Admin_Category_Edit();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));
    $form->setField($category);
    
    if( !$this->getRequest()->isPost() ) {
      // Output
      $this->renderScript('admin-support/form.tpl');
      return;
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      // Output
      $this->renderScript('admin-support/form.tpl');
      return;
    }
    
    // Process
    $values = $form->getValues();

    $db = $categoriesTable->getAdapter();
    $db->beginTransaction();
    
    try {
      $category->category_name = $values['label'];
      $category->save();
      
      if(isset($_POST['parentcategory_id']) && !empty($_POST['parentcategory_id'])) {
        $categoryItem = Engine_Api::_()->getItem('core_category', $_POST['parentcategory_id']);
        if(!empty($categoryItem->subcat_id)) {
          $category->subcat_id = 0;
          $category->subsubcat_id = $_POST['parentcategory_id'];
          $category->save();
        } else if(empty($categoryItem->subcat_id)) {
          $category->subcat_id = $_POST['parentcategory_id'];
          $category->subsubcat_id = 0;
          $category->save();
        } 
      } else if($_POST['parentcategory_id'] == '') {
        $category->subcat_id = 0;
        $category->subsubcat_id = 0;
        $category->save();
      }
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh'=> 10,
      'messages' => array('')
    ));
  }
  
  public function changeOrderAction() {

    if ($this->_getParam('id', false) || $this->_getParam('nextid', false)) {
      $categoryTable = Engine_Api::_()->getDbTable('categories', 'core');
      
      $id = $this->_getParam('id', false);
      $order = $this->_getParam('categoryorder', false);
      $order = explode(',', $order);
      $nextid = $this->_getParam('nextid', false);
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      if ($id) {
        $category_id = $id;
      } else if ($nextid) {
        $category_id = $id;
      }
      $categoryTypeId = '';
      $checkTypeCategory = $dbObject->query("SELECT * FROM engine4_core_categories WHERE category_id = " . $category_id . " AND type = 'tickets'")->fetchAll();
      if (isset($checkTypeCategory[0]['subcat_id']) && $checkTypeCategory[0]['subcat_id'] != 0) {
        $categoryType = 'subcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subcat_id'];
      } else if (isset($checkTypeCategory[0]['subsubcat_id']) && $checkTypeCategory[0]['subsubcat_id'] != 0) {
        $categoryType = 'subsubcat_id';
        $categoryTypeId = $checkTypeCategory[0]['subsubcat_id'];
      } else
        $categoryType = 'category_id';
      if ($checkTypeCategory)
        $currentOrder = $categoryTable->order($categoryTypeId, $categoryType, 'tickets');
      // Find the starting point?
      $start = null;
      $end = null;
      $order = array_reverse(array_values(array_intersect($order, $currentOrder)));
      for ($i = 0, $l = engine_count($currentOrder); $i < $l; $i++) {
        if (engine_in_array($currentOrder[$i], $order)) {
          $start = $i;
          $end = $i + engine_count($order);
          break;
        }
      }
      if (null === $start || null === $end) {
        echo "false";
        die;
      }
      
      for ($i = 0; $i < engine_count($order); $i++) {
        $category_id = $order[$i - $start];
        $categoryTable->update(array('order' => $i), array('category_id = ?' => $category_id));
      }
      $checkCategoryChildrenCondition = $dbObject->query("SELECT * FROM engine4_core_categories WHERE subcat_id = '" . $id . "' || subsubcat_id = '" . $id . "' || subcat_id = '" . $nextid . "' || subsubcat_id = '" . $nextid . "'")->fetchAll();
      if (empty($checkCategoryChildrenCondition)) {
        echo 'done';
        die;
      }
      echo "children";
      die;
    }
  }
}
