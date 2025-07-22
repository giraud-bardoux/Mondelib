<?php
/**
 * PhotoFloue Module for SocialEngine 7.4 - ItemBackgroundPhoto Helper
 *
 * @category   Application_Extensions
 * @package    PhotoFloue
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoFloue_View_Helper_ItemBackgroundPhoto extends Engine_View_Helper_ItemBackgroundPhoto
{
  /**
   * Surcharge de la méthode itemBackgroundPhoto pour appliquer le floutage
   */
  public function itemBackgroundPhoto($item, $type = 'thumb.normal', $title = '', $attribs = array(), $escape = true)
  {
    // Obtenir le HTML de l'image depuis la méthode parent
    $originalHtml = parent::itemBackgroundPhoto($item, $type, $title, $attribs, $escape);
    
    // Vérifier si l'utilisateur est connecté
    $viewer = Engine_Api::_()->user()->getViewer();
    $isLoggedIn = $viewer && $viewer->getIdentity();
    
    // Si l'utilisateur n'est pas connecté et que c'est un item à flouter
    if (!$isLoggedIn && $this->_shouldBlurItem($item)) {
      // Appliquer le floutage via le plugin
      $originalHtml = PhotoFloue_Plugin_Core::applyBlurToPhoto($originalHtml, 'photofloue-background-photo');
    }
    
    return $originalHtml;
  }
  
  /**
   * Détermine si l'item doit être flouté
   */
  protected function _shouldBlurItem($item)
  {
    if (!$item) {
      return false;
    }
    
    // Obtenir le type d'item
    $itemType = $item->getType();
    
    // Flouter les photos des utilisateurs
    if ($itemType === 'user') {
      return true;
    }
    
    // Flouter les photos d'albums
    if (in_array($itemType, array('album', 'album_photo'))) {
      return true;
    }
    
    // Flouter les fichiers de storage (photos de profil, etc.)
    if ($itemType === 'storage_file') {
      return true;
    }
    
    // Vérifier si l'item a une relation avec des photos
    if (method_exists($item, 'getPhotoUrl') || method_exists($item, 'getPhoto')) {
      return true;
    }
    
    return false;
  }
}