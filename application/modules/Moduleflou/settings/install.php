<?php
class Moduleflou_Installer extends Engine_Package_Installer_Module
{
  public function onInstall()
  {
    // Hook future migrations if needed.
    parent::onInstall();
  }
}
