<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: defaultsettings.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


$db = Zend_Db_Table_Abstract::getDefaultAdapter();
$defaultConstants = Engine_Api::_()->sesandroidapp()->themeConstants();
//insert theme constans in table
for($i = 1;$i<7;$i++){
  if($i == 1){
     $values = Engine_Api::_()->sesandroidapp()->themeOneConstants();
  }else if($i == 2){
     $values = Engine_Api::_()->sesandroidapp()->themeTwoConstants();
  }else if($i == 3){
     $values = Engine_Api::_()->sesandroidapp()->themeThreeConstants();
  }else if($i == 4){
     $values = Engine_Api::_()->sesandroidapp()->themeFourConstants();
  }else if($i == 5){
     $values = Engine_Api::_()->sesandroidapp()->themeFiveConstants();
  }else if($i == 6){
     $values = Engine_Api::_()->sesandroidapp()->themeSixConstants();
  }
  foreach($defaultConstants as $key=>$value){
    if(!empty($values[$key]))
      $valueConstant = $values[$key];
    else
      $valueConstant = "";
    $db->query("INSERT IGNORE INTO engine4_sesandroidapp_customthemes (column_key,is_custom,theme_id,value) VALUES ('".$value."','0','".$i."','".$valueConstant."')");
  }
}
Engine_Api::_()->getApi('settings', 'core')->getSetting('sesandroidapptheme.color',1);
//create custon theme
$db->query("INSERT IGNORE INTO engine4_sesandroidapp_customthemes (column_key,is_custom,theme_id,value) SELECT column_key,1,theme_id,value FROM engine4_sesandroidapp_customthemes");

$db->query("INSERT IGNORE INTO `engine4_core_menuitems` (`id`, `name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES (NULL, 'sesandroidapp_admin_main_background', 'sesandroidapp', 'Background Images', '', '{\"route\":\"admin_default\",\"module\":\"sesandroidapp\",\"controller\":\"settings\",\"action\":\"background\"}', 'sesandroidapp_admin_main', '', '1', '0', '6');");
$db->query("DELETE FROM engine4_core_menuitems WHERE name='sesandroidapp_admin_main_appsetup';");

$sesandroidapp_slides_table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesandroidapp_slides\'')->fetch();
if($sesandroidapp_slides_table_exist) {
  $type = $db->query('SHOW COLUMNS FROM engine4_sesandroidapp_slides LIKE \'type\'')->fetch();
  if (empty($type)) {
    $db->query("ALTER TABLE `engine4_sesandroidapp_slides` ADD `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0=>photo/1=>video'");
  }

  $video_id = $db->query('SHOW COLUMNS FROM engine4_sesandroidapp_slides LIKE \'video_id\'')->fetch();
  if (empty($video_id)) {
    $db->query("ALTER TABLE `engine4_sesandroidapp_slides` ADD `video_id` INT(11) NOT NULL DEFAULT '0';");
  }
}

$db->query("CREATE TABLE IF NOT EXISTS `engine4_sesandroidapp_graphics` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$db->query("INSERT IGNORE INTO `engine4_core_menuitems`(`name`, `module`, `label`, `params`, `menu`,  `enabled`,`order`) VALUES ('sesandroidapp_admin_main_graphic','sesandroidapp','Graphic Assets','{\"route\":\"admin_default\",\"module\":\"sesandroidapp\",\"controller\":\"graphic\"}','sesandroidapp_admin_main','1','5');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Groups','Sesgroup','1','1','21','0','core_main_sesgroup','2','0','0','sesgroup','2.3');
");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Pages','Sespage','1','1','22','0','core_main_sespage','2','0','0','sespage','2.3');
");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Events','Sesevent','1','1','23','0','core_main_sesevent','2','0','0','sesevent','2.3');
");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Contests','Sescontest','1','1','24','0','core_main_sescontest','2','0','0','sescontest','2.3');
");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Businesses','Sesbusiness','1','1','27','0','core_main_sesbusiness','2','0','0','sesbusiness','2.5');
;
");

$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Q&A','Sesqa','1','1','0','core_main_sesqa','2','0','0','sesqa','2.7');");
$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES ('Activity Feeds','sesmusicapp','1','1','0','core_main_sesmusicapp','2','0','0','sesmusicapp','2.8')
 ");

