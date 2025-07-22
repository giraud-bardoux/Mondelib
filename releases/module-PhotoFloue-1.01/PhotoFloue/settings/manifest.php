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
    'name' => 'photofloue',
    'version' => '1.0.1',
    'revision' => '$Revision: 1 $',
    'path' => 'application/modules/PhotoFloue',
    'repository' => 'custom',
    'title' => 'Photo Floue Module',
    'description' => 'Floute les photos des albums et utilisateurs pour les visiteurs non-membres afin d\'encourager l\'inscription',
    'author' => 'Custom Development',
    'thumb' => 'application/modules/PhotoFloue/externals/images/thumb.png',
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
      'path' => 'application/modules/PhotoFloue/settings/install.php',
      'class' => 'PhotoFloue_Installer',
    ),
    'directories' => array(
      'application/modules/PhotoFloue',
    ),
    'files' => array(
      'application/languages/en/photofloue.csv',
      'application/languages/fr/photofloue.csv',
    ),
  ),
  
  // Load default CSS and JS files
  'loadDefault' => array(
    'js' => array(
      'application/modules/PhotoFloue/externals/scripts/photofloue.js',
    ),
    'css' => array(
      'application/modules/PhotoFloue/externals/styles/photofloue.css',
    )
  ),
  
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onRenderLayoutDefault',
      'resource' => 'PhotoFloue_Plugin_Core',
    ),
    array(
      'event' => 'onUserPhotoUpload',
      'resource' => 'PhotoFloue_Plugin_Core',
    ),
    array(
      'event' => 'onItemCreateAfter',
      'resource' => 'PhotoFloue_Plugin_Core',
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