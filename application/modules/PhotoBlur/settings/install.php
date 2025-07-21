<?php
/**
 * PhotoBlur Module - Installation
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_Installer extends Engine_Package_Installer_Module
{
  /**
   * Installation du module
   */
  function onInstall()
  {
    // Créer les settings du module
    $this->_createSettings();
    
    // Message de succès
    $this->_log('PhotoBlur module installed successfully');
    
    parent::onInstall();
  }
  
  /**
   * Mise à jour du module
   */
  function onUpgrade()
  {
    // Gérer les mises à jour futures
    parent::onUpgrade();
  }
  
  /**
   * Désinstallation du module
   */
  function onUninstall()
  {
    // Nettoyer les settings
    $this->_removeSettings();
    
    parent::onUninstall();
  }
  
  /**
   * Activation du module
   */
  function onEnable()
  {
    parent::onEnable();
  }
  
  /**
   * Désactivation du module
   */
  function onDisable()
  {
    parent::onDisable();
  }
  
  /**
   * Créer les paramètres du module
   */
  protected function _createSettings()
  {
    $db = $this->getDb();
    
    // Vérifier si les settings existent déjà
    $select = $db->select()
      ->from('engine4_core_settings')
      ->where('name LIKE ?', 'photoblur.%');
    $existing = $db->fetchAll($select);
    
    if (empty($existing)) {
      // Paramètres par défaut du module
      $settings = array(
        'photoblur.enabled' => 1,
        'photoblur.blur_intensity' => 10,
        'photoblur.protect_screenshots' => 1,
        'photoblur.protect_mobile' => 1,
        'photoblur.show_login_message' => 1,
        'photoblur.login_message' => 'Connectez-vous pour ne plus voir flou'
      );
      
      foreach ($settings as $name => $value) {
        $db->insert('engine4_core_settings', array(
          'name' => $name,
          'value' => $value
        ));
      }
    }
  }
  
  /**
   * Supprimer les paramètres du module
   */
  protected function _removeSettings()
  {
    $db = $this->getDb();
    
    $db->delete('engine4_core_settings', array(
      'name LIKE ?' => 'photoblur.%'
    ));
  }
  
  /**
   * Logger un message
   */
  protected function _log($message)
  {
    if (APPLICATION_ENV === 'development') {
      error_log('[PhotoBlur] ' . $message);
    }
  }
}