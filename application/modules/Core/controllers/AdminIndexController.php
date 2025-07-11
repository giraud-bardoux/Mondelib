<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminIndexController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_AdminIndexController extends Core_Controller_Action_Admin {

  public function indexAction() {
    if( !Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.url')) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('core.general.site.url', _ENGINE_SITE_URL);
    }
    
    //Feed Default installation
    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.pluginactivated')) {
      include_once APPLICATION_PATH . "/application/modules/Activity/controllers/defaultsettings.php";
      Engine_Api::_()->getApi('settings', 'core')->setSetting('activity.pluginactivated', 1);
    }

    $table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $table->select()
                  ->from($table->info('name'))
                  ->where('import =?', 0);
    $this->view->paginator = Zend_Paginator::factory($select);
  }

  public function changeEnvironmentModeAction()
  {
    if ($this->getRequest()->isPost() && $this->_getParam('environment_mode', false)) {
      $environmentMode = $this->_getParam('environment_mode', false);
      
      $global_settings_file = APPLICATION_PATH . '/application/settings/general.php';
      if (file_exists($global_settings_file)) {
          $g = include $global_settings_file;
          if (!is_array($g)) {
              $g = (array) $g;
          }
      } else {
          $g = array();
      }

      if (!is_writable($global_settings_file)) {
          // not writable; can we delete and re-create?
          if (is_writable(dirname($global_settings_file))) {
              @rename($global_settings_file, $global_settings_file.'_backup.php');
              @touch($global_settings_file);
              @chmod($global_settings_file, 0666);
              if (!file_exists($global_settings_file) || !is_writable($global_settings_file)) {
                  @rename($global_settings_file, $global_settings_file.'_delete.php');
                  @rename($global_settings_file.'_backup.php', $global_settings_file);
                  @unlink($global_settings_file.'_delete.php');
              }
          }
          if (!is_writable($global_settings_file)) {
              $this->view->success = false;
              $this->view->error   = 'Unable to write to settings file; please CHMOD 666 the file /application/settings/general.php, then try again.';
              return;
          } else {
              // it worked; continue.
          }
      }
      if($environmentMode == "development"){
        // create js and css file
        Engine_Api::_()->core()->generateJsCss();
      }
      if ($this->_getParam('environment_mode') != @$g['environment_mode']) {
          $g['environment_mode'] = $this->_getParam('environment_mode');
          $file_contents  = "<?php defined('_ENGINE') or die('Access Denied'); return ";
          $file_contents .= var_export($g, true);
          $file_contents .= "; ?>";
          $this->view->success = @file_put_contents($global_settings_file, $file_contents);

          // clear scaffold cache
          Core_Model_DbTable_Themes::clearScaffoldCache();

          // Increment site counter
          $settings = Engine_Api::_()->getApi('settings', 'core');
          $settings->core_site_counter = $settings->core_site_counter + 1;

          return;
      } else {
          $this->view->message = 'No change necessary';
          $this->view->success = true; // no change
      }

      

    }
    $this->view->success = false;
  }
  
  public function flushPhotoAction() {
  
    $this->view->form = $form = new Core_Form_Admin_FlushPhotos();
    
    $this->_helper->layout->setLayout('admin-simple');
    
    if( !$this->getRequest()->isPost())
      return;

    try {
      $flushData = Engine_Api::_()->getDbTable('files', 'storage')->getFlushPhotoData();
      foreach($flushData as $item) {
        Engine_Api::_()->storage()->deleteExternalsFiles($item->file_id);
        $item->delete();
      }
    } catch(Exception $e) {
      //throw $e;
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_("Unmapped photos remove successfully.");
    return 
    $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'index'), 'admin_default', true),
      'messages' => Array($this->view->message)
    ));
  }

  public function notesAction() {
    if( !Engine_Api::_()->getApi('settings', 'core')->getSetting('coreadmin.notes')) {
      $notes = Engine_Api::_()->getApi('settings', 'core')->setSetting('coreadmin.notes', $_POST['coreadmin_notes']);
    } else {
      $notes = Engine_Api::_()->getApi('settings', 'core')->setSetting('coreadmin.notes', $_POST['coreadmin_notes']);
    }
    echo json_encode(array('status' => true));die;
  }

  public function userDataImportAction() {
  
    $this->view->form = $form = new Core_Form_Admin_UserDataImport();
    
    $this->_helper->layout->setLayout('admin-simple');
    
    if( !$this->getRequest()->isPost())
      return;

    try {
    
      $userApi = Engine_Api::_()->user();
      $usersTable = Engine_Api::_()->getDbTable('users', 'user');

      $results = $usersTable->fetchAll($usersTable->select()->from($usersTable->info('name'))->where('import =?', 0)->limit('500'));
      foreach($results as $result) {
        $fieldsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($result);

        if (!empty($fieldsByAlias['profile_type'])) {
          $optionId = $fieldsByAlias['profile_type']->getValue($result);
          
          //Save profile type value in users table
          $profile_type = $optionId->value;
          $result->profile_type = $profile_type;
          $result->save();
          
          if(!empty($profile_type)) {
            //Display Name
            $firstNameFieldId = $userApi->getFieldId($profile_type, array('first_name'));
            $lastNameFieldId = $userApi->getFieldId($profile_type, array('last_name'));
            $genderFieldId = $userApi->getFieldId($profile_type, array('gender'));
            $birthdateFieldId = $userApi->getFieldId($profile_type, array('birthdate'));
            
            $user_id = $result->getIdentity();
            $first_name = $userApi->getprofileFieldValue(array('user_id' => $user_id, 'field_id' => $firstNameFieldId));
            if(!empty($first_name)) {
              $result->firstname = $first_name;
              $result->save();
              
              Engine_Api::_()->fields()->getTable('user', 'values')->delete(array('item_id = ?' => $user_id, 'field_id = ?' => $firstNameFieldId));
            }
            
            $last_name = $userApi->getprofileFieldValue(array('user_id' => $user_id, 'field_id' => $lastNameFieldId));
            if(!empty($last_name)) {
              $result->lastname = $last_name;
              $result->save();
              
              Engine_Api::_()->fields()->getTable('user', 'values')->delete(array('item_id = ?' => $user_id, 'field_id = ?' => $lastNameFieldId));
            }
            
            $gender = $userApi->getprofileFieldValue(array('user_id' => $user_id, 'field_id' => $genderFieldId));
            if(!empty($gender)) {
              $getOptionValue = $userApi->getOptionIdValue(array('option_id' => $gender));
              $result->gender = strtolower($getOptionValue);
              $result->save();
              
              Engine_Api::_()->fields()->getTable('user', 'values')->delete(array('item_id = ?' => $user_id, 'field_id = ?' => $genderFieldId));
            }
            
            $birthdate = $userApi->getprofileFieldValue(array('user_id' => $user_id, 'field_id' => $birthdateFieldId));
            if(!empty($birthdate)) {
              $result->dob = $birthdate;
              $result->save();
              
              Engine_Api::_()->fields()->getTable('user', 'values')->delete(array('item_id = ?' => $user_id, 'field_id = ?' => $birthdateFieldId));
            }
          }
          $result->import = 1;
          $result->save();
        }
      }
      
      //When all field import done, then delete related profile field
      // $table = Engine_Api::_()->getDbtable('users', 'user');
      // $select = $table->select()
      //               ->from($table->info('name'))
      //               ->where('import =?', 0);
      // $userData = Zend_Paginator::factory($select);
      // if($userData->getTotalItemCount() == 0) {
      
      //   $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
      //   $metaTableName = $metaTable->info('name');
        
      //   $select = $metaTable->select()
      //             ->from($metaTableName)
      //             ->where($metaTableName . '.type IN (?)', array('first_name', 'last_name', 'gender', 'birthdate'));
      //   $datas = $metaTable->fetchAll($select);
      //   foreach($datas as $data) {
      //     $field = Engine_Api::_()->fields()->getField($data->field_id, 'user');
      //     Engine_Api::_()->fields()->deleteField('user', $field);
      //   }
      // }
    } catch(Exception $e) {
      //throw $e;
    }
    $this->view->message = Zend_Registry::get('Zend_Translate')->_("User data imported successfully.");
    return $this->_forward('success' ,'utility', 'core', array(
      'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'index'), 'admin_default', true),
      'messages' => Array($this->view->message)
    ));
  }
}
