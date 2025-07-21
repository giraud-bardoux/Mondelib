<?php
/**
 * EventFix Module
 * Plugin pour étendre la plage d'années du champ "Date de début" du module Event
 *
 * @category   Application_EventFix
 * @package    EventFix
 * @author     EventFix Plugin
 * @version    1.0.0
 */

return array(
    // Package -------------------------------------------------------------------
    'package' => array(
        'type' => 'module',
        'name' => 'eventfix',
        'version' => '1.0.0',
        'revision' => '1',
        'path' => 'application/modules/EventFix',
        'title' => 'Event Date Range Fix',
        'description' => 'Étend la plage d\'années sélectionnable dans les formulaires Event pour permettre des dates 5 ans en arrière.',
        'author' => 'EventFix Plugin',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/EventFix/settings/install.php',
            'class' => 'EventFix_Install',
        ),
        'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '7.0.0',
            ),
        ),
        'directories' => array(
            'application/modules/EventFix',
        ),
    ),

    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'EventFix_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutDefaultSimple', 
            'resource' => 'EventFix_Plugin_Core',
        ),
    ),

    // Items ---------------------------------------------------------------------
    'items' => array(
        'eventfix',
    ),
);