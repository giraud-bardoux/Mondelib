ALTER TABLE `engine4_core_mailtemplates` CHANGE `type` `type` VARCHAR(256) NOT NULL;
ALTER TABLE `engine4_core_menuitems` CHANGE `name` `name` VARCHAR(256) NOT NULL;
ALTER TABLE `engine4_core_menuitems` CHANGE `menu` `menu` VARCHAR(256) NULL DEFAULT NULL, CHANGE `submenu` `submenu` VARCHAR(256) NULL DEFAULT NULL;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_editor', 'core', 'TinyMCE Editor Settings', '', '{"route":"core_admin_settings","action":"editor"}', 'core_admin_main_settings', '', 16),
('core_admin_main_manage_comments', 'core', 'Manage Comments', '', '{"route":"admin_default","module":"core","controller":"manage-comments"}', 'core_admin_main_manage', '', 10), ('core_admin_main_contentcomments', 'core', 'Comments on Content', '', '{"route":"admin_default","module":"core","controller":"manage-comments"}', 'core_admin_main_manage_comments', '', 1),
('core_admin_main_activitycomments', 'core', 'Comments on Activity Feeds', '', '{"route":"admin_default","module":"core","controller":"manage-comments", "action":"activity"}', 'core_admin_main_manage_comments', '', 2),

('core_admin_settings_activity', 'activity', 'Activity Feed Settings', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"index"}', 'core_admin_main_settings_activity', '', 1),
('core_admin_settings_activitytypes', 'activity', 'Activity Feeds Item Type Settings', '', '{"route":"admin_default","module":"activity","controller":"settings","action":"types"}', 'core_admin_main_settings_activity', '', 2),
('core_admin_main_manage_activity', 'core', 'Manage Activity Feeds', '', '{"route":"admin_default","module":"core","controller":"manage-activity"}', 'core_admin_main_settings_activity', '', 3),('core_admin_main_manage_activitycom', 'core', 'Manage Comments on Activity Feeds', '', '{"route":"admin_default","module":"core","controller":"manage-comments", "action":"activity"}', 'core_admin_main_settings_activity', '', 4);

UPDATE `engine4_core_settings` SET `value` = 'socialengine' WHERE `engine4_core_settings`.`name` = 'core.iframely.host' AND `engine4_core_settings`.`value` = 'self';
DELETE FROM engine4_core_tasks WHERE `engine4_core_tasks`.`plugin` = "Core_Plugin_Task_Statistics";
ALTER TABLE `engine4_core_languages` ADD `enabled` TINYINT(1) NOT NULL DEFAULT "1";
ALTER TABLE `engine4_core_likes` ADD `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
DELETE FROM `engine4_core_settings` WHERE `engine4_core_settings`.`name` = "core.general.social.code";
DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.social-share";

ALTER TABLE `engine4_core_languages` ADD `icon` VARCHAR(255) NULL DEFAULT NULL;

UPDATE `engine4_core_modules` SET `type` = 'core' WHERE `engine4_core_modules`.`name` = 'network';

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"core","controller":"manage-packages"}' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_plugins';
