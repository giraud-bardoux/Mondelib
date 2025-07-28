<?php
//folder name or directory name.
$module_name = 'sesiosapp';

//product title and module title.
$module_title = 'iOS Native Mobile App Plugin';

if (!$this->getRequest()->isPost()) {
  return;
}

if (!$form->isValid($this->getRequest()->getPost())) {
  return;
}

if ($this->getRequest()->isPost()) {

  $postdata = array();
  //domain name
  $postdata['domain_name'] = $_SERVER['HTTP_HOST'];
  //license key
  $postdata['licenseKey'] = @base64_encode($_POST['sesiosapp_licensekey']);
  $postdata['module_title'] = @base64_encode($module_title);

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://socialnetworking.solutions/licensecheck.php");
  curl_setopt($ch, CURLOPT_POST, 1);

  // in real life you should use something like:
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));

  // receive server response ...
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $server_output = curl_exec($ch);

  $error = 0;
  if (curl_error($ch)) {
    $error = 1;
  }
  curl_close($ch);

  //here we can set some variable for checking in plugin files.
  if (1) {

    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesiosapp.pluginactivated')) {
    
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      
      $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
      ("sesiosapp_admin_main_menu", "sesiosapp", "Dashboard Menu Items", "", \'{"route":"admin_default","module":"sesiosapp","controller":"menu"}\', "sesiosapp_admin_main", "", 2),
      ("sesiosapp_admin_main_slideshow", "sesiosapp", "Welcome Slideshow", "", \'{"route":"admin_default","module":"sesiosapp","controller":"slideshow"}\', "sesiosapp_admin_main", "", 4),
      ("sesiosapp_admin_main_pushnoti", "sesiosapp", "Push Notifications", "", \'{"route":"admin_default","module":"sesiosapp","controller":"pushnotification","action":"manage"}\', "sesiosapp_admin_main", "", 3),
      ("sesiosapp_admin_main_managepushnoti", "sesiosapp", "Manage Push Notifications", "", \'{"route":"admin_default","module":"sesiosapp","controller":"pushnotification","action":"manage"}\', "sesiosapp_admin_main_pushnoti", "", 1),
      ("sesiosapp_admin_main_pushnotisettings", "sesiosapp", "Push Notifications Settings", "", \'{"route":"admin_default","module":"sesiosapp","controller":"pushnotification","action":"settings"}\', "sesiosapp_admin_main_pushnoti", "", 2),
      ("sesiosapp_admin_main_subscriber", "sesiosapp", "Manage Subscribers", "", \'{"route":"admin_default","module":"sesiosapp","controller":"subscribers"}\', "sesiosapp_admin_main", "", 4),
       ("sesiosapp_admin_main_styling", "sesiosapp", "Color Schemes", "", \'{"route":"admin_default","module":"sesiosapp","controller":"theme"}\', "sesiosapp_admin_main", "", 4),
      ("sesiosapp_admin_main_restapi", "sesiosapp", "REST APIs", "", \'{"route":"admin_default","module":"sesapi","controller":"settings","target":"_blank"}\', "sesiosapp_admin_main", "", 5);');

      $db->query('DROP TABLE IF EXISTS `engine4_sesiosapp_pushnotifications`;');
      $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesiosapp_pushnotifications` (
        `pushnotification_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
        `description` text COLLATE utf8mb4_unicode_ci,
        `criteria` varchar(244) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `param` text COLLATE utf8mb4_unicode_ci,
        `sent` tinyint(1) DEFAULT "0",
        `creation_date` datetime DEFAULT NULL,
        PRIMARY KEY (`pushnotification_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

      $db->query('DROP TABLE IF EXISTS `engine4_sesiosapp_slides`;');
      $db->query('CREATE TABLE IF NOT EXISTS `engine4_sesiosapp_slides` (
        `slide_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",
        `description` text COLLATE utf8mb4_unicode_ci,
        `status` TINYINT(1) COLLATE utf8mb4_unicode_ci DEFAULT "1",
        `file_id` INT(11) DEFAULT "0",
        `order` INT(11) DEFAULT "0",
        `creation_date` datetime DEFAULT NULL,
        PRIMARY KEY (`slide_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

      $db->query('DROP TABLE IF EXISTS `engine4_sesiosapp_themes`;');
      $db->query('CREATE TABLE `engine4_sesiosapp_themes` (
        `theme_id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        PRIMARY KEY (`theme_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
      $db->query("INSERT INTO `engine4_sesiosapp_themes` (`theme_id`, `name`) VALUES
        (1, 'Theme 1'),
        (2, 'Theme 2'),
        (3, 'Theme 3'),
        (4, 'Theme 4'),
        (5, 'Theme 5'),
        (6, 'Theme 6');");
        $db->query('DROP TABLE IF EXISTS `engine4_sesiosapp_customthemes`;');
      $db->query('CREATE TABLE `engine4_sesiosapp_customthemes` (
        `customtheme_id` int(11) NOT NULL AUTO_INCREMENT,
        `value` varchar(255) NOT NULL,
        `column_key` varchar(255) NOT NULL,
        `theme_id` int(11) NOT NULL,
        `is_custom` TINYINT(1) NOT NULL DEFAULT "0" ,
        PRIMARY KEY (`customtheme_id`),
        UNIQUE KEY `UNIQUEKEY` (`column_key`,`theme_id`,`is_custom`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
     
      include_once APPLICATION_PATH . "/application/modules/Sesiosapp/controllers/defaultsettings.php";

      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesiosapp.pluginactivated', 1);
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesiosapp.licensekey', $_POST['sesiosapp_licensekey']);
    }
    $domain_name = @base64_encode(str_replace(array('http://','https://','www.'),array('','',''),$_SERVER['HTTP_HOST']));
		$licensekey = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesiosapp.licensekey');
		$licensekey = @base64_encode($licensekey);
		Engine_Api::_()->getApi('settings', 'core')->setSetting('sesiosapp.sesdomainauth', $domain_name);
		Engine_Api::_()->getApi('settings', 'core')->setSetting('sesiosapp.seslkeyauth', $licensekey);
		$error = 1;
  } else {
    $error = $this->view->translate('Please enter correct License key for this product.');
    $error = Zend_Registry::get('Zend_Translate')->_($error);
    $form->getDecorator('errors')->setOption('escape', false);
    $form->addError($error);
    $error = 0;
    Engine_Api::_()->getApi('settings', 'core')->setSetting('sesiosapp.licensekey', $_POST['sesiosapp_licensekey']);
    return;
    $this->_helper->redirector->gotoRoute(array());
  }
}
