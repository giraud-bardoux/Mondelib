<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Signup.php 10180 2014-04-28 21:02:01Z lucas $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_Plugin_Signup {

  public function onUserCreateAfter($payload) {

    $user = $payload->getPayload();
    $isInvited = Engine_Api::_()->getDbtable('invites', 'invite')->isInvited($user);
    
    if(empty($isInvited)) {
      $session = new Zend_Session_Namespace('invite_referral_signup');
      if (isset($session->user_id) && !empty($session->user_id) && isset($session->referral_code) && !empty($session->referral_code)) {
        $userId = $session->user_id;
        $referralCode = $session->referral_code;
        
        $referral = Engine_Api::_()->getDbTable('users', 'user')->getUserExist($userId, $referralCode);
        
        $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
        $row = $inviteTable->createRow();
        $row->user_id = $userId;
        $row->recipient = $user->email ? $user->email : $user->phone_number;
        $row->send_request = 0;
        $row->code = $referralCode; //substr(md5(rand(0, 999) . $user->email), 10, 7);
        $row->timestamp = new Zend_Db_Expr('NOW()');
        $row->message = '';
        $row->new_user_id = $user->user_id;
        $row->import_method = "Referral";
        $row->save();
        
        $referral->referral_count++;
        $referral->save();
      } else if(isset($_SESSION['User_Plugin_Signup_Account']['data']['code']) && !empty($_SESSION['User_Plugin_Signup_Account']['data']['code'])) {
        $code = $_SESSION['User_Plugin_Signup_Account']['data']['code'];
        $referral = Engine_Api::_()->getDbTable('users', 'user')->getUserExist('', $code);
        if($referral) {
          $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
          $row = $inviteTable->createRow();
          $row->user_id = $referral->user_id;
          $row->recipient = $user->email ? $user->email : $user->phone_number;
          $row->send_request = 0;
          $row->code = $code; //substr(md5(rand(0, 999) . $user->email), 10, 7);
          $row->timestamp = new Zend_Db_Expr('NOW()');
          $row->message = '';
          $row->new_user_id = $user->user_id;
          $row->import_method = "Referral";
          $row->save();
          
          $referral->referral_count++;
          $referral->save();
        }
      }
    } else {
    
      $session = new Zend_Session_Namespace('invite');
      $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
      $isEligible = Engine_Api::_()->getApi('settings', 'core')->user_friends_eligible;
      //$inviteTable = new Zend_Db_Table();

      // Get codes
      $codes = [];
      if (!empty($session->invite_code)) {
          $codes[] = $session->invite_code;
      }
      if (!empty($session->signup_code)) {
          $codes[] = $session->signup_code;
      }
      $codes = array_unique($codes);

      // Get emails
      $emails = [];
      if (!empty($session->invite_email)) {
          $emails[] = $session->invite_email;
      }
      if (!empty($session->signup_email)) {
          $emails[] = $session->signup_email;
      }
      $emails = array_unique($emails);

      // Nothing, exit now
      if (empty($codes)) {
          return;
      }
      if (empty($codes) && empty($emails)) {
          return;
      }

      // Get related invites
      $select = $inviteTable->select();

      if (!empty($codes)) {
          //$select->orWhere('code IN(?)', $codes);
          $select->where('code = ?', $codes);
      }

      if (!empty($emails)) {
          //$select->orWhere('recipient IN(?)', $emails);
          $select->where('recipient =?', $emails);
      }

      $updateInviteIds = [];
      $befriendUserIds = [];
      $invites = false;
      foreach ($inviteTable->fetchAll($select) as $invite) {
          // Check if 'invite' wants to send a friend request
          $send_request = $invite->send_request;
          $invitedMember = Engine_Api::_()->getItem('user',$invite->user_id);
          $invite->send_request = 1;
          $invite->save();
          if (!empty($invitedMember)) {
              $befriendUserIds[] = $invite->user_id;
              if(!empty($send_request)) {
                try {
                    // send request
                    $user->membership()->addMember($invitedMember)->setUserApproved($invitedMember);
                    $this->_handleNotification($user,$invitedMember);

                } catch( Exception $e ) {}
              }
              $invites = true;
          }
          // Set new user if if not already
          if (0 == $invite->new_user_id) {
              $updateInviteIds[] = $invite->id;
          }
      }
      // Update invites
      if ($invites && engine_count($updateInviteIds)) {
          $inviteTable->update([
              'new_user_id' => $user->getIdentity(),
          ], [
              'id IN(?)' => $updateInviteIds,
              'new_user_id = ?' => 0,
          ]);
      }
    }

    // Clean session
    $session->unsetAll();
  }

  public function _handleNotification($user, $befriendUser)
  {
    // if one way friendship and verification not required
    if (!$user->membership()->isUserApprovalRequired()&&!$user->membership()->isReciprocal()) {
        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($befriendUser, $user, 'friends_follow', '{item:$object} is now following {item:$subject}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $befriendUser, 'friend_follow');

        $message = Zend_Registry::get('Zend_Translate')->_("You are now following this member.");
    }

    // if two way friendship and verification not required
    elseif (!$user->membership()->isUserApprovalRequired() && $user->membership()->isReciprocal()) {
        // Add activity
        Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $befriendUser, 'friends', '{item:$object} is now friends with {item:$subject}.');
        Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($befriendUser, $user, 'friends', '{item:$object} is now friends with {item:$subject}.');

        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_accepted');
        $message = Zend_Registry::get('Zend_Translate')->_("You are now friends with this member.");
    }

    // if one way friendship and verification required
    elseif (!$user->membership()->isReciprocal()) {
        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_follow_request');
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
    }
    // if two way friendship and verification required
    elseif ($user->membership()->isReciprocal()) {
        // Add notification
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $befriendUser, $user, 'friend_request');
        $message = Zend_Registry::get('Zend_Translate')->_("Your friend request has been sent.");
    }
  }
}
