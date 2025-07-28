<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Video_IndexController extends Sesapi_Controller_Action_Standard {
  protected $_permission = array();
  protected $_leftvideo ;
	protected $_counterVideoUploaded;
	
  public function init() {
    // only show videos if authorized
    if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    }
    $id = $this->_getParam('video_id', $this->_getParam('id', null));
    if ($id) {
        $video = Engine_Api::_()->getItem('video', $id);
        if ($video) {
            Engine_Api::_()->core()->setSubject($video);
        }
    }
    if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    }
    
    $this->_permission = array('canCreateVideo'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'create'),'watchLater'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.watchlater', 1),'canCreatePlaylist'=>Engine_Api::_()->authorization()->isAllowed('video', null, 'addplaylist_video'),'canCreateChannel'=>Engine_Api::_()->authorization()->isAllowed('sesvideo_chanel', null, 'create'),'canChannelEnable'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_chanel', 1));
  }
  
	public function menuAction() {
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('video_main', array());
		$menu_counter = 0;
		foreach ($menus as $menu) {
			$class = end(explode(' ', $menu->class));
			$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
			$result_menu[$menu_counter]['action'] = $class;
			$result_menu[$menu_counter]['isActive'] = $menu->active;
			$menu_counter++;
		}
		$result['menus'] = $result_menu;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}
  
  public function browseAction() {
  
    // Permissions
    $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();
    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    // Make form
    // Note: this code is duplicated in the video.browse-search widget
    $this->view->form = $form = new Video_Form_Search();
    // Process form
    if ($form->isValid($this->_getAllParams())) {
        $values = $form->getValues();
    } else {
        $values = array();
    }
    $this->view->formValues = $values;
    $search = $this->_getParam('search','');
    if(isset($search) && !empty($search)) {
        $values['text'] = $search;
    }
    $values['status'] = 1;
    $values['search'] = 1;

    if (!empty($values['tag'])) {
        $this->view->tag = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
    }
    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user_id');
    $type = $this->_getParam('type');
    if($type == "manage"){
        $user_id = $viewer->getIdentity();
        $manage = 1;
    }
    $category_id = $this->_getParam('category_id');
    if($category_id)
        $values['category'] = $category_id;
    if ($user_id) {
        $values['user_id'] = $user_id;
    }
    // Get videos
    
    $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);
    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 12);
    $paginator->setItemCountPerPage($items_count);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result['videos'] = $this->getVideos($paginator,@$manage);
		$result["permission"]['canCreateVideo'] = Engine_Api::_()->authorization()->isAllowed('video', null, 'create') ;

    if(!empty($user_id)) {
      $menuoptions= array();
      $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'edit');
      $counter = 0;
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Video"); 
        $counter++;
      }
      $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'delete');
      if($canDelete) {
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Video");
      }
      $result['menus'] = $menuoptions;  
    }
    
    $extraParams = array();
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $extraParams['pagging']['moduleName'] = "core_video";
    
    if(engine_count($result) <= 0) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video uploaded yet.', 'result' => array())); 
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }
  }
  protected function getVideos($paginator,$manage = "") {
  
    $result = array();
    $counter = 0;
    foreach($paginator as $videos) {
    
      $video = $videos->toArray();
      $video["description"] = preg_replace('/\s+/', ' ', $video["description"]);
      $video['user_title'] = $videos->getOwner()->getTitle();
      if($this->view->viewer()->getIdentity() != 0){
        try{
        $video['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($videos);
        $video['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($videos);
        }catch(Exception $e){}
      }        
      if($manage){
         $viewer = Engine_Api::_()->user()->getViewer();
          $menuoptions= array();
          $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'edit');
          $counterMenu = 0;
          if($canEdit){
            $menuoptions[$counterMenu]['name'] = "edit";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit Video"); 
            $counterMenu++;
          }
          $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'delete');
          if($canDelete){
            $menuoptions[$counterMenu]['name'] = "delete";
            $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete Video");
          }
          $video['menus'] = $menuoptions;
          
				$video['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $videos->getIdentity(), 'resource_type' => 'video'));
				$video['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1);
				$video['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratingicon', 'fas fa-star');
      }
      if( $videos->duration >= 3600 ) {
        $duration = gmdate("H:i:s", $videos->duration);
      } else {
        $duration = gmdate("i:s", $videos->duration);
      }
      $video['duration'] = $duration;
      
      $video['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($videos,'',"");
      if(!engine_count($video['images']))
        $video['images']['main'] = $this->getBaseUrl(false,$videos->getPhotoUrl());
     
       if ($videos instanceof Sesvideo_Model_Chanelvideo){
         $videoV = Engine_Api::_()->getItem('video',$videos->video_id);
        if ($videoV->type == 3) {
          if (!empty($videoV->file_id)) {
            $storage_file = Engine_Api::_()->getItem('storage_file', $videoV->file_id);
            $video['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
            $video['video_extension'] = $storage_file->extension;  
          }
        }else{
          $embedded = $videoV->getRichContent(true,array(),'',true);
          
          preg_match('/src="([^"]+)"/', $embedded, $match);
          if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
            $video['iframeURL'] = str_replace('//','https://',$match[1]);
          }else{
            $video['iframeURL'] = $match[1];
          }
        }
       }
      $result[$counter] = array_merge($video,array());
      $counter++;
    }
    return $result;
  }
  
  public function categoryAction() {
 
    $paginator = Engine_Api::_()->video()->getCategories();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {
      if($key == '') continue;
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');
      //Videos Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'videos', 'module_name' => 'video'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s video', '%s videos', $Itemcount), $this->view->locale()->toNumber($Itemcount));
      $counter++;
    }
      $catgeoryArray["permission"]['canCreateVideo'] = Engine_Api::_()->authorization()->isAllowed('video', null, 'create') ;
      $extraParams['pagging']['moduleName'] = "core_video";
    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),$extraParams));
  }
  
  public function viewAction() {
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $video = Engine_Api::_()->core()->getSubject('video');
    if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }
    
    // Network check
		$networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($video, 'owner_id');
		if(empty($networkPrivacy))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
			
    $viewer = Engine_Api::_()->user()->getViewer();
    if($video->status != 1)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("The video you are looking for does not exist or has not been processed yet."), 'result' => array()));
      
    $response = array();
    $response['video'] = $video->toArray();
    $response['video']['description'] = strip_tags($video->getDescription());
    $response['video']['tags'] = $video->tags()->getTagMaps()->toArray();
    if($viewer->getIdentity()){
      $menuoptions= array();
      $canEdit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid();
      $counterMenu = 0;
      if($canEdit){
        $menuoptions[$counterMenu]['name'] = "edit";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Edit"); 
        $counterMenu++;
      }
      $canDelete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid();
      if($canDelete){
        $menuoptions[$counterMenu]['name'] = "delete";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Delete");
        $counterMenu++;
      }
      if(!$video->isOwner($viewer)){
        $menuoptions[$counterMenu]['name'] = "report";
        $menuoptions[$counterMenu]['label'] = $this->view->translate("Report Video");
      }
      $response['menus'] = $menuoptions;
    }
    
    $photo = $this->getBaseUrl(false,$video->getPhotoUrl());
    if($photo)
      $response['video']["share"]["imageUrl"] = $photo;
			$response['video']["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
      $response['video']["share"]["title"] = $video->getTitle();
      $response['video']["share"]["description"] = strip_tags($video->getDescription());
      $response['video']["share"]['urlParams'] = array(
          "type" => $video->getType(),
          "id" => $video->getIdentity()
      );
    if(is_null($response['video']["share"]["title"]))
      unset($response['video']["share"]["title"]);
      
    if ($video->type == 3 || $video->type == "upload") {
      if (!empty($video->file_id)) {
        $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
        $response['video']['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
        $$response['video']['video_extension'] = $storage_file->extension;  
      }
    }else{
      // $embedded = $video->getRichContent(true,array(),'',true);
      $embedded = $video->getRichContent(true);
      preg_match('/src="([^"]+)"/', $embedded, $match);
      if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
        $response['video']['iframeURL'] = str_replace('//','https://',$match[1]);
      }else{
        $response['video']['iframeURL'] = $match[1];
      }
    }

    if(!empty($response['video']['iframeURL'])){
      $dataIframeURL = $response['video']['iframeURL'];
      if(strpos($dataIframeURL,'youtube') !== false ){
          if(strpos($dataIframeURL,'?') !== false ){
              $response['video']['iframeURL'] = $response['video']['iframeURL']."&feature=oembed";
          }else{
              $response['video']['iframeURL'] = $response['video']['iframeURL']."?feature=oembed";
          }
      }
    }

    if( !empty($video->category_id) ) {
      $category = Engine_Api::_()->getItem('video_category', $video->category_id);
      $response['video']['category_title'] = $category->category_name;
			if( !empty($video->subcat_id) ) {
				$category = Engine_Api::_()->getItem('video_category', $video->subcat_id);
				$response['video']['subcategory_title'] = $category->category_name;
			}
			if( !empty($video->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('video_category', $video->subsubcat_id);
				$response['video']['subsubcategory_title'] = $category->category_name;
			}
    }
    
		$response['video']['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $video->getIdentity(), 'resource_type' => 'video'));
		$response['video']['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1);
		$response['video']['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratingicon', 'fas fa-star');

    if($viewer->getIdentity()){
     $response['video']['canEdit'] = $video->authorization()->isAllowed($viewer, 'edit');
		 $response['video']['canDelete'] = $video->authorization()->isAllowed($viewer, 'delete');
    }
    if (!$viewer->isSelf($video->getOwner())){
        $video->view_count++;
        $video->save();
		}
    $response['video']['user_image'] = $this->userImage($video->getOwner()->getIdentity(),"thumb.profile");
    $response['video']['user_id'] = $video->getOwner()->getIdentity();
    $response['video']['user_title'] = $video->getOwner()->getTitle();
    $response['video']['resource_type'] = 'video';
    //similar videos
    $similarVideos = $this->getVideos($this->getSimilarVideos($video));
    if(engine_count($similarVideos) > 0){
      $response['similar_videos'] = $similarVideos;
    } 
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$response));
  }
  
  protected function getSimilarVideos($video){
    $table = Engine_Api::_()->getDbTable('videos','video');
    $tableName = $table->info('name');
    $select = $table->select()->where('video_id != ?',$video->getIdentity())->where('category_id =?',$video->category_id)->limit(10);
    $result = $table->fetchAll($select);  
    return $result;
  }
  
  
	public function createAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    // Upload video
    if (!$this->_helper->requireUser->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
      
    if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if (isset($_POST['c_type']))
      return $this->_forward('compose-upload', null, null, array('format' => 'json')); 
    
    if (isset($_FILES['video']) && !empty($_FILES['video']['name'])) {
        $_POST['id'] = $this->uploadVideoAction();
    }
    //check ses modules integration
    $values['parent_type'] = $parent_type = $this->_getParam('parent_type');
    $values['parent_id'] = $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
        if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'video') ) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        }
    } else {
        $parent_type = 'user';
        $parent_id = $viewer->getIdentity();
    }
    
    // set up data needed to check quota
    
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
    $currentCount = $paginator->getTotalItemCount();
    if ($quota)
      $leftVideos = $quota - $currentCount;
    else
      $leftVideos = 0; //o means unlimited
    
    if (($currentCount >= $quota) && !empty($quota)){
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of videos allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
    }

    // Create form
    $this->view->form = $form = new Video_Form_Video(array(
        'parent_type' => $parent_type,
        'parent_id' => $parent_id
    ));
    $user = Engine_Api::_()->user()->getViewer();

    $allowedUpload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'upload');
    $ffmpegPath = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
    if( !empty($ffmpegPath) && $allowedUpload ) {
      //$lable = 'My Computer';
      //if( Engine_Api::_()->hasModuleBootstrap('mobi') && Engine_Api::_()->mobi()->isMobile() ) {
        $lable = 'My Device';
      //}
      $videoOptions['upload'] = $lable;
    }
    if($videoOptions)
    $form->type->addMultiOptions($videoOptions);
    $form->removeElement('embedUrl');
    $form->removeElement('code');
    $form->removeElement('id');
    $form->removeElement('ignore');
    $form->removeElement('photo_id');
    if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      foreach ($formFields as $key => $value){
        if ($value['type'] == 'Hidden')
          $formFields[$key]['type'] = 'File';
      }
      $this->generateFormFields($formFields,array('resources_type'=>'video', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }
    
    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) { 
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
      $this->validateFormFields($validateFields);
    }
   
     $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('video', $this->view->viewer()->level_id, 'flood');
        if(!empty($itemFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("videos",'video');
            $select = $tableFlood->select()->where("owner_id = ?",$this->view->viewer()->getIdentity())->order("creation_date DESC");
            if($itemFlood[1] == "minute"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($itemFlood[1] == "day"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(engine_count($floodItem) && $itemFlood[0] <= engine_count($floodItem)){
                $message = Engine_Api::_()->core()->floodCheckMessage($itemFlood,$this->view);
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
            }
        }
    // Process
    $values = $form->getValues();
    unset($values['rotation']);
    $values['owner_id'] = $viewer->getIdentity();
    $db = Engine_Api::_()->getDbTable('videos', 'video')->getAdapter();
    $db->beginTransaction();
    try {
			$viewer = Engine_Api::_()->user()->getViewer();
      $table = Engine_Api::_()->getDbTable('videos', 'video');
        if ($values['type'] == 'upload') {
            $video = Engine_Api::_()->getItem('video', $this->_getParam('id'));
            unset($values['duration']);
        } else {
            $information = $this->handleIframelyInformation($values['url']);
            if (empty($information)) {
              Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$form->addError('We could not find a video there - please check the URL and try again.'), 'result' => array()));
            }
            $values['code'] = $information['code'];
            $values['thumbnail'] = $information['thumbnail'];
            $values['duration'] = $information['duration'];
            $video = $table->createRow();
            if (is_null($values['subcat_id']))
              $values['subcat_id'] = 0;
            if (is_null($values['subsubcat_id']))
              $values['subsubcat_id'] = 0;
        }
        if (empty($values['auth_view'])) {
            $values['auth_view'] = 'everyone';
        }
        if (isset($values['networks'])) {
            $network_privacy = 'network_'. implode(',network_', $values['networks']);
            $values['networks'] = implode(',', $values['networks']);
        }
        $values['view_privacy'] = $values['auth_view'];
        $video->setFromArray($values);
        $video->save();
        // Now try to create thumbnail
        if ($values['type'] !== 'upload') {
            $thumbnail = $values['thumbnail'];
            $ext = ltrim(strrchr($thumbnail, '.'), '.');
            $thumbnail_parsed = @parse_url($thumbnail);
            if (@GetImageSize($thumbnail)) {
                $valid_thumb = true;
            } else {
                $valid_thumb = false;
            }
            if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && engine_in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                $tmpFile = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                $thumbFile = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
                $srcFh = fopen($thumbnail, 'r');
                $tmpFh = fopen($tmpFile, 'w');
                stream_copy_to_stream($srcFh, $tmpFh, 1024 * 1024 * 2);
                $image = Engine_Image::factory();
                $image->open($tmpFile)
                    ->resize(330, 240)
                    ->write($thumbFile)
                    ->destroy();
                try {
                    $thumbFileRow = Engine_Api::_()->storage()->create($thumbFile, array(
                        'parent_type' => $video->getType(),
                        'parent_id' => $video->getIdentity()
                    ));
                    $video->photo_id = $thumbFileRow->file_id;
                    // Remove temp file
                    @unlink($thumbFile);
                    @unlink($tmpFile);
                } catch (Exception $e) {
                }
            }
            $video->status = 1;
            $video->save();
            // Insert new action item
            $insertAction = true;
        }
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $authView = $values['auth_view'];
        $viewMax = array_search($authView, $roles);
        foreach ($roles as $i => $role) {
            $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_comment'])) {
            $authComment = $values['auth_comment'];
        } else {
            $authComment = "everyone";
        }
        $commentMax = array_search($authComment, $roles);
        foreach ($roles as $i=>$role) {
            $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      if ($video->status == 1) {
        $owner = $video->getOwner();
        
        if( $parent_type == 'group') {
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($owner, $group, 'video_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        } else {
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($owner, $video, 'video_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        }
        if ($action != null) {
            Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $video);
        }
				// Rebuild privacy
				$actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
				foreach ($actionTable->getActionsByObject($video) as $action) {
					$actionTable->resetActivityBindings($action);
				}
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $result["message"] = $this->view->translate("Video created successfully.");
    $result['id'] = $video->getIdentity();
    if (($video->type == 3 && $video->status != 1) || !$approve) {
      $result['redirect'] = "manage";
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
    }
    $result['redirect'] = "video_view";
    $result['id'] = $video->getIdentity();
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
  public function uploadVideoAction()
  {
    if (!$this->_helper->requireUser()->checkRequire()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('Invalid request method'), 'result' => array()));
    }
    $values = $this->getRequest()->getPost();
    if (empty($_FILES['video'])) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('No file'), 'result' => array()));
    }
    if (!isset($_FILES['video']) || !is_uploaded_file($_FILES['video']['tmp_name'])) {
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('Invalid Upload').print_r($_FILES, true), 'result' => array()));
    }
    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt');
    if (engine_in_array(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('Invalid Upload'), 'result' => array()));
    }
    $db = Engine_Api::_()->getDbTable('videos', 'video')->getAdapter();
    $db->beginTransaction();
    try {
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['owner_id'] = $viewer->getIdentity();
        $params = array(
            'owner_type' => 'user',
            'owner_id' => $viewer->getIdentity()
        );
        $video = Engine_Api::_()->video()->createVideo($params, $_FILES['video'], $values);
        $this->view->status   = true;
        $this->view->name     = $_FILES['video']['name'];
        $this->view->code = $video->code;
        $this->view->video_id = $video->video_id;
        // sets up title and owner_id now just incase members switch page as soon as upload is completed
        $video->title = $_FILES['video']['name'];
        $video->owner_id = $viewer->getIdentity();
        $video->save();
        $db->commit();
        return $video->video_id;
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>Zend_Registry::get('Zend_Translate')->_('An error occurred.').$e, 'result' => array()));
    }
  }
  
  public function videosAction(){
    $resource_id = $this->_getParam('resource_id',0);
    $resource_type = $this->_getParam('resource_type',0);
    
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if(!$item)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request','result'=>''));
    
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result["permission"]['canCreateVideo'] = Engine_Api::_()->authorization()->isAllowed('video', null, 'create') ;
    $result['videos'] = $this->getVideos($paginator,"");
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
      $extraParams['pagging']['moduleName'] = "core_video";
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video found.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    
  }
  
  
  public function editAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
      
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    
    if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error','result'=>''));
    }
    $this->view->video = $video;
    $parent_type = $video->parent_type;
    $parent_id = $video->parent_id;
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
        if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'video') ) {
            return;
        }
    } else {
        $parent_type = 'user';
        $parent_id = $viewer->getIdentity();
    }
        
    $this->view->form = $form = new Video_Form_Edit(array(
      'parent_type' => $parent_type,
      'parent_id' => $parent_id
    ));
    $form->populate($video->toArray());
		
		
    $form->getElement('search')->setValue($video->search);
    $form->getElement('title')->setValue($video->title);
    $form->getElement('description')->setValue($video->description);
    if($form->getElement('category_id'))
    $form->getElement('category_id')->setValue($video->category_id);
    
    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    if($parent_type == 'user') {
      $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    } else if($parent_type = 'group') {
        if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
          $roles = array('owner', 'member', 'parent_member');
        } else {
          $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
        }
    }
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($video, $role, 'view')) {
          $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($video, $role, 'comment')) {
          $form->auth_comment->setValue($role);
      }
    }
    
    // prepare tags
    $videoTags = $video->tags()->getTagMaps();
    $tagString = '';
    foreach ($videoTags as $tagmap) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);
