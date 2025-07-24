<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_Model_Blur extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'photoblur_photo',
      'action' => 'download',
      'photo_id' => $this->photo_id,
      'blur_id' => $this->blur_id,
    ), $params);
    $route = $params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }
  
  public function getPhoto()
  {
    return Engine_Api::_()->getItem('album_photo', $this->photo_id);
  }
  
  public function getOwner()
  {
    return Engine_Api::_()->getItem('user', $this->user_id);
  }
  
  public function getBlurredFile()
  {
    return Engine_Api::_()->getItem('storage_file', $this->blurred_file_id);
  }
  
  public function getOriginalFile()
  {
    return Engine_Api::_()->getItem('storage_file', $this->original_file_id);
  }
}