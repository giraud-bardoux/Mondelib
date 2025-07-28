<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: PostController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Activity_PostController extends Sesapi_Controller_Action_Standard {
    function activityType(){

        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
            return 1;
        }else{
            if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
                return 0;
            }
        }
    }
    function uploadPhotos($subject){
      // Get album
      $viewer = Engine_Api::_()->user()->getViewer();
      $type = 'wall';      
      $table = Engine_Api::_()->getDbTable('albums',$subject->getModuleName());
      $album = $table->getSpecialAlbum($viewer, $type,$subject->getIdentity());      
      $photoTable = Engine_Api::_()->getDbTable('photos', $subject->getModuleName());
      $auth = Engine_Api::_()->authorization()->context;
      $subjectTableName = Engine_Api::_()->getItemTable($subject->getType());
      try{
       $counter = 0;
       foreach($_FILES['attachmentImage']['name'] as $image){
          $uploadimage = array();
          if ($image == ""){
           $counter++;
           continue;
          }
          $uploadimage["name"] = $_FILES['attachmentImage']['name'][$counter];
          $uploadimage["type"] = $_FILES['attachmentImage']['type'][$counter];
          $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$counter];
          $uploadimage["error"] = $_FILES['attachmentImage']['error'][$counter];
          $uploadimage["size"] = $_FILES['attachmentImage']['size'][$counter];
          $photoTable = Engine_Api::_()->getDbTable('photos', $subject->getModuleName());
          $photo = $photoTable->createRow();
          $primaryKey = $subjectTableName->info('primary');
          $photo->setFromArray(array(
            $primaryKey[1] => $subject->getIdentity(),
            'user_id' => $viewer->getIdentity()
          ));
          $photo->save();
          $photo = $photo->setAlbumPhoto($uploadimage,false,false,$album);   
          $photo->collection_id = $photo->album_id;
          $photo->save();    
          if($album){
            if(!$album->photo_id) {
              $album->photo_id = $photo->photo_id;
              $album->save();
            }
          }
          $_POST['fancyalbumuploadfileids'] = $_POST['fancyalbumuploadfileids'].$photo->getIdentity().' ';
        $counter++;
      }
          $_POST['fancyalbumuploadfileids'] = rtrim($_POST['fancyalbumuploadfileids'],' ');
      }catch(Exception $e){
        $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage()));
      }
      return;
    }
    public function indexAction(){  
      if(!empty($_POST['debug'])){
       // echo "<pre>";var_dump($_POST);die;  
      }
      ini_set("memory_limit","240M");
      $this->view->error = 'An error occured. Please try again after some time.';
      // Make sure user exists
      if( !$this->_helper->requireUser()->isValid() ) 
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));
      
      // Get subject if necessary
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = null;
      $subject_guid = $this->_getParam('resource_type', null);
      $subject_rid = $this->_getParam('resource_id',null);
      if( $subject_guid ) {
        $subject = Engine_Api::_()->getItem($subject_guid,$subject_rid);
      }
      // Use viewer as subject if no subject
      if( null === $subject ) {
        $subject = $viewer;
      }
  
      // Make form
        if($this->activityType())
            $form = $this->view->form = new Activity_Form_Post();
        else
            $form = $this->view->form = new Activity_Form_Post();
        // Check auth
      if( !$subject->authorization()->isAllowed($viewer, 'comment') ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate'));
      }
  
      // Check if post
      if( !$this->getRequest()->isPost() ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
      }
          
      // Check if form is valid
      $postData = $this->getRequest()->getPost();
      $body = @$postData['body'];
      $body = $this->stringsToURLStrings($body);
      
      //Emojis Work
      /*if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
         // $body = str_replace($bodyEmoji,$emojisCode,$body);
        }
      }*/
      //Emojis Work End
      //Engine_Api::_()->getApi('settings', 'core')->setSetting($viewer->getIdentity().'.activity.user.setting',$postData['privacy']);
      $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
      $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
      //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');
      $postData['body'] = $body;
        /*// Check one more thing
        if( $form->body->getValue() === '' && $form->getValue('attachment_type') === '' ) {
            $this->view->status = false;
         // $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
         // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
        }*/
        
        if(!empty($_POST['image_id'])) {
          $gifImageUrl = $_POST['image_id'];
          $context = "";
          if($body){
            $context = "<div class='body'>".$body."</div><br/>";
          }
          $context.= sprintf('<img src="%s" class="giphy_image" alt="%s">' , $gifImageUrl , $gifImageUrl);
          $postData['body'] = $context;
        }
      
       $activityFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $this->view->viewer()->level_id, 'activity_flood');

        if(!empty($activityFlood[0])){
            $tableFlood = Engine_Api::_()->getDbTable("actions",'activity');
            $select = $tableFlood->select()->where("subject_id = ?",$this->view->viewer()->getIdentity())->order("date DESC");
            if($activityFlood[1] == "minute"){ 
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($activityFlood[1] == "day"){
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(engine_count($floodItem) && $activityFlood[0] <= engine_count($floodItem)){
              Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('You have reached maximum limit of posting in 1 hour. Try again after this duration expires.'), 'result' => array()));
                return;
            }
        }
      // set up action variable
      $action = null;
      
      $scheduled_post = !empty($_POST['scheduled_post']) ? date('Y-m-d H:i:s',strtotime($_POST['scheduled_post'])) : false;
      if($scheduled_post){
        $postData['scheduled_post'] =   date('d/m/Y H:i:s',strtotime($scheduled_post));
      }
     
      // Process
      $videovideoUploadFile = !empty($_FILES['videoupload']['name']) ? $_FILES['videoupload'] : (!empty($_FILES['video']['name']) ? $_FILES['video'] : (!empty($_FILES['file_type_videoupload']['name']) ? $_FILES['file_type_videoupload'] : false));
      if(!empty($videovideoUploadFile)) {
          if(Engine_Api::_()->sesapi()->isModuleEnable('sesvideo'))
              $db = Engine_Api::_()->getDbTable('videos', 'sesvideo')->getAdapter();
          else
              $db = Engine_Api::_()->getDbTable('videos', 'video')->getAdapter();
        $db->beginTransaction();
        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $values['owner_id'] = $viewer->getIdentity();
            $params = array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            );
            if(Engine_Api::_()->sesapi()->isModuleEnable('sesvideo')){
              if(!$this->_getParam("not_merge_video")){
                $video = Engine_Api::_()->sesvideo()->createVideo($params, $videovideoUploadFile, $values);
              }else{
                $video = $this->setVideo($params,$videovideoUploadFile,$values);
              }
            }
            else
              $video = Engine_Api::_()->video()->createVideo($params, $videovideoUploadFile, $values);
            $video->title = 'Untitled Video';
            if(isset($video->activity_text)) {
              $video->activity_text = $body;
            }
            $video->save();
            // for live streaming.
            if (Engine_Api::_()->sesapi()->isModuleEnable('sesvideo'))
              if (!empty($postData['elivehost_id'])) {
                $elivehostItem = Engine_Api::_()->getItem('elivehost', $postData['elivehost_id']);
                if (!empty($elivehostItem)) {
                  $elivehostItem->status = 'processing';
                  $elivehostItem->video_id = $video->getIdentity();
                  $elivehostItem->save();
                  $actionItem = Engine_Api::_()->getItem('activity_action', $elivehostItem->action_id);
                  $actionItem->params = array('processing' => 1);
                  $actionItem->save();
                }else{
                  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => "Elive host id not found",'video_id'=>$video->getIdentity())));
                }
              }
            $video->owner_id = $viewer->getIdentity();
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            $auth_view = "everyone";
            $viewMax = array_search($auth_view, $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
            }
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            
            $auth_comment = "everyone";
            $commentMax = array_search($auth_comment, $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
            }
            $video->save();
          $db->commit();
          $_POST['attachment']['video_id'] = $video->getIdentity();
          $_POST['attachment']['type'] = 'video';
          $_POST['attachment']["title"] = !empty($postData["liveStreamingTitle"]) ? $postData["liveStreamingTitle"] : 'Untitled Video';
          //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','result'=>$this->view->translate('Your video is currently being processed - you will be notified when it is ready to be viewed.')));
        } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
      }
      if(!empty($_FILES['musicupload']['name'])) {
        if(Engine_Api::_()->sesapi()->isModuleEnable('sesmusic')) {
          $albumTable = Engine_Api::_()->getDbTable('albums', 'sesmusic');
          $file = Engine_Api::_()->getApi('core', 'sesmusic')->createSong($_FILES['musicupload']);
          $album = $albumTable->getSpecialPlaylist($viewer, 'wall');
          $albumsong = $album->addSong($file);
          $_POST['attachment']['albumsong_id'] = $albumsong->albumsong_id;
          $_POST['attachment']['type'] = 'sesmusic';
        } else {
          $albumTable = Engine_Api::_()->getDbTable('playlists', 'music');
          $file = Engine_Api::_()->getApi('core', 'music')->createSong($_FILES['musicupload']);
          $album = $albumTable->getSpecialPlaylist($viewer, 'wall');
          $song = $album->addSong($file);
          $_POST['attachment']['song_id'] = $song->getIdentity();
          $_POST['attachment']['type'] = 'music';
        }
      }
     
      // If we're here, we're done
      if($subject && ($subject->getType() == "sespage_page" || $subject->getType() == "sesgroup_group"  || $subject->getType() == "sesbusiness_business" )){
        if(!empty($_FILES["attachmentImage"]) && engine_count($_FILES["attachmentImage"]) > 0){
          $this->uploadPhotos($subject);  
        }
      }else if(!empty($_POST["attachmentImage"]) && engine_count($_POST["attachmentImage"]) > 0){
        // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
            foreach($_POST['attachmentImage'] as $image){
              if ($image == ""){
                continue;
              }

              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($image,true);
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $_POST['fancyalbumuploadfileids'] = $_POST['fancyalbumuploadfileids'].$photo->getIdentity().' ';
          }
         $_POST['fancyalbumuploadfileids'] = rtrim($_POST['fancyalbumuploadfileids'],' ');
       }catch(Exception $e){
         $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage()));
       }
      }else if(!empty($_FILES["attachmentImage"]) && engine_count($_FILES["attachmentImage"]) > 0){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
           $counter = 0;
           
           foreach($_FILES['attachmentImage']['name'] as $image){
              $uploadimage = array();
              if ($image == ""){
               $counter++;
               continue;
              }
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$counter];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$counter];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$counter];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$counter];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$counter];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);              
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $_POST['fancyalbumuploadfileids'] = $_POST['fancyalbumuploadfileids'].$photo->getIdentity().' ';
            $counter++;
          }
            $_POST['fancyalbumuploadfileids'] = rtrim($_POST['fancyalbumuploadfileids'],' ');
          }catch(Exception $e){
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage()));
          }
      }
        $this->view->status = true;
        try {
          // Get body
          $body = preg_replace('/<br[^<>]*>/', "\n", $body);
          // Try attachment getting stuff
          $attachment = null;
          $params = array();
          $embedpost = false;
          $attachmentData = $_POST['attachment']; 
          
          if(!empty($_POST['fancyalbumuploadfileids'])){
            if($attachmentData['type'] != 'buysell' && $attachmentData['type'] != 'sespage_photo' && $attachmentData['type'] != 'sesgroup_photo' && $attachmentData['type'] != 'sesbusiness_photo')
            $attachmentData['type'] = 'photo';
            $arrachmentPhotoIds = $_POST['fancyalbumuploadfileids'];
            $attachmentIds = explode(' ',$arrachmentPhotoIds);
            if($subject_guid == 'sespage_page'){
              $attachmentData['type'] = "sespage_photo";  
            }else if ($subject_guid == 'sesgroup_group'){
              $attachmentData['type'] = "sesgroup_photo";    
            }else if ($subject_guid == 'sesbusiness_business'){
              $attachmentData['type'] = "sesbusiness_photo";    
            }
          }
          
          $attachmentType = '';
          if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
            $attachmentType = $type = $attachmentData['type'];
            $config = null;
            
            if($type == 'photo' && Engine_Api::_()->sesapi()->isModuleEnable('album')) {
              $type = 'albumvideo';
            }
            
            foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
              if( !empty($data['composer'][$type]) ) {
                $config = $data['composer'][$type];
              }
            }
             
