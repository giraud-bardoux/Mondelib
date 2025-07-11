INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_google', 'user', 'Google Integration', '', '{"route":"admin_default", "action":"google", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 5),
('core_admin_main_linkedin', 'user', 'LinkedIn Integration', '', '{"route":"admin_default", "action":"linkedin", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 6);

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

CREATE TABLE IF NOT EXISTS `engine4_user_linkedin` (
  `user_id` int(11) UNSIGNED NOT NULL auto_increment,
  `linkedin_uid` varchar(128) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT "",
  `code` varchar(255) NOT NULL DEFAULT "",
  `expires` bigint(20) UNSIGNED NOT NULL DEFAULT "0",
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `linkedin_uid` (`linkedin_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;


INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_sharesettings', 'user', 'Social Share Settings', '', '{"route":"admin_default", "action":"socialshare", "controller":"settings", "module":"user"}', 'core_admin_main_socialmenus', '', 60);

DELETE FROM engine4_activity_actiontypes WHERE `engine4_activity_actiontypes`.`type` = 'login';
DELETE FROM engine4_activity_actiontypes WHERE `engine4_activity_actiontypes`.`type` = 'logout';
DELETE FROM engine4_activity_actiontypes WHERE `engine4_activity_actiontypes`.`type` = 'signup';

DELETE FROM `engine4_activity_actions` WHERE `engine4_activity_actions`.`type` = 'signup';
DELETE FROM `engine4_activity_actions` WHERE `engine4_activity_actions`.`type` = 'login';
DELETE FROM `engine4_activity_actions` WHERE `engine4_activity_actions`.`type` = 'logout';
