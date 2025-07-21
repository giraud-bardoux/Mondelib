<?php
/**
 * PhotoBlur Module
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 * @version    $Id: manifest.php 1.0.0 $
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
    'description' => 'Module qui floute les photos pour les visiteurs non connectés et empêche la sauvegarde des images',
    'author' => 'Custom Development',
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
        'name' => 'storage',
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
      'event' => 'onItemPhotoRender',
      'resource' => 'PhotoBlur_Plugin_Core',
    ),
  ),
  
  // Routes --------------------------------------------------------------------
  'routes' => array(
    // Pas de routes spécifiques nécessaires pour ce module
  ),
  
  // Layout --------------------------------------------------------------------
  // Pas de layout spécifique requis
);