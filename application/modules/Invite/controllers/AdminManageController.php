<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_invites', array(), 'invite_admin_manage');
    
    $this->view->formFilter = $formFilter = new Invite_Form_Admin_Manage_Filter();
    
    // Process form
    $values = array();
    if ($this->getRequest()->isPost()) {
      foreach ($_POST['selectedItems'] as $value) {
        $invite = Engine_Api::_()->getItem('invite', (int) $value);
        if($_POST['delete'] == 'delete') {
          $invite->delete();
        }
      }
    }
    
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbtable('invites', 'invite');
    $tableName = $table->info('name');
    
    $tableUserName = Engine_Api::_()->getDbtable('users', 'user')->info('name');
    
    $select = $table->select()
                  ->setIntegrityCheck(false)
                  ->from($tableName);
                  //->join($tableUserName, "$tableName.user_id = $tableUserName.user_id", null);

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    if( !empty($values['country_code']) || !empty($values['phone_number'])) {
      $select->join($tableUserName, "$tableName.new_user_id = $tableUserName.user_id", null);
    } else {
      $select->join($tableUserName, "$tableName.user_id = $tableUserName.user_id", null);
    }

    // Set up select info
    $select
    //->where('new_user_id =?', 0)
    ->order(( !empty($values['order']) ? $values['order'] : 'id' ) . ' ' . ( !empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    if( !empty($values['recipient']) ) {
      $select->where($table.'.recipient LIKE ?', $values['recipient'] . '%');
    }

    if( !empty($values['country_code']) ) {
      $select->where($tableUserName.'.country_code =?', $values['country_code']);
    }

    if( !empty($values['phone_number']) ) {
      $select->where($tableUserName.'.phone_number = ?', $values['phone_number']);
    }

    if( !empty($values['code']) ) {
      $select->where($table.'.code = ?', $values['code']);
    }
    
    if( isset($values['import_method']) && $values['import_method'] != -1 ) {
      $select->where($table.'.import_method LIKE ?', $values['import_method'] . '%');
    }

    if( !empty($values['id']) ) {
      $select->where($table.'.id = ?', (int) $values['id']);
    }

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;

    $this->view->hideEmails = _ENGINE_ADMIN_NEUTER;
    $this->view->viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();

    $this->view->openUser = (bool) ( $this->_getParam('open') && $paginator->getTotalItemCount() == 1 );
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $invite = Engine_Api::_()->getItem('invite', (int) $id);
    $this->view->form = $form = new Invite_Form_Admin_Manage_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('invites', 'invite')->getAdapter();
      $db->beginTransaction();
      try {
        $invite->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This invite has been successfully deleted.')
      ));
    }
  }
}
