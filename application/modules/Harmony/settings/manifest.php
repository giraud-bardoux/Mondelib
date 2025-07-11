<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manifest.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

return array (
  'package' =>
  array(
    'type' => 'module',
    'name' => 'harmony',
    'version' => '7.3.0',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'path' => 'application/modules/Harmony',
    'title' => 'Harmony Theme',
    'description' => 'Responsive, modern theme with features to delight you and your members!',
    'author' => '<a href="https://socialengine.com/" style="text-decoration:underline;" target="_blank">SocialEngine</a>',
    'thumb' => 'application/modules/Harmony/externals/images/thumb.png',
    'callback' => array(
        'path' => 'application/modules/Harmony/settings/install.php',
        'class' => 'Harmony_Installer',
    ),
    'actions' =>
    array(
        0 => 'install',
        1 => 'upgrade',
        2 => 'refresh',
        3 => 'enable',
        4 => 'disable',
    ),
    'directories' =>
    array(
      'application/modules/Harmony',
      'application/themes/harmony',
    ),
    'files' =>
    array(
      'application/languages/en/harmony.csv',
    ),
  ),
  'items' => array(
    'harmony_customthemes',
  ),
  //Load by default css / js file ------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
    ),
    'css' => array( 
      'application/themes/harmony/lineicon.css',
      'application/modules/Harmony/externals/styles/landing-page.css',
    )
  ),
	// Hooks ---------------------------------------------------------------------
	'hooks' => array(
		array(
			'event' => 'onRenderLayoutDefault',
			'resource' => 'Harmony_Plugin_Core'
		),
		array(
			'event' => 'onRenderLayoutDefaultSimple',
			'resource' => 'Harmony_Plugin_Core'
		),
	),
);
