<?php
/**
 * PhotoFloue Module v1.3.0 - Manifest
 * Version simplifiée et robuste pour SocialEngine 7.4
 */

return array(
  // Module Package Information
  'package' => array(
    'type' => 'module',
    'name' => 'photofloue',
    'version' => '1.3.0',
    'revision' => '$Revision: 1 $',
    'path' => 'application/modules/PhotoFloue',
    'repository' => 'custom',
    'title' => 'PhotoFloue - Floutage des Photos',
    'description' => 'Module de floutage des photos pour visiteurs non connectés avec protection anti-capture. Version simplifiée et stable.',
    'author' => 'Custom Development',
    'thumb' => 'application/modules/PhotoFloue/externals/images/admin-thumb.png',
    'changeLog' => array(
      '1.3.0' => array(
        'Refonte complète de l\'architecture',
        'Suppression des hooks problématiques',
        'Approche CSS/JS simplifiée et stable',
        'Installation sans conflits',
        'Performance optimisée'
      ),
      '1.0.1' => array(
        'Version initiale avec hooks avancés',
        'Problèmes d\'installation détectés'
      )
    ),
    'callback' => array(
      'path' => 'application/modules/PhotoFloue/settings/install.php',
      'class' => 'PhotoFloue_Installer'
    ),
    'actions' => array(
      0 => 'install',
      1 => 'upgrade', 
      2 => 'refresh',
      3 => 'enable',
      4 => 'disable',
      5 => 'uninstall'
    ),
    'directories' => array(
      'application/modules/PhotoFloue'
    ),
    'files' => array(
      'application/languages/en/photofloue.csv',
      'application/languages/fr/photofloue.csv'
    )
  ),

  // Module Items (Simplified)
  'items' => array(
    'photofloue_setting'
  ),

  // Routes (None needed for v1.3)
  'routes' => array(),

  // CSS and JavaScript
  'loadDefault' => array(
    'css' => array('application/modules/PhotoFloue/externals/styles/photofloue.css'),
    'js' => array('application/modules/PhotoFloue/externals/scripts/photofloue.js')
  ),

  // NO HOOKS - Simplified approach
  'hooks' => array(),

  // Dependencies
  'dependencies' => array(
    array(
      'type' => 'module',
      'name' => 'core',
      'minVersion' => '7.4.0'
    ),
    array(
      'type' => 'module', 
      'name' => 'user',
      'minVersion' => '7.4.0'
    )
  )
);
?>