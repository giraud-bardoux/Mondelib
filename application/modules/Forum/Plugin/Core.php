<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 10047 2013-05-29 02:06:53Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Plugin_Core
{
  public function onStatistics($event)
  {
    $table  = Engine_Api::_()->getDbTable('topics', 'forum');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'forum topic');
  }
  
  public function onUserDeleteAfter($event)
  {
    $payload = $event->getPayload();
    $user_id = $payload['identity'];

    // Signatures
    $table = Engine_Api::_()->getDbTable('signatures', 'forum');
    $table->delete(array(
      'user_id = ?' => $user_id,
    ));

    // Moderators
    $table = Engine_Api::_()->getDbTable('listItems', 'forum');
    $select = $table->select()->where('child_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach( $rows as $row ) {
      $row->delete();
    }

    // Topics
    $table = Engine_Api::_()->getDbTable('topics', 'forum');
    $select = $table->select()->where('user_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach( $rows as $row ) {
      $row->delete();
    }

    // Posts
    $table = Engine_Api::_()->getDbTable('posts', 'forum');
    $select = $table->select()->where('user_id = ?', $user_id);
    $rows = $table->fetchAll($select);
    foreach ($rows as $row)
    {
      $row->delete();
    }

    // Topic views
    $table = Engine_Api::_()->getDbTable('topicviews', 'forum');
    $table->delete(array(
      'user_id = ?' => $user_id,
    ));
  }

  public function addActivity($event)
  {
    $payload = $event->getPayload();
    $object  = $payload['object'];

    // Only for object=forum
    $innerObject = null;
    if( $object instanceof Forum_Model_Forum ) {
      $innerObject = $object;
    } else if( $object instanceof Forum_Model_Topic ) {
      $innerObject = $object->getParent();
    } else if( $object instanceof Forum_Model_Post ) {
      $innerObject = $object->getParent()->getParent();
    }
    
    if( $innerObject ) {
      $content    = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');
      $allowTable = Engine_Api::_()->getDbtable('allow', 'authorization');
      
      // Forum
      $event->addResponse(array(
        'type' => 'forum',
        'identity' => $object->forum_id
      ));
      
      // Everyone
      if( $content == 'everyone' && $allowTable->isAllowed($object->getAuthorizationItem(), 'everyone', 'view') ) {
        $event->addResponse(array(
          'type' => 'everyone',
          'identity' => 0,
        ));
      }
    }
  }

  public function getActivity($event)
  {
    // Detect viewer and subject
    $payload = $event->getPayload();
    $user = null;
    $subject = null;
    if( $payload instanceof User_Model_User ) {
      $user = $payload;
    } else if( is_array($payload) ) {
      if( isset($payload['for']) && $payload['for'] instanceof User_Model_User ) {
        $user = $payload['for'];
      }
      if( isset($payload['about']) && $payload['about'] instanceof Core_Model_Item_Abstract ) {
        $subject = $payload['about'];
      }
    }
    if( null === $user ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( $viewer->getIdentity() ) {
        $user = $viewer;
      }
    }
    if( null === $subject && Engine_Api::_()->core()->hasSubject() ) {
      $subject = Engine_Api::_()->core()->getSubject();
    }
    
    // Get forum
    if( $user ) {
      $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
      $perms = $authTable->select()
          ->where('resource_type = ?', 'forum')
          ->where('action = ?', 'view')
          ->query()
          ->fetchAll();
      $forumIds = array();
      foreach( $perms as $perm ) {
        if( $perm['role'] == 'everyone' ) {
          $forumIds[] = $perm['resource_id'];
        } else if( $user &&
            $user->getIdentity() &&
            $perm['role'] == 'authorization_level' && 
            $perm['role_id'] == $user->level_id ) {
          $forumIds[] = $perm['resource_id'];
        }
      }
      if( !empty($forumIds) ) {
        $event->addResponse(array(
          'type' => 'forum',
          'data' => $forumIds,
        ));
      }
    } else {
      $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
      $perms = $authTable->select()
          ->where('resource_type = ?', 'forum')
          ->where('action = ?', 'view')
          ->query()
          ->fetchAll();
      $forumIds = array();
      foreach( $perms as $perm ) {
        if( $perm['role'] == 'everyone' ) {
          $forumIds[] = $perm['resource_id'];
        }
      }
      if( !empty($forumIds) ) {
        $event->addResponse(array(
          'type' => 'forum',
          'data' => $forumIds,
        ));
      }
    }
  }
}
