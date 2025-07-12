<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: install.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_activity_actiontypes' => array('editable'),
      'engine4_activity_actions' => array('modified_date')
    )
  );
  
  public function onInstall() {
  
    $db = $this->getDb();

    // Activity Attachment View Page
    $pageId = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'activity_index_attachmentview')
      ->limit(1)
      ->query()
      ->fetchColumn();
    // insert if it doesn't exist yet
    if( !$pageId ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'activity_index_attachmentview',
        'displayname' => 'Activity Attachment View',
        'title' => 'Activity Attachment View',
        'description' => 'This page lists photos/videos post from the status box.',
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
        'name' => 'activity.photo-video-info',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 1,
      ));

      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.comments',
        'page_id' => $pageId,
        'parent_content_id' => $mainRightId,
        'order' => 2,
      ));
    }
    
    //Profile Feeds Page
    $page_id = $db->select()
                ->from('engine4_core_pages', 'page_id')
                ->where('name = ?', 'activity_index_view')
                ->limit(1)
                ->query()
                ->fetchColumn();
    $widgetOrder = 1;
    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'activity_index_view',
        'displayname' => 'Activity Profile Page',
        'title' => 'Profile Feed',
        'description' => '',
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

      // Insert main-left
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'left',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 3,
      ));
      $main_left_id = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'right',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 4,
      ));
      $main_right_id = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $top_id,
        'order' => 5,
      ));
      $top_middle_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 6,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'activity.feed',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => $widgetOrder++,
        'params' => '{"title":"What\'s New"}',
      ));
      // insert left content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'user.home-links',
        'page_id' => $page_id,
        'parent_content_id' => $main_left_id,
        'order' => $widgetOrder++,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.statistics',
        'page_id' => $page_id,
        'parent_content_id' => $main_left_id,
        'order' => $widgetOrder++,
        'params' => '{"title":"Statistics"}',
      ));
      // insert right content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.hashtags-cloud',
        'page_id' => $page_id,
        'parent_content_id' => $main_right_id,
        'order' => $widgetOrder++,
        'params' => '{"title":"Trending Hashtags"}',
      ));
    }
    
    
    //Memories On This Day Page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'activity_index_onthisday')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'activity_index_onthisday',
        'displayname' => 'Memories On This Day Page',
        'title' => 'Memories On This Day',
        'description' => 'This page show memories and feeds on this day.',
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

      // Insert main-left
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'left',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 1,
      ));
      $main_left_id = $db->lastInsertId();
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'activity.onthisday-banner',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 1,
      ));
      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'activity.feed',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 2,
      ));
      // insert left content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'user.home-links',
        'page_id' => $page_id,
        'parent_content_id' => $main_left_id,
        'order' => 1,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.statistics',
        'page_id' => $page_id,
        'parent_content_id' => $main_left_id,
        'order' => 2,
      ));
    }
    
    //Sell Something Page
    $page_id = $db->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('name = ?', 'activity_index_sell')
      ->limit(1)
      ->query()
      ->fetchColumn();

    // insert if it doesn't exist yet
    if( !$page_id ) {
      // Insert page
      $db->insert('engine4_core_pages', array(
        'name' => 'activity_index_sell',
        'displayname' => 'Sell Something Page',
        'title' => 'Sell Something Day',
        'description' => 'This page show sell something feed.',
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

      // Insert main-middle
      $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => $page_id,
        'parent_content_id' => $main_id,
        'order' => 2,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert content
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'activity.sell-something',
        'page_id' => $page_id,
        'parent_content_id' => $main_middle_id,
        'order' => 1,
      ));
    }


    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_modules')
          ->where('name = ?', 'activity')
          ->where('version < ?', '7.0.0');
    $isCoreCheck = $select->query()->fetchObject();
    if (!empty($isCoreCheck)) {

      $share_count = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'share_count'")->fetch();
      if (!empty($share_count)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `share_count`;');
      }

      $modified_date = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'modified_date'")->fetch();
      if (!empty($modified_date)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `modified_date`;');
      }

      $attachment_count = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'attachment_count'")->fetch();
      if (!empty($attachment_count)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `attachment_count`;');
      }

      $comment_count = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'comment_count'")->fetch();
      if (!empty($comment_count)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `comment_count`;');
      }

      $like_count = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'like_count'")->fetch();
      if (!empty($like_count)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `like_count`;');
      }

      $privacy = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'privacy'")->fetch();
      if (!empty($privacy)) {
        $db->query('ALTER TABLE `engine4_activity_actions` DROP INDEX `privacy`;');
      }

      $type = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'type'")->fetch();
      if (!empty($type)) {
        $db->query('ALTER TABLE `engine4_activity_stream` DROP INDEX `type`;');
      }

      $SUBJECT = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'SUBJECT'")->fetch();
      if (!empty($SUBJECT)) {
        $db->query('ALTER TABLE `engine4_activity_stream` DROP INDEX `SUBJECT`;');
      }

      $OBJECT = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'OBJECT'")->fetch();
      if (!empty($OBJECT)) {
        $db->query('ALTER TABLE `engine4_activity_stream` DROP INDEX `OBJECT`;');
      }

      //7.0.0 upgrade sql
      $activityActionsCols = $db->describeTable('engine4_activity_actions');
      if(!isset($activityActionsCols['commentable'])) {
        $db->query("ALTER TABLE `engine4_activity_actions` ADD `commentable` TINYINT(1) NOT NULL DEFAULT 1;");
      }

      if(!isset($activityActionsCols['schedule_time'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `schedule_time` varchar(256) NOT NULL ');
      }
      if(!isset($activityActionsCols['approved'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `approved` TINYINT(1) NOT NULL DEFAULT 1 ');
      }
      if(!isset($activityActionsCols['reaction_id'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `reaction_id` INT(11) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['resource_id'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `resource_id` INT( 11 ) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['resource_type'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `resource_type` VARCHAR( 45 ) NULL ');
      }
      if(!isset($activityActionsCols['is_community_ad'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `is_community_ad` TINYINT(1) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['vote_up_count'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `vote_up_count` INT(11) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['vote_down_count'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `vote_down_count` INT(11) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['feedbg_id'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `feedbg_id` INT(11) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['image_id'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `image_id` INT(11) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['view_count'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `view_count` INT UNSIGNED NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['share_count'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `share_count` INT UNSIGNED NOT NULL DEFAULT 0');
      }
      if(!isset($activityActionsCols['posting_type'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `posting_type` TINYINT(1) NOT NULL DEFAULT 0 ');
      }
      if(!isset($activityActionsCols['gif_url'])) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD `gif_url` TEXT NULL DEFAULT NULL');
      }
      $target_type = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'target_type'")->fetch();
      if (empty($target_type)) {
        $db->query('ALTER TABLE `engine4_activity_stream` ADD INDEX(`target_type`);');
      }

      $activityLikesCols = $db->describeTable('engine4_activity_likes');
      if(!isset($activityLikesCols['type'])) {
        $db->query('ALTER TABLE `engine4_activity_likes` ADD `type` TINYINT(1) NOT NULL DEFAULT 1;');
      }

      $activityCommentsCols = $db->describeTable('engine4_activity_comments');
      if(!isset($activityCommentsCols['file_id'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `file_id` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($activityCommentsCols['parent_id'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `parent_id` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($activityCommentsCols['gif_id'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `gif_id` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($activityCommentsCols['gif_url'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `gif_url` TEXT NULL DEFAULT NULL');
      }
      if(!isset($activityCommentsCols['emoji_id'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `emoji_id` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($activityCommentsCols['reply_count'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `reply_count` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($activityCommentsCols['preview'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `preview` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($activityCommentsCols['showpreview'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `showpreview` tinyint(1) NOT NULL DEFAULT "0"');
      }
      if(!isset($activityCommentsCols['vote_up_count'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `vote_up_count` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($activityCommentsCols['vote_down_count'])) {
        $db->query('ALTER TABLE `engine4_activity_comments` ADD `vote_down_count` int(11) NOT NULL DEFAULT "0"');
      }

      $coreCommentsCols = $db->describeTable('engine4_core_comments');
      if(!isset($coreCommentsCols['file_id'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `file_id` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($coreCommentsCols['parent_id'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `parent_id` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($coreCommentsCols['gif_id'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `gif_id` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($coreCommentsCols['gif_url'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `gif_url` TEXT NULL DEFAULT NULL');
      }
      if(!isset($coreCommentsCols['emoji_id'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `emoji_id` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($coreCommentsCols['reply_count'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `reply_count` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($coreCommentsCols['preview'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `preview` int(11) NOT NULL DEFAULT "0" ');
      }
      if(!isset($coreCommentsCols['showpreview'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `showpreview` tinyint(1) NOT NULL DEFAULT "0"');
      }
      if(!isset($coreCommentsCols['vote_up_count'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `vote_up_count` int(11) NOT NULL DEFAULT "0"');
      }
      if(!isset($coreCommentsCols['vote_down_count'])) {
        $db->query('ALTER TABLE `engine4_core_comments` ADD `vote_down_count` int(11) NOT NULL DEFAULT "0"');
      }

      $coreLikesCols = $db->describeTable('engine4_core_likes');
      if(!isset($coreLikesCols['type'])) {
        $db->query('ALTER TABLE `engine4_core_likes` ADD `type` TINYINT(1) NOT NULL DEFAULT 1;');
      }

      $db->query("INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
      ('activity.composer.options.0', 'userTags'),
      ('activity.composer.options.1', 'hashtags'),
      ('activity.composeroptions.0', 'activityfeedgif'),
      ('activity.composeroptions.1', 'feelingssctivity'),
      ('activity.composeroptions.2', 'buysell'),
      ('activity.composeroptions.3', 'tagUseActivity'),
      ('activity.composeroptions.4', 'smilesActivity'),
      ('activity.composeroptions.5', 'locationactivity'),
      ('activity.composeroptions.6', 'shedulepost'),
      ('activity.composeroptions.7', 'stickers'),
      ('activity.composeroptions.8', 'activitylink'),
      ('activity.composeroptions.9', 'activitytargetpost'),
      ('activity.composeroptions.10', 'fileupload'),
      ('activity.composeroptions.11', 'albumvideo'),
      ('activity.enableattachement.0', 'photos'),
      ('activity.enableattachement.1', 'videos'),
      ('activity.enableattachement.2', 'stickers'),
      ('activity.enableattachement.3', 'gif'),
      ('activity.enableattachement.4', 'emotions'),
      ('activity.enablenactivityupdownvote', '0');");

      $db->query('ALTER TABLE `engine4_activity_notifications` ADD INDEX (`user_id`, `read`, `object_type`);');

      $action_id_composite = $db->query("SHOW INDEX FROM `engine4_activity_actions` WHERE Key_name = 'action_id_composite'")->fetch();
      if (empty($action_id_composite)) {
        $db->query('ALTER TABLE `engine4_activity_actions` ADD INDEX `action_id_composite` (`action_id`, `approved`, `is_community_ad`);');
      }      

      $action_id = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'action_id'")->fetch();
      if (empty($action_id)) {
        $db->query('ALTER TABLE `engine4_activity_stream` ADD INDEX `action_id` (`action_id`);');
      }

      $subject_type = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'subject_type'")->fetch();
      if (empty($subject_type)) {
        $db->query('ALTER TABLE `engine4_activity_stream`  ADD INDEX `subject_type` (`subject_type`, `subject_id`);');
      }

      $object_type = $db->query("SHOW INDEX FROM `engine4_activity_stream` WHERE Key_name = 'object_type'")->fetch();
      if (empty($object_type)) {
        $db->query('ALTER TABLE `engine4_activity_stream`  ADD INDEX `object_type` (`object_type`, `object_id`);');
      }

      //Check if advancedsctivity plugin is installed?
      $select = new Zend_Db_Select($db);
      $select->from('engine4_core_modules')
            ->where('name = ?', 'sesadvancedactivity');
      $isSesadvEnabled = $select->query()->fetchObject();
      if (!empty($isSesadvEnabled)) {
        
        $db->query('RENAME TABLE `engine4_sesfeedbg_backgrounds` TO `engine4_activity_backgrounds`;');
        $db->query('RENAME TABLE `engine4_sesfeelingactivity_feelings` TO `engine4_activity_feelings`;');
        $db->query('RENAME TABLE `engine4_sesfeelingactivity_feelingicons` TO `engine4_activity_feelingicons`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_feelingposts` TO `engine4_activity_feelingposts`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_filterlists` TO `engine4_activity_filterlists`;');

        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-sync" WHERE `filtertype` = "all";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-network-wired" WHERE `filtertype` = "my_networks";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-users" WHERE `filtertype` = "my_friends";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-comment" WHERE `filtertype` = "posts";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-save" WHERE `filtertype` = "saved_feeds";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-shopping-cart" WHERE `filtertype` = "post_self_buysell";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-clock" WHERE `filtertype` = "scheduled_post";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-share-alt" WHERE `filtertype` = "share";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-image" WHERE `filtertype` = "album";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-video" WHERE `filtertype` = "video";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-music" WHERE `filtertype` = "music";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa-regular fa-file" WHERE `filtertype` = "post_self_file";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-comments" WHERE `filtertype` = "blog";');

        $db->query('RENAME TABLE `engine4_sesadvancedcomment_emotioncategories` TO `engine4_comment_emotioncategories`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_emotiongalleries` TO `engine4_comment_emotiongalleries`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_emotionfiles` TO `engine4_comment_emotionfiles`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_useremotions` TO `engine4_comment_useremotions`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_commentfiles` TO `engine4_comment_commentfiles`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_reactions` TO `engine4_comment_reactions`;');
        $db->query('RENAME TABLE `engine4_sesadvancedcomment_voteupdowns` TO `engine4_comment_voteupdowns`;');

        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'comment_tagged_item', `module` = 'comment' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedcomment_tagged_item';");
        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'comment_tagged_people', `module` = 'comment' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedcomment_tagged_people';");
        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'comment_taggedreply_people', `module` = 'comment' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedcomment_taggedreply_people';");
        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'comment_replycomment', `module` = 'comment' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedcomment_replycomment';");

        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'comment_tagged_item' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedcomment_tagged_item';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'comment_tagged_people' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedcomment_tagged_people';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'comment_taggedreply_people' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedcomment_taggedreply_people';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'comment_replycomment' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedcomment_replycomment';");
        
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_link';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_music';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_photo';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_video';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_file';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_self_buysell';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_video';");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `module` = 'activity' WHERE `engine4_activity_actiontypes`.`type` = 'post_photo';");
        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES ("post_self_photo_video", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0);');

        $db->query('RENAME TABLE `engine4_sesadvancedactivity_tagusers` TO `engine4_activity_tagusers`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_files` TO `engine4_activity_files`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_buysells` TO `engine4_activity_buysells`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_hashtags` TO `engine4_activity_hashtags`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_savefeeds` TO `engine4_activity_savefeeds`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_hides` TO `engine4_activity_hides`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_targetpost` TO `engine4_activity_targetpost`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_pinposts` TO `engine4_activity_pinposts`;');
        $db->query('RENAME TABLE `engine4_sesadvancedactivity_tagitems` TO `engine4_activity_tagitems`;');

        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'activity_tagged_people', `module` = 'activity' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_tagged_people';");
        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'activity_scheduled_live', `module` = 'activity' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_scheduled_live';");
        $db->query("UPDATE `engine4_activity_notificationtypes` SET `type` = 'activity_reacted', `module` = 'activity' WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted';");

        $db->query('UPDATE `engine4_activity_actions` oTable, engine4_sesadvancedactivity_details nTable SET oTable.`commentable` = nTable.`commentable`, oTable.`schedule_time` = nTable.`schedule_time`, oTable.`approved` = nTable.`sesapproved`, oTable.`reaction_id` = nTable.`reaction_id`,oTable.`resource_id` = nTable.`sesresource_id`,oTable.`resource_type` = nTable.`sesresource_type`,oTable.`is_community_ad` = nTable.`is_community_ad`,oTable.`vote_up_count` = nTable.`vote_up_count`,oTable.`vote_down_count` = nTable.`vote_down_count`,oTable.`feedbg_id` = nTable.`feedbg_id`,oTable.`view_count` = nTable.`view_count`,oTable.`image_id` = nTable.`image_id`,oTable.`share_count` = nTable.`share_count`,oTable.`posting_type` = nTable.`posting_type`  where oTable.action_id = nTable.action_id;');

        $db->query('UPDATE engine4_activity_likes oTable,engine4_sesadvancedactivity_activitylikes as nTable set oTable.type = nTable.type WHERE oTable.like_id = nTable.activity_like_id;');

        $db->query('UPDATE engine4_activity_comments oTable,engine4_sesadvancedactivity_activitycomments as nTable set oTable.file_id = nTable.file_id, oTable.parent_id = nTable.parent_id,oTable.gif_id = nTable.gif_id,oTable.emoji_id = nTable.emoji_id,oTable.reply_count = nTable.reply_count,oTable.preview = nTable.preview,oTable.showpreview = nTable.showpreview,oTable.vote_up_count = nTable.vote_up_count,oTable.vote_down_count = nTable.vote_down_count WHERE oTable.comment_id = nTable.activity_comment_id;');

        $db->query('UPDATE engine4_core_comments oTable,engine4_sesadvancedactivity_corecomments as nTable set oTable.file_id = nTable.file_id, oTable.parent_id = nTable.parent_id,oTable.gif_id = nTable.gif_id,oTable.emoji_id = nTable.emoji_id,oTable.reply_count = nTable.reply_count,oTable.preview = nTable.preview,oTable.showpreview = nTable.showpreview,oTable.vote_up_count = nTable.vote_up_count,oTable.vote_down_count = nTable.vote_down_count WHERE oTable.comment_id = nTable.core_comment_id;');

        $db->query('UPDATE engine4_core_likes oTable,engine4_sesadvancedactivity_corelikes as nTable set oTable.type = nTable.type WHERE oTable.like_id = nTable.core_like_id;');

        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'activity_reacted' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedactivity_reacted_love';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'activity_reacted' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedactivity_reacted_haha';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'activity_reacted' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedactivity_reacted_angry';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'activity_reacted' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedactivity_reacted_sad';");
        $db->query("UPDATE `engine4_activity_notifications` SET `type` = 'activity_reacted' WHERE `engine4_activity_notifications`.`type` = 'sesadvancedactivity_reacted_wow';");

        $db->query("DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted_love';");
        $db->query("DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted_haha';");
        $db->query("DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted_angry';");
        $db->query("DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted_sad';");
        $db->query("DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'sesadvancedactivity_reacted_wow';");

      } else {

        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_backgrounds` (
          `background_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `file_id` int(11) unsigned NOT NULL,
          `order` INT(11) NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT "1",
          `starttime` DATE NULL,
          `endtime` DATE NULL,
          `enableenddate` TINYINT(1) NOT NULL DEFAULT "1",
          `featured` TINYINT(1) NOT NULL DEFAULT "0",
          PRIMARY KEY  (`background_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
		
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_feelings` (
          `feeling_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title` VARCHAR(255) NOT NULL,
          `type` VARCHAR(255) NOT NULL,
          `file_id` int(11) unsigned NOT NULL,
          `order` INT(11) NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT "1",
          PRIMARY KEY  (`feeling_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_feelingicons` (
          `feelingicon_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `feeling_id` int(11) unsigned NOT NULL,
          `type` VARCHAR(255) NOT NULL,
          `title` VARCHAR(255) NOT NULL,
          `feeling_icon` int(11) unsigned NOT NULL,
          `resource_type` VARCHAR(255) NOT NULL,
          `order` INT(11) NOT NULL,
          PRIMARY KEY  (`feelingicon_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_feelingposts` (
          `feelingpost_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `feeling_id` int(11) unsigned NOT NULL,
          `feelingicon_id` int(11) unsigned NOT NULL,
          `resource_type` varchar(255) DEFAULT NULL,
          `action_id` int(11) unsigned NOT NULL,
          `feeling_custom` TINYINT(1) NOT NULL DEFAULT "0",
          `feeling_customtext` VARCHAR(255) NULL,
          PRIMARY KEY  (`feelingpost_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci');

        $db->query('INSERT IGNORE INTO `engine4_activity_feelings` (`feeling_id`, `title`, `type`, `file_id`, `order`) VALUES
        (1, "Feeling", "1", 0, 1),
        (2, "Celebrating", "1", 0, 2),
        (3, "Just", "1", 0, 3),
        (4, "Drinking", "1", 0, 4),
        (5, "Eating", "1", 0, 5),
        (6, "Attending", "1", 0, 11),
        (7, "Getting", "1", 0, 12),
        (8, "Looking For", "1", 0, 13),
        (9, "Making", "1", 0, 14),
        (10, "Meeting", "1", 0, 15),
        (11, "Remembering", "1", 0, 16),
        (12, "Thinking About", "1", 0, 17),
        (13, "Watching", "2", 0, 6),
        (14, "Reading", "2", 0, 10),
        (15, "Listening to", "2", 0, 9),
        (18, "Browsing", "2", 0, 7),
        (19, "Attending Event", "2", 0, 8);');
	
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_filterlists` (
          `filterlist_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `filtertype` VARCHAR(255) NOT NULL,
          `module` VARCHAR(255) NOT NULL,
          `title` varchar(255) NOT NULL,
          `active` TINYINT(1) NOT NULL DEFAULT "1",
          `is_delete` TINYINT(1) NOT NULL DEFAULT "1",
          `order` INT(11),
          `file_id` INT(11) NOT NULL DEFAULT "0",
          `icon` VARCHAR(128) NULL DEFAULT NULL,
          PRIMARY KEY  (`filterlist_id`),
          UNIQUE( `filtertype`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

        $db->query('ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`module`);');
        $db->query('ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`active`);');
        $db->query('ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`is_delete`);');
        $db->query('ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`order`);');
        $db->query('ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`file_id`);');

        $db->query('INSERT IGNORE INTO `engine4_activity_filterlists` (`filtertype`, `module`, `title`, `active`, `is_delete`, `order`) VALUES
        ("all", "Core", "All Updates", 1, 0, 1),
        ("my_networks", "Networks", "My Network", 1, 0, 3),
        ("my_friends", "Members", "Friends", 1, 0, 2),
        ("posts", "Core", "Posts", 1, 0, 12),
        ("saved_feeds", "Core", "Saved Feeds", 1, 0, 13),
        ("post_self_buysell", "Core", "Sell Something", 1, 0, 9),
        ("post_self_file", "Core", "Files", 1, 0, 10),
        ("scheduled_post", "Core", "Scheduled Post", 1, 0, 11),
        ("event", "Events", "Events", 1, 1, 7),
        ("album", "Albums", "Photos", 1, 1, 4),
        ("blog", "Blogs", "Blogs", 1, 1, 8),
        ("music", "Music", "Music", 1, 1, 6),
        ("video", "Videos", "Videos", 1, 1, 5),
        ("poll", "Polls", "Polls", 1, 1, 5),
        ("group", "Groups", "Groups", 1, 1, 5),
        ("classified", "Classifieds", "Classifieds", 1, 1, 5),
        ("share", "core", "Share Feeds", "1", "0", 10);');

        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-sync" WHERE `filtertype` = "all";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-network-wired" WHERE `filtertype` = "my_networks";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-users" WHERE `filtertype` = "my_friends";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-comment" WHERE `filtertype` = "posts";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-save" WHERE `filtertype` = "saved_feeds";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-shopping-cart" WHERE `filtertype` = "post_self_buysell";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-clock" WHERE `filtertype` = "scheduled_post";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fas fa-share-alt" WHERE `filtertype` = "share";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-image" WHERE `filtertype` = "album";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-video" WHERE `filtertype` = "video";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-music" WHERE `filtertype` = "music";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa-regular fa-file" WHERE `filtertype` = "post_self_file";');
        $db->query('UPDATE `engine4_activity_filterlists` SET `icon`= "fa fa-comments" WHERE `filtertype` = "blog";');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_emotioncategories` (
          `category_id` int(11) NOT NULL AUTO_INCREMENT,
          `title` VARCHAR( 255 ) NOT NULL,
          `color` varchar(128) NOT NULL,
          `file_id` int(11) NOT NULL DEFAULT "0",
          PRIMARY KEY (`category_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_emotiongalleries` (
          `gallery_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title` VARCHAR(255) NOT NULL,
          `file_id` int(11) unsigned NOT NULL,
          `category_id` INT(11) NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT "1",
          PRIMARY KEY  (`gallery_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_emotionfiles` (
          `files_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `photo_id` int(11) unsigned NOT NULL,
          `gallery_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`files_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_useremotions` (
          `emotion_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` INT(11) unsigned NOT NULL,
          `gallery_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`emotion_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_commentfiles` (
          `commentfile_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `comment_id` INT(11) unsigned NOT NULL,
          `type` VARCHAR(255) NOT NULL,
          `file_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`commentfile_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('INSERT IGNORE INTO `engine4_comment_emotioncategories` (`category_id`, `title`, `color`, `file_id`) VALUES
        (1, "Happy", "#FF4912", 26983),
        (2, "In Love", "#F64E88", 26984),
        (3, "Sad", "#A9A192", 26985),
        (4, "Eating", "#FC8A0F", 26986),
        (5, "Celebrating", "#95C63F", 26987),
        (6, "Active", "#54C6E3", 26988),
        (7, "Working", "#19B596", 26989),
        (8, "Sleepy", "#9571A9", 26990),
        (9, "Angry", "#ED513E", 26991),
        (10, "Confused", "#B37736", 26992);');
        $db->query('INSERT IGNORE INTO `engine4_comment_emotiongalleries` (`gallery_id`, `title`, `file_id`, `category_id`) VALUES
        (1, "Meep", 26993, 1),
        (2, "Minions", 27030, 1),
        (3, "Lazy Life Line", 27053, 8),
        (4, "Waddles", 27074, 1),
        (5, "Panda", 27109, 2),
        (6, "Tom And Jerry", 27148, 6);');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_reactions` (
          `reaction_id` int(11) NOT NULL AUTO_INCREMENT,
          `title` VARCHAR( 255 ) NOT NULL,
          `file_id` int(11) NOT NULL DEFAULT "0",
          `enabled` TINYINT(1) NOT NULL DEFAULT "1",
          PRIMARY KEY (`reaction_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;');
        $db->query('INSERT IGNORE INTO `engine4_comment_reactions` (`reaction_id`, `title`, `enabled`, `file_id`) VALUES
        (1, "Like", 1, 0),
        (2, "Love", 1, 0),
        (3, "Haha", 1, 0),
        (4, "Wow", 1, 0),
        (5, "Angry", 1, 0),
        (6, "Sad", 1, 0);');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_comment_voteupdowns` (
          `voteupdown_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `type` VARCHAR(10) NOT NULL DEFAULT "upvote",
          `resource_type` VARCHAR(100) NOT NULL,
          `resource_id` INT(11) NOT NULL,
          `user_type` VARCHAR(100) NOT NULL,
          `user_id` int(11) NOT NULL,
          `creation_date` datetime NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES 
        ("comment_tagged_item", "comment", \'{item:$subject} tagged your {var:$itemurl} in a {var:$postLink}.\', "0", "", "1"),
        ("comment_tagged_people", "comment", \'{item:$object} mention you in a {var:$commentLink}.\', 0, "", 1),
        ("comment_taggedreply_people", "comment", \'{item:$object} mention you in a {var:$commentLink} on comment.\', 0, "", 1), 
        ("comment_replycomment", "comment", \'{item:$subject} replied to your comment on a {item:$object:$label}.\', 0, "", 1);');
        $db->query('INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
        ("post_self_link", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 1, 0),
        ("post_self_music", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 1, 0),
        ("post_self_photo", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0),
        ("post_self_video", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0),
        ("post_self_file", "activity", \'{item:$subject} uploaded a file.{body:$body}\', 1, 5, 1, 4, 0, 0),
        ("post_self_buysell", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0),
        ("post_video", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0),
        ("post_photo", "activity", \'{actors:$subject:$object}: {body:$body}\', 1, 7, 1, 4, 4, 0),
        ("post_music", "activity", \'{actors:$subject:$object}: {body:$body}\', 1, 7, 1, 4, 1, 0),
        ("post_self_photo_video", "activity", \'{item:$subject} {body:$body}\', 1, 5, 1, 4, 4, 0);');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_tagusers` (
          `taguser_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int(11) unsigned NOT NULL,
          `action_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`taguser_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_files` (
          `file_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int(11) unsigned NOT NULL,
          `item_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`file_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_buysells` (
          `buysell_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int(11) unsigned NOT NULL,
          `action_id` int(11) unsigned NOT NULL,
          `title` varchar(255) NOT NULL,
          `price` DECIMAL(8,2) NOT NULL default "0",
          `currency` varchar(45) NOT NULL,
          `description` TEXT NULL,
          `is_sold` TINYINT(1) NOT NULL default "0",
          `buy` VARCHAR(1000) NULL,
          `location` VARCHAR(255) NULL DEFAULT NULL,
          PRIMARY KEY  (`buysell_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_hashtags` (
          `hashtag_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `action_id` int(11) unsigned NOT NULL,
          `title` varchar(255) NOT NULL,
          PRIMARY KEY  (`hashtag_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_savefeeds` (
          `savefeed_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `action_id` int(11) unsigned NOT NULL,
          `user_id` int(11) unsigned NOT NULL,
          PRIMARY KEY  (`savefeed_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_hides` (
          `hide_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `resource_id` int(11) unsigned NOT NULL,
          `resource_type` VARCHAR(20) NOT NULL,
          `user_id` int(11) unsigned NOT NULL,
          `subject_id` INT NULL DEFAULT NULL,
          PRIMARY KEY  (`hide_id`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`, `processes`, `semaphore`, `started_last`, `started_count`, `completed_last`, `completed_count`, `failure_last`, `failure_count`, `success_last`, `success_count`) VALUES ("Schedule Post", "activity", "Activity_Plugin_Task_Jobs", "100", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0");');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_targetpost` (
          `targetpost_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `action_id` int(20) UNSIGNED NOT NULL,
          `location_send` varchar(255)  NOT NULL DEFAULT "",
          `country_name` varchar(255)  NOT NULL DEFAULT "",
          `city_name` varchar(255)  NOT NULL DEFAULT "",
          `location_city` varchar(255)  NOT NULL DEFAULT "",
          `location_country` varchar(255)  NOT NULL DEFAULT "",
          `gender_send` varchar(255)  NOT NULL DEFAULT "",
          `age_min_send` varchar(255)  NOT NULL DEFAULT "",
          `age_max_send` varchar(255)  NOT NULL DEFAULT "",
          `lat` varchar(255)  NOT NULL DEFAULT "",
          `lng` varchar(255)  NOT NULL DEFAULT "",
          PRIMARY KEY  (`targetpost_id`),
          UNIQUE KEY `action_id` (`action_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_pinposts` (
          `pinpost_id` int(11) NOT NULL AUTO_INCREMENT,
          `action_id` int(11) NOT NULL,
          `resource_id` int(11) NOT NULL,
          `resource_type` varchar(255) NOT NULL,
          PRIMARY KEY (`pinpost_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('CREATE TABLE IF NOT EXISTS `engine4_activity_tagitems` (
          `tagitem_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `resource_id` INT(11) NOT NULL,
          `resource_type` VARCHAR(255) NOT NULL,
          `user_id` INT(11) NOT NULL,
          `action_id` INT(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $db->query('INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
        ("activity_tagged_people", "activity", \'{item:$subject} tagged you in a {var:$postLink}.\', 0, "", 1),
        ("activity_scheduled_live", "activity", "Your scheduled {var:$postLink} has been made live.", 0, "", 1),
        ("activity_reacted", "activity", \'{item:$subject} reacted {var:$reactionTitle} to your {item:$object:$label}.\', 0, "", 1);');
      }
    }
    
    parent::onInstall();
  }
}
