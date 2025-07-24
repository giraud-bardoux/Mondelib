<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_Model_DbTable_Blurs extends Engine_Db_Table
{
  protected $_rowClass = 'Photoblur_Model_Blur';
  protected $_name = 'photoblur_blurs';
  
  public function getBlursByPhoto($photo_id)
  {
    return $this->fetchAll($this->select()->where('photo_id = ?', $photo_id)->order('creation_date DESC'));
  }
  
  public function getBlursByUser($user_id)
  {
    return $this->fetchAll($this->select()->where('user_id = ?', $user_id)->order('creation_date DESC'));
  }
}