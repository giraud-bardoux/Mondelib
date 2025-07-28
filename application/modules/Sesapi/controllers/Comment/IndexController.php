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
class Comment_IndexController extends Sesapi_Controller_Action_Standard
{
  public function deleteAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));

    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
    
    
    // Identify if it's an action_id or comment_id being deleted
    $comment_id = (int) $this->_getParam('comment_id', null);
    $action_id  = (int) $this->_getParam('resource_id', null);
    $resources_type = $this->_getParam('resource_type',false);
    
    if(!$resources_type)
      $action = Engine_Api::_()->getDbTable('actions', 'activity')->getActionById($action_id);
    else
      $action = Engine_Api::_()->getItem($resources_type,$action_id);
      
      
    if (!$action){
      // tell smoothbox to close
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
    }

    // Send to view script if not POST
    //if (!$this->getRequest()->isPost())
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));

    // Both the author and the person being written about get to delete the action_id
    if (!$comment_id && (
        $activity_moderate ||
        ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
        ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id)))   // commenter
    {
      // Delete action item and all comments/likes
      $db = Engine_Api::_()->getDbTable('actions', 'activity')->getAdapter();
      $db->beginTransaction();
      try {
        $action->deleteItem();
        $db->commit();

        // tell smoothbox to close
        $this->view->status  = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
        $this->view->smoothboxClose = true;
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
      } catch (Exception $e) {
        $db->rollback();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => ""));
      }

    } elseif ($comment_id){
        $comment = $action->comments()->getComment($comment_id);
        
        // allow delete if profile/entry owner
        $db = Engine_Api::_()->getDbTable('comments', 'activity')->getAdapter();
        $db->beginTransaction();
        if ($resources_type || ($activity_moderate ||
           ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
           ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id)))
        {
          try {
            
            
            $action->comments()->removeComment($comment_id);
            if($comment->parent_id){
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
            
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
            $db->commit();

            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
          } catch (Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => ""));
          }
        } else {
          $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
        }
    } else {
      // neither the item owner, nor the item subject.  Denied!
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));
    }  
  }
  public function stickersAction(){
    $search = $this->_getParam('search','');
    $userEmojis = $this->_getParam('user_emojis',0);
    if($userEmojis == 1){
      $useremoji = Engine_Api::_()->getDbTable('useremotions','comment')->getEmotion(); 
      $arrayEmo = array();
      
      $arrayEmo[0]['title'] = "search";
      $arrayEmo[0]['emotion_id'] = 0;
      $arrayEmo[0]['gallery_id'] = 0;
      $counter = 1;
      foreach($useremoji as $emoji){
        $arrayEmo[$counter]['emotion_id'] = $emoji->emotion_id;
        $arrayEmo[$counter]['title'] = $emoji->title;
        $arrayEmo[$counter]['gallery_id'] = $emoji->gallery_id;
        $icon = Engine_Api::_()->storage()->get($emoji->file_id, "")->getPhotoUrl();
        $arrayEmo[$counter]['icon'] = $this->getBaseUrl('',$icon);
        $counter++;
      }
      $resultFeeling['useremotions'] = $arrayEmo;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling)); 
    }
    $paginator = Engine_Api::_()->getDbTable('emotioncategories','comment')->getCategories(array('fetchAll'=>true));
    
    $results = array();
    $counter = 0;
    foreach($paginator as $result){
       $icon = Engine_Api::_()->storage()->get($result->file_id, "")->getPhotoUrl();
       $results[$counter]['gallery_id'] = $result['category_id'];
       $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
       $results[$counter]['title'] = $result['title'];
       $results[$counter]['color'] = $result['color'];
       $counter++;
    }
    $resultFeeling['emotions'] = $results;
    
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling));  
  }
   public function emojiContentAction(){
    if($this->_getParam('search',false))
      return $this->_forward('search-reaction', null, null, array('format' => 'json'));
    $galleryId = $this->_getParam('gallery_id',0);
    $paginator = Engine_Api::_()->getDbTable('emotionfiles','comment')->getFiles(array('fetchAll'=>true,'gallery_id'=>$galleryId));
    $results = array();
    $counter = 0;
    foreach($paginator as $result){ 
       $icon = Engine_Api::_()->storage()->get($result->photo_id, "")->getPhotoUrl();
       $results[$counter]['files_id'] = $result['files_id'];
       $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
       $counter++;
    }
    $resultFeeling['emotions'] = $results;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling));  
  }
  public function searchReactionAction(){
    $text = $this->_getParam('search','happy');
    $paginator = Engine_Api::_()->getDbTable('emotioncategories','comment')->searchResult($text);
    $results = array();
    $counter = 0;
    foreach($paginator as $result){
      if(!$result->photo_id)
        continue;
       $icon = Engine_Api::_()->storage()->get($result->photo_id, "")->getPhotoUrl();
       $results[$counter]['files_id'] = $result['files_id'];
       $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
       $counter++;
    }
    $resultFeeling['emotions'] = $results;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling));  
  }
  
  public function reactionAddAction(){
    $storepopupTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('comment.stickertitle', 'Sticker Store');
    $galleries = Engine_Api::_()->getDbTable('emotiongalleries','comment')->getGallery(array('fetchAll'=>true));
    $useremotions = Engine_Api::_()->getDbTable('useremotions','comment')->getEmotion();
    $arrayEmo = array();
    $counter = 0;
    
    $useremotionsArray = array();
    foreach($useremotions as $userEmo)
      $useremotionsArray[] = $userEmo->gallery_id;
    
   // $response['useremotions'] = $useremotionsArray;
    
    foreach($galleries as $gallery){
      $arrayEmo[$counter]['title'] = $gallery->title;
      $arrayEmo[$counter]['gallery_id'] = $gallery->gallery_id;
      if(engine_in_array($gallery->gallery_id,$useremotionsArray))
        $is_selected = true;
      else
        $is_selected = false;
      $arrayEmo[$counter]['is_selected'] = $is_selected;
      $category = Engine_Api::_()->getItem('comment_emotioncategory',$gallery->category_id);
      if($category)
        $arrayEmo[$counter]['category'] = $category->getTitle();
      $icon = Engine_Api::_()->storage()->get($gallery->file_id, "")->getPhotoUrl();
      $arrayEmo[$counter]['icon'] = $this->getBaseUrl('',$icon);
      
      $paginator = Engine_Api::_()->getDbTable('emotionfiles','comment')->getFiles(array('fetchAll'=>true,'gallery_id'=>$gallery->gallery_id,'limit'=>6));
      $results = array();
      $counterImages = 0;
      foreach($paginator as $result){
         $icon = Engine_Api::_()->storage()->get($result->photo_id, "")->getPhotoUrl();
         $results[$counterImages]['files_id'] = $result['files_id'];
         $results[$counterImages]['icon'] = $this->getBaseUrl('',$icon);
         $counterImages++;
      }
      $arrayEmo[$counter]['images'] = $results;
      $counter++;
    }
    
    $response['gallery'] = $arrayEmo;
    $response['store_title'] = $storepopupTitle;
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $response));  
  }
  public function actionReactionAction(){
    $action = $this->_getParam('actionD',false);
    $gallery_id = $this->_getParam('gallery_id',false);
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();  
    if($action == '1'){
      $data = array(
          'gallery_id' => $gallery_id,
          'user_id' => $viewer->getIdentity(),
      );
     $db->insert('engine4_comment_useremotions', $data);
    }else{
      $db->delete('engine4_comment_useremotions', array(
          'gallery_id =?'      => $gallery_id,
          'user_id =?' => $viewer->getIdentity(),
      ));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Action perform successfully."))); 
  }
}
