<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Composer.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Plugin_Composer extends Core_Plugin_Abstract
{
  public function onAttachPhoto($data)
  {
    if( !is_array($data) || empty($data['photo_id']) ) {
      return;
    }
    
    if(isset($data['type']) && $data['type'] == 'photo') {
      $viewer = Engine_Api::_()->user()->getViewer();
      $album_type = $data['album_type'] ? $data['album_type'] : $data['type'];
      $album = Engine_Api::_()->getDbtable('albums', 'album')->getSpecialAlbum($viewer, $album_type);
    }

    $photos = array();
    $photo = null;
    foreach( explode(',', $data['photo_id']) as $photoId ) {
      $photo = Engine_Api::_()->getItem('album_photo', $photoId);
      if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) {
        continue;
      }
      if($data['album_type'] == 'wall') {
        $photo->album_id = $album->album_id;
        $photo->save();
      }
      $photos[] = $photo;
    }

    if( engine_count($photos) !=1 ) {
      return $photos;
    }

//     if( !empty($data['actionBody']) && empty($photo->description) ) {
//       $photo->description = $data['actionBody'];
//       $photo->save();
//     }

    return $photo;
  }

  public function onActivityActionUpdateAfter($event)
  {
    $payload = $event->getPayload();
    $modifiedFields = $payload->getModifiedFieldsName();
    $attachment = $payload->getFirstAttachment();
    if( engine_in_array('body', $modifiedFields) && !empty($attachment) ) {
      $attachment = $payload->getFirstAttachment()->item;
      $attachmentType = $attachment->getType();
      $oldData = $payload->getCleanData();
      if( $attachmentType == 'album_photo' && $oldData['body'] == $attachment->description ) {
        $attachment->description = $payload['body'];
        $attachment->save();
      }
    }
  }

}
