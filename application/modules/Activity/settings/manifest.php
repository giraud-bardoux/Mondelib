<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manifest.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'activity',
    'version' => '7.3.0',
    'revision' => '$Revision: 10267 $',
    'path' => 'application/modules/Activity',
    'repository' => 'socialengine.com',
    'title' => 'Activity',
    'description' => 'Activity',
    'author' => 'SocialEngine Core',
    'thumb' => 'application/modules/Core/externals/images/thumb.png',
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
      //'enable',
      //'disable',
    ),
    'callback' => array(
      'path' => 'application/modules/Activity/settings/install.php',
      'class' => 'Activity_Installer',
      'priority' => 4000,
    ),
    'directories' => array(
      'application/modules/Activity',
    ),
    'files' => array(
      'application/languages/en/activity.csv',
    ),
  ),
  //Load by default css / js file ---------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
      'application/modules/Activity/externals/scripts/composer.js',
      'externals/jQuery/mention/jquery.mentionsInput.js',
      'externals/jQuery/schedule/bootstrap.min.js',
      'externals/jQuery/schedule/bootstrap-datetimepicker.min.js',
    ),
    'css' => array(
      'application/modules/Activity/externals/styles/tooltip.css',
    )
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onActivityActionCreateAfter',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onActivityActionDeleteBefore',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onActivityActionUpdateAfter',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'getActivity',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Activity_Plugin_Core'
    ),
    array(
      'event' => 'onRenderLayoutDefaultSimple',
      'resource' => 'Activity_Plugin_Core'
    ),
  ),
  // Compose -------------------------------------------------------------------
  'composer' => array(
    'albumvideo' => array(
      'script' => array('_composeAlbumVideo.tpl', 'activity'),
      'plugin' => 'Activity_Plugin_AlbumVideoComposer',
    ),
    'activitylink' => array(
      'script' => array('_composeLink.tpl', 'activity'),
      'plugin' => 'Activity_Plugin_LinkComposer',
      'auth' => array('core_link', 'create'),
    ),
    'fileupload' => array(
      'script' => array('_composefileupload.tpl', 'activity'),
      'plugin' => 'Activity_Plugin_FileuploadComposer',
    ),
    'buysell' => array(
      'script' => array('_composebuysell.tpl', 'activity'),
      'plugin' => 'Activity_Plugin_BuysellComposer',
    ),
    'activitytargetpost' => array(
      'script' => array('_composetargetpost.tpl', 'activity'),
    ),
  ),

  // Items ---------------------------------------------------------------------
  'items' => array(
    'activity_action',
    'activity_comment',
    'activity_like',
    'activity_notification',
    'activity_background',
    'activity_feeling',
    'activity_feelingicon',
    'activity_filterlist',
    'activity_file',
    'activity_buysell',
    'activity_link',
    'activity_emoji',
    'activity_emojiicon',
    'activity_attachment',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'recent_activity' => array(
      'route' => 'activity/notifications/:action/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'notifications',
        'action' => 'index',
      )
    ),
    'activity_attachment_view' => array(
      'route' => 'media/:action_id/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'attachmentview'
      ),
    ),
    'activity_view' => array(
      'route' => 'feed/:action_id/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'view'
      ),
    ),
    'activity_onthisday' => array(
      'route' => 'onthisday',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'onthisday'
      ),
    ),
    'activity_sell' => array(
      'route' => 'sell',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'index',
        'action' => 'sell'
      ),
    ),
  )
);