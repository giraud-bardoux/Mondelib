<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: User.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_User extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = array('search', 'displayname', 'username');
  /**
   * Gets the title of the user (their username)
   *
   * @return string
   */
  public function getTitle($is_show = true)
  {
    // This will cause various problems
    //$viewer = Engine_Api::_()->user()->getViewer();
    //if( $viewer->getIdentity() && $viewer->getIdentity() == $this->getIdentity() )
    //{
    //  $translate = Zend_Registry::get('Zend_Translate');
    //  return $translate->translate('You');
    //}
    
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.showusername', 0) && isset($this->username) && '' !== trim($this->username)) { 
      $title = $this->username;
    } else {
      if( isset($this->displayname) && '' !== trim($this->displayname) ) {
        $title = $this->displayname;
      } else if( isset($this->username) && '' !== trim($this->username) ) {
        $title = $this->username;
      } else if( isset($this->email) && '' !== trim($this->email) ) {
        $tmp = explode('@', $this->email);
        $title = $tmp[0];
      } else {
        $title = "<i>" . Zend_Registry::get('Zend_Translate')->_("Deleted Member") . "</i>";
      }
    }
    
    if(!empty($this->is_verified) && $is_show) {
      if(!empty($_GET['restApi'])) {
        $title = $title. ' <img src="'.$this->verifiedIcon().'" />';
      } else {
        $verified_tiptext = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'verified_tiptext');
        $verified_tiptext = !empty($verified_tiptext) ? $verified_tiptext : 'Verified';
        $title = '<span class="user_name">'.$title. '</span><img data-bs-toggle="tooltip" title="'.Zend_Registry::get('Zend_Translate')->_($verified_tiptext).'" src="'.$this->verifiedIcon().'" alt="" class="verified_icon" width="14px">';
      }
    }
    return $title;
  }
  
  public function getUsername() {
    return $this->username;
  }
  
  public function getPhotoUrl($type = null) {
    if(isset($this->photo_id) && !empty($this->photo_id)) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, $type);
      if ($file)
        return $file->map();
    }
    return null;
  }

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array())
  {
    $profileAddress = null;
    if( isset($this->username) && '' != trim($this->username) ) {
      $profileAddress = $this->username;
    } else if( isset($this->user_id) && $this->user_id > 0 ) {
      $profileAddress = $this->user_id;
    } else {
      return 'javascript:void(0);';
    }
    
    $params = array_merge(array(
      'route' => 'user_profile',
      'reset' => true,
      'id' => $profileAddress,
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }


  public function setDisplayName($displayName)
  {
    if( is_string($displayName) )
    {
      $this->displayname = $displayName;
    }

    else if( is_array($displayName) )
    {
      // Has both names
      if( !empty($displayName['first_name']) && !empty($displayName['last_name']) )
      {
        $displayName = $displayName['first_name'].' '.$displayName['last_name'];
      }
      // Has full name
      else if( !empty($displayName['full_name']) )
      {
        $displayName = $displayName['full_name'];
      }
      // Has only first
      else if( !empty($displayName['first_name']) )
      {
        $displayName = $displayName['first_name'];
      }
      // Has only last
      else if( !empty($displayName['last_name']) )
      {
        $displayName = $displayName['last_name'];
      }
      // Has neither (use username)
      else
      {
        $displayName = $this->username;
      }
      
      $this->displayname = $displayName;
    }
  }

  public function setPhoto($photo)
  {
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if( $photo instanceof Storage_Model_File ) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if( $photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id) ) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if( !$fileName ) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr(basename($fileName), '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => $this->getType(),
      'parent_id' => $this->getIdentity(),
      'user_id' => $this->getIdentity(),
      'name' => basename($fileName),
    );

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

    $extension = Engine_Api::_()->core()->convertImageToWebp($extension);
    
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->autoRotate()
      ->resize(720, 720)
      ->write($mainPath)
      ->destroy();

    // Resize image (icon)
    $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->autoRotate();

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($squarePath)
      ->destroy();

    // Store
    $iMain = $filesTable->createFile($mainPath, $params);
    $iSquare = $filesTable->createFile($squarePath, $params);

    $iMain->bridge($iSquare, 'thumb.icon');

    // Remove temp files
    @unlink($mainPath);
    @unlink($squarePath);
    
		//profile photo delete if someone upload new photo.
		if(!empty($this->photo_id) && $this->photo_id != $this->avatar_id) {
			$file = Engine_Api::_()->getItem('storage_file', $this->photo_id);
			if($file) {
        $getParentChilds = $file->getChildren($file->getIdentity());
        foreach ($getParentChilds as $child) {
          // remove child file.
          @unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . $child['storage_path']);
          // remove child directory.
          $childPhotoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . str_replace(basename($child['storage_path']),"",$child['storage_path']);
          if(@is_dir($childPhotoDir)){
            @rmdir($childPhotoDir);
          }
          // remove child row from db.
          $child->remove();
        }
        // remove parent file.
        @unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . $file['storage_path']);
        // remove directory.
        $parentPhotoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . str_replace(basename($file['storage_path']),"",$file['storage_path']);
        if(@is_dir($parentPhotoDir)){
          @rmdir($parentPhotoDir);
        }
        if ($file) {
          // remove parent form db.
          $file->remove();
        }
			}
		}

    // Update row
    $this->modified_date = date('Y-m-d H:i:s');
    $this->photo_id = $iMain->file_id;
    $this->save();

    return $this;
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

    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
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
				// if user coverphoto column is empty.
				if(!empty($user['coverphoto'])){
					$file = Engine_Api::_()->getItem('storage_file', $user['coverphoto']);
					if($file) {
						Engine_Api::_()->storage()->deleteExternalsFiles($file->file_id);
						$file->delete();
					}
				}
        $user->coverphoto = $iMain->file_id;
        $user->save();
      } catch (Exception $e) {
        @unlink($mainPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
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
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
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

  public function isAllowed($resource, $action = 'view')
  {
    return Engine_Api::_()->getApi('core', 'authorization')->isAllowed($resource, $this, $action);
  }

  public function isEnabled()
  {
    return ( $this->enabled );
  }

  public function isSuperAdmin()
  {
    // Not logged in, not an admin
    if( !$this->getIdentity() || empty($this->level_id) ) {
      return false;
    }
    return $this->getTable()->getAdapter()
        ->select()
        ->from('engine4_authorization_levels', new Zend_Db_Expr('TRUE'))
        ->where('level_id = ?', $this->level_id)
        ->where('flag = ?', 'superadmin')
        ->limit(1)
        ->query()
        ->fetchColumn();
  }

  public function isAdmin()
  {
    // Not logged in, not an admin
    if( !$this->getIdentity() || empty($this->level_id) ) {
      return false;
    }
    
    // Check level
    //return (bool) Engine_Registry::get('database-default')
    // return (bool) Zend_Registry::get('Zend_Db')
    return $this->getTable()->getAdapter()
        ->select()
        ->from('engine4_authorization_levels', new Zend_Db_Expr('TRUE'))
        ->where('level_id = ?', $this->level_id)
        ->where('type IN(?)', array('admin', 'moderator'))
        ->limit(1)
        ->query()
        ->fetchColumn();
  }
  
  public function isAdminOnly()
  {
    // Not logged in, not an admin
    if( !$this->getIdentity() || empty($this->level_id) ) {
      return false;
    }
    
    // Check level
    //return (bool) Engine_Registry::get('database-default')
    // return (bool) Zend_Registry::get('Zend_Db')
    return $this->getTable()->getAdapter()
        ->select()
        ->from('engine4_authorization_levels', new Zend_Db_Expr('TRUE'))
        ->where('level_id = ?', $this->level_id)
        ->where('type IN(?)', array('admin'))
        ->limit(1)
        ->query()
        ->fetchColumn();
  }
  
  public function isOnlyAdmin()
  {
    // Not logged in, not an admin
    if( !$this->getIdentity() || empty($this->level_id) ) {
      return false;
    }
    
    // Check level
    //return (bool) Engine_Registry::get('database-default')
    // return (bool) Zend_Registry::get('Zend_Db')
    return $this->getTable()->getAdapter()
        ->select()
        ->from('engine4_authorization_levels', new Zend_Db_Expr('TRUE'))
        ->where('level_id = ?', $this->level_id)
        ->where('flag != ?', 'superadmin')
        ->where('type IN(?)', array('admin'))
        ->limit(1)
        ->query()
        ->fetchColumn();
  }

  // Internal hooks

  protected function _insert()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $mappedLevel = 0;
    $accountSession = new Zend_Session_Namespace('User_Plugin_Signup_Account');
    $profileTypeValue = @$accountSession->data['profile_type'];
    $mappedLevel = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')->getMappedLevelId($profileTypeValue);
    // These need to be done first so the hook can see them
    $this->level_id = $mappedLevel ? $mappedLevel : Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel()->level_id;
    
    if(!empty($this->email)) {
      $this->approved = (int) ($settings->getSetting('user.signup.approve', 1) == 1);
      $this->verified = (int) ($settings->getSetting('user.signup.enabletwostep', 0));
    } else {
      //In case of phone number
      $this->approved = 1;
      $this->verified = 1;
    }
    $this->enabled  = $this->approved;
    $this->search   = true;
    
    if(!empty($_SESSION['facebook_signup'])) {
      $this->approved = 1;
      $this->verified = 1;
      $this->enabled  = 1;
    }

    if( empty($this->_modifiedFields['timezone']) ) {
      $this->timezone = $settings->getSetting('core.locale.timezone', 'America/Los_Angeles');
    }
    if( empty($this->_modifiedFields['locale']) ) {
      $this->locale = $settings->getSetting('core.locale.locale', 'auto');
    }
    if( empty($this->_modifiedFields['language']) ) {
      $this->language = $settings->getSetting('core.locale.language', 'en_US');
    }
    
    if( 'cli' !== PHP_SAPI ) { // No CLI
      // Get ip address
      $db = $this->getTable()->getAdapter();
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
      $this->creation_ip = $ipExpr;
    }

    // Set defaults, process etc
    $this->salt = (string) rand(1000000, 9999999);
    if( !empty($this->password) ) {
//      $this->password = md5( $settings->getSetting('core.secret', 'staticSalt')
//                          . $this->password
//                          . $this->salt );
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    } else {
      $this->password = '';
    }
    
    
    //verified
    $verified_setting = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'verified');
    if(!empty($verified_setting) && $verified_setting == 1) {
      $this->is_verified = 1;
    }

    // The hook will be called here
    parent::_insert();
  }

  protected function _postInsert()
  {
    parent::_postInsert();
    
    // Create auth stuff
    $context = Engine_Api::_()->authorization()->context;
    
    // View
    $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'auth_view');
    if( empty($view_options) || !is_array($view_options) ) {
      $view_options = array('member', 'network', 'registered', 'everyone');
    }
    foreach( $view_options as $role ) {
      $context->setAllowed($this, $role, 'view', true);
    }

    // update 'view_privacy'
    $viewPrivacy = 'owner';
    if( engine_in_array('everyone', $view_options) ) {
      $viewPrivacy = 'everyone';
    } elseif( engine_in_array('registered', $view_options) ) {
      $viewPrivacy = 'registered';
    } elseif( engine_in_array('network', $view_options) ) {
      $viewPrivacy = 'network';
    } elseif( engine_in_array('member', $view_options) ) {
      $viewPrivacy = 'member';
    }

    $table = Engine_Api::_()->getDbtable('users', 'user');
    $where = $table->getAdapter()->quoteInto('user_id = ?', $this->user_id);
    $table->update(array('view_privacy' => $viewPrivacy), $where);

    // Comment
    $comment_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'auth_comment');
    if( empty($comment_options) || !is_array($comment_options) ) {
      $comment_options = array('member', 'network', 'registered', 'everyone');
    }
    foreach( $comment_options as $role ) {
      $context->setAllowed($this, $role, 'comment', true);
    }
    
    //Poke plugin
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poke')) {
      $context->setAllowed($this, "member", 'pokeAction', true);
    }
  }

  protected function _update()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    // Hash password if being updated
    if( !empty($this->_modifiedFields['password']) ) {
      if( empty($this->salt) ) {
        $this->salt = (string) rand(1000000, 9999999);
      }
      $this->password = password_hash($this->password,PASSWORD_DEFAULT);
//      $this->password = md5( $settings->getSetting('core.secret', 'staticSalt')
//        . $this->password
//        . $this->salt );
    }

    // Update enabled, hook will set to false if necessary
    if( !empty($this->_modifiedFields['approved']) ||
        !empty($this->_modifiedFields['verified']) ||
        !empty($this->_modifiedFields['enabled']) ) {
      $enabled = true;
      if( $this->_cleanData['enabled'] != $this->enabled ) {
        $enabled = $this->enabled;
      }
      if( 1 === (int) $settings->getSetting('user.signup.enabletwostep', 0) ) {
        $this->enabled = ( $this->approved && $this->verified && $enabled );
      } else {
        $this->enabled = (bool) $this->approved && $enabled;
      }
    }
    // Call parent
    parent::_update();
  }

  protected function _delete()
  {
    // Check level
    $level = Engine_Api::_()->getItem('authorization_level', $this->level_id);
    if( $level->flag == 'superadmin' ) {
      throw new User_Model_Exception('Cannot delete superadmins.');
    }
    
    // Remove from online users
    $table = Engine_Api::_()->getDbtable('online', 'user');
    $table->delete(array('user_id = ?' => $this->getIdentity()));

    // Remove from verify users
    $verifyTable = Engine_Api::_()->getDbtable('verify', 'user');
    $verifyTable->delete(array('user_id = ?' => $this->getIdentity()));
    
    // Remove fields values
    Engine_Api::_()->fields()->removeItemValues($this);

    // Call parent
    parent::_delete();
  }
  
  // Ownership
  public function isOwner($owner)
  {
    // A user only can be owned by self
    return ( $owner->getGuid(false) === $this->getGuid(false) );
  }

  public function getOwner($recurseType = null)
  {
    // A user only can be owned by self
    return $this;
  }

  public function getParent($recurseType = null)
  {
    // A user can only belong to self
    return $this;
  }



  // Blocking
  
  public function isBlocked($user)
  {
    // Check auth?
    if( !Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
      return false;
    }

    $table = Engine_Api::_()->getDbtable('block', 'user');
    $select = $table->select()
      ->where('user_id = ?', $this->getIdentity())
      ->where('blocked_user_id = ?', $user->getIdentity())
      ->limit(1);
    $row = $table->fetchRow($select);
    return ( null !== $row );
  }

  public function isBlockedBy($user)
  {
    // Check auth?
    if( !Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
      return false;
    }
    
    $table = Engine_Api::_()->getDbtable('block', 'user');
    $select = $table->select()
      ->where('user_id = ?', $user->getIdentity())
      ->where('blocked_user_id = ?', $this->getIdentity())
      ->limit(1);
    $row = $table->fetchRow($select);
    return ( null !== $row );
  }

  public function getBlockedUsers()
  {
    $user = Engine_Api::_()->user()->getViewer();
    // Check auth?
    if( !Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
      return array();
    }
    
    $table = Engine_Api::_()->getDbtable('block', 'user');
    $select = $table->select()
      ->where('user_id = ?', $this->getIdentity());
    
    $ids = array();
    foreach( $table->fetchAll($select) as $row )
    {
      $ids[] = $row->blocked_user_id;
    }

    return $ids;
  }

  public function getBlockedUserIds()
  {
    if( !Engine_Api::_()->authorization()->isAllowed('user', null, 'block') ) {
      return array();
    }

    $table = Engine_Api::_()->getDbtable('block', 'user');
    $select = $table->select()
      ->where('blocked_user_id = ?', $this->getIdentity());

    $ids = array();
    foreach( $table->fetchAll($select) as $row ) {
      $ids[] = $row->user_id;
    }

    return $ids;
  }

  public function getAllBlockedUserIds()
  {
    return array_unique(array_merge($this->getBlockedUsers(), $this->getBlockedUserIds()));
  }

  public function addBlock(User_Model_User $user)
  {
    // Check auth?
    //die(Engine_Api::_()->authorization()->isAllowed($user, $this, 'block'));
    if( !Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
      return $this;
    }
    
    if( !$this->isBlocked($user) && $user->getGuid(false) != $this->getGuid(false) )
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try
      {
        Engine_Api::_()->getDbtable('block', 'user')
          ->insert(array(
            'user_id' => $this->getIdentity(),
            'blocked_user_id' => $user->getIdentity()
          ));
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }

    return $this;
  }

  public function removeBlock(User_Model_User $user)
  {
    // Check auth?
    if( !Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
      return $this;
    }
    
    Engine_Api::_()->getDbtable('block', 'user')
      ->delete(array(
        'user_id = ?' => $this->getIdentity(),
        'blocked_user_id = ?' => $user->getIdentity()
      ));
      
    return $this;
  }



  // Interfaces

  /**
   * Gets a proxy object for the likes handler
   *
   * @return Engine_ProxyObject
   **/
  public function likes()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
  }

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
   * Gets a proxy object for the fields handler
   *
   * @return Engine_ProxyObject
   */
  public function fields()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getApi('core', 'fields'));
  }

  /**
   * Gets a proxy object for the membership handler
   * 
   * @return Engine_ProxyObject
   */
  public function membership()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('membership', 'user'));
  }

  public function lists()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('lists', 'user'));
  }


  /**
   * Gets a proxy object for the fields handler
   *
   * @return Engine_ProxyObject
   */
  public function status()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('status', 'core'));
  }



  // Utility
  
  protected function _readData($spec)
  {
    if( is_scalar($spec) )
    {
      // Identity
      if( is_numeric($spec) )
      {
        // Can't use find because it won't return a row class
        $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("user_id = ?", $spec));
      }

      // By email
      else if( is_string($spec) && strpos($spec, '@') !== false )
      {
        $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("email = ?", $spec));
      }

      // By username
      else if( is_string($spec) )
      {
        $spec = $this->getTable()->fetchRow($this->getTable()->select()->where("username = ?", $spec));
      }
    }

    parent::_readData($spec);
  }

  public function verifiedIcon() {
    $icon = '';
    if(!empty($this->is_verified)) {
      $verified_icon = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('user', $this, 'verified_icon');
      $icon = !empty($verified_icon) ? Engine_Api::_()->core()->getFileUrl($verified_icon) :  _ENGINE_SITE_URL . '/application/modules/User/externals/images/verify-icon.png';
    }
    return $icon;
  }
}
