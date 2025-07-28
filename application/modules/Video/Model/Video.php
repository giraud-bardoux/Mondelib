<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Video.php 10223 2014-05-15 17:02:17Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Model_Video extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'user';

  protected $_owner_type = 'user';

  protected $_parent_is_owner = true;

  public function getHref($params = array())
  {
    if($this->feedupload) {
      $params = array_merge(array(
        'route' => 'activity_attachment_view',
        'reset' => true,
        'id' => $this->video_id,
        'type' => 'video',
        'action_id' => $this->feedupload,
      ), $params);
    } else {
      $params = array_merge(array(
        'route' => 'video_view',
        'reset' => true,
        'user_id' => $this->owner_id,
        'video_id' => $this->video_id,
        'slug' => $this->getSlug(),
      ), $params);
    }
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }
  
  public function getTitle() {
    return Engine_Text_Emoji::decode($this->title);
  }

  public function getRichContent($view = false, $params = array())
  {
    $session = new Zend_Session_Namespace('mobile');
    $mobile = $session->mobile;
     
    $videoEmbedded = '';
    // if video type is youtube
    if( $this->type == 'youtube' ) {
      $videoEmbedded = $this->compileYouTube($this->video_id, $this->code, $view, $mobile);
    }
    // if video type is vimeo
    if( $this->type == 'vimeo' ) {
      $videoEmbedded = $this->compileVimeo($this->video_id, $this->code, $view, $mobile);
    }

    // if video type is vimeo
    if( $this->type == 'iframely' ) {
      $videoEmbedded = $this->code;
    }
    
    if(in_array($this->type, array('YouTube', 'Vimeo', 'Dailymotion'))) {
			$videoEmbedded = $this->code;
    }

    // if video type is uploaded
    if( $this->type == 'upload' ) {
      $storageFile = Engine_Api::_()->storage()->get($this->file_id, $this->getType());
      $videoLocation = $storageFile->getHref();
      if( $storageFile->extension === 'flv' ) {
        $videoEmbedded = $this->compileFlowPlayer($videoLocation, $view);
      } else {
        $videoEmbedded = $this->compileHTML5Media($videoLocation, $view);
      }
    }

    // $view == false means that this rich content is requested from the activity feed
    if( $view==false ) {

      // prepare the duration
      //
      $videoDuration = "";
      if( $this->duration ) {
        if( $this->duration >= 3600 ) {
          $duration = gmdate("H:i:s", $this->duration);
        } else {
          $duration = gmdate("i:s", $this->duration);
        }
        //$duration = ltrim($duration, '0:');

        $videoDuration = "<span class='item_length'>".$duration."</span>";
      }

      // prepare the thumbnail
      $thumb = '';
      if( $this->type != 'iframely' ) {
        $thumb = Zend_Registry::get('Zend_View')->itemBackgroundPhoto($this, 'thumb.normal', '', array(
          'class' => 'thumb_video_activity'
        ));

          $thumb = '<a id="video_thumb_' . $this->video_id . '" style="" href="javascript:void(0);" onclick="javascript:var myElement = scriptJquery(this);myElement.css(\'display\',\'none\');var next = myElement.next(); next.css(\'display\',\'block\');">
                  <div class="video_thumb_wrapper">' . $videoDuration . $thumb . '</div>
                  </a>';
      }

      // prepare title and description
      $title = $this->getTitle();
      $title = "<div class='feed_item_link_title'><a href='".$this->getHref($params)."'>$title</a></div>";
      $tmpBody = strip_tags($this->description);
      $description = "<div class='feed_item_link_desc'>".(Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody)."</div>";
      
      if($this->feedupload) {
        $videoEmbedded = '<div id="video_object_'.$this->video_id.'" class="video_object video_object_'. $this->type .'">'.$videoEmbedded.'</div><div class="video_info"></div>';
      } else {
        $videoEmbedded = '<div id="video_object_'.$this->video_id.'" class="video_object video_object_'. $this->type .'">'.$videoEmbedded.'</div><div class="video_info">'.$title.$description.'</div>';
      }
    }
    return $videoEmbedded;
  }

  public function getEmbedCode(array $options = null)
  {
    $options = array_merge(array(
      'height' => '525',
      'width' => '525',
    ), (array) $options);
    $view = Zend_Registry::get('Zend_View');
    $url = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
      . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
        'module' => 'video',
        'controller' => 'video',
        'action' => 'external',
        'video_id' => $this->getIdentity(),
      ), 'default', true) . '?format=frame';
    return '<iframe '
      . 'src="' . $view->escape($url) . '" '
      . 'width="' . sprintf("%d", $options['width']) . '" '
      . 'height="' . sprintf("%d", $options['width']) . '" '
      . 'style="overflow:hidden;"'
      . '>'
      . '</iframe>';
  }

  public function compileYouTube($videoId, $code, $view, $mobile = false)
  {
    $autoplay = !$mobile && $view;

    $embedded = '
    <iframe
    title="YouTube video player"
    id="videoFrame'.$videoId.'"
    class="youtube_iframe'.($view?"_big":"_small").'"'.
    'src="//www.youtube.com/embed/'.$code.'?wmode=opaque'.($autoplay?"&autoplay=1":"").'"
    frameborder="0"
    allowfullscreen=""
    scrolling="no">
    </iframe>
    <script type="text/javascript">
        en4.core.runonce.add(function() {
        var doResize = function() {
            var aspect = 16 / 9;
            var el = document.id("videoFrame'.$videoId.'");
            var parent = el.getParent();
            var parentSize = parent.getSize();
            el.set("width", parentSize.x);
            el.set("height", parentSize.x / aspect);
        }
        window.addEvent("resize", doResize);
        doResize();
        });
    </script>
    ';

    return $embedded;
  }

  public function compileVimeo($videoId, $code, $view, $mobile = false)
  {
      $autoplay = !$mobile && $view;

      $embedded = '
        <iframe
        title="Vimeo video player"
        id="videoFrame'.$videoId.'"
        class="vimeo_iframe'.($view?"_big":"_small").'"'.
        ' src="//player.vimeo.com/video/'.$code.'?title=0&amp;byline=0&amp;portrait=0&amp;wmode=opaque'.($autoplay?"&amp;autoplay=1":"").'"
        frameborder="0"
        allowfullscreen=""
        scrolling="no">
        </iframe>
        <script type="text/javascript">
          en4.core.runonce.add(function() {
            var doResize = function() {
              var aspect = 16 / 9;
              var el = document.id("videoFrame'.$videoId.'");
              var parent = el.getParent();
              var parentSize = parent.getSize();
              el.set("width", parentSize.x);
              el.set("height", parentSize.x / aspect);
            }
            window.addEvent("resize", doResize);
            doResize();
          });
        </script>
        ';

    return $embedded;
  }

  public function compileFlowPlayer($location, $view)
  {
      $embedded = "
      <div class=\"flowplayer\" data-embed=\"false\" data-share=\"false\" style=\"width: 480px;height: 386px;\">
        <video id='video".$this->video_id."'>
            <source type='video/flv;' src=".$location.">
        </video>
      </div>";

    return $embedded;
  }

  public function compileHTML5Media($location, $view)
  {
  	$storage_file = Engine_Api::_()->getItem('storage_file', $this->file_id);
  	$video_location =  Engine_Api::_()->video()->returncurrentvideo($this->video_id, $storage_file->map());
    
    $embedded = "
    <video id='video_".$this->video_id."' playsinline controls preload='auto' width='480' style='height:97%;object-fit:fill;'>
    	". $video_location ."
    </video>
    <script type='text/javascript'>
      en4.core.runonce.add(function() {
    		new Plyr('#video_".$this->video_id."');
    	});
    </script>
    ";
    return $embedded;
  }

  public function getKeywords($separator = ' ')
  {
    $keywords = array();
    foreach( $this->tags()->getTagMaps() as $tagmap ) {
          $tag = $tagmap->getTag();
          if ($tag === null) {
              continue;
          }
          $keywords[] = $tag->getTitle();
      }

    if( null === $separator ) {
      return $keywords;
    }

    return join($separator, $keywords);
  }

  // Interfaces

  /**
   * Gets a proxy object for the comment handler
   *
   * @return Engine_ProxyObject
   **/
  public function comments()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
  }

  /**
   * Gets a proxy object for the like handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

  /**
   * Gets a proxy object for the tags handler
   *
   * @return Engine_ProxyObject
   **/
  public function tags()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }
  
  public function follow() {
      return true;
  }
    
  public function getParentItem(){
      if(!$this->parent_type || !$this->parent_id || $this->parent_type == "user")
        return false;
      return Engine_Api::_()->getItem($this->parent_type,$this->parent_id) ?? false;
  }
  public function getCategoryItem()
  {
    if(!$this->category_id)
      return false;
    return Engine_Api::_()->getItem('video_category',$this->category_id);
  }
}
