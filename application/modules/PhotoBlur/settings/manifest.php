<?php
/**
 * PhotoBlur Module for SocialEngine 7.4
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 * @version    1.0.0
 * @author     Custom Development
 */

return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'photoblur',
    'version' => '1.0.0',
    'revision' => '$Revision: 1 $',
    'path' => 'application/modules/PhotoBlur',
    'repository' => 'custom',
    'title' => 'Photo Blur Module',
    'description' => 'Floute les photos des albums et utilisateurs pour les visiteurs non-membres afin d\'encourager l\'inscription',
    'author' => 'Custom Development',
    'thumb' => 'application/modules/PhotoBlur/externals/images/thumb.png',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '7.0.0',
      ),
      array(
        'type' => 'module',
        'name' => 'user',
        'minVersion' => '7.0.0',
      ),
      array(
        'type' => 'module',
        'name' => 'album',
        'minVersion' => '7.0.0',
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
      'path' => 'application/modules/PhotoBlur/settings/install.php',
      'class' => 'PhotoBlur_Installer',
    ),
    'directories' => array(
      'application/modules/PhotoBlur',
    ),
    'files' => array(
      'application/languages/en/photoblur.csv',
      'application/languages/fr/photoblur.csv',
    ),
  ),
  
  // Load default CSS and JS files
  'loadDefault' => array(
    'js' => array(
      'application/modules/PhotoBlur/externals/scripts/photoblur.js',
    ),
    'css' => array(
      'application/modules/PhotoBlur/externals/styles/photoblur.css',
    )
  ),
  
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'PhotoBlur_Plugin_Core',
    ),
    array(
      'event' => 'onUserPhotoUpload',
      'resource' => 'PhotoBlur_Plugin_Core',
    ),
    array(
      'event' => 'onItemCreateAfter',
      'resource' => 'PhotoBlur_Plugin_Core',
    ),
  ),
  
  // Items ---------------------------------------------------------------------
  'items' => array(
    // Pas d'items spécifiques pour ce module
  ),
  
  // Routes --------------------------------------------------------------------
  'routes' => array(
    // Pas de routes spécifiques pour ce module
  ),
);