DELETE FROM `engine4_core_menuitems` WHERE `engine4_core_menuitems`.`menu` = 'mobi_browse';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'header_mobi';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'footer_mobi';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'mobi_index_index';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'mobi_index_userhome';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'mobi_index_profile';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'mobi_event_profile';
DELETE FROM `engine4_core_pages` WHERE `engine4_core_pages`.`name` = 'mobi_group_profile';
DELETE FROM `engine4_core_content` WHERE `page_id` NOT IN (SELECT `page_id` FROM `engine4_core_pages`);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_redirection', 'core', 'Redirection Settings', '', '{"route":"core_admin_settings","action":"redirection"}', 'core_admin_main_settings', '', 15);
