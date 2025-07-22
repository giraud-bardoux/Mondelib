<?php

return array(
    'package' => array(
        'type' => 'module',
        'name' => 'sitetranslator',
        'version' => '6.5.0',
        'seao-seao-sku' => 'seao-sitetranslator',
        'path' => 'application/modules/Sitetranslator',
        'title' => 'Language Translator Plugin',
        'description' => 'SEAO - Language Translator Plugin',
        'author' => '<a href="http://www.socialapps.tech" style="text-decoration:underline;" target="_blank">SocialApps.tech</a>',
        'thumb' => 'application/modules/Sitetranslator/externals/images/thumb.png',
        'callback' => 
        array (
            'path' => 'application/modules/Sitetranslator/settings/install.php',
            'class' => 'Sitetranslator_Installer',
        ),
        'dependencies' => array(
          array(
            'type' => 'module',
            'name' => 'core',
            'minVersion' => '4.9.4p4',
          ),
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
            0 => 'application/modules/Sitetranslator',
        ),
        'files' =>
        array(
            0 => 'application/languages/en/sitetranslator.csv',
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Sitetranslator_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutDefaultSimple',
            'resource' => 'Sitetranslator_Plugin_Core',
        ),
        array(
            'event' => 'routeShutdown',
            'resource' => 'Sitetranslator_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutMobileSMDefault',
            'resource' => 'Sitetranslator_Plugin_Core',
        ),
    ),
);
?>
