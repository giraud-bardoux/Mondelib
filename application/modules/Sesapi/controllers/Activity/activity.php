<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: activity.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
$actionDetails = $action;
  if(isset($action->group_action_id)){
  $action_id = $action->group_action_id;
  $explodedData = explode(',',$action_id);
  if($explodedData > 1){
      $action_id = max($explodedData);
      $action = Engine_Api::_()->getItem('activity_action',$action_id);
  }
  }else{
    $action_id = $action->action_id;
  }
  // get next id
  if( null === @$nextid || $action_id <= @$nextid ) {
    $nextid = $action->action_id - 1;
  }
  // get first id
  if( null === @$firstid || $action_id > @$firstid ) {
    $firstid = $action_id;
  }
  // skip disabled actions
  if( !$action->getTypeInfo() || !$action->getTypeInfo()->enabled ){
      $return = true;
      return;
  }
  // skip items with missing items
  if( !$action->getSubject() || !$action->getSubject()->getIdentity() ){
      $return = true;
      return;
  }
  if( !$action->getObject() || !$action->getObject()->getIdentity() ) {
      $return = true;
      return;
  }
  // track/remove users who do too much (but only in the main feed)

  // if( empty($subject) ) {
  //   $actionSubject = $action->getSubject();
  //   $actionObject = $action->getObject();
  //   if( !isset($itemActionCounts[$actionSubject->getGuid()]) ) {
  //     $itemActionCounts[$actionSubject->getGuid()] = 1;
  //   } else if( $itemActionCounts[$actionSubject->getGuid()] >= $itemActionLimit ) {
  //       $return = true;
  //     return;
  //   } else {
  //     $itemActionCounts[$actionSubject->getGuid()]++;
  //   }
  // }

  // remove duplicate friend requests
  if( $action->type == 'friends' ) {
    $id = $action->subject_id . '_' . $action->object_id;
    $rev_id = $action->object_id . '_' . $action->subject_id;
    if( engine_in_array($id, $friendRequests) || engine_in_array($rev_id, $friendRequests) ) {
        $return = true;
      return;
    } else {
      $friendRequests[] = $id;
      $friendRequests[] = $rev_id;
    }
  }
  // remove items with disabled module attachments
  try {
    $attachments = $action->getAttachments();

  } catch (Exception $e) {
    // if a module is disabled, getAttachments() will throw an Engine_Api_Exception; catch and continue
      $return = true;
    return;
  }
  // add to list
  if( engine_count($activity) < $length ) {
    $activityArray = $action->toArray();
    $activityParams = $action->params;
    if(empty($activityParams['count'])){
      unset($activityArray['params']);
    }
    $activity[$counter] = $activityArray;

  $sescommunityads = empty($sescommunityads) ? Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sescommunityads') : $sescommunityads;
  if(empty($fromActivityFeed) && empty($_SESSION['fromActivityFeed']) && (empty($filterFeed) || $filterFeed != 'hiddenpost') && empty($previousAction) && $sescommunityads && is_array(Engine_Api::_()->sescommunityads()->allowedTypes($action)) && engine_in_array('boos_post',Engine_Api::_()->sescommunityads()->allowedTypes($action)) && Engine_Api::_()->sescommunityads()->getAllowedActivityType($action->type) && ($action->subject_id == $this->view->viewer()->getIdentity() && $action->subject_type == "user")){
    if(Engine_Api::_()->authorization()->isAllowed('sescommunityads', $this->view->viewer(), 'create')){
      if(!empty($actionDetails->view_count)){
         $activity[$counter]['people_reach_count'] =  $this->view->translate("%s people Reached",$actionDetails->view_count);
      }
      $activity[$counter]['boost_post_label'] = $this->view->translate('Boost Post');
      $activity[$counter]['boost_post_url'] = $this->getBaseUrl(false,$this->view->url(array("controller"=> "index", "action" => "create",'action_id'=>$action->action_id),'sescommunityads_general',true));

    }
  }

  //check feeling if enabled
  if($feeling){
     $feelingposts = Engine_Api::_()->getDbTable('feelingposts','activity')->getActionFeelingposts($action->getIdentity());
     if($feelingposts) {
        $feelings = Engine_Api::_()->getItem('activity_feeling', $feelingposts->feeling_id);
        if($feelingposts->feeling_custom){
          $feelingIcon = Engine_Api::_()->getItem('activity_feelingicon', $feelingposts->feelingicon_id);
          $activity[$counter]['feelings']['title'] = $feelingposts->feeling_customtext;
          $activity[$counter]['feelings']['feeling_title'] = strtolower($feelings->title);
          $icon = Engine_Api::_()->storage()->get($feelings->file_id, '');
          $activity[$counter]['feelings']['icon'] = $this->getBaseUrl('',$icon ? $icon->getPhotoUrl() : "");
          $activity[$counter]['feelings']['is_string'] = $this->view->translate("is ");
        }else if($feelings->type == 1) {
          $feelingIcon = Engine_Api::_()->getItem('activity_feelingicon', $feelingposts->feelingicon_id);
          $activity[$counter]['feelings']['title'] = strtolower($feelingIcon->title);
          $activity[$counter]['feelings']['feeling_title'] = strtolower($feelings->title);
          $icon = Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '');
          $activity[$counter]['feelings']['icon'] = $this->getBaseUrl('',$icon ? $icon->getPhotoUrl() : "");
          $activity[$counter]['feelings']['is_string'] = $this->view->translate("is ");
        } else if($feelings->type == 2 && $feelingposts->resource_type && $feelingposts->feelingicon_id) {
          $resource = Engine_Api::_()->getItem($feelingposts->resource_type, $feelingposts->feelingicon_id);
          $activity[$counter]['feelings']['feeling_title'] = strtolower($feelings->title);
          $activity[$counter]['feelings']['title'] = strtolower($resource->title);
          $icon = Engine_Api::_()->storage()->get($feelings->file_id, '');
          $activity[$counter]['feelings']['icon'] = $this->getBaseUrl('',$icon ? $icon->getPhotoUrl() : "");
          $activity[$counter]['feelings']['id'] = $resource ? $resource->getIdentity() : 0;
          $activity[$counter]['feelings']['href'] = $resource ? $this->getBaseUrl('',$resource->getHref()) : "";
          $activity[$counter]['feelings']['resource_type'] = $resource ? $resource->getType() : '';
          $activity[$counter]['feelings']['is_string'] = $this->view->translate("is ");
        }
     }
}
  $activity[$counter]['feedLink'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$action->getHref();
  //attachment info
  $activity[$counter]['guid'] = $action->getGuid();
  $attachment = array();
    $buysellattachment = '';
    //buysell
      if($action->type == 'post_self_buysell' || ($action->attachment_count == 1 && engine_count($action->getAttachments()) == 1 && current($action->getAttachments()))){
        if($action->type == 'post_self_buysell' || (!empty($buysellattachment->item) && $buysellattachment->item->getType() == 'activity_buysell')){
          if(empty($buysellattachment)){
            $buysell = $action->getBuySellItem();
          }else{
            $changeAction = $action;
            $buysellAction = $buysellattachment->meta->action_id;
            $buysell = Engine_Api::_()->getItem('activity_buysell',$buysellattachment->meta->id);
            $action = Engine_Api::_()->getItem('activity_action',$buysell->action_id);
            $buysellattachment = '';
          }
          if($buysell) {
            $attachment['attachmentType'] = 'activity_buysell';
            $locationBuySell = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type' => 'activity_buysell', 'resource_id' => $buysell->getIdentity()));
            $attachment['title'] = $buysell->title;
            $attachment['buy_url'] = $buysell->buy;
            $attachment['description'] = $buysell->description;
            $attachment['buysell_id'] = $buysell->getIdentity();
            $attachment['price'] = Engine_Api::_()->payment()->getCurrencyPrice($buysell->price,$buysell->currency);
            $attachment['owner_id'] = $action->subject_id;
            $attachment['owner_title'] = $action->getOwner()->getTitle();
            $attachment['can_message_owner'] = false;
            $attachment['can_mark_sold'] = false;
            $attachment['sold'] = false;
            if($this->view->viewer()->getIdentity() != 0){
              if(!$buysell->is_sold){
                 if($action->subject_id != $this->view->viewer()->getIdentity()){
                    $attachment['can_message_owner'] = true;
                 }else{
                    $attachment['can_mark_sold'] = true;
                 }
              }else{
                //sold
                $attachment['sold'] = true;
              }
            }
            if($locationBuySell){
              $attachment['location'] = $locationBuySell->venue;
              $attachment['description'] = $buysell->description;
            }
          }
        }
    }
  if(!empty($attachments) && engine_count($attachments) > 0){
    $attachment['totalImagesCount'] = 0;
    $counterImage = 0;
    foreach($attachments as $attachmentData){
      $getImageSize = engine_count($attachments) && strpos($attachmentData->item->getType(),'photo');
      $attachment['attachment_id'] = $attachmentData->item->getIdentity();
      $attachment['href'] = $this->getBaseUrl(false,$attachmentData->item->getHref());

      //get video url
      if($attachmentData->item->getType() == "video" || $attachmentData->item->getType() == "seseventvideo_video" || $attachmentData->item->getType() == "sespagevideo_video" || $attachmentData->item->getType() == "sesbusinessvideo_video" || $attachmentData->item->getType() == "sesgroupvideo_video"){
          $file_id = $attachmentData->item->file_id;
          $file = Engine_Api::_()->getItem('storage_file',$file_id);
          if($file){
            $videoUrl = $file->map();
            if(!empty($file["width"])){
              $attachment['width'] = $file["width"];
              $attachment['height'] = $file["height"];
            }
            $attachment['video_url'] = $this->getBaseUrl(true,$videoUrl);
          }
      }
      if( $attachmentData->meta->mode == 0 ){//silence
      }else if( $attachmentData->meta->mode == 1){
          if( $attachmentData->item->getPhotoUrl() ){
           $itemS = $attachmentData->item;
           $photo_id = $itemS;
           if($itemS instanceof Sesalbum_Model_Album || $itemS instanceof Sesevent_Model_Album){
              if(!empty($itemS->photo_id)){
                if($itemS instanceof Sesevent_Model_Album)
                  $photo = Engine_Api::_()->getItem('sesevent_photo',$itemS->photo_id);
                else if($itemS instanceof Estore_Model_Album)
                  $photo = Engine_Api::_()->getItem('estore_photo',$itemS->photo_id);
                else if($itemS instanceof Sesproduct_Model_Album)
                  $photo = Engine_Api::_()->getItem('sesproduct_photo',$itemS->photo_id);
                else
                  $photo = Engine_Api::_()->getItem('album_photo',$itemS->photo_id);
                if($photo)
                  $photo_id = $photo->file_id;
                else
                  $photo_id = 0;
              }
           }
           if($itemS instanceof Sesevent_Model_Photo){
            $photo_id = $itemS->file_id;
           }else if($itemS instanceof Sesgroup_Model_Album || $itemS instanceof Sesgroup_Model_Photo){
             if($itemS instanceof Sesgroup_Model_Photo){
              $photo_id = $itemS->file_id;
             }else{
               $photoItem = Engine_Api::_()->getItem('sesgroup_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
             }
           }else if($itemS instanceof Sespage_Model_Album || $itemS instanceof Sespage_Model_Photo){
            if($itemS instanceof Sespage_Model_Photo){
              $photo_id = $itemS->file_id;
             }else{
               $photoItem = Engine_Api::_()->getItem('sespage_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
             }
           }else if($itemS instanceof Sesbusiness_Model_Album || $itemS instanceof Sesbusiness_Model_Photo){
            if($itemS instanceof Sesbusiness_Model_Photo){
              $photo_id = $itemS->file_id;
             }else{
               $photoItem = Engine_Api::_()->getItem('sesbusiness_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
             }
           } else if($itemS instanceof Group_Model_Photo){
               $photoItem = Engine_Api::_()->getItem('group_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
           } else if($itemS instanceof Event_Model_Photo){
            $photoItem = Engine_Api::_()->getItem('event_photo',$itemS->photo_id);
            if($photoItem)
                $photo_id = $photoItem->file_id;
          } else if($itemS instanceof Estore_Model_Photo){
               $photoItem = Engine_Api::_()->getItem('estore_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
           }  else if($itemS instanceof Sesproduct_Model_Photo){
               $photoItem = Engine_Api::_()->getItem('sesproduct_photo',$itemS->photo_id);
               if($photoItem)
                   $photo_id = $photoItem->file_id;
           }

           $imageData = Engine_Api::_()->sesapi()->getPhotoUrls($photo_id,'','',$getImageSize);
           if(engine_count($imageData))
            $attachment["images"][$counterImage] =  $imageData;
           else{
              $attachment["images"][$counterImage]['main']  = $this->getBaseUrl(true,$attachmentData->item->getPhotoUrl());
           }
          }
          
          if($attachmentData->item->getType() == "album_photo" || $attachmentData->item->getType() == "sesevent_photo" || $attachmentData->item->getType() == "sesproduct_photo" || $attachmentData->item->getType() == "sesgroup_photo" || $attachmentData->item->getType() == "sesbusiness_photo" || $attachmentData->item->getType() == "sespage_photo" || $attachmentData->item->getType() == "estore_photo"){
            $attachment['images'][$counterImage]["attachmentData"]['photo_id'] =  $attachmentData->item->photo_id;
            if(!empty($attachmentData->item->album_id))
            $attachment['images'][$counterImage]["attachmentData"]['album_id'] =  $attachmentData->item->album_id;
            $attachment['images'][$counterImage]["attachmentData"]['href'] =  $this->getBaseUrl(false,$attachmentData->item->getHref());
            $attachment['images'][$counterImage]["attachmentData"]['type'] =  $attachmentData->item->getType();
            $attachment['images'][$counterImage]["attachmentData"]['feedupload'] =  isset($attachmentData->item->feedupload) ? $attachmentData->item->feedupload : 0;
          }
          if($attachmentData->item instanceof Core_Model_Link and !empty($attachmentData->item->ses_aaf_gif) && ( ($attachmentData->item->ses_aaf_gif == 1 /* && ($gifInfo = getimagesize($attachment->item->title)) && !empty($gifInfo[2]) */ || $attachmentData->item->ses_aaf_gif == 2)) ){
            $attachment["attachmentType"] = "activity_gif";
             if($attachmentData->item->ses_aaf_gif == 1){
                $attachment["gif_image"] = $attachmentData->item->description;
             }else{
                $explodeCode = explode('|| IFRAMEDATA',$attachmentData->item->description);
                $attachment['iframe'] = $explodeCode[1];
                $attachment['href'] = $this->getBaseUrl(false,$attachmentData->item->getHref());
                $attachment['title'] = $attachmentData->item->title;
                $attachment['description'] = $explodeCode[0];
             }
          }else if($action->type == 'activity_event_share'){
            $attachment['attachmentType'] = "activity_event_share";
            $attachment['title'] = $attachmentData->item;
            $attachment['description'] = $attachmentData->description;
            $attachment['title'] = $attachmentData->item;
            $attachment['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($attachmentData->item,'','',$getImageSize);
          }else if($attachmentData->item->getType() == 'activity_file'){
            $attachment['attachmentType'] = "activity_file";
             $storage = Engine_Api::_()->getItem('storage_file',$attachmentData->item->item_id);
            $attachment['title'] = $storage->name;
            $attachment['preview_url'] = $this->getBaseUrl(false,$storage->map());
            $filetype = current(explode('_',Engine_Api::_()->sesapi()->file_types($storage->mime_major.'/'.$storage->mime_minor)));
            if($filetype){
              $attachment['file_type'] = ucfirst($filetype);
              $attachment['file_type_image'] = $this->getBaseUrl(true,'application/modules/Activity/externals/images/file-icons/'.$filetype.'.png');
            }else{
              $attachment['file_type_image'] = $this->getBaseUrl(true,'application/modules/Activity/externals/images/file-icons/default.png');
            }
          }else{
            if(strpos($attachmentData->item->getType(),'sesmusic_')){
              $attachment['image'] = $attachmentData->item->getPhotoUrl();
            }
            $attachment['attachmentType'] =  $attachmentData->item->getType();
            if($attachmentData->item->getTitle() != "")
              $attachment['title'] = $attachmentData->item->getTitle();
            $attachment['description'] = ($attachmentData->item->getDescription());
            if($attachment['attachmentType'] == "sesquote_quote"){
              $mediaType = $attachmentData->item->mediatype == 2 ? "video" : "photo";
              $attachment['mediaType'] = $mediaType;
              if($attachmentData->item->source){
                $attachment['source'] =   $attachmentData->item->source;
              }
              $attachment['description'] = $attachmentData->item->title;
            }
          }
      } elseif( $attachmentData->meta->mode == 2 ){
          if(empty($attachment['totalImagesCount'])){
            $total = $activityParams;
            $attachment['totalImagesCount'] = !empty($total['count']) ? $total['count'] : engine_count($attachments);;
          }
          if($attachmentData->item->getType() == "sespage_photo" || $attachmentData->item->getType() == "sesgroup_photo" || $attachmentData->item->getType() == "sesbusiness_photo" || $attachmentData->item->getType() == "event_photo" || $attachmentData->item->getType() == "sesevent_photo"   || $attachmentData->item->getType() == "group_photo"){
            $attachment["images"][$counterImage] =  Engine_Api::_()->sesapi()->getPhotoUrls($attachmentData->item->file_id,'','',$getImageSize);
          }else{
            $attachment["images"][$counterImage] =  Engine_Api::_()->sesapi()->getPhotoUrls($attachmentData->item,'','',$getImageSize);
          }
          if(!empty($attachmentData->item->chanelphoto_id)){
            $photo_id = $attachmentData->item->chanelphoto_id;
            $album_id = $attachmentData->item->chanel_id;
          }else{
            $photo_id = $attachmentData->item->photo_id;
            try{
              $album_id = $attachmentData->item->album_id;
            }catch(Exception $e){
              $album_id = 0;
            }
          }

          if($attachmentData->item->getType() == "video" && isset($attachmentData->item->feedupload)){
            $file_id = $attachmentData->item->file_id;
            $file = Engine_Api::_()->getItem('storage_file',$file_id);
            if($file){
              $videoUrl = $file->map();
              if(!empty($file["width"])){
                $attachment['width'] = $file["width"];
                $attachment['height'] = $file["height"];
              }
              $attachment['images'][$counterImage]["attachmentData"]['video_url'] = $this->getBaseUrl(true,$videoUrl);
            }
          }

          $attachment['images'][$counterImage]["attachmentData"]['photo_id'] = $attachmentData->item->getType() == "video" && isset($attachmentData->item->feedupload) ? $attachmentData->item->video_id :  $photo_id;
          $attachment['images'][$counterImage]["attachmentData"]['album_id'] =  $album_id;
          $attachment['images'][$counterImage]["attachmentData"]['href'] =  $this->getBaseUrl(false,$attachmentData->item->getHref());
          $attachment['images'][$counterImage]["attachmentData"]['type'] =  $attachmentData->item->getType();
          $attachment["attachmentType"] = !empty($attachmentData->item->feedupload) && $attachment['totalImagesCount'] > 1 ? "photo" : $attachmentData->item->getType();
          $attachment['images'][$counterImage]["attachmentData"]['feedupload'] =  isset($attachmentData->item->feedupload) ? $attachmentData->item->feedupload : 0;
      }elseif( $attachmentData->meta->mode == 3 ){
        $attachment['description'] = $attachmentData->item->getDescription();
      }elseif( $attachmentData->meta->mode == 4 ){
        //silence
      }
      $counterImage++;
    }
    if($attachment['totalImagesCount'] == 0)
      unset($attachment['totalImagesCount']);
 }else if(!empty($actionDetails->reaction_id)){
    $attachment['attachmentType'] = "comment_emotionfile";
    $reaction = Engine_Api::_()->getItem('comment_emotionfile',$actionDetails->reaction_id);
    if($reaction){
      $reactionPhoto = Engine_Api::_()->storage()->get($reaction->photo_id, '');
      $attachment['reaction_image'] = $reactionPhoto ? $this->getBaseUrl(false,$reactionPhoto->getPhotoUrl()) : "";
    }
 } else if(!empty($actionDetails->gif_url)){
    $attachment['attachmentType'] = "activity_gif";
    $attachment['gif_url'] = $actionDetails->gif_url;
 }
 
 if(engine_count($attachment)){
  if(isset($attachment['title']) && $attachment['title'] && empty($attachment['images'])  && $attachment['attachmentType'] != "activity_file"){
    $extentions = substr($attachment['title'], -3);
    if($extentions == "gif" || $extentions == "png" || $extentions == "jpg"){
        $attachment['images'][0]['main'] = $attachment['title'];
    }
  }
   //attachment code ends here
   $activity[$counter]['attachment'] = $attachment;
   if($action->type == "profile_photo_update")
      $activity[$counter]['attachment']['attachmentType'] = "album_photo";
 }
  if($action->type == "profile_photo_update" && $contentprofilecoverphotoenable){
      $cover = false;
      if(!empty($action->getSubject()->cover) ){
          $memberCover =	Engine_Api::_()->storage()->get($action->getSubject()->cover, '');
      }else if(!empty($action->getSubject()->coverphoto) ){
          $memberCover =	Engine_Api::_()->storage()->get($action->getSubject()->coverphoto, '');
      }
      if($cover) {
          if($memberCover) {
              $memberCover = $this->getBaseUrl(true,$memberCover->map());
              $activity[$counter]['cover_photo_url'] = $memberCover;
          }
      }
  }

$getAllHashtags = $sesAdv ? Engine_Api::_()->getDbTable('hashtags', 'activity')->getAllHashtags($action->getIdentity()) : array();
$hashTagActivity = array();
$counterHash = 0;
$hashTagString = "";
foreach($getAllHashtags as $tags){
  if($tags->title == "")
    continue;
   $hashTagActivity[$counterHash]['text'] = "#".$tags->title;
   $hashTagActivity[$counterHash]['hashtag_id'] = $tags->hashtag_id;
   $hashTagString = $hashTagString . "#".$tags->title.', ';
   $counterHash++;
}
$hashTagString = trim($hashTagString,', ');
if(engine_count($hashTagActivity)){
  $activity[$counter]['hashTagString'] = $hashTagString;
  $activity[$counter]['activityTags'] = $hashTagActivity;
}
 //user tagged in activity
 $taggedMember = $sesAdv ? Engine_Api::_()->getDbTable('tagusers','activity')->getActionMembers($action->getIdentity()) : array();
 $tagged = array();
 if (engine_count($taggedMember)){
   $counterTagged = 0;
   foreach($taggedMember as $member){
     $member = Engine_Api::_()->getItem('user',$member['user_id']);
     $tagged[$counterTagged]['name'] = $member->getTitle();
     $tagged[$counterTagged]["user_id"] = $member->getIdentity();
     $tagged[$counterTagged]["image_url"] = $this->userImage($member->getIdentity(),"thumb.profile");
     $counterTagged++;
   }
   $activity[$counter]["tagged"] = $tagged;
 }
 
//location
$location = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type' => 'activity_action', 'resource_id' => $action->getIdentity()));
if(isset($location) && !empty($location)){
    $activity[$counter]['locationActivity'] = $location->toArray();
}
 
 $hashTag = Engine_Api::_()->sesapi()->gethashtags($action->body);
 if(engine_count($hashTag)>0)
   //get hashtags from body
   $activity[$counter]['hashTags'] = Engine_Api::_()->sesapi()->gethashtags($action->body);
 $mention = Engine_Api::_()->sesapi()->getMentionTags($action->body);
 if(engine_count($mention)>0)
   //get mention from body
   $activity[$counter]['mention'] = Engine_Api::_()->sesapi()->getMentionTags($action->body);
 //comment like code
 $isComment = ($action->getTypeInfo()->commentable &&
                $viewer->getIdentity() &&
                Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'));
 $activity[$counter]['comment_disable'] =  Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') ? false : true;
 $activity[$counter]['can_comment'] = !empty($isComment) ? $isComment : false;
 
 
 //Translate work
 if(strlen(preg_replace("/(\\\u[0-9a-f]{4})+?|\s+/","",strip_tags($action->body))) && Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.translate', 1)) {
  $languageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.language', 'en');
  $activity[$counter]['can_translate'] = 'https://translate.google.com/#auto/'.$languageTranslate.'/'.urlencode(strip_tags($action->body));
 }
 
 //todo
 $activity[$counter]["comment_count"] = $sesAdv ? Engine_Api::_()->comment()->commentCount($action,'') : 0;

 // get single comment
 if($viewer->getIdentity() && $sesAdv){
	$subjectComment = $action->comments(true);
	$friendIds = $viewer->membership()->getMembershipsOfIds();
     if(!$friendIds){
         $friendIds = array('0');
     }
	//$friendIds = implode(",", $friends);
	$commentSelect = $subjectComment->comments()->getCommentSelect();

	if(strpos($commentSelect,'`engine4_activity_comments`') === FALSE){
		$commentsTable = Engine_Api::_()->getDbTable('comments', 'core');
		$commentsTableName = Engine_Api::_()->getDbTable('comments', 'core')->info('name');
		$commentSelect->setIntegrityCheck(false)
					->where($commentsTableName.'.body  != ?', '');
		$commentSelect->where($commentsTableName.'.parent_id =?',0);
		$commentSelect->where($commentsTableName.'.poster_id IN (?)',$friendIds);

	}else{
		$commentsTable = Engine_Api::_()->getDbTable('comments', 'activity');
		$commentsTableName = Engine_Api::_()->getDbTable('comments', 'activity')->info('name');
		
		$commentSelect->setIntegrityCheck(false)
									->where($commentsTableName.'.body  != ?', '');
		$commentSelect->where($commentsTableName.'.parent_id =?',0);
		$commentSelect->where($commentsTableName.'.poster_id IN (?)',$friendIds);

	}
	$commentSelect->limit(1);
	$commentSelect->reset('order');
	$commentSelect->order('comment_id DESC');
	$commentData = $comment = $commentsTable->fetchRow($commentSelect);
	if($commentData){
    $activity[$counter]["comment"] = $commentData->toArray();
		$likeResult = array();
		$likesGroup = Engine_Api::_()->comment()->likesGroup($subjectComment,$comment);
		//$photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($comment);
		$reactionData = array();
		$reactionCounter = 0;
		if(engine_count($likesGroup['data'])){
			foreach($likesGroup['data'] as $type){
				$reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
				$reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $comment->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);;
				$reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
				$reactionCounter++;
			}
		}
    
		$activity[$counter]["comment"]['reactionUserData'] = $this->view->FluentListUsers($comment->likes()->getAllLikes(),'',$comment->likes()->getLike($this->view->viewer()),$this->view->viewer());;
		$activity[$counter]["comment"]['reactionData'] = $reactionData;

    $itemsLike = array();
    $counterLike = 0;
    $isLike = $comment->likes()->getLike($this->view->viewer());
    foreach($comment->likes()->getAllLikes() as $itemS){
      $item = $itemS->getType() != "user" ?  Engine_Api::_()->getItem($itemS->poster_type,$itemS->poster_id) : $itemS;
      if($isLike && $item->getType() == "user" && $item->getIdentity() == $this->view->viewer()->getIdentity()){
        continue;
      }
      $itemsLike[$counterLike]["title"] = $item->getTitle();
      $itemsLike[$counterLike]["id"] = $item->getIdentity();
      $itemsLike[$counterLike]["type"] = $item->getType();
      $counterLike++;
    }
    if($isLike){
      $uArray = array();
      $uArray["title"] = $this->view->viewer()->getTitle();
      $uArray["id"] = $this->view->viewer()->getIdentity();
      $uArray["type"] = $this->view->viewer()->getType();
      //array_unshift($itemS,$uArray);
    }
    $activity[$counter]['comment']['likeUserData'] = $itemsLike;

		 if($likeRow = $comment->likes()->getLike(!empty($guid) ? $guid : Engine_Api::_()->user()->getViewer())){
        
        $likeResult['is_like'] = true;
        $like = true;
        $type = $likeRow->type;
        $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable('reactions','comment')->likeImage($type));
        $text = Engine_Api::_()->getDbTable('reactions','comment')->likeWord($type);
				 }else{
						$likeResult['is_like'] = false;
						$like = false;
						$type = '';
						$imageLike = '';
						$text = 'Like';
				 }
					if(empty($like)) {
							$array[$counter]["like"]["name"] = "like";
					}else {
							$array[$counter]["like"]["name"] = "unlike";
					}
					$activity[$counter]["comment"]["like"]["type"] = $type;
					$activity[$counter]["comment"]["like"]["image"] = $imageLike;
					$activity[$counter]["comment"]["like"]["title"] = $this->view->translate($text);

      //get hashtags from body
      $activity[$counter]["comment"]['hashTags'] = Engine_Api::_()->sesapi()->gethashtags($comment->body);
      //get mention from body
      $activity[$counter]["comment"]['mention'] = Engine_Api::_()->sesapi()->getMentionTags($comment->body);
      
      if($comment->file_id){
          $getFilesForComment = Engine_Api::_()->getDbTable('commentfiles','comment')->getFiles(array('comment_id'=>$comment->comment_id));
          $attachmentCounter = 0;
        foreach($getFilesForComment as $fileid){
          if($fileid->type == 'album_photo'){
            try{
              $photo = Engine_Api::_()->getItem('album_photo',$fileid->file_id);
              if($photo){
                $attachPhoto  = Engine_Api::_()->sesapi()->getPhotoUrls($photo,'','');
                if(engine_count($attachPhoto)){
                  $activity[$counter]["comment"]['attachphotovideo'][$attachmentCounter]["images"] = $attachPhoto;
                  $activity[$counter]["comment"]["attachphotovideo"][$attachmentCounter]["id"] = $photo->getIdentity();
                  $activity[$counter]["comment"]['attachphotovideo'][$attachmentCounter]["type"] = "album_photo";
                }else{
                  continue;
                }
              } else {
                continue;
              }
            }catch(Exception $e){
              continue;
          }
          }else{
            try{
             $video = Engine_Api::_()->getItem('video',$fileid->file_id);
             if($video){
               $videoAttach =  Engine_Api::_()->sesapi()->getPhotoUrls($video,'','');
               if(engine_count($videoAttach)){
                $activity[$counter]["comment"]['attachphotovideo'][$attachmentCounter]["images"] = $videoAttach;
                $activity[$counter]["comment"]["attachphotovideo"][$attachmentCounter]["id"] = $video->getIdentity();
								$activity[$counter]["comment"]['attachphotovideo'][$attachmentCounter]["type"] = $video->getType();
               }else
                continue;
              } else {
                continue;
              }
            }catch(Exception $e){

            }
          }
          $attachmentCounter++;
        }
      }else if($comment->emoji_id){
        $emoji =  Engine_Api::_()->getItem('comment_emotionfile',$comment->emoji_id);
        if($emoji){
					$photo = Engine_Api::_()->sesapi()->getPhotoUrls($emoji->photo_id,'','');
					$activity[$counter]["comment"]['emoji_image'] = $photo["main"];
        }
      }
      if($comment->preview && !$comment->showpreview){
        $link = Engine_Api::_()->getItem('core_link',$comment->preview);
        $activity[$counter]["comment"]['link']['images']  = Engine_Api::_()->sesapi()->getPhotoUrls($link,'','');
        $activity[$counter]["comment"]['link']['href'] = $this->getBaseUrl(false,$link->getHref());
        $activity[$counter]["comment"]['link']['title'] = $link->title;
        $parseUrl = parse_url($link->uri);
        $desc =  str_replace(array('www.','demo.'),array('',''),$parseUrl['host']);
        $activity[$counter]["comment"]['link']['description'] = $desc;
      }
      //user
      if($comment->poster_type == "user"){
        $user = Engine_Api::_()->getItem('user',$comment->poster_id);
        $activity[$counter]["comment"]['user_image'] = $this->userImage($user->getIdentity(),"thumb.profile");
        $user_id = $user->getIdentity();
      }else{
        $user = Engine_Api::_()->getItem($comment->poster_type,$comment->poster_id);
        $activity[$counter]["comment"]['user_image'] = $this->getBaseUrl(true,$user->getPhotoUrl('thumb.profile'));
        $user_id = $user->getParent()->getIdentity();
      }
      $activity[$counter]["comment"]['user_title'] = $user->getTitle();
      
      if($commentData && isset($commentData->gif_url)) {
        $activity[$counter]["comment"]['gif_url'] = $commentData->gif_url;
        $activity[$counter]["comment"]['gif_id'] = 1;
      } else {
        $activity[$counter]["comment"]['gif_id'] = 0;
      }
      
      $type = $comment->getType();
      if ( $this->view->viewer()->getIdentity() &&
                 (($this->view->viewer()->getIdentity() == $user_id) ||
                  ($type == "activity_comment" && Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $this->view->viewer()->level_id, 'activity'))  ) ){
			$activity[$counter]["comment"]["can_delete"] = true;
			$optionCounter = 0;
			$activity[$counter]['options'][$optionCounter]['name']= 'edit';
			$activity[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Edit');
			$optionCounter++;
			$activity[$counter]['options'][$optionCounter]['name']= 'delete';
			$activity[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Delete');
     }else{
			$activity[$counter]["comment"]["can_delete"] = false;
     }
	}
 }
  if ($isComment) {
    if($sesAdv) {
      $likesGroup = Engine_Api::_()->comment()->likesGroup($action);
      $reactionData = array();
      $reactionCounter = 0;
      if(engine_count($likesGroup['data'])){
        $activity[$counter]['likeUserStats']['resource_type'] = $likesGroup['resource_type'];
        $activity[$counter]['likeUserStats']['item_id'] = $likesGroup['resource_id'];
        foreach($likesGroup['data'] as $type){
          $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],$this->view->translate(Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type'])));
          $reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);;
          $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
          $reactionCounter++;
        }
      }
    }
    $activity[$counter]['reactionUserData'] = $this->view->FluentListUsers($action->likes()->getAllLikes(),'',$action->likes()->getLike($this->view->viewer()),$this->view->viewer());
    
    $likesUser = $action->likes()->getAllLikes();
    $isLike = $action->likes()->getLike($this->view->viewer());

    $itemsLike = array();
    $counterLike = 0;
    foreach($likesUser as $itemS){
      $item = $itemS->getType() != "user" ?  Engine_Api::_()->getItem($itemS->poster_type,$itemS->poster_id) : $itemS;
      if($isLike && $item->getType() == "user" && $item->getIdentity() == $this->view->viewer()->getIdentity()){
        continue;
      }
      $itemsLike[$counterLike]["title"] = $item->getTitle();
      $itemsLike[$counterLike]["id"] = $item->getIdentity();
      $itemsLike[$counterLike]["type"] = $item->getType();
      $counterLike++;
    }
    if($isLike){
      $uArray = array();
      $uArray["title"] = $this->view->viewer()->getTitle();
      $uArray["id"] = $this->view->viewer()->getIdentity();
      $uArray["type"] = $this->view->viewer()->getType();
      array_unshift($itemsLike,$uArray);
    }
    $activity[$counter]['likeUserData'] = $itemsLike;
    $activity[$counter]['likeUserCount'] = count($likesUser);
    
    
    if (is_array(@($reactionData)) && engine_count($reactionData) > 0) {
      if(engine_count($reactionData))
        $activity[$counter]['reactionData'] = $reactionData;
    }
    if($likeRow =  $action->likes()->getLike($this->view->viewer()) ){
      $type = false;
      $text = "Unlike";
      $imageLike = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/b6c60430c0c81b44aac34d34239e44b0.png');
      if($sesAdv) {
       
        if(!$type)
        $type = $likeRow ? $likeRow->type : 1;
        $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
        $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
      }
      $activity[$counter]['is_like'] = true;
      $like = true;
      if(!$type)
        $type = 1;
     }else{
       $activity[$counter]['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
     }
    if(empty($like)) {
        $activity[$counter]["like"]["name"] = "like";
    }else {
        $activity[$counter]["like"]["name"] = "unlike";
    }
    $activity[$counter]["like"]["type"] = $type;
    $activity[$counter]["like"]["image"] = $imageLike;
    $activity[$counter]["like"]["title"] = $this->view->translate($text);
  }else{
     if($likeRow =  $action->likes()->getLike($this->view->viewer()) ){
         $activity[$counter]["like"]["name"] = "unlike";
         $activity[$counter]["like"]["title"] = $this->view->translate("Unlike");
     }else{
         $activity[$counter]["like"]["name"] = "like";
         $activity[$counter]["like"]["title"] = $this->view->translate("Like");
     }
  }
//get sharable
$isShareable = ($action->getTypeInfo()->shareable &&  $viewer->getIdentity()) ? 1 : 0;
$activity[$counter]['can_share'] = 0;
if ($action->getTypeInfo()->shareable == 1 && ($attachment = $action->getFirstAttachment())) {
    $activity[$counter]['can_share'] = $isShareable;
    $activity[$counter]["share"]["name"] = "share";
    $activity[$counter]["share"]["action_id"] = $action->getIdentity();
    $activity[$counter]["share"]["label"] = $this->view->translate("Share");
    $attachmentItem = $attachment->item;
    if($attachmentItem->getPhotoUrl())
    $activity[$counter]["share"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());
		$activity[$counter]["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
    $activity[$counter]["share"]["title"] = is_string($attachmentItem->getTitle()) ? $attachmentItem->getTitle() : '';
    $activity[$counter]["share"]["description"] = strip_tags($attachmentItem->getDescription());
    $activity[$counter]["share"]['urlParams'] = array(
        "type" => $attachment->item->getType(),
        "id" => $attachment->item->getIdentity()
    );
    if(is_null($activity[$counter]["share"]["title"]))
      unset($activity[$counter]["share"]["title"]);
} else if ($action->getTypeInfo()->shareable == 2) {
     $activity[$counter]['can_share'] = $isShareable;
     $activity[$counter]["share"]["action_id"] = $action->getIdentity();
    $activity[$counter]["share"]["name"] = "share";
    $activity[$counter]["share"]["label"] = $this->view->translate("Share");
    $attachmentItem = $action->getSubject();
    if($attachmentItem->getPhotoUrl())
    $activity[$counter]["share"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());
		$activity[$counter]["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
    $activity[$counter]["share"]["title"] = is_string($attachmentItem->getTitle()) ? $attachmentItem->getTitle() : '';
    $activity[$counter]["share"]["description"] = strip_tags($attachmentItem->getDescription());

    $activity[$counter]["share"]['urlParams'] = array(
        "type" => $action->getSubject()->getType(),
        "id" => $action->getSubject()->getIdentity()
    );
    if(is_null($activity[$counter]["share"]["title"]))
      unset($activity[$counter]["share"]["title"]);
} elseif ($action->getTypeInfo()->shareable == 3) {
  $activity[$counter]["share"]["action_id"] = $action->getIdentity();
     $activity[$counter]['can_share'] = $isShareable;
    $activity[$counter]["share"]["name"] = "share";
    $activity[$counter]["share"]["label"] = $this->view->translate("Share");
    $attachmentItem = $action->getObject();
    if($attachmentItem->getPhotoUrl())
    $activity[$counter]["share"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());
		$activity[$counter]["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
    $activity[$counter]["share"]["title"] = is_string($attachmentItem->getTitle()) ? $attachmentItem->getTitle() : '';
    $activity[$counter]["share"]["description"] = strip_tags($attachmentItem->getDescription());
    $activity[$counter]["share"]['urlParams'] = array(
        "type" => $action->getObject()->getType(),
        "id" => $action->getObject()->getIdentity()
    );
    if(is_null($activity[$counter]["share"]["title"]))
      unset($activity[$counter]["share"]["title"]);
} else if ($action->getTypeInfo()->shareable == 4) {
  $activity[$counter]["share"]["action_id"] = $action->getIdentity();
     $activity[$counter]['can_share'] = $isShareable;
    $activity[$counter]["share"]["name"] = "share";
    $activity[$counter]["share"]["label"] = $this->view->translate("Share");
    $attachmentItem = $action;
    if($attachmentItem->getPhotoUrl())
    $activity[$counter]["share"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());
		$activity[$counter]["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
    $activity[$counter]["share"]["title"] = is_string($attachmentItem->getTitle()) ? $attachmentItem->getTitle() : '';
    $activity[$counter]["share"]["description"] = strip_tags($attachmentItem->getDescription());
    $activity[$counter]["share"]['urlParams'] = array(
        "type" => $action->getType(),
        "id" => $action->getIdentity()
    );
    if(is_null($activity[$counter]["share"]["title"]))
      unset($activity[$counter]["share"]["title"]);
}

 //get background image
 if (@$backGroundEnable && $enableFeedBg && $actionDetails->feedbg_id && empty($location) && strlen(strip_tags($body)) <= $activitytextlimit){
      $background = Engine_Api::_()->getItem('activity_background', $actionDetails->feedbg_id);
      $photo = Engine_Api::_()->storage()->get($background->file_id, '');
      if($photo) {
        $activity[$counter]['bg_image'] = $this->getBaseUrl('',$photo->getPhotoUrl());
      }
 }
  // text size
  if(@$activitybigtext &&  strlen(strip_tags(@($body))) <= $activitytextlimit && $action->type == 'status') {
      $activity[$counter]['font-size'] = (int) $activityfonttextsize;
  }
  //get item photo
   $subjectModule = $action->getSubject();
  if($actionDetails && !empty($actionDetails->resource_id) && !empty($actionDetails->resource_type)){
     $itemSubject = Engine_Api::_()->getItem($actionDetails->resource_type,$actionDetails->resource_id);
     if($itemSubject)
      $subjectModule = $itemSubject;
  }
  $activity[$counter]['item_user']["user_id"] = $subjectModule->getIdentity();
  $activity[$counter]['item_user']["title"] = $subjectModule->getTitle();
  $activity[$counter]['item_user']["user_image"] = $subjectModule->getType() == "user" ? $this->userImage($subjectModule->getIdentity(),"thumb.profile") : $this->getBaseUrl(true,$subjectModule->getPhotoUrl('thumb.profile'));
  $activity[$counter]['item_user']["user_type"] = $subjectModule->getType();
  $counterOptions = 0;

  if(!empty($fromActivityFeed)){
    if($ad->sponsored)
      $activity[$counter]['sponsored'] = $this->view->translate('Sponsored');
    if($ad->featured && !$ad->sponsored)
       $activity[$counter]['sponsored'] = $this->view->translate('Featured') ;
  }

  if(!empty($fromActivityFeed) && $ad->user_id != $this->view->viewer()->getIdentity()){
    $menuOptionsCounter = 0;
    $activity[$counter]['options'][$counterOptions]['value'] = $activity[$counter]['options'][$counterOptions]['label']  = $this->view->translate('hide ad');
    $activity[$counter]['options'][$counterOptions]['name'] = $this->view->translate('hide_ad');
    $counterOptions = 1;
    $useful = $ad->isUseful();
    $activity[$counter]['options'][$counterOptions]['is_useful'] = $useful ? 1 : 0;
    $activity[$counter]['options'][$counterOptions]['value'] = $activity[$counter]['options'][$counterOptions]['label'] = !$useful ? $this->view->translate('This ad is useful') : $this->view->translate('Remove from useful');
    $activity[$counter]['options'][$counterOptions]['name'] = $this->view->translate('ad_useful');
  }

  if($viewer->getIdentity() && empty($fromActivityFeed)){
    //edit feed
    if(!$isOnThisDayPage && $viewer->getIdentity() && (
        $activity_moderate || (
          $allowDelete && (
            ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) ||
            ('user' == $action->object_type && $viewer->getIdentity()  == $action->object_id)
          )
        )
        ) || (!empty($subject) && method_exists($subject,'canEditActivity') && $subject->canEditActivity($subject))){
        $activity[$counter]['options'][$counterOptions]['name'] = "edit";
        $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Edit Feed");
        $counterOptions++;
    }
    //delete feed
    if( $viewer->getIdentity()  && (
        $activity_moderate || (
          $allowDelete && (
            ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) ||
            ('user' == $action->object_type && $viewer->getIdentity()  == $action->object_id)
          )
        )
    ) ){
        $activity[$counter]['options'][$counterOptions]['name'] = "delete";
        $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Delete Feed");
        $counterOptions++;
    }
    if($sesAdv) {
        $isSave = Engine_Api::_()->getDbTable('savefeeds', 'activity')->isSaved(array('action_id' => $action->getIdentity(), 'user_id' => $viewer->getIdentity()));
        if ($isSave) {
            $activity[$counter]['options'][$counterOptions]['name'] = "unsave";
            $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Unsave Feed");
            $counterOptions++;
        } else {
            $activity[$counter]['options'][$counterOptions]['name'] = "save";
            $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Save Feed");
            $counterOptions++;
        }
        if ($viewer->getIdentity() == $action->getSubject()->getIdentity()) {
            if ($actionDetails->commentable) {
                $activity[$counter]['options'][$counterOptions]['name'] = "disable_comment";
                $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Disable Comment");
                $counterOptions++;
            } else {
                $activity[$counter]['options'][$counterOptions]['name'] = "enable_comment";
                $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Enable Comment");
                $counterOptions++;
            }
        }
    }
    if($viewer->getIdentity() != $action->getSubject()->getIdentity()){
        if($sesAdv){
          $activity[$counter]['options'][$counterOptions]['name'] = "hide_feed";
          $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Hide Feed");
          $counterOptions++;
        }
        $activity[$counter]['options'][$counterOptions]['name'] = "report";
        $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Report Feed");
        $counterOptions++;
    }
  }
  if(empty($fromActivityFeed) && $sesAdv){
    $activity[$counter]['options'][$counterOptions]['name'] = "feed_link";
    $activity[$counter]['options'][$counterOptions]['value'] = $this->view->translate("Feed Link");
    $activity[$counter]['options'][$counterOptions]['url'] = $this->getBaseUrl(true,$action->getHref());
    $counterOptions++;
  }
  $object = $action->getObject();
  $paramsArray = $action->params;
  $owner = "";
  if(is_array($paramsArray) && engine_count($paramsArray)){
      if(!empty($paramsArray['owner'])){
          $owner = Engine_Api::_()->getItemByGuid($paramsArray['owner']);
      }
  }
  $activityParams = $action->params;
  if(!empty($activityParams['body'])){
    unset($activityParams['body']);
  }
  $params = array_merge(
    $action->toArray(),
    (array) $activityParams,
    array(
      'body'=>$action->body,
      'action'=>$action,
      'resource_type'=>$sesAdv ? $actionDetails->resource_type : "",
      'resource_id'=>$sesAdv ? $actionDetails->resource_id : "",
      'subject' => $action->getSubject(),
      'object' => $object,
      'owner' =>  $owner && ($action->type == "album_like" || $action->type == "album_photo_like") ? $object->getOwner() : (!$owner ? $object->getOwner() : $owner),
    )
  );
  $body = Engine_Api::_()->getApi('activity','sesapi')->translatedBody($action->getTypeInfo()->body);
  $activity[$counter]['activityTypeContent'] = str_replace(array('{body:$body}','{item:$subject}','{item:$subject} is now friends with {item:$object}.'),'',$body);
  $activity[$counter]["body"] = str_replace(array($action->getTypeInfo()->body,'{item:$object} is now friends with {item:$subject}.','{item:$subject} added a new profile photo.'),'',$activity[$counter]["body"]);

  if(!empty($action->group_action_id))
    $groupId = $action->group_action_id;
  else
    $groupId = '';
  
  //Gif work in feed
  if($actionDetails && !empty($actionDetails->gif_url)) {
    $activity[$counter]['gif_url'] = $actionDetails->gif_url;
    $activity[$counter]['gif_id'] = true;
  } else {
    $activity[$counter]['gif_id'] = false;
  }
  
  $activity[$counter]['activityType'] = Engine_Api::_()->getApi('activity','sesapi')->assemble($action->getTypeInfo()->body, $params,$groupId);          //get icon for privacy
    if($action->privacy == 'onlyme'){
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/onlyme.png");
    }else if($action->privacy == 'friends'){
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/friends.png");
    }else if($action->privacy == 'networks'){
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/network.png");
    }else if(strpos($action->privacy,'network_list') !== false){
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/network.png");
    }else if(strpos($action->privacy,'members_list') !== false || strpos($action->privacy,'member_list') !== false){
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/list.png");
    }else{
      $privacyImageUrl = $this->getBaseUrl(true,"/application/modules/Sesapi/externals/images/public.png");
    }
    $activity[$counter]['privacyImageUrl'] = $privacyImageUrl;
    //get activity icon
    $activitySelect = $activityTypeTable->select()->from($activityTypeTable->info('name'))->where('type =?',$action->type);
    $activitytype = $activityTypeTable->fetchRow($activitySelect);
    $icon_type = '.activity_icon_'.$action->type;
    $actionAttachment = engine_count($action->getAttachments()) ? $action->getAttachments() : array();
    $attachment = $action->attachment_count > 0 ? $actionAttachment[0] : "";
    if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
      $icon_type = '.item_icon_'.$attachment->item->getType();
    endif;
    if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ){
      $moduleName = $attachment->item->getModuleName();
    }elseif($activitytype){
      $moduleName =   ucfirst($activitytype->module);
    }else{
      $moduleName =   ucfirst('user');
    }
    if($moduleName){
      $typeFind = $icon_type.":before";
      $filePath =  APPLICATION_PATH . "/application/modules/" . ucfirst($moduleName) . "/externals/styles/main.css";
      $resultCss = Engine_Api::_()->sesapi()->parseCSSFile($filePath);
      if(!empty($resultCss[$typeFind]['content'])){
        $explodedData = $resultCss[$typeFind]['content'];
        $notificationIcon = str_replace(array("\"",'\\'),"",$explodedData);
        if($notificationIcon == "f2c0")
          $notificationIcon = "f007";
        if($activity[$counter]['type'] == "tagged")
        $notificationIcon = "f02b";
        if($activity[$counter]['type'] == "elivestreaming_golive" || $activity[$counter]['type'] == "elivestreaming_was_live")
          $notificationIcon = "f03d";
      }else{
        //default comment icon
        $notificationIcon = "f0e5";
        if($activity[$counter]['type'] == "profile_photo_update")
          $notificationIcon = "f03e";
      }
    }

     $module = Engine_Api::_()->getDbTable('actionTypes','activity')->getActionType($action->type);
     $moduleName = $module->module;
      if(!empty($attachments[0])){
		  $poll = $attachments[0]->item;
          if($poll->getType() == "sespagepoll_poll") {
              $owner = $poll->getOwner();
              $viewer = Engine_Api::_()->user()->getViewer();
              $viewer_id = $viewer->getIdentity();
              $pollOptions = $poll->getOptions();
              $hasVoted = $poll->viewerVoted();
              $showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagepoll.showpiechart', false);
              $canVote = $poll->authorization()->isAllowed(null, 'vote');
              $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagepoll.canchangevote', false);
              $canDelete = $poll->authorization()->isAllowed($viewer, 'delete');
              $canEdit = $poll->authorization()->isAllowed($viewer, 'edit');
              $poll_is_voted = $poll->vote_count > 0 ? true : false;
              $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagepoll.allow.share', 1);
              $likeStatus = Engine_Api::_()->sespage()->getLikeStatus($poll->poll_id, 'sespagepoll_poll');
              $can_fav = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagepoll.allow.favourite', 1);
              $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sespagepoll')->isFavourite(array('resource_id' => $poll->poll_id, 'resource_type' => 'sespagepoll_poll'));
              $result = $poll->toArray();
              $page_id = $poll->page_id;
              $result['resource_type'] = $poll->getTYpe();
              $result['content_id'] = $page_id;
              $user_id = $owner->getIdentity();
              $user = Engine_Api::_()->getItem('user', $user_id);
              $result['owner_title'] = $poll->getOwner()->getTitle();
              $result['can_edit'] = $canEdit > 0 ? true : false;
              $result['can_delete'] = $canDelete > 0 ? true : false;
              $result['has_voted'] = $hasVoted > 0 ? true : false;
              $result['has_voted_id'] = ($hasVoted == false) ? 0 : $hasVoted;
              $result['token'] = $this->view->sesVoteHash($poll)->generateHash();
			  $result['can_change_vote'] = $canChangeVote;
              if ($hasVoted) {
                  if ($canChangeVote) {
                      $result['can_vote'] = true;
                  } else {
                      $result['can_vote'] = false;
                  }
              } else {
                  $result['can_vote'] = $canVote > 0 ? true : false;
              }
              if ($user) {
                  $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                  $result['owner_image'] = $ownerimage;
              }
              $page = Engine_Api::_()->getItem('sespage_page', $page_id);
              if ($page)
                  $result['content_title'] = $page->title;
              if ($viewer_id)
                  $result['is_content_like'] = $likeStatus > 0 ? true : false;
              if ($can_fav)
                  $result['is_content_favourite'] = $favouriteStatus > 0 ? true : false;
              $counter1 = 0;

              foreach ($pollOptions as $option) {

                  $result['options'][$counter1] = $option->toArray();
                  if ($option->file_id > 0 && $option->image_type > 0) {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent']  = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $result['options'][$counter1]['option_image'] = ($storage = Engine_Api::_()->storage()->get($option->file_id, '')) ? $this->getBaseUrl(true, $storage->map()) : "";
                      $tables = Engine_Api::_()->getDbTable('votes', 'sespagepoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(5)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  } else {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $tables = Engine_Api::_()->getDbTable('votes', 'sespagepoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(4)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  }
                  $counter1++;
              }
			  $activity[$counter]['poll'] = $result;
          }
		  if($poll->getType() == "sesbusinesspoll_poll"){
			  $owner = $poll->getOwner();
              $viewer = Engine_Api::_()->user()->getViewer();
              $viewer_id = $viewer->getIdentity();
              $pollOptions = $poll->getOptions();
              $hasVoted = $poll->viewerVoted();
              $showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspoll.showpiechart', false);
              $canVote = $poll->authorization()->isAllowed(null, 'vote');
              $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspoll.canchangevote', false);
              $canDelete = $poll->authorization()->isAllowed($viewer, 'delete');
              $canEdit = $poll->authorization()->isAllowed($viewer, 'edit');
              $poll_is_voted = $poll->vote_count > 0 ? true : false;
              $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinesspoll.allow.share', 1);
              $likeStatus = Engine_Api::_()->sesgroup()->getLikeStatus($poll->poll_id, 'sesbusinesspoll_poll');
              $can_fav = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbusinessspoll.allow.favourite', 1);
              $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sesbusinesspoll')->isFavourite(array('resource_id' => $poll->poll_id, 'resource_type' => 'sesbusinesspoll_poll'));
              $result = $poll->toArray();
              $business_id = $poll->business_id;
              $result['resource_type'] = $poll->getTYpe();
              $result['content_id'] = $business_id;
              $user_id = $owner->getIdentity();
              $user = Engine_Api::_()->getItem('user', $user_id);
              $result['owner_title'] = $poll->getOwner()->getTitle();
              $result['can_edit'] = $canEdit > 0 ? true : false;
              $result['can_delete'] = $canDelete > 0 ? true : false;
              $result['has_voted'] = $hasVoted > 0 ? true : false;
              $result['has_voted_id'] = ($hasVoted == false) ? 0 : $hasVoted;
              $result['token'] = $this->view->sesBusinessVoteHash($poll)->generateHash();
			  $result['can_change_vote'] = $canChangeVote;
              if ($hasVoted) {
                  if ($canChangeVote) {
                      $result['can_vote'] = true;
                  } else {
                      $result['can_vote'] = false;
                  }
              } else {
                  $result['can_vote'] = $canVote > 0 ? true : false;
              }
              if ($user) {
                  $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                  $result['owner_image'] = $ownerimage;
              }
              $business = Engine_Api::_()->getItem('businesses', $business_id);
              if ($business)
                  $result['content_title'] = $business->title;
              if ($viewer_id)
                  $result['is_content_like'] = $likeStatus > 0 ? true : false;
              if ($can_fav)
                  $result['is_content_favourite'] = $favouriteStatus > 0 ? true : false;
              $counter1 = 0;

              foreach ($pollOptions as $option) {

                  $result['options'][$counter1] = $option->toArray();
                  if ($option->file_id > 0 && $option->image_type > 0) {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent'] =  $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $result['options'][$counter1]['option_image'] = ($storage = Engine_Api::_()->storage()->get($option->file_id, '')) ? $this->getBaseUrl(true, $storage->map()) : "";
                      $tables = Engine_Api::_()->getDbTable('votes', 'sesbusinesspoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(5)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  } else {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $tables = Engine_Api::_()->getDbTable('votes', 'sesbusinesspoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(4)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  }
                  $counter1++;
              }
			  $activity[$counter]['poll'] = $result;
		  }
		  if($poll->getType() == "sesgrouppoll_poll"){
			   $owner = $poll->getOwner();
              $viewer = Engine_Api::_()->user()->getViewer();
              $viewer_id = $viewer->getIdentity();
              $pollOptions = $poll->getOptions();
              $hasVoted = $poll->viewerVoted();
              $showPieChart = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.showpiechart', false);
              $canVote = $poll->authorization()->isAllowed(null, 'vote');
              $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.canchangevote', false);
              $canDelete = $poll->authorization()->isAllowed($viewer, 'delete');
              $canEdit = $poll->authorization()->isAllowed($viewer, 'edit');
              $poll_is_voted = $poll->vote_count > 0 ? true : false;
              $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.allow.share', 1);
              $likeStatus = Engine_Api::_()->sesgroup()->getLikeStatus($poll->poll_id, 'sesgrouppoll_poll');
              $can_fav = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgrouppoll.allow.favourite', 1);
              $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'sesgrouppoll')->isFavourite(array('resource_id' => $poll->poll_id, 'resource_type' => 'sesgrouppoll_poll'));
              $result = $poll->toArray();
              $group_id = $poll->group_id;
              $result['resource_type'] = $poll->getTYpe();
              $result['content_id'] = $group_id;
              $user_id = $owner->getIdentity();
              $user = Engine_Api::_()->getItem('user', $user_id);
              $result['owner_title'] = $poll->getOwner()->getTitle();
              $result['can_edit'] = $canEdit > 0 ? true : false;
              $result['can_delete'] = $canDelete > 0 ? true : false;
              $result['has_voted'] = $hasVoted > 0 ? true : false;
              $result['has_voted_id'] = ($hasVoted == false) ? 0 : $hasVoted;
              $result['token'] = $this->view->sesGroupVoteHash($poll)->generateHash();
			  $result['can_change_vote'] = $canChangeVote;
              if ($hasVoted) {
                  if ($canChangeVote) {
                      $result['can_vote'] = true;
                  } else {
                      $result['can_vote'] = false;
                  }
              } else {
                  $result['can_vote'] = $canVote > 0 ? true : false;
              }
              if ($user) {
                  $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                  $result['owner_image'] = $ownerimage;
              }
              $group = Engine_Api::_()->getItem('sesgroup_group', $group_id);
              if ($group)
                  $result['content_title'] = $group->title;
              if ($viewer_id)
                  $result['is_content_like'] = $likeStatus > 0 ? true : false;
              if ($can_fav)
                  $result['is_content_favourite'] = $favouriteStatus > 0 ? true : false;
              $counter1 = 0;
              foreach ($pollOptions as $option) {

                  $result['options'][$counter1] = $option->toArray();
                  if ($option->file_id > 0 && $option->image_type > 0) {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent'] =  $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $result['options'][$counter1]['option_image'] = ($storage = Engine_Api::_()->storage()->get($option->file_id, '')) ? $this->getBaseUrl(true, $storage->map()) : "";
                      $tables = Engine_Api::_()->getDbTable('votes', 'sesgrouppoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(5)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  } else {
                      $pct = $poll->vote_count ? floor(100 * ($option->votes / $poll->vote_count)) : 0;
                      if (!$pct)
                          $pct = 1;
                      $result['options'][$counter1]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->
                          translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
                      $tables = Engine_Api::_()->getDbTable('votes', 'sesgrouppoll')->getVotesPaginator($option->poll_option_id)->setItemCountPerPage(4)->setCurrentPageNumber(1);
                      $pagecount = $tables->getPages()->pageCount;
					  $voteUserCounter = 0;
                      foreach ($tables as $table) {
                          $user = Engine_Api::_()->getItem('user', $table->user_id);
                          $userImage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resourece_id'] = $user->getIdentity();
                          $result['options'][$counter1]['voted_user'][$voteUserCounter]['resource_type'] = $user->getType();
                          if ($userImage) {
                              $result['options'][$counter1]['voted_user'][$voteUserCounter]['user_image'] = $userImage;
                          }
                          $voteUserCounter++;
                      }
                      $result['options'][$counter1]['more_user_link'] = $pagecount > 1 ? true : false;
                  }
                  $counter1++;
              }
			  $activity[$counter]['poll'] = $result;
		  }

      }
      $activity[$counter]['activityIcon']  = str_replace(' ','',str_replace('!important','',$notificationIcon));
      if(empty($activity[$counter]['activityIcon'])){
          $activity[$counter]['activityIcon'] = "f15b";
      }
    //post attribution code
    $viewer = $this->view->viewer();
    if($viewer->getIdentity()){
        if($moduleName == "sespage" || $action->object_type == "sespage_page"){
            $subjectPage = $subject;
            $isPageSubject = false;
            if($subjectPage && $subject->getType() == "sespage_page"){
              if(Engine_Api::_()->getDbTable('pageroles','sespage')->toCheckUserPageRole($viewer->getIdentity(),$subjectPage->getIdentity(),'manage_dashboard','delete')){
                $attributionType = Engine_Api::_()->getDbTable('postattributions','sespage')->getPagePostAttribution(array('page_id' => $subjectPage->getIdentity()));
                $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'page_attribution');
                $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $this->view->viewer(), 'page_attribution_allowuser');
                if (!$pageAttributionType || $attributionType == 0) {
                  $isPageSubject = $viewer;
                }
                if($pageAttributionType && !$allowUserChoosePageAttribution) {
                  $isPageSubject = $viewer;
                }
                if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
                   $isPageSubject = $subjectPage;
                }
              }
            }
            $activity[$counter]['sespage_page']['selected_id']  = !empty($isPageSubject) ? $isPageSubject->getIdentity() : $viewer->getIdentity();                    $activity[$counter]['sespage_page']['selected_type']  = !empty($isPageSubject) ? $isPageSubject->getType() : $viewer->getType();
            $activity[$counter]['sespage_page']['selected_guid']  = !empty($isPageSubject) ? $isPageSubject->getGuid() : $viewer->getGuid();
            if($activity[$counter]['sespage_page']['selected_type'] == "user")
              $activity[$counter]['sespage_page']['photo'] = !empty($isPageSubject) ? $this->userImage($isPageSubject->getIdentity(),"thumb.profile") : $this->userImage($viewer->getIdentity(),"thumb.profile");
            else
              $activity[$counter]['sespage_page']['photo'] = $this->getBaseUrl(true,$isPageSubject->getPhotoUrl('thumb.profile'));

            $activity[$counter]['post_attribution'] = 'sespage_page';
        }else if($moduleName == "sesbusiness" || $action->object_type == "businesses"){
            $subjectPage = $subject;
            $isPageSubject = false;
            if($subjectPage && $subject->getType() == "businesses"){
              if(Engine_Api::_()->getDbTable('businessroles','sesbusiness')->toCheckUserBusinessRole($viewer->getIdentity(),$subjectPage->getIdentity(),'manage_dashboard','delete')){
                $attributionType = Engine_Api::_()->getDbTable('postattributions','sesbusiness')->getBusinessPostAttribution(array('business_id' => $subjectPage->getIdentity()));
                $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution');
                $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $this->view->viewer(), 'seb_attribution_allowuser');
                if (!$pageAttributionType || $attributionType == 0) {
                  $isPageSubject = $viewer;
                }
                if($pageAttributionType && !$allowUserChoosePageAttribution) {
                  $isPageSubject = $viewer;
                }
                if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
                   $isPageSubject = $subjectPage;
                }
              }
            }
            $activity[$counter]['businesses']['selected_id']  = !empty($isPageSubject) ? $isPageSubject->getIdentity() : $viewer->getIdentity();                    $activity[$counter]['businesses']['selected_type']  = !empty($isPageSubject) ? $isPageSubject->getType() : $viewer->getType();
            $activity[$counter]['businesses']['selected_guid']  = !empty($isPageSubject) ? $isPageSubject->getGuid() : $viewer->getGuid();
            if($activity[$counter]['businesses']['selected_type'] == "user")
              $activity[$counter]['businesses']['photo'] = !empty($isPageSubject) ? $this->userImage($isPageSubject->getIdentity(),"thumb.profile") : $this->userImage($viewer->getIdentity(),"thumb.profile");
            else
              $activity[$counter]['businesses']['photo'] = $this->getBaseUrl(true,$isPageSubject->getPhotoUrl('thumb.profile'));

            $activity[$counter]['post_attribution'] = 'businesses';
            // for live streaming.
            } else if ($moduleName == "elivestreaming" || $action->object_type == "elivehost") {
              if (_SESAPI_VERSION_IOS < 1.8 && _SESAPI_PLATFORM_SERVICE == 1) {
                $activity[$counter]['object_type'] = 'user';
                $activity[$counter]['object_id'] = $action->subject_id;
              }
            }
    }
    //end post attribution code

    if( engine_count($activity) == $length ) {
      $break = true;
    }
  }
