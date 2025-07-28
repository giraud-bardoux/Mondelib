<?php

if (!$this->getRequest()->isPost()) {
  return;
}

if (!$form->isValid($this->getRequest()->getPost())) {
  return;
}

if ($this->getRequest()->isPost()) {

    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesapi.pluginactivated')) {
    
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      
      $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesapi_admin_main_documentation", "sesapi", "API Documentation", "", \'{"route":"admin_default","module":"sesapi","controller":"settings","action":"documentation"}\', "sesapi_admin_main", "", 2);');

      $db->query('DROP TABLE IF EXISTS `engine4_sesapi_menus`;');
      $db->query('CREATE TABLE `engine4_sesapi_menus` (
        `menu_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `label` varchar(255) NOT NULL,
        `module` varchar(255) DEFAULT NULL,
        `type` tinyint(1) NOT NULL DEFAULT "0",
        `status` tinyint(1) NOT NULL DEFAULT "1",
        `order` tinyint(1) NOT NULL DEFAULT "1",
        `file_id` int(11) NOT NULL DEFAULT "0",
        `url` varchar(1024) DEFAULT NULL,
        `class` varchar(255) DEFAULT NULL,
        `device` tinyint(1) NOT NULL DEFAULT "1",
        `is_delete` tinyint(1) NOT NULL DEFAULT "0",
        `visibility` tinyint(1) NOT NULL DEFAULT "0",
        `module_name` VARCHAR(45) NULL DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

      $db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`menu_id`, `label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `is_delete`, `device`, `visibility`, `module_name`) VALUES
      (1, "Search", "Core", 1, 1, 1, 0, "", "core_main_search", 0, 1, 0, NULL),
      (2, "FAVOURITES", "", 0, 1, 3, 0, "", "core_fav", 0, 1, 1, NULL),
      (3, "Messages", "Core", 1, 1, 4, 0, "", "core_mini_messages", 0, 1, 1, NULL),
      (4, "Notifications", "Core", 1, 1, 5, 0, "", "core_mini_notifications", 0, 1, 1, NULL),
      (5, "Friend Requests", "Core", 1, 1, 6, 0, "", "core_mini_friends", 0, 1, 1, NULL),
      (6, "APPS", "", 0, 1, 7, 0, "", "core_fav", 0, 1, 0, NULL),
      (7, "Members", "Member", 1, 1, 12, 0, "", "core_main_members", 0, 1, 0, "sesmember"),
      (8, "Album", "Album", 1, 1, 8, 0, "", "core_main_album", 0, 1, 0, "sesalbum"),
      (9, "Videos", "Video", 1, 1, 9, 0, "", "core_main_video", 0, 1, 0, "sesvideo"),
      (10, "Blog", "Blog", 1, 1, 13, 0, "", "core_main_blog", 0, 1, 0, "sesblog"),
      (11, "Music", "Music", 1, 1, 14, 0, "", "core_main_music", 0, 1, 0, "sesmusic"),
      (12, "ACCOUNT SETTINGS", "", 0, 1, 17, 0, "", "core_fav", 0, 1, 1, NULL),
      (13, "Settings", "Core", 1, 1, 18, 0, "", "core_main_settings", 0, 1, 1, NULL),
      (14, "HELP & MORE", "", 0, 1, 19, 0, "", "core_fav", 0, 1, 0, NULL),
      (15, "Contact Us", "Core", 1, 1, 22, 0, "", "core_footer_contact", 0, 1, 0, NULL),
      (16, "Privacy", "Core", 1, 1, 20, 0, "", "core_footer_privacy", 0, 1, 0, NULL),
      (17, "Terms of Service", "Core", 1, 1, 21, 0, "", "core_footer_terms", 0, 1, 0, NULL),
      (22, "Rate Us", "Sesapi", 1, 1, 23, 0, "", "core_main_sesapi_rate", 0, 1, 0, "sesapi"),
      (18, "Sign out", "Core", 1, 1, 24, 0, "", "core_mini_auth", 0, 1, 1, NULL),
      (19, "Video Channels", "Video", 1, 1, 10, 0, "", "core_main_video_chanel", 0, 1, 0, "sesvideo"),
      (20, "Video Playlists", "Video", 1, 1, 11, 0, "", "core_main_video_playlist", 0, 1, 0, "sesvideo"),
      (21, "Music Playlists", "Music", 1, 1, 16, 0, "", "core_main_music_playlist", 0, 1, 0, "sesmusic"),
      (22, "Songs", "Music", 1, 1, 15, 0, "", "core_main_music_song", 0, 1, 0, "sesmusic");');

      $db->query('DROP TABLE IF EXISTS `engine4_sesapi_aouthtokens`;');
      $db->query('CREATE TABLE `engine4_sesapi_aouthtokens` (
        `aouthtoken_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `token` varchar(255) NOT NULL,
        `user_id` int(11) NOT NULL,
        `platform` tinyint(1) NOT NULL DEFAULT "1",
        `revoked` tinyint(1) NOT NULL DEFAULT "0",
        `sessions` INT(11) NOT NULL DEFAULT "1",
        `creation_date` datetime DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

      $db->query('DROP TABLE IF EXISTS `engine4_sesapi_users`;');
      $db->query('CREATE TABLE `engine4_sesapi_users` (
        `user_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `device_uuid` varchar(255) NOT NULL,
         `token` varchar(255) NOT NULL,
         `platform` tinyint(1) NOT NULL DEFAULT "1",
        `device_id` tinyint(1) NOT NULL DEFAULT "1",
        `resource_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;');

      include_once APPLICATION_PATH . "/application/modules/Sesapi/controllers/defaultsettings.php";

      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesapi.pluginactivated', 1);
    }

		$error = 1;
}
