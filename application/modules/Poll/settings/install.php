<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9896 2013-02-14 00:42:26Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_poll_polls' => array('like_count')
    ),
    '4.9.3' => array(
      'engine4_poll_polls' => array('view_privacy')
    )
  );

  public function onInstall()
  {
    //if($this->_databaseOperationType != 'upgrade'){
      $this->_addUserProfileContent();
      $this->_addPollBrowsePage();
      $this->_addPollViewPage();
      
      $this->_addPollCreatePage();
      $this->_addPollManagePage();

      $this->_addPrivacyColumn();
    //}
    parent::onInstall();
  }
  
  protected function _addPollManagePage()
  {
    $db = $this->getDb();

    // profile page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'poll_index_manage')
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'poll_index_manage',
        'displayname' => 'Poll Manage Page',
        'title' => 'My Poll',
        'description' => 'This page lists a user\'s polls.',
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $page_id,
        'order' => 1,
      ));
      $top_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
        'order' => 2,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_id,
      ));
      $top_middle_id = $db->lastInsertId();
      
      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 2,
      ));
      $main_middle_id = $db->lastInsertId();
      
      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 1,
      ));
      $main_right_id = $db->lastInsertId();
      
      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-menu',
        'page_id' => $page_id,
        'parent_content_id' => $top_middle_id,
        'order' => 1,
      ));
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 1,
      ));
      
      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-search',
        'page_id' => $page_id,
        'parent_content_id' => $main_right_id,
        'order' => 1,
      ));
      
      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-menu-quick',
        'page_id' => $page_id,
        'parent_content_id' => $main_right_id,
        'order' => 2,
      ));
    }
  }
  
  protected function _addPollCreatePage()
  {
  
  $db = $this->getDb();

    // profile page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'poll_index_create')
      ->limit(1)
      ->query()
      ->fetchColumn();
      
    if( !$page_id ) {
      
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'poll_index_create',
        'displayname' => 'Poll Create Page',
        'title' => 'Create New Poll',
        'description' => 'This page is the poll create page.',
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $page_id,
        'order' => 1,
      ));
      $top_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
        'order' => 2,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_id,
      ));
      $top_middle_id = $db->lastInsertId();
      
      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 2,
      ));
      $main_middle_id = $db->lastInsertId();
      
      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-menu',
        'page_id' => $page_id,
        'parent_content_id' => $top_middle_id,
        'order' => 1,
      ));
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 1,
      ));
    }
  }
  
  protected function _addPollBrowsePage()
  {
    $db = $this->getDb();

    // profile page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'poll_index_browse')
      ->limit(1)
      ->query()
      ->fetchColumn();
    
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'poll_index_browse',
        'displayname' => 'Poll Browse Page',
        'title' => 'Poll Browse',
        'description' => 'This page lists polls.',
        'custom' => 0,
      ));
      $page_id = $db->lastInsertId();
      
      // Insert top
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'top',
        'page_id' => $page_id,
        'order' => 1,
      ));
      $top_id = $db->lastInsertId();
      
      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $page_id,
        'order' => 2,
      ));
      $main_id = $db->lastInsertId();
      
      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_id,
      ));
      $top_middle_id = $db->lastInsertId();
      
      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 2,
      ));
      $main_middle_id = $db->lastInsertId();
      
      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 1,
      ));
      $main_right_id = $db->lastInsertId();
      
      // Insert menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-menu',
        'page_id' => $page_id,
        'parent_content_id' => $top_middle_id,
        'order' => 1,
      ));
      
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 1,
      ));
      
      // Insert search
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-search',
        'page_id' => $page_id,
        'parent_content_id' => $main_right_id,
        'order' => 1,
      ));
      
      // Insert gutter menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'poll.browse-menu-quick',
        'page_id' => $page_id,
        'parent_content_id' => $main_right_id,
        'order' => 2,
      ));
    }
  }
  
  protected function _addPollViewPage()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);
    
    // Check if it's already been placed
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'poll_poll_view')
      ->limit(1)
      ->query()
      ->fetchColumn(0);

    if( !$page_id ) {
      $db->insert('engine4_core_pages', array(
        'name' => 'poll_poll_view',
        'displayname' => 'Poll View Page',
        'title' => 'View Poll',
        'description' => 'This is the view page for a poll.',
        'custom' => 0,
        'provides' => 'subject=poll',
      ));
      $page_id = $db->lastInsertId('engine4_core_pages');

      // containers
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $container_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $container_id,
        'order' => 3,
        'params' => '',
      ));
      $middle_id = $db->lastInsertId('engine4_core_content');
      
      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'poll.breadcrumb',
          'page_id' => $page_id,
          'parent_content_id' => $middle_id,
          'order' => 1,
      ));
      // middle column content
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'poll.cover-photo',
        'parent_content_id' => $middle_id,
        'order' => 2,
        'params' => '',
      ));
      
      // middle column content
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.content',
        'parent_content_id' => $middle_id,
        'order' => 3,
        'params' => '',
      ));

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.comments',
        'parent_content_id' => $middle_id,
        'order' => 4,
        'params' => '',
      ));
    } else {
      $db->query('UPDATE `engine4_core_content` SET `order` = `order`+2 WHERE `engine4_core_content`.`page_id` = "'.$page_id.'" AND `engine4_core_content`.`type` = "widget";');

      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content', 'content_id')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle');
      $middle_id = $select->query()->fetchColumn();
      
      $hasBreadcrumb = $db->select()
                          ->from('engine4_core_content', 'content_id')
                          ->where('page_id = ?', $page_id)
                          ->where('name = ?', 'poll.breadcrumb')
                          ->limit(1)
                          ->query()
                          ->fetchColumn();
      if(empty($hasBreadcrumb)) {
        $db->insert('engine4_core_content', array(
            'type' => 'widget',
            'name' => 'poll.breadcrumb',
            'page_id' => $page_id,
            'parent_content_id' => $middle_id,
            'order' => 1,
        ));
      }
      
      $hasCover = $db->select()
                    ->from('engine4_core_content', 'content_id')
                    ->where('page_id = ?', $page_id)
                    ->where('name = ?', 'poll.cover-photo')
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
      if(empty($hasCover)) {
        // middle column content
        $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'poll.cover-photo',
          'parent_content_id' => $middle_id,
          'order' => 2,
          'params' => '',
        ));
      }
    }
  }

  protected function _addUserProfileContent()
  {
    //
    // install content areas
    //
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $page_id = $select->query()->fetchObject()->page_id;

    $isPageExist = $select->query()->fetchObject();
    // poll.profile-polls
    if($isPageExist) {
      $page_id = $isPageExist->page_id;
      // Check if it's already been placed
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'widget')
        ->where('name = ?', 'poll.profile-polls')
        ;

      $info = $select->query()->fetch();

      if( empty($info) ) {
        // container_id (will always be there)
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $page_id)
          ->where('type = ?', 'container')
          ->limit(1);
        $container_id = $select->query()->fetchObject()->content_id;

        // middle_id (will always be there)
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_content')
          ->where('parent_content_id = ?', $container_id)
          ->where('type = ?', 'container')
          ->where('name = ?', 'middle')
          ->limit(1);
        $middle_id = $select->query()->fetchObject()->content_id;

        // tab_id (tab container) may not always be there
        $select
          ->reset('where')
          ->where('type = ?', 'widget')
          ->where('name = ?', 'core.container-tabs')
          ->where('page_id = ?', $page_id)
          ->limit(1);
        $tab_id = $select->query()->fetchObject();
        if( $tab_id && @$tab_id->content_id ) {
            $tab_id = $tab_id->content_id;
        } else {
          $tab_id = null;
        }

        // tab on profile
        $db->insert('engine4_core_content', array(
          'page_id' => $page_id,
          'type'    => 'widget',
          'name'    => 'poll.profile-polls',
          'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
          'order'   => 11,
          'params'  => '{"title":"Polls","titleCount":true}',
        ));
      }
    }
  }

  // Create and populate `view_privacy` column
  protected function _addPrivacyColumn()
  {
    if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.3', $this->_currentVersion, '<=') ) {
      return $this;
    }

    $db = $this->getDb();
    $sql = "ALTER TABLE `engine4_poll_polls` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'owner' AFTER `closed`";
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
      ->where('resource_type = ?', 'poll')
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
      }

      $db->update('engine4_poll_polls',array(
            'view_privacy' => $viewPrivacy,
            ), array(
            'poll_id = ?' => $privacy['resource_id'],
          ));
    }

    return $this;
  }
}
?>
