<?php
/**
 * PhotoFloue Module v1.3.0 - Installer
 * Installation simplifiée et robuste pour SocialEngine 7.4
 */

class PhotoFloue_Installer extends Engine_Package_Installer_Module
{
    /**
     * Installation du module
     */
    public function onInstall()
    {
        $this->_checkEnvironment();
        $this->_createSettings();
        
        // Message de succès
        $this->_database->insert('engine4_core_content', array(
            'type' => 'container',
            'name' => 'photofloue-install-success',
            'page_id' => 1,
            'content_id' => 1,
            'parent_content_id' => null,
            'order' => 999,
            'params' => '{}',
            'attribs' => '{"title":"PhotoFloue v1.3.0 installé avec succès","description":"Module de floutage des photos activé"}'
        ));
        
        parent::onInstall();
    }

    /**
     * Mise à jour du module
     */
    public function onUpgrade($oldVersion)
    {
        $this->_updateSettings();
        
        // Nettoyage des anciennes versions
        if (version_compare($oldVersion, '1.3.0', '<')) {
            $this->_cleanupOldVersion();
        }
        
        parent::onUpgrade($oldVersion);
    }

    /**
     * Désinstallation du module
     */
    public function onUninstall()
    {
        $this->_removeSettings();
        $this->_removeContent();
        
        parent::onUninstall();
    }

    /**
     * Activation du module
     */
    public function onEnable()
    {
        // Simple activation, pas de hooks à enregistrer
        parent::onEnable();
    }

    /**
     * Désactivation du module
     */
    public function onDisable()
    {
        // Simple désactivation
        parent::onDisable();
    }

    /**
     * Vérification de l'environnement
     */
    protected function _checkEnvironment()
    {
        // Vérifier PHP version
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            throw new Engine_Package_Installer_Exception('PhotoFloue nécessite PHP 7.0 ou supérieur');
        }

        // Vérifier SocialEngine version
        $coreVersion = $this->_getModuleVersion('core');
        if (version_compare($coreVersion, '7.4.0', '<')) {
            throw new Engine_Package_Installer_Exception('PhotoFloue nécessite SocialEngine 7.4 ou supérieur');
        }

        // Vérifier que le module User est présent
        if (!$this->_isModuleEnabled('user')) {
            throw new Engine_Package_Installer_Exception('Le module User doit être activé');
        }

        // Vérifier les répertoires
        $paths = array(
            APPLICATION_PATH . '/modules/PhotoFloue',
            APPLICATION_PATH . '/modules/PhotoFloue/externals/styles',
            APPLICATION_PATH . '/modules/PhotoFloue/externals/scripts'
        );

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                throw new Engine_Package_Installer_Exception("Répertoire manquant: {$path}");
            }
        }

        // Vérifier les fichiers essentiels
        $files = array(
            APPLICATION_PATH . '/modules/PhotoFloue/externals/styles/photofloue.css',
            APPLICATION_PATH . '/modules/PhotoFloue/externals/scripts/photofloue.js'
        );

        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new Engine_Package_Installer_Exception("Fichier manquant: {$file}");
            }
        }
    }

    /**
     * Création des paramètres
     */
    protected function _createSettings()
    {
        $settings = array(
            'photofloue.enabled' => array(
                'type' => 'select',
                'value' => 1,
                'label' => 'Activer le floutage',
                'description' => 'Active ou désactive le floutage des photos',
                'options' => '{"1":"Activé","0":"Désactivé"}'
            ),
            'photofloue.blur_intensity' => array(
                'type' => 'text',
                'value' => 10,
                'label' => 'Intensité du flou (px)',
                'description' => 'Intensité du flou appliqué aux photos (1-20)'
            ),
            'photofloue.protection_enabled' => array(
                'type' => 'select', 
                'value' => 1,
                'label' => 'Activer la protection',
                'description' => 'Active la protection anti-capture (clic droit, clavier)',
                'options' => '{"1":"Activé","0":"Désactivé"}'
            ),
            'photofloue.mobile_protection' => array(
                'type' => 'select',
                'value' => 1, 
                'label' => 'Protection mobile',
                'description' => 'Active la protection sur appareils mobiles',
                'options' => '{"1":"Activé","0":"Désactivé"}'
            ),
            'photofloue.login_message' => array(
                'type' => 'text',
                'value' => 'Connectez-vous pour voir les photos nettes',
                'label' => 'Message d\'incitation',
                'description' => 'Message affiché au survol des photos floutées'
            )
        );

        foreach ($settings as $name => $setting) {
            $this->_database->insert('engine4_core_settings', array(
                'name' => $name,
                'value' => $setting['value'],
                'type' => $setting['type'],
                'label' => $setting['label'],
                'description' => $setting['description'],
                'options' => isset($setting['options']) ? $setting['options'] : null
            ));
        }
    }

    /**
     * Mise à jour des paramètres
     */
    protected function _updateSettings()
    {
        // Vérifier et ajouter les nouveaux paramètres s'ils n'existent pas
        $newSettings = array(
            'photofloue.version' => '1.3.0'
        );

        foreach ($newSettings as $name => $value) {
            $exists = $this->_database->select()
                ->from('engine4_core_settings', 'name')
                ->where('name = ?', $name)
                ->query()
                ->fetchColumn();

            if (!$exists) {
                $this->_database->insert('engine4_core_settings', array(
                    'name' => $name,
                    'value' => $value
                ));
            }
        }
    }

    /**
     * Suppression des paramètres
     */
    protected function _removeSettings()
    {
        $this->_database->delete('engine4_core_settings', array(
            'name LIKE ?' => 'photofloue.%'
        ));
    }

    /**
     * Suppression du contenu
     */
    protected function _removeContent()
    {
        $this->_database->delete('engine4_core_content', array(
            'name LIKE ?' => 'photofloue-%'
        ));
    }

    /**
     * Nettoyage des anciennes versions
     */
    protected function _cleanupOldVersion()
    {
        // Supprimer les anciens hooks qui causaient des problèmes
        $this->_database->delete('engine4_core_hooks', array(
            'name LIKE ?' => 'photofloue_%'
        ));

        // Nettoyer les anciens paramètres incompatibles
        $oldSettings = array('photoblur.%');
        foreach ($oldSettings as $pattern) {
            $this->_database->delete('engine4_core_settings', array(
                'name LIKE ?' => $pattern
            ));
        }
    }

    /**
     * Vérifier si un module est activé
     */
    protected function _isModuleEnabled($moduleName)
    {
        $enabled = $this->_database->select()
            ->from('engine4_core_modules', 'enabled')
            ->where('name = ?', $moduleName)
            ->query()
            ->fetchColumn();

        return (bool) $enabled;
    }

    /**
     * Récupérer la version d'un module
     */
    protected function _getModuleVersion($moduleName)
    {
        $version = $this->_database->select()
            ->from('engine4_core_modules', 'version')
            ->where('name = ?', $moduleName)
            ->query()
            ->fetchColumn();

        return $version ?: '0.0.0';
    }
}
?>