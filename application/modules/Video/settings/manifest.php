<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manifest.php 10269 2014-06-20 19:53:00Z mfeineman $
 * @author     Jung
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'video',
    'version' => '7.4.0',
    'revision' => '$Revision: 10269 $',
    'path' => 'application/modules/Video',
    'repository' => 'socialengine.com',
    'title' => 'Videos',
    'description' => 'Allow members to share videos from thousands of popular media sites or upload videos from their computers, keeping members engaged and entertained.',
    'author' => '<a href="https://socialengine.com/" style="text-decoration:underline;" target="_blank">SocialEngine</a>',
    'thumb' => 'application/modules/Video/externals/images/thumb.png',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       'enable',
       'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Video/settings/install.php',
      'class' => 'Video_Installer',
    ),
    'directories' => array(
      'application/modules/Video',
    ),
    'files' => array(
      'application/languages/en/video.csv',
    ),
  ),
  //Load by default css / js file ---------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
      'externals/html5media/html5media.min.js',
      'externals/jQuery/video/plyr.min.js',
    ),
    'css' => array(
      'externals/jQuery/video/css/plyr.css',
    )
  ),
  // Compose
  // 'composer' => array(
  //   'video' => array(
  //     'script' => array('_composeVideo.tpl', 'video'),
  //     'plugin' => 'Video_Plugin_Composer',
  //     'auth' => array('video', 'create'),
  //   ),
  // ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'video','video_category'
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onStatistics',
      'resource' => 'Video_Plugin_Core'
    ),
    array(
      'event' => 'onUserDeleteBefore',
      'resource' => 'Video_Plugin_Core',
    ),
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'video_general' => array(
      'route' => 'videos/:action/*',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'browse',
      ),
      'reqs' => array(
        'action' => '(index|browse|create|list|manage)',
      )
    ),
    'video_view' => array(
      'route' => 'videos/:user_id/:video_id/:slug/*',
      'defaults' => array(
        'module' => 'video',
        'controller' => 'index',
        'action' => 'view',
        'slug' => '',
      ),
      'reqs' => array(
        'user_id' => '\d+'
      )
    ),
  )
) ?>
