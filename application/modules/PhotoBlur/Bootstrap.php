<?php
/**
 * PhotoBlur Module - Bootstrap
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  /**
   * Initialisation du module
   */
  protected function _initPhotoBlur()
  {
    // Enregistrer les helpers de vue personnalisés
    $this->_initViewHelpers();
    
    // Enregistrer les hooks
    $this->_initHooks();
  }
  
  /**
   * Initialisation des helpers de vue
   */
  protected function _initViewHelpers()
  {
    $view = Zend_Registry::get('Zend_View');
    if ($view) {
      // Ajouter le chemin pour les helpers personnalisés
      $view->addHelperPath(APPLICATION_PATH . '/modules/PhotoBlur/View/Helper', 'PhotoBlur_View_Helper');
    }
  }
  
  /**
   * Initialisation des hooks
   */
  protected function _initHooks()
  {
    // Les hooks sont déjà définis dans le manifest.php
    // Cette méthode peut être utilisée pour des configurations supplémentaires
  }
  
  /**
   * Configuration des autoloaders
   */
  protected function _initAutoload()
  {
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
      'basePath'  => APPLICATION_PATH . '/modules/PhotoBlur',
      'namespace' => 'PhotoBlur',
      'resourceTypes' => array(
        'model' => array(
          'path' => 'Model/',
          'namespace' => 'Model',
        ),
        'form' => array(
          'path' => 'Form/',
          'namespace' => 'Form',
        ),
        'plugin' => array(
          'path' => 'Plugin/',
          'namespace' => 'Plugin',
        ),
      ),
    ));
    $autoloader->pushAutoloader($resourceLoader);
  }
}