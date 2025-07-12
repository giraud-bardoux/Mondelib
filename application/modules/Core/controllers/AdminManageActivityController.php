<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageActivityController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminManageActivityController extends Core_Controller_Action_Admin {

  public function indexAction() {
    
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_activity', array(), 'core_admin_main_manage_activity');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_ManageActivity_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('actions', 'activity');
    $tableName = $table->info('name');
    
    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    
    //Action type
    $mainActionTypes = array();
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    foreach( $masterActionTypes as $type ) {
      $mainActionTypes[] = $type->type;
    }
    
    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($tableName)
            ->joinLeft($userTableName, "$tableName.subject_id = $userTableName.user_id", 'displayname');
    
    if(engine_count($mainActionTypes) > 0) {
      $select->where('type IN (?)', $mainActionTypes);
    }

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
      'order' => 'action_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    if (!empty($_GET['posted_by']))
      $select->where($userTableName . '.displayname LIKE ?', $_GET['posted_by'] . '%');

    if(!empty($_GET['action_id']))
      $select->where($tableName . ".action_id = ?", $_GET['action_id']);
      
    if (!empty($_GET['body']))
      $select->where($tableName . ".body LIKE ?", $_GET['body'] . '%');

    $date_from = !empty($_GET['date']['date_from']) ? date("Y-m-d", strtotime($_GET['date']['date_from'])) : '';
    $date_to = !empty($_GET['date']['date_to']) ? date("Y-m-d", strtotime($_GET['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(date) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $select->where("DATE(date) >=?", $date_to);
			if (!empty($date_from))
        $select->where("DATE(date) <=?", $date_from);	
		}
		
    $select->order($tableName.'.date DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;
  }
  
  public function multiModifyAction() {
  
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      
      foreach ($values as $key=>$value) {
        if( $key == 'modify_' . $value ) {
          $action = Engine_Api::_()->getItem('activity_action', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $action->deleteItem();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $action = Engine_Api::_()->getItem('activity_action', (int) $id);
    $this->view->form = new Core_Form_Admin_ManageActivity_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
      $db->beginTransaction();
      try {
        $action->deleteItem();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This activity has been successfully deleted.')
      ));
    }
  }
}
