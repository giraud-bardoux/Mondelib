<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: TicketsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_SupportController extends Core_Controller_Action_Standard {

  public function init() {
    $this->_helper->requireUser();
  }
  
  public function resubmitAction() {
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $this->view->form = $form = new Core_Form_Support_Resubmit();
    $form->setTitle("Resubmit for Approval");
    $form->setDescription("This content is disapproved by our site admin. To resubmit your content for approval, enter your message (optional) and click on 'Submit' button.");
    $form->submit->setLabel("Submit");

		if (!$this->getRequest()->isPost()) {
      return;
		}

		if (!$form->isValid($this->getRequest()->getPost())) {
      return;
		}

    // Check post
    if( $this->getRequest()->isPost() ) {

      // Process
      $ticketTable = Engine_Api::_()->getItemTable('core_ticket');
      $db = $ticketTable->getAdapter();
      $db->beginTransaction();
      try {
        $formValues = $form->getValues();
        
        //Start Ticket work
        $ticket_id = $ticketTable->isExists(array('resource_type' => $resource_type, 'resource_id' => $resource_id));
        if(empty($ticket_id)) {
          $values = array_merge($formValues, array(
            'subject' => $resource->getTitle(),
            'user_id' => $viewer->getIdentity(),
            'resource_type' => $resource_type,
            'resource_id' => $resource_id,
          ));
          $ticket = $ticketTable->createRow();
          $ticket->setFromArray($values);
          $ticket->save();
          
          //Send to admins only
          $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
          $admin_ticket_link = '<a href="'.$this->view->url(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true).'">'.$ticket->subject.'</a>';
          $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true);
          foreach ($admins as $admin) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $viewer, $ticket, 'content_ticketcreate', array('admin_ticket_link' => $admin_ticket_link,  'ticket_subject' => $ticket->subject, 'ticket_description' => $values['description'], 'object_link' => $object_link, "ticket_id" => $ticket->getIdentity()));
          }
        } else {
          $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
          
          //Send to admins only
          $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
          $ticket_link = '<a href="'.$this->view->url(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true).'">'.$ticket->subject.'</a>';
          $object_link = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', "action" => 'manage', "ticket_id" => $ticket->getIdentity()), 'admin_default', true);
          foreach ($admins as $admin) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $viewer, $ticket, 'content_ticketreply', array('ticket_link' => $ticket_link,  'ticket_subject' => $ticket->subject, 'ticket_description' => $values['description'], 'object_link' => $object_link, "ticket_id" => $ticket->getIdentity()));
          }
        }
        
        //Ticket Replies
        $ticketReplyTable = Engine_Api::_()->getItemTable('core_ticketreply');
        $valuesReply = array_merge($formValues, array(
          'ticket_id' => $ticket->getIdentity(),
          'user_id' => $viewer->getIdentity(),
        ));
        $ticketReply = $ticketReplyTable->createRow();
        $ticketReply->setFromArray($valuesReply);
        $ticketReply->save();
        
        $ticket->lastreply_date = date('Y-m-d H:i:s');
        $ticket->save();
        //Ticket and ticket reply work
        
        //Start Send Approval Request to Admin
        if (!$resource->approved) {
          $resource->resubmit = 2;
          $resource->save();
          //Send to admins only
          $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
          foreach ($admins as $admin) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $viewer, $resource, 'content_resubmitwaitapprforadmin', array('content_text' => $resource->getMediaType(),  'sender_title' => $resource->getOwner()->getTitle(), 'object_title' => $resource->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST'], "ticket_id" => $ticket->getIdentity()));
          }
        }
        $db->commit();
      } catch(Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => 10,
        'parentRefresh'=> 10,
        'messages' => array('Your request has been successfully submitted.')
      ));
    }
  }
}
