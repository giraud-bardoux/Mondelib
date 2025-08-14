<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manifest.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

return array(
  'package' =>
  array(
		'type' => 'module',
    'name' => 'warning',
    'version' => '7.4.0',
    'path' => 'application/modules/Warning',
    'title' => 'Website Warning Pages',
    'description' => 'Website Warning Pages',
    'author' => '<a href="https://socialengine.com/" style="text-decoration:underline;" target="_blank">SocialEngine</a>',
    'thumb' => 'application/modules/Warning/externals/images/thumb.png',
		'callback' => array(
			'path' => 'application/modules/Warning/settings/install.php',
			'class' => 'Warning_Installer',
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
			'application/modules/Warning',
		),
		'files' =>
		array(
			'application/languages/en/warning.csv',
		),
  ),
  //Load by default css / js file ------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
      'application/modules/Warning/externals/scripts/moment.js',
      'application/modules/Warning/externals/scripts/jquery.syotimer.min.js',
    ),
    'css' => array( 
      'application/modules/Warning/externals/styles/comingsoon.css',
      'application/modules/Warning/externals/styles/flipclock.css',
    )
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Warning_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'warning_visitor'
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'warning_comingsoon' => array(
      'route' => 'comingsoon/:controller/:action/*',
      'defaults' => array(
        'module' => 'warning',
        'controller' => 'error',
        'action' => 'comingsoon'
      ),
    ),
  ),
);
