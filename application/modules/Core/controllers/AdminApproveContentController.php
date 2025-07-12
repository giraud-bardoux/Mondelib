<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminApproveContentController.php 9849 2013-01-09 22:34:21Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminApproveContentController extends Core_Controller_Action_Admin {

  public function approvedAction() {
    
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $this->view->form = $form = new Core_Form_Admin_ApproveContent_Approve();
    if(!empty($resource->approved)) {
      $form->setTitle("Disapprove Content?");
      $form->setDescription("This content is currently approved. Are you sure you want to disapprove it?");
      $form->description->setLabel("Enter the reason for disapproving this content (Optional).");
      $form->submit->setLabel("Disapprove");
    } else {
      $form->setTitle("Approve Content?");
      $form->setDescription("This content is currently disapproved. Are you sure you want to approve it?");
      $form->description->setLabel("Enter the reason for approving this content (Optional).");
      $form->submit->setLabel("Approve");
    }
    
    $db = Engine_Db_Table::getDefaultAdapter();

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
        
        $resource->approved = !$resource->approved;
        $resource->save();

        //Start Ticket work
        if(!empty($formValues['description'])) {
          $ticket_id = $ticketTable->isExists(array('resource_type' => $resource_type, 'resource_id' => $resource_id));
          if(empty($ticket_id)) {
            $values = array_merge($formValues, array(
              'subject' => $resource->getTitle(),
              'user_id' => $resource->getOwner()->getIdentity(),
              'resource_type' => $resource_type,
              'resource_id' => $resource_id,
            ));
            $ticket = $ticketTable->createRow();
            $ticket->setFromArray($values);
            $ticket->save();
          } else {
            $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
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
        }
        $resource->resubmit = 1;
        $resource->save();
        //Ticket and ticket reply work
        
        // Re-index
        Engine_Api::_()->getApi('search', 'core')->index($resource);
        
        if ($resource->approved) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($resource->getOwner(), $resource->getOwner(), $resource, 'content_approvedbyadmin', array('content_text' => $resource->getMediaType(), 'object_title' => $resource->getTitle(), 'owner_title' => $resource->getOwner()->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        } else {
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($resource->getOwner(), $resource->getOwner(), $resource, 'content_disapprovedbyadmin', array('content_text' => $resource->getMediaType(), 'object_title' => $resource->getTitle(), 'owner_title' => $resource->getOwner()->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST']));
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
  
  public function rejectAction() {
    
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $this->view->form = $form = new Core_Form_Admin_ApproveContent_Reject();
    $form->setTitle("Reject Request for Content Approval");
    $form->setDescription("Are you sure you want to reject the request to approve this content?");
    $form->description->setLabel("Enter the reason for rejecting this content (Optional).");
    $form->submit->setLabel("Reject");
    
    $db = Engine_Db_Table::getDefaultAdapter();

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
        if(!empty($formValues['description'])) {
          $ticket_id = $ticketTable->isExists(array('resource_type' => $resource_type, 'resource_id' => $resource_id));
          if(empty($ticket_id)) {
            $values = array_merge($formValues, array(
              'subject' => $resource->getTitle(),
              'user_id' => $resource->getOwner()->getIdentity(),
              'resource_type' => $resource_type,
              'resource_id' => $resource_id,
            ));
            $ticket = $ticketTable->createRow();
            $ticket->setFromArray($values);
            $ticket->save();
          } else {
            $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);
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
        }
        //Ticket and ticket reply work
        
        // Re-index
        Engine_Api::_()->getApi('search', 'core')->index($resource);

        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($resource->getOwner(), $resource->getOwner(), $resource, 'content_rejectedbyadmin', array('content_text' => $resource->getMediaType(), 'object_title' => $resource->getTitle(), 'owner_title' => $resource->getOwner()->getTitle(), 'object_link' => $resource->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        
        $resource->resubmit = 1;
        $resource->save();
        
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => 10,
        'parentRefresh'=> 10,
        'messages' => array('You have successfully rejected the content approval request.')
      ));
    }
  }
}
