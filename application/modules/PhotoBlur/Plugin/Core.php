<?php
/**
 * PhotoBlur Module - Core Plugin
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_Plugin_Core
{
  /**
   * Hook appelé lors du rendu du layout par défaut
   * Injecte le CSS et JavaScript nécessaires
   */
  public function onRenderLayoutDefault($event)
  {
    $view = Zend_Registry::get('Zend_View');
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Vérifier si l'utilisateur est connecté
    $isLoggedIn = $viewer && $viewer->getIdentity();
    
    // Ajouter une variable JavaScript globale pour indiquer le statut de connexion
    $script = "
    <script type='text/javascript'>
      window.PHOTOBLUR_USER_LOGGED_IN = " . ($isLoggedIn ? 'true' : 'false') . ";
      window.PHOTOBLUR_LOGIN_MESSAGE = '" . $view->translate("Connectez-vous pour ne plus voir flou") . "';
    </script>";
    
    $view->headScript()->appendScript($script);
  }
  
  /**
   * Hook personnalisé pour traiter le rendu des photos
   * Ce hook sera appelé depuis les helpers de vue modifiés
   */
  public function onItemPhotoRender($event)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $isLoggedIn = $viewer && $viewer->getIdentity();
    
    // Si l'utilisateur n'est pas connecté, marquer la photo pour floutage
    if (!$isLoggedIn) {
      $event->setParam('blur_photo', true);
    }
    
    return $event;
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
  public static function applyBlurClasses($htmlImg, $additionalClasses = '')
  {
    if (self::shouldBlurPhoto()) {
      // Ajouter les classes de floutage et de protection
      $classes = 'photoblur-blurred photoblur-protected ' . $additionalClasses;
      
      // Modifier le HTML de l'image pour ajouter les classes
      if (preg_match('/<img([^>]*?)class=[\'"](.*?)[\'"](.*?)>/i', $htmlImg, $matches)) {
        $existingClasses = $matches[2];
        $newHtml = str_replace(
          'class="' . $existingClasses . '"',
          'class="' . $existingClasses . ' ' . $classes . '"',
          $htmlImg
        );
      } else if (preg_match('/<img([^>]*?)>/i', $htmlImg, $matches)) {
        $newHtml = str_replace(
          '<img' . $matches[1] . '>',
          '<img' . $matches[1] . ' class="' . $classes . '">',
          $htmlImg
        );
      } else {
        $newHtml = $htmlImg;
      }
      
      // Envelopper dans un conteneur avec tooltip
      $tooltipMessage = Zend_Registry::get('Zend_View')->translate("Connectez-vous pour ne plus voir flou");
      $wrappedHtml = '<div class="photoblur-container" title="' . $tooltipMessage . '">' . $newHtml . '</div>';
      
      return $wrappedHtml;
    }
    
    return $htmlImg;
  }
}