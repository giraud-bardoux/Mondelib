<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: FeedController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Activity_FeedController extends Sesapi_Controller_Action_Standard {
    function reportAction(){
      $value['description'] = $this->_getParam('text','');
      $value['value'] = $this->_getParam('value','');
  
      $table = Engine_Api::_()->getDbTable('reports','sescommunityads');
      $report = $table->createRow();
      $value['item_id'] = $this->_getParam('ad_id');
      $value['user_id'] = $this->view->viewer()->getIdentity();
      $value['ip'] = $_SERVER['REMOTE_ADDR'];
      $report->setFromArray($value);
      $report->save();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Report submitted successfully.")));
    }
     public function usefulAction() {
      $sescommunityad_id = $this->_getParam('ad_id');
      $ad = Engine_Api::_()->getItem('sescommunityads',$sescommunityad_id);
      $isUseful = $ad->isUseful();
      if(!$isUseful){
        $table = Engine_Api::_()->getDbTable('usefulads','sescommunityads');
        $usefulads = $table->createRow();
        $value['item_id'] = $this->_getParam('sescommunityad_id');
        $value['user_id'] = $this->view->viewer()->getIdentity();
        $value['ip'] = $_SERVER['REMOTE_ADDR'];
        $usefulads->setFromArray($value);
        $usefulads->save();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Advertisement marked useful successfully.")));
      }else{
        $isUseful->delete();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Advertisement removed from  useful successfully.")));
      }
    }
    function attributionChangeAction(){
        $guid = $this->_getParam('guid',false);
        if(!$guid)
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_request', 'result' => array())); 
        $isPageSubject = Engine_Api::_()->getItemByGuid($guid);  
        $action =  Engine_Api::_()->getItem('activity_action',$this->_getParam('action_id'));
        $likesGroup = Engine_Api::_()->comment()->likesGroup($action);
        $reactionData = array();
        $reactionCounter = 0;
        $counter = 0;
        if(engine_count($likesGroup['data'])){
          $activity['likeUserStats']['resource_type'] = $likesGroup['resource_type'];
          $activity['likeUserStats']['item_id'] = $likesGroup['resource_id'];
          foreach($likesGroup['data'] as $type){
            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],$this->view->translate(Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type'])));
            $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);;
            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            $reactionCounter++;
          } 
        }
        $activity['reactionUserData'] = $this->view->FluentListUsers($action->likes()->getAllLikes(),'',$action->likes()->getLike($this->view->viewer()),$this->view->viewer());
        if(engine_count($reactionData))
          $activity['reactionData'] = $reactionData;
        
        if($likeRow =  $action->likes()->getLike($isPageSubject) ){ 
           $activity['is_like'] = true;
            $like = true;
            $type = $likeRow->type;
            $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
            $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
         }else{
           $activity['is_like'] = false;
            $like = false;
            $type = '';
            $imageLike = '';
            $text = 'Like';
         }
        
        if(empty($like)) {
            $activity["like"]["name"] = "like";
        }else {
            $activity["like"]["name"] = "unlike";
        }
        $activity["like"]["type"] = $type;
        $activity["like"]["image"] = $imageLike;
        $activity["like"]["title"] = $this->view->translate($text);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $activity));  
  }
  
    public function saveAction(){
      $activity_id = $this->_getParam('activity_id'); 
      
      $activity = Engine_Api::_()->getItem('activity_action',$activity_id);
      if(!$activity_id || !$activity){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));  
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      $isSaved = Engine_Api::_()->getDbTable('savefeeds','activity')->isSaved(array('action_id'=>$activity_id,'user_id'=>$viewer->getIdentity()));
      if($isSaved){
        $isSaved->delete();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>1)); 
      }else{    
        $db = Engine_Db_Table::getDefaultAdapter();  
        $data = array(
            'action_id'      => $activity_id,
            'user_id' => $viewer->getIdentity(),
        );
       $db->insert('engine4_activity_savefeeds', $data);
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>0));
      }
    } 
    public function getPhotos($paginator){
      $result = array();
    $counter = 0;
    $canFavourite =  Engine_Api::_()->authorization()->isAllowed('album',Engine_Api::_()->user()->getViewer(), 'favourite_photo');
    foreach($paginator as $photos){
        $photos = $photos->item;
        $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");  
        $album_photo['photo_id'] = $photos->getIdentity();              
        if(!engine_count($album_photo['images']))
          $album_photo['images']['main'] = $this->getBaseUrl(true,$photos->getPhotoUrl());
        $result[$counter] = $album_photo;
        $counter++;
    }
    return $result;
  }
    public function buysellLightboxAction(){
      $action_id = $this->_getParam('album_id');
       $action = Engine_Api::_()->getItem('activity_action',$action_id);
       $array_merge = $this->getPhotos($action->getAttachments());
       //foreach( $action->getAttachments() as $attachment){          
          if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')){
            $recArray = array();
            $reactions = Engine_Api::_()->getDbTable('reactions','comment')->getPaginator();
            $counterReaction = 0;
            
            foreach($reactions as $reac){
              if(!$reac->enabled)
                continue;
              $albumData['reaction_plugin'][$counterReaction]['reaction_id']  = $reac['reaction_id'];
              $albumData['reaction_plugin'][$counterReaction]['title']  = $this->view->translate($reac['title']);
              $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
              $albumData['reaction_plugin'][$counterReaction]['image']  = $icon['main'];
              $counterReaction++;
            }            
         // }
       }
        $albumData['photos'] = $array_merge;
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData)));
    }
    public function buysellsoldAction(){
      $action_id = $this->_getParam('action_id',false);
      if(!$action_id){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));  
      }
      $action = Engine_Api::_()->getItem('activity_action',$action_id);
      $item = $action->getBuySellItem();
      $item->is_sold = 1;
      $item->save();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>1));
    }
    function commentAction(){
      $activity_id = $this->_getParam('activity_id'); 
      $activity = Engine_Api::_()->getItem('activity_action',$activity_id);
      if(!$activity_id || !$activity){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));  
      }    
      
        $activity->commentable = !$activity->commentable;
        $activity->save();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' =>1));
    
    }
    function editAction(){
      // Make sure user exists
      if( !$this->_helper->requireUser()->isValid() ) 
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    
      // Get subject if necessary
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = $viewer;
       // Check if post
      if( !$this->getRequest()->isPost() ) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
        return;
      }
      $sesAdv = false;
      if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
      }else{
          if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
              $sesAdv = false;
          }
      }
      if($sesAdv)
        $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      else 
        $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      $action_id = $this->_getParam('activity_id',false);
      $action =  $actionTable->getActionById($action_id);
      if(!$action)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
      $body = $this->_getParam('body',$_POST['body']);
      $body = preg_replace('/<br[^<>]*>/', "\n", $body);
      //Emojis Work
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
          $body = str_replace($bodyEmoji,$emojisCode,$body);
        }
      }
       $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
      //tag friend in post
      if(!empty($_POST['tag_friends']) && $actionTable) {
        $dbGetInsert->query("DELETE FROM engine4_activity_tagusers WHERE action_id = '".$action->getIdentity()."'");
        if(empty($dbGetInsert))
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          $tagUsers = array_unique(explode(",", $_POST['tag_friends']));
        if(engine_count($tagUsers)){
          $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
          foreach($tagUsers  as $tagUser){
            //Notification work
            $item = Engine_Api::_()->getItem('user', $tagUser);
            if(!$item)
              continue;
            $dbGetInsert->query('INSERT INTO `engine4_activity_tagusers` (`user_id`, `action_id`) VALUES ("'.$tagUser.'", "'.$action->getIdentity().'")');
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
          }
        }
      }/* else {
        $dbGetInsert->query("DELETE FROM engine4_activity_tagusers WHERE action_id = '".$action->getIdentity()."'");  
      }*/
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
         //Tagging People by status box
        preg_match_all('/(^|\s)(@\w+)/', $_POST['body'], $result);
        $postLink = '<a href="' . $action->getHref() . '">' . "post" . '</a>';
        foreach($result[2] as $value) {
          $user_id = str_replace('@_user_','',$value);
          $item = Engine_Api::_()->getItem('user', $user_id);
          if($item)
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, $viewer, $viewer, 'activity_tagged_people', array("postLink" => $postLink));
        }
        if($body){
          // getting old params.
          // $activityParams = $action->params;
          // if(!empty($activityParams['body'])){
          //   unset($activityParams['body']);
          //   $action->params = $activityParams;
          //   $action->save();
          // }
          if(engine_count($action->params)){
            $params = $action->params;
            if(!empty($params['body']))
              unset($params['body']);
            $action->params = $params;
          }
        }
        $values = $this->_getAllParams();
        $privacy = 'privacy_'.$action->getIdentity();
        $privacyValue = (($values['networkprivacy']=="multi_networks") ? $values[$privacy] : $values['networkprivacy']);
        $values['privacy'] = $privacyValue ?? $action->privacy;
        $action->setFromArray($values);
        $action->save();
        if(isset($values['privacy'])){
            // Rebuild privacy
            $actionTable->resetActivityBindings($action);
        }
        //Tagging People by status box
        $action->body = $body;
        $action->save();
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => 1));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage()));
      }
    }
    function deleteAction(){
      if( !$this->_helper->requireUser()->isValid() ) 
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $viewer = Engine_Api::_()->user()->getViewer();
      $activity_moderate = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');

      // Identify if it's an action_id or comment_id being deleted
      $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
      $this->view->action_id  = $action_id  = (int) $this->_getParam('activity_id', $this->_getParam('action_id'));
      $sesAdv = false;
      if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
          $sesAdv = true;
      }else{
          if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
              $sesAdv = false;
          }
      }
      if($sesAdv)
        $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      else 
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      
      $action = $actionTable->getActionById($action_id);
      if (!$action){
        // tell smoothbox to close
        $this->view->status  = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => array()));
      }

      // Send to view script if not POST
      if (!$this->getRequest()->isPost())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));

      // Both the author and the person being written about get to delete the action_id
      if (!$comment_id && (
          $activity_moderate ||
          ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
          ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id)))   // commenter
      {
        // Delete action item and all comments/likes
        $db = $actionTable->getAdapter();
        $db->beginTransaction();
        try {
          $action->deleteItem();
          $db->commit();

          // tell smoothbox to close
          $this->view->status  = true;
          $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> "", 'result' => $this->view->message ));
          //return $this->render('deletedItem');
        } catch (Exception $e) {
          $db->rollback();
          $this->view->status = false;
        }
 
      } elseif ($comment_id) {
          $comment = $action->comments()->getComment($comment_id);
          // allow delete if profile/entry owner
          if($sesAdv)
            $db = Engine_Api::_()->getDbTable('comments', 'activity')->getAdapter();
          else 
            $db = Engine_Api::_()->getDbTable('comments', 'activity')->getAdapter();
          $db->beginTransaction();
          if ($activity_moderate ||
            ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
            ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id))
          {
            try {
              $action->comments()->removeComment($comment_id);
              $db->commit();
              $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
              $commentModuleEnableSes = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment');
              if($comment->parent_id && $commentModuleEnableSes){
                $parentCommentType = 'core_comment';
                
                
                $parentCommentId = $comment->parent_id; 
                $parentComment = Engine_Api::_()->getItem($parentCommentType,$parentCommentId);
                $parentComment->reply_count = new Zend_Db_Expr('reply_count - 1');
                $parentComment->save();
              }
              if($commentModuleEnableSes){
              $this->view->commentCount = Engine_Api::_()->comment()->commentCount($action);
              $this->view->action = $action;
              }
              $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment deleted successfully.');
              Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> "", 'result' => $this->view->message ));
            } catch (Exception $e) {
              $db->rollback();
              $this->view->status = false;
            }
          } else {
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->message, 'result' => array() ));
          }
        
      } else {
        // neither the item owner, nor the item subject.  Denied!
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
      }  
    }
    public function hiddenAction(){
      $action_id = $this->_getParam('activity_id',false); 
      $remove = $this->getParam('remove',false);
      $type =  $this->_getParam('type','post');
      $viewer = Engine_Api::_()->user()->getViewer();
      if( !$this->_helper->requireUser()->isValid() || !$action_id) 
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
     $db = Engine_Db_Table::getDefaultAdapter();      
      $resource_id = $action_id;
      $id = Engine_Api::_()->getItem('activity_action',$action_id)->getSubject()->getIdentity();  
      $db->delete('engine4_activity_hides', array(
          'resource_id =?'      => $id,
          'resource_type =?'    => 'user',
          'user_id =?' => $viewer->getIdentity(),
      ));
      if(!$remove){
      $data = array(
            'resource_id'      => $resource_id,
            'resource_type'    => $type,
            'user_id' => $viewer->getIdentity(),
        );
        $db->insert('engine4_activity_hides', $data);
      }else{
        $db->delete('engine4_activity_hides', array(
            'resource_id =?'      => $resource_id,
            'resource_type =?'    => $type,
            'user_id =?' => $viewer->getIdentity(),
        ));  
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => 1));
      
    }
    
  public function likeAction()
  {
    header('Access-Control-Allow-Origin: *'); 
    header('Access-Control-Allow-Methods: POST, GET');  
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));  
    $guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);  
      $guidUser = $guid->getOwner();
      if(!$guid) 
        $guid = "";
    }else{
        $guid = "";  
    }
    // Collect params
    $action_id = $this->_getParam('activity_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $sbjecttype = $this->_getParam('sbjecttype',false);
    $subjectid = $this->_getParam('subjectid',false);

    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    if($sesAdv)
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    else
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    // Start transaction
   // $db = Engine_Api::_()->getDbTable('likes', 'activity')->getAdapter();
   // $db->beginTransaction();
    try {
      if(!$sbjecttype)
        $action = $actionTable->getActionById($action_id);
      else
        $action = Engine_Api::_()->getItem($sbjecttype,$subjectid);
      // Action
      if( !$comment_id ) {
        // Check authorization
        if( $action && !$sbjecttype && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') ) {
          $this->view->error = ('This user is not allowed to like this item');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));  
        }
        if(!$guid){
           $isLike = $action->likes()->getLike($viewer);
          if( $isLike ) {
            
            $action->likes()->removeLike($viewer);
          }
          $like = $action->likes()->addLike($viewer);
        }else{
          $isLike = $action->likes()->getLike($guid);
          if($isLike){
            
            $action->likes()->removeLike($guid);
          }
          $like = $action->likes()->addLike($guid);
        } 
        
        $reactedType = $this->_getParam('type',1);
        // Add notification for owner of activity (if user and not viewer)
        if( ($action->getType() == "activity_action" && $action->subject_type == 'user') && ((($action->getType() != "activity_action" && $action->getOwner()->getIdentity() != $viewer->getIdentity()) || $action->subject_id != $viewer->getIdentity() ) || ($guid && $guidUser && $guidUser->getIdentity() != $action->subject_id )) ) {
          if($action->getType() == "activity_action")
            $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
          else
            $actionOwner = $action->getOwner();
          $senderObject = !empty($guid) ? $guidUser : $viewer;
          if($reactedType == 1) {
            //Remove Previous Notification
            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'liked', "subject_id =?" => $senderObject->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));
            if($sesAdv) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($actionOwner,$senderObject, $action, 'liked', array('label' => 'post'));
            }
          } else if($sesAdv) {
            if($reactedType == 2)
              $notiType = 'activity_reacted_love';
            elseif($reactedType == 3)
              $notiType = 'activity_reacted_haha';
            elseif($reactedType == 4)
              $notiType = 'activity_reacted_wow';
            elseif($reactedType == 5)
              $notiType = 'activity_reacted_angry';
            elseif($reactedType == 6)
              $notiType = 'activity_reacted_sad';
            //Remove previous notification
            $reaction_array = array('liked', 'activity_reacted_love', 'activity_reacted_haha', 'activity_reacted_wow', 'activity_reacted_angry', 'activity_reacted_sad');
            foreach($reaction_array as $reactionr) {
              Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $reactionr, "subject_id =?" => $senderObject->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));
            }
            //Send Reaction Notification
            if($notiType)
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($actionOwner,$senderObject, $action, $notiType, array('label' => 'post'));
          }
        }
      }
      // Comment
      else {
        
        $comment = $action->comments()->getComment($comment_id);
        // Check authorization
        if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment') ) {
          $this->view->error = ('This user is not allowed to like this item');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));  
        }
        if(empty($guid)){
          $isLike = $comment->likes()->getLike($viewer);
          if($isLike){
            $comment->like_count = new Zend_Db_Expr('like_count - 1');;
            $comment->save();
            
            $comment->likes()->removeLike($viewer);
          }
         $like = $comment->likes()->addLike($viewer);
        }
        else{
          $isLike = $comment->likes()->getLike($guid);
          if($isLike){
            $comment->like_count = new Zend_Db_Expr('like_count - 1');;
            $comment->save();
            
            $comment->likes()->removeLike($guid);
          }
         $like = $comment->likes()->addLike($guid);
        }
     
      $reactedType = $this->_getParam('type',1);
       if(($guid && $guidUser  && $guidUser->getIdentity() != $comment->poster_id)  || $comment->poster_id != $viewer->getIdentity() && $sesAdv) {
          if($comment->getPoster()->getType() == "user")
            $ownerNoti = $comment->getPoster();
          else
            $ownerNoti = $comment->getPoster()->getOwner();
          $reactedType = $this->_getParam('type',1);
          
          if($reactedType == 1) {
            //Remove Previous Notification
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($ownerNoti, !empty($guid) ? $guidUser : $viewer, $comment, 'liked', array('label' => 'comment'));
          } else {
            if($reactedType == 2)
              $notiType = 'activity_reacted_love';
            elseif($reactedType == 3)
              $notiType = 'activity_reacted_haha';
            elseif($reactedType == 4)
              $notiType = 'activity_reacted_wow';
            elseif($reactedType == 5)
              $notiType = 'activity_reacted_angry';
            elseif($reactedType == 6)
              $notiType = 'activity_reacted_sad';
            //Remove previous notification
            $reaction_array = array('liked', 'activity_reacted_love', 'activity_reacted_haha', 'activity_reacted_wow', 'activity_reacted_angry', 'activity_reacted_sad');            
            //Send Reaction Notification
            if($notiType)
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($ownerNoti, !empty($guid) ? $guidUser : $viewer, $comment, $notiType, array('label' => 'comment'));
          } 
        }
        //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('like_count'=>$comment->like_count)));
      }
      // Stats
      Engine_Api::_()->getDbTable('statistics', 'core')->increment('core.likes');
      //$db->commit();
    }
    catch( Exception $e )
    {
    // $db->rollBack();
      $this->view->error = 'database_error';
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array())); 
      //throw $e;
    }
    if(!$comment_id ) {
      if($sesAdv) {
        $likeResult = array();
        $likesGroup = Engine_Api::_()->comment()->likesGroup($action);
        $reactionData = array();
        $reactionCounter = 0;
        if(engine_count($likesGroup['data'])){
          foreach($likesGroup['data'] as $type){
            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
            $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);;
            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            $reactionCounter++;
          } 
        }
      }
      $likeResult['reactionUserData'] = $this->view->FluentListUsers($action->likes()->getAllLikes(),'',$action->likes()->getLike($this->view->viewer()),$this->view->viewer());
      $likeResult['reactionData'] = $reactionData;
      if($likeRow = $action->likes()->getLike(!empty($guid) ? $guid : $viewer)){ 
        $type = '1';
        $imageLike = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/b6c60430c0c81b44aac34d34239e44b0.png');
        $text = 'Unlike';
        if($likeRow->getType() == 'activity_like' && $sesAdv) {
            $type = $likeRow->type;
            $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
            $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        } else if($likeRow->getType() == 'core_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        }    
        $likeResult['is_like'] = true;
        $like = true;
      }else{
        $likeResult['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
      }
    }else{
      if($sesAdv) {
        $likesGroup = Engine_Api::_()->comment()->commentLikesGroup($comment,false);
        $reactionData = array();
        $reactionCounter = 0;
        if(engine_count($likesGroup['data'])){
          foreach($likesGroup['data'] as $type){

            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
            $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $comment->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);
            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            $reactionCounter++;
          } 
        }
      }
      $likeResult['reactionUserData'] = $this->view->FluentListUsers($comment->likes()->getAllLikes(),'',$comment->likes()->getLike($this->view->viewer()),$this->view->viewer());;
      if(engine_count($reactionData))
        $likeResult['reactionData'] = $reactionData;
      if($likeRow = $comment->likes()->getLike(!empty($guid) ? $guid : $viewer)){ 
        $type = '';
        $imageLike = '';
        $text = 'Unlike';
        if($likeRow->getType() == 'activity_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        } else if($likeRow->getType() == 'core_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        }
        $likeResult['is_like'] = true;
        $like = true;
      }else{
        $likeResult['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
      }
    }
    if(empty($like)) {
        $likeResult["like"]["name"] = "like";
    }else {
        $likeResult["like"]["name"] = "unlike";
    }
    $likeResult["like"]["type"] = $type;
    $likeResult["like"]["image"] = $imageLike;
    $likeResult["like"]["title"] = $this->view->translate($text);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $likeResult));  
  }
   public function unlikeAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 

    // Collect params
    $action_id = $this->_getParam('activity_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $page_id = $this->_getParam('page_id');
    $sbjecttype = $this->_getParam('sbjecttype',false);
    $subjectid = $this->_getParam('subjectid',false);
     $guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);
      $guidUser = $guid->getOwner();
      if(!$guid)
        $guid = "";
    }else{
        $guid = "";
    }
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    // Start transaction
    if($sesAdv)
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    else
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    $db = $actionTable->getAdapter();
    $db->beginTransaction();

    try {
      if(!$sbjecttype)
      $action = $actionTable->getActionById($action_id);
      else
        $action = Engine_Api::_()->getItem($sbjecttype,$subjectid);
      // Action
      if( !$comment_id ) {
        // Check authorization
        if(!$subjectid &&  !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') ) {
          $this->view->error = ('This user is not allowed to unlike this item');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array())); 
        }
        //Remove reaction notification
        $reaction_array = array('liked', 'activity_reacted_love', 'activity_reacted_haha', 'activity_reacted_wow', 'activity_reacted_angry', 'activity_reacted_sad');
        foreach($reaction_array as $reactionr) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $reactionr, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));
        }
        if(!empty($guid))
          $isLike = $action->likes()->getLike($guid);
        else
          $isLike = $action->likes()->getLike($viewer);
       
        if(empty($guid))
          $action->likes()->removeLike($viewer);
        else
          $action->likes()->removeLike($guid);
      }

      // Comment
      else {
        $comment = $action->comments()->getComment($comment_id);
        // Check authorization
        if( !$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment') ) {
          $this->view->error =  ('This user is not allowed to like this item');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array())); 
        }
        if(!empty($guid))
          $isLike = $comment->likes()->getLike($guid);
        else
          $isLike = $comment->likes()->getLike($viewer);
        
        if(empty($guid))
          $comment->likes()->removeLike($viewer);
        else{
          $comment->likes()->removeLike($guid);
        }
        $db->commit();
        $count = $comment->like_count;
        if($comment->like_count > 0){
          $count = $comment->like_count - 1;
        }
        //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('like_count'=>$count)));
      }
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
       $this->view->error = $e->getMessage();
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array())); 
    }
    if( !$comment_id ) {
      if($sesAdv) {
        $likeResult = array();
        $likesGroup = Engine_Api::_()->comment()->likesGroup($action);
        $reactionData = array();
        $reactionCounter = 0;
        if(engine_count($likesGroup['data'])){
          foreach($likesGroup['data'] as $type){
            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
            $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);;
            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            $reactionCounter++;
          } 
        }
      }
      $likeResult['reactionUserData'] = $this->view->FluentListUsers($action->likes()->getAllLikes(),'',$action->likes()->getLike($this->view->viewer()),$this->view->viewer());
      $likeResult['reactionData'] = $reactionData;
      if($likeRow = $action->likes()->getLike(!empty($guid) ? $guid : $viewer)){ 
          $type = '';
          $imageLike = '';
          $text = 'Unlike';
          if($likeRow->getType() == 'activity_like') {
            $type = $likeRow->type;
            $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
            $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
          } else if($likeRow->getType() == 'core_like') {
            $type = $likeRow->type;
            $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
            $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
          }    
          $likeResult['is_like'] = true;
          $like = true;
      }else{
          $likeResult['is_like'] = false;
          $like = false;
          $type = '';
          $imageLike = '';
          $text = 'Like';
      }
    }else{
      if($sesAdv) {
        $likesGroup = Engine_Api::_()->comment()->commentLikesGroup($comment,false);
        $reactionData = array();
        $reactionCounter = 0;
        if(engine_count($likesGroup['data'])){
          foreach($likesGroup['data'] as $type){
            $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
            $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $comment->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);
            $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            $reactionCounter++;
          } 
        }
      }
      $likeResult['reactionUserData'] = $this->view->FluentListUsers($comment->likes()->getAllLikes(),'',$comment->likes()->getLike($this->view->viewer()),$this->view->viewer());;
        if(engine_count($reactionData))
          $likeResult['reactionData'] = $reactionData;
    
      if($likeRow = $comment->likes()->getLike(!empty($guid) ? $guid : $viewer)){ 
        $type = '';
        $imageLike = '';
        $text = 'Unlike';
        if($likeRow->getType() == 'activity_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        } else if($likeRow->getType() == 'core_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        }
        $likeResult['is_like'] = true;
        $like = true;
      }else{
        $likeResult['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
      }
    }
    if(empty($like)) {
      $likeResult["like"]["name"] = "like";
    }else {
      $likeResult["like"]["name"] = "unlike";
    }
    $likeResult["like"]["type"] = $type;
    $likeResult["like"]["image"] = $imageLike;
    $likeResult["like"]["title"] = $this->view->translate($text);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $likeResult));  
  }
  public function shareAction()
  {
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $type = $this->_getParam('type');
    $id = $this->_getParam('id');    
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
    if(!$attachment ) {
      // tell smoothbox to close
      $this->view->status  = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => array()));
    }
    $sesAdv = false;
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    if($sesAdv)
      $api = Engine_Api::_()->getDbTable('actions', 'activity');
    else 
     $api = Engine_Api::_()->getDbTable('actions', 'activity');
    // Process
    $db = $api->getAdapter();
    $db->beginTransaction();
    try {
      // Get body
      $body = $_POST['body'];
      // Set Params for Attachment
      $params = array(
          'type' => '<a href="'.$attachment->getHref().'">'.$attachment->getMediaType().'</a>',          
      );
      //$action = $api->addActivity($viewer, $viewer, 'post_self', $body);
      if($type == 'activity_event'){
       $typeShare = 'activity_event_share'; 
      }else
        $typeShare = 'share';
      $action = $api->addActivity($viewer, $attachment->getOwner(), $typeShare, $body, $params);      
      if($action) { 
        if($type == 'activity_event'){
          $params = array(
          'type' => '<a href="'.$action->getHref().'">post</a>',          
          ); 
          $action->params = $params;
          $action->save();  
        }
        $api->attachActivity($action, $attachment);
      }
      $db->commit();
      // Notifications
      if($sesAdv)
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      else 
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      // Add notification for owner of activity (if user and not viewer)
      if( $action->subject_type == 'user' && $attachment->getOwner()->getIdentity() != $viewer->getIdentity() )
      {
        $notifyApi->addNotification($attachment->getOwner(), $viewer, $action, 'shared', array(
          'label' => $attachment->getMediaType(),
        ));
      }
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    // If we're here, we're done
    $this->view->status = true;
    $this->view->message =  Zend_Registry::get('Zend_Translate')->_('Activity feed shared Successfully.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
  }
  public function likesAction(){
    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content',false);
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
    $this->view->typeSelected = $typeSelected = $this->_getParam('type','all');
    $this->view->item_id = $item_id = $this->_getParam('item_id',false);
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if($item instanceof Activity_Model_Action)
      $subject = $item->likes(true);
    else
      $subject = $item;
    
    if($subject instanceof Activity_Model_Action){
        $table = Engine_Api::_()->getItemTable('activity_like');
        $select = $table->select();
    } else {
        $table = Engine_Api::_()->getItemTable('core_like');
        $select = $table->select();
        $select->where('resource_type = ?', $subject->getType());
    }
    
    $recTable = Engine_Api::_()->getDbTable('reactions','comment')->info('name');
    $tableName = $table->info('name');
    if(!$is_ajax){
      $select->setIntegrityCheck(false)
        ->from($tableName,array('counts'=>new Zend_Db_Expr('COUNT(like_id)'),"type",'total'=>new Zend_Db_Expr('COUNT(like_id)')));
      
       $select
          ->where('resource_id = ?', $subject->getIdentity())
          ->order('like_id ASC')
          ->group('type');
          $recTable = Engine_Api::_()->getDbTable('reactions','comment')->info('name');
      $data = $table->fetchAll($select);
      $countAllLikes = 0;
      $typesLikeData = array();
      $counter = 0;
      $typesLikeData['likeData'][0]['type'] = "all";
      $typesLikeData['likeData'][0]['count'] = 0;
      $typesLikeData['likeData'][0]['image'] = "";
      $counter++;
      foreach($data as $countlikes){
        $typesLikeData['likeData'][$counter]['type'] = $countlikes['type'];
        $typesLikeData['likeData'][$counter]['count'] = $countlikes['total'];
        $typesLikeData['likeData'][$counter]['image'] = $this->getBaseUrl('',Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($countlikes['type']));
        $countAllLikes = $countAllLikes+ $countlikes['counts'];
        $counter++;
      }
      $typesLikeData['likeData'][0]['count'] = $countAllLikes;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $typesLikeData));
    }
    if(!$typeSelected)
      $this->view->typeSelected = $typeSelected  = 'all';
    $this->view->page = $page = $this->_getParam('page',1);
    $this->view->page = $page = $this->_getParam('page',1);
      $select->setIntegrityCheck(false)
        ->from($tableName,'*');
      
       $select
          ->where('resource_id = ?', $subject->getIdentity());
    if($typeSelected != 'all')
      $select->where($tableName.'.type =?',$typeSelected);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $response = array();
    $counter = 0;
    foreach($paginator as $userS){
      if( $data['poster_type'] == 'user' ){
        $user = Engine_Api::_()->getItem('user',$userS['poster_id']);
        $response['reaction_data'][$counter]['user_image'] = $this->userImage($user->getIdentity(),"thumb.profile");
        $response['reaction_data'][$counter]['membership'] = $this->friendRequest($user);
      }else{
        $user = Engine_Api::_()->getItem($userS['poster_type'],$userS['poster_id']);
        if(!$user || !$user->getIdentity())
        continue;
        $response['reaction_data'][$counter]['user_image'] = $this->getBaseUrl(true,$user->getPhotoUrl("thumb.profile"));
      }
      $response['reaction_data'][$counter]['type'] = $user->getType();
      $response['reaction_data'][$counter]['user_id'] = $user->getIdentity();
      $response['reaction_data'][$counter]['title'] = $user->getTitle();      
      $response['reaction_data'][$counter]['image'] = $this->getBaseUrl('',Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($userS['type']));
      $counter++;      
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($response <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Does not exist member.', 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $response),$extraParams));
  }
  
  public function friendRequest($subject){
    
    $viewer = Engine_Api::_()->user()->getViewer();

    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return "";
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return "";
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbTable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'add',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        return array(
          'label' => $this->view->translate('Cancel Request'),
          'action'=>'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      } else {
        // Unfollow
       return array(
          'label' => $this->view->translate('Unfollow'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
       return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          
        );
      } else {
        // Remove as follower?
        return array(
          'label' => $this->view->translate('Unfollow'),
           'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
          
        );
      }
      if( engine_count($params) == 1 ) {
        return $params[0];
      } else if( engine_count($params) == 0 ) {
        return "";
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {
      
      $table =  Engine_Api::_()->getDbTable('membership','user');
      $select = $table->select()
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('user_id = ?', $subject->getIdentity());
      $select = $select->limit(1);
      $row = $table->fetchRow($select);
      
      if( null === $row ) {
        // Add
        return array(
          'label' => $this->view->translate('Add Friend'),
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          'action' => 'add',
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $this->view->translate('Cancel Friend'),
          'action' => 'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
          
        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          
        );
      } else {
        // Remove friend
        return array(
          'label' => $this->view->translate('Remove Friend'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
    }
  }
  
}
