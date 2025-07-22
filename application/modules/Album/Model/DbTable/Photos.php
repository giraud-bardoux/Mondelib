<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Photos.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Model_DbTable_Photos extends Core_Model_Item_DbTable_Abstract
{
  protected $_rowClass = 'Album_Model_Photo';
  
  protected $_temporyPath = DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR . 'temporary'. DIRECTORY_SEPARATOR .'album_photos';
  
  public function getPhotoSelect(array $params)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    
    $tablePhotoName = $this->info('name');
    $tableAlbum = Engine_Api::_()->getItemTable('album');
    $tableAlbumName = $tableAlbum->info('name');
    $select = $this->select()
      ->from($this->info('name'));
      
    if(isset($params['action']) && $params['action'] != 'browsephoto') {
      $select->where($tablePhotoName.".parent_type NOT IN ('activity_action') OR ".$tablePhotoName.".parent_type IS NULL");
    }
    
//     if(isset($params['albumview']) && !empty($params['albumview']) && $viewer_id != $params['owner_id']) {
//       $select->where($tablePhotoName.'.approved =?', 1);
//     }
    if( !empty($params['album']) && $params['album'] instanceof Album_Model_Album ) {
      $select->where($tablePhotoName.'.album_id = ?', $params['album']->getIdentity());
    } else if( !empty($params['album_id']) && is_numeric($params['album_id']) ) {
      $select->where($tablePhotoName.'.album_id = ?', $params['album_id']);
    } else if (!empty($params['album_ids']) && is_array($params['album_ids'])) {
      $select->where($tablePhotoName.'.album_id IN (?)', $params['album_ids']);
    }
    if(empty($params['showprivatephoto'])) {
      $select->where($tableAlbum->select()
        ->from($tableAlbumName,new Zend_Db_Expr('COUNT(*) > 0'))->where($tableAlbumName.".album_id = ".$tablePhotoName.".album_id")->where($tableAlbumName.".type NOT IN ('group','event') OR ".$tableAlbumName.".type IS NULL"));
    }
    
    if(isset($params['action']) && $params['action'] == 'browsephoto') {
      //$select->where($tablePhotoName.'.approved =?', 1);
      $select->where("CASE WHEN " .$tablePhotoName .".owner_id = '".$viewer_id."' THEN true ELSE ".$tablePhotoName.".approved = 1 END ");
    }
    
    if(!$viewer->isAdmin() && $viewer->getIdentity()) {
      $select->setIntegrityCheck(false)->joinLeft($tableAlbumName, "$tableAlbumName.album_id = $tablePhotoName.album_id", NULL);
      $select->where("CASE WHEN " .$tablePhotoName .".owner_id = '".$viewer_id."' THEN true ELSE ".$tableAlbumName.".approved = 1 END ");
    } else if(!$viewer->getIdentity()) {
      $select->setIntegrityCheck(false)->joinLeft($tableAlbumName, "$tableAlbumName.album_id = $tablePhotoName.album_id", NULL);
      $select->where($tableAlbumName.'.approved =?', 1);
    }

    if(isset($params['albumvieworder'])) {
      if($params['albumvieworder'] == 'newest')
        $select->order('photo_id DESC');
      else if($params['albumvieworder'] == 'oldest')
        $select->order('photo_id ASC');
      else
        $select->order('order ASC');
    } else {

			if(!empty($params['order']) && $params['order'] == 'atoz') {
				$select->order($tablePhotoName .'.title ASC');
			} else if(!empty($params['order']) && $params['order'] == 'ztoa') {
				$select->order($tablePhotoName .'.title DESC');
			} else  {
				$select->order( !empty($params['order']) ? $tablePhotoName.'.'.$params['order'].' DESC' : $tablePhotoName.'.creation_date DESC' );
			}
    }

    if (!empty($params['search'])) {
      $select->where($tablePhotoName.'.title LIKE ? OR '.$tablePhotoName.'. description LIKE ?', $params['search'] . '%');
    }

    if(!empty($params['tag'])) {
      $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
      $tmName = $tmTable->info('name');
      $rName = $this->info('name');
      $select
        ->joinLeft($tmName, "$tmName.resource_id = $rName.photo_id", NULL)
        ->where($tmName.'.resource_type = ?', 'album_photo')
        ->where($tmName.'.tag_id = ?', $params['tag']);
    }

    if(isset($params['action']) && $params['action'] == 'browsephoto') {
      $select->where($tablePhotoName.'.feedupload =?', 0);
    }

    return $select;
  }
  
  public function getPhotoPaginator(array $params)
  {
    return Zend_Paginator::factory($this->getPhotoSelect($params));
  }
  public function uploadTemPhoto($photo){
    if( $photo instanceof Zend_Form_Element_File ) {
        $file = $photo->getFileName();
        $fileName = $file;
    } else if( $photo instanceof Storage_Model_File ) {
        $file = $photo->temporary();
        $fileName = $photo->name;
    } else if( $photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id) ) {
        $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $file = $tmpRow->temporary();
        $fileName = $tmpRow->name;
    } else if( is_array($photo) && !empty($photo['tmp_name']) ) {
        $file = $photo['tmp_name'];
        $fileName = $photo['name'];
    } else if( is_string($photo) && file_exists($photo) ) {
        $file = $photo;
        $fileName = $photo;
    } else {
        throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    if (!is_dir(APPLICATION_PATH.$this->_temporyPath)) {
      mkdir(APPLICATION_PATH.$this->_temporyPath, 0777, true);
    }
    $uploadFileName = md5(time().rand(1,19234876)).'.'.$extension;
    $uploadFilePath = $this->_temporyPath.DIRECTORY_SEPARATOR.$uploadFileName;
    if(copy($file,APPLICATION_PATH.$uploadFilePath)){
      return base64_encode($uploadFileName);
    }
    return false;       
  }

  public function getTemPhoto($photoId,$fullPath = 0){
    $filePath = $this->_temporyPath.DIRECTORY_SEPARATOR.base64_decode($photoId);
    if(file_exists(APPLICATION_PATH.$filePath)){
      if($fullPath){
        return APPLICATION_PATH.$filePath;
      }
      return str_replace('\\','/',$filePath);
    }
    return false;
  }

  public function fetchWallPhotos($params = array()) {
    $select = $this->select()
                  ->from($this->info('name'))
                  ->where('feedupload = ?', $params['feedupload']);
    return $this->fetchAll($select);
  }
}
