<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: manifest.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

return array (
  'package' =>
  array (
    'type' => 'module',
    'name' => 'sesiosapp',
    'version' => '7.4.0',
	'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
    'path' => 'application/modules/Sesiosapp',
    'title' => 'Native iOS Mobile App Plugin',
    'description' => 'Native iOS Mobile App Plugin',
    'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
    'thumb' => 'application/modules/Sesiosapp/externals/images/thumb.png',
    'callback' => array(
      'path' => 'application/modules/Sesiosapp/settings/install.php',
      'class' => 'Sesiosapp_Installer',
		),
    'actions' =>
    array (
      0 => 'install',
      1 => 'upgrade',
      2 => 'refresh',
      3 => 'enable',
      4 => 'disable',
    ),
    'directories' =>
    array (
      0 => 'application/modules/Sesiosapp',
    ),
    'files' =>
    array (
      0 => 'application/languages/en/sesiosapp.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onActivityNotificationCreateAfter',
      'resource' => 'Sesiosapp_Plugin_Core',
    ),
  ),
  'items'=>array('sesiosapp_pushnotifications','sesiosapp_slide','sesiosapp_customthemes','sesiosapp_themes','sesiosapp_graphic')
);
