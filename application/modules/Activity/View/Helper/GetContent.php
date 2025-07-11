<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: GetContent.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_View_Helper_GetContent {

  const Emojione = 'Emojione';

  public function getContent($actions = null, array $data = array(),$break = true,$change = false) {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $group_feed_id = !empty($data['group_feed']) ? $data['group_feed'] : "";
    if($actions instanceof Activity_Model_Action || $actions instanceof Activity_Model_Action){
      $model = Engine_Api::_()->getApi('core', 'activity');
      $subject = $actions->getSubject();
      $object = $actions->getObject();
      $resourceType = empty($data['resource_type']) ? "" : $data['resource_type'];
      $resourceId = empty($data['resource_id']) ? 0 : $data['resource_id'] ;
      $params = array_merge(
        $actions->toArray(),
        (array) $actions->params,
        array(
          'subject' => $subject,
          'resource_type'=>$resourceType,
          'resource_id'=>$resourceId,
          'object' => $actions->getObject(),
          'owner' =>  $actions->type == "album_like" || $actions->type == "album_photo_like" ? Engine_Api::_()->getItem('user',$object->getOwner()) : "",
        )
      );
      if(isset($params['params']['body']) && !empty($params['params']['body'])){
        $params['body'] = $params['params']['body'] = preg_replace('/<div class=\'body\'>(.*?)<\/div>/', $actions->body, $params['params']['body']);
      }
      
      $content = $model->assemble($actions->getTypeInfo()->body, $params,$break,$group_feed_id);
    }else {
      $content = $actions;
    }
    //change content for emojies
    $emoji = Engine_Api::_()->activity()->getEmoticons(true);
    $content = str_replace(array_keys($emoji),array_values($emoji),$content);
    //usage
    $content =  $this->gethashtags($content);
    $content = $this->getMentionTags($content);
    //Feeling Post share work
    if($change) {
      $action_id = $actions->getIdentity();
      if($action_id) {
        $feelingposts = Engine_Api::_()->getDbTable('feelingposts','activity')->getActionFeelingposts($action_id);
        if($feelingposts) {
          $feelings = Engine_Api::_()->getItem('activity_feeling', $feelingposts->feeling_id);
          if($feelings->type == 1) {
            $feelingIcon = Engine_Api::_()->getItem('activity_feelingicon', $feelingposts->feelingicon_id);
            $content = $content . " is <img class='feeling_icon' src=".Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '')->getPhotoUrl()."> ".strtolower($feelings->title).' '.strtolower($feelingIcon->title);
          }  else if($feelings->type == 2 && $feelingposts->resource_type && $feelingposts->feelingicon_id) {
            $resource = Engine_Api::_()->getItem($feelingposts->resource_type, $feelingposts->feelingicon_id);
            $content = $content . " is <img title=".strtolower($resource->title).' class="feeling_icon" src='. Engine_Api::_()->storage()->get($feelings->file_id, "")->getPhotoUrl().'> '.strtolower($feelings->title).' <a href='.strtolower($resource->getHref()).'>'.strtolower($resource->title).'</a>';
          }
        }
      }
    }
    //Feeling Post share work

    //location share post work
    if($change) {
      $action_id = $actions->getIdentity();
      if($action_id) {
        $location = Engine_Api::_()->getDbTable('locations','core')->getLocationData('activity_action', $action_id);
        if($location && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
          $content = $content. " in <a target='_blank' href=".'http://maps.google.com/?q='.$location->venue.'>'.$location->venue."</a>";
        }
      }
    }
    //location share post work

    if( strpos( $content[1], $_SERVER['HTTP_HOST'] ) === false )
      $content[1] = str_replace('<a', '<a ', $content[1]);
    $content[1] = trim($content[1], ' ');

    return $this->stringsToURLStrings($content);
  }

  protected function stringsToURLStrings($stringBody) {

    $string = str_replace(" ", " RANDOMSTRING ", $stringBody[1]);
    $string = preg_replace(
        array(
            '~(\s|^)(www\..+?)(\s|$)~im', 
            '~(\s|^)(https?://)(.+?)(\s|$)~im', 
        ),
        array(
            '$1http://$2$3', 
            '$1<a href="$2$3" target="_blank">$3</a>$4', 
        ),
        $string
    );
    
    $string = str_replace(" RANDOMSTRING ", "  ", $string);
    
    return array($stringBody[0], $string);
  }
  
  function getMentionTags($content){
    if(is_array($content))
      $contentMention = $content[1];
    else
      $contentMention = $content;

    preg_match_all('/(^|\s)(@\w+)/', $contentMention, $result);
    foreach($result[2] as $value){
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $user = Engine_Api::_()->getItem('user',$user_id);
          if(!$user || !$user->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type = implode('_',$itemArray);
            try {
                if(intval($resource_id) > 0)
                $user = Engine_Api::_()->getItem($resource_type, $resource_id);
            }catch (Exception $e){
                continue;
            }
          if(!$user || !$user->getIdentity())
            continue;
        }

        $contentMention = str_replace($value,'<a href="'.$user->getHref().'" data-src="'.$user->getGuid().'" class="core_tooltip">'.$user->getTitle().'</a>',$contentMention);
    }

    if(is_array($content))
      $content[1] = $contentMention;
    else
      $content = $contentMention;

    return $content;

  }
  function gethashtags($content)
  {
   // return $parsedMessage = preg_replace(array('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', '/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '/(^|[^a-z0-9_])#([a-z0-9_]+)/i'), array('<a href="$1">$1</a>', '$1@$2', '$1<a href="hashtag?search=$2">#$2</a>'), $content);
    preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", @$content[1], $matches);
    $searchword = $replaceWord = array();
    foreach($matches[0] as $value){
      if(!engine_in_array($value,$searchword)){
        $searchword[]=$value;
        $replaceWord[] = '<a href="hashtag?search='.str_replace('#','',strip_tags($value)).'">'.strip_tags($value).'</a>';
      }
    }
    $content[1] = str_replace($searchword,$replaceWord, @$content[1]);
    return $content;
  }
}
