INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Storage', 'core', 'Core_Plugin_Task_Storage', 20);
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ('core_admin_main_settings_location', 'core', 'Location Settings', '', '{"route":"core_admin_settings","action":"location"}', 'core_admin_main_settings', '', 17);

--
-- Dumping data for table `engine4_core_locations`
--

DROP TABLE IF EXISTS `engine4_core_locations`;
CREATE TABLE IF NOT EXISTS `engine4_core_locations` (
  `location_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `resource_id` INT( 11 ) NOT NULL ,
  `resource_type` VARCHAR( 65 ) NOT NULL DEFAULT 'activity_action',
  `lat` VARCHAR(128) NULL DEFAULT NULL,
  `lng` VARCHAR(128) NULL DEFAULT NULL,
  `venue` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `address2` TEXT NULL,
  `city` VARCHAR(255) NULL,
  `state` VARCHAR(255) NULL,
  `zip` VARCHAR(255) NULL,
  `country` VARCHAR(255) NULL,
  `modified_date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniqueKey` (`resource_id`,`resource_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `engine4_core_recentlyviewitems`;
CREATE TABLE IF NOT EXISTS  `engine4_core_recentlyviewitems` (
  `recentlyviewed_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `resource_id` INT NOT NULL ,
  `resource_type` VARCHAR(128) NOT NULL DEFAULT "album",
  `owner_id` INT NOT NULL ,
  `creation_date` DATETIME NOT NULL,
  UNIQUE KEY `uniqueKey` (`resource_id`,`resource_type`, `owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `engine4_core_favourites`;
CREATE TABLE IF NOT EXISTS `engine4_core_favourites` (
  `favourite_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `resource_type` varchar(128) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `creation_date` DATETIME NOT NULL,
  PRIMARY KEY (`favourite_id`),
  KEY (`user_id`,`resource_type`,`resource_id`),
  KEY (`resource_type`,`resource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `engine4_core_integratemodules`;
CREATE TABLE IF NOT EXISTS `engine4_core_integratemodules` (
  `integratemodule_id` int(11) unsigned NOT NULL auto_increment,
  `module_name` varchar(128) NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `content_type` varchar(128) NOT NULL,
  `content_id` varchar(128) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`integratemodule_id`),
  UNIQUE KEY `content_type` (`content_type`,`content_id`),
  KEY `module_name` (`module_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

INSERT IGNORE INTO `engine4_core_integratemodules` (`module_name`, `module_title`, `content_type`, `content_id`, `enabled`) VALUES 
('album', 'Albums', 'album', 'album_id', 1),
('blog', 'Blogs', 'blog', 'blog_id', 1),
('bizlist', 'Business', 'bizlist', 'bizlist_id', 1),
('classified', 'Classifieds', 'classified', 'classified_id', 1),
('employment', 'Employment', 'employment', 'employment_id', 1),
('event', 'Events', 'event', 'event_id', 1),
('group', 'Groups', 'group', 'group_id', 1),
('music', 'Music', 'music_albums', 'album_id', 1),
('poll', 'Polls', 'poll', 'poll_id', 1),
('travel', 'Travel', 'travel', 'travel_id', 1),
('video', 'Videos', 'video', 'video_id', 1);

DROP TABLE IF EXISTS `engine4_core_ratings`;
CREATE TABLE IF NOT EXISTS `engine4_core_ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(9) unsigned NOT NULL,
  `resource_id` int(11) NOT NULL,
  `resource_type` varchar(128) NOT NULL,
  `rating` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `resource_id` (`resource_id`,`resource_type`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"core","controller":"support", "action":"settings"}' WHERE `engine4_core_menuitems`.`name` = "core_admin_main_manage_tickets";

UPDATE `engine4_core_menuitems` SET `order` = '2' WHERE `engine4_core_menuitems`.`name` = "core_admin_manage_tickets";
UPDATE `engine4_core_menuitems` SET `order` = '3' WHERE `engine4_core_menuitems`.`name` = "core_admin_manage_categories";

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES  
('core_admin_manage_settings', 'core', 'Global Settings', '', '{"route":"admin_default","module":"core","controller":"support", "action":"settings"}', 'core_admin_main_manage_tickets', '', 1);

UPDATE `engine4_core_menuitems` SET `plugin` = 'User_Plugin_Menus' WHERE `engine4_core_menuitems`.`name` = "core_minimenu_supportinbox";

UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","controller":"files","action":"settings"}' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_layout_files';

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES  
('core_admin_main_layout_files_settings', 'core', 'Settings', '', '{"route":"admin_default","controller":"files", "action":"settings"}', 'core_admin_main_layout_files', '', 1),
('core_admin_main_layout_files_manager', 'core', 'File & Media Manager', '', '{"route":"admin_default","controller":"files"}', 'core_admin_main_layout_files', '', 2);