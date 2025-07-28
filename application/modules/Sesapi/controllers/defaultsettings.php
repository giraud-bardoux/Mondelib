<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: defaultsettings.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
$db = Zend_Db_Table_Abstract::getDefaultAdapter();

$sesapi_menus_table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesapi_menus\'')->fetch();
if($sesapi_menus_table_exist) {
  $version = $db->query('SHOW COLUMNS FROM engine4_sesapi_menus LIKE \'version\'')->fetch();
  if (empty($version)) {
    $db->query("ALTER TABLE `engine4_sesapi_menus` ADD `version` VARCHAR(45) NULL DEFAULT '0';");
  }
}

$db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `device`, `is_delete`, `visibility`, `module_name`) VALUES 
("Articles", "sesarticle", "1", "1", "13", "", "", "core_main_sesarticle", "1", "0", "0", "sesarticle"),
("Prayers", "Prayers", 1, 1, 14, 0, "", "core_main_sesprayer", 1, 0, 0, "sesprayer"),
("Thoughts", "Thoughts", 1, 1, 14, 0, "", "core_main_sesthought", 1, 0, 0, "sesthought"),
("Prayers", "Prayers", 1, 1, 14, 0, "", "core_main_sesprayer", 2, 0, 0, "sesprayer"),
("Thoughts", "Thoughts", 1, 1, 14, 0, "", "core_main_sesthought", 2, 0, 0, "seswishe");');

$db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES
("Wishes", "Wishes", 1, 1, 14, 0, "", "core_main_seswishe", 1, 0, 0, "seswishe","1.1"),
("Wishes", "Wishes", 1, 1, 14, 0, "", "core_main_seswishe", 2, 0, 0, "seswishe","1.2");');

//insert icons
$table = Engine_Api::_()->getDbTable('menus','sesapi');
$res = $table->fetchAll($table->select());
foreach($res as $re){
  $name = strtolower(str_replace(' ','',$re['label']));
  $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "dashboardicons" . DIRECTORY_SEPARATOR;
  if (is_file($PathFile . $name.'.png') && $re['type']){
    $icon = $this->setDashboardIcons($PathFile.$name.'.png' , $re->getIdentity());
    $re->file_id = $icon;
    $re->save();
  }
}

if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
  $activity_table_exist = $db->query('SHOW TABLES LIKE \'engine4_activity_filterlists\'')->fetch();
  if($activity_table_exist) {
    $file_id = $db->query('SHOW COLUMNS FROM engine4_activity_filterlists LIKE \'file_id\'')->fetch();
    if (empty($file_id)) {
      $db->query("ALTER TABLE `engine4_activity_filterlists` ADD `file_id` INT(11) NOT NULL DEFAULT '0';");
    }
  }
  //insert filter icons
  $table = Engine_Api::_()->getDbTable('filterlists','activity');
  $res = $table->fetchAll($table->select());
  foreach($res as $re){
    $name = ($re['filtertype']);
    $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "filter" . DIRECTORY_SEPARATOR;
    if (is_file($PathFile . $name.'.png')){
      $icon = $this->setDashboardIcons($PathFile.$name.'.png' , $re->getIdentity());
      $re->file_id = $icon;
      $re->save();
    }
  }
}

$db->query("DELETE FROM engine4_core_menuitems WHERE name='sesapi_admin_main_documentation';");

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`,`plugin` ,`params`, `menu`,`enabled`,`order`) VALUES
(\'sesapi_admin_main_androidapp\', \'sesapi\', \'Android  Mobile App\', \'Sesapi_Plugin_Menus::enableAndroidModule\',\'{"route":"admin_default","module":"sesandroidapp","controller":"settings","action":"index"}\', \'sesapi_admin_main\', 1,4)');


$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES  ('Businesses','Sesbusiness','1','1','27','0','core_main_sesbusiness','2','0','0','sesbusiness','2.5'),
('Groups','Sesgroup','1','1','21','0','core_main_sesgroup','2','0','0','sesgroup','2.3'),
('Pages','Sespage','1','1','22','0','core_main_sespage','2','0','0','sespage','2.3'),
('Events','Sesevent','1','1','23','0','core_main_sesevent','2','0','0','sesevent','2.3'),
('Contests','Sescontest','1','1','24','0','core_main_sescontest','2','0','0','sescontest','2.3'),
('Members','user','1','1','1','0','core_main_members','2','0','0','user','0'),
('Albums','album','1','1','2','0','core_main_album','2','0','0','album','0'),
('Albums','album','1','1','2','0','core_main_album','1','0','0','album','0'),
('Videos','video','1','1','3','0','core_main_video','2','0','0','video','0'),
('Videos','video','1','1','3','0','core_main_video','1','0','0','video','0'),
('Groups','group','1','1','4','0','core_main_group','2','0','0','group','0'),
('Blogs','blog','1','1','5','0','core_main_blog','2','0','0','blog','0'),
('Classifieds','classified','1','1','6','0','core_main_classified','2','0','0','classified','0'),
('Events','event','1','1','7','0','core_main_event','2','0','0','event','0'),
('Music','music','1','1','8','0','core_main_music','2','0','0','music','0'),
('Stores','Estore','1','1','52','0','core_main_estore','2','0','0','estore','0'),
('Forums','Sesforum','1','1','53','0','core_main_sesforum','2','0','0','sesforum','0'),
('Polls','poll','1','1','9','0','core_main_poll','2','0','0','poll','0'),
('Forums','Forum','1','1','54','0','core_main_forum','2','0','0','forum','0');");

$db->query("INSERT IGNORE INTO `engine4_sesapi_menus`(`label`, `module`, `type`, `status`, `order`, `file_id`,`class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES  ('Credit','sescredit','1','1','60','0','core_main_sescredit','2','0','0','sescredit','');");


$db->query("ALTER TABLE `engine4_sesapi_menus` CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `module` `module` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `url` `url` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `class` `class` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `module_name` `module_name` VARCHAR(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `module_name` `module_name` VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
$db->query("ALTER TABLE `engine4_sesapi_menus` CHANGE `version` `version` VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");


$db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`, `url`, `class`, `device`, `is_delete`, `visibility`, `module_name`, `version`) VALUES
("Polls", "Polls", 1, 1, 20, 0, "", "core_main_sesadvpoll", 1, 0, 0, "sesadvpoll",""),
("Polls", "Polls", 1, 1, 20, 0, "", "core_main_sesadvpoll", 2, 0, 0, "sesadvpoll","");');

$db->query('INSERT IGNORE INTO `engine4_sesapi_menus` (`label`, `module`, `type`, `status`, `order`, `file_id`,  `class`, `device`, `is_delete`,  `version`,`visibility`,`url`) VALUES
("Wallet", "Core", 1, 1, 65, 0, "core_wallet", 1, 0, "",1,"BASE_URLpayment/settings/wallet"),
("Wallet", "Core", 1, 1, 65, 0, "core_wallet", 2, 0, "",1,"BASE_URLpayment/settings/wallet"),
("Support", "Core", 1, 1, 67, 0, "core_support", 1, 0, "",1,"BASE_URLsupport"),
("Support", "Core", 1, 1, 67, 0,  "core_support", 2, 0, "",1,"BASE_URLsupport");');
