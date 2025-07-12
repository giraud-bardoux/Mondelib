DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_social_site_googleplus";

ALTER TABLE `engine4_core_search` ADD `approved` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE `engine4_core_mailtemplates` ADD `is_admin` TINYINT(1) NOT NULL DEFAULT '0';

-- --------------------------------------------------------

--
-- Table structure for table `engine4_core_reasons`
--

DROP TABLE IF EXISTS `engine4_core_tickets`;
CREATE TABLE IF NOT EXISTS `engine4_core_tickets` (
  `ticket_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `description` text NULL DEFAULT NULL,
  `resource_type` varchar(32) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL default 'Open',
  `creation_date` datetime NOT NULL,
  `lastreply_date` datetime NOT NULL,
  `category_id` INT(11) NOT NULL DEFAULT '0',
  `subcat_id` INT(11) NOT NULL DEFAULT '0',
  `subsubcat_id` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `subcat_id` (`subcat_id`),
  KEY `subsubcat_id` (`subsubcat_id`),
  KEY `status` (`status`),
  KEY `resource_type` (`resource_type`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

DROP TABLE IF EXISTS `engine4_core_ticketreplies`;
CREATE TABLE IF NOT EXISTS `engine4_core_ticketreplies` (
  `ticketreply_id` int(11) NOT NULL auto_increment,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NULL DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY  (`ticketreply_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_main_manage_tickets', 'core', 'Support Inbox', '', '{"route":"admin_default","module":"core","controller":"support"}', 'core_admin_main_manage', '', 12), ('core_admin_manage_tickets', 'core', 'Support Inbox', '', '{"route":"admin_default","module":"core","controller":"support"}', 'core_admin_main_manage_tickets', '', 1), ('core_admin_manage_categories', 'core', 'Categories', '', '{"route":"admin_default","module":"core","controller":"support", "action": "categories"}', 'core_admin_main_manage_tickets', '', 2);


DROP TABLE IF EXISTS `engine4_core_categories`;
CREATE TABLE `engine4_core_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) NOT NULL,
  `subcat_id` INT(11) NOT NULL DEFAULT '0',
  `subsubcat_id` INT(11) NOT NULL DEFAULT '0',
  `order` INT(11) NOT NULL DEFAULT '0',
  `type` varchar(128) NOT NULL DEFAULT 'tickets',
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`, `category_name`),
  KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_mini_friends";
DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_mini_settings";
DELETE FROM engine4_core_menuitems WHERE `engine4_core_menuitems`.`name` = "core_mini_admin";

UPDATE `engine4_core_menuitems` SET `order` = '1' WHERE `engine4_core_menuitems`.`name` = 'core_mini_update';
UPDATE `engine4_core_menuitems` SET `order` = '2' WHERE `engine4_core_menuitems`.`name` = 'core_mini_messages';
UPDATE `engine4_core_menuitems` SET `order` = '3' WHERE `engine4_core_menuitems`.`name` = 'core_mini_profile';

ALTER TABLE `engine4_core_search` DROP PRIMARY KEY;
ALTER TABLE `engine4_core_search` ADD `search_id` INT(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`search_id`);
ALTER TABLE `engine4_core_search` ADD UNIQUE(`type`, `id`);


INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`) VALUES
('core_minimenuquick', 'standard', 'Mini Menu Quick Links Menu');

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_minimenu_edit', 'core', 'Edit Profile', 'User_Plugin_Menus', '', 'core_minimenuquick', '', 1),
('core_minimenu_settings', 'core', 'Account Settings', '', '{"route":"user_extended","module":"user","controller":"settings","action":"general", "icon":"fas fa-cog"}', 'core_minimenuquick', '', 2);

ALTER TABLE `engine4_core_categories` ADD INDEX(`subcat_id`);
ALTER TABLE `engine4_core_categories` ADD INDEX(`subsubcat_id`);
ALTER TABLE `engine4_core_categories` ADD INDEX(`order`);
ALTER TABLE `engine4_core_categories` ADD INDEX(`type`);
ALTER TABLE `engine4_core_comments` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_core_comments` ADD INDEX(`like_count`);
ALTER TABLE `engine4_core_files` ADD INDEX(`name`);
ALTER TABLE `engine4_core_files` ADD INDEX(`storage_file_id`);
ALTER TABLE `engine4_core_files` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_core_languages` ADD INDEX(`code`);
ALTER TABLE `engine4_core_languages` ADD INDEX(`enabled`);
ALTER TABLE `engine4_core_languages` ADD INDEX(`order`);
ALTER TABLE `engine4_core_likes` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_core_search` ADD INDEX(`approved`);
ALTER TABLE `engine4_core_ticketreplies` ADD INDEX(`ticket_id`);
ALTER TABLE `engine4_core_ticketreplies` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_core_tickets` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_core_tickets` ADD INDEX(`lastreply_date`);
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"core","controller":"manage-packages","action":"enabled"}' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_plugins";

ALTER TABLE `engine4_core_search` DROP INDEX `LOOKUP`;
ALTER TABLE `engine4_core_search` CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES 
('core_minimenu_supportinbox', 'core', 'Support Inbox', '', '{"route":"user_support","module":"user","controller":"support","action":"index", "icon":"fas fa-headset"}', 'core_minimenuquick', '', 3);

ALTER TABLE `engine4_core_search` ADD `username` VARCHAR(255) NULL DEFAULT NULL;
