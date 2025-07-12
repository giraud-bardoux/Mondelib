<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9874 2013-02-13 00:48:05Z shaun $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Installer extends Engine_Package_Installer_Module
{
    protected $_dropColumnsOnPreInstall = array(
        '4.9.0' => array(
            'engine4_users' => array('like_count', 'comment_count')
        ),
        '4.10.0' => array(
            'engine4_users' => array('view_privacy'),
        )
    );

    public function onInstall()
    {
        $db = $this->getDb();

        // Add some pages
        if( method_exists($this, '_addGenericPage') ) {
            $this->_addGenericPage('user_auth_login', 'Sign-in', 'Sign-in Page', 'This is the site sign-in page.');
            $this->_addGenericPage('user_signup_index', 'Sign-up', 'Sign-up Page', 'This is the site sign-up page.');
            $this->_addGenericPage('user_auth_forgot', 'Forgot Password', 'Forgot Password Page', 'This is the site forgot password page.');
            $this->_addBrowsePage();
        } else {
            $this->_error('Missing _addGenericPage method');
        }
        
        if($this->_databaseOperationType != 'upgrade'){
					$this->_addContentCoverPhoto();
				}
				$this->_addPrivacyColumn();
				
				// profile page
        //Follow widgets
        $select = new Zend_Db_Select($db);
        $select
          ->from('engine4_core_pages')
          ->where('name = ?', 'user_profile_index')
          ->limit(1);
        $pageId = $select->query()->fetchObject()->page_id;
        if($pageId) {
          // container_id (will always be there)
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
          if($tabId || $middleId) {
            //Followers
            $select = new Zend_Db_Select($db);
            $select_content = $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $pageId)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'user.followers')
                ->limit(1);
            $content = $select_content->query()->fetchObject();
            if(empty(@$content->content_id)) {
              $db->insert('engine4_core_content', array(
                  'page_id' => $pageId,
                  'type'    => 'widget',
                  'name'    => 'user.followers',
                  'parent_content_id' => ($tabId ? $tabId : $middleId),
                  'order'   => 21,
                  'params'  => '{"title":"Followers","titleCount":true,"name":"user.followers","itemCountPerPage":"10"}',
              ));
            }
            
            //Following
            $select = new Zend_Db_Select($db);
            $select_content = $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $pageId)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'user.following')
                ->limit(1);
            $content = $select_content->query()->fetchObject();
            if(empty(@$content->content_id)) {
              $db->insert('engine4_core_content', array(
                  'page_id' => $pageId,
                  'type'    => 'widget',
                  'name'    => 'user.following',
                  'parent_content_id' => ($tabId ? $tabId : $middleId),
                  'order'   => 22,
                  'params'  => '{"title":"Following","titleCount":true,"name":"user.following","itemCountPerPage":"10"}',
              ));
            }
          }
        }
        
				
				//Language work
				//if($this->_databaseOperationType == 'upgrade'){
          $localeObject = Zend_Registry::get('Locale');
          $languages = Zend_Locale::getTranslationList('language', $localeObject);
          $PathFile = APPLICATION_PATH . '/application/languages';
          if (file_exists($PathFile)) {
            $dir_contents = scandir( $PathFile );
            foreach ( $dir_contents as $file ) {
              if ( ($file !== '.') && ($file !== '..') && ($file !== 'index.html')) {
                $languageName = @$languages[$file];
                $select = new Zend_Db_Select($db);
                $select_content = $select
                    ->from('engine4_core_languages')
                    ->where('code =?', $file)
                    ->limit(1);
                $languageObject = $select_content->query()->fetchObject();
                if($languageObject) {
                    $language_id = $languageObject->language_id;
                    if(empty($language_id) && !empty($languageName)) {
                      $db->insert('engine4_core_languages', array(
                        'code' => $file,
                        'name' => $languageName,
                        'fallback' => $file,
                      ));
                      $languageId = $db->lastInsertId();
                      $db->query("UPDATE  `engine4_core_languages` SET  `order` =  '".$languageId."' WHERE  `engine4_core_languages`.`language_id` ='".$languageId."' LIMIT 1");
                    }
                }
              }
            }
          }
        //}
        

        try {
            $cols = $db->describeTable('engine4_users');
            if(!isset($cols['mention'])) {
                $db->query("ALTER TABLE `engine4_users` ".
                    "ADD COLUMN `mention` VARCHAR(24) NOT NULL DEFAULT 'registered'");
            }
        } catch( Exception $e ) {
            throw $e;
        }

        //6.7.0-7.0.0
        try {
          $cols = $db->describeTable('engine4_users');
          if(!isset($cols['location'])) {
            $db->query("ALTER TABLE `engine4_users` ADD `location` VARCHAR(255) NULL DEFAULT NULL;");
          }
        } catch( Exception $e ) {
            throw $e;
        }

        // Run upgrades first to prevent issues with upgrading from older versions
        parent::onInstall();

        // Update all ip address to ipv6
        try {
            $this->_convertToIPv6($db, 'engine4_users', 'creation_ip', false);
            $this->_convertToIPv6($db, 'engine4_users', 'lastlogin_ip', true);
            $this->_convertToIPv6($db, 'engine4_user_logins', 'ip', false);
        } catch( Exception $e ) {
            $this->_error('Query failed with error: ' . $e->getMessage());
        }
    }

    protected function _addBrowsePage()
    {
        $db = $this->getDb();

        // member browse page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'user_index_browse')
            ->limit(1)
            ->query()
            ->fetchColumn();

        // insert if it doesn't exist yet
        if( !$pageId ) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'user_index_browse',
                'displayname' => 'Member Browse Page',
                'title' => 'Member Browse',
                'description' => 'This page show member lists.',
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
                'name' => 'user',
                'module' => 'user',
                'title' => 'Connect with People',
                'body' => 'The world is a book. Those who do not connect with others miss many pages.',
                'photo_id' => 0,
                'params' => '{"label":"Invite","route":"default","routeParams":{"module":"invite"}}',
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
                'name' => 'user.browse-menu',
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
                'name' => 'user.browse-search',
                'page_id' => $pageId,
                'parent_content_id' => $mainRightId,
                'order' => 1,
            ));
        }
    }
    
    protected function _addContentCoverPhoto()
    {
        $db = $this->getDb();

        // profile page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'user_profile_index')
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (empty($pageId)) {
            return;
        }

        $hasCover = $db->select()
            ->from('engine4_core_content', 'content_id')
            ->where('page_id = ?', $pageId)
            ->where('name = ?', 'user.cover-photo')
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (!empty($hasCover)) {
            return;
        }

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
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'user.cover-photo',
                'page_id' => $pageId,
                'parent_content_id' => $topMiddleId,
            ));
        }

        // Remove profile status widget
        $contentId = $db->select()
            ->from('engine4_core_content', 'content_id')
            ->where('page_id = ?', $pageId)
            ->where('name = ?', 'user.profile-status')
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (!empty($contentId)) {
            $db->delete('engine4_core_content', array(
                'content_id = ?' => $contentId,
            ));
        }

        // Remove profile-options widget
        $contentId = $db->select()
            ->from('engine4_core_content', 'content_id')
            ->where('page_id = ?', $pageId)
            ->where('name = ?', 'user.profile-options')
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (!empty($contentId)) {
            $db->delete('engine4_core_content', array(
                'content_id = ?' => $contentId,
            ));
        }

        // Remove profile-photos widget
        $contentId = $db->select()
            ->from('engine4_core_content', 'content_id')
            ->where('page_id = ?', $pageId)
            ->where('name = ?', 'user.profile-photos')
            ->limit(1)
            ->query()
            ->fetchColumn();
        if (!empty($contentId)) {
            $db->delete('engine4_core_content', array(
                'content_id = ?' => $contentId,
            ));
        }
    }

    protected function _convertToIPv6($db, $table, $column, $isNull = false)
    {
        // Note: this group of functions will convert an IPv4 address to the new
        // IPv6-compatibly representation
        // ip = UNHEX(CONV(ip, 10, 16))

        // Detect if this is a 32bit system
        $is32bit = ( ip2long('200.200.200.200') < 0 );
        $offset = ( $is32bit ? '4294967296' : '0' );

        // Describe
        $cols = $db->describeTable($table);

        // Update
        if( isset($cols[$column]) && $cols[$column]['DATA_TYPE'] != 'varbinary(16)' ) {
            $temporaryColumn = $column . '_tmp6';
            // Drop temporary column if it already exists
            if( isset($cols[$temporaryColumn]) ) {
                $db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $temporaryColumn));
            }
            // Create temporary column
            $db->query(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` varbinary(16) default NULL', $table, $temporaryColumn));
            // Copy and convert data
            $db->query(sprintf('UPDATE `%s` SET `%s` = UNHEX(CONV(%s + %u, 10, 16)) WHERE `%s` IS NOT NULL', $table, $temporaryColumn, $column, $offset, $column));
            // Drop old column
            $db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $column));
            // Rename new column
            $db->query(sprintf('ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` varbinary(16) %s', $table, $temporaryColumn, $column, ($isNull ? 'default NULL' : 'NOT NULL')));
        }
    }

    // Create and populate `view_privacy` column
    protected function _addPrivacyColumn()
    {
//         if( $this->_databaseOperationType != 'upgrade' || version_compare('4.10.0', $this->_currentVersion, '<=') ) {
//             return $this;
//         }
        
      $db = $this->getDb();
      
      $table_exist_user = $db->query('SHOW TABLES LIKE \'engine4_users\'')->fetch();
      if (!empty($table_exist_user)) {
        $view_privacy = $db->query('SHOW COLUMNS FROM engine4_users LIKE \'view_privacy\'')->fetch();
        if (empty($view_privacy)) {
          $sql = "ALTER TABLE `engine4_users` ADD `view_privacy` VARCHAR(24) NOT NULL DEFAULT 'everyone';";
          try {
              $db->query($sql);
          } catch( Exception $e ) {
              //return $this->_error('Query failed with error: ' . $e->getMessage());
          }

          // populate `view_privacy` column
          $select = new Zend_Db_Select($db);

          try {
              $select
                  ->from('engine4_authorization_allow', array('resource_id' => 'resource_id', 'privacy_values' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT role)')))
                  ->where('resource_type = ?', 'user')
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

              $db->update('engine4_users',array(
                  'view_privacy' => $viewPrivacy,
              ), array(
                  'user_id = ?' => $privacy['resource_id'],
              ));
          }
        }
      } else {
        return $this;
      }
    }
}
