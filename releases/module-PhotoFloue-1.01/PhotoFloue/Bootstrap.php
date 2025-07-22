<?php
/**
 * PhotoFloue Module for SocialEngine 7.4 - Bootstrap
 *
 * @category   Application_Extensions
 * @package    PhotoFloue
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoFloue_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
      /**
     * Initialisation du module PhotoFloue
     */
    public function __construct($application)
    {
      parent::__construct($application);
      
      // Initialiser les chemins des helpers de vue
      $this->initViewHelperPath();
    }
    
    /**
     * Initialisation des helpers de vue personnalisés
     */
    protected function initViewHelperPath()
    {
      $view = Zend_Registry::get('Zend_View');
      if ($view) {
        // Ajouter le chemin pour nos helpers personnalisés
        $view->addHelperPath(APPLICATION_PATH . '/modules/PhotoFloue/View/Helper', 'PhotoFloue_View_Helper');
      }
    }
    
    /**
     * Enregistrement du plugin principal
     */
    protected function _initPhotoFlouePlugin()
    {
      $front = Zend_Controller_Front::getInstance();
      $front->registerPlugin(new PhotoFloue_Plugin_Core());
    }
}