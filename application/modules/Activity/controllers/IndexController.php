<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: IndexController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_IndexController extends Core_Controller_Action_Standard {
  
  public function attachmentviewAction() {
    
    $this->view->format = $format = $_GET['format'];
    $this->view->formaturl = '';
    if($format == 'smoothbox') {
      $this->_helper->layout->setLayout('default-simple');
      $this->view->formaturl = '?format=smoothbox';
    }

    $this->view->action_id = $action_id = $this->_getParam("action_id", 0);
    $this->view->id = $id = $this->_getParam("id", 0);
    $this->view->type = $type = $this->_getParam("type", null);

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->action = $action = Engine_Api::_()->getItem("activity_action",$action_id);

    $this->view->attachmentId = $getAttachmentId = Engine_Api::_()->getDbTable('attachments', 'activity')->attachmentId($type, $id);

    $this->view->attachment = $attachment = Engine_Api::_()->getItem('activity_attachment', $getAttachmentId);

    $this->view->subject = $subject = Engine_Api::_()->getItem($type, $id);
    if(!$subject) {
      return $this->_forward('notfound', 'error', 'core');
    }
    Engine_Api::_()->core()->setSubject($subject);
    
    // if($action) {
    //   $subject = Engine_Api::_()->user()->getUser($action->subject_id);
    //   if($subject->getIdentity()) {
    //     Engine_Api::_()->core()->setSubject($subject);
    //   }

    //   $this->_helper->requireSubject('user');
    //   $this->_helper->requireAuth()
    //               ->setAuthParams($subject, $viewer, 'view')
    //               ->isValid();
    // }

    if($type == 'album_photo') {

      $this->view->photo = $photo = $subject;
      $this->view->album = $album = $subject->getAlbum();

      if( !$album || !$album->getIdentity() || ((!$album->approved) && !$album->isOwner($viewer)) ) {
        if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
        } else
          return $this->_helper->requireSubject->forward();
      }

      if( !$photo || !$photo->getIdentity() || ((!$photo->approved) && !$photo->isOwner($viewer)) ) {
        if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
        } else
          return $this->_helper->requireSubject->forward();
      }
     

      if( !$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer) ) {
          $photo->view_count = new Zend_Db_Expr('view_count + 1');
          $photo->save();
      }

      //Check for feed photo privacy
      if(!empty($photo->parent_type) && !empty($photo->parent_id) && $photo->parent_type == 'activity_action') {
        $action = Engine_Api::_()->getItem($photo->parent_type, $photo->parent_id);
        if($action) {
          $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
          $actions = $actionTable->getActivity($viewer, array('action_id' => $photo->parent_id));
          if(!$actions) {
            return $this->_forward('requireauth', 'error', 'core');
          }
        }
      }

      // if this is sending a message id, the user is being directed from a coversation
      // check if member is part of the conversation
      $message_id = $this->getRequest()->getParam('message');
      $message_view = false;
      if ($message_id){
          $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
          if($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) $message_view = true;
      }
      $this->view->message_view = $message_view;
      $this->view->isprivate = 0;
      if(engine_in_array($album->type, array("group","event"))){
          $this->view->isprivate = 1;
          if($album->getOwner()->getIdentity() == $viewer->getIdentity()){
              $this->view->isprivate = 0;
          }
      }
      
      //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) return;
      if(!$message_view && !$this->_helper->requireAuth()->setAuthParams($photo, null, 'view')->isValid() ) return;

      // $checkAlbum = Engine_Api::_()->getItem('album', $this->_getParam('album_id'));
      // if( !($checkAlbum instanceof Core_Model_Item_Abstract) || !$checkAlbum->getIdentity() || $checkAlbum->album_id != $photo->album_id )
      // {
      //     $this->_forward('requiresubject', 'error', 'core');
      //     return;
      // }
      
      // // Network check
      // $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($checkAlbum);
      // if(empty($networkPrivacy))
      //     return $this->_forward('requireauth', 'error', 'core');

      $this->view->canEdit = $canEdit = $album->authorization()->isAllowed($viewer, 'edit');
      $this->view->canDelete = $canDelete = $album->authorization()->isAllowed($viewer, 'delete');
      $this->view->canTag = $canTag = $album->authorization()->isAllowed($viewer, 'tag');
      $this->view->canUntagGlobal = $canUntag = $album->isOwner($viewer);

      $this->view->photoTags = $photo->tags()->getTagMaps();
      

      // Get tags
      $tags = array();
      foreach( $photo->tags()->getTagMaps() as $tagmap ) {
          $tags[] = array_merge($tagmap->toArray(), array(
              'id' => $tagmap->getIdentity(),
              'text' => $tagmap->getTitle(),
              'href' => $tagmap->getHref(),
              'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
          ));
      }
      $this->view->tags = $tags;
      $this->view->viewer_id = $viewer->getIdentity();

    } elseif($type == 'video') {

      $video = $subject;

      // if this is sending a message id, the user is being directed from a coversation
      // check if member is part of the conversation
      $message_id = $this->getRequest()->getParam('message');
      $message_view = false;
      if ($message_id) {
          $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
          if ($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) {
              $message_view = true;
          }
      }
      $this->view->message_view = $message_view;
      if (!$message_view &&
          !$this->_helper->requireAuth()->setAuthParams($video, null, 'view')->isValid()) {
          return;
      }
      
      if( !$video || !$video->getIdentity() || ((!$video->approved) && !$video->isOwner($viewer)) ) {
        if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
        } else
          return $this->_forward('requireauth', 'error', 'core');
      }
      
      if($video->parent_type == 'group' && $video->parent_id) {
        $group = Engine_Api::_()->getItem($video->parent_type, $video->parent_id);
        if( !$group || !$group->getIdentity() || ((!$group->approved) && !$group->isOwner($viewer)) ) {
          if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
          } else
            return $this->_forward('requireauth', 'error', 'core');
        }
        $viewPermission = $group->authorization()->isAllowed($viewer, 'view');
        if(empty($viewPermission)) {
          return $this->_forward('requireauth', 'error', 'core');
        }
      }

      // Network check
      $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($video);
      if(empty($networkPrivacy))
          return $this->_forward('requireauth', 'error', 'core');

      $this->view->videoTags = $video->tags()->getTagMaps();

      // Check if edit/delete is allowed
      $this->view->can_edit = $can_edit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->checkRequire();
      $this->view->can_delete = $can_delete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->checkRequire();

      // check if embedding is allowed
      $can_embed = true;
      if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
          $can_embed = false;
      } elseif (isset($video->allow_embed) && !$video->allow_embed) {
          $can_embed = false;
      }
      $this->view->can_embed = $can_embed;

      // increment count
      $embedded = "";
      if ($video->status == 1) {
          if (!$video->isOwner($viewer)) {
              $video->view_count++;
              $video->save();
          }
          $embedded = $video->getRichContent(true);
      }

      if ($video->type == 'upload' && $video->status == 1) {
          if (!empty($video->file_id)) {
              $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
              if ($storage_file) {
                  $this->view->video_location = $storage_file->map();
                  $this->view->video_extension = $storage_file->extension;
              }
          }
      }

      $this->view->viewer_id = $viewer->getIdentity();
      $this->view->video = $video;
      $this->view->videoEmbedded = $embedded;
      if ($video->category_id) {
          $this->view->category = Engine_Api::_()->video()->getCategory($video->category_id);
      }
    }

    $this->view->nextPhoto = $attachment->getNextPhoto();
    if($this->view->nextPhoto) {
      $this->view->nextPhotoItem = Engine_Api::_()->getItem($this->view->nextPhoto->type, $this->view->nextPhoto->id);
    } else {
      $this->view->nextPhotoItem = null;
    }
    $this->view->previousPhoto = $attachment->getPreviousPhoto();
    if($this->view->previousPhoto) {
      $this->view->previousPhotoItem = Engine_Api::_()->getItem($this->view->previousPhoto->type, $this->view->previousPhoto->id);
    } else {
      $this->view->previousPhotoItem = null;
    }

    // Render
    $this->_helper->content->setEnabled();
  }

  public function editMediaAction()
  {
    $type = $this->_getParam('type', null);
    $id = $this->_getParam('id', null);

    $viewer = Engine_Api::_()->user()->getViewer();
    
    $this->view->subject = $photo = Engine_Api::_()->getItem($type, $id);
    if(!$photo)
      return $this->_forward('requireauth', 'error', 'core');

    $edit = $photo->authorization()->isAllowed($viewer, 'edit');
    if(!$edit)
      return $this->_forward('requireauth', 'error', 'core');

    $this->view->form = $form = new Activity_Form_Media_Edit();
    if($type == 'album_photo') {
      $form->setTitle('Edit Photo');
    } else if($type == 'video') {
      $form->setTitle('Edit Video');
    }

    $form->populate($photo->toArray());
    
    // $tagStr = '';
    // foreach( $photo->tags()->getTagMaps() as $tagMap ) {
    //     $tag = $tagMap->getTag();
    //     if( !isset($tag->text) ) continue;
    //     if( '' !== $tagStr ) $tagStr .= ', ';
    //     $tagStr .= $tag->text;
    // }

    // $form->populate(array(
    //     'tags' => $tagStr,
    // ));

    if( !$this->getRequest()->isPost() ) {
        return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }

    $values = $form->getValues();

    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $tags = preg_split('/[,]+/', trim($values['tags']));
      //$photo->tags()->setTagMaps(Engine_Api::_()->user()->getViewer(), $tags);
      $photo->setFromArray($values);
      $photo->save();
      $db->commit();

      $this->view->success = true;
      $this->view->title = $photo->title;
      $this->view->description = $photo->description;
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    // return $this->_forward('success', 'utility', 'core', array(
    //     'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
    //     'layout' => 'default-simple',
    //     'parentRefresh' => true,
    // ));
  }

  public function deleteMediaAction()
  {
    $type = $this->_getParam('type', null);
    $id = $this->_getParam('id', null);

    $viewer = Engine_Api::_()->user()->getViewer();
    
    $this->view->subject = $photo = Engine_Api::_()->getItem($type, $id);
    
    if(!$photo)
      return $this->_forward('requireauth', 'error', 'core');

    $edit = $photo->authorization()->isAllowed($viewer, 'delete');
    if(!$edit)
      return $this->_forward('requireauth', 'error', 'core');

    if($type == 'album_photo') {
      $album = $photo->getParent();
      $owner = Engine_Api::_()->getItem('user', $album->owner_id);
    }

    $this->view->form = $form = new Activity_Form_Media_Delete();
    if($type == 'album_photo') {
      $form->setTitle('Delete Photo');
      $form->setTitle('Are you sure you want to delete this photo?');
    } else if($type == 'video') {
      $form->setTitle('Delete Video');
      $form->setTitle('Are you sure you want to delete this video?');
    }

    if( !$this->getRequest()->isPost() ) {
        return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }

    try {
      if($type == 'album_photo') {
        // delete files from server
        $filesDB = Engine_Api::_()->getDbtable('files', 'storage');
        
        $thumbPath = $filesDB->fetchRow($filesDB->select()->where('parent_file_id = ?', $photo->file_id))->storage_path;
        Engine_Api::_()->storage()->deleteExternalsFiles($thumbPath);

        $filePath = $filesDB->fetchRow($filesDB->select()->where('file_id = ?', $photo->file_id))->storage_path;
        Engine_Api::_()->storage()->deleteExternalsFiles($filePath);

        // Delete image and thumbnail
        $filesDB->delete(array('file_id = ?' => $photo->file_id));
        $filesDB->delete(array('parent_file_id = ?' => $photo->file_id));

        // Check activity actions
        $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
        $actions =  $attachDB->fetchAll($attachDB->select()->where('type = ?', 'album_photo')->where('id = ?',$photo->photo_id));
        $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');

        foreach($actions as $action) {
            $action_id = $action->action_id;
            $actionItem =  $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
            $actionItem->attachment_count--;
            $actionItem->save();
            $attachDB->delete(array('type = ?' => 'album_photo', 'id = ?' => $photo->photo_id));

            $action =  $actionItem; //$actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
            if(is_array($action->params))
            $count = $action->params['count'];
            if( !is_null($count) && ($count > 1) ) {
                $action->params = array('count' => (integer)$count-1);
                $action->save();
            } else if($actionItem->attachment_count > 0) {
            
            } else {
                $action->delete();
            }
        }
        
        //If album photo delete then check profile photo also setup to zero.
        if($owner->photo_id == $photo->file_id) {
          $owner->photo_id = 0;
          $owner->save();
        }

        // delete photo
        Engine_Api::_()->getItem('album_photo', $photo->photo_id)->delete();
      } else if($type == 'video') {

        // Check activity actions
        $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
        $actions =  $attachDB->fetchAll($attachDB->select()->where('type = ?', 'video')->where('id = ?',$photo->video_id));

        $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');

        foreach($actions as $action){
          $action_id = $action->action_id;
          $actionItem =  $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
          $actionItem->attachment_count--;
          $actionItem->save();
          $attachDB->delete(array('type = ?' => 'video', 'id = ?' => $photo->video_id));

          $action =  $actionItem; //$actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
          if(is_array($action->params))
          $count = $action->params['count'];
          if( !is_null($count) && ($count > 1) ) {
              $action->params = array('count' => (integer)$count-1);
              $action->save();
          } else if($actionItem->attachment_count > 0) {

          }
          else {
            $action->delete();
          }
        }

        Engine_Api::_()->getApi('core', 'video')->deleteVideo($photo);
      }

      $this->view->success = true;
    } catch( Exception $e ) {
      throw $e;
    }

    // return $this->_forward('success', 'utility', 'core', array(
    //   'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'home'), 'default', true),
    //   'messages' => array(Zend_Registry::get('Zend_Translate')->_('Photo has been deleted.')),
    // ));
  }

  public function viewAction() {

    $action_id = $this->_getParam("action_id",0);
    $action = Engine_Api::_()->getItem("activity_action",$action_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if($action) {
      $subject = Engine_Api::_()->user()->getUser($action->subject_id);
      if($subject->getIdentity()) {
        Engine_Api::_()->core()->setSubject($subject);
      }

      $this->_helper->requireSubject('user');
      $this->_helper->requireAuth()
    //  ->setNoForward()                         // for showing image and name irrespective of privacy
      ->setAuthParams($subject, $viewer, 'view')
      ->isValid();
    }
    
    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled();
  }
  
  public function updateusereventAction() {

    $event_id = $this->_getParam('event_id', null);
    $user_id = $this->_getParam('user_id', null);
    Engine_Api::_()->getDbtable('eventmessages', 'activity')->update(array('userclose' => 1), array('event_id = ?' => $event_id, 'user_id = ?' => $user_id));


  }
  
  public function sellAction() {
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check && empty($viewer_id)){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }

    if(!Engine_Api::_()->user()->getViewer()->getIdentity())
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);

    // Render
    $this->_helper->content->setNoRender()->setEnabled();
  }
  
  public function onthisdayAction(){
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }

    if( !Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled()
        ;
  }
  public function postAction()
  {
    $this->view->error = 'An error occured. Please try again after some time.';
    $this->view->userphotoalign = $this->_getParam('userphotoalign', 'left');
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get subject if necessary
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = null;
    $subject_guid = $this->_getParam('subject', null);
    if( $subject_guid ) {
      $subject = Engine_Api::_()->getItemByGuid($subject_guid);
    }
    // Use viewer as subject if no subject
    if( null === $subject ) {
      $subject = $viewer;
    }

    // Make form
    $form = $this->view->form = new Activity_Form_Post();

    // Check auth
    if( !$subject->authorization()->isAllowed($viewer, 'comment') ) {
      return $this->_helper->requireAuth()->forward();
    }

    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    if(empty($_GET['is_ajax'])){
      // Check token
      if( !($token = $this->_getParam('token')) ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('No token, please try again');
        return;
      }
      $session = new Zend_Session_Namespace('ActivityFormToken');
      if( $token != $session->token ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid token, please try again');
        return;
      }
      $session->unsetAll();
    }

    // Check if form is valid
    $postData = $this->getRequest()->getPost();

    $body = @$postData['body'];
    Engine_Api::_()->getApi('settings', 'core')->setSetting($viewer->getIdentity().'.activity.user.setting',$postData['privacy']);
    $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
    $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
    $postData['body'] = $body;

    if( !$form->isValid($postData) ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Check one more thing
    if( $form->body->getValue() === '' && $form->getValue('attachment_type') === '' ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // set up action variable
    $action = null;
    $scheduled_post = !empty($_POST['scheduled_post']) ? $_POST['scheduled_post'] : false;
    // Process
    $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
    $db->beginTransaction();
    // If we're here, we're done
    $this->view->status = true;

    $multipleupload = $_POST['multipleupload'] ? $_POST['multipleupload'] : 0;
    $ismultiplephoto = false;

    try {
      // Get body
      $body = $form->getValue('body');
      $body = preg_replace('/<br[^<>]*>/', "\n", $body);
      //string contain url only.
      // Try attachment getting stuff
      $attachment = null;
      $params = array();
      $embedpost = false;
      $attachmentData = $this->getRequest()->getParam('attachment');

      if(!empty($_POST['fancyalbumuploadfileids'])){
        $arrachmentPhotoIds = $_POST['fancyalbumuploadfileids'];
        $attachmentIds = explode(' ',$arrachmentPhotoIds);
      }

      if(!empty($multipleupload) && !empty($_POST['fancyalbumuploadfileidsvideo'])) {
        $attachmentData['type'] = 'albumvideo';
        $fancyalbumuploadfileidsvideo = $_POST['fancyalbumuploadfileidsvideo'];
        $attachmentIds = explode(' ',$fancyalbumuploadfileidsvideo);
      }

      //Insert GIF Image
      // if(!empty($_POST['image_id'])) {
      //   $gifImageUrl = $_POST['image_id'];
      //   $context = "";
      //   if($body){
      //     $context = "<div class='body'>".$body."</div><br/>";
      //   }
      //   $context.= sprintf('<img src="%s" class="giphy_image" alt="%s">' , $gifImageUrl , $gifImageUrl);
      //   $params['body'] = $context;
      // }

      $type = '';
      if( !empty($attachmentData) && !empty($attachmentData['type']) ) {
        $type = $attachmentData['type'];
        $config = null;
        
        foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
          if( !empty($data['composer'][$type]) ) {
            $config = $data['composer'][$type];
          }
        }
        
        if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
          $config = null;
        }
        
        if( $config ) {
          $attachmentData['actionBody'] = $body;
          $plugin = Engine_Api::_()->loadClass($config['plugin']);
          $method = 'onAttach'.ucfirst($type);
          
          $execute = false;
          if(empty($attachmentIds) || ($attachmentData['type'] == 'buysell' && !empty($attachmentIds))) {
            if($config['plugin'] == 'Activity_Plugin_FileuploadComposer')
              $fileUpload = $_FILES['fileupload'];
           else
            $fileUpload = '';
            $attachment = $attachmentAttachData = $plugin->$method($attachmentData,$fileUpload,$_POST);
            $execute = true;
          }
          if(!$execute || $attachmentData['type'] == 'buysell') {
            $attachmentData['actionBody'] = '';
            if($attachmentData['type'] == 'buysell') {
              if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum'))
                $plugin =  Engine_Api::_()->loadClass('Sesalbum_Plugin_Composer');
              else if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album'))
                $plugin =  Engine_Api::_()->loadClass('Album_Plugin_Composer');
              $method = 'onAttachPhoto';
            }
            if(!empty($attachmentIds)) {
              foreach($attachmentIds as $attachmentId) {
                if(!$attachmentId) continue;
                if(!empty($multipleupload)) {
                  $attachmentId = explode('_', $attachmentId);
                  $attachmentData['type'] = $attachmentId[0];
                  $method = 'onAttachAlbumVideo';
                  $plugin =  Engine_Api::_()->loadClass('Activity_Plugin_AlbumVideoComposer');
                  if($attachmentData['type'] == 'video') {
                    $attachmentData['video_id'] = $attachmentId[1];
                  } elseif($attachmentData['type'] == 'photo') {
                    $attachmentData['photo_id'] = $attachmentId[1];
                  }
                } else {
                  $attachmentData['photo_id'] = $attachmentId;
                }
                $attachment = $plugin->$method($attachmentData);
              }
            }
          }
        }
      }

      // Is double encoded because of design mode
      $videoProcess = 0;
      // Special case: status
      if( !$attachment && $viewer->isSelf($subject) ) {
        // if( $body != '' && !$embedpost) {
        //   $viewer->status = $body;
        //   $viewer->status_date = date('Y-m-d H:i:s');
        //   $viewer->save();
        //   $viewer->status()->setStatus($body);
        // }

        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $subject, 'status', $body ,$params, $postData);

        //Feed Background Image Work
        if(isset($_POST['feedbgid']) && @$_POST['feedbgid_isphoto'] == 1 && $body && empty($_POST['image_id']) && empty($_POST['reaction_id'])) {
          if($action) {
            $action->feedbg_id = $_POST['feedbgid'];
            $action->save();
          }
        }
        //Feed Background Image Work

        //Feeling Work
        if($action && $_POST['feelingactivityiconid'] && $_POST['feelingactivity_resource_type'] && $_POST['feelingactivity_resource_type'] != 'undefined') {
          $resource = Engine_Api::_()->getItem($_POST['feelingactivity_resource_type'], $_POST['feelingactivityiconid']);
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $resource);
        }
        //Feeling Work

      } else { // General post

        $type = 'post';
        if( $viewer->isSelf($subject) ) {
          $type = 'post_self';
        } else {
          $postActionType = 'post_' . $subject->getType();
          $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
          if ($actionType) {
            $type = $postActionType;
          }
        }

        if($attachment) {
          if( $attachmentData['type'] == 'activitylink'  )
          $type = 'post_self_link';
          if( $attachmentData['type'] == 'buysell'  )
            $type = 'post_self_buysell';
          else if(  $attachment->getType() == 'album_photo' || $attachment->getType()  == 'photo' ){
           if(  $viewer->isSelf($subject) )
              $type = 'post_self_photo';
           else
              $type = 'post_photo';
          } else if(  $attachment->getType() == 'video') {
            if( $viewer->isSelf($subject) )
              $type = 'post_self_video';
            else
              $type = 'post_video';
            if($attachment->status != 1){
              $videoProcess = 1;
            }
          } else if( $attachment->getType() == 'music_playlist' || $attachment->getType() == 'music_playlist_song') {
            if( $viewer->isSelf($subject) ) {
              $type = 'post_self_music';
            } else {
              $type = 'post_music';
            }
          } else if(  $attachment->getType() == 'activity_file'  ){
            $type = 'post_self_file';
          }
          
          if( method_exists($attachment,'feedActivityType') ){
            $type = $attachment->feedActivityType($subject,$viewer->isSelf($subject));
          }
          if(!empty($multipleupload)) {
            $photoExists = false;
            $videoExists = false;

            foreach ($attachmentIds as $value) {
              if (strpos($value, 'photo') !== false) {
                $photoExists = true;
              }
              if (strpos($value, 'video') !== false) {
                $videoExists = true;
              }
            }

            if ($photoExists && $videoExists) {
              $type = 'post_self_photo_video';
            } elseif ($photoExists) {
              if(  $viewer->isSelf($subject) )
                $type = 'post_self_photo';
              else
                $type = 'post_photo';
            } elseif ($videoExists) {
              if( $viewer->isSelf($subject) )
                $type = 'post_self_video';
              else
                $type = 'post_video';
            }
          }
        }

        if($attachment && false !== strpos($attachment->getType(),'video') && isset($attachment->status) && $attachment->status != 1) {
          $videoProcess = 1;
          if(isset($attachment->activity_text) && empty($attachment->activity_text)){
            $attachment->activity_text = $body;
            $attachment->save();
          }
        }

        // Add notification for <del>owner</del> user
        $subjectOwner = $subject->getOwner();
        if( !$viewer->isSelf($subject) &&
            $subject instanceof User_Model_User ) {
          $notificationType = 'post_'.$subject->getType();
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($subjectOwner, $viewer, $subject, $notificationType, array(
            'url1' => $subject->getHref()
          ));
        }

        // Add activity
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $subject, $type, $body,$params,$postData);

        if($action && !empty($attachmentAttachData) && $attachmentData['type'] == 'buysell'){
          $attachmentAttachData->action_id = $action->getIdentity();
          $attachmentAttachData->save();
        }

        // Try to attach if necessary
        if( $action && $attachment) {
          if(empty($attachmentIds) && $attachmentData['type'] != 'buysell')
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
          else {
            
            if(!empty($attachmentIds)) {
              foreach($attachmentIds as $attachmentId){
                
                if(!$attachmentId)
                  continue;
                if(!empty($multipleupload)) {
                  $attachmentId = explode('_', $attachmentId);
                  $attachmentData['type'] = $attachmentId[0];
                  //make item of photo object
                  
                  if($attachmentData['type'] == 'video') {
                    $photo = Engine_Api::_()->getItem('video',$attachmentId[1]);
                    $photo->feedupload = $action->getIdentity();
                    $photo->save();
                  } elseif($attachmentData['type'] == 'photo') {
                    $ismultiplephoto = true;
                    $photo = Engine_Api::_()->getItem('album_photo',$attachmentId[1]);
                    $photo->feedupload = $action->getIdentity();
                    $photo->save();
                  }
                } else {
                  $photo = Engine_Api::_()->getItem('album_photo',$attachmentId);
                }
                
                Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
              }
            }
          }
        }

        //Multiple photo/video work
				if(empty($ismultiplephoto) && !empty($multipleupload)) {
					$action->approved = 0;
					$action->save();
				}
      }
     
      //Feed Gif work
      if(!empty($_POST['image_id'])) {
        if($action) {
          $action->gif_url = $_POST['image_id'];
          $action->save();
        }
      }

      //tag location in post
      if(!empty($_POST['tag_location']) && !empty($_POST['activitylng']) && !empty($_POST['activitylat'])){
         //check location
         $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
         $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $action->getIdentity() . '", "' . $_POST['activitylat'] . '","' . $_POST['activitylng'] . '","activity_action","'.$_POST['tag_location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $_POST['activitylat'] . '" , lng = "' . $_POST['activitylng'] . '",venue="'.$_POST['tag_location'].'"');
      }
      //tag friend in post
      if(!empty($_POST['tag_friends'])){
        if(empty($dbGetInsert))
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $tagUsers = array_unique(explode(",", $_POST['tag_friends']));
        if(engine_count($tagUsers)) {
          $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
          foreach($tagUsers  as $tagUser) {
            $dbGetInsert->query('INSERT INTO `engine4_activity_tagusers` (`user_id`, `action_id`) VALUES ("'.$tagUser.'", "'.$action->getIdentity().'")');

            $item = Engine_Api::_()->getItem('user', $tagUser);
            if($tagUser != $this->view->viewer()->getIdentity())
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
          }
        }
      }

      //Feling Work
      if(!empty($_POST['feelingactivityid']) && !empty($_POST['feelingactivityiconid']) && !empty($_POST['feeling_activity']) && empty($_POST['feelingactivity_custom'])) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $typeRes = $_POST['feelingactivity_resource_type'] ? $_POST['feelingactivity_resource_type'] : "";
        $db->query('INSERT IGNORE INTO `engine4_activity_feelingposts` (`feeling_id`, `feelingicon_id`, `resource_type`, `action_id`) VALUES ("'.$_POST['feelingactivityid'].'", "'.$_POST['feelingactivityiconid'].'" ,"'.$typeRes.'", "'.$action->getIdentity().'")');
      } else if(!empty($_POST['feelingactivity_custom'])) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $typeRes = $_POST['feelingactivity_resource_type'] ? $_POST['feelingactivity_resource_type'] : "";
        $db->query('INSERT IGNORE INTO `engine4_activity_feelingposts` (`feeling_id`, `feelingicon_id`, `resource_type`, `action_id`, `feeling_custom`, `feeling_customtext`) VALUES ("'.$_POST['feelingactivityid'].'", "'.$_POST['feelingactivityiconid'].'" ,"'.$typeRes.'", "'.$action->getIdentity().'", "'.$_POST['feelingactivity_custom'].'", "'.$_POST['feelingactivity_customtext'].'")');
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['body'], $result);
      $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        } else {
          $itemArray = explode('_',$user_id);
          $resource_id = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type,$resource_id);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, $viewer, $item, 'activity_tagged_people', array("postLink" => $postLink));
      }
      //Tagging People by status box

      //insert reaction
      if(!empty($_POST['reaction_id'])) {
        if($action) {
          $action->reaction_id = $_POST['reaction_id'];
          $action->save();
        }
      }

      $hashtagValue = '';
      if(isset($_GET['hashtag'])){
        $hashtagValue = $_GET['hashtag'];
      }
      $existsHashTag = false;
      // extrack #  tag value from post
      if($action){
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
      $db->commit();
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Success');
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
      $this->view->status = false;
      if(!empty($_GET['is_ajax']))
        $this->view->error = 'An error occured. Please try again after some time.';
      else
        throw $e;
    }

    // Check if action was created
    $post_fail = "";
    if( !$action ){
      $post_fail = "?pf=1";
    }
    if($action && $scheduled_post){
      $post_fail = "?sp=1";
    }
    if($action){
      $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onActivitySubmittedAfter', $action);
    }
    
    // Redirect if in normal context
    if( null === $this->_helper->contextSwitch->getCurrentContext() && empty($_GET['is_ajax'])) {
      $return_url = $form->getValue('return_url', false);
      if( $return_url ) {
        if($videoProcess && empty($multipleupload)) {
          $action->delete();
        }
        return $this->_helper->redirector->gotoUrl($return_url.$post_fail, array('prependBase' => false));
      }
    } else if(!empty($_GET['is_ajax'])){

      if($action){
       $feed = $this->view->activity($action,array('ulInclude'=>true, 'userphotoalign' => $this->view->userphotoalign));
       $last_id = $action->getIdentity();
      }else{
        $feed = $last_id = '';
      }

      if($videoProcess){
        $action->approved=  0;
        $action->save();
        //$action->delete();
      }
      
      //approve feed
      $approveFeed = "";
      if($subject && method_exists($subject,'approveFeed')){
        $approveFeed = $subject->approveFeed($action);
      }
      
      echo json_encode(array('videoProcess'=>$videoProcess,'attachmentType'=>!empty($attachment) ? $attachment->getType() : "", 'status'=> $this->view->status,'last_id'=>$last_id,'existsHashTag'=>$existsHashTag,'feed'=>$feed,'error'=>$this->view->error,'scheduled_post'=>$scheduled_post,'userhref'=>$viewer->getHref(),'scheduled_post_time'=>(!empty($action->schedule_time)) ? $action->schedule_time : '','approveFeed'=>$approveFeed),JSON_HEX_QUOT | JSON_HEX_TAG | JSON_INVALID_UTF8_IGNORE);die;
    }
  }
  
  public function approveFeedAction() {
    $this->view->action_id = $this->_getParam('action_id');
    if(engine_count($_POST)){
      $action = Engine_Api::_()->getItem('activity_action',$this->_getParam('action_id'));
      $action->approved = 1;
      $action->save();
      echo 1;  die;
    }
  }
  
  /**
   * Handles HTTP request to get an activity feed item's likes and returns a
   * Json as the response
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/viewlike
   *
   * @return void
   */
  public function viewlikeAction()
  {
    // Collect params
    $action_id = $this->_getParam('action_id');
    $viewer = Engine_Api::_()->user()->getViewer();

    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);


    // Redirect if not json context
    if( null === $this->_getParam('format', null) ) {
      $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else if ('json' === $this->_getParam('format', null) ) {
      $this->view->body = $this->view->activity($action, array('viewAllLikes' => true, 'noList' => $this->_getParam('nolist', false)));
    }
  }

  /**
   * Handles HTTP request to like an activity feed item
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/like
   *   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function likeAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Collect params
    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();

    // Start transaction
    $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
    $db->beginTransaction();
    $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
    try {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);

      // Action
      if( !$comment_id ) {

        // Check authorization
        if($coreVersion <= '4.8.5') {
          // Check authorization
          if( $action && !Engine_Api::_()->authorization()->isAllowed($action->getObject(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        } else {
          if( $action && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        }

        $action->likes()->addLike($viewer);

        // Add notification for owner of activity (if user and not viewer)
        if( $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() ) {
          $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);

          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($actionOwner, $viewer, $action, 'liked', array(
            'label' => 'post'
          ));
        }

      }
      // Comment
      else {
        $comment = $action->comments()->getComment($comment_id);

        if(version_compare($coreVersion, '4.8.5') < 0){
          // Check authorization
          if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment, null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        } else {
          // Check authorization
          if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        }

        $comment->likes()->addLike($viewer);

        // @todo make sure notifications work right
        if( $comment->poster_id != $viewer->getIdentity() ) {
          Engine_Api::_()->getDbtable('notifications', 'activity')
              ->addNotification($comment->getPoster(), $viewer, $comment, 'liked', array(
                'label' => 'comment'
              ));
        }

        // Add notification for owner of activity (if user and not viewer)
        if( $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() ) {
          $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);

        }
      }

      // Stats
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.likes');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Success
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('You now like this action.');

    // Redirect if not json context
    if( null === $this->_helper->contextSwitch->getCurrentContext() )
    {
      $this->_helper->redirector->gotoRoute(array(), 'default', true);

    }
    else if ('json'===$this->_helper->contextSwitch->getCurrentContext())
    {
      $method = 'update';
      $this->view->body = $this->view->activity($action, array('noList' => true), $method);
    }
  }

  /**
   * Handles HTTP request to remove a like from an activity feed item
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/unlike
   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function unlikeAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Collect params
    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();

    // Start transaction
    $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
    $db->beginTransaction();
    $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
    try {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);

      // Action
      if( !$comment_id ) {

        if(version_compare($coreVersion, '4.8.5') < 0){
          // Check authorization
          if( !Engine_Api::_()->authorization()->isAllowed($action->getObject(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to unlike this item');
          }
        } else {
          // Check authorization
          if( !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to unlike this item');
          }
        }

        $action->likes()->removeLike($viewer);
      }

      // Comment
      else {
        $comment = $action->comments()->getComment($comment_id);

        if(version_compare($coreVersion, '4.8.5') < 0){
          // Check authorization
          if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment, null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        } else {
          // Check authorization
          if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment') ) {
            throw new Engine_Exception('This user is not allowed to like this item');
          }
        }

        $comment->likes()->removeLike($viewer);
      }

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Success
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('You no longer like this action.');

    // Redirect if not json context
    if( null === $this->_helper->contextSwitch->getCurrentContext() )
    {
      $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    else if ('json'===$this->_helper->contextSwitch->getCurrentContext())
    {
      $method = 'update';
      $this->view->body = $this->view->activity($action, array('noList' => true), $method);
    }
  }

  /**
   * Handles HTTP request to get an activity feed item's comments and returns
   * a Json as the response
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/viewcomment
   *
   * @return void
   */
  public function viewcommentAction()
  {
    // Collect params
    $action_id = $this->_getParam('action_id');
    $viewer    = Engine_Api::_()->user()->getViewer();

    $action    = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    $form      = $this->view->form = new Activity_Form_Comment();
    $form->setActionIdentity($action_id);


    // Redirect if not json context
    if (null===$this->_getParam('format', null))
    {
      $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    else if ('json'===$this->_getParam('format', null))
    {
      $this->view->body = $this->view->activity($action, array('viewAllComments' => true, 'noList' => $this->_getParam('nolist', false)));
    }
  }

  /**
   * Handles HTTP POST request to comment on an activity feed item
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/comment
   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function commentAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Make form
    $this->view->form = $form = new Activity_Form_Comment();

    // Not post
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }

    // Not valid
    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Start transaction
    $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
    $db->beginTransaction();
    $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $action_id = $this->view->action_id = $this->_getParam('action_id', $this->_getParam('action', null));
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
      if (!$action) {
        $this->view->status = false;
        $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
        return;
      }
      $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
      $body = $form->getValue('body');

      if(version_compare($coreVersion, '4.8.5') < 0){
        // Check authorization
        if (!Engine_Api::_()->authorization()->isAllowed($action->getObject(), null, 'comment'))
          throw new Engine_Exception('This user is not allowed to comment on this item.');
      } else {
        // Check authorization
        if (!Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
          throw new Engine_Exception('This user is not allowed to comment on this item.');
      }

      // Add the comment
      $action->comments()->addComment($viewer, $body);

      // Notifications
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');

      // Add notification for owner of activity (if user and not viewer)
      if( $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity() )
      {
        $notifyApi->addNotification($actionOwner, $viewer, $action, 'commented', array(
          'label' => 'post'
        ));
      }

      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->comments()->getAllCommentsUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, $viewer, $action, 'commented_commented', array(
            'label' => 'post'
          ));
        }
      }

      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->likes()->getAllLikesUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, $viewer, $action, 'liked_commented', array(
            'label' => 'post'
          ));
        }
      }

      // Stats
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Assign message for json
    $this->view->status = true;
    $this->view->message = 'Comment posted';

    // Redirect if not json
    if( null === $this->_getParam('format', null) )
    {
      $this->_redirect($form->return_url->getValue(), array('prependBase' => false));
    }
    else if ('json'===$this->_getParam('format', null))
    {
      $method = 'update';
      $show_all_comments = $this->_getParam('show_all_comments');
      //$showAllComments = $this->_getParam('show_all_comments', false);
      $this->view->body = $this->view->activity($action, array('noList' => true), $method, $show_all_comments);
    }
  }

  /**
    * Handles HTTP POST request to share an activity feed item
    *
    * Uses the default route and can be accessed from
    *  - /activity/index/share
    *
    * @return void
    */
  public function shareAction()
  {
      // if (!$this->_helper->requireUser()->isValid()) {
      //     return;
      // }

      $type = $this->_getParam('type');
      $id = $this->_getParam('id');
      $action_id = $this->_getParam('action_id');
      if(isset($action_id)) {
          $actionItem = Engine_Api::_()->getItem('activity_action', $action_id);
      }

      $viewer = Engine_Api::_()->user()->getViewer();
      $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
      $this->view->form = $form = new Activity_Form_Share();
      if(!$this->view->viewer()->getIdentity()){
        $form->removeElement("submit");
        $form->removeElement("cancel");
        $form->removeElement("body");
      }
      if (!$attachment) {
          // tell smoothbox to close
          $this->view->status  = true;
          $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
          $this->view->smoothboxClose = true;
          return $this->render('deletedItem');
      }

      if (!$this->getRequest()->isPost()) {
          return;
      }

      if (!$form->isValid($this->getRequest()->getPost())) {
          return;
      }

      // Process

      $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
      $db->beginTransaction();

      try {
        // Get body
        $body = $form->getValue('body');
        // Set Params for Attachment
        $params = array(
            'type' => '<a href="'.$attachment->getHref().'">'.$attachment->getMediaType().'</a>',
            'privacy' => isset($action_id) ? $actionItem->privacy : (isset($attachment->networks) ? 'network_'. implode(',network_', explode(',',$attachment->networks)) : null),
        );

        // Add activity
        $api = Engine_Api::_()->getDbtable('actions', 'activity');
        //$action = $api->addActivity($viewer, $viewer, 'post_self', $body);
        $action = $api->addActivity($viewer, $attachment->getOwner(), 'share', $body, $params);
        if ($action) {
            $api->attachActivity($action, $attachment);
        }
        
        if(isset($actionItem->share_count)) {
          $actionItem->share_count++;
          $actionItem->save();
        }
        if($attachment->getType() == 'activity_action' && isset($attachment->share_count)) {
          $attachment->share_count++;
          $attachment->save();
        }
        $db->commit();

        // Notifications
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        // Add notification for owner of activity (if user and not viewer)
        if ($action->subject_type == 'user' && $attachment->getOwner()->getIdentity() != $viewer->getIdentity()) {
            $notifyApi->addNotification($attachment->getOwner(), $viewer, $action, 'shared', array(
                'label' => $attachment->getMediaType(),
            ));
        }
      } catch (Exception $e) {
          $db->rollBack();
          throw $e; // This should be caught by error handler
      }

      // If we're here, we're done
      $this->view->status = true;
      $this->view->message =  Zend_Registry::get('Zend_Translate')->_('Shared Successfully!');

      // Redirect if in normal context
      if (null === $this->_helper->contextSwitch->getCurrentContext()) {
          $return_url = $form->getValue('return_url', false);
          if (!$return_url) {
              $return_url = $this->view->url(array(), 'default', true);
          }
          return $this->_helper->redirector->gotoUrl($return_url, array('prependBase' => false));
      } elseif ($action && 'smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
          $this->_forward('success', 'utility', 'core', array(
              'smoothboxClose' => true,
              'parentRefresh'=> true,
              'messages' => array($this->view->message)
          ));
      } elseif ('smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            //'parentRefresh'=> true,
            'messages' => array($this->view->message)
        ));
      }
  }

  /**
   * Handles HTTP POST request to delete a comment or an activity feed item
   *
   * Uses the default route and can be accessed from
   *  - /activity/index/delete
   *
   * @return void
   */
  function deleteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');


    // Identify if it's an action_id or comment_id being deleted
    $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
    $this->view->action_id  = $action_id  = (int) $this->_getParam('action_id', null);

    $action       = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    if (!$action){
      // tell smoothbox to close
      $this->view->status  = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
      $this->view->smoothboxClose = true;
      return $this->render('deletedItem');
    }

    // Send to view script if not POST
    if (!$this->getRequest()->isPost())
      return;

    // Both the author and the person being written about get to delete the action_id
    if(!$comment_id) {
			$subject = Engine_Api::_()->getItem($action->subject_type, $action->subject_id);
    } else {
			$comment = $action->comments()->getComment($comment_id);
			
			if($comment->getType() == 'core_comment') 
				$subject = Engine_Api::_()->getItem($comment->resource_type, $comment->resource_id);
			else	
				$subject = $action;
    }
    if (!$comment_id && (
        $activity_moderate ||
        ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
        ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id) || (($subject && method_exists($subject,'canDeleteComment') && $subject->canDeleteComment($subject)) || (!$subject || ($subject && !method_exists($subject,'canDeleteComment'))))))   // commenter
    {
      // Delete action item and all comments/likes
      $db = Engine_Api::_()->getDbTable('actions', 'activity')->getAdapter();
      $db->beginTransaction();
      try {

        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();

        //Delete photo and video from the table which is post from wall only
        if(engine_in_array($action->type, array('post_self_video', 'post_self_photo_video', 'video_new', 'post_self_photo'))) {

          if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
            $fetchWallPhotos = Engine_Api::_()->getDbTable('photos', 'album')->fetchWallPhotos(array('feedupload' => $action->getIdentity()));
            if(engine_count($fetchWallPhotos) > 0) {
              foreach($fetchWallPhotos as $fetchWallPhoto) {
                $fetchWallPhoto->delete();
              }
            }
          }

          if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')) {
            $fetchWallVideos = Engine_Api::_()->getDbTable('videos', 'video')->fetchWallVideos(array('feedupload' => $action->getIdentity()));
            if(engine_count($fetchWallVideos) > 0) {
              foreach($fetchWallVideos as $fetchWallVideo) {
                $fetchWallVideo->delete();
              }
            }
          }
        }

        $action->deleteItem();

        //Feeling Work
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_activity_feelingposts WHERE action_id = '".$action->getIdentity()."'");


        $db->commit();

        // tell smoothbox to close
        $this->view->status  = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
        $this->view->smoothboxClose = true;
        echo true;die;
        //return $this->render('deletedItem');
      } catch (Exception $e) {
        $db->rollback();
        $this->view->status = false;
      }

    } elseif ($comment_id) {
        $comment = $action->comments()->getComment($comment_id);
        // allow delete if profile/entry owner
        $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
        $db->beginTransaction();
        if ($activity_moderate ||
           ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
           ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id)  || (($subject && method_exists($subject,'canDeleteComment') && $subject->canDeleteComment($subject)) || (!$subject || ($subject && !method_exists($subject,'canDeleteComment')))))
        {
          
          try {
            if (($comment->getType())) {
              
            
            $action->comments()->removeComment($comment_id);
            $db->commit();
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
            $commentModuleEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('comment');

            if( $comment->parent_id && $commentModuleEnable){
              $parentCommentType = 'core_comment';

              if($action->getType() == 'activity_action'){
                $commentType = $action->likes(true);
                if($commentType->getType() == 'activity_action')
                  $parentCommentType = 'activity_comment';
              }
              $parentCommentId = $comment->parent_id;
              $parentComment = Engine_Api::_()->getItem($parentCommentType,$parentCommentId);
              $parentComment->reply_count = new Zend_Db_Expr('reply_count - 1');
              $parentComment->save();

            }
            if($commentModuleEnable){
             $this->view->commentCount = Engine_Api::_()->comment()->commentCount($action);
             $this->view->action = $action;
            }
            $this->view->status  = true;
            $this->view->smoothboxClose = true;
            return $this->render('deletedComment');
          }
          } catch (Exception $e) {
            $db->rollback();
            $this->view->status = false;
          }
        } else {
          $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
          return $this->render('deletedComment');
        }

    } else {
      // neither the item owner, nor the item subject.  Denied!
      $this->_forward('requireauth', 'error', 'core');
    }

  }

  public function getLikesAction()
  {
    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');

    if( !$action_id ||
        !$comment_id ||
        !($action = Engine_Api::_()->getItem('activity_action', $action_id)) ||
        !($comment = $action->comments()->getComment($comment_id)) ) {
      $this->view->status = false;
      $this->view->body = '-';
      return;
    }

    $likes = $comment->likes()->getAllLikesUsers();
    $this->view->body = $this->view->translate(array('%s likes this', '%s like this',
      engine_count($likes)), strip_tags($this->view->fluentList($likes)));
    $this->view->status = true;
  }
  public function suggestAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');

      $usersAllowed = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('messages', $viewer->level_id, 'auth');

      $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
      if( null !== ($text = $this->_getParam('text', $this->_getParam('value'))) ) {
        $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
      }

      if( $this->_getParam('includeSelf', false) ) {
        $data[] = array(
          'type' => 'user',
          'id' => $viewer->getIdentity(),
          'guid' => $viewer->getGuid(),
          'label' => $viewer->getTitle() . ' (you)',
          'title' => $viewer->getTitle(false) . ' (you)',
          'photo' => $this->view->itemPhoto($viewer, 'thumb.icon'),
          'url' => $viewer->getHref(),
        );
      }

      if( 0 < ($limit = (int) $this->_getParam('limit', 10)) ) {
        $select->limit($limit);
      }

      $ids = array();
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
        $data[] = array(
          'type'  => 'user',
          'id'    => $friend->getIdentity(),
          'guid'  => $friend->getGuid(),
          'label' => $friend->getTitle(),
          'title' => $friend->getTitle(false),
          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
          'url'   => $friend->getHref(),
        );
        $ids[] = $friend->getIdentity();
        //$friend_data[$friend->getIdentity()] = $friend->getTitle();
      }
    }

    if( $this->_getParam('sendNow', true) ) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
  public function getMemberallAction() {

    $data = array();
    $userTable = Engine_Api::_()->getItemTable('user');
    $selectUserTable = $userTable->select()->where('displayname LIKE "%' . $this->_getParam('text', '') . '%"');
    $users = $userTable->fetchAll($selectUserTable);
    foreach ($users as $user) {
      $user_icon = $this->view->itemPhoto($user, 'thumb.icon');
      $data[] = array(
          'id' => $user->user_id,
          'user_id' => $user->user_id,
          'label' => $user->getTitle(),
          'photo' => $user_icon
      );
    }
    return $this->_helper->json($data);
  }
  public function editPostAction(){
   try{
    $this->view->composerOptions = $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composeroptions',array());
    $this->view->userphotoalign = $this->_getParam('userphotoalign', 'left');

    $action_id = $this->_getParam('action_id',false);
    $this->view->action = $action =  Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    if(!$action)
      throw new Engine_Exception('Not Valid Action');
    //fetch networks
       $viewer = $this->view->viewer();

       $this->view->allownetworkprivacy = $allownetworkprivacytype = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy',0);

       $this->view->allowlistprivacy = 1;
       if($allownetworkprivacytype == 1){
           $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
       }
       else if($allownetworkprivacytype == 2){
           $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
       }
       else{
           $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->where(0);
       }
       $this->view->usernetworks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

       //fetch lists
       $this->view->userlists = Engine_Api::_()->getDbtable('lists', 'user')->fetchAll(Engine_Api::_()->getDbtable('lists', 'user')->select()->order('engine4_user_lists.title ASC')->where('owner_id =?',$viewer->getIdentity()));
    if($action->type == 'post_self_buysell')
    {
       $this->view->item = $buysell =  $action->getBuySellItem();
       $this->view->locationBuySell = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type' => 'activity_buysell', "resource_id" => $buysell->getIdentity()));
    }
    //fetch target post data
    $this->view->targetPost = Engine_Api::_()->getDbTable('targetpost','activity')->getTargetPost($action->getIdentity());
    //fetch location
    $this->view->location = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type' => 'activity_action', 'resource_id' => $action->getIdentity()));
    $this->view->members = Engine_Api::_()->getDbTable('tagusers','activity')->getActionMembers($action_id);;

    //Feeling Work

      $this->view->feelings = $feelings = Engine_Api::_()->getDbTable('feelingposts','activity')->getActionFeelingposts($action_id);

      $this->view->feeling = $feeling = Engine_Api::_()->getItem('activity_feeling', $feelings->feeling_id);

      if($feelings->resource_type != 'undefined' && $feelings->resource_type != '') {
        //$this->view->feelingIcons = Engine_Api::_()->getItem('activity_feelingicon', $feeling->file_id);
        $resource = Engine_Api::_()->getItem($feelings->resource_type, $feelings->feelingicon_id);
        $this->view->feeling_Icons = $feeling->file_id;
        $this->view->feelingIcons_title = $resource->getTitle();
      } else {
        $this->view->feelingIcons = $feelingIcons = Engine_Api::_()->getItem('activity_feelingicon', $feelings->feelingicon_id);
        $this->view->feeling_Icons = $feelingIcons->feeling_icon;
        $this->view->feelingIcons_title = $feelingIcons->title;
      }
    //Feeling Work


   }catch(Exception $e){
      throw $e;
   }
   $mentionUserData = array();
    preg_match_all('/(^|\s)(@\w+)/', $action->body, $result);
    foreach($result[2] as $value){
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $user = Engine_Api::_()->getItem('user',$user_id);
         if(!$user)
          continue;
          $id = $user->getIdentity();
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type = implode('_',$itemArray);
          $user = Engine_Api::_()->getItem($resource_type,$resource_id);
          if(!$user)
            continue;
          $id = $user->getGuid();
        }

        $mentionUserData[] = array(
          'type'  => 'user',
          'id'    => $id,
          'name' => $user->getTitle(false),
          'avatar' => $this->view->itemPhoto($user, 'thumb.icon'),
        );
    }
    $this->view->mentionData = $mentionUserData;
    $this->renderScript('_editPostComposer.tpl');
  }
  public function editFeedPostAction()
  {
    $this->view->error = 'An error occured. Please try again after some time.';
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) return;

    // Get subject if necessary
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = $viewer;
    $this->view->userphotoalign = $this->_getParam('userphotoalign', 'left');

    // Check auth
    if( !$subject->authorization()->isAllowed($viewer, 'comment') ) {
      return $this->_helper->requireAuth()->forward();
    }

    $form = new Activity_Form_Post();

    // Check if post
    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
      return;
    }

    // Check if form is valid
    $postData = $this->getRequest()->getPost();
    $body = $postData['body'];
    $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
    $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
    //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');
    $postData['body'] = $body;

    if( !$form->isValid($postData) ) {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Get body
    $body = $form->getValue('body');
    $body = preg_replace('/<br[^<>]*>/', "\n", $body);
    //string contain url only.
    // Try attachment getting stuff

    // If we're here, we're done
    $this->view->status = true;
    try {
      $action_id = $this->_getParam('action_id',false);
      $this->view->action = $action =  Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
      if(!$action)
        throw new Engine_Exception('Not Valid Action');
      // Get body
      //$body = $postData['bodyText'];
      $body = preg_replace('/<br[^<>]*>/', "\n", $body);


      // Add activity
      $action->body = $body;
      $action->privacy = $_POST['privacy'];
      //tag location in post
      if(engine_count($action->params)){
        $params = $action->params;
        if(!empty($params['body']))
          unset($params['body']);
        $action->params = $params;
      }
      $action->save();
      //Feeling Work
        if(!empty($_POST['feelingactivityidedit']) && !empty($_POST['feelingactivityiconidedit']) && !empty($_POST['feeling_activityedit']) && empty($_POST['feelingactivity_customedit'])) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_activity_feelingposts WHERE action_id = '".$action->getIdentity()."'");
          $dbGetInsert->query('INSERT IGNORE INTO `engine4_activity_feelingposts` (`feeling_id`, `feelingicon_id`, `resource_type`, `action_id`) VALUES ("'.$_POST['feelingactivityidedit'].'", "'.$_POST['feelingactivityiconidedit'].'" ,"'.$_POST['feelingactivity_resource_typeedit'].'", "'.$action->getIdentity().'")');
        } else if(!empty($_POST['feelingactivity_customedit'])) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_activity_feelingposts WHERE action_id = '".$action->getIdentity()."'");
          $dbGetInsert->query('INSERT IGNORE INTO `engine4_activity_feelingposts` (`feeling_id`, `feelingicon_id`, `resource_type`, `action_id`, `feeling_custom`, `feeling_customtext`) VALUES ("'.$_POST['feelingactivityidedit'].'", "'.$_POST['feelingactivityiconidedit'].'" ,"'.$_POST['feelingactivity_resource_typeedit'].'", "'.$action->getIdentity().'", "'.$_POST['feelingactivity_customedit'].'", "'.$_POST['feelingactivity_customtextedit'].'")');
        } else {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $dbGetInsert->query("DELETE FROM engine4_activity_feelingposts WHERE action_id = '".$action->getIdentity()."'");
        }

      if(!empty($_POST['tag_location']) && !empty($_POST['activitylng']) && !empty($_POST['activitylat'])){
        //check location
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $action->getIdentity() . '", "' . $_POST['activitylat'] . '","' . $_POST['activitylng'] . '","activity_action","'.$_POST['tag_location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $_POST['activitylat'] . '" , lng = "' . $_POST['activitylng'] . '",venue="'.$_POST['tag_location'].'"');
      } else {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query("DELETE FROM engine4_core_locations WHERE resource_id = '".$action->getIdentity()."' AND resource_type = 'activity_action'");
      }

      //tag friend in post
      if(!empty($_POST['tag_friends'])) {
        $dbGetInsert->query("DELETE FROM engine4_activity_tagusers WHERE action_id = '".$action->getIdentity()."'");
        if(empty($dbGetInsert))
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $tagUsers = array_unique(explode(",", $_POST['tag_friends']));
        if(engine_count($tagUsers)){
          $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
          foreach($tagUsers  as $tagUser){
              $dbGetInsert->query('INSERT INTO `engine4_activity_tagusers` (`user_id`, `action_id`) VALUES ("'.$tagUser.'", "'.$action->getIdentity().'")');
            //Notification work
            $item = Engine_Api::_()->getItem('user', $tagUser);
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, $viewer, $item, 'activity_tagged_people', array("postLink" => $postLink));
          }
        }
      } else {
        $dbGetInsert->query("DELETE FROM engine4_activity_tagusers WHERE action_id = '".$action->getIdentity()."'");
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['body'], $result);
      $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type,$resource_id);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
      }
      //Tagging People by status box

      // extrack #  tag value from post
      if($action){
        $dbGetInsert->query("DELETE FROM engine4_activity_hashtags WHERE action_id = '".$action->getIdentity()."'");
         preg_match_all("/(#\w+)/u", $action->body, $matches);
         if(engine_count($matches)){
          if(empty($dbGetInsert))
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $hashtags = array_unique($matches[0]);
          foreach($hashtags  as $hashTag){
           $dbGetInsert->query('INSERT INTO `engine4_activity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.str_replace('#','',$hashTag).'")');
          }
         }
      }
      if($action->type == 'post_self_buysell')
      {
        $buysell = $action->getBuySellItem();
        $buysell->title = $_POST['buysell-title'];
        $buysell->buy = $_POST['buy-url'];
        $buysell->description = $_POST['buysell-description'];
        $buysell->price = $_POST['buysell-price'];
        $buysell->currency = $_POST['buysell-currency'];
        $buysell->location = $_POST['buysell-location'];
        $buysell->save();
        if(!empty($_POST['buysell-location']) && !empty($_POST['activitybuyselllng']) && !empty($_POST['activitybuyselllat'])){
         $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
         $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $buysell->getIdentity() . '", "' . $postData['activitybuyselllat'] . '","' . $postData['activitybuyselllng'] . '","activity_buysell","'.$postData['buysell-location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $postData['activitybuyselllat'] . '" , lng = "' . $postData['activitybuyselllng'] . '",venue="'.$postData['buysell-location'].'"');
        }
      }
      if(empty($dbGetInsert))
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
      $dbGetInsert->query('DELETE FROM engine4_activity_targetpost WHERE action_id ='.$action->getIdentity());
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

      //reset privacy
      if(!$action->schedule_time)
        Engine_Api::_()->getDbTable('actions','activity')->resetActivityBindings($action);
      // Preprocess attachment parameters
      $action->save();
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Success');
    } catch( Exception $e ) {
      //$db->rollBack();
      throw $e;
      $this->view->status = false;
      if(!empty($_GET['is_ajax']))
        $this->view->error = 'An error occured. Please try again after some time.';
      else
        throw $e;
    }

    $feed = $this->view->activity($action,array('ulInclude'=>true, 'userphotoalign' => $this->view->userphotoalign));
    $last_id = $action->getIdentity();
    echo json_encode(array('status'=> $this->view->status,'last_id'=>$last_id,'feed'=>$feed,'error'=>$this->view->error),JSON_HEX_QUOT | JSON_HEX_TAG);die;
  }
  public function reschedulePostAction(){
    $action_id = $this->_getParam('action_id',false);
    $value = $this->_getParam('value',false);
    $action = Engine_Api::_()->getItem('activity_action',$action_id);


    if($action && $action->schedule_time){
        $str = str_replace('_','/',$value);
        $date = DateTime::createFromFormat('d/m/Y H:i:s', $str);

        $time = $date->format('Y-m-d H:i:s');
        $timeZone = date_default_timezone_get();
        date_default_timezone_set(Engine_Api::_()->user()->getViewer()->timezone);
        $time = strtotime($time);
        date_default_timezone_set($timeZone);
        $schedulePost = date('Y-m-d H:i:s',$time);
        $action->schedule_time = $schedulePost;
        $action->save();
        $feed = $this->view->activity($action,array('ulInclude'=>true));
       $last_id = $action->getIdentity();
       echo json_encode(array('status'=> true,'last_id'=>$last_id,'feed'=>$feed),JSON_HEX_QUOT | JSON_HEX_TAG);die;
    }
   echo json_encode(array('status'=> false,'last_id'=>'','feed'=>''),JSON_HEX_QUOT | JSON_HEX_TAG);die;
  }
  public function pintotopAction(){
    $action_id = $this->_getParam('action_id','0');
    $res_id = $this->_getParam('res_id');
    $res_type = $this->_getParam('res_type');
    if($action_id){
      $db = Engine_Db_Table::getDefaultAdapter();

      $result = $db->query("SELECT * FROM engine4_activity_pinposts WHERE resource_id = '".$res_id."' AND resource_type = '".$res_type."'");
      $res = $result->fetchAll();
      if(engine_count($res)){
        $db->query("DELETE FROM engine4_activity_pinposts WHERE resource_id = '".$res_id."' AND resource_type = '".$res_type."'");
      }else{
        $db->query("INSERT INTO `engine4_activity_pinposts` (`action_id`, `resource_id`, `resource_type`) VALUES ('".$action_id."','".$res_id."','".$res_type."')");
      }
      return $this->_helper->redirector->gotoUrl($_GET["url"], array('prependBase' => false));
    }
  }
  
  public function downloadAction() {

    $file_id = $this->_getParam('file_id', null);
    if (!empty($file_id) && $file_id) {
      $storageTable = Engine_Api::_()->getDbTable('files', 'storage');
      $select = $storageTable->select()->from($storageTable->info('name'), array('storage_path', 'name'))->where('file_id = ?', $this->_getParam('file_id'));
      $storageData = $storageTable->fetchRow($select);
      
      $storage = Engine_Api::_()->getItem('storage_file', $this->_getParam('file_id'));
      $basePath = $storage->map();
      if($storage->service_id == 1)
        $basePath = APPLICATION_PATH . '/' . $storageData->storage_path;
        
      $storageData = (object) $storageData->toArray();
      if (empty($storageData->name) || $storageData->name == '' || empty($storageData->storage_path) || $storageData->storage_path == '')
        return;
      
      if($storage->service_id != 1) {
        
        $details = Engine_Api::_()->getDbTable('services', 'storage')->getServiceDetails();
        $config = Zend_Json::decode($details->config);

        $s3 = new Zend_Service_Amazon_S3($config['accessKey'], $config['secretKey'], $config['region']);
        $object = $s3->getObject($config['bucket'].'/'. $storageData->storage_path);
        $info = $s3->getInfo($config['bucket'].'/'. $storageData->storage_path);

        header("Content-Disposition: attachment; filename=" . urlencode(basename($storageData->name)), true);
        header("Content-Transfer-Encoding: Binary", true);
        header('Content-Type: ' . $info['type']);
        header("Content-Type: application/force-download", true);
        header("Content-Type: application/octet-stream", true);
        header("Content-Type: application/download", true);
        header("Content-Description: File Transfer", true);
        header("Content-Length: " . $info['size'], true);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        //send file to browser for download. 
        ob_clean();
        flush();
        echo $object;
        exit();
      } else {
        @chmod($basePath, 0777);
        header("Content-Disposition: attachment; filename=" . urlencode(basename($storageData->name)), true);
        header("Content-Transfer-Encoding: Binary", true);
        header("Content-Type: application/force-download", true);
        header("Content-Type: application/octet-stream", true);
        header("Content-Type: application/download", true);
        header("Content-Description: File Transfer", true);
        header("Content-Length: " . filesize($basePath), true);
        readfile("$basePath");
        exit();
        // for safety resason double check
        return;
      }
    }
    return $this->_forward('notfound', 'error', 'core');
  }
  public function getfeelingiconsAction() {

    $feeling_id = $this->_getParam('feeling_id', null);
    $feeling_type = $this->_getParam('feeling_type', null);
    $text = $this->_getParam('text', null);
    $edit = $this->_getParam('edit', 0);
    
    $table = Engine_Api::_()->getDbtable('feelingicons', 'activity');
    
    if ($feeling_type == 1) {
      
      $select = $table->select()->where('type =?', $feeling_type)->order('feeling_id DESC');
      if($text != 'default')
        $select->where('title LIKE ?', $text . '%');

      if (!empty($feeling_id))
        $select->where('feeling_id =?', $feeling_id);

      $results = $table->fetchAll($select);
      
    } else if($feeling_type == 2) {
    
      $select = $table->select()
                      ->where('feeling_id =?', $feeling_id)
                      ->where('type =?', $feeling_type);
      $results = $table->fetchAll($select);
      $resource_typeArray = array();
      foreach($results as $result) {
        $resource_typeArray[] = $result->resource_type;
      }
      
      $searchtable = Engine_Api::_()->getDbtable('search', 'core');
      $select = $searchtable->select()
                            ->where('type in(?)', $resource_typeArray)
                            ->order('id DESC');
      if($text != 'default')
        $select->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', $text . '%');
      $results = $searchtable->fetchAll($select);
    }
          
    $feelingsIcon = Engine_Api::_()->getItem('activity_feeling', $feeling_id);

    $html = '';
    foreach ($results as $result) {
      if ($feeling_type == 1) {

        if($edit) {
          $liClassName = 'activity_feelingactivitytypeliedit';
        } else {
          $liClassName = 'activity_feelingactivitytypeli';
        }
        
        $html .= '<li data-title="'.$result->title.'" class="'.$liClassName.' clearfix" data-rel='.$result->feelingicon_id.'><a href="javascript:void(0);"><img class="feeling_icon" title="'.$result->title.'" src="'.Engine_Api::_()->storage()->get($result->feeling_icon, "")->getPhotoUrl().'"><span>'.$result->title.'</span></a></li>';

      } else {

        $itemType = $result->type;
        if (Engine_Api::_()->hasItemType($itemType)) {
        
          $item = Engine_Api::_()->getItem($itemType, $result->id);
          if($item) {
            $photo_icon_photo = $this->view->itemPhoto($item, 'thumb.icon');
            if($edit) {
              $liClassName = 'activity_feelingactivitytypeliedit';
            } else {
              $liClassName = 'activity_feelingactivitytypeli';
            }
            
            $html .= '<li data-type="'.$itemType.'" data-icon="'.Engine_Api::_()->storage()->get($feelingsIcon->file_id, "")->getPhotoUrl().'" data-title="'.$item->getTitle().'" class="'.$liClassName.' clearfix" data-rel='.$result->id.'><a href="javascript:void(0);">'.$photo_icon_photo.'<span>'.$item->getTitle().'</span></a></li>';
          }
        }
      }
    }
    echo Zend_Json::encode(array('status' => 1, 'html' => $html));exit();
  }
  
  public function gifAction() {
    $this->view->edit = $this->_getParam('edit',false);
    $this->renderScript('_gif.tpl');
  }
  
  public function searchGifAction() {
  
    $page = $this->_getParam('page', 1);
    $text = $this->_getParam('text','ha');
    $this->view->is_ajax = $this->_getParam('is_ajax', 1);
    $this->view->searchvalue = $this->_getParam('searchvalue', 0);
    $paginator = $this->view->paginator = Engine_Api::_()->getDbTable('images', 'activity')->searchGif($text);
		$paginator->setItemCountPerPage(10);
		$this->view->page = $page ;
		$paginator->setCurrentPageNumber($page);
  }
  public function feelingemojicommentAction() { 
    $this->view->edit = $this->_getParam('edit',false);
    $this->renderScript('_feelingemojicomment.tpl');
  }
}
