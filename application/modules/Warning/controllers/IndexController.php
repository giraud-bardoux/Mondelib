<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: IndexController.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_IndexController extends Core_Controller_Action_Standard {

  public function contactAction() {
  
    $translate = Zend_Registry::get('Zend_Translate');
    $this->view->form = $form = new Warning_Form_Contact();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Success! Process
    // Mail gets logged into database, so perform try/catch in this Controller
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      // the contact form is emailed to the first SuperAdmin by default
	  $users_table = Engine_Api::_()->getDbtable('users', 'user');
	  $users_select = $users_table->select()
	  		->where('level_id = ?', 1)
	  		->where('enabled >= ?', 1);
	  $super_admin = $users_table->fetchRow($users_select);
      $adminEmail = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.contact');
      if( !$adminEmail ) {
        $adminEmail = $super_admin->email;
      }

      $viewer = Engine_Api::_()->user()->getViewer();

      $values = $form->getValues(); 
      
      $visitorsTable = Engine_Api::_()->getDbtable('visitors', 'warning');
      $row = $visitorsTable->createRow();
      $row->setFromArray($values);
      $row->save();

      // Check for error report
      $error_report = '';
      $name = $this->_getParam('name');
      $loc = $this->_getParam('loc');
      $time = $this->_getParam('time');
      if( $name && $loc && $time ) {
        $error_report .= "\r\n";
        $error_report .= "\r\n";
        $error_report .= "-------------------------------------";
        $error_report .= "\r\n";
        $error_report .= $this->view->translate('The following information about an error was included with this message:');
        $error_report .= "\r\n";
        $error_report .= $this->view->translate('Exception: ') . base64_decode(urldecode($name));
        $error_report .= "\r\n";
        $error_report .= $this->view->translate('Location: ') . base64_decode(urldecode($loc));
        $error_report .= "\r\n";
        $error_report .= $this->view->translate('Time: ') . date('c', base64_decode(urldecode($time)));
        $error_report .= "\r\n";
      }

      // Make params
      $mail_settings = array(
        'host' => $_SERVER['HTTP_HOST'],
        'email' => $adminEmail,
        'date' => time(),
        'recipient_title' => $super_admin->getTitle(),
        'recipient_link' => $super_admin->getHref(),
        'recipient_photo' => $super_admin->getPhotoUrl('thumb.icon'),
        'sender_title' => $values['name'],
        'sender_email' => $values['email'],
        'message' => $values['body'],
        'error_report' => $error_report,
      );

      if( $viewer && $viewer->getIdentity() ) {
        $mail_settings['sender_title'] .= ' (' . $viewer->getTitle() . ')';
        $mail_settings['sender_email'] .= ' (' . $viewer->email . ')';
        $mail_settings['sender_link'] = $viewer->getHref();
      }

      // send email
      Engine_Api::_()->getApi('mail', 'core')->sendSystem(
        $adminEmail,
        'core_contact',
        $mail_settings
      );

      //If the above did not throw an exception, it succeeded
      $db->commit();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Thank you for contacting us!');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => false,
        'messages' => array($this->view->message)
      ));
    } catch( Zend_Mail_Transport_Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
  
  
  public function searchAction() {

    $text = $this->_getParam('text', null);

    $table = Engine_Api::_()->getDbtable('search', 'core');
    $select = $table->select()->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', $text . '%')->order('id DESC');

    $select->limit('10');

    $results = Zend_Paginator::factory($select);
    foreach ($results as $result) {
      $itemType = $result->type;
      if (Engine_Api::_()->hasItemType($itemType)) {
        $item = Engine_Api::_()->getItem($itemType, $result->id);
        $item_type = ucfirst($item->getShortType());
        $photo_icon_photo = $this->view->itemPhoto($item, 'thumb.icon');
        $data[] = array(
            'id' => $result->id,
            'label' => $item->getTitle(),
            'photo' => $photo_icon_photo,
            'url' => $item->getHref(),
            'resource_type' => $item_type,
        );
      }
    }
    $data[] = array(
        'id' => 'show_all',
        'label' => $text,
        'url' => 'all',
        'resource_type' => '',
    );
    return $this->_helper->json($data);
  }
}
