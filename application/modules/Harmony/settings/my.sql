/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: my.sql 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("core_admin_main_plugins_harmony", "harmony", "Harmony Theme", "", '{"route":"admin_default","module":"harmony","controller":"settings"}', "core_admin_main_plugins", "", 999);

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
("core_admin_main_harmony", "harmony", "Harmony Theme", "", '{"route":"admin_default","module":"harmony","controller":"settings"}', "core_admin_main", "", 999),
("harmony_admin_main_settings", "harmony", "Global Settings", "", '{"route":"admin_default","module":"harmony","controller":"settings"}', "harmony_admin_main", "", 1),
("harmony_admin_main_footer", "harmony", "Footer Settings", "", '{"route":"admin_default","module":"harmony","controller":"settings", "action":"footer"}', "harmony_admin_main", "", 2),
("harmony_admin_main_styling", "harmony", "Color Schemes", "", '{"route":"admin_default","module":"harmony","controller":"settings", "action":"styling"}', "harmony_admin_main", "", 3),
("harmony_admin_main_managefonts", "harmony", "Manage Fonts", "", '{"route":"admin_default","module":"harmony","controller":"settings", "action":"manage-fonts"}', "harmony_admin_main", "", 4),
("harmony_admin_main_customcss", "harmony", "Custom CSS", "", '{"route":"admin_default","module":"harmony","controller":"custom-theme", "action":"index"}', "harmony_admin_main", "", 5);

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`, `order`) VALUES
("harmony_quicklinks_footer", "standard", "Harmony Theme - Footer Quicklinks", 1),
("harmony_aboutlinks_footer", "standard", "Harmony Theme - Footer Explore Links", 2);

DROP TABLE IF EXISTS `engine4_harmony_customthemes`;
CREATE TABLE IF NOT EXISTS `engine4_harmony_customthemes` (
  `customtheme_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `value` varchar(32) NOT NULL,
  `column_key` varchar(128) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `default` TINYINT(1) NOT NULL DEFAULT "0" ,
  PRIMARY KEY (`customtheme_id`),
  UNIQUE KEY `UNIQUEKEY` (`column_key`,`theme_id`,`default`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;


INSERT IGNORE INTO `engine4_harmony_customthemes` (`name`, `value`, `column_key`, `theme_id`, `default`) VALUES
("Theme - 1", "1", "theme_color", 1, 0),
("Theme - 1", "1", "custom_theme_color", 1, 0),
("Theme - 1", "#ffffff", "harmony_header_background_color", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_mainmenu_background_color", 1, 0),
("Theme - 1", "#333333", "harmony_mainmenu_links_color", 1, 0),
("Theme - 1", "#000000", "harmony_mainmenu_links_hover_color", 1, 0),
("Theme - 1", "#F6F6F6", "harmony_minimenu_search_background_color", 1, 0),
("Theme - 1", "#1E293B", "harmony_minimenu_search_font_color", 1, 0),
("Theme - 1", "#FDFDFD", "harmony_footer_background_color", 1, 0),
("Theme - 1", "#1F1F1F", "harmony_footer_font_color", 1, 0),
("Theme - 1", "#4f4f4f", "harmony_footer_links_color", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_footer_border_color", 1, 0),
("Theme - 1", "#208ED3", "harmony_theme_color", 1, 0),
("Theme - 1", "#EFEDE9", "harmony_body_background_color", 1, 0),
("Theme - 1", "#1E293B", "harmony_font_color", 1, 0),
("Theme - 1", "#64748B", "harmony_font_color_light", 1, 0),
("Theme - 1", "#208ed3", "harmony_links_color", 1, 0),
("Theme - 1", "#208ed3", "harmony_links_hover_color", 1, 0),
("Theme - 1", "#1E293B", "harmony_headline_color", 1, 0),
("Theme - 1", "#d5d5d5", "harmony_border_color", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_box_background_color", 1, 0),
("Theme - 1", "#F5F5F5", "harmony_box_background_color_alt", 1, 0),
("Theme - 1", "#1F1F1F", "harmony_form_label_color", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_input_background_color", 1, 0),
("Theme - 1", "#1F1F1F", "harmony_input_font_color", 1, 0),
("Theme - 1", "#CBD5E1", "harmony_input_border_color", 1, 0),
("Theme - 1", "#208ED3", "harmony_button_background_color", 1, 0),
("Theme - 1", "#208ED3", "harmony_button_background_color_hover", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_button_font_color", 1, 0),
("Theme - 1", "#FFFFFF", "harmony_button_font_color_hover", 1, 0),
("Theme - 1", "#208ED3", "harmony_button_border_color", 1, 0),
("Theme - 1", "#208ED3", "harmony_button_border_color_hover", 1, 0),
("Theme - 1", "#f2f2f2", "harmony_secondary_button_background_color", 1, 0),
("Theme - 1", "#e5e5e5", "harmony_secondary_button_background_color_hover", 1, 0),
("Theme - 1", "#212529", "harmony_secondary_button_font_color", 1, 0),
("Theme - 1", "#212529", "harmony_secondary_button_font_color_hover", 1, 0),
("Theme - 1", "#E4E6EB", "harmony_secondary_button_border_color", 1, 0),
("Theme - 1", "#d8dadf", "harmony_secondary_button_border_color_hover", 1, 0),
("Theme - 1", "#FDFDFD", "harmony_comments_background_color", 1, 0),
("Theme - 2", "2", "theme_color", 2, 0),
("Theme - 2", "#ffffff", "harmony_header_background_color", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_mainmenu_background_color", 2, 0),
("Theme - 2", "#333333", "harmony_mainmenu_links_color", 2, 0),
("Theme - 2", "#000000", "harmony_mainmenu_links_hover_color", 2, 0),
("Theme - 2", "#F6F6F6", "harmony_minimenu_search_background_color", 2, 0),
("Theme - 2", "#1E293B", "harmony_minimenu_search_font_color", 2, 0),
("Theme - 2", "#FDFDFD", "harmony_footer_background_color", 2, 0),
("Theme - 2", "#1F1F1F", "harmony_footer_font_color", 2, 0),
("Theme - 2", "#4f4f4f", "harmony_footer_links_color", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_footer_border_color", 2, 0),
("Theme - 2", "#FF0083", "harmony_theme_color", 2, 0),
("Theme - 2", "#EFEDE9", "harmony_body_background_color", 2, 0),
("Theme - 2", "#1E293B", "harmony_font_color", 2, 0),
("Theme - 2", "#64748B", "harmony_font_color_light", 2, 0),
("Theme - 2", "#ff0083", "harmony_links_color", 2, 0),
("Theme - 2", "#db0071", "harmony_links_hover_color", 2, 0),
("Theme - 2", "#1E293B", "harmony_headline_color", 2, 0),
("Theme - 2", "#d5d5d5", "harmony_border_color", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_box_background_color", 2, 0),
("Theme - 2", "#F5F5F5", "harmony_box_background_color_alt", 2, 0),
("Theme - 2", "#1F1F1F", "harmony_form_label_color", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_input_background_color", 2, 0),
("Theme - 2", "#1F1F1F", "harmony_input_font_color", 2, 0),
("Theme - 2", "#CBD5E1", "harmony_input_border_color", 2, 0),
("Theme - 2", "#FF0083", "harmony_button_background_color", 2, 0),
("Theme - 2", "#DB0071", "harmony_button_background_color_hover", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_button_font_color", 2, 0),
("Theme - 2", "#FFFFFF", "harmony_button_font_color_hover", 2, 0),
("Theme - 2", "#FF0083", "harmony_button_border_color", 2, 0),
("Theme - 2", "#DB0071", "harmony_button_border_color_hover", 2, 0),
("Theme - 2", "#f2f2f2", "harmony_secondary_button_background_color", 2, 0),
("Theme - 2", "#e5e5e5", "harmony_secondary_button_background_color_hover", 2, 0),
("Theme - 2", "#212529", "harmony_secondary_button_font_color", 2, 0),
("Theme - 2", "#212529", "harmony_secondary_button_font_color_hover", 2, 0),
("Theme - 2", "#E4E6EB", "harmony_secondary_button_border_color", 2, 0),
("Theme - 2", "#d8dadf", "harmony_secondary_button_border_color_hover", 2, 0),
("Theme - 2", "#FDFDFD", "harmony_comments_background_color", 2, 0),
("Theme - 3", "3", "theme_color", 3, 0),
("Theme - 3", "3", "custom_theme_color", 3, 0),
("Theme - 3", "#333536", "harmony_header_background_color", 3, 0),
("Theme - 3", "#333536", "harmony_mainmenu_background_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_mainmenu_links_color", 3, 0),
("Theme - 3", "#208ED3", "harmony_mainmenu_links_hover_color", 3, 0),
("Theme - 3", "#1E1D1D ", "harmony_minimenu_search_background_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_minimenu_search_font_color", 3, 0),
("Theme - 3", "#1E1E1E", "harmony_footer_background_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_footer_font_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_footer_links_color", 3, 0),
("Theme - 3", "#6D6D6D", "harmony_footer_border_color", 3, 0),
("Theme - 3", "#208ED3", "harmony_theme_color", 3, 0),
("Theme - 3", "#1D1D1F", "harmony_body_background_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_font_color", 3, 0),
("Theme - 3", "#F1F1F1", "harmony_font_color_light", 3, 0),
("Theme - 3", "#208ed3", "harmony_links_color", 3, 0),
("Theme - 3", "#208ed3", "harmony_links_hover_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_headline_color", 3, 0),
("Theme - 3", "#6D6D6D", "harmony_border_color", 3, 0),
("Theme - 3", "#3A3A3A", "harmony_box_background_color", 3, 0),
("Theme - 3", "#2F2F2F", "harmony_box_background_color_alt", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_form_label_color", 3, 0),
("Theme - 3", "#3A3A3A", "harmony_input_background_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_input_font_color", 3, 0),
("Theme - 3", "#666666", "harmony_input_border_color", 3, 0),
("Theme - 3", "#208ED3", "harmony_button_background_color", 3, 0),
("Theme - 3", "#208ED3", "harmony_button_background_color_hover", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_button_font_color", 3, 0),
("Theme - 3", "#FFFFFF", "harmony_button_font_color_hover", 3, 0),
("Theme - 3", "#208ED3", "harmony_button_border_color", 3, 0),
("Theme - 3", "#208ED3", "harmony_button_border_color_hover", 3, 0),
("Theme - 3", "#343536", "harmony_secondary_button_background_color", 3, 0),
("Theme - 3", "#4c4d4e", "harmony_secondary_button_background_color_hover", 3, 0),
("Theme - 3", "#e4e6eb", "harmony_secondary_button_font_color", 3, 0),
("Theme - 3", "#dbdbdb", "harmony_secondary_button_font_color_hover", 3, 0),
("Theme - 3", "#d8dadf", "harmony_secondary_button_border_color", 3, 0),
("Theme - 3", "#FDFDFD", "harmony_secondary_button_border_color_hover", 3, 0),
("Theme - 3", "#404040", "harmony_comments_background_color", 3, 0);

UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fi fi-rr-bell"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_update";
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fi fi-rs-circle-user"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_profile";
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fi fi-rr-sign-in-alt"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_auth";
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fi fi-rr-user-add"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_signup";
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fi fi-rr-envelope"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_messages";
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"default","module":"payment","icon":"fi fi-rr-dollar"}' WHERE `engine4_core_menuitems`.`name` = "core_mini_currency";


INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('harmony.aboutlinksenable', '1'),
('harmony.body.fontfamily', 'Default Font'),
('harmony.body.fontsize', '0.85rem'),
('harmony.description', "At our community we believe in the power of connections. Our platform is more than just a social networking site; it\'s a vibrant community where individuals from diverse backgrounds come together to share, connect, and thrive.\r\nWe are dedicated to fostering creativity, building strong communities, and raising awareness on a global scale."),
('harmony.footer.enablelogo', '1'),
('harmony.googlebody.fontfamily', '\"ABeeZee\"'),
('harmony.googlebody.fontsize', '0.85rem'),
('harmony.googlefonts', '0'),
('harmony.googleheading.fontfamily', '\"ABeeZee\"'),
('harmony.googleheading.fontsize', '1.1rem'),
('harmony.googlemainmenu.fontfamily', '\"ABeeZee\"'),
('harmony.googlemainmenu.fontsize', '0.8rem'),
('harmony.googletab.fontfamily', '\"ABeeZee\"'),
('harmony.googletab.fontsize', '0.875rem'),
('harmony.headerloggedinoptions', 'a:4:{i:0;s:6:\"search\";i:1;s:8:\"miniMenu\";i:2;s:8:\"mainMenu\";i:3;s:4:\"logo\";}'),
('harmony.headernonloggedinoptions', 'a:4:{i:0;s:6:\"search\";i:1;s:8:\"miniMenu\";i:2;s:8:\"mainMenu\";i:3;s:4:\"logo\";}'),
('harmony.heading.fontfamily', 'Default Font'),
('harmony.heading.fontsize', '1.1rem'),
('harmony.helpenable', '1'),
('harmony.mainmenu.fontfamily', 'Default Font'),
('harmony.mainmenu.fontsize', '0.8rem'),
('harmony.quicklinksenable', '1'),
('harmony.rightcolhdingemail', 'info@abc.com'),
('harmony.rightcolhdinglocation', 'Los Angeles, USA'),
('harmony.rightcolhdingphone', '1234567890'),
('harmony.socialenable', '1'),
('harmony.tab.fontfamily', 'Default Font'),
('harmony.tab.fontsize', '0.875rem');
