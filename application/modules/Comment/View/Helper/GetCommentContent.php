<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: GetCommentContent.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Comment_View_Helper_GetCommentContent
{
  public function getCommentContent($content = null, array $data = array()) {

    $content =  $this->stringsToURLStrings($content);
    $content =  $this->gethashtags($content);
    $content = $this->getMentionTags($content);

    return ($content);
  }
  protected function stringsToURLStrings($stringBody){
    $pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
    return preg_replace($pattern, '<a href="http$2://$3" target="_blank">$0</a>', $stringBody);
  }
  function gethashtags($content)
  {
    preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", $content, $matches);
    $searchword = $replaceWord = array();
    foreach($matches[0] as $value){
      if(!engine_in_array($value,$searchword)){
        $searchword[]=$value;
        $replaceWord[] = '<a target="_blank" href="hashtag?hashtag='.str_replace('#','',$value).'">'.$value.'</a>';
      }
    }
    $content = str_replace($searchword,$replaceWord,$content);
    return $content;
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
                $user = Engine_Api::_()->getItem($resource_type, $resource_id);
            }catch (Exception $e){
                continue;
            }
          if(!$user || !$user->getIdentity())
            continue;
        }
        $contentMention = str_replace($value,'<a href="'.$user->getHref().'" data-src="'.$user->getGuid().'">'.$user->getTitle().'</a>',$contentMention);
    }

    if(is_array($content))
      $content[1] = $contentMention;
    else
      $content = $contentMention;

    return $content;
  }
}
