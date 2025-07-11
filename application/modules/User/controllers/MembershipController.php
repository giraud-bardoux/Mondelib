<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: MembershipController.php 10259 2014-06-04 21:43:01Z lucas $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_MembershipController extends Core_Controller_Action_Standard {
 public function addFriendAction() {

    if( !$this->_helper->requireUser()->isValid() ) {
			echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Please login to continue.'))));exit();;	
		}
    $parambutton = $this->_getParam('parambutton', null);
    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
				echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No member specified.'))));exit();
    }

    // check that user is not trying to befriend 'self'
    if( $viewer->isSelf($user) ) {
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('You cannot befriend yourself.'))));exit();
    }

    // check that user is already friends with the member
    if( $user->membership()->isMember($viewer) ) {
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('You are already friends with this member.'))));exit();
    }

    // check that user has not blocked the member
    if( $viewer->isBlocked($user) ) {
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Friendship request was not sent because you blocked this member.'))));exit();
    }

    if($viewer->member_count >= Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.maxfriends', 5000)) {
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('You have already added the maximum number of friends.'))));exit();
    }
    
    // Make form
    // $this->view->form = $form = new User_Form_Friends_Add(array('user' => $user));
    // if($form->{'token_'.$user->getGuid()})
    // $form->removeElement('token_'.$user->getGuid());

    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      
      // send request
      $user->membership()
        ->addMember($viewer)
        ->setUserApproved($viewer);
      
      if( !$viewer->membership()->isUserApprovalRequired() && !$viewer->membership()->isReciprocal() ) {
        // if one way friendship and verification not required

        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($viewer, $user, 'friends_follow', '{item:$subject} is now following {item:$object}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $viewer, 'friend_follow');

        $message = Zend_Registry::get('Zend_Translate')->_("You are now following this member.");
        
      } else if( !$viewer->membership()->isUserApprovalRequired() && $viewer->membership()->isReciprocal() ){
        // if two way friendship and verification not required

        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $viewer, 'friends', '{item:$object} is now friends with {item:$subject}.');
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($viewer, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_accepted');
        
        $message = Zend_Registry::get('Zend_Translate')->_("You are now friends with this member.");

      } else if( !$user->membership()->isReciprocal() ) {
        // if one way friendship and verification required

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_follow_request');
        
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
        
      } else if( $user->membership()->isReciprocal() ) {
        // if two way friendship and verification required

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')
            ->addNotification($user, $viewer, $user, 'friend_request');
        
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
      }

      $db->commit();


      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your friend request has been sent.');
      if($parambutton == 'button') {
        $showData =  $this->view->partial('_addfriend_button.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      } else {
        $showData =  $this->view->partial('_addFriend.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      }
      echo Zend_Json::encode(array('status' =>1, 'message' => $showData,'tip'=>$this->view->message));exit();
      
    } catch( Exception $e ) {
      $db->rollBack();
			throw $e;
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      $this->view->exception = $e->__toString();
      echo Zend_Json::encode(array('status' => 0, 'message' => $this->view->error));die;
    }
  }
  
  public function cancelFriendAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) {
			echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Please login to continue.'))));exit();;
		}
    $parambutton = $this->_getParam('parambutton', null);
    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No member specified');
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No member specified.'))));exit();;
    }
    
    // Make form
    // $this->view->form = $form = new User_Form_Friends_Cancel(array('user'=>$user));
    // if($form->{'token_'.$user->getGuid()})
    //   $form->removeElement('token_'.$user->getGuid());
    
    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $user->membership()->removeMember($viewer);

      // Set the requests as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($user, $viewer, 'friend_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($user, $viewer, 'friend_follow_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }

      $db->commit();
      $user = Engine_Api::_()->getItem('user', $user_id);
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your friend request has been cancelled.');
      if($parambutton == 'button') {
        $showData =  $this->view->partial('_addfriend_button.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      } else {
        $showData =  $this->view->partial('_addFriend.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      }
			echo Zend_Json::encode(array('status' =>1, 'message' => $showData,'tip'=>$this->view->message));exit();
    } catch( Exception $e ) {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      echo Zend_Json::encode(array('status' => 0, 'message' => $this->view->error));die;
      $this->view->exception = $e->__toString();
    }
  }
  
  public function removeFriendAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) {
			echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Please login to continue.'))));exit();;
		}
    $parambutton = $this->_getParam('parambutton', null);
    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
      $this->view->status = false;
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No member specified.'))));exit();;
    }

    // Make form
    // $this->view->form = $form = new User_Form_Friends_Remove(array('user'=>$user));
    // if($form->{'token_'.$user->getGuid()})
    //  $form->removeElement('token_'.$user->getGuid());

    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      if( $this->_getParam('rev') ) {
        $viewer->membership()->removeMember($user);
      } else {
        $user->membership()->removeMember($viewer);
      }

      // Remove from lists?
      // @todo make sure this works with one-way friendships
      $user->lists()->removeFriendFromLists($viewer);
      $viewer->lists()->removeFriendFromLists($user);

      // Set the requests as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
        ->getNotificationBySubjectAndType($user, $viewer, 'friend_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($viewer, $user, 'friend_follow_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      
      $db->commit();

      $message = Zend_Registry::get('Zend_Translate')->_('This person has been removed from your friends.');
      
      $this->view->status = true;
      $this->view->message = $message;
      if($parambutton == 'button') {
        $showData =  $this->view->partial('_addfriend_button.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      } else {
        $showData =  $this->view->partial('_addFriend.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      }
			echo Zend_Json::encode(array('status' =>1, 'message' => $showData,'tip'=>$message));exit();
    } catch( Exception $e ) {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      echo Zend_Json::encode(array('status' => 0, 'message' => $this->view->error));die;
      $this->view->exception = $e->__toString();
    }
  }
	public function acceptFriendAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) {
			echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Please login to continue.'))));exit();
		}
    $parambutton = $this->_getParam('parambutton', null);
    
    // Get viewer and other user
    $viewer = Engine_Api::_()->user()->getViewer();
    if( null == ($user_id = $this->_getParam('user_id')) ||
        null == ($user = Engine_Api::_()->getItem('user', $user_id)) ) {
      $this->view->status = false;
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('No member specified.'))));exit();
    }

    if($viewer->member_count >= Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.maxfriends', 5000)) {
      $this->view->status = false;
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('You have already added the maximum number of friends.'))));exit();
    }

    // Make form
    // $this->view->form = $form = new User_Form_Friends_Confirm(array('user'=>$user));
    // if($form->{'token_'.$user->getGuid()})
    //  $form->removeElement('token_'.$user->getGuid());
    
    $friendship = $viewer->membership()->getRow($user);
    if( $friendship->active ) {
      $this->view->status = false;
      echo Zend_Json::encode(array('status' =>0, 'message' => array(Zend_Registry::get('Zend_Translate')->_('Already friends.'))));exit();;
    }

    // Process
    $db = Engine_Api::_()->getDbtable('membership', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer->membership()->setResourceApproved($user);

      // Add activity
      if( !$user->membership()->isReciprocal() ) {
        Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $viewer, 'friends_follow', '{item:$subject} is now following {item:$object}.');
      } else {
        Engine_Api::_()->getDbtable('actions', 'activity')
          ->addActivity($user, $viewer, 'friends', '{item:$object} is now friends with {item:$subject}.');
        Engine_Api::_()->getDbtable('actions', 'activity')
          ->addActivity($viewer, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');
      }
      
      // Add notification
      if( !$user->membership()->isReciprocal() ) {
        Engine_Api::_()->getDbtable('notifications', 'activity')
          ->addNotification($user, $viewer, $user, 'friend_follow_accepted');
      } else {
        Engine_Api::_()->getDbtable('notifications', 'activity')
          ->addNotification($user, $viewer, $user, 'friend_accepted');
      }

      // Set the requests as handled
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($viewer, $user, 'friend_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      $notification = Engine_Api::_()->getDbtable('notifications', 'activity')
          ->getNotificationBySubjectAndType($viewer, $user, 'friend_follow_request');
      if( $notification ) {
        $notification->mitigated = true;
        $notification->read = 1;
        $notification->save();
      }
      
      // Increment friends counter
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.friendships');

      $db->commit();

      $message = Zend_Registry::get('Zend_Translate')->_('You are now friends with %s');
      $message = sprintf($message, $user->__toString());

      $this->view->status = true;
      $this->view->message = $message;
      if($parambutton == 'button') {
        $showData =  $this->view->partial('_addfriend_button.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      } else {
        $showData =  $this->view->partial('_addFriend.tpl','user',array('subject' => $user,'viewer'=>$viewer,'is_ajax'=>true));
      }
     echo Zend_Json::encode(array('status' =>1, 'message' => $showData,'tip'=>$message));exit();
    } catch( Exception $e ) {
      $db->rollBack();

      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error has occurred.');
      $this->view->exception = $e->__toString();
      echo Zend_Json::encode(array('status' => 0, 'message' => $this->view->error));die;
    }
  }
  public function requestFriendAction()
  {
    $this->view->row = $row = $this->_getParam('row');
		$this->view->subject = $subject = $this->_getParam('subjectItem');
    $this->setTokenData();
  }
	private function setTokenData()
  {
    $subject = $this->view->subject;
    $this->view->tokenName = $tokenName = 'token_' . $subject->getGuid();
    $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret');
    $this->view->tokenValue = $this->view->token(null, $tokenName, $salt);
  }
}
