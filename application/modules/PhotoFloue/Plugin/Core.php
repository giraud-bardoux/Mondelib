<?php
/**
 * PhotoFloue Module for SocialEngine 7.4 - Core Plugin
 *
 * @category   Application_Extensions
 * @package    PhotoFloue
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoFloue_Plugin_Core extends Zend_Controller_Plugin_Abstract
{
  /**
   * Hook appelé lors du rendu du layout par défaut
   * Injecte les variables JavaScript nécessaires
   */
  public function onRenderLayoutDefault($event)
  {
    $view = Zend_Registry::get('Zend_View');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Vérifier si l'utilisateur est connecté
    $isLoggedIn = $viewer && $viewer->getIdentity();
    
    // Déterminer si on est sur la page d'accueil
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $isHomepage = $this->_isHomepage($request);
    
    // Ajouter les variables JavaScript globales
    $script = "
    <script type='text/javascript'>
      window.PHOTOFLOUE_CONFIG = {
        userLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . ",
        isHomepage: " . ($isHomepage ? 'true' : 'false') . ",
        loginMessage: '" . $view->translate("Connectez-vous pour voir les photos nettes") . "',
        protectionMessage: '" . $view->translate("Connectez-vous pour accéder aux photos") . "'
      };
    </script>";
    
    $view->headScript()->appendScript($script);
  }
  
  /**
   * Hook appelé lors de l'upload d'une photo utilisateur
   */
  public function onUserPhotoUpload($event)
  {
    // Pour l'instant, on laisse le comportement normal
    // Pourrait être utilisé pour des fonctionnalités futures
  }
  
  /**
   * Hook appelé après la création d'un item
   */
  public function onItemCreateAfter($event)
  {
    // Pour l'instant, on laisse le comportement normal
    // Pourrait être utilisé pour des fonctionnalités futures
  }
  
  /**
   * Méthode utilitaire pour vérifier si une photo doit être floutée
   */
  public static function shouldBlurPhoto()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    return !($viewer && $viewer->getIdentity());
  }
  
  /**
   * Méthode pour appliquer les classes CSS de floutage à une image
   */
  public static function applyBlurToPhoto($imageHtml, $additionalClasses = '')
  {
    if (!self::shouldBlurPhoto()) {
      return $imageHtml; // Utilisateur connecté, pas de flou
    }
    
    // Ajouter les classes de floutage
    $blurClasses = 'photofloue-blurred photofloue-protected ' . $additionalClasses;
    
    // Modifier le HTML pour ajouter les classes
    if (preg_match('/<([^>]*?)class=[\'"](.*?)[\'"](.*?)>/i', $imageHtml, $matches)) {
      // L'élément a déjà une classe
      $existingClasses = $matches[2];
      $newImageHtml = str_replace(
        'class="' . $existingClasses . '"',
        'class="' . $existingClasses . ' ' . $blurClasses . '"',
        $imageHtml
      );
    } else {
      // L'élément n'a pas de classe, on l'ajoute
      $newImageHtml = preg_replace(
        '/(<[^>]*?)>/i',
        '$1 class="' . $blurClasses . '">',
        $imageHtml,
        1
      );
    }
    
    // Envelopper dans un conteneur avec tooltip si nécessaire
    if (strpos($imageHtml, 'photofloue-container') === false) {
      $view = Zend_Registry::get('Zend_View');
      $tooltipMessage = $view->translate("Connectez-vous pour voir les photos nettes");
      $wrappedHtml = '<div class="photofloue-container" title="' . htmlspecialchars($tooltipMessage) . '">' . $newImageHtml . '</div>';
      return $wrappedHtml;
    }
    
    return $newImageHtml;
  }
  
  /**
   * Vérifier si on est sur la page d'accueil
   */
  protected function _isHomepage($request)
  {
    if (!$request) {
      return false;
    }
    
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    
    // Page d'accueil typique
    if (($module == 'core' || $module == 'default') && 
        $controller == 'index' && 
        $action == 'index') {
      return true;
    }
    
    // Autres cas de page d'accueil
    $requestUri = $request->getRequestUri();
    if ($requestUri == '/' || $requestUri == '/index' || $requestUri == '/home') {
      return true;
    }
    
    return false;
  }
  
  /**
   * Method called before dispatching
   */
  public function preDispatch(Zend_Controller_Request_Abstract $request)
  {
    // Ajouter des classes CSS au body pour identifier le statut
    $view = Zend_Registry::get('Zend_View');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if ($viewer && $viewer->getIdentity()) {
      $view->headScript()->appendScript("
        document.addEventListener('DOMContentLoaded', function() {
          document.body.classList.add('photofloue-user-logged-in');
          document.body.classList.remove('photofloue-user-not-logged-in');
        });
      ");
    } else {
      $view->headScript()->appendScript("
        document.addEventListener('DOMContentLoaded', function() {
          document.body.classList.add('photofloue-user-not-logged-in');
          document.body.classList.remove('photofloue-user-logged-in');
        });
      ");
    }
  }
}