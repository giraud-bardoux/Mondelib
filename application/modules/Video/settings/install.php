<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9882 2013-02-13 20:10:47Z shaun $
 * @author     Stephen
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Installer extends Engine_Package_Installer_Module
{
    protected $_dropColumnsOnPreInstall = array(
        '4.9.0' => array(
            'engine4_video_videos' => array('like_count')
        ),
        '4.9.2' => array(
            'engine4_video_videos' => array('view_privacy'),
        )
    );

    public function onInstall()
    {
        $this->_checkFfmpegPath();
        //if($this->_databaseOperationType != 'upgrade'){
            $this->_addUserProfileContent();
            $this->_addHashtagSearchContent();
            $this->_addVideoViewPage();
            $this->_addVideoBrowsePage();
            $this->_addVideoCreatePage();
            $this->_addVideoManagePage();
            $this->_addPrivacyColumn();
        //}
        parent::onInstall();
    }


    protected function _addHashtagSearchContent()
    {
        $db = $this->getDb();
        $select = new Zend_Db_Select($db);

        // hashtag search page
        $pageId = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'core_hashtag_index')
        ->limit(1)
        ->query()
        ->fetchColumn();
        if(!$pageId){
            return;
        }

        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('page_id = ?', $pageId)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'video.hashtag-search-results')
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
                'name'    => 'video.hashtag-search-results',
                'parent_content_id' => ($tabId ? $tabId : $middleId),
                'order'   => 300,
                'params'  => '{"title":"Videos","titleCount":true}',
            ));
        }
    }


    protected function _addVideoManagePage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'video_index_manage')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'video_index_manage',
                'displayname' => 'Video Manage Page',
                'title' => 'My Videos',
                'description' => 'This page lists a user\'s videos.',
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
                'name' => 'video.browse-menu',
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
                'name' => 'video.browse-search',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 1,
            ));

            // Insert gutter menu
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'video.browse-menu-quick',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 2,
            ));
        }
    }

    protected function _addVideoCreatePage()
    {
        $db = $this->getDb();

        // create page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'video_index_create')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'video_index_create',
                'displayname' => 'Video Create Page',
                'title' => 'Video Create',
                'description' => 'This page allows video to be added.',
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
                'name' => 'video.browse-menu',
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

    protected function _addVideoBrowsePage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'video_index_browse')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'video_index_browse',
                'displayname' => 'Video Browse Page',
                'title' => 'Video Browse',
                'description' => 'This page lists videos.',
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
                'name' => 'video',
                'module' => 'video',
                'title' => 'Grab some Popcorn',
                'body' => 'Play the moments. Pause the memories. Rewind the happiness. Explore Videos!',
                'photo_id' => 0,
                'params' => '{"label":"Post New Video","route":"video_general","routeParams":{"action":"create"}}',
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
                'name' => 'video.browse-menu',
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
                'order' => 2,
            ));

            // Insert search
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'video.browse-search',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 1,
            ));

            // Insert gutter menu
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'video.browse-menu-quick',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 2,
            ));

            // Insert list categories
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'video.list-categories',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 3,
            ));
        }
    }

    protected function _checkFfmpegPath()
    {
        $db     = $this->getDb();
        $select = new Zend_Db_Select($db);

        // Check ffmpeg path for correctness
        if( function_exists('exec') && function_exists('shell_exec') ) {
            // Api is not available
            //$ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
            $ffmpeg_path = $db->select()
                ->from('engine4_core_settings', 'value')
                ->where('name = ?', 'video.ffmpeg.path')
                ->limit(1)
                ->query()
                ->fetchColumn(0)
            ;

            $output = null;
            $return = null;
            if( !empty($ffmpeg_path) ) {
                exec($ffmpeg_path . ' -version', $output, $return);
            }
            // Try to auto-guess ffmpeg path if it is not set correctly
            $ffmpeg_path_original = $ffmpeg_path;
            if( empty($ffmpeg_path) || $return > 0 || stripos(join('', $output), 'ffmpeg') === false ) {
                $ffmpeg_path = null;
                // Windows
                if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
                    // @todo
                }
                // Not windows
                else {
                    $output = null;
                    $return = null;
                    @exec('which ffmpeg', $output, $return);
                    if( 0 == $return ) {
                        $ffmpeg_path = array_shift($output);
                        $output = null;
                        $return = null;
                        exec($ffmpeg_path . ' -version', $output, $return);
                        if( 0 == $return ) {
                            $ffmpeg_path = null;
                        }
                    }
                }
            }
            if( $ffmpeg_path != $ffmpeg_path_original ) {
                $count = $db->update('engine4_core_settings', array(
                    'value' => $ffmpeg_path,
                ), array(
                    'name = ?' => 'video.ffmpeg.path',
                ));
                if( $count === 0 ) {
                    try {
                        $db->insert('engine4_core_settings', array(
                            'value' => $ffmpeg_path,
                            'name' => 'video.ffmpeg.path',
                        ));
                    } catch( Exception $e ) {

                    }
                }
            }
        }
    }

    protected function _addUserProfileContent()
    {
        $db     = $this->getDb();
        $select = new Zend_Db_Select($db);


        // profile page
        $select
            ->from('engine4_core_pages')
            ->where('name = ?', 'user_profile_index')
            ->limit(1);
        $pageId = $select->query()->fetchObject()->page_id;


        // video.profile-videos

        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('page_id = ?', $pageId)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'video.profile-videos')
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
                'name'    => 'video.profile-videos',
                'parent_content_id' => ($tabId ? $tabId : $middleId),
                'order'   => 12,
                'params'  => '{"title":"Videos","titleCount":true}',
            ));

        }
    }

    protected function _addVideoViewPage()
    {
      $db     = $this->getDb();
      // profile page
      $pageId = $db->select()
          ->from('engine4_core_pages', 'page_id')
          ->where('name = ?', 'video_index_view')
          ->limit(1)
          ->query()
          ->fetchColumn();

      if( empty($pageId) ) {
          $db->insert('engine4_core_pages', array(
              'name' => 'video_index_view',
              'displayname' => 'Video View Page',
              'title' => 'View Video',
              'description' => 'This is the view page for a video.',
              'custom' => 0,
              'provides' => 'subject=video',
          ));
          $pageId = $db->lastInsertId('engine4_core_pages');

          // containers
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
              'name' => 'right',
              'parent_content_id' => $containerId,
              'order' => 1,
              'params' => '',
          ));
          $rightId = $db->lastInsertId('engine4_core_content');

          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'container',
              'name' => 'middle',
              'parent_content_id' => $containerId,
              'order' => 3,
              'params' => '',
          ));
          $middleId = $db->lastInsertId('engine4_core_content');
          
          // middle column content
          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'video.breadcrumb',
              'parent_content_id' => $middleId,
              'order' => 1,
              'params' => '',
          ));

          // middle column content
          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'core.content',
              'parent_content_id' => $middleId,
              'order' => 2,
              'params' => '',
          ));

          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'core.comments',
              'parent_content_id' => $middleId,
              'order' => 3,
              'params' => '',
          ));

          // right column
          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'video.show-same-tags',
              'parent_content_id' => $rightId,
              'order' => 1,
              'params' => '',
          ));

          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'video.show-also-liked',
              'parent_content_id' => $rightId,
              'order' => 2,
              'params' => '',
          ));

          $db->insert('engine4_core_content', array(
              'page_id' => $pageId,
              'type' => 'widget',
              'name' => 'video.show-same-poster',
              'parent_content_id' => $rightId,
              'order' => 3,
              'params' => '',
          ));
      } else {
        $db->query('UPDATE `engine4_core_content` SET `order` = `order`+1 WHERE `engine4_core_content`.`page_id` = "'.$pageId.'" AND `engine4_core_content`.`type` = "widget";');

        $hasBreadcrumb = $db->select()
                  ->from('engine4_core_content', 'content_id')
                  ->where('page_id = ?', $pageId)
                  ->where('name = ?', 'video.breadcrumb')
                  ->limit(1)
                  ->query()
                  ->fetchColumn();
        
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_content', 'content_id')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'container')
          ->where('name = ?', 'middle');
        $middle_id = $select->query()->fetchColumn();
        
        if(empty($hasBreadcrumb)) {
          $db->insert('engine4_core_content', array(
            'type' => 'widget',
            'name' => 'video.breadcrumb',
            'page_id' => $pageId,
            'parent_content_id' => $middle_id,
            'order' => 1,
          ));
        }
      }
    }

    // Create and populate `view_privacy` column
    protected function _addPrivacyColumn()
    {
        if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.2', $this->_currentVersion, '<=') ) {
            return $this;
        }

        $db = $this->getDb();
        $sql = "ALTER TABLE `engine4_video_videos` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'owner' AFTER `rotation`";
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
                ->where('resource_type = ?', 'video')
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

            $db->update('engine4_video_videos', array(
                'view_privacy' => $viewPrivacy,
            ), array(
                'video_id = ?' => $privacy['resource_id'],
            ));
        }

        return $this;
    }
}
