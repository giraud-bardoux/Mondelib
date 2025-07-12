<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageMessagesController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminManageMessagesController extends Core_Controller_Action_Admin {

  public function indexAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage', array(), 'core_admin_main_manage_message');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_ManageMessage_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('messages', 'messages');
    $tableName = $table->info('name');
    
    $select = $table->select()->from($tableName);

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
      'order' => 'message_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    if (!empty($_GET['body']))
      $select->where($tableName . ".body LIKE ?", $_GET['body'] . '%');

    $select->order($tableName.'.message_id DESC');

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
          $message = Engine_Api::_()->getItem('messages_message', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $message->delete();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $message = Engine_Api::_()->getItem('messages_message', (int) $id);
    $this->view->form = new Core_Form_Admin_ManageMessage_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
      $db->beginTransaction();
      try {
        $message->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This message has been successfully deleted.')
      ));
    }
  }
}
