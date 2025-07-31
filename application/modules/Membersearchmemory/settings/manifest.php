<?php
/**
 * Member Search Memory Module
 * Sauvegarde et restaure les paramètres de recherche de membres
 */
return array(
    'package' => array(
        'type' => 'module',
        'name' => 'membersearchmemory',
        'version' => '1.0.0',
        'path' => 'application/modules/Membersearchmemory',
        'title' => 'Member Search Memory',
        'description' => 'Sauvegarde automatiquement les paramètres de recherche de membres',
        'author' => 'MonDeLibertin',
        'callback' => array(
            'class' => 'Engine_Package_Installer_Module',
        ),
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'directories' => array(
            'application/modules/Membersearchmemory',
        ),
        'files' => array(
            'application/languages/en/membersearchmemory.csv',
            'application/languages/fr/membersearchmemory.csv',
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Membersearchmemory_Plugin_Core',
        ),
    ),
);