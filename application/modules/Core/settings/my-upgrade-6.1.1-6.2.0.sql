INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_monetization', 'core', 'Monetization', '', '{"uri":"javascript:void(0);this.blur();"}', 'core_admin_main', 'core_admin_main_monetization', 6);

UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_monetization' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_ads';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","controller":"ads"}' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_ads';

UPDATE `engine4_core_menuitems` SET `menu` = 'core_admin_main_monetization' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_payment';
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"payment","controller":"index","action":"index"}' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_payment';

DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_admin_main_wibiya";
