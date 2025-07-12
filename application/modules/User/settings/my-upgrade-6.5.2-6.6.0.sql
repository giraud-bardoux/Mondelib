UPDATE `engine4_core_menuitems` SET `label` = 'Friendship / Follow' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_settings_friends";

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_friends', 'user', 'Friendship Settings', '', '{"route":"admin_default","module":"user","controller":"settings","action":"friends"}', 'core_admin_main_settings_friends', '', 1),
('core_admin_main_follow', 'user', 'Follow Settings', '', '{"route":"admin_default","module":"user","controller":"settings","action":"follow"}', 'core_admin_main_settings_friends', '', 2);

ALTER TABLE `engine4_users` ADD `follow_verification` TINYINT(1) NOT NULL DEFAULT '0';
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

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('user.friends.maxfriends', '5000');

UPDATE `engine4_core_settings` SET `value` = '1' WHERE `engine4_core_settings`.`name` = 'user.friends.direction';
UPDATE `engine4_core_settings` SET `value` = '1' WHERE `engine4_core_settings`.`name` = 'user.friends.verification';

UPDATE `engine4_core_menuitems` SET `order` = '999' WHERE `engine4_core_menuitems`.`name` = 'user_settings_delete';
UPDATE `engine4_core_menuitems` SET `order` = '3' WHERE `engine4_core_menuitems`.`name` = 'user_settings_invites';
UPDATE `engine4_core_menuitems` SET `order` = '4' WHERE `engine4_core_menuitems`.`name` = 'user_settings_notifications';
UPDATE `engine4_core_menuitems` SET `order` = '5' WHERE `engine4_core_menuitems`.`name` = 'user_settings_emails';

DELETE FROM engine4_activity_actiontypes WHERE `engine4_activity_actiontypes`.`type` = 'friends_follow';
DELETE FROM `engine4_activity_actions` WHERE `engine4_activity_actions`.`type` = 'friends_follow';

DELETE FROM engine4_activity_notifications WHERE `engine4_activity_notifications`.`type` = "friend_follow";
DELETE FROM engine4_activity_notifications WHERE `engine4_activity_notifications`.`type` = "friend_follow_accepted";
DELETE FROM engine4_activity_notifications WHERE `engine4_activity_notifications`.`type` = "friend_follow_request";

DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'friend_follow';
DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'friend_follow_accepted';
DELETE FROM engine4_activity_notificationtypes WHERE `engine4_activity_notificationtypes`.`type` = 'friend_follow_request';

DELETE FROM engine4_core_mailtemplates WHERE `engine4_core_mailtemplates`.`type` = "notify_friend_follow_request";
DELETE FROM engine4_core_mailtemplates WHERE `engine4_core_mailtemplates`.`type` = "notify_friend_follow_accepted";
DELETE FROM engine4_core_mailtemplates WHERE `engine4_core_mailtemplates`.`type` = "notify_friend_follow";

DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = 'user_edit_style';
ALTER TABLE `engine4_users`
  DROP `lastLoginDate`,
  DROP `lastUpdateDate`,
  DROP `inviteeName`,
  DROP `profileType`,
  DROP `memberLevel`,
  DROP `profileViews`,
  DROP `joinedDate`,
  DROP `friendsCount`;

ALTER TABLE `engine4_users` CHANGE `email` `email` VARCHAR(128) NULL DEFAULT NULL;

ALTER TABLE `engine4_users` ADD `firstname` VARCHAR(16) NULL DEFAULT NULL, ADD `lastname` VARCHAR(16) NULL DEFAULT NULL, ADD `dob` DATE NULL DEFAULT NULL, ADD `gender` VARCHAR(16) NULL DEFAULT NULL;

ALTER TABLE `engine4_users` ADD `profile_type` INT(11) NOT NULL DEFAULT '1';

DELETE FROM `engine4_user_signup` WHERE `engine4_user_signup`.`class` = 'User_Plugin_Signup_Fields';
DELETE FROM `engine4_user_signup` WHERE `engine4_user_signup`.`class` = 'User_Plugin_Signup_Otp';
DELETE FROM `engine4_user_signup` WHERE `engine4_user_signup`.`class` = 'User_Plugin_Signup_Photo';

UPDATE `engine4_user_signup` SET `order` = '1' WHERE `engine4_user_signup`.`class` = 'User_Plugin_Signup_Account';
UPDATE `engine4_user_signup` SET `order` = '2' WHERE `engine4_user_signup`.`class` = 'Payment_Plugin_Signup_Subscription';

UPDATE `engine4_core_menuitems` SET `label` = 'Signup & Profile Settings' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_signup';
UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_signup' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_settings_fields';

DROP TABLE IF EXISTS `engine4_user_avatars`;
CREATE TABLE `engine4_user_avatars` (
  `avatar_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` int(11) unsigned NOT NULL,
  `order` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT "1",
  PRIMARY KEY  (`avatar_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `engine4_users` ADD `avatar_id` INT(11) NOT NULL DEFAULT '0';

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

ALTER TABLE `engine4_users` ADD `import` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `engine4_users` CHANGE `import` `import` TINYINT(1) NOT NULL DEFAULT '1';