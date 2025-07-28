<?php

/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: manifest.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

$array = array(
	'package' => array(
		'type' => 'module',
		'name' => 'sesapi',
		'version' => '7.4.0',
		'dependencies' => array(
			array(
				'type' => 'module',
				'name' => 'core',
				'minVersion' => '6.6.0',
			),
		),
		'path' => 'application/modules/Sesapi',
		'title' => 'SNS - SocialEngine REST APIs Plugin',
		'description' => 'SNS - SocialEngine REST APIs Plugin',
		'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
		'thumb' => 'application/modules/Sesapi/externals/images/thumb.png',
		'callback' => array(
			'path' => 'application/modules/Sesapi/settings/install.php',
			'class' => 'Sesapi_Installer',
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
				'application/modules/Sesapi',
				'apps',
			),
		'files' =>
			array(
				'application/languages/en/sesapi.csv',
				'application/sesapi.php',
				'boot/Sesapi.php',
			),
	),
	'loadDefault' => array(
		'js' => array(
		),
		'css' => array(
		),
	),
	// Items --------------------------------------------------------------------
	'items' => array(
		'sesapi_menu',
		'sesapi_aouthtoken'
	),
	// Routes --------------------------------------------------------------------
	'routes' => array(
		'sesapi_navigation_menu' => array(
			'route' => 'sesapi/navigation/:action/*',
			'defaults' => array(
				'module' => 'core',
				'controller' => 'index',
				'action' => 'index',
			),
		),
		'user_info' => array(
			'route' => 'user/profile/user-info/*',
			'defaults' => array(
				'module' => 'user',
				'controller' => 'profile',
				'action' => 'user-info',
			),
		),
		'activity_feeling_result' => array(
			'route' => 'feelings/:action/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feelings',
				'action' => 'feeling',
			),
		),
		'comment_result' => array(
			'route' => 'stickers/:action/*',
			'defaults' => array(
				'module' => 'comment',
				'controller' => 'index',
				'action' => 'stickers',
			),
		),
		'sesapi_content_like' => array(
			'route' => 'content/like/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'like',
			),
		),
		'sesapi_app_data' => array(
			'route' => 'app-default-data/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'app-default-data',
			),
		),

		'sesapi_music_create' => array(
			'route' => 'musics/albums/:action/*',
			'defaults' => array(
				'module' => 'music',
				'controller' => 'index',
				'action' => 'create',
			),
		),

		'sesapi_activity' => array(
			'route' => 'activity/post/index',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'post',
				'action' => 'index',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/save/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'save',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/comment/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'comment',
			),
		),
		'sesapi_content_commentdelete' => array(
			'route' => 'comment/delete/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'delete',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/delete/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'delete',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/edit/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'edit',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/hidden/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'hidden',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/like/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'hidden',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/share/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'share',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/feed/unlike/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'feed',
				'action' => 'hidden',
			),
		),
		'sesapi_content_comment' => array(
			'route' => 'comments/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'comments',
			),
		),
		'sesapi_content_commentviewmore' => array(
			'route' => 'comment/viewcommentreply/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'viewcommentreply',
			),
		),
		'sesapi_content_commentcreate' => array(
			'route' => 'comment/create/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'create',
			),
		),
		'sesapi_content_commentedit' => array(
			'route' => 'comment/edit/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'edit',
			),
		),
		'sesapi_content_commentreply' => array(
			'route' => 'comment/reply/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'reply',
			),
		),

		'sesapi_content_get_comment_like' => array(
			'route' => 'comment-like/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'comment-like',
			),
		),
		'sesapi_content_fav' => array(
			'route' => 'content/favourite/*',
			'defaults' => array(
				'module' => 'sesapi',
				'controller' => 'index',
				'action' => 'favourite',
			),
		),
		'sesapi_activity' => array(
			'route' => 'activity/index/*',
			'defaults' => array(
				'module' => 'activity',
				'controller' => 'index',
				'action' => 'index',
			),
		),
		'sesapi_contact' => array(
			'route' => 'sesapi/help/:action/*',
			'defaults' => array(
				'module' => 'core',
				'controller' => 'help',
				'action' => 'contact',
			),
		),
	),
);

if (empty($_GET['restApi'])) {
	unset($array['routes']['sesapi_activity']);
}
return $array;