//     if (Engine_Api::_()->authorization()->isAllowed('video', Engine_Api::_()->user()->getViewer(), 'allow_network'))
//       $form->networks->setValue(explode(',', $video->networks));
    
    $form->removeElement('code');
    $form->removeElement('id');
    $form->removeElement('ignore');

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields)){
        if($video->category_id){
          foreach($formFields as $fields){
            foreach($fields as $field){
              $subcat = array();
              if($fields['name'] == "subcat_id"){ 
                $subcat = Engine_Api::_()->getItemTable('video_category')->getSubcategory(array('category_id'=>$video->category_id,'column_name'=>'*'));
              }else if($fields['name'] == "subsubcat_id"){
                if($video->subcat_id)
                $subcat = Engine_Api::_()->getItemTable('video_category')->getSubSubcategory(array('category_id'=>$video->subcat_id,'column_name'=>'*'));
              }
              if(is_countable($subcat) && engine_count($subcat)){
                $arrayCat = array();
                foreach($subcat as $cat){
                  $arrayCat[$cat->getIdentity()] = $cat->getTitle(); 
                }
                $fields["multiOptions"] = $arrayCat;  
              }
            }
            $newFormFieldsArray[] = $fields;
          }
          if(!engine_count($newFormFieldsArray))
            $newFormFieldsArray = $formFields;
        }
        if(!engine_count($newFormFieldsArray))
          $newFormFieldsArray = $formFields;
				$this->generateFormFields($newFormFieldsArray,array('resources_type'=>'video', 'formTitle' => $form->getTitle() ? $this->view->translate($form->getTitle()) : '', 'formDescription' => $form->getDescription() ? $this->view->translate($form->getDescription()) : ''));

      }
    }
    
     // Check if valid
    if( !$form->isValid($_POST) ) { 
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    // Process
    $db = Engine_Api::_()->getDbTable('videos', 'video')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      if (isset($values['networks'])) {
        $network_privacy = 'network_'. implode(',network_', $values['networks']);
        $values['networks'] = implode(',', $values['networks']);
      }
      if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '' && $_FILES['image']['size'] > 0) {
        $values['photo_id'] = $this->setPhoto($_FILES['image'], $video->video_id, true);
      } else {
        if (empty($values['photo_id'])){
          unset($values['photo_id']);
				}
      }
			      
      $video->setFromArray($values);
      $video->save();
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_view'])
        $auth_view = $values['auth_view'];
      else
        $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_comment'])
        $auth_comment = $values['auth_comment'];
      else
        $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->setTagMaps($viewer, $tags);
      $db->commit();
    } catch (Exception $e) { 
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($video) as $action) {
          $action->privacy = isset($values['networks'])? $network_privacy : null;
          $action->save();
          $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("Video edited successfully.")));
  }
  protected function setPhoto($photo, $id) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'video',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    $extension = Engine_Api::_()->core()->convertImageToWebp($extension);
    // Save
    $filesTable = Engine_Api::_()->getDbTable('files', 'storage');
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(400, 400)
            ->write($mainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('upload_limit_reach'), 'result' => array()));
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('uploading_error'), 'result' => array()));
      
      }
    }
    // Remove temp files
    @unlink($mainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
  public function searchFormAction(){
    $search_for = $this-> _getParam('search_for', 'video');
    $setting = Engine_Api::_()->getApi('settings', 'core');
    $form = new Video_Form_Search();
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields,array('resources_type'=>'video'));
  }
  
  public function rateAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $table = Engine_Api::_()->getDbTable('ratings', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
    
			Engine_Api::_()->getDbTable('ratings', 'core')->setRating(array('resource_id' =>  $resource_id, 'resource_type' => 'video', 'rating' => $rating));

			$video = Engine_Api::_()->getItem('video', $resource_id);
			$video->rating = Engine_Api::_()->getDbTable('ratings', 'core')->getRating(array('resource_id' => $video->getIdentity(), 'resource_type' => 'video'));
			$video->save();
			
			$owner = Engine_Api::_()->getItem('user', $video->owner_id);
			if($owner->user_id != $user_id)
				Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $video, 'video_rating');
			
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("You have successfully rated video.")));
  }
  
	public function composeUploadAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate')); 
    }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request')); 
    }
    $video_title = $this->_getParam('title');
    $video_url = $this->_getParam('uri');
    $video_type = $this->_getParam('type');
    $composer_type = $this->_getParam('c_type', 'wall');
    if (strpos($video_url,'youtube') !== false || strpos($video_url,'youtu.be') !== false) 
      $video_type = (($composer_type == "wall") ? "iframely" : 1); 
    else if(strpos($video_url,'vimeo') !== false)
      $video_type = 2;
    // extract code    echo '<pre>';print_r($video_url);
    $checkvideo = $this->handleIframelyInformation($video_url);
    // check if code is valid
    // check which API should be used
    /*if (strpos($video_url,'youtube') !== false || strpos($video_url,'youtu.be') !== false) {
        echo 'dfdsaf';die;
      $valid = $this->checkYouTube($code);
    }
    else if (strpos($video_url,'vimeo') !== false) {
      $valid = $this->checkVimeo($code);
    }else{
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid upload video Url.')); 
    }*/
    if($checkvideo)
        $valid = true;
    // check to make sure the user has not met their quota of # of allowed video uploads
    // set up data needed to check quota
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
    $current_count = $paginator->getTotalItemCount();
    
    if (($current_count >= $quota) && !empty($quota)) {
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of videos allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
    } else if ($valid) {
      $db = Engine_Api::_()->getItemTable('video')->getAdapter();
      $db->beginTransaction();
      try {
        
         
        $information = $this->handleIframelyInformation($video_url);
        // create video
        $table = Engine_Api::_()->getItemTable('video');
        $video = $table->createRow();
        $video->title = $information['title'] ? $information['title']  : 'Untitled Video';
        $video->description = $information['description'] ? $information['description'] : '';
        $video->duration = $information['duration'] ? $information['duration'] : '';
        $video->owner_id = $viewer->getIdentity();
        $video->code = $information['code'];
        $video->type = $video_type;
        $video->save();
        
         // Now try to create thumbnail
        $thumbnail = $information['thumbnail'];
           
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
				$thumbnail_parsed = @parse_url($thumbnail);
				$imageUploadSize = @getimagesize($thumbnail);
				$width = isset($imageUploadSize[0]) ? $imageUploadSize[0] : '';
        $height = isset($imageUploadSize[1]) ? $imageUploadSize[1] : '';
        if (@$imageUploadSize && $width > 120 && $height > 90) {$valid_thumb = true;}else{
					if($video_type == 1) {
							$thumbnail = "http://img.youtube.com/vi/".$video->code."/hqdefault.jpg";
							if (@getimagesize($thumbnail)) {
								 $valid_thumb = true;
								 $thumbnail_parsed = @parse_url($thumbnail);
							} else {	$valid_thumb = false;}
						}else
							$valid_thumb = false;
				}
       if($valid_thumb){
        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
        $image = Engine_Image::factory();
        $image->open($tmp_file)
                ->resize(400, 400)
                ->write($thumb_file)
                ->destroy();
        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
            'parent_type' => $video->getType(),
            'parent_id' => $video->getIdentity()
        ));
				@unlink($tmp_file);
				@unlink($thumb_file);
        $video->photo_id = $thumbFileRow->file_id;
       }
        // If video is from the composer, keep it hidden until the post is complete
       
        $video->status = 1;
        $video->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
				@unlink($tmp_file);
				@unlink($thumb_file);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $e->getMessage()));
      }
      // make the video public
      if ($composer_type === 'wall') {
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $roles));
          $auth->setAllowed($video, $role, 'comment', ($i <= $roles));
        }
      }
      
      $result['video']['status'] = true;
      $result['video']['video_id'] = (string) $video->video_id;
      $result['video']['photo_id'] = $video->photo_id;
      $result['video']['title'] = $video->title;
      $result['video']['description'] = $video->description;
      $result['video']['src'] = $this->getBaseUrl(false,$video->getPhotoUrl());
      $result['video']['message'] = $this->view->translate('Video posted successfully');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
    } else {
      $message = $this->view->translate('We could not find a video there - please check the URL and try again.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$message, 'result' => array()));
    }
  }
  public function handleIframelyInformation($uri)
  {
    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
    if (parse_url($uri, PHP_URL_SCHEME) === null) {
        $uri = "http://" . $uri;
    }
    $uriHost = Zend_Uri::factory($uri)->getHost();
    if ($iframelyDisallowHost && engine_in_array($uriHost, $iframelyDisallowHost)) {
        return;
    }
    $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
    $iframely = Engine_Iframely::factory($config)->get($uri);
    if (!engine_in_array('player', array_keys($iframely['links']))) {
        return;
    }
    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['links']['thumbnail'])) {
        $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
        if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
            $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
            $information['thumbnail'] = "http://" . $information['thumbnail'];
        }
    }
    if (!empty($iframely['meta']['title'])) {
        $information['title'] = $iframely['meta']['title'];
    }
    if (!empty($iframely['meta']['description'])) {
        $information['description'] = $iframely['meta']['description'];
    }
    if (!empty($iframely['meta']['duration'])) {
        $information['duration'] = $iframely['meta']['duration'];
    }
    $information['code'] = $iframely['html'];
    return $information;
  }
	public function deleteAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('video', $this->getRequest()->getParam('video_id'));
    if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"user_not_autheticate", 'result' => array()));
    // In smoothbox    
    if (!$video) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"invalid_request", 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"invalid_request", 'result' => array()));
    }
    
    
    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getApi('core', 'video')->deleteVideo($video);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
  }
  public function validationAction($params = array()) {
    $video_type = $this->_getParam('type',$params['type']);
    if(!empty($params['type']))
      $video_type = $params['type'];
    $code = $this->_getParam('code',$params['code']);
    $ajax = $this->_getParam('ajax', false);
    $mURL = $this->_getParam('url',$params['url']);
    $valid = false;
    // check which API should be used
    if ($video_type == "youtube") {
      $valid = $this->checkYouTube($code);
    } else if ($video_type == "vimeo") {
      $valid = $this->checkVimeo($code);
    } else if ($video_type == 'dailymotion') {
      $valid = $this->checkdailymotion($code);
    } 
    if(is_countable($params) && engine_count($params))
      return $valid;
    $this->view->code = $code;
    $this->view->ajax = $ajax;
    $this->view->valid = $valid;
  }
  // HELPER FUNCTIONS
  public function extractCode($url, $type) {
    switch ($type) {
      //youtube
      case "1":
        // change new youtube URL to old one
        $new_code = @pathinfo($url);
        $url = preg_replace("/#!/", "?", $url);
        // get v variable from the url
        $arr = array();
        $arr = @parse_url($url);
        if ($arr['host'] === 'youtu.be') {
          $data = explode("?", $new_code['basename']);
          $code = $data[0];
        } else {
          $parameters = $arr["query"];
          parse_str($parameters, $data);
          $code = $data['v'];
          if ($code == "") {
            $code = $new_code['basename'];
          }
        }
        return $code;
      //vimeo
      case "2":
        // get the first variable after slash
        $code = @pathinfo($url);
        return $code['basename'];
      //dailymotion
      case "4":
        // get the first variable after slash
        $code = @pathinfo($url);
        $code = explode('_', $code['basename']);
        if (isset($code[0]))
          return $code[0];
        else
          return '';
    }
  }
  // YouTube Functions
  public function checkYouTube($code) {
    $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
    if (function_exists('curl_init')){ 
      $data =  $this->url_get_contents('https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key);
    }else{
     if (!$data = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key))
      return false;
    }
    $data = Zend_Json::decode($data);
    if (empty($data['items']))
      return false;
    return true;
  }
  function url_get_contents ($Url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }
  // Vimeo Functions
  public function checkVimeo($code) {
    //http://www.vimeo.com/api/docs/simple-api
    //http://vimeo.com/api/v2/video
    $data = @simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
    $id = engine_count($data->video->id);
    if ($id == 0)
      return false;
    return true;
  }
  public function checkdailymotion($code) {
    //https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title
     if (function_exists('curl_init')){ 
                 $data =  $this->url_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed");
                }else		
    $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed");
    if ($data != '') {
      $data = json_decode($data, true);
      if (isset($data['allow_embed']) && $data['allow_embed'])
        return true;
    }
    return false;
  }
  // handles thumbnails
  public function handleThumbnail($type, $code = null) {
    switch ($type) {
      //youtube
      case "1":
        return "http://img.youtube.com/vi/$code/maxresdefault.jpg";
      //vimeo
      case "2":
        //thumbnail_medium
         if (function_exists('curl_init')){ 
                 $data =  unserialize($this->url_get_contents("http://vimeo.com/api/v2/video/$code.php"));
                }else	
        $data = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$code.php"));
        $thumbnail = $data[0]['thumbnail_large'];
        return $thumbnail;
      case "4":
      if (function_exists('curl_init')){ 
                 $data =  ($this->url_get_contents("https://api.dailymotion.com/video/$code?fields=thumbnail_url"));
                }else	
        $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=thumbnail_url");
        if ($data != '') {
          $data = json_decode($data, true);
          $thumbnail_url = (isset($data['thumbnail_url']) && $data['thumbnail_url']) ? $data['thumbnail_url'] : '';
          return $thumbnail_url;
        }
    }
  }
  // retrieves infromation and returns title + desc
  public function handleInformation($type, $code) {
    switch ($type) {
      //youtube
      case "1":
        $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
        if (function_exists('curl_init')){ 
            $data =  ($this->url_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key"));
        }else	
        $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key");
        if (empty($data)) {
          return;
        }
        $data = Zend_Json::decode($data);
        $information = array();
        $youtube_video = $data['items'][0];
        $information['title'] = $youtube_video['snippet']['title'];
        $information['description'] = $youtube_video['snippet']['description'];
        $information['duration'] = Engine_Date::convertISO8601IntoSeconds($youtube_video['contentDetails']['duration']);
        return $information;
      //vimeo
      case "2":
        //thumbnail_medium
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
        $thumbnail = $data->video->thumbnail_medium;
        $information = array();
        $information['title'] = $data->video->title;
        $information['description'] = $data->video->description;
        $information['duration'] = $data->video->duration;
        return $information;
      case "4":
      if (function_exists('curl_init')){ 
                 $data =  ($this->url_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title"));
                }else	
        $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title");
        $data = json_decode($data, true);
        $information['title'] = $data['title'];
        $information['description'] = $data['description'];
        $information['duration'] = $data['duration'];
        return $information;
    }
  }
}
