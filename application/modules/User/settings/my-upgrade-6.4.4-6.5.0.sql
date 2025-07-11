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

ALTER TABLE `engine4_users` ADD `is_verified` TINYINT(1) NOT NULL DEFAULT "0";

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


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_main_manage_verification', 'user', 'Manage Verifications', '', '{"route":"core_admin_settings","action":"verification"}', 'core_admin_main_manage', '', 10),
('core_admin_main_settings_verification', 'core', 'Verification Settings', '', '{"route":"core_admin_settings","action":"verification"}', 'core_admin_main_manage_verification', '', 1),
('core_admin_main_manage_verificationrequests', 'user', 'Manage Verifications', '', '{"route":"admin_default","module":"user","controller":"manage","action":"verification-requests"}', 'core_admin_main_manage_verification', '', 2);

  
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
