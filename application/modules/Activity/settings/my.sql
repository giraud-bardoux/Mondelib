
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: my.sql 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actions`
--

DROP TABLE IF EXISTS `engine4_activity_actions`;
CREATE TABLE `engine4_activity_actions` (
  `action_id` int(11) unsigned NOT NULL auto_increment,
  `type` varchar(128) NOT NULL,
  `subject_type` varchar(24) NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `body` text NULL,
  `params` text NULL,
  `date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `attachment_count` smallint(3) unsigned NOT NULL default '0',
  `comment_count` mediumint(5) unsigned NOT NULL default '0',
  `like_count` mediumint(5) unsigned NOT NULL default '0',
  `privacy` varchar(500) NULL default NULL,
  `commentable` TINYINT(1) NOT NULL DEFAULT 1,
  `schedule_time` varchar(256) NOT NULL,
  `approved` TINYINT(1) NOT NULL DEFAULT 1,
  `reaction_id` INT(11) NOT NULL DEFAULT 0,
  `resource_id` INT( 11 ) NOT NULL DEFAULT 0,
  `resource_type` VARCHAR( 45 ) NULL,
  `is_community_ad` TINYINT(1) NOT NULL DEFAULT 0,
  `vote_up_count` INT(11) NOT NULL DEFAULT 0,
  `vote_down_count` INT(11) NOT NULL DEFAULT 0,
  `feedbg_id` INT(11) NOT NULL DEFAULT 0,
  `image_id` INT(11) NOT NULL DEFAULT 0,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `share_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `posting_type` TINYINT(1) NOT NULL DEFAULT 0,
  `gif_url` TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`),
  KEY `OBJECT` (`object_type`,`object_id`),
  KEY `action_id` (`action_id`, `approved`, `is_community_ad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actionsettings`
--

DROP TABLE IF EXISTS `engine4_activity_actionsettings`;
CREATE TABLE IF NOT EXISTS `engine4_activity_actionsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(128) NOT NULL,
  `publish` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_actiontypes`
--

DROP TABLE IF EXISTS `engine4_activity_actiontypes`;
CREATE TABLE IF NOT EXISTS `engine4_activity_actiontypes` (
  `type` varchar(128) NOT NULL,
  `module` varchar(32) NOT NULL,
  `body` text NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  `displayable` tinyint(1) NOT NULL default '3',
  `attachable` tinyint(1) NOT NULL default '1',
  `commentable` tinyint(1) NOT NULL default '1',
  `shareable` tinyint(1) NOT NULL default '1',
  `editable` tinyint(1) NOT NULL default '0',
  `is_generated` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `editable`, `is_generated`)
VALUES ('share', 'activity', '{item:$subject} shared {item:$object}''s {var:$type}. {body:$body}', 1, 5, 1, 1, 0, 1, 1),
('like_activity_action', 'activity', '{item:$subject} liked {item:$owner}''s {item:$object:post}.', 1, 1, 1, 3, 3, 0, 0);

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`)
VALUES ('share', 'activity', '{item:$subject} shared {item:$object}''s {var:$type}. {body:$body}', 1, 5, 1, 1, 0, 1),
('comment_activity_action', 'activity', '{item:$subject} commented on {item:$owner}''s {item:$object:post}.', 1, 1, 1, 3, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_attachments`
--

DROP TABLE IF EXISTS `engine4_activity_attachments`;
CREATE TABLE IF NOT EXISTS `engine4_activity_attachments` (
  `attachment_id` int(11) unsigned NOT NULL auto_increment,
  `action_id` int(11) unsigned NOT NULL,
  `type` varchar(128) NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `mode` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`attachment_id`),
  KEY `action_id` (`action_id`),
  KEY `type_id` (`type`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_comments`
--

DROP TABLE IF EXISTS `engine4_activity_comments`;
CREATE TABLE IF NOT EXISTS `engine4_activity_comments` (
  `comment_id` int(11) unsigned NOT NULL auto_increment,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(24) NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `body` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `like_count` int(11) unsigned NOT NULL default '0',
  `params` text NULL,
  `file_id` int(11) NOT NULL DEFAULT "0",
  `parent_id` int(11) NOT NULL DEFAULT "0",
  `gif_id` int(11) NOT NULL DEFAULT "0",
  `gif_url` TEXT NULL DEFAULT NULL,
  `emoji_id` int(11) NOT NULL DEFAULT "0",
  `reply_count` int(11) NOT NULL DEFAULT "0",
  `preview` int(11) NOT NULL DEFAULT "0",
  `showpreview` tinyint(1) NOT NULL DEFAULT "0",
  `vote_up_count` int(11) NOT NULL DEFAULT "0",
  `vote_down_count` int(11) NOT NULL DEFAULT "0",
  PRIMARY KEY  (`comment_id`),
  KEY `resource_type` (`resource_id`),
  KEY `poster_type` (`poster_type`, `poster_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_likes`
--

DROP TABLE IF EXISTS `engine4_activity_likes`;
CREATE TABLE `engine4_activity_likes` (
  `like_id` int(11) unsigned NOT NULL auto_increment,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(64) NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `type` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY  (`like_id`),
  KEY `resource_id` (`resource_id`),
  KEY `poster_type` (`poster_type`, `poster_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notifications`
--

DROP TABLE IF EXISTS `engine4_activity_notifications`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notifications` (
  `notification_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `subject_type` varchar(128) NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(128) NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` varchar(128) NOT NULL,
  `params` text NULL,
  `read` tinyint(1) NOT NULL default '0',
  `mitigated` tinyint(1) NOT NULL default '0',
  `date` datetime NOT NULL,
  PRIMARY KEY  (`notification_id`),
  KEY `LOOKUP` (`user_id`,`date`),
  KEY `subject` (`subject_type`, `subject_id`),
  KEY `object` (`object_type`, `object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notificationsettings`
--

DROP TABLE IF EXISTS `engine4_activity_notificationsettings`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notificationsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `email` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_notificationtypes`
--

DROP TABLE IF EXISTS `engine4_activity_notificationtypes`;
CREATE TABLE IF NOT EXISTS `engine4_activity_notificationtypes` (
  `type` varchar(128) NOT NULL,
  `module` varchar(32) NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_request` tinyint(1) NOT NULL DEFAULT '0',
  `handler` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `default` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('liked', 'activity', '{item:$subject} likes your {item:$object:$label}.', 0, ''),
('commented', 'activity', '{item:$subject} has commented on your {item:$object:$label}.', 0, ''),
('commented_commented', 'activity', '{item:$subject} has commented on a {item:$object:$label} you commented on.', 0, ''),
('liked_commented', 'activity', '{item:$subject} has commented on a {item:$object:$label} you liked.', 0, ''),
('shared', 'activity', '{item:$subject} has shared your {item:$object:$label}.', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `engine4_activity_stream`
--

DROP TABLE IF EXISTS `engine4_activity_stream`;
CREATE TABLE `engine4_activity_stream` (
  `target_type` varchar(16) NOT NULL,
  `target_id` int(11) unsigned NOT NULL,
  `subject_type` varchar(24) NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `action_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`target_type`,`target_id`,`action_id`),
  KEY `action_id` (`action_id`),
  KEY `subject_type` (`subject_type`, `subject_id`),
  KEY `object_type` (`object_type`, `object_id`),
  KEY `target_type` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Activity Privacy', 'activity_maintenance_rebuild_privacy', 'activity', 'Activity_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_commented_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_liked', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_liked_commented', 'activity', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_activity', 'activity', 'Activity Feed Settings', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"index"}', 'core_admin_main_settings', '', 4),
('core_admin_main_settings_emoticons', 'activity', 'Emoticons', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"manage-emoticons"}', 'core_admin_main_settings', '', 4);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_notifications', 'activity', 'Default Notification Alerts', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"notifications"}', 'core_admin_main_settings', '', 11)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('activity', 'Activity', 'Activity', '4.10.0beta1', 1, 'core');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('activity.disallowed', 'N'),
('activity.content', 'everyone'),
('activity.filter', 1),
('activity.length', 15),
('activity.publish', 1),
('activity.userdelete', 1),
('activity.userlength', 5),
('activity.composer.options.0', 'emoticons'),
('activity.composer.options.1', 'userTags'),
('activity.composer.options.2', 'hashtags'),
('activity.view.privacy.0', 'everyone'),
('activity.view.privacy.1', 'networks'),
('activity.view.privacy.2', 'friends'),
('activity.view.privacy.3', 'onlyme'),
('activity.network.privacy', 2),
('activity.liveupdate', 120000)
;

--
-- Dumping data for table `engine4_authorization_permissions`
--


-- ADMIN, MODERATOR, USER
-- activity - edit_time

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity' as `type`,
    'edit_time' as `name`,
    3 as `value`,
    0 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('admin', 'moderator', 'user');

ALTER TABLE `engine4_activity_actions` ADD INDEX(`type`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`module`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`displayable`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`enabled`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`type`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`read`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`mitigated`);

ALTER TABLE `engine4_activity_notificationtypes` ADD INDEX(`module`);

ALTER TABLE `engine4_activity_notificationtypes` ADD INDEX(`default`);

ALTER TABLE `engine4_activity_notificationtypes` ADD `is_admin` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `engine4_activity_notifications` ADD `is_admin` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `engine4_activity_comments` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_activity_comments` ADD INDEX(`like_count`);
ALTER TABLE `engine4_activity_notifications` ADD INDEX(`is_admin`);
ALTER TABLE `engine4_activity_notificationtypes` ADD INDEX(`is_admin`);



-- Feed Work
INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
("comment", "Comments Plugin", "Plugin", "7.0.0", 1, "extra");

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('activity_index_onthisday', 'activity', 'Memories On This Day', 'Activity_Plugin_Menus::enableonthisday', '{"route":"activity_onthisday","icon":"activity_icon_memori"}', 'user_home', '', 6),

('activity_index_sell', 'activity', 'BuySell Marketplace', '', '{"route":"activity_sell"}', 'user_home', '', 99);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("activity_admin_filter", "activity", "Manage Filters", "", '{"route":"admin_default","module":"activity","controller":"settings","action":"filter-content"}', "core_admin_main_settings_activity", "", 50),
("activity_admin_main_febgsettings", "activity", "Feed Backgrounds", "", '{"route":"admin_default","module":"activity","controller":"manage-feedbg","action":"index"}', "core_admin_main_settings_activity", "", 51),
("activity_admin_main_flngsettings", "activity", "Feelings", "", '{"route":"admin_default","module":"activity","controller":"feeling","action":"index"}', "core_admin_main_settings_activity", "", 52),
("comment_admin_emotio", "comment", "Stickers Settings", "", '{"route":"admin_default","module":"comment","controller":"emotion","action":"index"}', "core_admin_main_settings_activity", "", 53),
("comment_admin_main_emotionssettingsmain", "comment", "Stickers Categories", "", '{"route":"admin_default","module":"comment","controller":"emotion","action":"index"}', "comment_admin_emotio", "", 1),
("comment_admin_main_emotiongallery", "comment", "Stickers Packs", "", '{"route":"admin_default","module":"comment","controller":"emotion","action":"gallery"}', "comment_admin_emotio", "", 2),
("activity_admin_main_managereactions", "comment", "Manage Reactions", "", '{"route":"admin_default","module":"comment","controller":"manage-reactions","action":"index"}', "core_admin_main_settings_activity", "", 54),
("activity_admin_main_emojisettings", "activity", "Emojis", "", '{"route":"admin_default","module":"activity","controller":"emoji","action":"index"}', "core_admin_main_settings_activity", "", 60);

-- ("core_admin_main_settings_activity_settings", "activity", "Global Settings", "", '{"route":"admin_default","module":"activity","controller":"settings"}', "core_admin_main_settings_activity", "", 1);


CREATE TABLE IF NOT EXISTS `engine4_activity_backgrounds` (
  `background_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  `starttime` DATE NULL,
  `endtime` DATE NULL,
  `enableenddate` TINYINT(1) NOT NULL DEFAULT "1",
  `featured` TINYINT(1) NOT NULL DEFAULT "0",
  PRIMARY KEY  (`background_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_feelings` (
  `feeling_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY  (`feeling_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_feelingicons` (
  `feelingicon_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feeling_id` int(11) unsigned NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `feeling_icon` int(11) unsigned NOT NULL,
  `resource_type` VARCHAR(255) NOT NULL,
  `order` INT(11) NOT NULL,
  PRIMARY KEY  (`feelingicon_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_feelingposts` (
  `feelingpost_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feeling_id` int(11) unsigned NOT NULL,
  `feelingicon_id` int(11) unsigned NOT NULL,
  `resource_type` varchar(255) DEFAULT NULL,
  `action_id` int(11) unsigned NOT NULL,
  `feeling_custom` TINYINT(1) NOT NULL DEFAULT "0",
  `feeling_customtext` VARCHAR(255) NULL,
  PRIMARY KEY  (`feelingpost_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT IGNORE INTO `engine4_activity_feelings` (`feeling_id`, `title`, `type`, `file_id`, `order`) VALUES
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
(19, "Attending Event", "2", 0, 8);


CREATE TABLE IF NOT EXISTS `engine4_activity_filterlists` (
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
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`module`);
ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`active`);
ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`is_delete`);
ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`order`);
ALTER TABLE `engine4_activity_filterlists` ADD INDEX(`file_id`);

INSERT IGNORE INTO `engine4_activity_filterlists` (`filtertype`, `module`, `title`, `active`, `is_delete`, `order`) VALUES
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
("share", "core", "Share Feeds", "1", "0", 10);

UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-sync' WHERE `filtertype` = 'all';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-network-wired' WHERE `filtertype` = 'my_networks';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-users' WHERE `filtertype` = 'my_friends';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-comment' WHERE `filtertype` = 'posts';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-save' WHERE `filtertype` = 'saved_feeds';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-shopping-cart' WHERE `filtertype` = 'post_self_buysell';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-clock' WHERE `filtertype` = 'scheduled_post';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fas fa-share-alt' WHERE `filtertype` = 'share';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fa fa-image' WHERE `filtertype` = 'album';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fa fa-video' WHERE `filtertype` = 'video';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fa fa-music' WHERE `filtertype` = 'music';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fa-regular fa-file' WHERE `filtertype` = 'post_self_file';
UPDATE `engine4_activity_filterlists` SET `icon`= 'fa fa-comments' WHERE `filtertype` = 'blog';


-- Comment plugin
CREATE TABLE IF NOT EXISTS `engine4_comment_emotioncategories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR( 255 ) NOT NULL,
  `color` varchar(128) NOT NULL,
  `file_id` int(11) NOT NULL DEFAULT "0",
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `engine4_comment_emotiongalleries` (
  `gallery_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `category_id` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY  (`gallery_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_comment_emotionfiles` (
  `files_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) unsigned NOT NULL,
  `gallery_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`files_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_comment_useremotions` (
  `emotion_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) unsigned NOT NULL,
  `gallery_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`emotion_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_comment_commentfiles` (
  `commentfile_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_id` INT(11) unsigned NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`commentfile_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_comment_emotioncategories` (`category_id`, `title`, `color`, `file_id`) VALUES
(1, "Happy", "#FF4912", 26983),
(2, "In Love", "#F64E88", 26984),
(3, "Sad", "#A9A192", 26985),
(4, "Eating", "#FC8A0F", 26986),
(5, "Celebrating", "#95C63F", 26987),
(6, "Active", "#54C6E3", 26988),
(7, "Working", "#19B596", 26989),
(8, "Sleepy", "#9571A9", 26990),
(9, "Angry", "#ED513E", 26991),
(10, "Confused", "#B37736", 26992);

INSERT IGNORE INTO `engine4_comment_emotiongalleries` (`gallery_id`, `title`, `file_id`, `category_id`) VALUES
(1, "Meep", 26993, 1),
(2, "Minions", 27030, 1),
(3, "Lazy Life Line", 27053, 8),
(4, "Waddles", 27074, 1),
(5, "Panda", 27109, 2),
(6, "Tom And Jerry", 27148, 6);

CREATE TABLE IF NOT EXISTS `engine4_comment_reactions` (
  `reaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR( 255 ) NOT NULL,
  `file_id` int(11) NOT NULL DEFAULT "0",
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY (`reaction_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

INSERT IGNORE INTO `engine4_comment_reactions` (`reaction_id`, `title`, `enabled`, `file_id`) VALUES
(1, "Like", 1, 0),
(2, "Love", 1, 0),
(3, "Haha", 1, 0),
(4, "Wow", 1, 0),
(5, "Angry", 1, 0),
(6, "Sad", 1, 0);

CREATE TABLE IF NOT EXISTS `engine4_comment_voteupdowns` (
  `voteupdown_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(10) NOT NULL DEFAULT "upvote",
  `resource_type` VARCHAR(100) NOT NULL,
  `resource_id` INT(11) NOT NULL,
  `user_type` VARCHAR(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES 
("comment_tagged_item", "comment", '{item:$subject} tagged your {var:$itemurl} in a {var:$postLink}.', "0", "", "1"),
("comment_tagged_people", "comment", '{item:$object} mention you in a {var:$commentLink}.', 0, "", 1),
("comment_taggedreply_people", "comment", '{item:$object} mention you in a {var:$commentLink} on comment.', 0, "", 1), 
("comment_replycomment", "comment", '{item:$subject} replied to your comment on a {item:$object:$label}.', 0, "", 1);

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
("post_self_link", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 1, 0),
("post_self_music", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 1, 0),
("post_self_photo", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 4, 0),
("post_self_video", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 4, 0),
("post_self_file", "activity", '{item:$subject} uploaded a file.{body:$body}', 1, 5, 1, 4, 0, 0),
("post_self_buysell", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 4, 0),
("post_video", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 4, 0),
("post_photo", "activity", '{actors:$subject:$object}: {body:$body}', 1, 7, 1, 4, 4, 0),
("post_music", "activity", '{actors:$subject:$object}: {body:$body}', 1, 7, 1, 4, 1, 0),
("post_self_photo_video", "activity", '{item:$subject} {body:$body}', 1, 5, 1, 4, 4, 0);

CREATE TABLE IF NOT EXISTS `engine4_activity_tagusers` (
  `taguser_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `action_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`taguser_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

CREATE TABLE IF NOT EXISTS `engine4_activity_files` (
  `file_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `item_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`file_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

CREATE TABLE IF NOT EXISTS `engine4_activity_buysells` (
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
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_hashtags` (
  `hashtag_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`hashtag_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_savefeeds` (
  `savefeed_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`savefeed_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_hides` (
  `hide_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) unsigned NOT NULL,
  `resource_type` VARCHAR(20) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `subject_id` INT NULL DEFAULT NULL,
  PRIMARY KEY  (`hide_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`, `processes`, `semaphore`, `started_last`, `started_count`, `completed_last`, `completed_count`, `failure_last`, `failure_count`, `success_last`, `success_count`) VALUES ("Schedule Post", "activity", "Activity_Plugin_Task_Jobs", "100", "0", "0", "0", "0", "0", "0", "0", "0", "0", "0");

CREATE TABLE IF NOT EXISTS `engine4_activity_targetpost` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_pinposts` (
  `pinpost_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `resource_type` varchar(255) NOT NULL,
  PRIMARY KEY (`pinpost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_activity_tagitems` (
  `tagitem_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `resource_id` INT(11) NOT NULL,
  `resource_type` VARCHAR(255) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `action_id` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("activity_tagged_people", "activity", '{item:$subject} tagged you in a {var:$postLink}.', 0, "", 1),
("activity_scheduled_live", "activity", "Your scheduled {var:$postLink} has been made live.", 0, "", 1),
("activity_reacted", "activity", '{item:$subject} reacted {var:$reactionTitle} to your {item:$object:$label}.', 0, "", 1);


INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
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
('activity.enablenactivityupdownvote', '0');


ALTER TABLE `engine4_activity_actions` ADD INDEX `date` (`date`) USING BTREE;
ALTER TABLE `engine4_activity_notifications` ADD INDEX (`user_id`, `read`, `object_type`);

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('favourite', 'core', '{item:$subject} favorite to your {item:$object}.', 0, '');

DROP TABLE IF EXISTS `engine4_activity_emojis`;
CREATE TABLE `engine4_activity_emojis` (
  `emoji_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  PRIMARY KEY  (`emoji_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_activity_emojis` (`emoji_id`, `title`, `file_id`) VALUES
(1, "Activity", 1),
(2, "Animals & Nature", 2),
(3, "Flags", 3),
(4, "Food & Drink", 4),
(5, "Objects", 5),
(6, "Smilies & People", 6),
(7, "Symbols", 7),
(8, "Travel & Places", 8);

DROP TABLE IF EXISTS `engine4_activity_emojiicons`;
CREATE TABLE `engine4_activity_emojiicons` (
  `emojiicon_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `emoji_icon` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `emoji_encodecode` VARCHAR(255) NOT NULL,
  `emoji_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  PRIMARY KEY  (`emojiicon_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_activity_emojiicons` (`emojiicon_id`, `emoji_icon`, `title`, `emoji_encodecode`, `emoji_id`, `order`) VALUES
(1, '🎃', '', 'ud83cudf83', 1, 1),
(2, '🎄', '', 'ud83cudf84', 1, 2),
(3, '🎅', '', 'ud83cudf85', 1, 3),
(4, '🎉', '', 'ud83cudf89', 1, 4),
(5, '🎣', '', 'ud83cudfa3', 1, 5),
(6, '🎧', '', 'ud83cudfa7', 1, 6),
(7, '🎨', '', 'ud83cudfa8', 1, 7),
(8, '🎭', '', 'ud83cudfad', 1, 8),
(9, '🎮', '', 'ud83cudfae', 1, 9),
(10, '🎯', '', 'ud83cudfaf', 1, 10),
(11, '🎰', '', 'ud83cudfb0', 1, 11),
(12, '🎱', '', 'ud83cudfb1', 1, 12),
(13, '🎲', '', 'ud83cudfb2', 1, 13),
(14, '🎳', '', 'ud83cudfb3', 1, 14),
(15, '🎷', '', 'ud83cudfb7', 1, 15),
(16, '🎸', '', 'ud83cudfb8', 1, 16),
(17, '🎾', '', 'ud83cudfbe', 1, 17),
(18, '🏀', '', 'ud83cudfc0', 1, 18),
(19, '🏁', '', 'ud83cudfc1', 1, 19),
(20, '🏂', '', 'ud83cudfc2', 1, 20),
(21, '🏃', '', 'ud83cudfc3', 1, 21),
(22, '🏄', '', 'ud83cudfc4', 1, 22),
(23, '🏆', '', 'ud83cudfc6', 1, 23),
(24, '🏇', '', 'ud83cudfc7', 1, 24),
(25, '🏈', '', 'ud83cudfc8', 1, 25),
(26, '🏉', '', 'ud83cudfc9', 1, 26),
(27, '🏊', '', 'ud83cudfca', 1, 27),
(28, '🚴', '', 'ud83dudeb4', 1, 28),
(29, '🚵', '', 'ud83dudeb5', 1, 29),
(30, '⚽', '', 'u26bd', 1, 30),
(31, '⚾', '', 'u26be', 1, 31),
(32, '⛄', '', 'u26c4', 1, 32),
(33, '🌀', '', 'ud83cudf00', 2, 33),
(34, '🌁', '', 'ud83cudf01', 2, 34),
(35, '🌃', '', 'ud83cudf03', 2, 35),
(36, '🌄', '', 'ud83cudf04', 2, 36),
(37, '🌅', '', 'ud83cudf05', 2, 37),
(38, '🌆', '', 'ud83cudf06', 2, 38),
(39, '🌇', '', 'ud83cudf07', 2, 39),
(40, '🌈', '', 'ud83cudf08', 2, 40),
(41, '🌉', '', 'ud83cudf09', 2, 41),
(42, '🌐', '', 'ud83cudf10', 2, 42),
(43, '🌑', '', 'ud83cudf11', 2, 43),
(44, '🌒', '', 'ud83cudf12', 2, 44),
(45, '🌓', '', 'ud83cudf13', 2, 45),
(46, '🌔', '', 'ud83cudf14', 2, 46),
(47, '🌕', '', 'ud83cudf15', 2, 47),
(48, '🌖', '', 'ud83cudf16', 2, 48),
(49, '🌗', '', 'ud83cudf17', 2, 49),
(50, '🌘', '', 'ud83cudf18', 2, 50),
(51, '🌙', '', 'ud83cudf19', 2, 51),
(52, '🌞', '', 'ud83cudf1e', 2, 52),
(53, '🌱', '', 'ud83cudf31', 2, 53),
(54, '🌲', '', 'ud83cudf32', 2, 54),
(55, '🌳', '', 'ud83cudf33', 2, 55),
(56, '🌴', '', 'ud83cudf34', 2, 56),
(57, '🌵', '', 'ud83cudf35', 2, 57),
(58, '🌷', '', 'ud83cudf37', 2, 58),
(59, '🌸', '', 'ud83cudf38', 2, 59),
(60, '🌹', '', 'ud83cudf39', 2, 60),
(61, '🌺', '', 'ud83cudf3a', 2, 61),
(62, '🌻', '', 'ud83cudf3b', 2, 62),
(63, '🌼', '', 'ud83cudf3c', 2, 63),
(64, '🌾', '', 'ud83cudf3e', 2, 64),
(65, '🌿', '', 'ud83cudf3f', 2, 65),
(66, '🍀', '', 'ud83cudf40', 2, 66),
(67, '🍁', '', 'ud83cudf41', 2, 67),
(68, '🍂', '', 'ud83cudf42', 2, 68),
(69, '🍃', '', 'ud83cudf43', 2, 69),
(70, '🍄', '', 'ud83cudf44', 2, 70),
(71, '🐀', '', 'ud83dudc00', 2, 71),
(72, '🐁', '', 'ud83dudc01', 2, 72),
(73, '🐂', '', 'ud83dudc02', 2, 73),
(74, '🐃', '', 'ud83dudc03', 2, 74),
(75, '🐄', '', 'ud83dudc04', 2, 75),
(76, '🐅', '', 'ud83dudc05', 2, 76),
(77, '🐆', '', 'ud83dudc06', 2, 77),
(78, '🐇', '', 'ud83dudc07', 2, 78),
(79, '🐈', '', 'ud83dudc08', 2, 79),
(80, '🐉', '', 'ud83dudc09', 2, 80),
(81, '🐊', '', 'ud83dudc0a', 2, 81),
(82, '🐋', '', 'ud83dudc0b', 2, 82),
(83, '🐌', '', 'ud83dudc0c', 2, 83),
(84, '🐍', '', 'ud83dudc0d', 2, 84),
(85, '🐎', '', 'ud83dudc0e', 2, 85),
(86, '🐏', '', 'ud83dudc0f', 2, 86),
(87, '🐐', '', 'ud83dudc10', 2, 87),
(88, '🐑', '', 'ud83dudc11', 2, 88),
(89, '🐒', '', 'ud83dudc12', 2, 89),
(90, '🐓', '', 'ud83dudc13', 2, 90),
(91, '🐔', '', 'ud83dudc14', 2, 91),
(92, '🐕', '', 'ud83dudc15', 2, 92),
(93, '🐖', '', 'ud83dudc16', 2, 93),
(94, '🐗', '', 'ud83dudc17', 2, 94),
(95, '🐘', '', 'ud83dudc18', 2, 95),
(96, '🐙', '', 'ud83dudc19', 2, 96),
(97, '🐚', '', 'ud83dudc1a', 2, 97),
(98, '🐛', '', 'ud83dudc1b', 2, 98),
(99, '🐜', '', 'ud83dudc1c', 2, 99),
(100, '🐝', '', 'ud83dudc1d', 2, 100),
(101, '🐞', '', 'ud83dudc1e', 2, 101),
(102, '🐟', '', 'ud83dudc1f', 2, 102),
(103, '🐠', '', 'ud83dudc20', 2, 103),
(104, '🐡', '', 'ud83dudc21', 2, 104),
(105, '🐢', '', 'ud83dudc22', 2, 105),
(106, '🐣', '', 'ud83dudc23', 2, 106),
(107, '🐤', '', 'ud83dudc24', 2, 107),
(108, '🐥', '', 'ud83dudc25', 2, 108),
(109, '🐦', '', 'ud83dudc26', 2, 109),
(110, '🐧', '', 'ud83dudc27', 2, 110),
(111, '🐨', '', 'ud83dudc28', 2, 111),
(112, '🐩', '', 'ud83dudc29', 2, 112),
(113, '🐪', '', 'ud83dudc2a', 2, 113),
(114, '🐫', '', 'ud83dudc2b', 2, 114),
(115, '🐬', '', 'ud83dudc2c', 2, 115),
(116, '🐭', '', 'ud83dudc2d', 2, 116),
(117, '🐮', '', 'ud83dudc2e', 2, 117),
(118, '🐯', '', 'ud83dudc2f', 2, 118),
(119, '🐰', '', 'ud83dudc30', 2, 119),
(120, '🐱', '', 'ud83dudc31', 2, 120),
(121, '🐲', '', 'ud83dudc32', 2, 121),
(122, '🐳', '', 'ud83dudc33', 2, 122),
(123, '🐴', '', 'ud83dudc34', 2, 123),
(124, '🐵', '', 'ud83dudc35', 2, 124),
(125, '🐶', '', 'ud83dudc36', 2, 125),
(126, '🐷', '', 'ud83dudc37', 2, 126),
(127, '🐸', '', 'ud83dudc38', 2, 127),
(128, '🐹', '', 'ud83dudc39', 2, 128),
(129, '🐺', '', 'ud83dudc3a', 2, 129),
(130, '🐻', '', 'ud83dudc3b', 2, 130),
(131, '🐼', '', 'ud83dudc3c', 2, 131),
(132, '🐽', '', 'ud83dudc3d', 2, 132),
(133, '🙈', '', 'ud83dude48', 2, 133),
(134, '🙉', '', 'ud83dude49', 2, 134),
(135, '🙊', '', 'ud83dude4a', 2, 135),
(136, '☀', '', 'u2600', 2, 136),
(137, '✨', '', 'u2728', 2, 137),
(138, '❄', '', 'u2744', 2, 138),
(139, '🇨🇳', '', '', 3, 139),
(140, '🇩🇪', '', '', 3, 140),
(141, '🇪🇸', '', '', 3, 141),
(142, '🇫🇷', '', '', 3, 142),
(143, '🇬🇧', '', '', 3, 143),
(144, '🇮🇹', '', '', 3, 144),
(145, '🇯🇵', '', '', 3, 145),
(146, '🇰🇷', '', '', 3, 146),
(147, '🇷🇺', '', '', 3, 147),
(148, '🇺🇸', '', '', 3, 148),
(149, '🌰', '', 'ud83cudf30', 4, 149),
(150, '🌽', '', 'ud83cudf3d', 4, 150),
(151, '🍅', '', 'ud83cudf45', 4, 151),
(152, '🍆', '', 'ud83cudf46', 4, 152),
(153, '🍇', '', 'ud83cudf47', 4, 153),
(154, '🍈', '', 'ud83cudf48', 4, 154),
(155, '🍉', '', 'ud83cudf49', 4, 155),
(156, '🍊', '', 'ud83cudf4a', 4, 156),
(157, '🍋', '', 'ud83cudf4b', 4, 157),
(158, '🍌', '', 'ud83cudf4c', 4, 158),
(159, '🍍', '', 'ud83cudf4d', 4, 159),
(160, '🍎', '', 'ud83cudf4e', 4, 160),
(161, '🍏', '', 'ud83cudf4f', 4, 161),
(162, '🍐', '', 'ud83cudf50', 4, 162),
(163, '🍑', '', 'ud83cudf51', 4, 163),
(164, '🍒', '', 'ud83cudf52', 4, 164),
(165, '🍓', '', 'ud83cudf53', 4, 165),
(166, '🍔', '', 'ud83cudf54', 4, 166),
(167, '🍕', '', 'ud83cudf55', 4, 167),
(168, '🍖', '', 'ud83cudf56', 4, 168),
(169, '🍗', '', 'ud83cudf57', 4, 169),
(170, '🍘', '', 'ud83cudf58', 4, 170),
(171, '🍙', '', 'ud83cudf59', 4, 171),
(172, '🍚', '', 'ud83cudf5a', 4, 172),
(173, '🍛', '', 'ud83cudf5b', 4, 173),
(174, '🍜', '', 'ud83cudf5c', 4, 174),
(175, '🍝', '', 'ud83cudf5d', 4, 175),
(176, '🍞', '', 'ud83cudf5e', 4, 176),
(177, '🍟', '', 'ud83cudf5f', 4, 177),
(178, '🍠', '', 'ud83cudf60', 4, 178),
(179, '🍡', '', 'ud83cudf61', 4, 179),
(180, '🍢', '', 'ud83cudf62', 4, 180),
(181, '🍣', '', 'ud83cudf63', 4, 181),
(182, '🍤', '', 'ud83cudf64', 4, 182),
(183, '🍥', '', 'ud83cudf65', 4, 183),
(184, '🍦', '', 'ud83cudf66', 4, 184),
(185, '🍧', '', 'ud83cudf67', 4, 185),
(186, '🍨', '', 'ud83cudf68', 4, 186),
(187, '🍩', '', 'ud83cudf69', 4, 187),
(188, '🍪', '', 'ud83cudf6a', 4, 188),
(189, '🍫', '', 'ud83cudf6b', 4, 189),
(190, '🍬', '', 'ud83cudf6c', 4, 190),
(191, '🍭', '', 'ud83cudf6d', 4, 191),
(192, '🍮', '', 'ud83cudf6e', 4, 192),
(193, '🍯', '', 'ud83cudf6f', 4, 193),
(194, '🍰', '', 'ud83cudf70', 4, 194),
(195, '🍱', '', 'ud83cudf71', 4, 195),
(196, '🍲', '', 'ud83cudf72', 4, 196),
(197, '🍳', '', 'ud83cudf73', 4, 197),
(198, '🍴', '', 'ud83cudf74', 4, 198),
(199, '🍵', '', 'ud83cudf75', 4, 199),
(200, '🍶', '', 'ud83cudf76', 4, 200),
(201, '🍷', '', 'ud83cudf77', 4, 201),
(202, '🍸', '', 'ud83cudf78', 4, 202),
(203, '🍹', '', 'ud83cudf79', 4, 203),
(204, '🍺', '', 'ud83cudf7a', 4, 204),
(205, '🍻', '', 'ud83cudf7b', 4, 205),
(206, '🍼', '', 'ud83cudf7c', 4, 206),
(207, '🎂', '', 'ud83cudf82', 4, 207),
(208, '☕', '', 'u2615', 4, 208),
(209, '🎈', '', 'ud83cudf88', 5, 209),
(210, '🎤', '', 'ud83cudfa4', 5, 210),
(211, '🎥', '', 'ud83cudfa5', 5, 211),
(212, '🎬', '', 'ud83cudfac', 5, 212),
(213, '🎹', '', 'ud83cudfb9', 5, 213),
(214, '🎺', '', 'ud83cudfba', 5, 214),
(215, '🎻', '', 'ud83cudfbb', 5, 215),
(216, '💉', '', 'ud83dudc89', 5, 216),
(217, '💍', '', 'ud83dudc8d', 5, 217),
(218, '💎', '', 'ud83dudc8e', 5, 218),
(219, '💣', '', 'ud83dudca3', 5, 219),
(220, '💳', '', 'ud83dudcb3', 5, 220),
(221, '💴', '', 'ud83dudcb4', 5, 221),
(222, '💵', '', 'ud83dudcb5', 5, 222),
(223, '💶', '', 'ud83dudcb6', 5, 223),
(224, '💷', '', 'ud83dudcb7', 5, 224),
(225, '💸', '', 'ud83dudcb8', 5, 225),
(226, '💼', '', 'ud83dudcbc', 5, 226),
(227, '💽', '', 'ud83dudcbd', 5, 227),
(228, '💾', '', 'ud83dudcbe', 5, 228),
(229, '💿', '', 'ud83dudcbf', 5, 229),
(230, '📀', '', 'ud83dudcc0', 5, 230),
(231, '📋', '', 'ud83dudccb', 5, 231),
(232, '📌', '', 'ud83dudccc', 5, 232),
(233, '📍', '', 'ud83dudccd', 5, 233),
(234, '📎', '', 'ud83dudcce', 5, 234),
(235, '📏', '', 'ud83dudccf', 5, 235),
(236, '📐', '', 'ud83dudcd0', 5, 236),
(237, '📑', '', 'ud83dudcd1', 5, 237),
(238, '📒', '', 'ud83dudcd2', 5, 238),
(239, '📓', '', 'ud83dudcd3', 5, 239),
(240, '📔', '', 'ud83dudcd4', 5, 240),
(241, '📕', '', 'ud83dudcd5', 5, 241),
(242, '📖', '', 'ud83dudcd6', 5, 242),
(243, '📗', '', 'ud83dudcd7', 5, 243),
(244, '📘', '', 'ud83dudcd8', 5, 244),
(245, '📙', '', 'ud83dudcd9', 5, 245),
(246, '📚', '', 'ud83dudcda', 5, 246),
(247, '📛', '', 'ud83dudcdb', 5, 247),
(248, '📜', '', 'ud83dudcdc', 5, 248),
(249, '📝', '', 'ud83dudcdd', 5, 249),
(250, '📞', '', 'ud83dudcde', 5, 250),
(251, '📡', '', 'ud83dudce1', 5, 251),
(252, '📢', '', 'ud83dudce2', 5, 252),
(253, '📣', '', 'ud83dudce3', 5, 253),
(254, '📤', '', 'ud83dudce4', 5, 254),
(255, '📥', '', 'ud83dudce5', 5, 255),
(256, '📱', '', 'ud83dudcf1', 5, 256),
(257, '📲', '', 'ud83dudcf2', 5, 257),
(258, '📷', '', 'ud83dudcf7', 5, 258),
(259, '📹', '', 'ud83dudcf9', 5, 259),
(260, '📺', '', 'ud83dudcfa', 5, 260),
(261, '📻', '', 'ud83dudcfb', 5, 261),
(262, '🔊', '', 'ud83dudd0a', 5, 262),
(263, '🔌', '', 'ud83dudd0c', 5, 263),
(264, '🔍', '', 'ud83dudd0d', 5, 264),
(265, '🔎', '', 'ud83dudd0e', 5, 265),
(266, '🔏', '', 'ud83dudd0f', 5, 266),
(267, '🔐', '', 'ud83dudd10', 5, 267),
(268, '🔑', '', 'ud83dudd11', 5, 268),
(269, '🔒', '', 'ud83dudd12', 5, 269),
(270, '🔓', '', 'ud83dudd13', 5, 270),
(271, '🔔', '', 'ud83dudd14', 5, 271),
(272, '🔕', '', 'ud83dudd15', 5, 272),
(273, '🔗', '', 'ud83dudd17', 5, 273),
(274, '🔦', '', 'ud83dudd26', 5, 274),
(275, '🔧', '', 'ud83dudd27', 5, 275),
(276, '🔨', '', 'ud83dudd28', 5, 276),
(277, '🔩', '', 'ud83dudd29', 5, 277),
(278, '🔪', '', 'ud83dudd2a', 5, 278),
(279, '🔫', '', 'ud83dudd2b', 5, 279),
(280, '🔬', '', 'ud83dudd2c', 5, 280),
(281, '🔭', '', 'ud83dudd2d', 5, 281),
(282, '🔮', '', 'ud83dudd2e', 5, 282),
(283, '🔰', '', 'ud83dudd30', 5, 283),
(284, '🔱', '', 'ud83dudd31', 5, 284),
(285, '⌚', '', 'u231a', 5, 285),
(286, '⌛', '', 'u231b', 5, 286),
(287, '☎', '', 'u260e', 5, 287),
(288, '⚓', '', 'u2693', 5, 288),
(289, '✂', '', 'u2702', 5, 289),
(290, '✉', '', 'u2709', 5, 290),
(291, '✏', '', 'u270f', 5, 291),
(292, '✒', '', 'u2712', 5, 292),
(293, '〽', '', 'u303d', 5, 293),
(294, '🎎', '', 'ud83cudf8e', 6, 294),
(295, '👀', '', 'ud83dudc40', 6, 295),
(296, '👂', '', 'ud83dudc42', 6, 296),
(297, '👃', '', 'ud83dudc43', 6, 297),
(298, '👄', '', 'ud83dudc44', 6, 298),
(299, '👅', '', 'ud83dudc45', 6, 299),
(300, '👆', '', 'ud83dudc46', 6, 300),
(301, '👇', '', 'ud83dudc47', 6, 301),
(302, '👈', '', 'ud83dudc48', 6, 302),
(303, '👉', '', 'ud83dudc49', 6, 303),
(304, '👊', '', 'ud83dudc4a', 6, 304),
(305, '👋', '', 'ud83dudc4b', 6, 305),
(306, '👌', '', 'ud83dudc4c', 6, 306),
(307, '👍', '', 'ud83dudc4d', 6, 307),
(308, '👎', '', 'ud83dudc4e', 6, 308),
(309, '👏', '', 'ud83dudc4f', 6, 309),
(310, '👐', '', 'ud83dudc50', 6, 310),
(311, '👒', '', 'ud83dudc52', 6, 311),
(312, '👔', '', 'ud83dudc54', 6, 312),
(313, '👕', '', 'ud83dudc55', 6, 313),
(314, '👖', '', 'ud83dudc56', 6, 314),
(315, '👗', '', 'ud83dudc57', 6, 315),
(316, '👘', '', 'ud83dudc58', 6, 316),
(317, '👙', '', 'ud83dudc59', 6, 317),
(318, '👠', '', 'ud83dudc60', 6, 318),
(319, '👡', '', 'ud83dudc61', 6, 319),
(320, '👢', '', 'ud83dudc62', 6, 320),
(321, '👣', '', 'ud83dudc63', 6, 321),
(322, '👤', '', 'ud83dudc64', 6, 322),
(323, '👥', '', 'ud83dudc65', 6, 323),
(324, '👦', '', 'ud83dudc66', 6, 324),
(325, '👧', '', 'ud83dudc67', 6, 325),
(326, '👨', '', 'ud83dudc68', 6, 326),
(327, '👩', '', 'ud83dudc69', 6, 327),
(328, '👪', '', 'ud83dudc6a', 6, 328),
(329, '👫', '', 'ud83dudc6b', 6, 329),
(330, '👬', '', 'ud83dudc6c', 6, 330),
(331, '👭', '', 'ud83dudc6d', 6, 331),
(332, '👮', '', 'ud83dudc6e', 6, 332),
(333, '👯', '', 'ud83dudc6f', 6, 333),
(334, '👰', '', 'ud83dudc70', 6, 334),
(335, '👱', '', 'ud83dudc71', 6, 335),
(336, '👲', '', 'ud83dudc72', 6, 336),
(337, '👳', '', 'ud83dudc73', 6, 337),
(338, '👴', '', 'ud83dudc74', 6, 338),
(339, '👵', '', 'ud83dudc75', 6, 339),
(340, '👶', '', 'ud83dudc76', 6, 340),
(341, '👷', '', 'ud83dudc77', 6, 341),
(342, '👸', '', 'ud83dudc78', 6, 342),
(343, '👹', '', 'ud83dudc79', 6, 343),
(344, '👺', '', 'ud83dudc7a', 6, 344),
(345, '👻', '', 'ud83dudc7b', 6, 345),
(346, '👼', '', 'ud83dudc7c', 6, 346),
(347, '👽', '', 'ud83dudc7d', 6, 347),
(348, '👿', '', 'ud83dudc7f', 6, 348),
(349, '💀', '', 'ud83dudc80', 6, 349),
(350, '💁', '', 'ud83dudc81', 6, 350),
(351, '💂', '', 'ud83dudc82', 6, 351),
(352, '💃', '', 'ud83dudc83', 6, 352),
(353, '💄', '', 'ud83dudc84', 6, 353),
(354, '💅', '', 'ud83dudc85', 6, 354),
(355, '💆', '', 'ud83dudc86', 6, 355),
(356, '💇', '', 'ud83dudc87', 6, 356),
(357, '💋', '', 'ud83dudc8b', 6, 357),
(358, '💌', '', 'ud83dudc8c', 6, 358),
(359, '💏', '', 'ud83dudc8f', 6, 359),
(360, '💐', '', 'ud83dudc90', 6, 360),
(361, '💑', '', 'ud83dudc91', 6, 361),
(362, '💒', '', 'ud83dudc92', 6, 362),
(363, '💓', '', 'ud83dudc93', 6, 363),
(364, '💔', '', 'ud83dudc94', 6, 364),
(365, '💕', '', 'ud83dudc95', 6, 365),
(366, '💖', '', 'ud83dudc96', 6, 366),
(367, '💗', '', 'ud83dudc97', 6, 367),
(368, '💘', '', 'ud83dudc98', 6, 368),
(369, '💙', '', 'ud83dudc99', 6, 369),
(370, '💚', '', 'ud83dudc9a', 6, 370),
(371, '💛', '', 'ud83dudc9b', 6, 371),
(372, '💜', '', 'ud83dudc9c', 6, 372),
(373, '💝', '', 'ud83dudc9d', 6, 373),
(374, '💞', '', 'ud83dudc9e', 6, 374),
(375, '💟', '', 'ud83dudc9f', 6, 375),
(376, '💩', '', 'ud83dudca9', 6, 376),
(377, '💪', '', 'ud83dudcaa', 6, 377),
(378, '😀', '', 'ud83dude00', 6, 378),
(379, '😁', '', 'ud83dude01', 6, 379),
(380, '😂', '', 'ud83dude02', 6, 380),
(381, '😃', '', 'ud83dude03', 6, 381),
(382, '😄', '', 'ud83dude04', 6, 382),
(383, '😅', '', 'ud83dude05', 6, 383),
(384, '😆', '', 'ud83dude06', 6, 384),
(385, '😇', '', 'ud83dude07', 6, 385),
(386, '😈', '', 'ud83dude08', 6, 386),
(387, '😉', '', 'ud83dude09', 6, 387),
(388, '😊', '', 'ud83dude0a', 6, 388),
(389, '😋', '', 'ud83dude0b', 6, 389),
(390, '😌', '', 'ud83dude0c', 6, 390),
(391, '😍', '', 'ud83dude0d', 6, 391),
(392, '😎', '', 'ud83dude0e', 6, 392),
(393, '😏', '', 'ud83dude0f', 6, 393),
(394, '😐', '', 'ud83dude10', 6, 394),
(395, '😑', '', 'ud83dude11', 6, 395),
(396, '😒', '', 'ud83dude12', 6, 396),
(397, '😓', '', 'ud83dude13', 6, 397),
(398, '😔', '', 'ud83dude14', 6, 398),
(399, '😕', '', 'ud83dude15', 6, 399),
(400, '😖', '', 'ud83dude16', 6, 400),
(401, '😗', '', 'ud83dude17', 6, 401),
(402, '😘', '', 'ud83dude18', 6, 402),
(403, '😙', '', 'ud83dude19', 6, 403),
(404, '😚', '', 'ud83dude1a', 6, 404),
(405, '😛', '', 'ud83dude1b', 6, 405),
(406, '😜', '', 'ud83dude1c', 6, 406),
(407, '😝', '', 'ud83dude1d', 6, 407),
(408, '😞', '', 'ud83dude1e', 6, 408),
(409, '😟', '', 'ud83dude1f', 6, 409),
(410, '😠', '', 'ud83dude20', 6, 410),
(411, '😡', '', 'ud83dude21', 6, 411),
(412, '😢', '', 'ud83dude22', 6, 412),
(413, '😣', '', 'ud83dude23', 6, 413),
(414, '😤', '', 'ud83dude24', 6, 414),
(415, '😥', '', 'ud83dude25', 6, 415),
(416, '😦', '', 'ud83dude26', 6, 416),
(417, '😧', '', 'ud83dude27', 6, 417),
(418, '😨', '', 'ud83dude28', 6, 418),
(419, '😩', '', 'ud83dude29', 6, 419),
(420, '😪', '', 'ud83dude2a', 6, 420),
(421, '😫', '', 'ud83dude2b', 6, 421),
(422, '😬', '', 'ud83dude2c', 6, 422),
(423, '😭', '', 'ud83dude2d', 6, 423),
(424, '😮', '', 'ud83dude2e', 6, 424),
(425, '😯', '', 'ud83dude2f', 6, 425),
(426, '😰', '', 'ud83dude30', 6, 426),
(427, '😱', '', 'ud83dude31', 6, 427),
(428, '😲', '', 'ud83dude32', 6, 428),
(429, '😳', '', 'ud83dude33', 6, 429),
(430, '😴', '', 'ud83dude34', 6, 430),
(431, '😵', '', 'ud83dude35', 6, 431),
(432, '😶', '', 'ud83dude36', 6, 432),
(433, '😷', '', 'ud83dude37', 6, 433),
(434, '😸', '', 'ud83dude38', 6, 434),
(435, '😹', '', 'ud83dude39', 6, 435),
(436, '😺', '', 'ud83dude3a', 6, 436),
(437, '😻', '', 'ud83dude3b', 6, 437),
(438, '😼', '', 'ud83dude3c', 6, 438),
(439, '😽', '', 'ud83dude3d', 6, 439),
(440, '😾', '', 'ud83dude3e', 6, 440),
(441, '😿', '', 'ud83dude3f', 6, 441),
(442, '🙀', '', 'ud83dude40', 6, 442),
(443, '🙅', '', 'ud83dude45', 6, 443),
(444, '🙆', '', 'ud83dude46', 6, 444),
(445, '🙇', '', 'ud83dude47', 6, 445),
(446, '🙋', '', 'ud83dude4b', 6, 446),
(447, '🙌', '', 'ud83dude4c', 6, 447),
(448, '🙍', '', 'ud83dude4d', 6, 448),
(449, '🙎', '', 'ud83dude4e', 6, 449),
(450, '🙏', '', 'ud83dude4f', 6, 450),
(451, '🚶', '', 'ud83dudeb6', 6, 451),
(452, '☺', '', 'u263a', 6, 452),
(453, '✊', '', 'u270a', 6, 453),
(454, '✋', '', 'u270b', 6, 454),
(455, '✌', '', 'u270c', 6, 455),
(456, '#', '', '#', 7, 456),
(457, '0', '', '0', 7, 457),
(458, '1', '', '1', 7, 458),
(459, '2', '', '2', 7, 459),
(460, '3', '', '3', 7, 460),
(461, '4', '', '4', 7, 461),
(462, '5', '', '5', 7, 462),
(463, '6', '', '6', 7, 463),
(464, '7', '', '7', 7, 464),
(465, '8', '', '8', 7, 465),
(466, '9', '', '9', 7, 466),
(467, '©', '', 'u00a9', 7, 467),
(468, '®', '', 'u00ae', 7, 468),
(469, '🅰', '', 'ud83cudd70', 7, 469),
(470, '🅱', '', 'ud83cudd71', 7, 470),
(471, '🅾', '', 'ud83cudd7e', 7, 471),
(472, '🅿', '', 'ud83cudd7f', 7, 472),
(473, '🆎', '', 'ud83cudd8e', 7, 473),
(474, '🆑', '', 'ud83cudd91', 7, 474),
(475, '🆒', '', 'ud83cudd92', 7, 475),
(476, '🆓', '', 'ud83cudd93', 7, 476),
(477, '🆔', '', 'ud83cudd94', 7, 477),
(478, '🆕', '', 'ud83cudd95', 7, 478),
(479, '🆖', '', 'ud83cudd96', 7, 479),
(480, '🆗', '', 'ud83cudd97', 7, 480),
(481, '🆘', '', 'ud83cudd98', 7, 481),
(482, '🆙', '', 'ud83cudd99', 7, 482),
(483, '🆚', '', 'ud83cudd9a', 7, 483),
(484, '🈁', '', 'ud83cude01', 7, 484),
(485, '🈂', '', 'ud83cude02', 7, 485),
(486, '🈚', '', 'ud83cude1a', 7, 486),
(487, '🈯', '', 'ud83cude2f', 7, 487),
(488, '🈲', '', 'ud83cude32', 7, 488),
(489, '🈳', '', 'ud83cude33', 7, 489),
(490, '🈴', '', 'ud83cude34', 7, 490),
(491, '🈵', '', 'ud83cude35', 7, 491),
(492, '🈶', '', 'ud83cude36', 7, 492),
(493, '🈷', '', 'ud83cude37', 7, 493),
(494, '🈸', '', 'ud83cude38', 7, 494),
(495, '🈹', '', 'ud83cude39', 7, 495),
(496, '🈺', '', 'ud83cude3a', 7, 496),
(497, '🉐', '', 'ud83cude50', 7, 497),
(498, '🉑', '', 'ud83cude51', 7, 498),
(499, '🎦', '', 'ud83cudfa6', 7, 499),
(500, '💤', '', 'ud83dudca4', 7, 500),
(501, '📳', '', 'ud83dudcf3', 7, 501),
(502, '📴', '', 'ud83dudcf4', 7, 502),
(503, '📶', '', 'ud83dudcf6', 7, 503),
(504, '🔀', '', 'ud83dudd00', 7, 504),
(505, '🔁', '', 'ud83dudd01', 7, 505),
(506, '🔂', '', 'ud83dudd02', 7, 506),
(507, '🔄', '', 'ud83dudd04', 7, 507),
(508, '🔅', '', 'ud83dudd05', 7, 508),
(509, '🔆', '', 'ud83dudd06', 7, 509),
(510, '🔙', '', 'ud83dudd19', 7, 510),
(511, '🔚', '', 'ud83dudd1a', 7, 511),
(512, '🔛', '', 'ud83dudd1b', 7, 512),
(513, '🔜', '', 'ud83dudd1c', 7, 513),
(514, '🔝', '', 'ud83dudd1d', 7, 514),
(515, '🔞', '', 'ud83dudd1e', 7, 515),
(516, '🔟', '', 'ud83dudd1f', 7, 516),
(517, '🔠', '', 'ud83dudd20', 7, 517),
(518, '🔡', '', 'ud83dudd21', 7, 518),
(519, '🔢', '', 'ud83dudd22', 7, 519),
(520, '🔣', '', 'ud83dudd23', 7, 520),
(521, '🔤', '', 'ud83dudd24', 7, 521),
(522, '🔯', '', 'ud83dudd2f', 7, 522),
(523, '🔲', '', 'ud83dudd32', 7, 523),
(524, '🔳', '', 'ud83dudd33', 7, 524),
(525, '🔴', '', 'ud83dudd34', 7, 525),
(526, '🔵', '', 'ud83dudd35', 7, 526),
(527, '🔶', '', 'ud83dudd36', 7, 527),
(528, '🔷', '', 'ud83dudd37', 7, 528),
(529, '🔼', '', 'ud83dudd3c', 7, 529),
(530, '🔽', '', 'ud83dudd3d', 7, 530),
(531, '🕐', '', 'ud83dudd50', 7, 531),
(532, '🕑', '', 'ud83dudd51', 7, 532),
(533, '🕒', '', 'ud83dudd52', 7, 533),
(534, '🕓', '', 'ud83dudd53', 7, 534),
(535, '🕔', '', 'ud83dudd54', 7, 535),
(536, '🕕', '', 'ud83dudd55', 7, 536),
(537, '🕖', '', 'ud83dudd56', 7, 537),
(538, '🕗', '', 'ud83dudd57', 7, 538),
(539, '🕘', '', 'ud83dudd58', 7, 539),
(540, '🕙', '', 'ud83dudd59', 7, 540),
(541, '🕚', '', 'ud83dudd5a', 7, 541),
(542, '🕛', '', 'ud83dudd5b', 7, 542),
(543, '🕜', '', 'ud83dudd5c', 7, 543),
(544, '🕝', '', 'ud83dudd5d', 7, 544),
(545, '🕞', '', 'ud83dudd5e', 7, 545),
(546, '🕟', '', 'ud83dudd5f', 7, 546),
(547, '🕠', '', 'ud83dudd60', 7, 547),
(548, '🕡', '', 'ud83dudd61', 7, 548),
(549, '🕢', '', 'ud83dudd62', 7, 549),
(550, '🕣', '', 'ud83dudd63', 7, 550),
(551, '🕤', '', 'ud83dudd64', 7, 551),
(552, '🕥', '', 'ud83dudd65', 7, 552),
(553, '🕦', '', 'ud83dudd66', 7, 553),
(554, '🕧', '', 'ud83dudd67', 7, 554),
(555, '🚫', '', 'ud83dudeab', 7, 555),
(556, '🚭', '', 'ud83dudead', 7, 556),
(557, '🚮', '', 'ud83dudeae', 7, 557),
(558, '🚯', '', 'ud83dudeaf', 7, 558),
(559, '🚰', '', 'ud83dudeb0', 7, 559),
(560, '🚱', '', 'ud83dudeb1', 7, 560),
(561, '🚳', '', 'ud83dudeb3', 7, 561),
(562, '🚷', '', 'ud83dudeb7', 7, 562),
(563, '🚹', '', 'ud83dudeb9', 7, 563),
(564, '🚺', '', 'ud83dudeba', 7, 564),
(565, '🚻', '', 'ud83dudebb', 7, 565),
(566, '🚼', '', 'ud83dudebc', 7, 566),
(567, '🚾', '', 'ud83dudebe', 7, 567),
(568, '🛂', '', 'ud83dudec2', 7, 568),
(569, '🛃', '', 'ud83dudec3', 7, 569),
(570, '🛄', '', 'ud83dudec4', 7, 570),
(571, '🛅', '', 'ud83dudec5', 7, 571),
(572, '⁉', '', 'u2049', 7, 572),
(573, '™', '', 'u2122', 7, 573),
(574, 'ℹ', '', 'u2139', 7, 574),
(575, '↔', '', 'u2194', 7, 575),
(576, '↕', '', 'u2195', 7, 576),
(577, '↖', '', 'u2196', 7, 577),
(578, '↗', '', 'u2197', 7, 578),
(579, '↘', '', 'u2198', 7, 579),
(580, '↙', '', 'u2199', 7, 580),
(581, '↩', '', 'u21a9', 7, 581),
(582, '↪', '', 'u21aa', 7, 582),
(583, '⏩', '', 'u23e9', 7, 583),
(584, '⏪', '', 'u23ea', 7, 584),
(585, '⏫', '', 'u23eb', 7, 585),
(586, '⏬', '', 'u23ec', 7, 586),
(587, 'Ⓜ', '', 'u24c2', 7, 587),
(588, '▪', '', 'u25aa', 7, 588),
(589, '▫', '', 'u25ab', 7, 589),
(590, '▶', '', 'u25b6', 7, 590),
(591, '◀', '', 'u25c0', 7, 591),
(592, '◻', '', 'u25fb', 7, 592),
(593, '◼', '', 'u25fc', 7, 593),
(594, '◽', '', 'u25fd', 7, 594),
(595, '♈', '', 'u2648', 7, 595),
(596, '♉', '', 'u2649', 7, 596),
(597, '♊', '', 'u264a', 7, 597),
(598, '♋', '', 'u264b', 7, 598),
(599, '♌', '', 'u264c', 7, 599),
(600, '♍', '', 'u264d', 7, 600),
(601, '♎', '', 'u264e', 7, 601),
(602, '♏', '', 'u264f', 7, 602),
(603, '♐', '', 'u2650', 7, 603),
(604, '♑', '', 'u2651', 7, 604),
(605, '♒', '', 'u2652', 7, 605),
(606, '♓', '', 'u2653', 7, 606),
(607, '♨', '', 'u2668', 7, 607),
(608, '♿', '', 'u267f', 7, 608),
(609, '⚪', '', 'u26aa', 7, 609),
(610, '⚫', '', 'u26ab', 7, 610),
(611, '✅', '', 'u2705', 7, 611),
(612, '✔', '', 'u2714', 7, 612),
(613, '✖', '', 'u2716', 7, 613),
(614, '✳', '', 'u2733', 7, 614),
(615, '✴', '', 'u2734', 7, 615),
(616, '❇', '', 'u2747', 7, 616),
(617, '❌', '', 'u274c', 7, 617),
(618, '❎', '', 'u274e', 7, 618),
(619, '❓', '', 'u2753', 7, 619),
(620, '❔', '', 'u2754', 7, 620),
(621, '❕', '', 'u2755', 7, 621),
(622, '❗', '', 'u2757', 7, 622),
(623, '➕', '', 'u2795', 7, 623),
(624, '➖', '', 'u2796', 7, 624),
(625, '➗', '', 'u2797', 7, 625),
(626, '➡', '', 'u27a1', 7, 626),
(627, '⤴', '', 'u2934', 7, 627),
(628, '⤵', '', 'u2935', 7, 628),
(629, '⬅', '', 'u2b05', 7, 629),
(630, '⬆', '', 'u2b06', 7, 630),
(631, '⬇', '', 'u2b07', 7, 631),
(632, '⭕', '', 'u2b55', 7, 632),
(633, '〰', '', 'u3030', 7, 633),
(634, '㊗', '', 'u3297', 7, 634),
(635, '㊙', '', 'u3299', 7, 635),
(636, '🌊', '', 'ud83cudf0a', 8, 636),
(637, '🌋', '', 'ud83cudf0b', 8, 637),
(638, '🌌', '', 'ud83cudf0c', 8, 638),
(639, '🎠', '', 'ud83cudfa0', 8, 639),
(640, '🎡', '', 'ud83cudfa1', 8, 640),
(641, '🎢', '', 'ud83cudfa2', 8, 641),
(642, '🎪', '', 'ud83cudfaa', 8, 642),
(643, '🏠', '', 'ud83cudfe0', 8, 643),
(644, '🏡', '', 'ud83cudfe1', 8, 644),
(645, '🏢', '', 'ud83cudfe2', 8, 645),
(646, '🏣', '', 'ud83cudfe3', 8, 646),
(647, '🏤', '', 'ud83cudfe4', 8, 647),
(648, '🏥', '', 'ud83cudfe5', 8, 648),
(649, '🏦', '', 'ud83cudfe6', 8, 649),
(650, '🏧', '', 'ud83cudfe7', 8, 650),
(651, '🏨', '', 'ud83cudfe8', 8, 651),
(652, '🏩', '', 'ud83cudfe9', 8, 652),
(653, '🏪', '', 'ud83cudfea', 8, 653),
(654, '🏫', '', 'ud83cudfeb', 8, 654),
(655, '🏬', '', 'ud83cudfec', 8, 655),
(656, '🏭', '', 'ud83cudfed', 8, 656),
(657, '🏯', '', 'ud83cudfef', 8, 657),
(658, '🏰', '', 'ud83cudff0', 8, 658),
(659, '🗻', '', 'ud83duddfb', 8, 659),
(660, '🗼', '', 'ud83duddfc', 8, 660),
(661, '🗽', '', 'ud83duddfd', 8, 661),
(662, '🗾', '', 'ud83duddfe', 8, 662),
(663, '🗿', '', 'ud83duddff', 8, 663),
(664, '🚀', '', 'ud83dude80', 8, 664),
(665, '🚁', '', 'ud83dude81', 8, 665),
(666, '🚂', '', 'ud83dude82', 8, 666),
(667, '🚃', '', 'ud83dude83', 8, 667),
(668, '🚄', '', 'ud83dude84', 8, 668),
(669, '🚅', '', 'ud83dude85', 8, 669),
(670, '🚆', '', 'ud83dude86', 8, 670),
(671, '🚇', '', 'ud83dude87', 8, 671),
(672, '🚈', '', 'ud83dude88', 8, 672),
(673, '🚉', '', 'ud83dude89', 8, 673),
(674, '🚐', '', 'ud83dude90', 8, 674),
(675, '🚑', '', 'ud83dude91', 8, 675),
(676, '🚒', '', 'ud83dude92', 8, 676),
(677, '🚓', '', 'ud83dude93', 8, 677),
(678, '🚔', '', 'ud83dude94', 8, 678),
(679, '🚕', '', 'ud83dude95', 8, 679),
(680, '🚖', '', 'ud83dude96', 8, 680),
(681, '🚗', '', 'ud83dude97', 8, 681),
(682, '🚘', '', 'ud83dude98', 8, 682),
(683, '🚙', '', 'ud83dude99', 8, 683),
(684, '🚠', '', 'ud83dudea0', 8, 684),
(685, '🚡', '', 'ud83dudea1', 8, 685),
(686, '🚢', '', 'ud83dudea2', 8, 686),
(687, '🚣', '', 'ud83dudea3', 8, 687),
(688, '🚤', '', 'ud83dudea4', 8, 688),
(689, '🚦', '', 'ud83dudea6', 8, 689),
(690, '🚲', '', 'ud83dudeb2', 8, 690),
(691, '🛤', '', 'ud83dudee4', 8, 691),
(692, '⛪', '', 'u26ea', 8, 692),
(693, '✈', '', 'u2708', 8, 693);


INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity_file' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","owner_network","owner_member_member","owner_member","parent_member","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
  INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity_file' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
  INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity_file' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

  INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity_file' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');