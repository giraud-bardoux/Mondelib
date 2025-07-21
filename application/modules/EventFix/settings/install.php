<?php
/**
 * EventFix Module
 * Installation file
 *
 * @category   Application_EventFix
 * @package    EventFix
 * @author     EventFix Plugin
 * @version    1.0.0
 */

class EventFix_Install extends Engine_Package_Installer_Module
{
    public function onInstall()
    {
        parent::onInstall();
        $this->_packageInstall();
    }

    public function onEnable()
    {
        parent::onEnable();
        return $this;
    }

    public function onDisable()
    {
        parent::onDisable();
        return $this;
    }

    public function onUninstall()
    {
        parent::onUninstall();
        $this->_packageUninstall();
    }

    public function onRefresh()
    {
        parent::onRefresh();
        return $this;
    }

    protected function _packageInstall()
    {
        // Aucune table de base de données requise pour ce plugin
        return $this;
    }

    protected function _packageUninstall()
    {
        // Aucune table de base de données à supprimer
        return $this;
    }
}