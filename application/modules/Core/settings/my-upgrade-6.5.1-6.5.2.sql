
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES 
('core_admin_main_settings_seo', 'core', 'SEO Settings', '', '{"route":"core_admin_seo","action":"index"}', 'core_admin_main_settings', '', 17),
('core_admin_main_settings_seo_settings', 'core', 'SEO Settings', '', '{"route":"core_admin_seo","action":"index"}', 'core_admin_main_settings_seo', '', 1),
('core_admin_main_settings_seo_managemetakeywords', 'core', 'Meta Tags Settings', '', '{"route":"core_admin_seo","action":"managemetakeywords"}', 'core_admin_main_settings_seo', '', 2),
('core_admin_main_settings_seo_roboto', 'core', 'Robots txt Editor', '', '{"route":"core_admin_seo","action":"roboto"}', 'core_admin_main_settings_seo', '', 3),
('core_admin_main_settings_seo_opensearch', 'core', 'Open Search Editor', '', '{"route":"core_admin_seo","action":"opensearch"}', 'core_admin_main_settings_seo', '', 4),
('core_admin_main_settings_seo_schemamarkup', 'core', 'Schema Markup', '', '{"route":"core_admin_seo","action":"schema-markup"}', 'core_admin_main_settings_seo', '', 5),
('core_admin_main_settings_seo_sitemap', 'core', 'Sitemap', '', '{"route":"core_admin_seo","action":"sitemap"}', 'core_admin_main_settings_seo', '', 6);

ALTER TABLE `engine4_core_pages` ADD `meta_tags` TEXT NULL DEFAULT NULL AFTER `search`, ADD `roboto_tags` TINYINT(1) NOT NULL DEFAULT '1' AFTER `meta_tags`, ADD `meta_image` VARCHAR(255) NULL DEFAULT NULL AFTER `roboto_tags`;

DROP TABLE IF EXISTS `engine4_core_sitemaps`;
CREATE TABLE IF NOT EXISTS `engine4_core_sitemaps` (
  `sitemap_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(64) NOT NULL,
  `title` varchar(64) NOT NULL,
  `frequency` VARCHAR(32) NOT NULL,
  `priority` VARCHAR(32) NOT NULL,
  `limit` INT(11) DEFAULT "0",
  `modified_date` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`sitemap_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

INSERT IGNORE INTO `engine4_core_sitemaps` (`resource_type`, `title`, `frequency`, `priority`, `limit`, `enabled`, `modified_date`) VALUES ("menu_urls", "Menu Urls", "always", "0.5", "0", "1", "");
