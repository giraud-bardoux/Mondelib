<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Friends.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Settings_Friends extends Engine_Form
{
  public function init()
  {
    $friend_settings = Engine_Api::_()->getApi('settings', 'core')->user_friends;
    $this->setTitle('Friendship Settings');
    $this->setDescription('USER_FORM_ADMIN_SETTINGS_FRIENDS_DESCRIPTION');

    $this->addElement('Radio', 'eligible', array(
      'label' => 'Enable Friendship',
      'description' => 'USER_FORM_ADMIN_SETTINGS_ELIGIBLE_DESCRIPTION',
      'multiOptions' => array(
        '2'=>'Yes', 
        '0'=>'No'
      ),
      'value' => $friend_settings['eligible']
    ));
    
    $this->addElement('Radio', 'lists', array(
      'label' => 'Friend Lists',
      'description' => 'USER_FORM_ADMIN_SETTINGS_LISTS_DESCRIPTION',
      'multiOptions' => array(
        '1'=>"Yes, users can group their friends into lists", 
        '0'=>"No, do not allow friend lists"
      ),
      'value' => $friend_settings['lists']
    ));
    
    $this->addElement('Integer', 'maxfriends', array(
      'label' => 'Maximum Friends',
      'description' => 'Enter the maximum number of allowed friends. The field must contain an integer between 1 and 5000.',
      'value' => $friend_settings['maxfriends']
    ));
    
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }

  public function saveValues()
  {
    $values = $this->getValues();

    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try
    {
//       $table = Engine_Api::_()->getDbtable('membership', 'user');
//       $select = $table->select();
//       
//       // handle directional change
//       // get the current directional
//       // if direction 0 && the value['direction'] is == 1
//       // we must make sure all the one way ones have an opposite
//       // update all friendships and notifications
//       $direction = Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction');
//       if($values['direction']== 1 && $direction ==0){          
//         $direction_select = $select;
//         // go through the friendships and make active, resource_approved, user_approved into 1
//         foreach( $direction_select->getTable()->fetchAll($direction_select) as $friendship )
//         {
//           $direction_select = $table->select()->where('resource_id = ?', $friendship->user_id)->where('user_id = ?', $friendship->resource_id);
//           $row = $direction_select->getTable()->fetchRow($direction_select);
//           if(!$row){
//             $new_friendship = $table->createRow();
//             $new_friendship->resource_id = $friendship->user_id;
//             $new_friendship->user_id = $friendship->resource_id;
//             $new_friendship->active = $friendship->active;
//             $new_friendship->resource_approved = $friendship->user_approved;
//             $new_friendship->user_approved = $friendship->resource_approved;
//             $new_friendship->message = $friendship->message;
//             $new_friendship->description = $friendship->description;
//             $new_friendship->save();            
//           }
//         }
// 
//         // go through the notifications and change all follow requests to friend requests
//         Engine_Api::_()->getDbtable('notifications', 'activity')->update(array(
//         'type' => 'friend_request',
//         ), array (
//         'type = ?' => 'friend_follow_request',
//         ));        
// 
//         // change all friend_follow_accepted to friend_accepted notifications
//         Engine_Api::_()->getDbtable('notifications', 'activity')->update(array(
//         'type' => 'friend_accepted',
//         ), array (
//         'type = ?' => 'friend_follow_accepted',
//         ));
// 
//         // change activity actions from following to friends with
//         Engine_Api::_()->getDbtable('actions', 'activity')->update(array(
//         'type' => 'friends',
//         'body' => '{item:$object} is now friends with {item:$subject}.',
//         ), array (
//         'type = ?' => 'friends_follow',
//         ));        
//       }
// 
//       // if direction 1 && the value['direction'] is == 0
//       // need to change notifications and activity actions from follow to friend
//       if( $values['direction']== 0 && $direction == 1 ){
//         // go through the notifications and change all follow requests to friend requests
//         Engine_Api::_()->getDbtable('notifications', 'activity')->update(array(
//         'type' => 'friend_follow_request',
//         ), array (
//         'type = ?' => 'friend_request',
//         ));        
//         
//         // change all friend_follow_accepted to friend_accepted notifications
//         Engine_Api::_()->getDbtable('notifications', 'activity')->update(array(
//         'type' => 'friend_follow_accepted',
//         ), array (
//         'type = ?' => 'friend_accepted',
//         ));
//         
//         // change activity actions from following to friends with
//         Engine_Api::_()->getDbtable('actions', 'activity')->update(array(
//         'type' => 'friends_follow',
//         'body' => '{item:$subject} is now following {item:$object}.',
//         ), array (
//         'type = ?' => 'friends',
//         ));                
//       }
//             
//       // handle verification change
//       // if verification ==0 and changes to ==1
//       // we must make all pending requests active
//       $verification = Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.verification');
//       if($values['verification']==0 && $verification ==1){
//         // select all where active ==0
//         $verification_select = $table->select()->where('active = ?', 0);
// 
//         // go through the friendships and make active, resource_approved, user_approved into 1
//         foreach( $verification_select->getTable()->fetchAll($verification_select) as $friendship )
//         {
//           $friendship->active =1;
//           $friendship->resource_approved =1;
//           $friendship->user_approved =1;
//           $friendship->save();
//         }
// 
//         // delete friend request notifications
//         Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array (
//           'type IN(?)' => array('friend_follow_request', 'friend_request')
//           ));
//       }
// 
//       Engine_Api::_()->getApi('settings', 'core')->setSetting("user.friends.verification", $values['verification']);
//       Engine_Api::_()->getApi('settings', 'core')->setSetting("user.friends.direction", $values['direction']);
//      
//       
//       // reset friendship counts on users table      
//       $userTable = Engine_Api::_()->getDbtable('users', 'user');
//       $user_select = $userTable->fetchAll();
//            
//       foreach( $user_select as $userRow )
//       {
//         $count = $table->select()
//           ->from($table->info('name'), new Zend_Db_Expr('COUNT(*)'))
//           ->where('user_id = ?', $userRow->user_id)
//           ->where('active = ?', '1')
//           ->query()
//           ->fetchColumn();
// 
//         $userTable->update(array(
//           'member_count' => $count,
//           ), array (
//           'user_id = ?' => $userRow->user_id,
//           ));
//       }
      
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $settings->user_friends = $values;
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
       throw $e;
      return;
    }
  }
}
