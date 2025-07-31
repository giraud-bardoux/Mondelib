<?php
/**
 * Member Search Memory Plugin
 * Injecte automatiquement le script de mémorisation de recherche
 */
class Membersearchmemory_Plugin_Core
{
    public function onRenderLayoutDefault($event)
    {
        // Obtenir la vue
        $view = $event->getPayload();
        if( !($view instanceof Zend_View_Interface) ) {
            return;
        }
        
        // Vérifier si nous sommes sur la page de recherche de membres
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        // Injecter le script sur toutes les pages où il pourrait y avoir une recherche de membres
        // ou seulement sur la page de recherche de membres
        if( ($module == 'user' && $controller == 'index' && $action == 'browse') ||
            ($module == 'core' && $controller == 'search') ||
            strpos($request->getRequestUri(), '/members') !== false ) {
            
            // Ajouter notre script JavaScript
            $view->headScript()->appendFile($view->layout()->staticBaseUrl . 'externals/scripts/member-search-memory.js');
        }
    }
}