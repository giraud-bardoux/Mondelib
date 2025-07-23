<?php
/**
 * PhotoFloue Module v1.3.0 - Bootstrap
 * Bootstrap simplifié pour SocialEngine 7.4
 */

class PhotoFloue_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
    /**
     * Constructeur
     */
    public function __construct($application)
    {
        parent::__construct($application);
    }

    /**
     * Initialisation des chemins des vues
     */
    protected function _initViewHelperPath()
    {
        $view = Zend_Registry::get('Zend_View');
        if ($view) {
            $view->addHelperPath(APPLICATION_PATH . '/modules/PhotoFloue/View/Helper', 'PhotoFloue_View_Helper');
        }
    }

    /**
     * Initialisation simplifiée du module PhotoFloue
     * Version 1.3.0 : Pas de hooks, approche CSS/JS pure
     */
    protected function _initPhotoFloue()
    {
        // Vérifier si le module est activé
        $enabled = Engine_Api::_()->getApi('settings', 'core')->getSetting('photofloue.enabled', 1);
        
        if (!$enabled) {
            return;
        }

        // Initialisation des scripts et styles
        $this->_initAssets();
        
        // Initialisation des variables JavaScript
        $this->_initJavaScriptConfig();
    }

    /**
     * Initialisation des assets (CSS/JS)
     */
    protected function _initAssets()
    {
        // Les assets sont chargés automatiquement via le manifest
        // Pas besoin de code supplémentaire
    }

    /**
     * Initialisation de la configuration JavaScript
     */
    protected function _initJavaScriptConfig()
    {
        // Récupérer les paramètres du module
        $settings = Engine_Api::_()->getApi('settings', 'core');
        
        $config = array(
            'enabled' => (bool) $settings->getSetting('photofloue.enabled', 1),
            'blurIntensity' => (int) $settings->getSetting('photofloue.blur_intensity', 10),
            'protectionEnabled' => (bool) $settings->getSetting('photofloue.protection_enabled', 1),
            'mobileProtection' => (bool) $settings->getSetting('photofloue.mobile_protection', 1),
            'loginMessage' => $settings->getSetting('photofloue.login_message', 'Connectez-vous pour voir les photos nettes'),
            'version' => '1.3.0'
        );

        // Injecter la configuration dans la page
        $view = Zend_Registry::get('Zend_View');
        if ($view) {
            // Vérifier si l'utilisateur est connecté
            $viewer = Engine_Api::_()->user()->getViewer();
            $isLoggedIn = $viewer && $viewer->getIdentity();
            
            // Script de configuration
            $script = "
                window.PHOTOFLOUE_CONFIG = " . json_encode($config) . ";
                window.PHOTOFLOUE_USER_LOGGED_IN = " . ($isLoggedIn ? 'true' : 'false') . ";
            ";
            
            $view->headScript()->appendScript($script);
            
            // Ajouter une classe CSS au body pour la détection côté CSS
            if ($isLoggedIn) {
                $view->headScript()->appendScript("document.body.classList.add('photofloue-user-logged-in');");
            } else {
                $view->headScript()->appendScript("document.body.classList.add('photofloue-user-not-logged-in');");
            }
        }
    }
}
?>