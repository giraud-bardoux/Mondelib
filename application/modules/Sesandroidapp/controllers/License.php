<?php


if (!$this->getRequest()->isPost()) {
  return;
}

if (!$form->isValid($this->getRequest()->getPost())) {
  return;
}

if ($this->getRequest()->isPost()) {

    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesandroidapp.pluginactivated')) {
    
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      
      $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
      ("sesandroidapp_admin_main_menu", "sesandroidapp", "Dashboard Menu Items", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"menu"}\', "sesandroidapp_admin_main", "", 2),
      ("sesandroidapp_admin_main_slideshow", "sesandroidapp", "Welcome Slideshow", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"slideshow"}\', "sesandroidapp_admin_main", "", 4),
      ("sesandroidapp_admin_main_pushnoti", "sesandroidapp", "Push Notifications", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"pushnotification","action":"manage"}\', "sesandroidapp_admin_main", "", 3),
      ("sesandroidapp_admin_main_managepushnoti", "sesandroidapp", "Manage Push Notifications", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"pushnotification","action":"manage"}\', "sesandroidapp_admin_main_pushnoti", "", 1),
      ("sesandroidapp_admin_main_pushnotisettings", "sesandroidapp", "Push Notifications Settings", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"pushnotification","action":"settings"}\', "sesandroidapp_admin_main_pushnoti", "", 2),
      ("sesandroidapp_admin_main_subscriber", "sesandroidapp", "Manage Subscribers", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"subscribers"}\', "sesandroidapp_admin_main", "", 4),
       ("sesandroidapp_admin_main_styling", "sesandroidapp", "Color Schemes", "", \'{"route":"admin_default","module":"sesandroidapp","controller":"theme"}\', "sesandroidapp_admin_main", "", 4),
      ("sesandroidapp_admin_main_restapi", "sesandroidapp", "REST APIs", "", \'{"route":"admin_default","module":"sesapi","controller":"settings","target":"_blank"}\', "sesandroidapp_admin_main", "", 6);');

      $db->query('DROP TABLE IF EXISTS `engine4_sesandroidapp_pushnotifications`;');
      $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesandroidapp_pushnotifications` (
        `pushnotification_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
        `description` text COLLATE utf8mb4_unicode_ci,
        `criteria` varchar(244) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `param` text COLLATE utf8mb4_unicode_ci,
        `sent` tinyint(1) DEFAULT "0",
        `creation_date` datetime DEFAULT NULL,
        PRIMARY KEY (`pushnotification_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

      $db->query('DROP TABLE IF EXISTS `engine4_sesandroidapp_slides`;');
      $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesandroidapp_slides` (
        `slide_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
        `description` text COLLATE utf8mb4_unicode_ci,
        `status` TINYINT(1) COLLATE utf8mb4_unicode_ci DEFAULT "1",
        `file_id` INT(11) DEFAULT "0",
        `order` INT(11) DEFAULT "0",
        `creation_date` datetime DEFAULT NULL,
        PRIMARY KEY (`slide_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

      $activity_table_exist = $db->query('SHOW TABLES LIKE \'engine4_activity_notificationtypes\'')->fetch();
      if($activity_table_exist) {
        $sesandoidapp_enable_pushnotification = $db->query('SHOW COLUMNS FROM engine4_activity_notificationtypes LIKE \'sesandoidapp_enable_pushnotification\'')->fetch();
        if (empty($sesandoidapp_enable_pushnotification)) {
          $db->query('ALTER TABLE `engine4_activity_notificationtypes` ADD `sesandoidapp_enable_pushnotification` TINYINT(1) NOT NULL DEFAULT "1";');
        }
      }

      $db->query('DROP TABLE IF EXISTS `engine4_sesandroidapp_themes`;');
      $db->query('CREATE TABLE `engine4_sesandroidapp_themes` (
        `theme_id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        PRIMARY KEY (`theme_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
      $db->query("INSERT INTO `engine4_sesandroidapp_themes` (`theme_id`, `name`) VALUES
        (1, 'Theme 1'),
        (2, 'Theme 2'),
        (3, 'Theme 3'),
        (4, 'Theme 4'),
        (5, 'Theme 5'),
        (6, 'Theme 6');");
      $db->query('DROP TABLE IF EXISTS `engine4_sesandroidapp_customthemes`;');
      $db->query('CREATE TABLE `engine4_sesandroidapp_customthemes` (
        `customtheme_id` int(11) NOT NULL AUTO_INCREMENT,
        `value` varchar(255) NOT NULL,
        `column_key` varchar(255) NOT NULL,
        `theme_id` int(11) NOT NULL,
        `is_custom` TINYINT(1) NOT NULL DEFAULT "0" ,
        PRIMARY KEY (`customtheme_id`),
        UNIQUE KEY `UNIQUEKEY` (`column_key`,`theme_id`,`is_custom`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');


       $db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `is_delete`, `device`, `visibility`, `module_name`) VALUES
      ( "Search", "Core", 1, 1, 1, 0, "", "core_main_search", 0, 2, 0, NULL),
      ( "FAVOURITES", "", 0, 1, 3, 0, "", "core_fav", 0, 2, 1, NULL),
      ( "Messages", "Core", 1, 1, 4, 0, "", "core_mini_messages", 0, 2, 1, NULL),
      ( "Notifications", "Core", 1, 1, 5, 0, "", "core_mini_notifications", 0, 2, 1, NULL),
      ( "Friend Requests", "Core", 1, 1, 6, 0, "", "core_mini_friends", 0, 2, 1, NULL),
      ( "APPS", "", 0, 1, 7, 0, "", "core_fav", 0, 2, 0, NULL),
      ( "Members", "Member", 1, 1, 12, 0, "", "core_main_members", 0, 2, 0, "sesmember"),
      ( "Album", "Album", 1, 1, 8, 0, "", "core_main_album", 0, 2, 0, "sesalbum"),
      ( "Videos", "Video", 1, 1, 9, 0, "", "core_main_video", 0, 2, 0, "sesvideo"),
      ("Blog", "Blog", 1, 1, 13, 0, "", "core_main_blog", 0, 2, 0, "sesblog"),
      ("Music", "Music", 1, 1, 14, 0, "", "core_main_music", 0, 2, 0, "sesmusic"),
      ("ACCOUNT SETTINGS", "", 0, 1, 17, 0, "", "core_fav", 0, 2, 1, NULL),
      ("Settings", "Core", 1, 1, 18, 0, "", "core_main_settings", 0, 2, 1, NULL),
      ("HELP & MORE", "", 0, 1, 19, 0, "", "core_fav", 0, 2, 0, NULL),
      ("Contact Us", "Core", 1, 1, 22, 0, "", "core_footer_contact", 0, 2, 0, NULL),
      ( "Privacy", "Core", 1, 1, 20, 0, "", "core_footer_privacy", 0, 2, 0, NULL),
      ( "Terms of Service", "Core", 1, 1, 21, 0, "", "core_footer_terms", 0, 2, 0, NULL),
      ( "Rate Us", "Sesapi", 1, 1, 23, 0, "", "core_main_sesapi_rate", 0, 2, 0, "sesapi"),
      ( "Sign out", "Core", 1, 1, 24, 0, "", "core_mini_auth", 0, 2, 1, NULL),
      ("Video Channels", "Video", 1, 1, 10, 0, "", "core_main_video_chanel", 0, 2, 0, "sesvideo"),
      ("Video Playlists", "Video", 1, 1, 11, 0, "", "core_main_video_playlist", 0, 2, 0, "sesvideo"),
      ("Music Playlists", "Music", 1, 1, 16, 0, "", "core_main_music_playlist", 0, 2, 0, "sesmusic"),
      ("Songs", "Music", 1, 1, 15, 0, "", "core_main_music_song", 0, 2, 0, "sesmusic");');

      //insert icons
      $table = Engine_Api::_()->getDbTable('menus','sesapi');
      $res = $table->fetchAll($table->select()->where('device =?',2));
      foreach($res as $re){
        $name = strtolower(str_replace(' ','',$re['label']));
        $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "dashboardicons" . DIRECTORY_SEPARATOR;
        if (is_file($PathFile . $name.'.png') && $re['type']){
          $icon = $this->setDashboardIcons($PathFile.$name.'.png' , $re->getIdentity());
          $re->file_id = $icon;
          $re->save();
        }
      }   
      include_once APPLICATION_PATH . "/application/modules/Sesandroidapp/controllers/defaultsettings.php";
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesandroidapp.pluginactivated', 1);
    }
		$error = 1;
}