//             if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
//               $config = null;
//             }
            
            if( $config ) {
            
              $attachmentData['actionBody'] = $body;          
              $plugin = Engine_Api::_()->loadClass($config['plugin']);
              $method = 'onAttach'.ucfirst($type);
              $execute = false;
              
              if(empty($attachmentIds) || ($attachmentData['type'] == 'buysell' && !empty($attachmentIds))){
              
               if($config['plugin'] == 'Activity_Plugin_FileuploadComposer')
                $fileUpload = $_FILES['fileupload'];
               else
                $fileUpload = '';
               $attachment = $attachmentAttachData = $plugin->$method($attachmentData,$fileUpload,$_POST);
               $execute = true;
              }
              if(!$execute || $attachmentData['type'] == 'buysell'){
                $attachmentData['actionBody'] = '';
                if($attachmentData['type'] == 'buysell'){
                 if(!Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum'))
                  $plugin =  Engine_Api::_()->loadClass('Album_Plugin_Composer');
                 else
                  $plugin =  Engine_Api::_()->loadClass('Sesalbum_Plugin_Composer');
                 $method = 'onAttachPhoto';
                }
                $attachmentCount = 0;
                foreach($attachmentIds as $attachmentId){
                  if(!$attachmentId)
                    continue;
                   $attachmentData['photo_id'] = $attachmentId;
                  $attachment = $plugin->$method($attachmentData);
                  $attachmentCount++;
                 }  
              }
            }
          }
          if($this->activityType()) {
            $activityTypeModel = "activity";
          }else {
            $activityTypeModel = "activity";
          }
          // Is double encoded because of design mode
          //$body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
          //$body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
          //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');
          $videoProcess = 0;
          // Special case: status
          if( !$attachment && $viewer->isSelf($subject) ) {
            if( $body != '' && !$embedpost) {
              $viewer->status = $body;
              $viewer->status_date = date('Y-m-d H:i:s');
              $viewer->save();
              $viewer->status()->setStatus($body);
            }
            $action = Engine_Api::_()->getDbTable('actions', $activityTypeModel)->addActivity($viewer, $subject, 'status', $body, $postData, $postData);
          } else {
            // General post
            if($attachment){
              if($attachment->getType() == 'video' && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')) {
                if($attachment->status != 1)
                  $videoProcess = 1;
              }
            }
            if(1) {
              $type = 'post';
              if( $viewer->isSelf($subject) ) {
                $type = 'post_self';
              }else {
                  $postActionType = 'post_' . $subject->getType();
                  $actionType = Engine_Api::_()->getDbTable('actionTypes', 'activity')->getActionType($postActionType);
                  if ($actionType) {
                      $type = $postActionType;
                  }
              }
              if($attachment){
                if($attachmentData['type'] == 'buysell')
                  $type = 'post_self_buysell';
                else if($attachment->getType() == 'album_photo' || $attachment->getType()  == 'photo'){
                if($viewer->isSelf($subject))
                    $type = 'post_self_photo';
                else
                    $type = 'post_photo';
                }
                else if($attachment->getType() == 'video' || $attachment->getType()  == 'sesvideo_video'){
                    if($viewer->isSelf($subject))
                        $type =  'post_self_video';
                    else
                        $type = 'post_video';
                if($attachment->status != 1)
                  $videoProcess = 1;
                }else if($attachment->getType() == 'music_playlist' || $attachment->getType()  == 'sesmusic_albumsong') {
                    if($viewer->isSelf($subject))
                      $type = 'post_self_music';
                    else
                      $type = 'post_music';
                }
                else if($attachment->getType() == 'activity_file')
                  $type = 'post_self_file';
              }
            } else {
                $type = 'post';
                if ($viewer->isSelf($subject)) {
                   $activityType =  $type = 'post_self';
                } else {
                    $activityType = $postActionType = 'post_' . $subject->getType();
                    $actionType = Engine_Api::_()->getDbTable('actionTypes', 'activity')->getActionType($postActionType);
                    if ($actionType) {
                        $type = $postActionType;
                    }
                }
                if ($attachmentCount > 1) {
                    $postActionType = $activityType . '_multi_' . $attachmentType;
                    $actionType = Engine_Api::_()->getDbTable('actionTypes', 'activity')->getActionType($postActionType);
                    $type =  !empty($actionType) ? $postActionType : $activityType;
                    $postData['count'] = $attachmentCount;
                }
            }
            
            // Add notification for <del>owner</del> user
            $subjectOwner = $subject->getOwner();
            if( !$viewer->isSelf($subject) &&
                $subject instanceof User_Model_User ) {
                $notificationType = 'post_'.$subject->getType();
                  Engine_Api::_()->getDbTable('notifications', $activityTypeModel)->addNotification($subjectOwner, $viewer, $subject, $notificationType, array(
                      'url1' => $subject->getHref()
                  ));
            }
            // Add activity
            $action = Engine_Api::_()->getDbTable('actions', $activityTypeModel)->addActivity($viewer, $subject, $type, $body, $postData,$postData) ;
            if($action && !empty($attachmentAttachData) && $attachmentData['type'] == 'buysell'){
              $attachmentAttachData->action_id = $action->getIdentity();
              $attachmentAttachData->save();
            }
            // Try to attach if necessary
            if( $action && $attachment) {
                if (empty($attachmentIds) && $attachmentData['type'] != 'buysell') {
                    Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $attachment);
            }
              else if(!empty($attachmentIds) && $attachmentData['type'] == 'sespage_photo' ) {
              foreach($attachmentIds as $attachmentId){
                if(!$attachmentId)
                  continue;
                  //make item of photo object
                  $photo = Engine_Api::_()->getItem('sespage_photo',$attachmentId);
                  Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                }
              }else if(!empty($attachmentIds) && $attachmentData['type'] == 'sesgroup_photo' ) {
                foreach($attachmentIds as $attachmentId){
                  if(!$attachmentId)
                      continue;
                    //make item of photo object
                    $photo = Engine_Api::_()->getItem('sesgroup_photo',$attachmentId);
                    Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                  }
              }else if(!empty($attachmentIds) && $attachmentData['type'] == 'sesbusiness_photo' ) {
                foreach($attachmentIds as $attachmentId){
                  if(!$attachmentId)
                    continue;
                  //make item of photo object
                  $photo = Engine_Api::_()->getItem('sesbusiness_photo',$attachmentId);
                  Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                }
              }else{
                foreach($attachmentIds as $attachmentId){
                  if(!$attachmentId)
                    continue;
                 //make item of photo object
                 $photo = Engine_Api::_()->getItem('album_photo',$attachmentId);
                  Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
                }
              }
            }
          }
          
          //ses quote integration
          if($attachmentData['type'] == 'quote') {
            $quote = Engine_Api::_()->getItem('sesquote_quote', $attachmentAttachData->quote_id);
            $action = Engine_Api::_()->getDbTable('actions', $activityTypeModel)->addActivity($viewer, $quote, 'sesquote_new');
            if( $action ) {
              Engine_Api::_()->getDbTable('actions', $activityTypeModel)->attachActivity($action, $quote);
            }
            if($action && !empty($attachmentAttachData)) {
              $attachmentAttachData->action_id = $action->getIdentity();
              $attachmentAttachData->save();
            }
            
            if(!empty($postData['tags']) && $this->activityType()) {
              $tags = preg_split('/[,]+/', $postData['tags']);
              $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
              foreach($tags as $tag) {
                $dbGetInsert->query('INSERT INTO `engine4_activity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
              }
            }
          } 
          
          //buysell
          if($action->type == 'post_self_buysell' && $this->activityType())
          {
            $buysell = $action->getBuySellItem();
            $buysell->title = $_POST['buysell-title'];
            $buysell->description = $_POST['buysell-description'];
            $buysell->price = $_POST['buysell-price'];
            $buysell->currency = $_POST['buysell-currency'];
            $buysell->save();
            if(!empty($_POST['buysell-location']) && !empty($_POST['activitybuyselllng']) && !empty($_POST['activitybuyselllat'])){
             $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
             $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $buysell->getIdentity() . '", "' . $postData['activitybuyselllat'] . '","' . $postData['activitybuyselllng'] . '","activity_buysell","'.$postData['buysell-location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $postData['activitybuyselllat'] . '" , lng = "' . $postData['activitybuyselllng'] . '",venue="'.$postData['buysell-location'].'"');     
            }
          }

          //tag location in post
          if(!empty($_POST['checkin_loc']['label']) && !empty($_POST['checkin_loc']['latitude']) && !empty($_POST['checkin_loc']['longitude']) && $this->activityType()){
             //check location
             $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
             $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $action->getIdentity() . '", "' . $_POST['checkin_loc']['latitude'] . '","' . $_POST['checkin_loc']['longitude'] . '","activity_action","'.$_POST['checkin_loc']['label'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $_POST['checkin_loc']['latitude'] . '" , lng = "' . $_POST['checkin_loc']['longitude'] . '",venue="'.$_POST['checkin_loc']['label'].'"');     
          }
          
          //tag friend in post
      if(!empty($_POST['tag_page']) && $this->activityType()){
        if(empty($dbGetInsert))
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $tagPages = array_unique(explode(",", $_POST['tag_page']));
        if(engine_count($tagPages)) {
          foreach($tagPages  as $tagPage) {
            $dbGetInsert->query('INSERT INTO `engine4_activity_tagitems` (`user_id`, `action_id`,`resource_id`,`resource_type`) VALUES ("'.$this->view->viewer()->getIdentity().'", "'.$action->getIdentity().'","'.$tagPage.'","sespage_page")');
            $page = Engine_Api::_()->getItem('sespage_page',$tagPage);
            $item = Engine_Api::_()->getItem('user',$page->user_id );
            $itemUrl = '<a href="' . $page->getHref() . '">' . "page" . '</a>';
            $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
          // if($page->user_id != $this->view->viewer()->getIdentity())
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_item', array("postLink" => $postLink,'itemurl'=>$itemUrl));
          }
        }
      }
          
          //tag friend in post
          if(!empty($_POST['taggedData']) && $this->activityType()){
            if(empty($dbGetInsert))
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            $tagUsers = array_unique(explode(",", $_POST['taggedData']));
            if(engine_count($tagUsers)) {
              $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
              foreach($tagUsers  as $tagUser) {
                if(!$tagUser)
                  continue;
                $item = Engine_Api::_()->getItem('user', $tagUser);
                if(!$item)
                  continue;
                $dbGetInsert->query('INSERT INTO `engine4_activity_tagusers` (`user_id`, `action_id`) VALUES ("'.$tagUser.'", "'.$action->getIdentity().'")');
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
              }
            }
          }
        if($this->activityType()) {
            //Tagging People by status box
            preg_match_all('/(^|\s)(@\w+)/', $_POST['body'], $result);
            $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
            foreach ($result[2] as $value) {
                $user_id = str_replace('@_user_', '', $value);
                if (intval($user_id) > 0) {
                    $item = Engine_Api::_()->getItem('user', $user_id);
                    if (!$item || !$item->getIdentity())
                        continue;
                } else {
                    $itemArray = explode('_', $user_id);
                    $resource_id = $itemArray[count($itemArray) - 1];
                    unset($itemArray[count($itemArray) - 1]);
                    $resource_type = implode('_', $itemArray);
                    $item = Engine_Api::_()->getItem($resource_type, $resource_id);
                    if (!$item || !$item->getIdentity())
                        continue;
                    $item = $item->getOwner();
                    if (!$item || !$item->getIdentity())
                        continue;
               }
               if ($item)
                  Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
               
            }
            //Tagging People by status box
        }
          $reactionId = $this->_getParam('reaction_id', @$_POST['reaction_id']); /* write this line because some time reaction_id has not come with $_POST keyword */
          //insert reaction
          if(!empty($reactionId) && $this->activityType()){
            $action->reaction_id = $reactionId;
            $action->save();
          }
          $action->save();
         
          $hashtagValue = '';
          if(isset($_GET['hashtag'])){
            $hashtagValue = $_GET['hashtag'];  
          }
          $existsHashTag = false;
          // extrack #  tag value from post
          if($action && $this->activityType()){
             preg_match_all("/(#\w+)/u", $action->body, $matches);
             if(engine_count($matches)){
              if(empty($dbGetInsert))
                $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
              $hashtags = array_unique($matches[0]);
              foreach($hashtags  as $hashTag){
               if('#'.$hashtagValue == $hashTag)
                 $existsHashTag = true;
               $dbGetInsert->query('INSERT INTO `engine4_activity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.str_replace('#','',$hashTag).'")');  
              }  
             }
          }
          if(!empty($_POST['feelings']) && engine_count($_POST['feelings'])){
              //Feeling Work
            if($action && $_POST['feelings']['feelingicon_id'] && $_POST['feelings']['resource_type']) {
              $resource = Engine_Api::_()->getItem($_POST['feelings']['resource_type'], $_POST['feelings']['feelingicon_id']);
              Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $resource);
            }
            //Feling Work
            if(!empty($_POST['feelings']['feeling_id']) && !empty($_POST['feelings']['feelingicon_id'])) {
              $db = Engine_Db_Table::getDefaultAdapter();
              $db->query('INSERT INTO `engine4_activity_feelingposts` (`feeling_id`, `feelingicon_id`, `resource_type`, `action_id`) VALUES ("'.$_POST['feelings']['feeling_id'].'", "'.$_POST['feelings']['feelingicon_id'].'" ,"'.$_POST['feelings']['resource_type'].'", "'.$action->getIdentity().'")');
            }
          }
          
          //Feed Background Image Work
          if(@$_POST['feedbg_id']) {
              $action->feedbg_id = $_POST['feedbg_id'];
              $action->save();
          }
          
          //check for target post
          if( $this->_getParam('post_to_targetpost', false)) {
            $targetpost['location_send'] = $_POST['targetpost']['location_send'];        
            $targetpost['gender_send'] =  $_POST['targetpost']['gender_send'];
            $targetpost['age_min_send'] =  $_POST['targetpost']['age_min_send'];
            $targetpost['age_max_send'] = $_POST['targetpost']['age_max_send'];
            $targetpost['action_id'] = $action->getIdentity(); 
            $targetpost['country_name'] = '';
            $targetpost['city_name'] = '';
            if($targetpost['location_send'] == 'country'){
              $targetpost['country_name'] = $_POST['targetpost']['country_name'];
              $targetpost['lat'] =  $_POST['targetpost']['targetpostlat'];
              $targetpost['lng'] = $_POST['targetpost']['targetpostlng'];
              $targetpost['location_country'] = $_POST['targetpost']['location_country'];
            }else if($targetpost['location_send'] == 'city'){
              $targetpost['lat'] =  $_POST['targetpost']['targetpostlatcity'];
              $targetpost['lng'] = $_POST['targetpost']['targetpostlngcity'];  
              $targetpost['location_city'] = $_POST['targetpost']['location_city'];  
              $targetpost['city_name'] = $_POST['targetpost']['city_name'];              
            }else{
              $targetpost['lat'] =  '';
              $targetpost['lng'] = '';
              $targetpost['location_country']  = '';
              $targetpost['location_city']  = '';
            }
            if(empty($dbGetInsert))
               $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            $dbGetInsert->query('INSERT INTO `engine4_activity_targetpost`(`action_id`, `location_send`, `location_city`, `location_country`, `gender_send`, `age_min_send`, `age_max_send`, `lat`, `lng`,`country_name`,`city_name`) VALUES ("'.$targetpost['action_id'].'","'.$targetpost['location_send'].'","'.$targetpost['location_city'].'","'.$targetpost['location_country'].'","'.$targetpost['gender_send'].'","'.$targetpost['age_min_send'].'","'.$targetpost['age_max_send'].'","'.$targetpost['lat'].'","'.$targetpost['lng'].'","'.$targetpost['country_name'].'","'.$targetpost['city_name'].'")'); 
          }
          
          //update status posting type
          if(_SESAPI_PLATFORM_SERVICE && $this->activityType()){
            $action->posting_type = (int)_SESAPI_PLATFORM_SERVICE;
            $action->save();
          }
          
          //Feed Gif work
          if(!empty($_POST['image_id'])) {
            $action->gif_url = $_POST['image_id'];
              $action->save();
          }
          
          //$db->commit();
          
          if($action)
            Engine_Hooks_Dispatcher::getInstance()->callEvent('onActivitySubmittedAfter', $action);
            
          if(!empty($attachment) && $attachment->getType() == 'video' && !empty($videoProcess)) {
            if($action)
              $action->delete();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','result'=>$this->view->translate('Your video is currently being processed - you will be notified when it is ready to be viewed.')));
          }
          
          $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Activity Created Successfully.');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$this->view->error));
        } catch( Exception $e ) {
          $this->view->status = false;
          $this->view->error = $e->getMessage();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
        }
    } 
  protected function stringsToURLStrings($stringBody){
    $pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
    return preg_replace($pattern, '<a href="http$2://$3">$0</a>', $stringBody);
  }
  protected function setVideo($params,$file) {
    // create video item
    $video = Engine_Api::_()->getDbTable('videos', 'sesvideo')->createRow();
    $file_ext = pathinfo($file['name']);
    $file_ext = $file_ext['extension'];
    $video->save();
    $video->type = 3;
    $videoFile = $file;
    // Store video in temporary storage object for ffmpeg to handle
    $storage = Engine_Api::_()->getItemTable('storage_file');
    $params = array(
        'parent_id' => $video->getIdentity(),
        'parent_type' => $video->getType(),
        'user_id' => $this->view->viewer()->getIdentity(),
        'mime_major' => 'video',
        'mime_minor' => $file_ext,
    );
    $video->code = $file_ext;
    $storageObject = $storage->createFile($file, $params);
    $video->file_id = $file_id = $storageObject->file_id;
    
    // Remove temporary file
    
    $video->save();
    if($file_ext == 'mp4' || $file_ext == 'flv'){
        $video->status = 1;
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, null);
        $file = $file->map();
        if(strpos($file,'http') === false ){
            $file = APPLICATION_PATH.$file;
        }
        $video->duration = $duration = $this->getVideoDuration($video,$videoFile['tmp_name']);
        if($duration){
            $thumb_splice = $duration / 2;
            $this->getVideoThumbnail($video,$thumb_splice,$videoFile['tmp_name']);
        }
        $video->save();
        //@unlink($file['tmp_name']);
        return $video;
    }
    //@unlink($file['tmp_name']);
    return $video;
  }
  public function getVideoThumbnail($video,$thumb_splice,$file = false){
$tmpDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . 'video';
$thumbImage = $tmpDir . DIRECTORY_SEPARATOR . $video -> getIdentity() . '_thumb_image.jpg';
$ffmpeg_path = Engine_Api::_() -> getApi('settings', 'core') -> video_ffmpeg_path;
if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path))
{
  $output = null;
  $return = null;
  exec($ffmpeg_path . ' -version', $output, $return);
  if ($return > 0)
  {
    return 0;
  }
}
if(!$file)
  $fileExe = $video->code;
