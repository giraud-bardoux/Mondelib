<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Plugin_Core
{
  public function onActivityNotificationCreateAfter($event) {
  if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesandroidapp.pluginactivated')) {
   $notification = $event->getPayload();
   
   try{
    $object = $notification->getObject();
    $subject = $notification->getSubject(); 
    $user = $notification->getUser(); 
   }catch(Exception $e){
      return false;  
   }
   
   if($notification->subject_id == $notification->user_id)
      return;
    $notificationType = $notification->type;
    //check column exists
    $check = false;
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $column = $db->query('SHOW COLUMNS FROM engine4_activity_notificationtypes LIKE \'sesandoidapp_enable_pushnotification\'')->fetch();
    if(!empty($column )){
      $check = true;
    }
    if($check){
      $table = Engine_Api::_()->getDbTable('notificationTypes','activity');
      $isEnabled = $table->select()->from($table->info('name'),'sesandoidapp_enable_pushnotification')->where('type =?',$notificationType)->where('sesandoidapp_enable_pushnotification =?',1)->query()->fetchColumn();
      if(!$isEnabled)
        return;
    }
    //get tokens
    $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getToken($notification->user_id,2); 
    if(!engine_count($tokens))
      return;
    $result = array();
    $model = Engine_Api::_()->getApi('core', 'activity');
    $counterLoop = 0;
    $baseURL = Engine_Api::_()->sesapi()->getBaseUrl();
    
      $params = array_merge(
        $notification->toArray(),
        (array) $notification->params,
        array(
          'user' => $user,
          'object' => $object,
          'subject' => $subject,
        )
      );
      $info = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationType($notification->type);
      if( !$info )
        return;
      $title = $model->assemble($info->body, $params)[0];
      $dom = new DOMDocument;
      $dom->loadHTML($title);
      $xpath = new DOMXPath($dom);
      $nodes = $xpath->query('//a/@href');
      $hrefValue = array();
      $parentNodeValue = '';
      $counter = 0;
      foreach($nodes as $href){
        if($counter == 0) 
         $parentNodeValue =  $href->parentNode->nodeValue;
        $counter++;
        $hrefValue[] = $href->nodeValue;  // remove attribute
      }
      if(engine_count($hrefValue) > 0)
        $href = Engine_Api::_()->sesapi()->getBaseUrl('',$hrefValue[count($hrefValue) - 1]);
      else
        $href = $baseURL;
        
      $user = Engine_Api::_()->getItem('user', $notification->subject_id);
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;;
      
      //commented liked _like
      if(strpos($notification->type,'commented') !== false || strpos($notification->type,'liked') !== false || strpos($notification->type,'_like') !== false || strpos($notification->type,'_reacted_') !== false)
        $isCommentLike = true;
      else
        $isCommentLike = false;
        
      $image = Engine_Api::_()->sesapi()->getBaseUrl('', $user->getPhotoUrl());
      $result['notification'] = array('object_id'=>$notification->object_id,'subject_id'=>$notification->subject_id,'user_image'=>$image,'user_name'=>$user->getTitle(false),'href'=>$href,'object_type'=>$notification->object_type,'isCommentLike'=>$isCommentLike);
    
      // for activity and core comment like notifications
      if($notification->object_type == "activity_comment" || $notification->object_type == "core_comment"){
        $nitiItem = Engine_Api::_()->getItem($notification->object_type,$notification->object_id); 
        if($nitiItem){
          $result['notification']['object_id'] = $nitiItem->resource_id;
          $result['notification']['object_type'] = 'activity_action';
          $result['notification']['isCommentLike'] = true;
        }else{
          return;
        } 
      }
      
      if(($notification->type == "activity_tagged_people")  && $notification->params != ""){
         if(isset($notification->params['postLink'])){
           $content = $notification->params['postLink'];
           if(strpos($content,'<a ') !== false){
             $a = new SimpleXMLElement($content);
            $result['notification']['object_id'] = (int) end(explode('/',$a['href']));
            $result['notification']['object_type']  = 'activity_action';
            $result['notification']['href']  = Engine_Api::_()->sesapi()->getBaseUrl('',$a['href']);
           }
         }
      }
      $result['notification']['type'] = $notification->type;
      // for live streaming.
      if($notification->type == "elivestreaming_golive" && $notification->params != ""){
        $result['notification']['activity_action_id'] = $notification->params['activity_action_id'];
        $result['notification']['host_id'] = $notification->params['host_id'];
      }
      
      if($notification->object_type == "album_photo"){
         $objectItem = Engine_Api::_()->getItem($notification->object_type,$notification->object_id);
         $result['notification']['photo_image'] = Engine_Api::_()->sesapi()->getBaseUrl('',$objectItem->getPhotoUrl());
         $result['notification']['album_id'] = $objectItem->album_id;
      }
      $result['notification']['object'] = $notification->toArray();
      $data['title'] = strip_tags($title);
      $data['description'] = " ";
      $userInfo = $result['notification'];
      $result = Engine_Api::_()->getApi('pushnoti','sesapi')->android($data,$tokens,$userInfo);
    }
  }
}
