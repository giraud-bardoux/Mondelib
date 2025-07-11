<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: NotificationsController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_NotificationsController extends Core_Controller_Action_Standard
{

  public function init()
  {
    $this->_helper->requireUser();
  }
  
  public function indexAction() {
  
		$this->view->isAjax = $isAjax = $this->_getParam('isAjax', 0);
    $viewer = Engine_Api::_()->user()->getViewer();
		$page = $this->_getParam('page');
    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $notifications->setCurrentPageNumber($page);

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);

    $this->view->hasunread = false;
  }
  
  public function removeNotificationAction() {
    $notification_id = $this->_getParam('notification_id', null);
    $notification = Engine_Api::_()->getItem('activity_notification', $notification_id);

    try {
      if($notification) {
        $notification->delete();
        echo Zend_Json::encode(array('status' => 1));exit();
      } else {
        echo Zend_Json::encode(array('status' => 0));exit();
      }
    } catch( Exception $e ) {
      echo 0;die;
    }
  }
  
  public function deleteNotificationAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->form = $form = new Activity_Form_Delete();
    die;
    $notification = Engine_Api::_()->getItem('activity_notification', $this->_getParam('notification_id'));
    
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
        return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }
        
    // Process
    $table = Engine_Api::_()->getItemTable('activity_notification');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $notification->delete();
      $db->commit();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your notification entry has been deleted.');
      return $this->_forward('success' ,'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => Array($this->view->message)
      ));
    } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
    }
  }
  
  public function deleteNotificationsAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->form = $form = new Activity_Form_DeleteNotification();

    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
        return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }
        
    // Process
    $table = Engine_Api::_()->getItemTable('activity_notification');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $dbQuery = Zend_Db_Table_Abstract::getDefaultAdapter();
      $dbQuery->query('DELETE FROM `engine4_activity_notifications` WHERE `engine4_activity_notifications`.`user_id` = "'.$viewer->getIdentity().'";');
      $db->commit();
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your notification entry has been deleted.');
      return $this->_forward('success' ,'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => Array($this->view->message)
      ));
    } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
    }
  }

  public function hideAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer);
    echo 1;die;
  }

  public function markreadAction()
  {
    $request = Zend_Controller_Front::getInstance()->getRequest();

    $notification_id = $request->getParam('notification_id', 0);

    $viewer = Engine_Api::_()->user()->getViewer();
    $notificationsTable = Engine_Api::_()->getDbtable('notifications', 'activity');
    $db = $notificationsTable->getAdapter();
    $db->beginTransaction();

    try {
      $notification = Engine_Api::_()->getItem('activity_notification', $notification_id);
      if( $notification ) {
        $notification->read = 1;
        $notification->save();
      }
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    
    if ($this->_helper->contextSwitch->getCurrentContext()  != 'json') {
      $this->_helper->viewRenderer->setNoRender();
    }
  }

  public function updateAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $this->view->notificationCount = $notificationCount = (int) Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->notificationOnly = $request->getParam('notificationOnly', false);

    // @todo locale()->tonumber
    $this->view->text = $this->view->translate(array('%s Update', '%s Updates', $notificationCount), $notificationCount);
  }

  public function pulldownAction()
  {
    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $notifications->setCurrentPageNumber($page);

    if( $notifications->getCurrentItemCount() <= 0 || $page > $notifications->getCurrentPageNumber() ) {
      $this->_helper->viewRenderer->setNoRender(true);
      return;
    }
    Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer);

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);
  }
  
  public function friendshipRequestsAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->friendRequests = $newFriendRequests = Engine_Api::_()->getDbtable('notifications', 'activity')->getFriendrequestPaginator($viewer);
    $newFriendRequests->setCurrentPageNumber($this->_getParam('page'));
  }
}
