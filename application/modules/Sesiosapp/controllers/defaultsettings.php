<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: defaultsettings.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


$db = Zend_Db_Table_Abstract::getDefaultAdapter();
$defaultConstants = Engine_Api::_()->sesiosapp()->themeConstants();
//insert theme constans in table
for($i = 1;$i<7;$i++){
  if($i == 1){
     $values = Engine_Api::_()->sesiosapp()->themeOneConstants();
  }else if($i == 2){
     $values = Engine_Api::_()->sesiosapp()->themeTwoConstants();
  }else if($i == 3){
     $values = Engine_Api::_()->sesiosapp()->themeThreeConstants();
  }else if($i == 4){
     $values = Engine_Api::_()->sesiosapp()->themeFourConstants();
  }else if($i == 5){
     $values = Engine_Api::_()->sesiosapp()->themeFiveConstants();
  }else if($i == 6){
     $values = Engine_Api::_()->sesiosapp()->themeSixConstants();
  }
  foreach($defaultConstants as $key=>$value){
    if(!empty($values[$key]))
      $valueConstant = $values[$key];
    else
      $valueConstant = "";
    $db->query("INSERT IGNORE INTO engine4_sesiosapp_customthemes (column_key,is_custom,theme_id,value) VALUES ('".$value."','0','".$i."','".$valueConstant."')");
  }
}
Engine_Api::_()->getApi('settings', 'core')->getSetting('sesiosapptheme.color',1);
//create custon theme
$db->query("INSERT IGNORE INTO engine4_sesiosapp_customthemes (column_key,is_custom,theme_id,value) SELECT column_key,1,theme_id,value FROM engine4_sesiosapp_customthemes");

$db->query("UPDATE `engine4_core_menuitems` SET `params` = '{\"uri\":\"https:\\/\\/www.socialenginesolutions.com\\/ios-mobile-app-submission-details\\/\",\"icon\":\"\",\"target\":\"_blank\",\"enabled\":\"1\"}' WHERE `engine4_core_menuitems`.`name` = 'sesiosapp_admin_main_appsetup';");

$sesandroidapp_slides_table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesandroidapp_slides\'')->fetch();
if($sesandroidapp_slides_table_exist) {
  $type = $db->query('SHOW COLUMNS FROM engine4_sesandroidapp_slides LIKE \'type\'')->fetch();
  if (empty($type)) {
    $db->query("ALTER TABLE `engine4_sesiosapp_slides` ADD `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0=>photo/1=>video'");
  }

  $video_id = $db->query('SHOW COLUMNS FROM engine4_sesandroidapp_slides LIKE \'video_id\'')->fetch();
  if (empty($video_id)) {
    $db->query("ALTER TABLE `engine4_sesiosapp_slides` ADD `video_id` INT(11) NOT NULL DEFAULT '0';");
  }
}

$db->query("CREATE TABLE IF NOT EXISTS `engine4_sesiosapp_graphics` (
  `graphic_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `title_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FFFFFF',
  `description_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FFFFFF',
  `background_color` VARCHAR(255) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `file_id` int(11) DEFAULT '0',
  `order` int(11) DEFAULT '0',
  `creation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`graphic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
$db->query("INSERT IGNORE INTO `engine4_core_menuitems`(`name`, `module`, `label`, `params`, `menu`,  `enabled`,`order`) VALUES ('sesiosapp_admin_main_graphic','sesiosapp','Graphic Assets','{\"route\":\"admin_default\",\"module\":\"sesiosapp\",\"controller\":\"graphic\"}','sesiosapp_admin_main','1','5');");
$db->query("INSERT IGNORE INTO `engine4_core_menuitems`(`name`, `module`, `label`, `params`, `menu`,  `enabled`,`order`) VALUES ('sesiosapp_admin_main_background','sesiosapp','Background Images','{\"route\":\"admin_default\",\"module\":\"sesiosapp\",\"controller\":\"settings\",\"action\":\"background\"}','sesiosapp_admin_main','1','5');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Groups','Sesgroup','1','1','21','0','core_main_sesgroup','1','0','0','sesgroup','1.5');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Pages','Sespage','1','1','22','0','core_main_sespage','1','0','0','sespage','1.5');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Events','Sesevent','1','1','23','0','core_main_sesevent','1','0','0','sesevent','1.5');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Businesses','Sesbusiness','1','1','27','0','core_main_sesbusiness','1','0','0','sesbusiness','1.5');");

$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES 
('Members','user','1','1','1','0','core_main_members','1','0','0','user','0'),
('Groups','group','1','1','4','0','core_main_group','1','0','0','group','0'),
('Blogs','blog','1','1','5','0','core_main_blog','1','0','0','blog','0'),
('Classifieds','classified','1','1','6','0','core_main_classified','1','0','0','classified','0'),
('Events','event','1','1','7','0','core_main_event','1','0','0','event','0'),
('Music','music','1','1','8','0','core_main_music','1','0','0','music','0'),
('Polls','poll','1','1','9','0','core_main_poll','1','0','0','poll','0'),
('Forums','Forum','1','1','54','0','core_main_forum','1','0','0','forum','0');");

$db->query('ALTER TABLE `engine4_activity_notificationtypes` ADD `sesios_enable_pushnotification` TINYINT(1) NOT NULL DEFAULT "1";');
