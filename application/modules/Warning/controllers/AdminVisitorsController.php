<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminVisitorsController.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_AdminVisitorsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('warning_admin_main', array(), 'warning_admin_main_comingsoon');
    
    $this->view->formFilter = $formFilter = new Warning_Form_Admin_Comingsoon_Filter();
    
    // Process form
    $values = array();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values['selectedItems'] as $value) {
        $item = Engine_Api::_()->getItem('warning_visitor', $value);
        if($_POST['delete'] == 'delete') {
          $item->delete();
        }
      }
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
      'order' => 'visitor_id',
      'order_direction' => 'DESC',
    ), $values);
    
    $this->view->assign($values);
    
    $table = Engine_Api::_()->getDbtable('visitors', 'warning');
    $tableName = $table->info('name');
    
    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($tableName);
    
    if(!empty($_GET['visitor_id']))
      $select->where($tableName . ".visitor_id = ?", $_GET['visitor_id']);
    
    if (!empty($_GET['name']))
      $select->where($tableName . '.name LIKE ?', $_GET['name'] . '%');
      
    if (!empty($_GET['email']))
      $select->where($tableName . '.email LIKE ?', $_GET['email'] . '%');
      
    if (!empty($_GET['body']))
      $select->where($tableName . ".body LIKE ?", $_GET['body'] . '%');
    
    $select->order($tableName.'.visitor_id DESC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber( $this->_getParam('page', 1) );
    $this->view->formValues = $valuesCopy;
  }

  public function mailAction() {
    
    $email = $this->_getParam('email', null);
    
    $this->view->form = $form = new Warning_Form_Admin_Mail();
    if($email) {
      $form->submit->setLabel("Send Email");
    }
      
    if( !$this->getRequest()->isPost())
      return;

    if( !$form->isValid($this->getRequest()->getPost()))
      return;
      
    $values = $form->getValues();

    $table = Engine_Api::_()->getDbTable('visitors', 'warning');
    
    $emails = array();
    if(empty($email)) {
    
      $select = new Zend_Db_Select($table->getAdapter());
      $select->from($table->info('name'), 'email');
      
      foreach( $select->query()->fetchAll(Zend_Db::FETCH_COLUMN, 0) as $email ) {
        $emails[] = $email;
      }
    } else {
      $emails[] = $email;
    }

    // temporarily enable queueing if requested
    $temporary_queueing = Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing;
    if (isset($values['queueing']) && $values['queueing']) {
      Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing = 1;
    }

    $mailApi = Engine_Api::_()->getApi('mail', 'core');

    $mail = $mailApi->create();
    $mail->setFrom($values['from_address'], $values['from_name'])
      ->setSubject($values['subject'])
      ->setBodyHtml(nl2br($values['body']));

    if( !empty($values['body_text']) ) {
      $mail->setBodyText($values['body_text']);
    } else {
      $mail->setBodyText(strip_tags($values['body']));
    }
    foreach( $emails as $email ) {
      $mail->addTo($email);
    }

    $mailApi->send($mail);

    $mailComplete = $mailApi->create();
    $mailComplete
      ->addTo(Engine_Api::_()->user()->getViewer()->email)
      ->setFrom($values['from_address'], $values['from_name'])
      ->setSubject('Mailing Complete: '.$values['subject'])
      ->setBodyHtml('Your email blast to your members has completed.  Please note that, while the emails have been
        sent to the recipients\' mail server, there may be a delay in them actually receiving the email due to
        spam filtering systems, incoming mail throttling features, and other systems beyond SocialEngine\'s control.');
    $mailApi->send($mailComplete);

    // emails have been queued (or sent); re-set queueing value to original if changed
    if (isset($values['queueing']) && $values['queueing']) {
      Engine_Api::_()->getApi('settings', 'core')->core_mail_queueing = $temporary_queueing;
      $message = 'Your message has been queued for sending. An email will be sent to you when all email have been sent.';
    } else {
      $message = 'Email Sent Successfully.';
    }
    $this->_forward('success', 'utility', 'core', array(
      'parentRefresh' => 20,
      'messages' => array($message)
    ));
  }
  
  public function readMessageAction() {
    $id = $this->_getParam('id', null);
    $resource_type = $this->_getParam('resource_type', 'warning_visitor');
    $this->view->item = Engine_Api::_()->getItem($resource_type, (int) $id);
  }
}
