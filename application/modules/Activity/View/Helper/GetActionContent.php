<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: GetActionContent.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_View_Helper_GetActionContent extends Zend_View_Helper_Abstract
{

  public function getActionContent(Activity_Model_Action $action, $similarActivities = array() )
  {
    return $action->getContent();
  }

  public function updateActionContent($action, $content)
  {
    $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
    if (empty($composerOptions)) {
      return $content;
    }

    $content = $this->smileyToEmoticons($content);

    if (engine_in_array('userTags', $composerOptions)) {
      $content = $this->replaceTags($action, $content);
    }

    if (engine_in_array('hashtags', $composerOptions) &&
      ($action instanceof Activity_Model_Action || $action instanceof Activity_Model_Comment)
    ) {
      $content = $this->replaceHashTags($content);
    }
    $content =  $this->stringsToURLStrings($content);
    return $content;
  }

  public function smileyToEmoticons($string = null)
  {
    if(empty(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')))
      return $string;
    if (!engine_in_array('emoticons', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return $string;
    }

    $emoticonsTag = Engine_Api::_()->activity()->getEmoticons(true);

    if (empty($emoticonsTag)) {
      return $string;
    }

    $string = str_replace("&lt;:o)", "<:o)", $string);
    $string = str_replace("(&amp;)", "(&)", $string);

    return strtr($string, $emoticonsTag);
  }

  private function replaceTags($action, $content)
  {
    $actionParams = is_string($action->params) ? Zend_Json::decode($action->params) : (array) $action->params;
    if (isset($actionParams['tags'])) {
      foreach ((array) $actionParams['tags'] as $key => $tagStrValue) {
        $tag = Engine_Api::_()->getItemByGuid($key);
        if (!$tag) {
          continue;
        }
        $replaceStr = '<a class="feed_item_username" '
          . 'href="' . $tag->getHref() . '" '
          . 'rel="' . $tag->getType() . ' ' . $tag->getIdentity() . '" >'
          . $tag->getTitle()
          . '</a>';
        $content = preg_replace("/" .addcslashes(preg_quote($tagStrValue),"/"). "/", $replaceStr, $content);
      }
    }
    return $content;
  }

  private function replaceHashTags($content)
  {
    $string = $content;
    $hashtags = Engine_Api::_()->activity()->getHashTags($string);
    $hashtags = $hashtags[0];
    if( empty($hashtags) ) {
      return $string;
    }
    $newString = '';
    foreach( $hashtags as $hashtag ) {
      $hasHastag = strpos($string, '#' . $hashtag);
      $substr = $hasHastag ? substr($string, 0, $hasHastag) : '';
      $newString .= $substr . $this->getHashtagLink($hashtag);
      $string = substr($string, $hasHastag + strlen($hashtag) + 1);
    }
    $newString .= $string;
    return $newString;
  } 
  protected function stringsToURLStrings($stringBody){
    $pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
    return preg_replace($pattern, '<a href="http$2://$3" target="_blank">$0</a>', $stringBody);
  }
  private function getHashtagLink($hashtag)
  {
    $view = Zend_Registry::get('Zend_View');
    $url = $this->view->url(array('controller' => 'hashtag', 'action' => 'index'), "core_hashtags") . "?search=" . urlencode('#' . $hashtag);
    return "<a href='$url'>" . '#' . $hashtag . "</a>";
  }
}
