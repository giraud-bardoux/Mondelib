<?php
/**
 * EventFix Module - Plugin Core
 * Plugin pour étendre la plage d'années du champ "Date de début" du module Event
 *
 * @category   Application_EventFix
 * @package    EventFix
 * @author     EventFix Plugin
 * @version    1.0.0
 */

class EventFix_Plugin_Core extends Core_Plugin_Abstract
{
    /**
     * Intercepte le rendu des layouts pour injecter le JavaScript de modification des formulaires Event
     */
    public function onRenderLayoutDefault($event, $mode = null)
    {
        // Vérifier si nous sommes dans l'admin et si le module est désactivé
        if (defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER) return;
        
        // Obtenir la vue actuelle
        $view = Zend_Registry::get('Zend_View');
        if (!$view) return;

        // Vérifier si nous sommes sur une page liée aux événements
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (!$request) return;
        
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        // Détection des pages de création/édition d'événements
        $isEventForm = (
            ($module === 'event' && in_array($action, array('create', 'edit'))) ||
            ($controller === 'event' && in_array($action, array('create', 'edit'))) ||
            // Support pour différentes structures de modules Event
            (stripos($controller, 'event') !== false && in_array($action, array('create', 'edit')))
        );
        
        // Injecter le JavaScript sur toutes les pages pour une compatibilité maximale
        // Le script ne s'exécutera que si les éléments appropriés sont présents
        $this->_injectEventFixScript($view);
    }

    /**
     * Version simplifiée pour les layouts simple
     */
    public function onRenderLayoutDefaultSimple($event)
    {
        return $this->onRenderLayoutDefault($event, 'simple');
    }

    /**
     * Injecte le JavaScript qui modifie les formulaires Event
     */
    protected function _injectEventFixScript($view)
    {
        $script = "
<script type=\"text/javascript\">
(function() {
    /**
     * EventFix Plugin - Extension de la plage d'années pour les formulaires Event
     * Modifie dynamiquement les éléments de date pour permettre 5 ans en arrière
     */
    
    function extendEventDateRange() {
        // Rechercher les éléments de formulaire starttime des formulaires Event
        var starttimeSelectors = [
            'select[name=\"starttime[year]\"]',
            'select[name=\"starttime-year\"]', 
            'select[id*=\"starttime\"][id*=\"year\"]',
            'select[class*=\"starttime\"][class*=\"year\"]',
            '.form-element select[name*=\"starttime\"]',
            '#starttime-element select',
            'form[class*=\"event\"] select[name*=\"year\"]',
            'form[id*=\"event\"] select[name*=\"year\"]'
        ];
        
        var currentYear = new Date().getFullYear();
        var minYear = currentYear - 5;
        
        starttimeSelectors.forEach(function(selector) {
            var yearSelects = document.querySelectorAll(selector);
            
            yearSelects.forEach(function(yearSelect) {
                if (!yearSelect || yearSelect.getAttribute('data-eventfix-processed')) {
                    return;
                }
                
                // Marquer comme traité pour éviter les doublons
                yearSelect.setAttribute('data-eventfix-processed', 'true');
                
                // Vérifier si c'est bien un sélecteur d'année (contient des années)
                var hasYearOptions = false;
                var options = yearSelect.options;
                for (var i = 0; i < options.length; i++) {
                    var value = parseInt(options[i].value);
                    if (value >= 2000 && value <= currentYear + 10) {
                        hasYearOptions = true;
                        break;
                    }
                }
                
                if (!hasYearOptions) return;
                
                // Sauvegarder la valeur sélectionnée
                var selectedValue = yearSelect.value;
                
                // Collecter les années existantes
                var existingYears = [];
                for (var i = 0; i < options.length; i++) {
                    var yearValue = parseInt(options[i].value);
                    if (!isNaN(yearValue)) {
                        existingYears.push(yearValue);
                    }
                }
                
                // Trouver l'année minimum existante
                var existingMinYear = Math.min.apply(Math, existingYears);
                
                // Si l'année minimum existante est déjà inférieure ou égale à notre minimum souhaité, 
                // ne pas modifier
                if (existingMinYear <= minYear) {
                    return;
                }
                
                // Ajouter les années manquantes (de minYear à existingMinYear-1)
                var newOptions = [];
                
                // Copier d'abord les options non-année (comme les options vides)
                for (var i = 0; i < options.length; i++) {
                    var value = parseInt(options[i].value);
                    if (isNaN(value)) {
                        newOptions.push({
                            value: options[i].value,
                            text: options[i].text,
                            isYear: false
                        });
                    }
                }
                
                // Ajouter toutes les années (nouvelles + existantes) triées
                var allYears = [];
                for (var year = minYear; year < existingMinYear; year++) {
                    allYears.push(year);
                }
                allYears = allYears.concat(existingYears);
                allYears.sort(function(a, b) { return a - b; });
                
                // Ajouter les années comme options
                allYears.forEach(function(year) {
                    newOptions.push({
                        value: year.toString(),
                        text: year.toString(),
                        isYear: true
                    });
                });
                
                // Reconstruire les options
                yearSelect.innerHTML = '';
                newOptions.forEach(function(optionData) {
                    var option = document.createElement('option');
                    option.value = optionData.value;
                    option.text = optionData.text;
                    yearSelect.appendChild(option);
                });
                
                // Restaurer la valeur sélectionnée si elle existe encore
                if (selectedValue) {
                    yearSelect.value = selectedValue;
                }
                
                console.log('EventFix: Étendu la plage d\\'années pour', selector, 'de', minYear, 'à', Math.max.apply(Math, allYears));
            });
        });
    }
    
    // Exécuter immédiatement
    extendEventDateRange();
    
    // Exécuter après le chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', extendEventDateRange);
    }
    
    // Exécuter après le chargement complet de la page
    if (document.readyState !== 'complete') {
        window.addEventListener('load', extendEventDateRange);
    }
    
    // Observer les changements du DOM pour les formulaires chargés dynamiquement
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function(mutations) {
            var shouldCheck = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'SELECT' || 
                                node.tagName === 'FORM' || 
                                node.querySelector && node.querySelector('select')) {
                                shouldCheck = true;
                                break;
                            }
                        }
                    }
                }
            });
            
            if (shouldCheck) {
                setTimeout(extendEventDateRange, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Fonction globale pour une utilisation manuelle si nécessaire
    window.eventFixExtendDateRange = extendEventDateRange;
    
})();
</script>";

        // Ajouter le script à la vue
        $view->headScript()->appendScript($script);
    }
}