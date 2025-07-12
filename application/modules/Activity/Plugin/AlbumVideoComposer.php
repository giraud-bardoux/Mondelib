<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AlbumVideoComposer.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Plugin_AlbumVideoComposer extends Core_Plugin_Abstract
{
  public function onAttachAlbumvideo($data)
  {
    if (!is_array($data) && (empty($data['video_id']) || empty($data['photo_id']))) {
      return;
    }

    if (isset($data['video_id']) && !empty($data['video_id'])) {
      $video = Engine_Api::_()->getItem('video', $data['video_id']);
      $filter = new Zend_Filter();
      $filter->addFilter(new Engine_Filter_Censor());
      $filter->addFilter(new Zend_Filter_StripTags());
      // update $video with new title and description
      $video->title = @$data['title'] ? $filter->filter(@$data['title']) : 'Untitled Video';
      $video->description = $filter->filter(@$data['description']);

      // Set parents of the video
      if (Engine_Api::_()->core()->hasSubject()) {
        $subject = Engine_Api::_()->core()->getSubject();
        $subject_type = $subject->getType();
        $subject_id = $subject->getIdentity();

        $video->parent_type = $subject_type;
        $video->parent_id = $subject_id;
      }
      $video->search = 1;
      $video->save();

      if (!($video instanceof Core_Model_Item_Abstract) || !$video->getIdentity()) {
        return;
      }
      return $video;
    } else if (isset($data['photo_id']) && !empty(($data['photo_id']))) {
      if (isset($data['type']) && $data['type'] == 'photo') {
        $viewer = Engine_Api::_()->user()->getViewer();
        $data['album_type'] = $album_type = @$data['album_type'] ? @$data['album_type'] : 'wall';
        $album = Engine_Api::_()->getDbtable('albums', 'album')->getSpecialAlbum($viewer, $album_type);
      }

      $photos = array();
      $photo = null;
      foreach (explode(',', $data['photo_id']) as $photoId) {
        $photo = Engine_Api::_()->getItem('album_photo', $photoId);
        if (!($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity()) {
          continue;
        }
        if ($data['album_type'] == 'wall') {
          $photo->album_id = $album->album_id;
          $photo->save();
        }
        $photos[] = $photo;
      }

      if (engine_count($photos) != 1) {
        return $photos;
      }
      return $photo;
    }
  }
}