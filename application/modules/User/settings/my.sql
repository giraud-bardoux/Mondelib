
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */


-- --------------------------------------------------------

--
-- Table structure for table `engine4_users`
--

DROP TABLE IF EXISTS `engine4_users`;
CREATE TABLE `engine4_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `email` VARCHAR(128) NULL DEFAULT NULL,
  `username` varchar(128) default NULL,
  `displayname` varchar(128) NOT NULL default '',
  `firstname` VARCHAR(16) NULL DEFAULT NULL,
  `lastname` VARCHAR(16) NULL DEFAULT NULL,
  `dob` DATE NULL DEFAULT NULL,
  `gender` VARCHAR(16) NULL DEFAULT NULL,
  `profile_type` INT(11) NOT NULL DEFAULT '1',
  `photo_id` int(11) unsigned NOT NULL default '0',
  `status` text NULL,
  `status_date` datetime NULL,
  `password` char(255) NOT NULL,
  `salt` char(64) NOT NULL,
  `locale` varchar(16) NOT NULL default 'auto',
  `language` varchar(8) NOT NULL default 'en_US',
  `timezone` varchar(64) NOT NULL default 'America/Los_Angeles',
  `search` tinyint(1) NOT NULL default '1',
  `show_profileviewers` tinyint(1) NOT NULL default '1',
  `level_id` int(11) unsigned NOT NULL,
  `invites_used` int(11) unsigned NOT NULL default '0',
  `extra_invites` int(11) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL default '1',
  `verified` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  `creation_date` datetime NOT NULL,
  `creation_ip` varbinary(16) NOT NULL,
  `modified_date` datetime NOT NULL,
  `lastlogin_date` datetime default NULL,
  `lastlogin_ip` varbinary(16) default NULL,
  `update_date` int(11) default NULL,
  `member_count` smallint(5) unsigned NOT NULL default '0',
  `view_count` int(11) unsigned NOT NULL default '0',
  `comment_count` int(11) unsigned NOT NULL default '0',
  `like_count` int(11) unsigned NOT NULL default '0',
  `coverphoto` int (11) unsigned NOT NULL DEFAULT '0',
  `coverphotoparams` VARCHAR(265) NULL DEFAULT NULL,
  `view_privacy` VARCHAR(24) NOT NULL default 'everyone',
  `disable_email` TINYINT(1) NOT NULL DEFAULT '0',
  `disable_adminemail` TINYINT(1) NOT NULL DEFAULT '0',
  `last_password_reset` DATETIME NULL,
  `last_login_attempt` DATETIME NULL,
  `login_attempt_count` INT(5) NOT NULL DEFAULT '0',
  `donotsellinfo` TINYINT(1) NOT NULL DEFAULT '0',
  `mention` VARCHAR(24) NOT NULL default 'registered',
  `birthday_format` VARCHAR(24) DEFAULT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT '0',
  `referral_code` VARCHAR(256) NULL DEFAULT NULL,
  `referral_count` INT(11) NOT NULL DEFAULT '0',
  `avatar_id` INT(11) NOT NULL DEFAULT '0',
  `location` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `USERNAME` (`username`),
  KEY `MEMBER_COUNT` (`member_count`),
  KEY `CREATION_DATE` (`creation_date`),
  KEY `search` (`search`),
  KEY `enabled` (`enabled`),
  KEY `is_verified` (`is_verified`),
  KEY `referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;
