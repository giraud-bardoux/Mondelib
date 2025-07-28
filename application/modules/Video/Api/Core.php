<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 10212 2014-05-13 17:34:39Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Api_Core extends Core_Api_Abstract
{
  public function getCategories()
  {
    $table = Engine_Api::_()->getDbTable('categories', 'video');
    $select = $table->select()
      ->from($table->info('name'))
      ->where('subcat_id = ?', 0)
      ->where('subsubcat_id = ?', 0)
      ->order('order DESC');
    return $table->fetchAll($select);
  }

  public function getCategory($category_id)
  {
    return Engine_Api::_()->getDbtable('categories', 'video')->find($category_id)->current();
  }

  // handle video upload
  public function createVideo($params, $file, $values)
  {
    if ($file instanceof Storage_Model_File) {
      $params['file_id'] = $file->getIdentity();
    } else {
      // create video item
      $video = Engine_Api::_()->getDbtable('videos', 'video')->createRow();
      $file_ext = pathinfo($file['name']);
      $file_ext = $file_ext['extension'];
      $video->code = $file_ext;
      $video->save();

      // Store video in temporary storage object for ffmpeg to handle
      $storage = Engine_Api::_()->getItemTable('storage_file');
      $storageObject = $storage->createFile($file, array(
        'parent_id' => $video->getIdentity(),
        'parent_type' => $video->getType(),
        'user_id' => $video->owner_id,
      )
      );

      // Remove temporary file
      @unlink($file['tmp_name']);

      $video->file_id = $storageObject->file_id;
      $video->save();

      // Add to jobs
      Engine_Api::_()->getDbtable('jobs', 'core')->addJob('video_encode', array(
        'video_id' => $video->getIdentity(),
        'type' => 'mp4',
      )
      );
    }

    return $video;
  }

  public function deleteVideo($video)
  {
    // delete video ratings
    Engine_Api::_()->getDbTable('ratings', 'core')->delete(array('resource_id = ?' => $video->getIdentity(), 'resource_type = ?' => $video->getType()));

    // check to make sure the video did not fail, if it did we wont have files to remove
    if ($video->status == 1) {
      // delete storage files (video file and thumb)
      if ($video->type == 3)
        Engine_Api::_()->getItem('storage_file', $video->file_id)->remove();
      if ($video->photo_id)
        Engine_Api::_()->getItem('storage_file', $video->photo_id)->remove();
    }

    // Check activity actions
    $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
    $actions = $attachDB->fetchAll($attachDB->select()->where('type = ?', $video->getType())->where('id = ?', $video->getIdentity()));
    $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');

    foreach ($actions as $action) {
      $action_id = $action->action_id;
      $attachDB->delete(array('type = ?' => $video->getType(), 'id = ?' => $video->getIdentity()));

      $action = $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
      $action->delete();
    }

    // delete activity feed and its comments/likes
    $item = Engine_Api::_()->getItem('video', $video->video_id);
    if ($item) {
      $item->delete();
    }
  }
  public function createvideoondemand($file_id, $video_id)
  {
    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
    $storage = Engine_Api::_()->getItem('storage_file', $file_id);

    if ($storage->service_id == 2) {
      $servicesTable = Engine_Api::_()->getDbtable('services', 'storage');
      $result = $servicesTable->select()
        ->from($servicesTable->info('name'), 'config')
        ->where('service_id = ?', $storage->service_id)
        ->limit(1)
        ->query()
        ->fetchColumn();
      $serviceResults = Zend_Json_Decoder::decode($result);
      if ($serviceResults['baseUrl']) {
        $path = 'http://' . $serviceResults['baseUrl'] . '/' . $storage->storage_path;
      } else {
        $path = 'http://' . $serviceResults['bucket'] . '.s3.amazonaws.com/' . $storage->storage_path;
      }
    } else {
      //Song file name and path
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $storage->storage_path;
    }

    $videoformate = array('mp4', 'avi', 'mov', 'flv', 'wmv');
    $parts = explode('.', $path);
    $last = array_pop($parts);
    $parts = array(implode('.', $parts), $last);
    $ffmpeg = trim(shell_exec('type -P ' . $ffmpeg_path));
    $video = Engine_Api::_()->getItem('video', $video_id);

    if (isset($parts[0]) && isset($parts[1]) && in_array($parts[1], $videoformate) && !empty($ffmpeg)) {
      $height = $storage->height; //shell_exec($ffmpeg_path." -i ".$path." 2>&1 | grep Video: | grep -Po '\d{3,5}x\d{3,5}' | cut -d'x' -f1");
      $width = $storage->height; //shell_exec($ffmpeg_path." -i ".$path." 2>&1 | grep Video: | grep -Po '\d{3,5}x\d{3,5}' | cut -d'x' -f2");

      $tempPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . md5($path);

      if(!file_exists($parts[0]."-240.".$parts[1])  && $width>240 && $height>240)
      {
        $tempFilePath = $tempPath."-240.".$parts[1];
        $cmd = $ffmpeg_path." -i ".$path." -vf scale=240:-2 ".$tempPath."-240.".$parts[1]."";
        shell_exec($cmd);
        $filer240 = $this->createFile($video,$tempFilePath,$parts[1]);
        if($filer240){
          $video->r240 = $filer240->file_id;
          $video->save();
        }
      }

      if (!file_exists($parts[0] . "-360." . $parts[1]) && $width > 360 && $height > 360) {
        $tempFilePath = $tempPath . "-360." . $parts[1];
        $cmd = $ffmpeg_path . " -i " . $path . " -vf scale=360:-2 " . $tempPath . "-360." . $parts[1] . "";
        shell_exec($cmd);
        $filer360 = $this->createFile($video, $tempFilePath, $parts[1]);
        if ($filer360) {
          $video->r360 = $filer360->file_id;
          $video->save();
        }
      }

      if(!file_exists($parts[0]."-480.".$parts[1]) && $width>480 && $height>480 )
      {
        $tempFilePath = $tempPath."-480.".$parts[1];
        $cmd = $ffmpeg_path." -i ".$path." -vf scale=480:-2 ".$tempPath."-480.".$parts[1]."";
        shell_exec($cmd);
        $filer480 = $this->createFile($video,$tempFilePath,$parts[1]);
        if($filer480){
          $video->r480 = $filer480->file_id;
          $video->save();
        }
      }

      if (!file_exists($parts[0] . "-720." . $parts[1]) && $width > 720 && $height > 720) {
        $tempFilePath = $tempPath . "-720." . $parts[1];
        $cmd = $ffmpeg_path . " -i " . $path . " -vf scale=720:-2 " . $tempPath . "-720." . $parts[1] . "";
        shell_exec($cmd);
        $filer720 = $this->createFile($video, $tempFilePath, $parts[1]);
        if ($filer720) {
          $video->r720 = $filer720->file_id;
          $video->save();
        }
      }

      if(!file_exists($parts[0]."-1080.".$parts[1]) && $width>1080 && $height>1080)
      {
        $tempFilePath = $tempPath."-1080.".$parts[1];
        $cmd = $ffmpeg_path." -i ".$path." -vf scale=1080:-2 ".$tempPath."-1080.".$parts[1]."";
        shell_exec($cmd);
        $filer1080 = $this->createFile($video,$tempFilePath,$parts[1]);
        if($filer1080){
          $video->r1080 = $filer1080->file_id;
          $video->save();
        }
      }
    }
  }
  public function returncurrentvideo($video_id, $url)
  {
    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
    $ffmpeg = trim(shell_exec('type -P ' . $ffmpeg_path));

    if (!empty($ffmpeg) && Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_videoondemad', 0)) {
      $video = Engine_Api::_()->getItem('video', $video_id);
      $storage = Engine_Api::_()->getItem('storage_file', $video->file_id);

      if ($storage->service_id == 2) {
        $servicesTable = Engine_Api::_()->getDbtable('services', 'storage');
        $result = $servicesTable->select()
          ->from($servicesTable->info('name'), 'config')
          ->where('service_id = ?', $storage->service_id)
          ->limit(1)
          ->query()
          ->fetchColumn();
        $serviceResults = Zend_Json_Decoder::decode($result);
        if ($serviceResults['baseUrl']) {
          $path = 'http://' . $serviceResults['baseUrl'] . '/' . $storage->storage_path;
        } else {
          $path = 'http://' . $serviceResults['bucket'] . '.s3.amazonaws.com/' . $storage->storage_path;
        }
      } else {
        //Song file name and path
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $storage->storage_path;
      }

      $parts = explode('.', $path);
      $last = array_pop($parts);
      $parts = array(implode('.', $parts), $last);
      $returnurl = '';
      $urlparts = explode('.', $url);
      $urllast = array_pop($urlparts);
      $urlparts = array(implode('.', $urlparts), $urllast);
      $parts = array(implode('.', $parts), $last);

      if($video->r240){  
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($video->r240, null);
        if($file){
          $returnurl.=' <source src="'.$file->map().'" type="video/'.$parts[1].'" size="240">'; 
        }
      }
      
      if($video->r360){  
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($video->r360, null);
        if($file){
          $returnurl.=' <source src="'.$file->map().'" type="video/'.$parts[1].'" size="360">'; 
        }
      }
      if($video->r480){ 
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($video->r480, null);
        if($file){
          $returnurl.=' <source src="'.$file->map().'" type="video/'.$parts[1].'" size="480">'; 
        }
      }
      if($video->r720){ 
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($video->r720, null);
        if($file){
          $returnurl.=' <source src="'.$file->map().'" type="video/'.$parts[1].'" size="720">'; 
        }
      }
      if($video->r1080){ 
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($video->r1080, null);
        if($file){
          $returnurl.=' <source src="'.$file->map().'" type="video/'.$parts[1].'" size="1080">'; 
        }
      }

      if (empty($returnurl)) {
        $returnurl .= ' <source src="' . $url . '" type="video/' . $parts[1] . '" size="480">';
      }
      return $returnurl;
    } else {
      $urlparts = explode('.', $url);
      $urllast = array_pop($urlparts);
      $urlparts = array(implode('.', $urlparts), $urllast);
      return '<source src="' . $url . '" type="video/' . $urlparts[1] . '" size="480">';
    }
  }
  public function createFile($video, $file)
  {
    try {
      $FileRow = Engine_Api::_()->storage()->create($file, array(
        'parent_type' => $video->getType(),
        'parent_id' => $video->getIdentity(),
        'user_id' => $video->owner_id,
        'mime_major' => 'video',
        'mime_minor' => 'mp4',
      )
      );
      @unlink($file);
      return $FileRow;
    } catch (Exception $e) {

    }
  }
}

