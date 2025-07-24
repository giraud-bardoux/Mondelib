<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 * @copyright  Copyright 2024
 * @license    http://www.socialengine.com/license/
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'photoblur',
    'version' => '1.0.0',
    'revision' => '$Revision: 1 $',
    'path' => 'application/modules/Photoblur',
    'repository' => 'local',
    'title' => 'Photo Blur',
    'description' => 'Module permettant de flouter des photos pour les membres',
    'author' => 'Custom Module',
    'thumb' => 'application/modules/Photoblur/externals/images/thumb.png',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
      array(
        'type' => 'module',
        'name' => 'album',
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
      'path' => 'application/modules/Photoblur/settings/install.php',
      'class' => 'Photoblur_Installer',
    ),
    'directories' => array(
      'application/modules/Photoblur',
    ),
    'files' => array(
      'application/languages/en/photoblur.csv',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'photoblur_blur',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'photoblur_general' => array(
      'route' => 'photoblur/:action/*',
      'defaults' => array(
        'module' => 'photoblur',
        'controller' => 'index',
        'action' => 'index'
      ),
      'reqs' => array(
        'action' => '(index|blur|process)',
      ),
    ),
    'photoblur_photo' => array(
      'route' => 'photoblur/photo/:action/:photo_id/*',
      'defaults' => array(
        'module' => 'photoblur',
        'controller' => 'photo',
        'action' => 'blur',
      ),
      'reqs' => array(
        'action' => '(blur|download)',
      ),
    ),
  ),
  // Admin menu
  'menus' => array(
    array(
      'label' => 'Photo Blur',
      'route' => 'admin_default',
      'params' => array(
        'module' => 'photoblur',
        'controller' => 'manage',
        'action' => 'index'
      ),
      'type' => 'admin',
      'order' => 999,
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'Photoblur_Plugin_Core'
    ),
  ),
) ?>