<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SettingsController.php 10003 2013-03-26 22:48:26Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_SupportController extends Core_Controller_Action_User {

  protected $_user;

  public function init()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;

    if( null === $id )
    {
        if(!Engine_Api::_()->core()->hasSubject($subject)) {
            $subject = Engine_Api::_()->user()->getViewer();
            Engine_Api::_()->core()->setSubject($subject);
        }
    }
    else
    {
        $subject = Engine_Api::_()->getItem('user', $id);
        Engine_Api::_()->core()->setSubject($subject);
    }

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
    $this->_helper->requireAuth()->setAuthParams(
        $subject,
        null,
        'edit'
    );

    $contextSwitch = $this->_helper->contextSwitch;
    $contextSwitch
        //->addActionContext('reject', 'json')
        ->initContext();

    $param = $this->_getParam('param', 0);
  }

  public function indexAction() {

    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('core.enablesupport', 1))
      return $this->_forward('requireauth', 'error', 'core');
    
    $user = Engine_Api::_()->core()->getSubject();
    $this->view->user_id = $user->getIdentity();
    
    $this->view->formFilter = $formFilter = new User_Form_Settings_TicketFilter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('tickets', 'core');
    $tableName = $table->info('name');
    
    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    
    $select = $table->select()
                  ->setIntegrityCheck(false)
                  ->from($tableName)
                  ->joinLeft($userTableName, "$tableName.user_id = $userTableName.user_id", 'displayname')
                  ->where($tableName.'.user_id =?', $user->getIdentity());

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
    
    $this->view->categories = Engine_Api::_()->getDbTable('categories', 'core')->getCategory(array('column_name' => '*'));
  }

  public function manageAction() {
    
    $user = Engine_Api::_()->core()->getSubject();
    $this->view->user_id = $user->getIdentity();
    
    $this->view->form = new User_Form_Settings_Reply();
    
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbTable('ticketreplies', 'core');
    $tableName = $table->info('name');
    
    $this->view->ticket_id = $ticket_id = $this->_getParam('ticket_id', null);
    $this->view->ticket = $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
    
    if($user->getIdentity() != $ticket->user_id)
      return $this->_forward('requireauth', 'error', 'core');

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
    
    $user = Engine_Api::_()->core()->getSubject();

    $ticket_id = $_POST['ticket_id'];

    $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
    
    if(!empty($ticket->resource_type) && !empty($ticket->resource_id)) {
      $resource = Engine_Api::_()->getItem($ticket->resource_type, $ticket->resource_id);
    }
    
    //Ticket Replies
    $ticketReplyTable = Engine_Api::_()->getItemTable('core_ticketreply');

    $valuesReply['ticket_id'] = $ticket->getIdentity();
    $valuesReply['user_id'] = $user->getIdentity();
    $valuesReply['description'] = $_POST['description'];
    
    $ticketReply = $ticketReplyTable->createRow();
    $ticketReply->setFromArray($valuesReply);
    $ticketReply->save();
    
    $ticket->lastreply_date = date('Y-m-d H:i:s');
    $ticket->save();

    //Send to admins only
    $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
    $ticket_link = '<a href="'.$this->view->url(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true).'">'.$ticket->subject.'</a>';
    $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true);
    foreach ($admins as $admin) {
      Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $user, $ticket, 'content_ticketreply', array('ticket_link' => $ticket_link,  'ticket_subject' => $ticket->subject, 'ticket_description' => $_POST['description'], 'object_link' => $object_link, 'ticket_id' => $ticket->getIdentity()));
    }
    
    echo json_encode(array('status' => true));die;
  }
  
  public function createAction() {

    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('core.enablesupport', 1))
      return $this->_forward('requireauth', 'error', 'core');
   
    $user = Engine_Api::_()->core()->getSubject();
    
    $this->_helper->layout->setLayout('default-simple');
    
    $this->view->category_id = (isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0;
    $this->view->subcat_id = (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0;
    $this->view->subsubcat_id = (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0;
    
    $this->view->form = $form = new User_Form_Settings_Create();

		if (!$this->getRequest()->isPost())
      return;

		if (!$form->isValid($this->getRequest()->getPost()))
      return;

    // Check post
    if( $this->getRequest()->isPost() ) {

      // Process
      $ticketTable = Engine_Api::_()->getItemTable('core_ticket');
      $db = $ticketTable->getAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();
        $values['user_id'] = $user->getIdentity();

        if(!empty($values['description'])) {

          $ticket = $ticketTable->createRow();

          if (is_null($values['subcat_id']))
            $values['subcat_id'] = 0;

          if (is_null($values['subsubcat_id']))
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
          
          //Send to admins only
          $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
          $admin_ticket_link = '<a href="'.$this->view->url(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true).'">'.$ticket->subject.'</a>';
          $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true);
          foreach ($admins as $admin) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $user, $ticket, 'content_ticketcreate', array('admin_ticket_link' => $admin_ticket_link,  'ticket_subject' => $ticket->subject, 'ticket_description' => $values['description'], 'object_link' => $object_link));
          }
        }

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
}
