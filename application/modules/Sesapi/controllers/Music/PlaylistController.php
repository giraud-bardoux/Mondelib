<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PlaylistController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Music_PlaylistController extends Sesapi_Controller_Action_Standard {

  public function init()
  {
    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('music_playlist', null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }

    // Get viewer info
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id  = Engine_Api::_()->user()->getViewer()->getIdentity();

    // Get subject
    if( null !== ($playlist_id = $this->_getParam('playlist_id')) && null !== ($playlist = Engine_Api::_()->getItem('music_playlist', $playlist_id)) && $playlist instanceof Music_Model_Playlist && !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->core()->setSubject($playlist);
    }
  }
  
  public function viewAction() {

    if(!Engine_Api::_()->core()->hasSubject()){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
    }  
    
    $playlist = Engine_Api::_()->core()->getSubject();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    //Songs Data
    $response_songs = $this->getSongs($playlist);

    $viewer = Engine_Api::_()->user()->getViewer();
    $response = $playlist->toArray(); 
    $response['user_title'] = $playlist->getOwner()->getTitle();
    if($viewer->getIdentity()){
      $menuoptions= array();
      $canEdit = $this->_helper->requireAuth()->setAuthParams($playlist, null, 'edit')->isValid();
      $counterMenu = 0;
      if($canEdit){
        $menuoptions[$counterMenu]['name'] = "edit";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
        $counterMenu++;
      }
      $canDelete = $this->_helper->requireAuth()->setAuthParams($playlist, null, 'delete')->isValid();
      if($canDelete){
        $menuoptions[$counterMenu]['name'] = "delete";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
        $counterMenu++;
      }
      if(!$playlist->isOwner($viewer)){
        $menuoptions[$counterMenu]['name'] = "report";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Music");
      }
      $response['menus'] = $menuoptions;
    }
    
    if($playlist->photo_id)
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($playlist,'','');
    else {
      $images = array('main' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'icon' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_thumb_icon.png'),'normal' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'profile' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'));
    }
    if(!engine_count($images))
      $images['main'] = $this->getBaseUrl(true,$playlist->getPhotoUrl());
    $response['images'] = $images;
    
    // Increment view count
    if( !$viewer->isSelf($playlist->getOwner()) ) {
      $playlist->view_count++;
      $playlist->save();
    }
    
    $photo = $this->getBaseUrl(false,$playlist->getPhotoUrl());
    if($photo)
      $response["share"]["imageUrl"] = $photo;
			$response["share"]["url"] = $this->getBaseUrl(false,$playlist->getHref());
      $response["share"]["title"] = $playlist->getTitle();
      $response["share"]["description"] = strip_tags($playlist->getDescription());
      $response["share"]['urlParams'] = array(
          "type" => $playlist->getType(),
          "id" => $playlist->getIdentity()
      );
    if(is_null($response["share"]["title"]))
      unset($response["share"]["title"]);

		$response['resource_type'] = $playlist->getType();
		
		$response['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'music')->checkRated($playlist->getIdentity(), $viewer->getIdentity());
		$response['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('music.enable.rating', 1);
		$response['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('music.ratingicon', 'fas fa-star');
		
    if( !empty($playlist->category_id) ) {
      $category = Engine_Api::_()->getItem('music_category', $playlist->category_id);
      $response['category_title'] = $category->category_name;
			if( !empty($playlist->subcat_id) ) {
				$category = Engine_Api::_()->getItem('music_category', $playlist->subcat_id);
				$response['subcategory_title'] = $category->category_name;
			}
			if( !empty($playlist->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('music_category', $playlist->subsubcat_id);
				$response['subsubcategory_title'] = $category->category_name;
			}
    }
		
    //$response['songs'] = $response_songs;
    $result['playlist'] = $response;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No songs created yet.', 'result' => array_merge($result, $response_songs))); 
  }
  
  function getSongs($playlist) {
  
    $result = array();
    $counterLoop = 0;
    $songs =  $playlist->getSongs();
    foreach($songs as $song) {
      $parent = $song->getParent();
      if(!$parent){
        continue;  
      }
      if($playlist->photo_id)
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($playlist,'','');
      else {
        $images = array('main' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'icon' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_thumb_icon.png'),'normal' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'),'profile' => $this->getBaseUrl(true, 'application/modules/Music/externals/images/nophoto_playlist_main.png'));
      }
      if(!engine_count($images))
        $images['main'] = $this->getBaseUrl(true,$playlist->getPhotoUrl());
      $album['images'] = $images;
      $album = $song->toArray();
      $album['owner_id'] = $parent->getIdentity();
      $album['user_title'] = $parent->getOwner()->getTitle();
      $album['resource_type'] = $song->getType();
      $URL = $this->getBaseUrl(false,$song->getFilePath());
      if(!empty($URL))
        $album['song_url'] = $URL;
      $result['songs'][$counterLoop] = $album;
      $counterLoop++;  
    }

    return $result;
  }
}
