<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: BlockController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class User_BlockController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    $this->_helper->requireUser();
  }
  
  public function addAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', "");
    if( !$user_id ) {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    }
    if( !$this->getRequest()->isPost() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid data', 'result' => array()));
    } 
    // Process
    $db = Engine_Api::_()->getDbTable('block', 'user')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $user = Engine_Api::_()->getItem('user', $user_id);
      
      $viewer->addBlock($user);
      if( $user->membership()->isMember($viewer, null) ) {
        $user->membership()->removeMember($viewer);
      }
      try {
        // Set the requests as handled
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')
          ->getNotificationBySubjectAndType($viewer, $user, 'friend_request');
        if( $notification ) {
          $notification->mitigated = true;
          $notification->read = 1;
          $notification->save();
        }
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')
            ->getNotificationBySubjectAndType($viewer, $user, 'friend_follow_request');
        if( $notification ) {
          $notification->mitigated = true;
          $notification->read = 1;
          $notification->save();
        }
      } catch( Exception $e ) {}
      $db->commit();
      $result = $this->memberResult($user);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
    } catch( Exception $e ) {
      $db->rollBack();

        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }

  public function removeAction()
  {
    // Get id of friend to add
    $user_id = $this->_getParam('user_id', null);
    if( !$user_id ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    }

    // Make form

    if( !$this->getRequest()->isPost() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'No action taken', 'result' => array()));
    }

   

    // Process
    $db = Engine_Api::_()->getDbTable('block', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $user = Engine_Api::_()->getItem('user', $user_id);

      $viewer->removeBlock($user);

      $db->commit();

      $this->view->status = true;
      $result = $this->memberResult($user);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));

     
    } catch( Exception $e ) {
      $db->rollBack();

     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  public function memberResult($member){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember')){
        $memberEnable = true; 
      }
      $followActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1);
      if($followActive){
        $unfollowText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow'));
        $followText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));  
      }

      $result['notification']['user_id'] = $member->getIdentity();
      $result['notification']['title'] = $member->getTitle();
      //user location
      if(!empty($member->location))
         $result['notification']['location'] =   $member->location;
     //follow
      if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $member->getIdentity()){
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember')) {
          $FollowUser = Engine_Api::_()->getDbTable('follows','user')->getFollowStatus($member->user_id);
          if (!$FollowUser) {
              $result['notification']['follow']['action'] = 'follow';
              $result['notification']['follow']['text'] = $followText;
          } else {
              $result['notification']['follow']['action'] = 'unfollow';
              $result['notification']['follow']['text'] = $unfollowText;
          }
        }
      }
      //Block
      if($viewer->getIdentity() != $member->getIdentity()){
        if ($member->isBlockedBy($viewer)) {
            $result['notification']['block']['action'] = 'unblock';
            $result['notification']['block']['text'] = $this->view->translate("Unblock");
        } else {
            $result['notification']['block']['action'] = 'block';
            $result['notification']['block']['text'] = $this->view->translate("Block");
        }
      }
      if(!empty($memberEnable)){
        //mutual friends
        $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
        if(!$member->isSelf($viewer)){
           $result['notification']['mutualFriends'] = $mfriend == 1 ? $mfriend.$this->view->translate(" mutual friend") : $mfriend.$this->view->translate(" mutual friends");
        }
      }
      $result['notification']['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
      $result['notification']['membership'] = $this->friendRequest($member);
    
      return $result;
  }
  public function friendRequest($subject){
    $viewer = Engine_Api::_()->user()->getViewer();
    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return "";
    }
    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return "";
    }
    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }
    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {
      $networkMembershipTable = Engine_Api::_()->getDbTable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity());
      $data = $select->query()->fetch();
      if( empty($data) ) {
        return '';
      }
    }
    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'add',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        return array(
          'label' => $this->view->translate('Cancel Request'),
          'action'=>'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      } else {
        // Unfollow
        return array(
          'label' => $this->view->translate('Unfollow'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else {
        // Remove as follower?
        return array(
          'label' => $this->view->translate('Unfollow'),
           'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
          
        );
      }
      if( engine_count($params) == 1 ) {
        return $params[0];
      } else if( engine_count($params) == 0 ) {
        return "";
      } else {
        return $params;
      }
    }
    // Two-way mode
    else {
      $table =  Engine_Api::_()->getDbTable('membership','user');
      $select = $table->select()
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('user_id = ?', $subject->getIdentity());
      $select = $select->limit(1);
      $row = $table->fetchRow($select);
      
      if( null === $row ) {
        // Add
        return array(
          'label' => $this->view->translate('Add Friend'),
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          'action' => 'add',
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $this->view->translate('Cancel Friend'),
          'action' => 'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
          
        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          
        );
      } else {
        // Remove friend
        return array(
          'label' => $this->view->translate('Remove Friend'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
          
        );
      }
    }
  }
}
