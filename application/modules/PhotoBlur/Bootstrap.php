<?php
/**
 * PhotoBlur Module for SocialEngine 7.4 - Bootstrap
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  /**
   * Initialisation du module PhotoBlur
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
      $view->addHelperPath(APPLICATION_PATH . '/modules/PhotoBlur/View/Helper', 'PhotoBlur_View_Helper');
    }
  }
  
  /**
   * Enregistrement du plugin principal
   */
  protected function _initPhotoBlurPlugin()
  {
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new PhotoBlur_Plugin_Core());
  }
}