INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('user_admin_banning_logins', 'user', 'Login History', '', '{"route":"admin_default","module":"user","controller":"logins","action":"index"}', 'core_admin_banning', '', 2);
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.general.enableloginlogs', '0');
ALTER TABLE `engine4_user_logins` ADD `source` VARCHAR(32) NULL DEFAULT NULL;
UPDATE `engine4_core_menuitems` SET `plugin` = 'User_Plugin_Menus' WHERE `engine4_core_menuitems`.`name` = 'user_settings_notifications';
