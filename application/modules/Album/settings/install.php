<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9898 2013-02-14 00:59:42Z shaun $
 * @author     Stephen
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_album_albums' => array('like_count'),
      'engine4_album_photos' => array('like_count')
    ),
    '4.9.1' => array(
      'engine4_album_albums' => array('view_privacy'),
    )
  );

  public function onInstall()
  {
    $this->_albumPhotoViewPage();
    $this->_albumViewPage();
    $this->_albumBrowsePage();
    $this->_userProfileAlbums();
    $this->_albumCreatePage();
    $this->_albumManagePage();
    $this->_addPrivacyColumn();
    $this->_albumPhotoBrowsePage();
    $this->_addHashtagSearchContent();
    parent::onInstall();
  }

  protected function _addHashtagSearchContent() {
  
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);

    // hashtag search page
    $select
        ->from('engine4_core_pages')
        ->where('name = ?', 'core_hashtag_index')
        ->limit(1);
    $isPageExist = $select->query()->fetchObject();
    if($isPageExist) {
      $pageId = $isPageExist->page_id;
      if($pageId) {
        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('page_id = ?', $pageId)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'album.hashtag-search-results');
        $info = $select->query()->fetch();

        if( empty($info) ) {
          // container_id (will always be there)
          $select = new Zend_Db_Select($db);
          $select
              ->from('engine4_core_content')
              ->where('page_id = ?', $pageId)
              ->where('type = ?', 'container')
              ->limit(1);
          $containerId = $select->query()->fetchObject()->content_id;

          // middle_id (will always be there)
          $select = new Zend_Db_Select($db);
          $select
              ->from('engine4_core_content')
              ->where('parent_content_id = ?', $containerId)
              ->where('type = ?', 'container')
              ->where('name = ?', 'middle')
              ->limit(1);
          $middleId = $select->query()->fetchObject()->content_id;

          // tab_id (tab container) may not always be there
          $select
              ->reset('where')
              ->where('type = ?', 'widget')
              ->where('name = ?', 'core.container-tabs')
              ->where('page_id = ?', $pageId)
              ->limit(1);
          $tabId = $select->query()->fetchObject();
          if( $tabId && @$tabId->content_id ) {
              $tabId = $tabId->content_id;
          } else {
              $tabId = null;
          }

          // tab on profile
          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type'    => 'widget',
              'name'    => 'album.hashtag-search-results',
              'parent_content_id' => ($tabId ? $tabId : $middleId),
              'order'   => 100,
              'params'  => '{"title":"Photos","titleCount":true}',
          ));
        }
      }
    }
  }
  
  protected function _albumManagePage()
  {

    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_index_manage')
      ->limit(1)
      ->query()
      ->fetchColumn();


    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_index_manage',
        'displayname' => 'Album Manage Page',
        'title' => 'My Albums',
        'description' => 'This page lists album a user\'s albums.',
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
        'name' => 'album.browse-menu',
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
        'name' => 'album.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert browse menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'album.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));

    }

    return $this;
  }

  protected function _albumCreatePage()
  {

  $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_index_upload')
      ->limit(1)
      ->query()
      ->fetchColumn();

    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_index_upload',
        'displayname' => 'Album Create Page',
        'title' => 'Add New Photos',
        'description' => 'This page is the album create page.',
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
        'name' => 'album.browse-menu',
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

  protected function _albumPhotoViewPage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_photo_view')
      ->limit(1)
      ->query()
      ->fetchColumn();

    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_photo_view',
        'displayname' => 'Album Photo View Page',
        'title' => 'Album Photo View',
        'description' => 'This page displays an album\'s photo.',
        'provides' => 'subject=album_photo',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

     // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
      ));
      $mainId = $db->lastInsertId();

      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $middleId = $db->lastInsertId();

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'album.breadcrumb-photo',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 1,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 2,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.comments',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 3,
      ));
    } else if($pageId) {
      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'container')
          ->limit(1);
      $containerId = $select->query()->fetchObject()->content_id;

      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_content')
          ->where('parent_content_id = ?', $containerId)
          ->where('type = ?', 'container')
          ->where('name = ?', 'middle')
          ->limit(1);
      $middleId = $select->query()->fetchObject()->content_id;
      
      $select = new Zend_Db_Select($db);
      $select_content = $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'album.breadcrumb-photo')
          ->limit(1);
      $content_id = $select_content->query()->fetchObject()->content_id;
      
      if(empty($content_id)) {
        // Insert content
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'album.breadcrumb-photo',
          'page_id' => $pageId,
          'parent_content_id' => $middleId,
          'order' => 1,
        ));
      }
    }

    return $this;
  }

  protected function _albumViewPage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_album_view')
      ->limit(1)
      ->query()
      ->fetchColumn();

    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_album_view',
        'displayname' => 'Album View Page',
        'title' => 'Album View',
        'description' => 'This page displays an album\'s photos.',
        'provides' => 'subject=album',
        'custom' => 0,
      ));
      $pageId = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => $pageId,
      ));
      $mainId = $db->lastInsertId();

      // Insert middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $pageId,
        'parent_content_id' => $mainId,
        'order' => 2,
      ));
      $middleId = $db->lastInsertId();

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'album.breadcrumb-album',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 1,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.content',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 2,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.comments',
        'page_id' => $pageId,
        'parent_content_id' => $middleId,
        'order' => 3,
      ));
    } else if($pageId) {
      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'container')
          ->limit(1);
      $containerId = $select->query()->fetchObject()->content_id;

      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_content')
          ->where('parent_content_id = ?', $containerId)
          ->where('type = ?', 'container')
          ->where('name = ?', 'middle')
          ->limit(1);
      $middleId = $select->query()->fetchObject()->content_id;
      
      $select = new Zend_Db_Select($db);
      $select_content = $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'album.breadcrumb-album')
          ->limit(1);
      $content_id = $select_content->query()->fetchObject()->content_id;
      
      if(empty($content_id)) {
        $db->query('UPDATE `engine4_core_content` SET `order` = `order`+1 WHERE `engine4_core_content`.`page_id` = "'.$pageId.'" AND `engine4_core_content`.`type` = "widget";');
        // Insert content
        $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'album.breadcrumb-album',
          'page_id' => $pageId,
          'parent_content_id' => $middleId,
          'order' => 1,
        ));
      }
    }

    return $this;
  }

  protected function _albumBrowsePage()
  {

    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_index_browse')
      ->limit(1)
      ->query()
      ->fetchColumn();


    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_index_browse',
        'displayname' => 'Album Browse Page',
        'title' => 'Album Browse',
        'description' => 'This page lists album entries.',
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

      $bannerId = $db->select()
        ->from('engine4_core_banners', 'banner_id')
        ->where('name = ?', 'album')
        ->where('module = ?', 'album')
        ->limit(1)
        ->query()
        ->fetchColumn();
      if(!$bannerId){
        // Insert banner
       $db->insert('engine4_core_banners', array(
          'name' => 'album',
          'module' => 'album',
          'title' => 'Explore. Create. Inspire',
          'body' => 'Share your passion to capture the world. Find inspiration in breathtaking Photos.',
          'photo_id' => 0,
          'params' => '{"label":"Add New Photos","route":"album_general","routeParams":{"action":"upload"}}',
          'custom' => 0
        ));
        $bannerId = $db->lastInsertId();
      }
      if($bannerId) {
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
        'name' => 'album.browse-menu',
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
        'name' => 'album.browse-search',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      // Insert browse menu
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'album.browse-menu-quick',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));

      // Insert list categories
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'album.list-categories',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 3,
      ));
    }

    return $this;
  }

  protected function _albumPhotoBrowsePage()
  {
    $db = $this->getDb();

    // profile page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'album_index_browse-photos')
      ->limit(1)
      ->query()
      ->fetchColumn();


    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'album_index_browse-photos',
        'displayname' => 'Album Photos Browse Page',
        'title' => 'Album Photos Browse',
        'description' => 'This page lists photos entries.',
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

      $bannerId = $db->select()
      ->from('engine4_core_banners', 'banner_id')
      ->where('name = ?', 'album')
      ->where('module = ?', 'album')
      ->limit(1)
      ->query()
      ->fetchColumn();
      if(!$bannerId) {
        // Insert banner
       $db->insert('engine4_core_banners', array(
          'name' => 'album',
          'module' => 'album',
          'title' => 'Explore. Create. Inspire',
          'body' => 'Share your passion to capture the world. Find inspiration in breathtaking Photos.',
          'photo_id' => 0,
          'params' => '{"label":"Add New Photos","route":"album_general","routeParams":{"action":"upload"}}',
          'custom' => 0
        ));
        $bannerId = $db->lastInsertId();
      }
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
        'name' => 'album.browse-menu',
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
        'name' => 'album.search-photos',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));
    }

    return $this;
  }

  protected function _userProfileAlbums()
  {
    //
    // install content areas
    //
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    if( $this->_databaseOperationType == 'upgrade') {
        return;
    }
    
    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $pageId = $select->query()->fetchObject()->page_id;


    // album.profile-albums

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_content')
      ->where('page_id = ?', $pageId)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'album.profile-albums')
      ;

    $info = $select->query()->fetch();

    if( empty($info) ) {
      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $pageId)
        ->where('type = ?', 'container')
        ->limit(1);
      $containerId = $select->query()->fetchObject()->content_id;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('parent_content_id = ?', $containerId)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle')
        ->limit(1);
      $middleId = $select->query()->fetchObject()->content_id;

      // tab_id (tab container) may not always be there
      $select
        ->reset('where')
        ->where('type = ?', 'widget')
        ->where('name = ?', 'core.container-tabs')
        ->where('page_id = ?', $pageId)
        ->limit(1);
      $tabId = $select->query()->fetchObject();
      if( $tabId && @$tabId->content_id ) {
          $tabId = $tabId->content_id;
      } else {
        $tabId = null;
      }

      // tab on profile
      $db->insert('engine4_core_content', array(
        'page_id' => $pageId,
        'type'    => 'widget',
        'name'    => 'album.profile-albums',
        'parent_content_id' => ($tabId ? $tabId : $middleId),
        'order'   => 4,
        'params'  => '{"title":"Albums","titleCount":true}',
      ));

      return $this;

    }
  }

  // Create and populate `view_privacy` column
  protected function _addPrivacyColumn()
  {
    if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.1', $this->_currentVersion, '<=') ) {
      return $this;
    }

    $db = $this->getDb();
    $sql = "ALTER TABLE `engine4_album_albums` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'owner' AFTER `type`";
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
      ->where('resource_type = ?', 'album')
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

      $db->update('engine4_album_albums',array(
            'view_privacy' => $viewPrivacy,
            ), array(
            'album_id = ?' => $privacy['resource_id'],
          ));
    }

    return $this;
  }
  
  public function onPostInstall() {

    $db = $this->getDb();
    
    //Make Super admin wall album
    $adapter = Zend_Registry::get('Zend_Db');
    
    $usersTable = new Zend_Db_Table(array(
      'db' => $adapter,
      'name' => 'engine4_users',
    ));
    $usersTableName = $usersTable->info('name');
    
    $albumTable = new Zend_Db_Table(array(
      'db' => $adapter,
      'name' => 'engine4_album_albums',
    ));
    $select = $usersTable->select()
              ->from($usersTableName)
              ->where('level_id = ?', 1)
              ->limit(1);
    $user = $usersTable->fetchRow($select);
    if($user->user_id) {
      $select = $albumTable->select()
            ->where('owner_type = ?', 'user')
            ->where('owner_id = ?', $user->user_id)
            ->where('type = ?', 'wall')
            ->order('album_id ASC')
            ->limit(1);
      $album = $albumTable->fetchRow($select);
      if( null === $album ) {

        $album = $albumTable->createRow();
        $album->owner_type = 'user';
        $album->owner_id = $user->user_id;
        $album->title = 'Wall Photos';
        $album->type = 'wall';

        $select = new Zend_Db_Select($db);
        $select->from('engine4_core_settings', 'value')
            ->where('name = ?', 'album.searchable');
        $album_searchable = $select->query()->fetchColumn();
        
        $album->search = (int) $album_searchable;
        $album->save();
        
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach($roles as $role) {
          //View privacy
          $db->query('INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) VALUES ("album", "'.$album->album_id.'", "view", "'.$role.'", "0", "1", NULL);');
          
          //Comment Privacy
          $db->query('INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) VALUES ("album", "'.$album->album_id.'", "comment", "'.$role.'", "0", "1", NULL);');
        }
      }
    }
  }
}
