<?php
return array(
  'package' => array(
    'type' => 'module',
    'name' => 'moduleflou',
    'version' => '1.22',
    'path' => 'application/modules/Moduleflou',
    'title' => 'Module Flou',
    'description' => 'Effet de floutage d’images et protections visuelles pour Mondelibertin.',
    'author' => 'Mondelibertin',
    'actions' => array('install', 'upgrade', 'refresh', 'enable', 'disable'),
    'callback' => array(
      'path' => 'application/modules/Moduleflou/settings/install.php',
      'class' => 'Moduleflou_Installer',
    ),
    'directories' => array('application/modules/Moduleflou'),
    'files' => array('application/languages/fr/moduleflou.csv'),
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '7.4.0'
      ),
    ),
  ),
  'routes' => array(
    'moduleflou_general' => array(
      'route' => 'flou/:action/*',
      'defaults' => array(
        'module' => 'moduleflou',
        'controller' => 'index',
        'action' => 'index'
      )
    ),
    'moduleflou_admin' => array(
      'route' => 'admin/flou/:action/*',
      'defaults' => array(
        'module' => 'moduleflou',
        'controller' => 'admin-settings',
        'action' => 'index'
      )
    )
  )
);
