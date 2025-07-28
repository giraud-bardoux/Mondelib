<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PhotoController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Album_PhotoController extends Sesapi_Controller_Action_Standard {

	//photo constructor function
  public function init() {
  
		if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) && null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id))) {
      Engine_Api::_()->core()->setSubject($photo);
    }
    
    if (strpos($_SERVER['REQUEST_URI'], 'get-photos') === false && strpos($_SERVER['REQUEST_URI'], 'o/like') === false) {
      if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));     
    }
  }
  
  function getPhotosAction() {
  
    $album_id = $this->_getParam('album_id',0);
    if(!$album_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
      
    $table = Engine_Api::_()->getItemTable('photo');
    $select = $table->select()->from($table)->where('album_id =?',$album_id);
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit', 20));
    $paginator->setCurrentPageNumber( $this->_getParam('page'));
    $result = $this->getPhotos($paginator);
  }
  
  public function getPhotos($paginator) {
  
    $result = array();
    $counter = 0;
    
    foreach($paginator as $photos) {
    
      $photo = $photos->toArray();
      if($photo)
        $album_photo['photos'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");
      else
        continue;
      $result[$counter] = array_merge($photo,$album_photo);
      $counter++;
    }
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $results['photos'] = $result;
    
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No photo created by you yet in this album.'), 'result' => array())); 
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results),$extraParams));
    }
  }
}