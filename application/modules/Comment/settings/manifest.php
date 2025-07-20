<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manifest.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

return array (
  'package' =>
  array (
    'type' => 'module',
    'name' => 'comment',
    'version' => '7.4.0',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '6.0.0',
      ),
    ),
    'path' => 'application/modules/Comment',
    'title' => 'Comments',
    'description' => 'Comments',
    'author' => 'Webligo Developments',
    'callback' => array(
			'path' => 'application/modules/Comment/settings/install.php',
			'class' => 'Comment_Installer',
    ),
    'actions' =>
    array (
      0 => 'install',
      1 => 'upgrade',
      2 => 'refresh',
      //3 => 'enable',
      //4 => 'disable',
    ),
    'directories' =>
    array (
      'application/modules/Comment',
    ),
    'files' =>
    array (
      'application/languages/en/comment.csv',
    ),
  ),
  //Load by default css / js file ---------------------------------------------------------------------
  'loadDefault' => array(
    "js" => array(
    ),
    'css' => array(
    )
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'comment_emotioncategory',
    'comment_emotiongallery',
    'comment_emotionfile', 
    'comment_reaction',
    'comment_voteupdown',
  ),
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Comment_Plugin_Core',
    ),
    array(
      'event' => 'onRenderLayoutDefaultSimple',
      'resource' => 'Comment_Plugin_Core'
    ),
  ),
);
