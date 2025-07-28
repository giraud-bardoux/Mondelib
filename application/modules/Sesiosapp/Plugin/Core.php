<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Plugin_Core
{
  public function onActivityNotificationCreateAfter($event) {
  
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesiosapp.pluginactivated')) {
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
      $column = $db->query('SHOW COLUMNS FROM engine4_activity_notificationtypes LIKE \'sesios_enable_pushnotification\'')->fetch();
      if (!empty($column )) {
        $check = true;
      }
      if($check){
        $table = Engine_Api::_()->getDbTable('notificationTypes','activity');
        $isEnabled = $table->select()->from($table->info('name'),'sesios_enable_pushnotification')->where('type =?',$notificationType)->where('sesios_enable_pushnotification =?',1)->query()->fetchColumn();
        
        if(!$isEnabled)
          return;
      }
      //get tokens
      $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getToken($notification->user_id); 
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
      
      //commented liked
      if(strpos($notification->type,'commented') !== false || strpos($notification->type,'liked') !== false || strpos($notification->type,'_like') !== false || strpos($notification->type,'_reacted_') !== false)
        $isCommentLike = true;
      else
        $isCommentLike = false;
      $result['notification'] = array('object_id'=>$notification->object_id,'subject_id'=>$notification->subject_id,'href'=>$href,'object_type'=>$notification->object_type,'isCommentLike'=>$isCommentLike);
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
        $result['notification']['type'] = $notification->type;
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
      
      if($notification->object_type == "album_photo"){
        $objectItem = Engine_Api::_()->getItem($notification->object_type,$notification->object_id);
        $result['notification']['photo_image'] = Engine_Api::_()->sesapi()->getBaseUrl('',$objectItem->getPhotoUrl());
        $result['notification']['album_id'] = $objectItem->album_id;
      }
      $data['title'] = strip_tags($title);
      $data['description'] = " ";
      $userInfo = $result['notification'];
      
      $result = Engine_Api::_()->getApi('pushnoti','sesapi')->iOS($data,$tokens,$userInfo);
    }
  }
}
