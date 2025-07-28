<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: EditController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class User_EditController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      // Can specifiy custom id
      $id = $this->_getParam('id', null);
      $subject = null;
      if( null === $id ) {
        $subject = Engine_Api::_()->user()->getViewer();
        Engine_Api::_()->core()->setSubject($subject);
      } else {
        $subject = Engine_Api::_()->getItem('user', $id);
        Engine_Api::_()->core()->setSubject($subject);
      }
    }
    // if( !empty($id) ) {
    //   $params = array('id' => $id);
    // } else {
    //   $params = array();
    // }
    // // Set up require's
    // $this->_helper->requireUser();
    // $this->_helper->requireSubject('user');
    // $this->_helper->requireAuth()->setAuthParams(
    //   null,
    //   null,
    //   'edit'
    // );
  }
  public function profileAction()
  {
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    
    // Element: profile_type
    $editProfileType = false;
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
        $editProfileType = true;
      }
    }
    
    // General form w/o profile type
    $profileTypesArray = [];
    $aliasedFields = $viewer->fields()->getFieldsObjectsByAlias();
    //$changeUserProfileType = Engine_Api::_()->getDbTable('values', 'authorization')->changeUsersProfileType($viewer);
    $topLevelId = 0;
    $topLevelValue = null;
    if (isset($aliasedFields['profile_type'])) {
      $aliasedFieldValue = $aliasedFields['profile_type']->getValue($viewer);
      $topLevelId = $aliasedFields['profile_type']->field_id;
      $topLevelValue = (is_object($aliasedFieldValue) ? $aliasedFieldValue->value : null);
      if (!$topLevelId || !$topLevelValue) {
        $topLevelId = null;
        $topLevelValue = null;
      }
      //$this->view->topLevelId = $topLevelId;
      //$this->view->topLevelValue = $topLevelValue;
    }

    // Get form
    $form = $this->view->form = new Sesapi_Form_Standard(array(
      'item' => Engine_Api::_()->core()->getSubject(),
      'topLevelId' => $topLevelId,
      'topLevelValue' => $topLevelValue,
    ));
    $form->populate($user->toArray());
    
    if($this->_getParam('getForm')) {
     $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
     $this->generateFormFields($formFields);
    } else if($this->_getParam('validateFieldsForm')) {
      $values = $this->getRequest()->getPost();
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      foreach($formFields as $key => $value){
        if($value['type'] == "Date"){
          $date = $values[$value['name']];
          //Date format change 
          $date = str_replace('/', '-', $date);
          if(!empty($date) && !is_null($date)){
            $values[$value['name']] = array();
            $values[$value['name']]['month'] = date('m',strtotime($date));
            $values[$value['name']]['year'] = date('Y',strtotime($date));
            $values[$value['name']]['day'] = date('d',strtotime($date));
          }
        }else if($value['type'] == "MultiCheckbox"){
          $arrayValues = $valuesArray = array();
          $valuesArray = $values[$value['name']];
          unset($values[$value['name']]);
          $counter = 0;
          foreach($valuesArray as $key=>$val){
            $arrayValues[$counter] = $key;
            $counter++;
          }
          $values[$value['name']] = $arrayValues;
        }
      }
      if( !$form->isValid($values) ) {
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        $this->validateFormFields($validateFields);
      } else {
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
        
        $form->populate($user->toArray());
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your changes have been saved.")));
        
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  public function photoAction()
  {
    ini_set('memory_limit', '-1');
    $resource_type = $this->_getParam('resource_type','album_photo');
    $this->view->user = $user = Engine_Api::_()->core()->getSubject();
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photo = Engine_Api::_()->getItem($resource_type,$photo_id);
    }


    if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0) || !empty($photo)) {
      $db = $user->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        if(!empty($photo))
          $file = $photo;
        else
          $file = $_FILES['image'];
        $user->setPhoto($file);

        $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);

        // Insert activity
        $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($user, $user, 'profile_photo_update');

        // Hooks to enable albums to work
        if( $action ) {

            $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);
            if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album')) {
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserPhotoUpload', array(
                        'user' => $user,
                        'file' => $iMain,
                    ));
            } else if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum')) {
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserProfilePhotoUpload', array(
                        'user' => $user,
                        'file' => $iMain,
                    ));
            }
          if(!empty($event)){
            $attachment = $event->getResponse();
          }
          if( !$attachment ) $attachment = $iMain;
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $attachment);
        }
        $db->commit();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your profile photo updated successfully.")));
      }

      // If an exception occurred within the image adapter, it's probably an invalid image
      catch( Engine_Image_Adapter_Exception $e )
      {
         $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The uploaded file is not supported or is corrupt.'), 'result' => array()));
      }

      // Otherwise it's probably a problem with the database or the storage system (just throw it)
      catch( Exception $e )
      {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->__toString(), 'result' => array()));
      }
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  public function removeCoverAction()
  {
    // Get form
    $user = Engine_Api::_()->core()->getSubject();
    $user->coverphoto = 0;
    $user->coverphotoparams = null;
    $user->save();

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your cover photo has been removed.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));

  }
  public function coverAction(){
    $user = Engine_Api::_()->user()->getViewer();
    $photo_id = $this->_getParam('photo_id',0);
    if($photo_id){
      $photoAlbum = Engine_Api::_()->getItem('album_photo',$photo_id);
    }
		$art_cover = $user->coverphoto;
		if((!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0)) {
      try {
        $file = $_FILES['image'];
        
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum')) {
        
					$type = 'cover';
					
					$table = Engine_Api::_()->getItemTable('album');
					$select = $table->select()
							->where('owner_type = ?', $user->getType())
							->where('owner_id = ?', $user->getIdentity())
							->where('type = ?', $type)
							->order('album_id ASC')
							->limit(1);
					$album = $table->fetchRow($select);
					// Create wall photos album if it doesn't exist yet
					if( null === $album ) {
						$translate = Zend_Registry::get('Zend_Translate');
						$album = $table->createRow();
						$album->owner_type = 'user';
						$album->owner_id = $user->getIdentity();
						$album->title = $translate->_(ucfirst($type) . ' Photos');
						$album->type = $type;
						$album->search = 1;
						$album->save();
						// Authorizations
						$auth = Engine_Api::_()->authorization()->context;
						$auth->setAllowed($album, 'everyone', 'view',    true);
						$auth->setAllowed($album, 'everyone', 'comment', true);
					}

					$photoTable = Engine_Api::_()->getItemTable('photo');
					$photo = $photoTable->createRow();
					$photo->setFromArray(array(
							'owner_type' => 'user',
							'owner_id' => $user->getIdentity()
					));
					$photo->save();
					$user = $this->setCoverPhoto($file,$user);
					if(isset($photo->order))
						$photo->order = $photo->photo_id;
					$photo->album_id = $album->album_id;
					$photo->file_id = $user->coverphoto;
					$photo->save();
					if (!$album->photo_id) {
						$album->photo_id = $photo->getIdentity();
						$album->save();
					}

					$action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($user, $user, 'cover_photo_update');
					// Hooks to enable albums to work
					if ($action) {
						$event = Engine_Hooks_Dispatcher::_()
							->callEvent('onUserPhotoUpload', array(
							'user' => $user,
							'file' => $photo,
							'type' => 'cover',
							));

						$attachment = $event->getResponse();
						if (empty($attachment)) {
							$attachment = $photo;
						}

						Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $attachment);
					}
				} else {
					$this->setCoverPhoto($file, $user);
				}

      } catch(Exception $e){
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate($e), 'result' => array()));
      }
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->translate("Your cover photo edit successfully.")));
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Something went wrong, please try again later."), 'result' => array()));
  }
  private function setCoverPhoto($photo, $user, $level_id = null)
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
    $filesTable = Engine_Api::_()->getDbTable('files', 'storage');
    
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(1600, 1600)
      ->write($mainPath)
      ->destroy();

    if (!empty($user)) {
      $params = array(
        'parent_type' => $user->getType(),
        'parent_id' => $user->getIdentity(),
        'user_id' => $user->getIdentity(),
        'name' => basename($fileName),
      );
      try {
        $iMain = $filesTable->createFile($mainPath, $params);

        $user->coverphoto = $iMain->file_id;
        $user->save();
      } catch (Exception $e) {
        @unlink($mainPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      @unlink($mainPath);
      if (!empty($tmpRow)) {
        $tmpRow->delete();
      }
      return $user;
    } else {
      try {
        $iMain = $filesTable->createSystemFile($mainPath);
        // Remove temp files
        @unlink($mainPath);
      } catch (Exception $e) {
        @unlink($mainPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      Engine_Api::_()->getApi("settings", "core")
        ->setSetting("usercoverphoto.preview.level.id.$level_id", $iMain->file_id);
      return $user;
    }
  }
  public function removePhotoAction()
  {
    // Get form
    $user = Engine_Api::_()->core()->getSubject();

    $file = Engine_Api::_()->getItem('storage_file', $user->photo_id);
    if($file->parent_type == 'user') {
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
    $user->photo_id = 0;
    $user->save();

    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));

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
}
