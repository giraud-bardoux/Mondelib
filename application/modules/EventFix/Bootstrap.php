<?php
/**
 * EventFix Module - Bootstrap
 * Initialisation du module EventFix
 *
 * @category   Application_EventFix
 * @package    EventFix
 * @author     EventFix Plugin
 * @version    1.0.0
 */

class EventFix_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
    public function __construct($application)
    {
        parent::__construct($application);
    }

    protected function _initEventFix()
    {
        // Vérifier si le module est activé
        if (!Engine_Api::_()->hasModuleBootstrap('eventfix')) {
            return;
        }

        // Le plugin se charge automatiquement via les hooks définis dans le manifest
        // Aucune initialisation spéciale requise
    }
}