<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AlbumController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_AlbumController extends Core_Controller_Action_Standard {

  public function editPhotoAction(){
    $this->view->photo_id = $photo_id = $this->_getParam('photo_id');
		$this->view->photo = Engine_Api::_()->getItem('photo', $photo_id);
  }
  
  //edit photo details from light function.
  public function saveInformationAction() {
    $photo_id = $this->_getParam('photo_id');
    $title = $this->_getParam('title', null);
    $description = $this->_getParam('description', null);
    $photoTable = Engine_Api::_()->getItemTable('photo');
    $photoTable->update(array('title' => $title, 'description' => $description), array('photo_id = ?' => $photo_id));
    echo json_encode(array('status'=>"true"));die;
  }
  
  //album constructor function
  public function init() {
    if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid())
      return;
    if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
            null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id))) {
      Engine_Api::_()->core()->setSubject($photo);
    } else if (0 !== ($album_id = (int) $this->_getParam('album_id')) &&
            null !== ($album = Engine_Api::_()->getItem('album', $album_id))) {
      Engine_Api::_()->core()->setSubject($album);
    }

  }

  //album photo upload function
  public function composeUploadAction() {
    if (!Engine_Api::_()->user()->getViewer()->getIdentity()) {
      $this->_redirect('login');
      return;
    }
    
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
      return;
    }
    if (empty($_FILES['Filedata']) && !$this->_getParam("isUrl")) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Get album
    $viewer = Engine_Api::_()->user()->getViewer();
    $table = Engine_Api::_()->getItemTable('album');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $type = $this->_getParam('type', 'wall');
      if (empty($type))
        $type = 'wall';
      $album = $table->getSpecialAlbum($viewer, $type);
      $photoTable = Engine_Api::_()->getItemTable('album_photo');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
          'owner_type' => 'user',
          'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
      ));
      $photo->save();
      $isUrl = $this->_getParam('isUrl');
      $photo->setPhoto($isUrl ? $_POST['Filedata'] : $_FILES['Filedata'],$isUrl);
      if ($type == 'message') {
        $photo->title = Zend_Registry::get('Zend_Translate')->_('Attached Image');
      }
      $photo->order = $photo->photo_id;
      $photo->album_id = $album->album_id;
      $photo->save();
      if (!$album->photo_id) {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }
      if ($type != 'message') {
        // Authorizations
        $auth = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($photo, 'everyone', 'view', true);
        $auth->setAllowed($photo, 'everyone', 'comment', true);
      }
      $db->commit();
      $this->view->status = true;
      $this->view->photo_id = $photo->photo_id;
      $this->view->album_id = $album->album_id;
			$this->view->src = $this->view->url = $photo->getPhotoUrl('thumb.normalmain');
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected photos have been successfully saved.');
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
      $this->view->status = false;
    }
    if($this->_getParam('isactivity',false)){
      echo json_encode(array('src'=>$this->view->src ,'photo_id'=>$this->view->photo_id,'status'=>$this->view->status));die;

    }
  }
  
  //ACTION FOR PHOTO DELETE
  public function removeAction() {
			if(empty($_GET['photo_id']))
				die('error');
      //GET PHOTO ID AND ITEM
			$photo_id = (int) $this->_getParam('photo_id');
	    $photo = Engine_Api::_()->getItem('photo', $photo_id);
      $db = Engine_Api::_()->getItemTable('album_photo')->getAdapter();
      $db->beginTransaction();
      try {
        $photo->delete();
        $db->commit();
        echo true;die;
      } catch (Exception $e) {
        $db->rollBack();
      }
      echo false;die;
  }
}
