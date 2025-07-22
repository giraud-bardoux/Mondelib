<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9903 2013-02-14 02:38:27Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_event_events' => array('like_count', 'comment_count'),
      'engine4_event_albums' => array('like_count'),
      'engine4_event_photos' => array('like_count')
    ),
    '4.9.3' => array(
      'engine4_event_events' => array('view_privacy'),
    )
  );

  public function onInstall()
  {
    //if($this->_databaseOperationType != 'upgrade'){
      $this->_addContentEventProfile();
      $this->_addContentMemberHome();
      $this->_addContentMemberProfile();

      $this->_addEventBrowsePage();
      $this->_addEventCreatePage();
      $this->_addEventManagePage();
      
      $this->_addPrivacyColumn();
    //}
    parent::onInstall();
  }

  protected function _addEventManagePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'event_index_manage')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'event_index_manage',
        'displayname' => 'Event Manage Page',
        'title' => 'My Events',
        'description' => 'This page lists a user\'s events.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 1,
      ));
      $mainRightId = $db->lastInsertId();


      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 1,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));
    }
  }

  protected function _addEventCreatePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'event_index_create')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'event_index_create',
        'displayname' => 'Event Create Page',
        'title' => 'Event Create',
        'description' => 'This page allows users to create events.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 1,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

    }

  }

  protected function _addEventBrowsePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'event_index_browse')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'event_index_browse',
        'displayname' => 'Event Browse Page',
        'title' => 'Event Browse',
        'description' => 'This page lists events.',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 1,
      ));
      $topId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
        'order' => 2,
      ));
      $mainId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $mainMiddleId = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 1,
      ));
      $mainRightId = $db->lastInsertId();

      // Insert banner
     $db->insert('engine4_core_banners', array(
        'name' => 'event',
        'module' => 'event',
        'title' => 'Your Source for Events',
        'body' => 'Don’t miss the action! Create, join and explore Events.',
        'photo_id' => 0,
        'params' => '{"label":"Create New Event","route":"event_general","routeParams":{"action":"create"}}',
        'custom' => 0
      ));
      $bannerId = $db->lastInsertId();

      if( $bannerId ) {
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'core.banner',
          'page_id' => $pageId,
          'parent_content_id' => $topMiddleId,
          'params' => '{"title":"","name":"core.banner","banner_id":"'. $bannerId .'","nomobile":"0"}',
          'order' => 1,
        ));
      }

      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-menu',
        'page_id' => $pageId,
        'parent_content_id' => $topMiddleId,
        'order' => 2,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $mainMiddleId,
        'order' => 1,
      ));

      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));
      // Insert list categories
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'event.list-categories',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 3,
      ));
    }
  }
  
  protected function _addContentMemberHome()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Get page id
    $pageId = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'user_index_home')
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    // event.home-upcoming

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $hasWidget = $select
      ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
      ->where('page_id = ?', $pageId)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'event.home-upcoming')
      ->query()
      ->fetchColumn()
      ;

    // Add it
    if( !$hasWidget ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $containerId = $select
        ->from('engine4_core_content', 'content_id')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'container')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $rightId = $select
        ->from('engine4_core_content', 'content_id')
        ->where('parent_content_id = ?', $containerId)
        ->where('type = ?', 'container')
        ->where('name = ?', 'right')
        ->limit(1)
        ->query()
        ->fetchColumn()
        ;

      // insert
      if( $rightId ) {
        $db->insert('engine4_core_content', array(
          'page_id' => $pageId,
          'type'    => 'widget',
          'name'    => 'event.home-upcoming',
          'parent_content_id' => $rightId,
          'order'   => 1,
          'params'  => '{"title":"Upcoming Events","titleCount":true}',
        ));
      }
    }
  }

  protected function _addContentMemberProfile()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Get page id
    $pageId = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'user_profile_index')
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    // event.profile-events
    if($pageId) {
        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $hasProfileEvents = $select
          ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'event.profile-events')
          ->query()
          ->fetchColumn()
          ;
    
      // Add it
      if( !$hasProfileEvents ) {
        // container_id (will always be there)
        $select = new Zend_Db_Select($db);
        $containerId = $select
          ->from('engine4_core_content', 'content_id')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'container')
          ->limit(1)
          ->query()
          ->fetchColumn()
          ;

        // middle_id (will always be there)
        $select = new Zend_Db_Select($db);
        $middleId = $select
          ->from('engine4_core_content', 'content_id')
          ->where('parent_content_id = ?', $containerId)
          ->where('type = ?', 'container')
          ->where('name = ?', 'middle')
          ->limit(1)
          ->query()
          ->fetchColumn()
          ;

        // tab_id (tab container) may not always be there
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_content', 'content_id')
          ->where('type = ?', 'widget')
          ->where('name = ?', 'core.container-tabs')
          ->where('page_id = ?', $pageId)
          ->limit(1);
        $tabId = $select->query()->fetchObject();
        if( $tabId && @$tabId->content_id ) {
            $tabId = $tabId->content_id;
        } else {
          $tabId = $middleId;
        }

        // insert
        if( $tabId ) {
          $db->insert('engine4_core_content', array(
            'page_id' => $pageId,
            'type'    => 'widget',
            'name'    => 'event.profile-events',
            'parent_content_id' => $tabId,
            'order'   => 8,
            'params'  => '{"title":"Events","titleCount":true}',
          ));
        }
      }
    }
  }

  protected function _addContentEventProfile()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $pageId = $select
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'event_profile_index')
      ->limit(1)
      ->query()
      ->fetchColumn()
      ;

    // Add it
    if( empty($pageId) ) {

      $db->insert('engine4_core_pages', array(
        'name' => 'event_profile_index',
        'displayname' => 'Event Profile',
        'title' => 'Event Profile',
        'description' => 'This is the profile for an event.',
        'custom' => 0,
        'provides' => 'subject=event',
      ));
      $pageId = $db->lastInsertId('engine4_core_pages');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $containerId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $containerId,
        'order' => 3,
        'params' => '',
      ));
      $middleId = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'container',
        'name' => 'left',
        'parent_content_id' => $containerId,
        'order' => 1,
        'params' => '',
      ));
      $leftId = $db->lastInsertId('engine4_core_content');

      // middle column
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'core.container-tabs',
        'parent_content_id' => $middleId,
        'order' => 2,
        'params' => '{"max":"6"}',
      ));
      $tabId = $db->lastInsertId('engine4_core_content');

      // left column
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'event.profile-info',
        'parent_content_id' => $leftId,
        'order' => 3,
        'params' => '',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'event.profile-rsvp',
        'parent_content_id' => $leftId,
        'order' => 4,
        'params' => '',
      ));

      // tabs
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'activity.feed',
        'parent_content_id' => $tabId,
        'order' => 1,
        'params' => '{"title":"Updates"}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'event.profile-members',
        'parent_content_id' => $tabId,
        'order' => 2,
        'params' => '{"title":"Guest Responses","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'event.profile-photos',
        'parent_content_id' => $tabId,
        'order' => 3,
        'params' => '{"title":"Photos","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'event.profile-discussions',
        'parent_content_id' => $tabId,
        'order' => 4,
        'params' => '{"title":"Discussions","titleCount":true}',
      ));
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type' => 'widget',
        'name' => 'core.profile-links',
        'parent_content_id' => $tabId,
        'order' => 5,
        'params' => '{"title":"Links","titleCount":true}',
      ));
    }
    $this->_addContentCoverPhoto($pageId);
  }

  // Create and populate `view_privacy` column
  protected function _addPrivacyColumn()
  {
    if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.3', $this->_currentVersion, '<=') ) {
      return $this;
    }

    $db = $this->getDb();
    $sql = "ALTER TABLE `engine4_event_events` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'owner' AFTER `category_id`";
    try {
     $db->query($sql);
    } catch( Exception $e ) {
      return $this->_error('Query failed with error: ' . $e->getMessage());
    }

    // populate `view_privacy` column
    $select = new Zend_Db_Select($db);

    try {
    $select
      ->from('engine4_authorization_allow', array('resource_id' => 'resource_id', 'privacy_values' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT role)')))
      ->where('resource_type = ?', 'event')
      ->where('action = ?', 'view')
      ->group('resource_id');

      $privacyList = $select->query()->fetchAll();
    } catch( Exception $e ) {
      return $this->_error('Query failed with error: ' . $e->getMessage());
    }

    foreach( $privacyList as $privacy ) {
      $viewPrivacy = 'owner';
      $privacyVal = explode(",", $privacy['privacy_values']);
      if( engine_in_array('everyone', $privacyVal) ) {
        $viewPrivacy = 'everyone';
      } elseif( engine_in_array('registered', $privacyVal) ) {
        $viewPrivacy = 'registered';
      } elseif( engine_in_array('owner_network', $privacyVal) ) {
        $viewPrivacy = 'owner_network';
      } elseif( engine_in_array('owner_member_member', $privacyVal) ) {
        $viewPrivacy = 'owner_member_member';
      } elseif( engine_in_array('owner_member', $privacyVal) ) {
        $viewPrivacy = 'owner_member';
      } elseif( engine_in_array('member', $privacyVal) ) {
        $viewPrivacy = 'member';
      }

      $db->update('engine4_event_events', array(
            'view_privacy' => $viewPrivacy,
            ), array(
            'event_id = ?' => $privacy['resource_id'],
          ));
    }

    return $this;
  }

  private function _addContentCoverPhoto($pageId)
  {
    if (empty($pageId)) {
      return;
    }

    $db = $this->getDb();
    $hasCover = $db->select()
      ->from('engine4_core_content', 'content_id')
      ->where('page_id = ?', $pageId)
      ->where('name = ?', 'event.cover-photo')
      ->limit(1)
      ->query()
      ->fetchColumn();

    $hasTop = $db->select()
      ->from('engine4_core_content', 'content_id')
      ->where('name = ?', 'top')
      ->where('page_id = ?', $pageId)
      ->where('type = ?', 'container')
      ->limit(1)
      ->query()
      ->fetchColumn();
    if (empty($hasTop)) {
      // top-containers
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $pageId,
        'order' => 0
      ));
      $topId = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $topId,
      ));
      $topMiddleId = $db->lastInsertId();
    } else {
      $topMiddleId = $db->select()
        ->from('engine4_core_content', 'content_id')
        ->where('name = ?', 'middle')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'container')
        ->where('parent_content_id = ?', $hasTop)
        ->limit(1)
        ->query()
        ->fetchColumn();
    }

    if (!empty($topMiddleId)) {
    
      $db->query('UPDATE `engine4_core_content` SET `order` = `order`+2 WHERE `engine4_core_content`.`page_id` = "'.$pageId.'" AND `engine4_core_content`.`type` = "widget";');
      
      $hasBreadcrumb = $db->select()
                  ->from('engine4_core_content', 'content_id')
                  ->where('page_id = ?', $pageId)
                  ->where('name = ?', 'event.breadcrumb')
                  ->limit(1)
                  ->query()
                  ->fetchColumn();
      if(empty($hasBreadcrumb)) {
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'event.breadcrumb',
          'page_id' => $pageId,
          'parent_content_id' => $topMiddleId,
          'order' => 1,
        ));
      }
      
      if(empty($hasCover)) {
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'event.cover-photo',
          'page_id' => $pageId,
          'parent_content_id' => $topMiddleId,
          'order' => 2,
        ));
      }
    }
  }
}
