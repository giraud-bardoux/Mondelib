<?php
/**
 * PhotoBlur Module for SocialEngine 7.4 - Installer
 *
 * @category   Application_Extensions
 * @package    PhotoBlur
 * @copyright  Copyright 2024
 * @license    Custom License
 */

class PhotoBlur_Installer extends Engine_Package_Installer_Module
{
  protected $_hasSettings = true;
  
  /**
   * Installation du module PhotoBlur
   */
  public function onInstall()
  {
    parent::onInstall();
    
    // Créer les paramètres par défaut
    $this->_createSettings();
    
    return $this;
  }
  
  /**
   * Mise à jour du module
   */
  public function onUpgrade($previousVersion)
  {
    parent::onUpgrade($previousVersion);
    
    // Logique de mise à jour si nécessaire
    
    return $this;
  }
  
  /**
   * Désinstallation du module
   */
  public function onUninstall()
  {
    parent::onUninstall();
    
    // Nettoyer les paramètres
    $this->_removeSettings();
    
    return $this;
  }
  
  /**
   * Activation du module
   */
  public function onEnable()
  {
    parent::onEnable();
    
    // Vérifier les dépendances
    $this->_checkDependencies();
    
    return $this;
  }
  
  /**
   * Désactivation du module
   */
  public function onDisable()
  {
    parent::onDisable();
    
    return $this;
  }
  
  /**
   * Création des paramètres par défaut
   */
  protected function _createSettings()
  {
    $db = Engine_Db_Table::getDefaultAdapter();
    
    // Paramètres par défaut du module
    $settings = array(
      'photoblur.enabled' => array(
        'value' => 1,
        'description' => 'Activer le module de floutage des photos'
      ),
      'photoblur.blur_intensity' => array(
        'value' => 10,
        'description' => 'Intensité du flou en pixels (1-20)'
      ),
      'photoblur.protection_level' => array(
        'value' => 'high',
        'description' => 'Niveau de protection (low, medium, high)'
      ),
      'photoblur.apply_to_users' => array(
        'value' => 1,
        'description' => 'Appliquer le flou aux photos des utilisateurs'
      ),
      'photoblur.apply_to_albums' => array(
        'value' => 1,
        'description' => 'Appliquer le flou aux photos d\'albums'
      ),
      'photoblur.mobile_protection' => array(
        'value' => 1,
        'description' => 'Activer la protection mobile renforcée'
      ),
      'photoblur.login_message' => array(
        'value' => 'Connectez-vous pour voir les photos nettes',
        'description' => 'Message affiché au survol des photos floues'
      ),
      'photoblur.show_tooltips' => array(
        'value' => 1,
        'description' => 'Afficher les tooltips explicatifs'
      ),
      'photoblur.devtools_detection' => array(
        'value' => 1,
        'description' => 'Détecter l\'ouverture des outils de développement'
      ),
      'photoblur.print_protection' => array(
        'value' => 1,
        'description' => 'Empêcher l\'impression des photos protégées'
      )
    );
    
    foreach ($settings as $name => $config) {
      // Vérifier si le paramètre existe déjà
      $select = $db->select()
        ->from('engine4_core_settings', 'COUNT(*) as count')
        ->where('name = ?', $name);
      
      $exists = $db->fetchOne($select);
      
      if (!$exists) {
        $db->insert('engine4_core_settings', array(
          'name' => $name,
          'value' => $config['value']
        ));
      }
    }
    
    // Vérifier que les modules dépendants sont activés
    $this->_ensureDependenciesEnabled();
  }
  
  /**
   * Suppression des paramètres
   */
  protected function _removeSettings()
  {
    $db = Engine_Db_Table::getDefaultAdapter();
    
    // Supprimer tous les paramètres du module
    $db->delete('engine4_core_settings', array(
      'name LIKE ?' => 'photoblur.%'
    ));
  }
  
  /**
   * Vérifier les dépendances du module
   */
  protected function _checkDependencies()
  {
    $requiredModules = array('core', 'user', 'album');
    
    foreach ($requiredModules as $moduleName) {
      if (!Engine_Api::_()->hasModuleBootstrap($moduleName)) {
        throw new Engine_Exception(sprintf(
          'Le module PhotoBlur nécessite le module "%s" qui n\'est pas disponible.',
          $moduleName
        ));
      }
    }
  }
  
  /**
   * S'assurer que les modules dépendants sont activés
   */
  protected function _ensureDependenciesEnabled()
  {
    $db = Engine_Db_Table::getDefaultAdapter();
    
    $requiredModules = array('user', 'album');
    
    foreach ($requiredModules as $moduleName) {
      // Vérifier si le module est activé
      $select = $db->select()
        ->from('engine4_core_modules', 'enabled')
        ->where('name = ?', $moduleName);
      
      $enabled = $db->fetchOne($select);
      
      if ($enabled === null) {
        throw new Engine_Exception(sprintf(
          'Le module "%s" n\'est pas installé. PhotoBlur nécessite ce module.',
          $moduleName
        ));
      }
      
      if (!$enabled) {
        // Logger un avertissement
        Engine_Api::_()->getApi('response', 'core')->setRedirect(null, array(
          'messages' => array(sprintf(
            'Attention : Le module "%s" est désactivé. PhotoBlur peut ne pas fonctionner correctement.',
            $moduleName
          ))
        ));
      }
    }
  }
  
  /**
   * Valider la configuration du module
   */
  protected function _validateConfiguration()
  {
    // Vérifier que les répertoires nécessaires existent
    $directories = array(
      APPLICATION_PATH . '/modules/PhotoBlur/externals/styles',
      APPLICATION_PATH . '/modules/PhotoBlur/externals/scripts',
    );
    
    foreach ($directories as $dir) {
      if (!is_dir($dir)) {
        throw new Engine_Exception(sprintf(
          'Le répertoire requis "%s" n\'existe pas.',
          $dir
        ));
      }
    }
    
    // Vérifier que les fichiers CSS et JS existent
    $files = array(
      APPLICATION_PATH . '/modules/PhotoBlur/externals/styles/photoblur.css',
      APPLICATION_PATH . '/modules/PhotoBlur/externals/scripts/photoblur.js',
    );
    
    foreach ($files as $file) {
      if (!file_exists($file)) {
        throw new Engine_Exception(sprintf(
          'Le fichier requis "%s" n\'existe pas.',
          $file
        ));
      }
    }
    
    return true;
  }
  
  /**
   * Post-installation : vérifications et optimisations
   */
  protected function _postInstall()
  {
    // Valider la configuration
    $this->_validateConfiguration();
    
    // Nettoyer le cache si nécessaire
    if (Engine_Api::_()->hasModuleBootstrap('core')) {
      $cache = Engine_Api::_()->getApi('cache', 'core');
      if ($cache) {
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
      }
    }
    
    return true;
  }
}