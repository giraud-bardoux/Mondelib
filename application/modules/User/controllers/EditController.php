<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_EditController extends Core_Controller_Action_User
{
    public function init()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            // Can specifiy custom id
            $id = $this->_getParam('id', null);
            $subject = null;
            if (null === $id) {
                $subject = Engine_Api::_()->user()->getViewer();
                Engine_Api::_()->core()->setSubject($subject);
            } else {
                $subject = Engine_Api::_()->getItem('user', $id);
                Engine_Api::_()->core()->setSubject($subject);
            }
        }

        if (!empty($id)) {
            $params = array('id' => $id);
        } else {
            $params = array();
        }
        // Set up navigation
        $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('user_edit', array('params' => $params));

        // Set up require's
        $this->_helper->requireUser();
        $this->_helper->requireSubject('user');
        $this->_helper->requireAuth()->setAuthParams(
            null,
            null,
            'edit'
        );
    }

    public function profileAction()
    {
      $this->view->user = $user = Engine_Api::_()->core()->getSubject();
      $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
          
      // Element: profile_type
      $this->view->editProfileType = false;
      $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
      if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
        $profileTypeField = $topStructure[0]->getChild();
        $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['']);
        if($options['type'] == 'ProfileType') {
          unset($options['options']['multiOptions']['5']);
          unset($options['options']['multiOptions']['9']);
        }
        if( engine_count($options['options']['multiOptions']) > 1 ) { 
          $this->view->editProfileType = true;
        }
      }

      // General form w/o profile type
      $profileTypesArray = [];
      $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
      $changeUserProfileType = Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($user);
      $this->view->topLevelId = $topLevelId = 0;
      $this->view->topLevelValue = $topLevelValue = null;
      if (isset($aliasedFields['profile_type'])) {
        $aliasedFieldValue = $aliasedFields['profile_type']->getValue($user);
        $topLevelId = $aliasedFields['profile_type']->field_id;
        $topLevelValue = (is_object($aliasedFieldValue) ? $aliasedFieldValue->value : null);
        if (!$topLevelId || !$topLevelValue) {
          $topLevelId = null;
          $topLevelValue = null;
        }
        $this->view->topLevelId = $topLevelId;
        $this->view->topLevelValue = $topLevelValue;
      }

      if ($changeUserProfileType) {
        $profileTypesArray = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
          ->getMappedProfileTypeIds($user->level_id);
          
        $profileTypeValue = Engine_Api::_()->user()->getProfileFieldValue(array('user_id' => $user->getIdentity(), 'field_id' => 1));
        
        if (!empty($profileTypeValue)) {
          $this->view->topLevelId = $topLevelId = 1;
          $this->view->topLevelValue = $topLevelValue = $profileTypeValue; //$profileTypesArray[0]['profile_type_id'];
        }
      }

      $params = [
          'item' => Engine_Api::_()->core()->getSubject(),
          'topLevelId' => $topLevelId,
          'topLevelValue' => $topLevelValue,
          'hasPrivacy' => true,
          'privacyValues' => $this->getRequest()->getParam('privacy'),
          'ajaxUrl'=>Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'fields'),'user_general')
      ];
      if(!$this->getRequest()->isPost()){
          $params['enableAjaxLoad'] = true;
      } 
      // Get form
      $form = $this->view->form = new Fields_Form_Standard($params);
      $form->setAttrib('class', 'global_form form_submit_ajax');
      $form->populate($user->toArray());

      //Profile field auto populate work
      $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAliasId($user);
      $form->populate($aliasValues);

      if (!empty($profileTypeValue)) {
        $form->addElement('Hidden', '0_0_1', array(
          'value' => $profileTypeValue, //$profileTypesArray[0]['profile_type_id']
        ));
      }

      if (empty($topLevelValue) && $changeUserProfileType) {
        $profileTypes = Engine_Api::_()->getDbtable('options', 'authorization')->getAllProfileTypes();
        
        $profileTypeOptions = array('' => '');
        foreach ($profileTypes as $profileType) {
            if(in_array($profileType->option_id, array(5,9))) continue;
            $profileTypeOptions[$profileType->option_id] = $profileType->label;
        }
        $form->getElement('0_0_1')->setMultiOptions($profileTypeOptions);
      }
      
      // If not post or form not valid, return
      if( !$this->getRequest()->isPost() ) {
          return;
      }

      if( !$form->isValid($this->getRequest()->getPost()) ) {
        $validateFields = Engine_Api::_()->core()->validateFormFields($form);
        if(is_countable($validateFields) && engine_count($validateFields)){
          echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
        }
      }

      if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

        $values = $form->getValues();
        $form->saveValues();

        // Update display name
        $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
        $user->setDisplayName($aliasValues);

        //Save values in users table
        if( is_array($aliasValues) )
        {
          // Has only first
          if( !empty($aliasValues['first_name']) )
          {
            $user->firstname = $aliasValues['first_name'];
            $user->save();
          }
          // Has only last
          if( !empty($aliasValues['last_name']) )
          {
            $user->lastname = $aliasValues['last_name'];
            $user->save();
          } 
          //has only birthdate
          if( !empty($aliasValues['gender']) )
          {
            $gender = Engine_Api::_()->user()->getOptionIdValue(array('option_id' => $aliasValues['gender']));
            $user->gender = strtolower($gender);
            $user->save();
          }
          //has only birthdate
          if( !empty($aliasValues['birthdate']) )
          {
            $user->dob = $aliasValues['birthdate'];
            $user->save();
          }
        }
        
        $user->modified_date = date('Y-m-d H:i:s');
        $user->save();

        // update networks
        Engine_Api::_()->network()->recalculate($user);
        
        //Save General Information
        // $user->firstname = $_POST['firstname'];
        // $user->lastname = $_POST['lastname'];
        // $user->dob = $_POST['dob'] ? $_POST['dob']['year'].'-'.$_POST['dob']['month'].'-'.$_POST['dob']['day'] : NULL;
        // $user->gender = $_POST['gender'] ? $_POST['gender'] : NULL;
        // $user->save();
        // Update display name
        // $user->setDisplayName(array('first_name' => $_POST['firstname'], 'last_name' => $_POST['lastname']));
        // $user->save();
        
        $form->populate($user->toArray());
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')));die;
      }
    }


    public function photoAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        // Get form
        $this->view->form = $form = new User_Form_Edit_Photo();
        
        if (empty($user->photo_id)) {
            $form->removeElement('remove');
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Uploading a new photo
        if ($form->Filedata->getValue() !== null) {
            $db = $user->getTable()->getAdapter();
            $db->beginTransaction();

            try {
							// if album not enable remove old photo.
								// if user photo_id column is empty.
								if(!empty($user['photo_id']) && $user->photo_id != $user->avatar_id){
									$file = Engine_Api::_()->getItem('storage_file', $user['photo_id']);
									if($file) {
                    $getParentChilds = $file->getChildren($file->getIdentity());
                    foreach ($getParentChilds as $child) {
                      // remove child file.
                      $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $child['storage_path']);
                      // remove child directory.
                      $childPhotoDir = $this->getDirectoryPath($child['storage_path']);
                      $this->removeDir($childPhotoDir);
                      // remove child row from db.
                      $child->remove();
                    }
                    // remove parent file.
                    $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $file['storage_path']);
                    // remove directory.
                    $parentPhotoDir = $this->getDirectoryPath($file['storage_path']);
                    $this->removeDir($parentPhotoDir);
                    if ($file) {
                      // remove parent form db.
                      $file->remove();
                    }
									}
								}

							$form->coordinates->setValue('');  //reset coordinates value
							$fileElement = $form->Filedata;

							$user->setPhoto($fileElement);
							//if there are already choose avatar then avatar id 0
							$user->avatar_id = 0;
							$user->save();

							$iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);

							// Insert activity
							$action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'profile_photo_update');

							// Hooks to enable albums to work
							if ($action) {
								$event = Engine_Hooks_Dispatcher::_()
										->callEvent('onUserPhotoUpload', array(
												'user' => $user,
												'file' => $iMain,
										));

								$attachment = $event->getResponse();
								if (!$attachment) {
										$attachment = $iMain;
								}

								// We have to attach the user himself w/o album plugin
								Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
							}

							$db->commit();

              return $this->_helper->redirector->gotoRoute(array('module' => 'user', 'controller' => 'edit', 'action' => 'photo', 'isURLFullLoad' => true), 'user_extended', true);
            } catch (Exception $e) {
                return $this->exceptionWrapper($e, $form, $db);
            }
        }

        // Resizing a photo
        elseif ($form->getValue('coordinates') !== '') {
            $storage = Engine_Api::_()->storage();

            $iProfile = $storage->get($user->photo_id);
            if (!$iProfile) {
                return;   // don't do anything
            }
            $iSquare = $storage->get($user->photo_id, 'thumb.icon');

            // Read into tmp file
            $pName = $iProfile->getStorageService()->temporary($iProfile);
            $iName = dirname($pName) . '/nis_' . basename($pName);

            list($x, $y, $w, $h) = explode(':', $form->getValue('coordinates'));

            $image = Engine_Image::factory();
            $image->open($pName)
                ->resample($x+.1, $y+.1, $w-.1, $h-.1, 48, 48)
                ->write($iName)
                ->destroy();

            $iSquare->store($iName);

            $image = Engine_Image::factory();
            $image->open($pName)
                ->resample($x+.1, $y+.1, $w-.1, $h-.1, 440, 440)
                ->write($pName)
                ->destroy();
            $iProfile->store($pName);
            // Remove temp files
            @unlink($iName);
            @unlink($pName);

            return $this->_helper->redirector->gotoRoute(array('module' => 'user', 'controller' => 'edit', 'action' => 'photo', 'isURLFullLoad' => true), 'user_extended', true);
        }
        else {
            $storage = Engine_Api::_()->storage();

            $iProfile = $storage->get($user->photo_id);
            if (!$iProfile) {
                return;   // don't do anything
            }

            $pName = $iProfile->getStorageService()->temporary($iProfile);
            $image = Engine_Image::factory();
            $image->open($pName);
            $profileImgRatio = $image->width / $image->height ;

            if ($profileImgRatio == 1) {
                return;
            }

            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;

            $image->resample($x, $y, $size, $size, 400, 400)
                ->write($pName)
                ->destroy();
            $iProfile->store($pName);

            @unlink($pName);

            return $this->_helper->redirector->gotoRoute(array('module' => 'user', 'controller' => 'edit', 'action' => 'photo', 'isURLFullLoad' => true), 'user_extended', true);
        }
    }

    protected function getDirectoryPath($storage_path){
        return APPLICATION_PATH . DIRECTORY_SEPARATOR . str_replace(basename($storage_path),"",$storage_path);
    }

    protected function removeDir($dirPath){
        if(@is_dir($dirPath)){
           @rmdir($dirPath);
        }
    }

    protected function unlinkFile($filePath){
        @unlink($filePath);
    }

	function whenRemove($user,$deleteType = null){

    if(!empty($user[$deleteType])){
      $file = Engine_Api::_()->getItem('storage_file', $user[$deleteType]);
      $getParentChilds = $file->getChildren($file->getIdentity());
      foreach ($getParentChilds as $child) {
        // remove child file.
        $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $child['storage_path']);
        // remove child directory.
        $childPhotoDir = $this->getDirectoryPath($child['storage_path']);
        $this->removeDir($childPhotoDir);
        // remove child row from db.
        $child->remove();
      }
      // remove parent file.
      $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $file['storage_path']);
      // remove directory.
      $parentPhotoDir = $this->getDirectoryPath($file['storage_path']);
      $this->removeDir($parentPhotoDir);
      if ($file) {
        // remove parent form db.
        $file->remove();
      }
    }
  }

  public function removePhotoAction() {
    // Get form
    $this->view->form = $form = new User_Form_Edit_RemovePhoto();

    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
        return;
    }

    $user = Engine_Api::_()->core()->getSubject();
    if (empty($user->avatar_id)) {
      $this->whenRemove($user,"photo_id");
    } else {
      $user->avatar_id = 0;
    }
    $user->photo_id = 0;
    $user->save();

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.');

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.'))
    ));
  }

    public function externalPhotoAction()
    {
        if (!$this->_helper->requireSubject()->isValid()) {
            return;
        }
        $user = Engine_Api::_()->core()->getSubject();

        // Get photo
        $photo = Engine_Api::_()->getItemByGuid($this->_getParam('photo'));
        if (!$photo || !($photo instanceof Core_Model_Item_Abstract) || empty($photo->photo_id)) {
            $this->_forward('requiresubject', 'error', 'core');
            return;
        }

        if (!$photo->authorization()->isAllowed(null, 'view')) {
            $this->_forward('requireauth', 'error', 'core');
            return;
        }


        // Make form
        $this->view->form = $form = new User_Form_Edit_ExternalPhoto();
        $this->view->photo = $photo;

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $db = $user->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            // Get the owner of the photo
            $photoOwnerId = null;
            if (isset($photo->user_id)) {
                $photoOwnerId = $photo->user_id;
            } elseif (isset($photo->owner_id) && (!isset($photo->owner_type) || $photo->owner_type == 'user')) {
                $photoOwnerId = $photo->owner_id;
            }

            // if it is from your own profile album do not make copies of the image
            if ($photo instanceof Album_Model_Photo &&
                ($photoParent = $photo->getParent()) instanceof Album_Model_Album &&
                $photoParent->owner_id == $photoOwnerId &&
                $photoParent->type == 'profile') {

                // Set it
                $user->photo_id = $photo->file_id;
                $user->save();

                // Insert activity
                // @todo maybe it should read "changed their profile photo" ?
                $action = Engine_Api::_()->getDbtable('actions', 'activity')
                    ->addActivity($user, $user, 'profile_photo_update',
                        '{item:$subject} changed their profile photo.');
                if ($action) {
                    // We have to attach the user himself w/o album plugin
                    Engine_Api::_()->getDbtable('actions', 'activity')
                        ->attachActivity($action, $photo);
                }
            }

            // Otherwise copy to the profile album
            else {
                $user->setPhoto($photo);

                // Insert activity
                $action = Engine_Api::_()->getDbtable('actions', 'activity')
                    ->addActivity($user, $user, 'profile_photo_update');

                // Hooks to enable albums to work
                $newStorageFile = Engine_Api::_()->getItem('storage_file', $user->photo_id);
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserPhotoUpload', array(
                        'user' => $user,
                        'file' => $newStorageFile,
                    ));

                $attachment = $event->getResponse();
                if (!$attachment) {
                    $attachment = $newStorageFile;
                }

                if ($action) {
                    // We have to attach the user himself w/o album plugin
                    Engine_Api::_()->getDbtable('actions', 'activity')
                        ->attachActivity($action, $attachment);
                }
            }

            $db->commit();
        }

            // Otherwise it's probably a problem with the database or the storage system (just throw it)
        catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Set as profile photo')),
            'smoothboxClose' => true,
            'parentRefresh' => true,
        ));
    }

    public function clearStatusAction()
    {
        $this->view->status = false;

        if ($this->getRequest()->isPost()) {
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer->status = '';
            $viewer->status_date = '00-00-0000';

            $viewer->save();

            $this->view->status = true;
        }
    }

    public function profilePhotosAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
            return $this->_helper->redirector->gotoRoute(['action' => 'manage'], 'album_general', true);
        }

        $fileTable = Engine_Api::_()->getDbtable('files', 'storage');
        $fileSelect = $fileTable->select()
            ->where("user_id = ?", $viewer->getIdentity())
            ->where("parent_type = ?", "user")
            ->where("parent_id = ?", $viewer->getIdentity())
            ->where('parent_file_id is NULL');
        $this->view->paginator = $paginator = Zend_Paginator::factory($fileSelect);
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    }

    public function deleteProfilePhotosAction()
    {
        $photoIds = (array) $this->_getParam('photo_ids');
        if (empty($photoIds)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fileTable = Engine_Api::_()->getDbtable('files', 'storage');
        $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
        $fileSelect = $fileTable->select()
            ->where("user_id = ?", $viewerId)
            ->where('file_id IN (?)', $photoIds)
            ->orWhere('parent_file_id IN (?)', $photoIds);
        $files = $fileTable->fetchAll($fileSelect);
        foreach($files as $file) {
            $file = Engine_Api::_()->getItem('storage_file', $file->file_id);
            // Delete attachments
            $attachmentSelect = $attachmentTable->select()
                ->where('type = ?', $file->getType())
                ->where('id = ?', $file->getIdentity())
                ;

            $attachmentActionIds = array();
            foreach( $attachmentTable->fetchAll($attachmentSelect) as $attachmentRow ) {
                $attachmentActionIds[] = $attachmentRow->action_id;
            }

            if( !empty($attachmentActionIds) ) {
                $attachmentTable->delete('action_id IN('.join(',', $attachmentActionIds).')');
                Engine_Api::_()->getDbtable('stream', 'activity')->delete('action_id IN('.join(',', $attachmentActionIds).')');
            }

            $file->delete();
        }

        $user = Engine_Api::_()->getItem('user', $viewerId);
        if (engine_in_array($user->photo_id, $photoIds)) {
            $user->photo_id = 0;
        }
        $user->save();

        $this->_helper->redirector->gotoRoute(array(
            'controller' => 'edit',
            'action' => 'profile-photos',
        ), 'user_extended', true);
    }
    
  public function editProfileTypeAction() {
    
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    
    $profileTypeValue = Engine_Api::_()->user()->getProfileFieldValue(array('user_id' => $user->getIdentity(), 'field_id' => 1));
    
    $this->view->form = $form = new User_Form_Edit_ProfileType();
    $form->profile_type->setValue($profileTypeValue);
    
    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    
    $values = $form->getValues();
    $profileTypeId = $values['profile_type'];
  
    if ($profileTypeValue == $profileTypeId) {
      $form->addError($this->view->translate("Please select different profile type."));
      return;
    }

    $mapLevelId = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')->getMappedLevelId($profileTypeId);
    
    $this->_helper->redirector->gotoRoute(array(
      'action' => 'update-member-profiletype',
      'controller' => 'edit',
      'id' => $user->getIdentity(),
      'profile_type_id' => $profileTypeId,
      'level_id' => $mapLevelId ? $mapLevelId : '',
    ), 'user_extended', false);
  }
  
  public function updateMemberProfiletypeAction() {

    $this->_helper->layout->setLayout('default-simple');
    if (!$this->getRequest()->isPost()) {
      $this->view->id = $id =  $this->_getParam('id', null);
      $this->view->profile_type_id = $profileTypeId =  $this->_getParam('profile_type_id', null);
      $this->view->member_level_id = $levelId =  $this->_getParam('level_id', null);
    }

    if ($this->getRequest()->isPost()) {
      if(!empty($_POST['profile_type_id'])) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $user_id = $_POST['id'];
        try {
          $db->query("DELETE FROM `engine4_user_fields_values` WHERE `engine4_user_fields_values`.`item_id` = '".$user_id."';");
          $db->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES ('".$user_id."', 1, 0, '".$_POST['profile_type_id']."', NULL);");
          $user = Engine_Api::_()->getItem('user', $user_id);
          $user->profile_type = $_POST['profile_type_id'];
          $user->save();
          if (Engine_Api::_()->authorization()->getPermission($user, 'user', 'editprotylevel') && isset($_POST['level_id']) && !empty($_POST['level_id'])) {
            $user->level_id = $_POST['level_id'];
            $user->save();
          }
        } catch (Exception $ex) {
          throw $ex;
        }
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh'=> true,
        'messages' => Array(Zend_Registry::get('Zend_Translate')->_('The profile type has been successfully edited.'))
      ));
    }
  }
  
  public function chooseAvatarAction() {

    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax', 0);
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');

    if(!$is_ajax) {
      // Prepare form
      $this->view->form = $form = new User_Form_Edit_Avatar();
      if(!empty($user->avatar_id)) {
        $form->setTitle("Edit Avatar");
        $form->avatar->setValue($user->photo_id);
      }
    }

    if($is_ajax) {
      try {
        $user = Engine_Api::_()->getItem('user', $_POST['id']);
        $user->photo_id = $_POST['avatar'];
        $user->avatar_id = $_POST['avatar'];
        $user->save();
        echo Zend_Json::encode(array('status' => 1));exit();
      } catch (Exception $e) {
        echo 0;die;
      }
    }
  }
}