else
  $fileExe = $file;
$output = PHP_EOL;
$output .= $fileExe . PHP_EOL;
$output .= $thumbImage . PHP_EOL;
$thumbCommand = $ffmpeg_path . ' ' . '-i ' . escapeshellarg($fileExe) . ' ' . '-f image2' . ' ' . '-ss ' . $thumb_splice . ' ' . '-vframes ' . '1' . ' ' . '-v 2' . ' ' . '-y ' . escapeshellarg($thumbImage) . ' ' . '2>&1';
// Process thumbnail
$thumbOutput = $output . $thumbCommand . PHP_EOL . shell_exec($thumbCommand);
// Check output message for success
    $thumbSuccess = true;
if (preg_match('/video:0kB/i', $thumbOutput))
{
  $thumbSuccess = false;
}
// Resize thumbnail
if ($thumbSuccess && is_file($thumbImage))
{
  try
  {
    $image = Engine_Image::factory();
    $image->open($thumbImage)->resize(500, 500)->write($thumbImage)->destroy();
    $thumbImageFile = Engine_Api::_()->storage()->create($thumbImage, array(
      'parent_id' => $video -> getIdentity(),
      'parent_type' => $video -> getType(),
      'user_id' => $video -> owner_id
      )
    );
    $video->photo_id = $thumbImageFile->file_id;
    $video->save();
    @unlink($thumbImage);
    return true;
  }
  catch (Exception $e)
  {
    throw $e;
    @unlink($thumbImage);
  }
}
 @unlink(@$thumbImage);
 return false;
}
  public function getVideoDuration($video,$file = false)
  {
      $duration = 0;
      if ($video)
      {
        $ffmpeg_path = Engine_Api::_() -> getApi('settings', 'core') -> video_ffmpeg_path;
        
        if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path))
        {
            $output = null;
            $return = null;
            exec($ffmpeg_path . ' -version', $output, $return);
            
            if ($return > 0)
            {
                return 0;
            }
        }
        if(!$file)
            $fileExe = $video->code;
        else
            $fileExe = $file;
        // Prepare output header
        $fileCommand = $ffmpeg_path . ' ' . '-i ' . escapeshellarg($fileExe) . ' ' . '2>&1';
        // Process thumbnail
        $fileOutput = shell_exec($fileCommand);
        // Check output message for success
        $infoSuccess = true;
        if (preg_match('/video:0kB/i', $fileOutput))
        {
            $infoSuccess = false;
        }
        // Resize thumbnail
        if ($infoSuccess)
        {
            // Get duration of the video to caculate where to get the thumbnail
            if (preg_match('/Duration:\s+(.*?)[.]/i', $fileOutput, $matches))
            {
                list($hours, $minutes, $seconds) = preg_split('[:]', $matches[1]);
                $duration = ceil($seconds + ($minutes * 60) + ($hours * 3600));
            }
        }

      }
      return $duration;
  }

}
