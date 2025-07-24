<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_Installer extends Engine_Package_Installer_Module
{
  public function onInstall()
  {
    $this->_addPhotoBlurTable();
    parent::onInstall();
  }

  protected function _addPhotoBlurTable()
  {
    $db = $this->getDb();
    $sql = "
    CREATE TABLE IF NOT EXISTS `engine4_photoblur_blurs` (
      `blur_id` int(11) unsigned NOT NULL auto_increment,
      `photo_id` int(11) unsigned NOT NULL,
      `user_id` int(11) unsigned NOT NULL,
      `original_file_id` int(11) unsigned NOT NULL,
      `blurred_file_id` int(11) unsigned NOT NULL,
      `blur_level` tinyint(1) NOT NULL default '5',
      `creation_date` datetime NOT NULL,
      `modified_date` datetime NOT NULL,
      PRIMARY KEY (`blur_id`),
      KEY `photo_id` (`photo_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;
    ";
    
    try {
      $db->query($sql);
    } catch (Exception $e) {
      // Table already exists
    }
  }
}