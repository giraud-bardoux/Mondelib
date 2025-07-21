<?php
/**
 * PhotoBlur Module - ItemBackgroundPhoto Helper
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_View_Helper_ItemBackgroundPhoto extends Engine_View_Helper_ItemBackgroundPhoto
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
    
    // Si l'utilisateur n'est pas connecté et que c'est une photo d'utilisateur ou d'album
    if (!$isLoggedIn && $this->_shouldBlurItem($item)) {
      // Appliquer les classes de floutage
      $originalHtml = PhotoBlur_Plugin_Core::applyBlurClasses($originalHtml, 'photoblur-background-photo');
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
    
    // Vérifier le type d'item
    $itemType = $item->getType();
    
    // Flouter les photos des utilisateurs
    if ($itemType === 'user') {
      return true;
    }
    
    // Flouter les photos d'album/galerie
    if (in_array($itemType, array('album', 'album_photo', 'storage_file'))) {
      return true;
    }
    
    // Vérifier si l'item a une photo associée
    if (method_exists($item, 'getPhotoUrl') || method_exists($item, 'getPhoto')) {
      return true;
    }
    
    return false;
  }
}