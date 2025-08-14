<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9895 2013-02-14 00:12:22Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Installer extends Engine_Package_Installer_Module
{
    protected $_dropColumnsOnPreInstall = array(
        '4.9.0' => array(
            'engine4_blog_blogs' => array('like_count', 'photo_id')
        ),
        '4.9.3' => array(
            'engine4_blog_blogs' => array('view_privacy')
        )
    );

    public function onInstall()
    {
        $db = $this->getDb();
        //if($this->_databaseOperationType != 'upgrade'){
            $select = new Zend_Db_Select($db);
        
            $this->_addUserProfileContent();
            $this->_addHashtagSearchContent();
            $this->_addBlogListPage();
            $this->_addBlogViewPage();
            $this->_addBlogBrowsePage();

            $this->_addBlogCreatePage();
            $this->_addBlogManagePage();
        //}
        $this->_addPrivacyColumn();
        parent::onInstall();
    }

    protected function _addBlogManagePage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'blog_index_manage')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'blog_index_manage',
                'displayname' => 'Blog Manage Page',
                'title' => 'My Entries',
                'description' => 'This page lists a user\'s blog entries.',
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
                'name' => 'blog.browse-menu',
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
                'name' => 'blog.browse-search',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 1,
            ));

            // Insert gutter menu
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.browse-menu-quick',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 2,
            ));
        }
    }


    protected function _addBlogCreatePage()
    {

        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'blog_index_create')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if( !$pageId ) {

            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'blog_index_create',
                'displayname' => 'Blog Create Page',
                'title' => 'Write New Entry',
                'description' => 'This page is the blog create page.',
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
                'name' => 'blog.browse-menu',
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

    protected function _addBlogBrowsePage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'blog_index_index')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'blog_index_index',
                'displayname' => 'Blog Browse Page',
                'title' => 'Blog Browse',
                'description' => 'This page lists blog entries.',
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
                'name' => 'blog',
                'module' => 'blog',
                'title' => 'Words have Power',
                'body' => 'Post or read Blogs for inspiration, entertainment, education and more.',
                'photo_id' => 0,
                'params' => '{"label":"Write New Entry","route":"blog_general","routeParams":{"action":"create"}}',
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
                'name' => 'blog.browse-menu',
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
                'name' => 'blog.browse-search',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 1,
            ));

            // Insert gutter menu
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.browse-menu-quick',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 2,
            ));

            // Insert list categories
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.list-categories',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 3,
            ));
        }
    }

    protected function _addBlogListPage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'blog_index_list')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'blog_index_list',
                'displayname' => 'Blog List Page',
                'title' => 'Blog List',
                'description' => 'This page lists a member\'s blog entries.',
                'provides' => 'subject=user',
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

            // Insert left
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'left',
                'page_id' => $pageId,
                'parent_content_id' => $mainId,
                'order' => 1,
            ));
            $leftId = $db->lastInsertId();

            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $pageId,
                'parent_content_id' => $mainId,
                'order' => 2,
            ));
            $middleId = $db->lastInsertId();

            // Insert gutter
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-photo',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 1,
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-menu',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 2,
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-search',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 3,
            ));

            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $pageId,
                'parent_content_id' => $middleId,
                'order' => 1,
            ));
            /*
            $db->insert('engine4_core_content', array(
              'type' => 'widget',
              'name' => 'core.comments',
              'page_id' => $pageId,
              'parent_content_id' => $middleId,
              'order' => 2,
            ));
            */
        }
    }

    protected function _addBlogViewPage()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'blog_index_view')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'blog_index_view',
                'displayname' => 'Blog View Page',
                'title' => 'Blog View',
                'description' => 'This page displays a blog entry.',
                'provides' => 'subject=blog',
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

            // Insert left
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'left',
                'page_id' => $pageId,
                'parent_content_id' => $mainId,
                'order' => 1,
            ));
            $leftId = $db->lastInsertId();

            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $pageId,
                'parent_content_id' => $mainId,
                'order' => 2,
            ));
            $middleId = $db->lastInsertId();

            // Insert gutter
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-photo',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 1,
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-menu',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 2,
            ));
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.gutter-search',
                'page_id' => $pageId,
                'parent_content_id' => $leftId,
                'order' => 3,
            ));

            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'blog.breadcrumb',
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
            ->where('name = ?', 'blog.breadcrumb')
            ->limit(1);
        $content_id = $select_content->query()->fetchObject()->content_id;
        
        if(empty($content_id)) {
          $db->query('UPDATE `engine4_core_content` SET `order` = `order`+1 WHERE `engine4_core_content`.`page_id` = "'.$pageId.'" AND `engine4_core_content`.`type` = "widget";');
          // Insert content
          $db->insert('engine4_core_content', array(
            'type' => 'widget',
            'name' => 'blog.breadcrumb',
            'page_id' => $pageId,
            'parent_content_id' => $middleId,
            'order' => 1,
          ));
        }
      }
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
            ->where('name = ?', 'blog.hashtag-search-results')
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
                'name'    => 'blog.hashtag-search-results',
                'parent_content_id' => ($tabId ? $tabId : $middleId),
                'order'   => 100,
                'params'  => '{"title":"Blogs","titleCount":true}',
            ));
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
        $pageId = $select->query()->fetchObject()->page_id;


        // blog.profile-blogs

        // Check if it's already been placed
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('page_id = ?', $pageId)
            ->where('type = ?', 'widget')
            ->where('name = ?', 'blog.profile-blogs')
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
                'name'    => 'blog.profile-blogs',
                'parent_content_id' => ($tabId ? $tabId : $middleId),
                'order'   => 6,
                'params'  => '{"title":"Blogs","titleCount":true}',
            ));

        }
    }

    // Create and populate `view_privacy` column
    protected function _addPrivacyColumn()
    {
        if( $this->_databaseOperationType != 'upgrade' || version_compare('4.9.3', $this->_currentVersion, '<=') ) {
            return $this;
        }

        $db = $this->getDb();
        $sql = "ALTER TABLE `engine4_blog_blogs` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'owner' AFTER `draft`";
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
                ->where('resource_type = ?', 'blog')
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

            $db->update('engine4_blog_blogs',array(
                'view_privacy' => $viewPrivacy,
            ), array(
                'blog_id = ?' => $privacy['resource_id'],
            ));
        }

        return $this;
    }
}
?>