-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_block`
--

DROP TABLE IF EXISTS `engine4_user_block`;
CREATE TABLE IF NOT EXISTS `engine4_user_block` (
  `user_id` int(11) unsigned NOT NULL,
  `blocked_user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`blocked_user_id`),
  KEY `REVERSE` (`blocked_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_facebook`
--

DROP TABLE IF EXISTS `engine4_user_facebook`;
CREATE TABLE IF NOT EXISTS `engine4_user_facebook` (
  `user_id` int(11) unsigned NOT NULL,
  `facebook_uid` bigint(20) unsigned NOT NULL,
  `access_token` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `expires` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `facebook_uid` (`facebook_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_forgot`
--

DROP TABLE IF EXISTS `engine4_user_forgot`;
CREATE TABLE IF NOT EXISTS `engine4_user_forgot` (
  `user_id` int(11) unsigned NOT NULL,
  `code` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_listitems`
--

DROP TABLE IF EXISTS `engine4_user_listitems`;
CREATE TABLE IF NOT EXISTS `engine4_user_listitems` (
  `listitem_id` int(11) unsigned NOT NULL auto_increment,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_lists`
--

DROP TABLE IF EXISTS `engine4_user_lists`;
CREATE TABLE IF NOT EXISTS `engine4_user_lists` (
  `list_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `owner_id` int(11) unsigned NOT NULL,
  `child_count` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`list_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_logins`
--

DROP TABLE IF EXISTS `engine4_user_logins`;
CREATE TABLE IF NOT EXISTS `engine4_user_logins` (
  `login_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned default NULL,
  `email` varchar(128) default NULL,
  `ip` varbinary(16) NOT NULL,
  `timestamp` datetime NOT NULL,
  `state` enum('success','no-member','bad-password','disabled','unpaid','third-party','v3-migration','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL default 'unknown',
  `active` tinyint(1) NOT NULL default '0',
  `source` VARCHAR(32) NULL DEFAULT NULL,
  PRIMARY KEY (`login_id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_membership`
--

DROP TABLE IF EXISTS `engine4_user_membership`;
CREATE TABLE `engine4_user_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `resource_approved` tinyint(1) NOT NULL default '0',
  `user_approved` tinyint(1) NOT NULL default '0',
  `message` text default NULL,
  `description` text default NULL,
  PRIMARY KEY  (`resource_id`, `user_id`),
  KEY `REVERSE` (`user_id`),
  KEY `active` (`active`),
  KEY `resource_approved` (`resource_approved`),
  KEY `user_approved` (`user_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_online`
--

DROP TABLE IF EXISTS `engine4_user_online`;
CREATE TABLE IF NOT EXISTS `engine4_user_online` (
  `ip` varbinary(16) NOT NULL,
  `user_id` int(11) unsigned NOT NULL default '0',
  `active` datetime NOT NULL,
  PRIMARY KEY  (`ip`,`user_id`),
  KEY `LOOKUP` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_settings`
--

DROP TABLE IF EXISTS `engine4_user_settings`;
CREATE TABLE IF NOT EXISTS `engine4_user_settings` (
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_signup`
--

DROP TABLE IF EXISTS `engine4_user_signup`;
CREATE TABLE IF NOT EXISTS `engine4_user_signup` (
  `signup_id` int(11) unsigned NOT NULL auto_increment,
  `class` varchar(128) NOT NULL,
  `order` smallint(6) NOT NULL default '999',
  `enable` smallint(1) NOT NULL default '0',
  PRIMARY KEY  (`signup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Dumping data for table `engine4_user_signup`
--

INSERT IGNORE INTO `engine4_user_signup` (`signup_id`, `class`, `order`, `enable`) VALUES
(1, 'User_Plugin_Signup_Account', 1, 1),
-- (2, 'User_Plugin_Signup_Otp', 2, 1),
(3, 'User_Plugin_Signup_Fields', 3, 1),
(4, 'User_Plugin_Signup_Photo', 4, 1),
(5, 'User_Plugin_Signup_Invite', 5, 0);
-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_twitter`
--

DROP TABLE IF EXISTS `engine4_user_twitter`;
CREATE TABLE IF NOT EXISTS `engine4_user_twitter` (
  `user_id` int(10) unsigned NOT NULL,
  `twitter_uid` bigint(20) unsigned NOT NULL,
  `twitter_token` varchar(255) NOT NULL default '',
  `twitter_secret` varchar(255) NOT NULL default '',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `twitter_uid` (`twitter_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_verify`
--

DROP TABLE IF EXISTS `engine4_user_verify`;
CREATE TABLE IF NOT EXISTS `engine4_user_verify` (
  `user_id` int(11) unsigned NOT NULL,
  `code` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_jobtypes`
--

INSERT IGNORE INTO `engine4_core_jobtypes` (`title`, `type`, `module`, `plugin`, `priority`) VALUES
('Rebuild Member Privacy', 'user_maintenance_rebuild_privacy', 'user', 'User_Plugin_Job_Maintenance_RebuildPrivacy', 50);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_mailtemplates`
--

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('user_account_approved', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo]'),
('notify_friend_accepted', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_friend_request', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_post_user', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_tagged', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('user_otp', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[code]'),
('user_deleteotp', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[code]'),
('abuse_report', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[admin_link]');

-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_main_user', 'user', 'Members', '', '{"route":"user_general","action":"browse","icon":"fa fa-user"}', 'core_main', '', 2),

('core_sitemap_user', 'user', 'Members', '', '{"route":"user_general","action":"browse"}', 'core_sitemap', '', 2),

('user_home_updates', 'user', 'View Recent Updates', '', '{"route":"recent_activity"}', 'user_home', '', 1),
('user_home_view', 'user', 'View My Profile', 'User_Plugin_Menus', '{"route":"user_profile_self"}', 'user_home', '', 2),
('user_home_edit', 'user', 'Edit My Profile', 'User_Plugin_Menus', '{"route":"user_extended","module":"user","controller":"edit","action":"profile"}', 'user_home', '', 3),
('user_home_friends', 'user', 'Browse Members', '', '{"route":"user_general","controller":"index","action":"browse"}', 'user_home', '', 4),

('user_profile_edit', 'user', 'Edit Profile', 'User_Plugin_Menus', '', 'user_profile', '', 1),
('user_profile_friend', 'user', 'Friends', 'User_Plugin_Menus', '', 'user_profile', '', 3),
('user_profile_block', 'user', 'Block', 'User_Plugin_Menus', '', 'user_profile', '', 4),
('user_profile_report', 'user', 'Report User', 'User_Plugin_Menus', '', 'user_profile', '', 5),
('user_profile_admin', 'user', 'Admin Settings', 'User_Plugin_Menus', '', 'user_profile', '', 9),

('user_edit_profile', 'user', 'Personal Info', '', '{"route":"user_extended","module":"user","controller":"edit","action":"profile","icon":"fas fa-id-card-alt"}', 'user_edit', '', 1),
('user_edit_photo', 'user', 'Edit My Photo', '', '{"route":"user_extended","module":"user","controller":"edit","action":"photo","icon":"fas fa-user-edit"}', 'user_edit', '', 2),
('user_delete_photos', 'user', 'Delete My Photos', 'User_Plugin_Menus', '{"route":"user_extended","module":"user","controller":"edit","action":"profile-photos","icon":"fas fa-trash-alt"}', 'user_edit', '', 4),

('user_settings_general', 'user', 'General', '', '{"route":"user_extended","module":"user","controller":"settings","action":"general", "icon":"fas fa-cog"}', 'user_settings', '', 1),
('user_settings_privacy', 'user', 'Privacy', '', '{"route":"user_extended","module":"user","controller":"settings","action":"privacy", "icon":"fas fa-lock"}', 'user_settings', '', 2),
('user_settings_notifications', 'user', 'Notifications', 'User_Plugin_Menus', '{"route":"user_extended","module":"user","controller":"settings","action":"notifications", "icon":"fas fa-bell"}', 'user_settings', '', 4),
('user_settings_emails', 'user', 'Emails', '', '{"route":"user_extended","module":"user","controller":"settings","action":"emails", "icon":"fas fa-envelope"}', 'user_settings', '', 5),
('user_settings_password', 'user', 'Change Password', '', '{"route":"user_extended", "module":"user", "controller":"settings", "action":"password", "icon":"fas fa-key"}', 'user_settings', '', 5),
('user_settings_delete', 'user', 'Delete Account', 'User_Plugin_Menus::canDelete', '{"route":"user_extended", "module":"user", "controller":"settings", "action":"delete", "icon":"fas fa-user-times"}', 'user_settings', '', 999),

('core_admin_main_manage_members', 'user', 'Members', '', '{"route":"admin_default","module":"user","controller":"manage"}', 'core_admin_main_manage', '', 1),
('core_admin_main_signup', 'user', 'Signup & Profile Settings', '', '{"route":"admin_default", "controller":"signup", "module":"user"}', 'core_admin_main_settings', '', 3),
('core_admin_main_socialmenus', 'user', 'Social Menus', '', '{"route":"admin_default", "action":"facebook", "controller":"settings", "module":"user"}', 'core_admin_main_settings', '', 4),
('core_admin_main_facebook', 'user', 'Facebook Integration', '', '{"route":"admin_default", "action":"facebook", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 4),
('core_admin_main_twitter', 'user', 'X Integration', '', '{"route":"admin_default", "action":"twitter", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 4),
('core_admin_main_settings_friends', 'user', 'Friendship / Follow', '', '{"route":"admin_default","module":"user","controller":"settings","action":"friends"}', 'core_admin_main_settings', '', 6),
('user_admin_banning_logins', 'user', 'Login History', '', '{"route":"admin_default","module":"user","controller":"logins","action":"index"}', 'core_admin_banning', '', 2),
('authorization_admin_level_user', 'user', 'Members', '', '{"route":"admin_default","module":"user","controller":"settings","action":"level"}', 'authorization_admin_level', '', 2)
;


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('user_home', 'standard', 'Member Home Quick Links Menu'),
('user_profile', 'standard', 'Member Profile Options Menu'),
('user_edit', 'standard', 'Member Edit Profile Navigation Menu'),
('user_browse', 'standard', 'Member Browse Navigation Menu'),
('user_settings', 'standard', 'Member Settings Navigation Menu');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_modules`
--

INSERT INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES
('user', 'Members', 'Members', '4.10.0beta1', 1, 'core');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('user.friends.eligible', '2'),
('user.friends.direction', '1'),
('user.friends.verification', '1'),
('user.friends.lists', '1'),
('user.friends.maxfriends', '5000'),
('user.signup.approve', 1),
('user.signup.checkemail', 1),
('user.signup.username', 1),
('core.facebook.enable', 'none'),
('core.general.enableloginlogs', '0');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_core_tasks`
--

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Member Data Maintenance', 'user', 'User_Plugin_Task_Cleanup', 60);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `editable`, `is_generated`) VALUES
('status', 'user', '{item:$subject} {body:$body}', 1, 5, 0, 1, 4, 1, 0),
('post', 'user', '{actors:$subject:$object}: {body:$body}', 1, 7, 1, 4, 1, 1, 0),
('post_self', 'user', '{item:$subject} {body:$body}', 1, 5, 1, 4, 1, 1, 0),

('profile_photo_update', 'user', '{item:$subject} has added a new profile photo: {body:$body}', 1, 5, 1, 4, 1, 0, 1),
('friends', 'user', '{item:$subject} is now friends with {item:$object}.', 0, 3, 0, 1, 1, 0, 1),
('tagged', 'user', '{item:$subject} tagged {item:$object} in a {var:$label}:', 1, 7, 1, 1, 0, 0, 1),
('comment_user', 'user', '{item:$subject} commented on {item:$owner}''s profile: {body:$body}', 1, 7, 1, 3, 1, 0, 1),
('cover_photo_update', 'user', '{item:$subject} has added a new cover photo.', 1, 5, 1, 4, 1, 0, 1),
('login', 'user', '{item:$subject} has signed in.', 0, 1, 0, 1, 1, 0, 1),
('signup', 'user', '{item:$subject} has just signed up. Say hello!', 0, 5, 0, 1, 1, 0, 1);


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_notificationtypes`
--

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('post_user', 'user', '{item:$subject} has posted on your {item:$object:profile}.', 0, ''),
('friend_accepted', 'user', 'You and {item:$subject} are now friends.', 0, ''),
('friend_request', 'user', '{item:$subject} has requested to be your friend.', 1, 'user.friends.request-friend'),
('tagged', 'user', '{item:$subject} tagged you in a {item:$object:$label}.', 0, ''),
('abuse_report', 'user', 'A {var:$userprofilelink} has reported on the {var:$adminsidelink}.', 0, '');


-- --------------------------------------------------------

--
-- Dumping data for table `engine4_authorization_permissions`
--

-- ALL
-- auth_view, auth_comment
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'auth_view' as `name`,
    5 as `value`,
    '["everyone","registered","network","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'auth_comment' as `name`,
    5 as `value`,
    '["registered","network","member","owner"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

-- ADMIN, MODERATOR
-- create, edit, delete, view, comment, block, username
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'edit' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'delete' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'comment' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'block' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'username' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'activity' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

-- USER
-- create, edit, delete, view, comment, block, username
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'create' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'edit' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'delete' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'comment' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'block' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'search' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'status' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'username' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- PUBLIC
-- view
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('public');

-- can member level blocked
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'canblock' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'canblock' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_maps`
--

DROP TABLE IF EXISTS `engine4_user_fields_maps`;
CREATE TABLE `engine4_user_fields_maps` (
  `field_id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  `order` smallint(6) NOT NULL,
  PRIMARY KEY  (`field_id`,`option_id`,`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `engine4_user_fields_maps`
--

INSERT IGNORE INTO `engine4_user_fields_maps` (`field_id`, `option_id`, `child_id`, `order`) VALUES
(0, 0, 1, 1),
(1, 1, 2, 2),
(1, 1, 3, 3),
(1, 1, 4, 4),
(1, 1, 5, 5),
(1, 1, 6, 6),
(1, 1, 7, 7),
(1, 1, 8, 8),
(1, 1, 9, 9),
(1, 1, 10, 10),
(1, 1, 11, 11),
(1, 1, 12, 12),
(1, 5, 13, 13),
(1, 5, 14, 14),
(1, 5, 15, 15),
(1, 5, 16, 16),
(1, 5, 17, 17),
(1, 5, 18, 18),
(1, 5, 19, 19),
(1, 5, 20, 20),
(1, 5, 21, 21),
(1, 5, 22, 22),
(1, 5, 23, 23),
(1, 9, 24, 24),
(1, 9, 25, 25),
(1, 9, 26, 26),
(1, 9, 27, 27),
(1, 9, 28, 28),
(1, 9, 29, 29),
(1, 9, 30, 30),
(1, 9, 31, 31),
(1, 9, 32, 32),
(1, 9, 33, 33),
(1, 9, 34, 34);

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_meta`
--

DROP TABLE IF EXISTS `engine4_user_fields_meta`;
CREATE TABLE `engine4_user_fields_meta` (
  `field_id` int(11) unsigned NOT NULL auto_increment,
  `type` varchar(24) NOT NULL,
  `label` varchar(64) NOT NULL,
  `description` varchar(255) NULL DEFAULT NULL,
  `alias` varchar(32) NOT NULL default '',
  `required` tinyint(1) NOT NULL default '0',
  `display` tinyint(1) unsigned NOT NULL,
  `publish` tinyint(1) unsigned NOT NULL default '0',
  `search` tinyint(1) unsigned NOT NULL default '0',
  `show` tinyint(1) unsigned NOT NULL default '1',
  `order` smallint(3) unsigned NOT NULL default '999',
  `config` text NULL,
  `validators` text NULL,
  `filters` text NULL,
  `style` text NULL,
  `error` text NULL,
  `icon` text NULL,
  PRIMARY KEY  (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_fields`
--

INSERT IGNORE INTO `engine4_user_fields_meta` (`field_id`, `type`, `label`, `description`, `alias`, `required`, `config`, `validators`, `filters`, `display`, `search`, `icon`) VALUES
(1, 'profile_type', 'Profile Type', '', 'profile_type', 1, '', NULL, NULL, 0, 2, NULL),
(2, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(3, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(4, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(5, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(6, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt'),
(7, 'heading', 'Contact Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(8, 'website', 'Website', '', '', 0, '', NULL, NULL, 1, 0, 'fa fa-globe'),
(9, 'twitter', 'X', '', '', 0, '', NULL, NULL, 1, 0, 'fa-brands fa-square-x-twitter'),
(10, 'facebook', 'Facebook', '', '', 0, '', NULL, NULL, 1, 0, 'fab fa-facebook-f'),
(11, 'heading', 'Personal Details', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(12, 'about_me', 'About Me', '', '', 0, '', NULL, NULL, 1, 0 ,'fa fa-info-circle'),
(13, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(14, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(15, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(16, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(17, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt'),
(18, 'heading', 'Contact Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(19, 'website', 'Website', '', '', 0, '', NULL, NULL, 1, 0, 'fa fa-globe'),
(20, 'twitter', 'X', '', '', 0, '', NULL, NULL, 1, 0, 'fa-brands fa-square-x-twitter'),
(21, 'facebook', 'Facebook', '', '', 0, '', NULL, NULL, 1, 0, 'fab fa-facebook-f'),
(22, 'heading', 'Personal Details', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(23, 'about_me', 'About Me', '', '', 0, '', NULL, NULL, 1, 0 ,'fa fa-info-circle'),
(24, 'heading', 'Personal Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(25, 'first_name', 'First Name', '', 'first_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(26, 'last_name', 'Last Name', '', 'last_name', 1, '', '[["StringLength",false,[1,32]]]', NULL, 1, 2, 'fa fa-user'),
(27, 'gender', 'Gender', '', 'gender', 0, '', NULL, NULL, 1, 1, 'fa fa-venus-mars'),
(28, 'birthdate', 'Birthday', '', 'birthdate', 0, '', NULL, NULL, 1, 1, 'fa fa-calendar-alt'),
(29, 'heading', 'Contact Information', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(30, 'website', 'Website', '', '', 0, '', NULL, NULL, 1, 0, 'fa fa-globe'),
(31, 'twitter', 'X', '', '', 0, '', NULL, NULL, 1, 0, 'fa-brands fa-square-x-twitter'),
(32, 'facebook', 'Facebook', '', '', 0, '', NULL, NULL, 1, 0, 'fab fa-facebook-f'),
(33, 'heading', 'Personal Details', '', '', 0, '', NULL, NULL, 1, 0, NULL),
(34, 'about_me', 'About Me', '', '', 0, '', NULL, NULL, 1, 0 ,'fa fa-info-circle');


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_options`
--

DROP TABLE IF EXISTS `engine4_user_fields_options`;
CREATE TABLE `engine4_user_fields_options` (
  `option_id` int(11) unsigned NOT NULL auto_increment,
  `field_id` int(11) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `order` smallint(6) NOT NULL default '999',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`option_id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_options`
--

INSERT IGNORE INTO `engine4_user_fields_options` (`option_id`, `field_id`, `label`, `order`, `type`) VALUES
(1, 1, 'Regular Member', 1,0),
(2, 5, 'Male', 1,0),
(3, 5, 'Female', 2,0),
(4, 5, 'Other', 3,0),
(5, 1, 'Super Admin Member', 5,1),
(6, 16, 'Male', 6,0),
(7, 16, 'Female', 7,0),
(8, 16, 'Other', 8,0),
(9, 1, 'Admin Member', 9,1),
(10, 27, 'Male', 10,0),
(11, 27, 'Female', 11,0),
(12, 27, 'Other', 12,0);

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_values`
--

DROP TABLE IF EXISTS `engine4_user_fields_values`;
CREATE TABLE `engine4_user_fields_values` (
  `item_id` int(11) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  `index` smallint(3) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  `privacy` varchar(64) default NULL,
  PRIMARY KEY  (`item_id`,`field_id`,`index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

--
-- Dumping data for table `engine4_user_fields_values`
--


-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_fields_search`
--

DROP TABLE IF EXISTS `engine4_user_fields_search`;
CREATE TABLE IF NOT EXISTS `engine4_user_fields_search` (
  `item_id` int(11) unsigned NOT NULL,
  `profile_type` smallint(11) unsigned NULL,
  `first_name` varchar(255) NULL,
  `last_name` varchar(255) NULL,
  `gender` smallint(6) unsigned NULL,
  `birthdate` date NULL,
  PRIMARY KEY  (`item_id`),
  KEY (`profile_type`),
  KEY (`first_name`),
  KEY (`last_name`),
  KEY (`gender`),
  KEY (`birthdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_emailsettings`
--

DROP TABLE IF EXISTS `engine4_user_emailsettings`;
CREATE TABLE IF NOT EXISTS `engine4_user_emailsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(128) NOT NULL,
  `email` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_emails', 'user', 'Default Email Alerts', '', '{"route":"admin_default","module":"user","controller":"settings","action":"emails"}', 'core_admin_main_settings', '', 12);

INSERT IGNORE INTO `engine4_authorization_permissions`
SELECT
  level_id as `level_id`,
  'user' as `type`,
  'mention' as `name`,
  5 as `value`,
  '["owner_network","registered","network","member","owner"]' as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
--
-- indexing for table `engine4_users`
--
ALTER TABLE `engine4_users` ADD INDEX(`level_id`);
ALTER TABLE `engine4_users` ADD INDEX(`verified`);
ALTER TABLE `engine4_users` ADD INDEX(`approved`);
ALTER TABLE `engine4_users` ADD INDEX(`modified_date`);
ALTER TABLE `engine4_users` ADD INDEX(`view_count`);
ALTER TABLE `engine4_users` ADD INDEX(`comment_count`);
ALTER TABLE `engine4_users` ADD INDEX(`like_count`);
ALTER TABLE `engine4_users` ADD INDEX(`coverphoto`);
ALTER TABLE `engine4_users` ADD INDEX(`view_privacy`);
ALTER TABLE `engine4_users` ADD INDEX(`disable_email`);

--
-- indexing for table `engine4_user_logins`
--
ALTER TABLE `engine4_user_logins` ADD INDEX(`state`);
ALTER TABLE `engine4_user_logins` ADD INDEX(`active`);

--
-- indexing for table `engine4_user_signup
--
ALTER TABLE `engine4_user_signup` ADD INDEX(`order`);
ALTER TABLE `engine4_user_signup` ADD INDEX(`enable`);

DROP TABLE IF EXISTS `engine4_user_codes`;
CREATE TABLE IF NOT EXISTS `engine4_user_codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL,
  `code` varchar(64) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `email` (`email`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'abuseNotifi' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'abuseEmail' as `name`,
    1 as `value`,
    NULL as `params`
FROM `engine4_authorization_levels` WHERE `type` IN('admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
SELECT
  level_id as `level_id`,
  'user' as `type`,
  'birthday_options' as `name`,
  5 as `value`,
  '["monthday","monthdayyear"]' as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'changeemail' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'changeemail' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'emailverify' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'emailverify' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

  
INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('user_changeemailotp', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[object_link],[code]');

INSERT IGNORE INTO `engine4_authorization_mapprofiletypelevels` (`title`, `description`, `profile_type_id`, `member_level_id`, `member_count`) VALUES
('', '', 5, 1, 0),
('', '', 9, 2, 0);
  
-- editor
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_menubar_editor' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_menubar_editor' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_statusbar_editor' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_statusbar_editor' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
  
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_autosave_editor' as `name`,
    3 as `value`,
    300 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_autosave_editor' as `name`,
    3 as `value`,
    300 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_editors_allow' as `name`,
    5 as `value`,
    '["table","fullscreen","media","code","image","link","lists","advlist","searchreplace","emoticons","autolink","autosave","preview","directionality","visualblocks","visualchars","codesample","wordcount","accordion","charmap","pagebreak","nonbreaking","anchor","insertdatetime"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'core_editors_allow' as `name`,
    5 as `value`,
    '["table","fullscreen","media","code","image","link","lists","advlist","searchreplace","emoticons","autolink","autosave","preview","directionality","visualblocks","visualchars","codesample","wordcount","accordion","charmap","pagebreak","nonbreaking","anchor","insertdatetime"]' as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'verified' as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'verified' as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'verified_icon' as `name`,
    3 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'verified_icon' as `name`,
    3 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_main_manage_verification', 'user', 'Manage Verifications', '', '{"route":"admin_default","module":"payment","controller":"verification","action":"index"}', 'core_admin_main_manage', '', 10),
('core_admin_main_settings_verification', 'core', 'Verification Settings', '', '{"route":"admin_default","module":"payment","controller":"verification","action":"index"}', 'core_admin_main_manage_verification', '', 1),
('core_admin_main_manage_verificationrequests', 'user', 'Manage Verification Requests', '', '{"route":"admin_default","module":"user","controller":"manage","action":"verification-requests"}', 'core_admin_main_manage_verification', '', 2);

  
DROP TABLE IF EXISTS `engine4_user_verificationrequests`;
CREATE TABLE IF NOT EXISTS `engine4_user_verificationrequests` (
  `verificationrequest_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT "0",
  `message` VARCHAR(265) NULL DEFAULT NULL,
  PRIMARY KEY  (`verificationrequest_id`),
  KEY `approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
("user_verirequestto_superadmin", "user", '{item:$subject} has sent verification request and is waiting for approval.', 0, ""),
("user_verirequest_approved", "user", 'Your {var:$verificationlink} request has been approved. You are now a verified member.', 0, ""),
("user_verirequest_reject", "user", 'Your {var:$verificationlink} request has been rejected.', 0, "");



INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `is_admin`) VALUES
("content_waitingapprovalforadmin", "user", '{item:$subject} has created a new {var:$content_text} {item:$object} and this is waiting for admin approval.', 0, "", 1),
("content_resubmitwaitapprforadmin", "user", '{item:$subject} has resubmitted the {var:$content_text} {item:$object} and is waiting for admin approval.', 0, "", 1),
("content_waitingapprovalforowner", "user", 'Your {var:$content_text} {item:$object} has been created and is waiting for admin approval.', 0, "", 0),
("content_approvedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been approved.', 0, "", 0),
("content_disapprovedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been disapproved.', 0, "", 0),
("content_rejectedbyadmin", "user", 'Your {var:$content_text} {item:$object} has been rejected.', 0, "", 0),
("content_ticketcreate", "user", '{item:$subject} has opened a new support ticket {var:$admin_ticket_link}.', 0, "", 1),
("content_ticketreply", "user", 'You have received a reply on your support ticket {var:$ticket_link}.', 0, "", 0),
("content_newticketcreate", "user", 'A new support ticket {var:$ticket_link} has been created by our site admin.', 0, "", 0);

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`, `is_admin`) VALUES
("notify_content_waitingapprovalforadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[owner_title],[content_text]", 1),
("notify_content_resubmitwaitapprforadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[owner_title],[content_text]", 1),
("notify_content_waitingapprovalforowner", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_approvedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_disapprovedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[content_text]", 0),
("notify_content_rejectedbyadmin", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[admin_ticket_link],[ticket_subject],[ticket_description]", 0),
("notify_content_ticketreply", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_subject],[ticket_description]", 0),
("notify_content_newticketcreate", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_id],[ticket_description]", 0),
("notify_content_ticketcreate", "user", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description],[object_title],[owner_title],[ticket_link],[ticket_id],[ticket_description]", 1);


INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticket' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticket' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');
  

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticketreply' as `type`,
    'view' as `name`,
    2 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('moderator', 'admin');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'core_ticketreply' as `type`,
    'view' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('user');

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_google', 'user', 'Google Integration', '', '{"route":"admin_default", "action":"google", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 5),
('core_admin_main_linkedin', 'user', 'LinkedIn Integration', '', '{"route":"admin_default", "action":"linkedin", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 6),
("core_admin_main_telegram", "user", "Telegram Integration", "", '{"route":"admin_default","module":"user","controller":"settings","action":"telegram"}', "core_admin_main_socialmenus", "", 7);


CREATE TABLE IF NOT EXISTS `engine4_user_google` (
  `google_id` int(11) NOT NULL auto_increment,
  `user_id` INT(11) NOT NULL,
  `google_uid` varchar(128) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT "",
  `code` varchar(255) NOT NULL DEFAULT "",
  `expires` bigint(20) UNSIGNED NOT NULL DEFAULT "0",
  PRIMARY KEY (`google_id`),
  UNIQUE KEY `google_uid` (`google_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_user_telegram` (
`telegram_id` int(11) NOT NULL auto_increment,
`user_id` INT(11) NOT NULL,
`telegram_uid` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
`access_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
PRIMARY KEY (`telegram_id`),
UNIQUE KEY `telegram_uid` (`telegram_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `engine4_user_linkedin` (
  `user_id` int(11) UNSIGNED NOT NULL auto_increment,
  `linkedin_uid` varchar(128) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT "",
  `code` varchar(255) NOT NULL DEFAULT "",
  `expires` bigint(20) UNSIGNED NOT NULL DEFAULT "0",
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `linkedin_uid` (`linkedin_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Signup
DROP TABLE IF EXISTS `engine4_user_avatars`;
CREATE TABLE `engine4_user_avatars` (
  `avatar_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY  (`avatar_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `engine4_user_membership` ADD INDEX(`active`);
ALTER TABLE `engine4_user_membership` ADD INDEX(`resource_approved`);
ALTER TABLE `engine4_user_membership` ADD INDEX(`user_approved`);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_signupprocess', 'user', 'Signup Process', '', '{"route":"admin_default", "controller":"signup", "module":"user"}', 'core_admin_main_signup', '', 1);
-- ('core_admin_manageavatars', 'user', 'Manage Avatar Images', '', '{"route":"admin_default", "module":"user", "controller":"avatars"}', 'core_admin_main_signup', '', 3);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_otp', 'user', 'OTP Settings', '', '{"route":"admin_default", "module":"user", "controller":"otp", "action":"index"}', 'core_admin_main_settings', '', 21),
('core_admin_otp_settings', 'user', 'OTP Settings', '', '{"route":"admin_default", "module":"user", "controller":"otp", "action":"index"}', 'core_admin_main_otp', '', 1),
("core_admin_otp_integration", "user", "3rd Party Services Integration", "", '{"route":"admin_default","module":"user","controller":"otp","action":"service-integration"}', "core_admin_main_otp", "", 2),
("core_admin_otp_countries", "user", "Manage Countries", "", '{"route":"admin_default","module":"user", "controller":"otp","action":"manage-countries"}', "core_admin_main_otp", "", 3);

ALTER TABLE `engine4_users` ADD `phone_number` VARCHAR(45) NULL;
ALTER TABLE `engine4_users` ADD `country_code` VARCHAR(16) NULL;
ALTER TABLE `engine4_users` ADD `enable_verification` tinyint(1) NOT NULL DEFAULT "0";
ALTER TABLE `engine4_user_codes` ADD `resend_count` INT(11) NOT NULL, ADD `type` VARCHAR(16) NULL DEFAULT NULL;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_admin_phone_messages',  'user',  'Manage & Send Messages',  '',  '{"route":"admin_default","module":"user","controller":"phone-messages","action":"index"}',  'core_admin_main_manage',  '',  60);

DROP TABLE IF EXISTS `engine4_user_phonemessages`;
CREATE TABLE IF NOT EXISTS `engine4_user_phonemessages` (
  `phonemessage_id` int(11) NOT NULL auto_increment,
  `parent_type` varchar(45) NOT NULL,
  `type` VARCHAR(45) NULL,
  `specific` tinyint(1) NOT NULL DEFAULT "0",
  `message` TEXT NOT NULL,
  `user_id` INT(11) NOT NULL DEFAULT "0",
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`phonemessage_id`),
  KEY `parent_type` (`parent_type`),
  KEY `type` (`type`),
  KEY `specific` (`specific`),
  KEY `user_id` (`user_id`),
  KEY `creation_date` (`creation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_sharesettings', 'user', 'Social Share Settings', '', '{"route":"admin_default", "action":"socialshare", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 60);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_friends', 'user', 'Friendship Settings', '', '{"route":"admin_default","module":"user","controller":"settings","action":"friends"}', 'core_admin_main_settings_friends', '', 1),
('core_admin_main_follow', 'user', 'Follow Settings', '', '{"route":"admin_default","module":"user","controller":"settings","action":"follow"}', 'core_admin_main_settings_friends', '', 2);


ALTER TABLE `engine4_users` ADD `follow_count` INT(11) NOT NULL DEFAULT "0";

DROP TABLE IF EXISTS `engine4_user_follows`;
CREATE TABLE IF NOT EXISTS `engine4_user_follows` (
  `follow_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `resource_approved` TINYINT(1) NOT NULL DEFAULT "0",
  `user_approved` TINYINT(1) NOT NULL DEFAULT "0",
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`follow_id`),
  UNIQUE KEY `uniqueKey` (`user_id`,`resource_id`),
  KEY `resource_approved` (`resource_approved`),
  KEY `user_approved` (`user_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("user_follow", "user", '{item:$subject} has started following you.', "0", "", "1"),
("user_follow_request", "user", '{item:$subject} has requested to follow you.', "1", "user.follow.request-follow", "1"),
("user_follow_requestaccept", "user", '{item:$subject} has accepted your follow request.', "0", "", "1"),
("user_follow_create", "user", '{item:$subject} created a {item:$object:$itemtype} you might like.', 0, "", "1");

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_user_follow', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_user_follow_request', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]'),
('notify_user_follow_requestaccept', 'user', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');

ALTER TABLE `engine4_users` ADD `follow_verification` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_users` ADD `import` TINYINT(1) NOT NULL DEFAULT '1';


-- 7.0.0
ALTER TABLE `engine4_users` ADD `wallet_amount` FLOAT(16,2) NOT NULL DEFAULT '0.00';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_wallet', 'user', 'Wallet', 'Payment_Plugin_Menus', '{"route":"default", "module":"payment", "controller":"settings", "action":"wallet", "icon":"fa-solid fa-wallet"}', 'user_settings', '', 16);

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'viewtype' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'is_fullwidth' as `name`,
    0 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
  
INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'tab' as `name`,
    3 as `value`,
    'outside' as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

DROP TABLE IF EXISTS `engine4_user_recentsearch`;
CREATE TABLE IF NOT EXISTS  `engine4_user_recentsearch` (
  `recentsearch_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `user_id` INT NOT NULL ,
  `query` VARCHAR(128) NOT NULL,
  `id` INT(11) NOT NULL DEFAULT '0',
  `type` VARCHAR(128) NULL DEFAULT NULL,
  `creation_date` DATETIME NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;
