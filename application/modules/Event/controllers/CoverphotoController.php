<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: CoverphotoController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_CoverphotoController extends Core_Controller_Action_Standard {

  public function getCoverPhotoAction() {
    $this->view->event = $event = Engine_Api::_()->getItem('event', $this->_getParam("event_id"));
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->user = $user = Engine_Api::_()->getItem('user', $event->user_id);
    $this->view->photoType = $photoType = $this->_getParam("photoType", "cover");
    $this->view->can_edit = $can_edit = (int) $event->authorization()->isAllowed($viewer, 'edit') &&
      Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'coverphotoupload');

    $this->view->photo = $photo = Engine_Api::_()->getItem('event_photo', $event->coverphoto);
    $this->view->topPosition = 0;
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = $level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $event->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }
    if ($photo && empty($uploadDefaultCover)) {

      $album = Engine_Api::_()->getItem('event_album', $photo->album_id);
      if ($album && $album->coverphotoparams) {
        $coverphotoparams = is_array($album->coverphotoparams) ? $album->coverphotoparams :
          Zend_Json_Decoder::decode($album->coverphotoparams);
        $this->view->topPosition = $coverphotoparams['top'];
      }
    } else {
      $coverphotoparams = Zend_Json_Decoder::decode(Engine_Api::_()->getApi("settings", "core")->getSetting(
        "eventcoverphoto.preview.level.params.$user->level_id",
        Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0))
      ));
      $this->view->topPosition = $coverphotoparams['top'];
    }
  }

  public function getMainPhotoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->event = $event = Engine_Api::_()->getItem('event', $this->_getParam("event_id"));
    $this->view->uploadDefaultCover = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $event->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
    }
    $this->view->auth = $event->authorization()->isAllowed($viewer, 'view');
    $this->view->eventNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('event_profile');
    $this->view->can_edit = $can_edit = $event->authorization()->isAllowed($viewer, 'edit');
    $this->view->level_id = $level_id = $this->_getParam("level_id", $event->getOwner()->level_id);
  }

  public function resetCoverPhotoPositionAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->view->user = $viewer = Engine_Api::_()->user()->getViewer();
    $event_id = $this->_getParam("event_id");
    $this->view->event = $event = Engine_Api::_()->getItem('event', $event_id);
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $event->getOwner()->isSelf($viewer)) {
        $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
        $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }
    if (!$uploadDefaultCover) {
      $this->view->can_edit = $can_edit = (int) $event->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'coverphotoupload');

      if (!$can_edit) {
        return;
      }
      $this->view->photo = $photo = Engine_Api::_()->getItem('event_photo', $event->coverphoto);
      if ($photo && empty($uploadDefaultCover)) {
        $album = Engine_Api::_()->getItem('event_album', $photo->album_id);

        $album->coverphotoparams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => '0', 'left' => 0)));
        $album->save();
      }
    } else {
      $defaultCover = $this->_getParam('defaultCover', 0);
      $coreSettingsApi = Engine_Api::_()->getApi("settings", "core");
      $postionParams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => '0', 'left' => 0)));
      if (!empty($defaultCover)) {
        $level_ids = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();
        $public_level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        foreach ($level_ids as $key => $value) {
          if ($public_level_id == $key) {
            continue;
          }

          $coreSettingsApi->setSetting("eventcoverphoto.preview.level.params.$key", $postionParams);
        }
      } else {
          $postionParams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => '0', 'left' => 0)));
          $coreSettingsApi->setSetting("eventcoverphoto.preview.level.params.$level_id", $postionParams);
      }
    }
    die;
  }

  public function chooseFromAlbumsAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->_helper->layout->setLayout('default-simple');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $this->view->event = $event = Engine_Api::_()->getItem('event', $this->_getParam("event_id"));
    if ($photoType == 'cover') {
      $this->view->can_edit = $can_edit = (int) $event->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'coverphotoupload');
      if (!$can_edit) {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }

    $this->view->recentAdded = $recentAdded = $this->_getParam("recent", false);
    $this->view->album_id = $album_id = $this->_getParam("album_id");
    $paginator = '';
    if ($album_id) {
      $this->view->album = $album = Engine_Api::_()->getItem('event_album', $album_id);
      $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    } else {
      $album = $event->getSingletonAlbum();
      $this->view->paginator = $paginator = $album->getCollectiblesPaginator();
    }
  }

  public function uploadCoverPhotoAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->_helper->layout->setLayout('default-simple');
    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded.');
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $this->view->event = $event = Engine_Api::_()->getItem('event', $this->_getParam('event_id'));
    $this->view->level_id = $level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $event->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }

    if ($photoType == 'cover') {
      if (!$uploadDefaultCover) {
        $this->view->can_edit = $can_edit = (int) $event->authorization()->isAllowed($viewer, 'edit') &&
          Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'coverphotoupload');

        if (!$can_edit) {
          return $this->_forward('requireauth', 'error', 'core');
        }
        $this->view->form = $form = new Event_Form_CoverPhoto_Cover();
      } else {
        $this->view->form = $form = new Event_Form_CoverPhoto_Cover();
      }
    } else {
      $this->view->form = $form = new Event_Form_CoverPhoto_Cover();
      $form->setTitle('Upload Event Profile Picture');
      $form->setAttrib('name', 'Upload a Event Profile Picture');
      $form->Filedata->setLabel('Choose a event profile picture.');
    }

    if (empty($uploadDefaultCover)) {
      $file = '';
      $alreadyHasCover = false;
      $photo_id = $this->_getParam('photo_id');
      if ($photo_id) {
        $photo = Engine_Api::_()->getItem('event_photo', $photo_id);
        $album = Engine_Api::_()->getItem('event_album', $photo->album_id);

        if ($album && ($album->type == 'cover' || $album->type == 'profile')) {
          $alreadyHasCover = true;
        }
        if ($photo->file_id && !$alreadyHasCover) {
          $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photo->file_id);
        }
      }

      if (empty($photo_id) || empty($photo)) {
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
          return;
        }
      }

      if ($form->Filedata->getValue() !== null || $photo || ($alreadyHasCover && $file)) {

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
          $album = $event->getSingletonAlbum();
          $tablePhoto = Engine_Api::_()->getDbtable('photos', 'event');
          if(empty($photo_id)) {
            if (!$alreadyHasCover) {
              $photo = $tablePhoto->createRow();
              $photo->setFromArray(array(
                'user_id' => $viewer->getIdentity(),
                'event_id' => $event->getIdentity(),
                'album_id' => $album->getIdentity(),
                'collection_id' => $album->getIdentity(),
              ));
              $photo->save();
              if ($file) {
                if ($photoType == 'cover') {
                  $this->setCoverPhoto($file, $photo, $uploadDefaultCover);
                } else {
                  $this->setMainPhoto($file, $photo);
                }
              } else {
                if ($photoType == 'cover') {
                  $this->setCoverPhoto($form->Filedata, $photo, $uploadDefaultCover);
                } else {
                  $this->setMainPhoto($form->Filedata, $photo);
                }
              }
              $photo->album_id = $album->album_id;
              $photo->save();
            }
          }

          $album->coverphotoparams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => 0, 'left' => 0)));
          $album->save();
          if (!$album->photo_id) {
            $album->photo_id = $photo->photo_id;
            $album->save();
          }
          if ($photoType == 'cover') {
            $event->coverphoto = $photo->photo_id;
            $album->type = 'cover';
          } else {
            $event->photo_id = $photo->file_id;
            $album->type = 'profile';
          }
          $album->save();
          $event->save();

          $viewer = Engine_Api::_()->user()->getViewer();
          $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
          if ($photoType == 'cover') {
            $action = $activityApi->addActivity($viewer, $event, 'event_cover_photo_update');
            if ($action) {
              Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
            }
          }

          $this->view->status = true;
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          $this->view->status = false;
          $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
          return;
        }
      }
    } else {
      if (!$form->isValid($this->getRequest()->getPost())) {
        return;
      }
      if ($form->Filedata->getValue() !== null) {
        $values = $form->getValues();
        $this->setCoverPhoto($form->Filedata, null, $uploadDefaultCover, $level_id);
        $this->view->status = true;
      }
    }
  }

  public function removeCoverPhotoAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = $level_id = 0;
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->getItem('event', $this->_getParam('event_id'));
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $event->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }

    if ($photoType == 'cover' && empty($uploadDefaultCover)) {
      $this->view->can_edit = $can_edit = (int) $event->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('event', $viewer, 'coverphotoupload');
      if (!$can_edit) {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }

    $coreSettingsApi = Engine_Api::_()->getApi("settings", "core");
    $preview_id = $coreSettingsApi->getSetting("eventcoverphoto.preview.level.id.$level_id");
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if ($photoType == 'cover') {
      if (empty($uploadDefaultCover)) {
        $photo = Engine_Api::_()->getItem('event_photo', $event->coverphoto);
        $album = Engine_Api::_()->getItem('event_album', $photo->album_id);
        $event->coverphoto = null;
        $album->coverphotoparams = Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0));
        $album->save();
      } else {
        $coreSettingsApi->setSetting("eventcoverphoto.preview.level.id.$level_id", 0);
        $postionParams = Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0));
        $coreSettingsApi->setSetting("eventcoverphoto.preview.level.params.$level_id", $postionParams);
        $file = Engine_Api::_()->getItem('storage_file', $preview_id);
        if ($file) {
          $file->delete();
        }
      }
    } else {
      $event->photo_id = 0;
    }
    $event->save();

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
    ));
  }

  private function setCoverPhoto($photo, $photoObject, $uploadDefaultCover, $level_id = null)
  {

    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');

    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(1600, 1600)
      ->write($mainPath)
      ->destroy();

    $coverPath = $path . DIRECTORY_SEPARATOR . $base . '_c.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(1500, 1500)
      ->write($coverPath)
      ->destroy();

    if (empty($uploadDefaultCover)) {
      $params = array(
        'parent_type' => $photoObject->getType(),
        'parent_id' => $photoObject->getIdentity(),
        'user_id' => $photoObject->user_id,
        'name' => basename($fileName),
      );

      try {
        $iMain = $filesTable->createFile($mainPath, $params);
        $iCover = $filesTable->createFile($coverPath, $params);
        $iMain->bridge($iCover, 'thumb.cover');
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($coverPath);
      }
      @unlink($mainPath);
      @unlink($coverPath);
      $photoObject->modified_date = date('Y-m-d H:i:s');
      $photoObject->file_id = $iMain->file_id;
      $photoObject->save();
      if (!empty($tmpRow)) {
        $tmpRow->delete();
      }
      return $photoObject;
    } else {
      try {
        $iMain = $filesTable->createSystemFile($mainPath);
        $iCover = $filesTable->createSystemFile($coverPath);
        $iMain->bridge($iCover, 'thumb.cover');
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($coverPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      Engine_Api::_()->getApi("settings", "core")->setSetting("eventcoverphoto.preview.level.id.$level_id", $iMain->file_id);
    }
  }

  private function setMainPhoto($photo, $photoObject) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => $photoObject->getType(),
      'parent_id' => $photoObject->getIdentity(),
      'user_id' => $photoObject->user_id,
      'name' => basename($fileName),
    );

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');
    
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($mainPath)
      ->destroy();
      
    // Resize image (icon)
    $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($squarePath)
      ->destroy();
        // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      $iSquare = $filesTable->createFile($squarePath, $params);
      $iMain->bridge($iSquare, 'thumb.icon');
    } catch (Exception $e) {
			// Remove temp files
      @unlink($mainPath);
      @unlink($squarePath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Album_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
		@unlink($mainPath);
		@unlink($squarePath);
    $photoObject->modified_date = date('Y-m-d H:i:s');
    $photoObject->file_id = $iMain->file_id;
    $photoObject->save();
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $photoObject;
  }
}
