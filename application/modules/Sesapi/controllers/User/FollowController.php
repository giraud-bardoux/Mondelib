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
class User_FollowController extends Sesapi_Controller_Action_Standard {

  function indexAction() {

    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'unauthorize_access', 'result' => array()));  
    }
    
    $item_id = $this->_getParam('id');
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => Zend_Registry::get('Zend_Translate')->_('Invalid argument supplied.'), 'result' => array()));
    }
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $itemTable = Engine_Api::_()->getItemTable('user');
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $subject = Engine_Api::_()->getItem('user', $item_id);

    $follow_id = $this->_getParam('follow_id');
    $notification_id = $this->_getParam('notification_id');
    $actiontype = $this->_getParam('actiontype');

    if(!empty($follow_id)) {
    
      $follow = Engine_Api::_()->getItem('user_follow', $follow_id);
      if (!$follow) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => Zend_Registry::get('Zend_Translate')->_('Invalid argument supplied.'), 'result' => array()));
      }
      
      if(engine_in_array($actiontype, array('accept', 'follow_accept'))) {
      
        $follow->user_approved = 1;
        $follow->save();

        $subject->follow_count++;
        $subject->save();

        //Send notification
        if ($subject->getType() == 'user' && $subject->getIdentity() != $viewer_id) {

          Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'user_follow_requestaccept', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($subject, $viewer, $subject, 'user_follow_requestaccept');

          Engine_Api::_()->getDbTable('notifications', 'activity')->update(array('mitigated' => true, 'read' => 1), array('type =?' => "user_follow_request", "subject_id =?" => $subject->getIdentity(), "object_type =? " => $viewer->getType(), "object_id = ?" => $viewer->getIdentity()));
        }

        if(!empty($notification_id)) {
          $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationBySubjectAndType($viewer, $subject, 'user_follow_request');
          if($notification) {
            $notification->mitigated = true;
            $notification->read = 1;
            $notification->save();
          }
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('Follow request accepted successfully.'), 'status' => 1, 'count' => $subject->follow_count, 'condition' => 'accept')));
      } elseif(engine_in_array($actiontype, array('reject', 'follow_reject'))) {
        $follow->delete();
        $itemTable->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('user_id = ?' => $item_id));

        if(!empty($notification_id)) {
          $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationBySubjectAndType($viewer, $subject, 'user_follow_request');
          $notification->delete();
        }
        
        Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => "user_follow_request", "subject_id =?" => $subject->getIdentity(), "object_type =? " => $viewer->getType(), "object_id = ?" => $viewer->getIdentity()));
        
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('Follow request deleted successfully.'), 'status' => 1, 'count' => $subject->follow_count, 'condition' => 'reject')));
      }
    
    } else {
    
      $tableFollow = Engine_Api::_()->getDbTable('follows', 'user');
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
            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => "user_follow_request", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          }

          if($settings->getSetting('core.autofollow', 1)) {
            $subject->follow_count--;
            $subject->save();

            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => "user_follow", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          }

          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
        
        if(!empty($settings->getSetting('core.allowuserverfication', 0))) {
          if(!empty($subject->follow_verification)) {
            
            $showData = $this->followactions($subject);
            
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message, 'status' => true, 'count' => $subject->follow_count, 'condition' => 'reduced', 'data' => $showData)));
          } else {
            $showData = $this->followactions($subject);
            
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message, 'status' => true, 'count' => $subject->follow_count, 'condition' => 'reduced', 'autofollow' => $settings->getSetting('core.autofollow', 1), 'data' => $showData)));
          }
        } else {
          if(!empty($settings->getSetting('core.autofollow', 1))) {
            $showData = $this->followactions($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message, 'status' => true, 'count' => $subject->follow_count, 'condition' => 'reduced', 'autofollow' => $settings->getSetting('core.autofollow', 1), 'data' => $showData)));
          } else {
            $showData = $this->followactions($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message, 'status' => true, 'count' => $subject->follow_count, 'condition' => 'reduced', 'data' => $showData)));
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
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'user_follow_request', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($subject, $viewer, $subject, 'user_follow_request');
            }
            $showData = $this->followactions($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('Your follow request has been sent successfully.'), 'status' => true, 'data' => $showData)));
          } else {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'user_follow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($subject, $viewer, $subject, 'user_follow', array('sender_title' => $viewer->getTitle(), 'object_link' => $viewer->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            $showData = $this->followactions($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('You are now following this user.'), 'status' => true, 'data' => $showData)));
          }
        } else {
          if(!empty($settings->getSetting('core.autofollow', 1))) {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'user_follow', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($subject, $viewer, $subject, 'user_follow', array('sender_title' => $viewer->getTitle(), 'object_link' => $viewer->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            
            $showData = $this->followactions($subject);
            
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('You are now following this user.'), 'status' => true, 'condition' => 'increment', 'count' => $subject->follow_count, 'autofollow' => $settings->getSetting('core.autofollow', 1), 'data' => $showData)));
          } else {
            if ($subject->getIdentity() != $viewer_id) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'user_follow_request', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($subject, $viewer, $subject, 'user_follow_request');
            }
            $showData = $this->followactions($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => Zend_Registry::get('Zend_Translate')->_('Your follow request has been sent successfully.'), 'status' => true, 'data' => $showData)));
          }
        }
      }
    }
  }
  
  public function followactions($subject) {

    $followTable = Engine_Api::_()->getDbTable('follows', 'user');
    $isFollow = $followTable->getFollowStatus($subject->user_id);
    $getFollowResourceStatus = $followTable->getFollowResourceStatus($subject->user_id);

    $getFollowUserStatus = $followTable->getFollowUserStatus($subject->user_id);

    if($isFollow && $getFollowResourceStatus->user_approved == 1 && $getFollowResourceStatus->resource_approved == 1) {

      return array(
        'label' => $this->view->translate('Following'),
        'action' => 'index',
        'id' => $subject->getIdentity(),
      );
    } else if($getFollowResourceStatus &&  $getFollowResourceStatus->user_approved == 0 && $getFollowResourceStatus->resource_approved == 1) { 
      return array(
        'label' => $this->view->translate('Requested'),
        'action' => 'index',
        'id' => $subject->getIdentity(),
      );
    } else if( $getFollowResourceStatus && $getFollowResourceStatus->user_approved == 0 && $getFollowResourceStatus->resource_approved == 1 ) {
      return array(
        'label' => $this->view->translate('Confirm'),
        'action' => 'index',
        'id' => $subject->getIdentity(),
      );
    } else if(empty($isFollow) && empty($getFollowResourceStatus)) {
      if(!empty($getFollowUserStatus) && !empty($getFollowUserStatus->user_approved) && !empty($getFollowUserStatus->resource_approved)) {
        return array(
          'label' => $this->view->translate('Follw Back'),
          'action' => 'index',
          'id' => $subject->getIdentity(),
        );
      } else {
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'index',
          'id' => $subject->getIdentity(),
        );
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
