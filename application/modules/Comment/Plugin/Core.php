<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Core.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Plugin_Core {
  
  public function onRenderLayoutDefault($event, $mode = null) {
  
		// if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) return;
		
    $view = $event->getPayload();
    if( !($view instanceof Zend_View_Interface) ) {
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer->getIdentity()) {
      $search = array(
          '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
          '/[^\S ]+\</s',  // strip whitespaces before tags, except space
          '/(\s)+/s'       // shorten multiple whitespace sequences
      );
      $replace = array('>', '<', '\\1');
      
      $emojiContent = $view->partial('emojicontent.tpl','comment',array());
      $emojiContent = preg_replace($search, $replace, $emojiContent);
      
      $script = "en4.core.runonce.add(function() {
        scriptJquery(".json_encode($emojiContent.'<a href="javascript:;" class="exit_emoji_btn notclose" style="display:none;">').").appendTo('#append-script-data');
      });";
      $view->headScript()->appendScript($script);

      $gifContent = $view->partial('gifcontent.tpl','activity',array());
      $gifContent = preg_replace($search, $replace, $gifContent);
      $script = "en4.core.runonce.add(function() {
        scriptJquery('".$gifContent.'<a href="javascript:;" class="exit_gif_btn notclose" style="display:none;">'."').appendTo('#append-script-data');
      });";
      $view->headScript()->appendScript($script);

      //Emojis work
      $feeling_emojiContent = $view->partial('_emojis.tpl','activity',array());
      $search = array(
          '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
          '/[^\S ]+\</s',  // strip whitespaces before tags, except space
          '/(\s)+/s'       // shorten multiple whitespace sequences
      );
      $replace = array(
          '>',
          '<',
          '\\1'
      );
      $feeling_emojiContent = preg_replace($search, $replace, $feeling_emojiContent);
      $script = "scriptJquery(document).ready(function() {
        scriptJquery('".$feeling_emojiContent.'<a href="javascript:;" class="feeling_exit_emoji_btn notclose" style="display:none;">'."').appendTo('#append-script-data');
      });";
      $view->headScript()->appendScript($script);
      //Emojis work
      

      //check album and video plugins enable
      $album = $video = 0;
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')){
        $album = 1; 
      }
      
      $videoType = '';
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {
        $video = 1; 
        $videoType = 'video'; 
      } else if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo')){
        $video = 1; 
        $videoType = 'sesvideo'; 
      }
      
      $youtubeEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey', 0) ?  1 : 0;

      $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
      $userTagsEnable = $hashtagsEnable = false;
      if (engine_in_array('userTags', $composerOptions)) {
        $userTagsEnable = true;
      }
      if (engine_in_array('hashtags', $composerOptions)) {
        $hashtagsEnable = true;
      }

      $script = "
        var AlbumModuleEnable = ".$album.";
        var videoModuleEnable = ".$video.";
        var youtubePlaylistEnable = '".$youtubeEnable."';
        var videoModuleName = '".$videoType."';
        var hashtagsEnable = '".$hashtagsEnable."';
        var userTagsEnable = '".$userTagsEnable."';
        var enablesearch = '1';
      ";
      $view->headScript()->appendScript($script);
    }
  }

  public function onRenderLayoutDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }
}
