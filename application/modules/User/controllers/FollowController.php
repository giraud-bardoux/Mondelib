<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FollowController.php 10259 2014-06-04 21:43:01Z lucas $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_FollowController extends Core_Controller_Action_User {

  function indexAction() {

    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      echo json_encode(array('status' => 'false', 'error' => 'Login'));
      die;
    }
    
    $item_id = $this->_getParam('id');
    if (intval($item_id) == 0) {
      echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
      die;
    }
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $itemTable = Engine_Api::_()->getItemTable('user');
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $subject = Engine_Api::_()->getItem('user', $item_id);

    $iconType = $this->_getParam('iconType', '');

    $follow_id = $this->_getParam('follow_id');
    $notification_id = $this->_getParam('notification_id');
    $actiontype = $this->_getParam('actiontype');

    if(!empty($follow_id)) {
    
      $follow = Engine_Api::_()->getItem('user_follow', $follow_id);
      if (!$follow) {
        echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
        die;
      }
      
      if($actiontype == 'accept') {
      
        $follow->user_approved = 1;
        $follow->save();

        $subject->follow_count++;
        $subject->save();

        //Send notification
        if ($subject->getType() == 'user' && $subject->getIdentity() != $viewer_id) {

          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'user_follow_requestaccept', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          
          Engine_Api::_()->getDbTable('notificationrecipients', 'core')->addRecipientNotification($subject, $viewer, $subject, 'user_follow_requestaccept');

          Engine_Api::_()->getDbtable('notifications', 'activity')->update(array('mitigated' => true, 'read' => 1), array('type =?' => "user_follow_request", "subject_id =?" => $subject->getIdentity(), "object_type =? " => $viewer->getType(), "object_id = ?" => $viewer->getIdentity()));
        }

        if(!empty($notification_id)) {
          $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationBySubjectAndType($viewer, $subject, 'user_follow_request');
          if($notification) {
            $notification->mitigated = true;
            $notification->read = 1;
            $notification->save();
          }
        }

        echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'accept', 'message' => Zend_Registry::get('Zend_Translate')->_('Follow request accepted successfully.'), 'count' => $subject->follow_count));
        die;
      } elseif($actiontype == 'reject') {
        $follow->delete();
        $itemTable->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('user_id = ?' => $item_id));

        if(!empty($notification_id)) {
          $notification = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationBySubjectAndType($viewer, $subject, 'user_follow_request');
          $notification->delete();
        }
        
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "user_follow_request", "subject_id =?" => $subject->getIdentity(), "object_type =? " => $viewer->getType(), "object_id = ?" => $viewer->getIdentity()));
        
        echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'reject', 'message' => Zend_Registry::get('Zend_Translate')->_('Follow request deleted successfully.'), 'count' => $subject->follow_count));
        die;
      }
    
    } else {
    
      $tableFollow = Engine_Api::_()->getDbtable('follows', 'user');
      $tableFollowName = $tableFollow->info('name');

      $select = $tableFollow->select()
              ->from($tableFollowName)
              ->where('resource_id = ?', $viewer_id)
              ->where('user_id = ?', $item_id);
      $result = $tableFollow->fetchRow($select);

      if (!empty($result)) {
        //delete
        $db = $result->getTable()->getAdapter();
        $db->beginTransaction();
        try {
          if($result && !empty($result->user_approved) && !empty($result->resource_approved)) {
            $message = Zend_Registry::get('Zend_Translate')->_('User unfollowed successfully.');
          } else {
            $message = Zend_Registry::get('Zend_Translate')->_('Your follow request has been cancelled.');
          }
            
          $result->delete();
          
          if ($subject->getIdentity() != $viewer_id) {
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "user_follow_request", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          }

          if($settings->getSetting('core.autofollow', 1)) {
            $subject->follow_count--;
            $subject->save();

            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "user_follow", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          }

          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
        
        if(!empty($settings->getSetting('core.allowuserverfication', 0))) {
          if(!empty($subject->follow_verification)) {
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => $message, 'condition' => 'reduced'));
            exit();
          } else {
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => $message, 'condition' => 'reduced', 'autofollow' => $settings->getSetting('core.autofollow', 1)));
            exit();
          }
        } else {
          if(!empty($settings->getSetting('core.autofollow', 1))) {
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => $message, 'condition' => 'reduced', 'autofollow' => $settings->getSetting('core.autofollow', 1)));
            exit();
          } else {
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => $message, 'condition' => 'reduced'));
            exit();
          }
        }

      } else {
        $db = Engine_Api::_()->getDbTable('follows', 'user')->getAdapter();
        $db->beginTransaction();
        try {
          $follow = $tableFollow->createRow();
          $follow->resource_id = $viewer_id;
          $follow->user_id = $item_id;
          $follow->resource_approved = 1;
          $follow->save();
          
          if(!empty($settings->getSetting('core.allowuserverfication', 0))) {
            if(empty($subject->follow_verification)) {
              $subject->follow_count++;
              $subject->save();
              $follow->user_approved = 1;
              $follow->save();
            } else {
              $follow->user_approved = 0;
              $follow->save();
            }
          } else{
            if(!empty($settings->getSetting('core.autofollow', 1))) {
              $subject->follow_count++;
              $subject->save();
              $follow->user_approved = 1;
              $follow->save();
            } else {
              $follow->user_approved = 0;
              $follow->save();
            }
          }
          //Commit
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }

        if(!empty($settings->getSetting('core.allowuserverfication', 0))) {
          if(!empty($subject->follow_verification)) {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'user_follow_request', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              
              Engine_Api::_()->getDbTable('notificationrecipients', 'core')->addRecipientNotification($subject, $viewer, $subject, 'user_follow_request');
            }
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => Zend_Registry::get('Zend_Translate')->_('Your follow request has been sent successfully.')));
            exit();
          } else {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'user_follow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              
              Engine_Api::_()->getDbTable('notificationrecipients', 'core')->addRecipientNotification($subject, $viewer, $subject, 'user_follow', array('sender_title' => $viewer->getTitle(false), 'object_link' => $viewer->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => Zend_Registry::get('Zend_Translate')->_('You are now following this user.')));
            exit();
          }
        } else {
          if(!empty($settings->getSetting('core.autofollow', 1))) {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'user_follow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              
              Engine_Api::_()->getDbTable('notificationrecipients', 'core')->addRecipientNotification($subject, $viewer, $subject, 'user_follow', array('sender_title' => $viewer->getTitle(false), 'object_link' => $viewer->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'increment', 'count' => $subject->follow_count, 'autofollow' => $settings->getSetting('core.autofollow', 1)));
            die;
          } else {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'user_follow_request', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              
              Engine_Api::_()->getDbTable('notificationrecipients', 'core')->addRecipientNotification($subject, $viewer, $subject, 'user_follow_request');
            }
            $showData = $this->view->partial('_followmembers.tpl', 'user', array('subject' => $subject, 'iconType' => $iconType));
            echo Zend_Json::encode(array('status' => 'true', 'error' => '', 'data' => $showData, 'message' => Zend_Registry::get('Zend_Translate')->_('Your follow request has been sent successfully.')));
            exit();
          }
        }
      }
    }
  }
  
  public function requestFollowAction()
  {
    $this->view->notification = $notification = $this->_getParam('notification');
    $this->setTokenData();
  }

  private function setTokenData()
  {
    $this->view->tokenName = $tokenName = 'token_' . $this->view->notification->getSubject()->getGuid();
    $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret');
    $this->view->tokenValue = $this->view->token(null, $tokenName, $salt);
  }
}
