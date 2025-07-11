DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = 'user_admin_banning_logins';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_main_socialmenus', 'user', 'Social Menus', '', '{"route":"admin_default", "action":"facebook", "controller":"settings", "module":"user"}', 'core_admin_main_settings', '', 4);

UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_socialmenus' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_facebook';
UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_socialmenus' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_twitter';
