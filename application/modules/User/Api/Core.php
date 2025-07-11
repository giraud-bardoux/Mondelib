<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Api_Core extends Core_Api_Abstract
{
  /**
   * @var array User objects by id
   */
  protected $_users = array();

  /**
   * @var arraay User ids by email or id or username
   */
  protected $_indexes = array();

  /**
   * @var User_Model_User Contains the current viewer instance
   */
  protected $_viewer;

  /**
   * @var Zend_Auth Authentication object
   */
  protected $_auth;

  /**
   * @var Zend_Auth_Adapter_Interface Authentication adapter object
   */
  protected $_authAdapter;

  public function telegramEnable(){
    $settings['username'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('telegram.username', '');
    $settings['token'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('telegram.token', '');
    $settings['enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('telegram.enable', '0');
    if(!empty($settings['username']) && !empty($settings['token']) && !empty($settings['enable'])){
      return $settings;
    }
    return false;
  }


  // Users

  /**
   * Gets an instance of a user
   *
   * @param mixed $identity An id, username, or email
   * @return User_Model_User
   */
  public function getUser($identity)
  {
    // Identity is already a user!
    if( $identity instanceof User_Model_User ) {
      return $identity;
    }

    // Lookup in index
    $user = $this->_lookupUser($identity);
    if( $user instanceof User_Model_User ) {
      return $user;
    }
    
    // Create new instance
    $user = $this->_getUser($identity);
    if( null === $user ) {
      $user = new User_Model_User(array());
    } else {
      $this->_indexUser($user);
    }

    return $user;
  }
  /**
   * Gets an instance of multiple users
   *
   * @param array $ids
   * @return array An array of Core_Model_Item_Abstract
   */
  public function getUserMulti(array $ids)
  {
    // Remove any non-numeric values and already retv rows
    $getIds = array();
    foreach( $ids as $index => $value ) {
      if( !is_numeric($value) ) {
        unset($ids[$index]);
      } else if( !isset($this->_users[$value]) ) {
        $getIds[] = $value;
      }
    }

    // Now get any remaining rows, if necessary
    if( !empty($getIds) ) {
      foreach( Engine_Api::_()->getItemTable('user')->find($getIds) as $row ) {
        $this->_indexUser($this->_getUser($row));
      }
    }

    // Now build the return data
    $users = array();
    foreach( $ids as $id ) {
      if( isset($this->_users[$id]) ) {
        $users[] = $this->_users[$id];
      }
    }
    
    return $users;
  }



  // Viewer

  /**
   * Gets the current viewer instance using the authentication storage
   *
   * @return User_Model_User
   */
  public function getViewer()
  {
    if( null === $this->_viewer ){
      $identity = $this->getAuth()->getIdentity();
      $this->_viewer = $this->_getUser($identity);
    }

    return $this->_viewer;
  }

  public function setViewer(User_Model_User $viewer = null)
  {
    $this->_viewer = $viewer;
    return $this;
  }



  // Authentication

  /**
   * Authenticate user
   *
   * @param string $identity Email
   * @param string $credential Password
   * @return Zend_Auth_Result
   */
  public function authenticate($identity, $credential,$user)
  {
    $emailField = 'email';
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1)) {
      if (strpos($identity, '@') == false) { 
        $emailField = 'username';
      }
    }
    if(is_numeric($identity)) {
      $emailField = 'phone_number';
    }

    // Translate email
    $userTable = Engine_Api::_()->getItemTable('user');
    $userIdentity = $userTable->select()
      ->from($userTable, 'user_id')
      ->where("`$emailField` = ?", $identity)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
      
    if(strlen($user->password) > 32){
        $credential = $user->password;
    }
    $authAdapter = $this->getAuthAdapter($user,$credential)
      ->setIdentity($userIdentity)
      ->setCredential($credential);

    return $this->getAuth()->authenticate($authAdapter);
  }

  /**
   * Get the authentication object
   *
   * @return Zend_Auth
   */
  public function getAuth()
  {
    if( null === $this->_auth ) {
      $this->_auth = Zend_Auth::getInstance();
      if( _ENGINE_NO_AUTH && !$this->_auth->getIdentity() ) {
        $this->_auth->getStorage()->write(1);
      }
    }
    return $this->_auth;
  }

  /**
   * Set the authentication object
   *
   * @param Zend_Auth $auth
   * @return User_Api_Core
   */
  public function setAuth(Zend_Auth $auth)
  {
    $this->_auth = $auth;
    return $this;
  }

  /**
   * Get the authentication adapter
   *
   * @return Zend_Auth_Adapter_Interface
   */
  public function getAuthAdapter($user,$credential = "")
  {
    if( null === $this->_authAdapter ) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $tablePrefix = Engine_Db_Table::getTablePrefix();
      $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');

      $password = "MD5(CONCAT('".$salt."', ?, salt))";
      if(strlen($user->password) > 32){
          $password = password_hash($credential,PASSWORD_DEFAULT);
      }

      $this->_authAdapter = new Zend_Auth_Adapter_DbTable(
        $db,
        Engine_Api::_()->getItemTable('user')->info('name'),
        'user_id',
        'password',
        $password
      );
    }
    //  var_dump($this->_authAdapter);die;
    return $this->_authAdapter;
  }

  /**
   * Set the authentication adapter object
   *
   * @param Zend_Auth_Adapter_Interface $authAdapter
   * @return Zend_Auth
   */
  public function setAuthAdapter(Zend_Auth_Adapter_Interface $authAdapter)
  {
    $this->_authAdapter = $authAdapter;
    return $this;
  }


  /**
   * Check user Credential
   *
   * @param integer $identity user id
   * @param string $credential Password
   * @return bool
   */
  public function checkCredential($identity, $credential,$user)
  {
    if(strlen($user->password) > 32){
        return password_verify($credential, $user->password);
    }
    return $this->getAuthAdapter($user)
      ->setIdentity($identity)
      ->setCredential($credential)
      ->authenticate()
      ->isValid();
  }

  /* Utility */

  /**
   * Indexes a user object internally by id, username, email
   *
   * @param User_Model_User $user A user object
   * @return void
   */
  protected function _indexUser(User_Model_User $user)
  {
    // Ignore if not an actual user or user is already set
    if( !empty($user->user_id) && !isset($this->_users[$user->user_id]) ) {
      $this->_indexes[$user->user_id] = $user->user_id;

      if ( !empty($user->email)){
        $this->_indexes[$user->email] = $user->user_id;
      }

      if ( !empty($user->username)){
        $this->_indexes[$user->username] = $user->user_id;
      }
      
      $this->_users[$user->user_id] = $user;
    }
  }

  /**
   * Looks up a user by id, username, email
   *
    * @param mixed $identity
   * @return integer|false
   */
  protected function _lookupUser($identity)
  {
    $index = null;
    if( is_scalar($identity) && isset($this->_indexes[$identity]) ) {
      $index = $identity;
    } else if( $identity instanceof Zend_Db_Table_Row_Abstract && isset($identity->user_id) ) {
      $index = $identity->user_id;
    } else if( is_array($identity) && is_string($identity[0]) && is_numeric($identity[1]) ) {
      $index = $identity[1];
    }

    if( isset($this->_indexes[$index]) && isset($this->_users[$this->_indexes[$index]]) ) {
      return $this->_users[$this->_indexes[$index]];
    }

    return null;
  }

  protected function _getUser($identity)
  {
    if( !$identity ) {
      $user = new User_Model_User(array(
        'table' => Engine_Api::_()->getItemTable('user'),
      ));
    } else if( $identity instanceof User_Model_User ) {
      $user = $identity;
    } else if( is_numeric($identity) ) {
      $user = Engine_Api::_()->getItemTable('user')->find($identity)->current();
    } else if( is_string($identity) && strpos($identity, '@') !== false ) {
      $user = Engine_Api::_()->getItemTable('user')->fetchRow(array(
        'email = ?' => $identity,
      ));
    } else /* if( is_string($identity) ) */ {
      $user = Engine_Api::_()->getItemTable('user')->fetchRow(array(
        'username = ?' => $identity,
      ));
    }

    // Empty user?
    if( null === $user ) {
      return new User_Model_User(array());
    }
    
    return $user;
  }

  public function randomPass($len)
  {
    return substr(md5(rand().rand()), 0, $len);
  }

  public function getSuperAdmins()
  {
    $table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $table->select()
      ->where('level_id = ?', 1);

    $superadmins = $table->fetchAll($select);
    return $superadmins;
  }

  public function getVerifyToken($userId) {
    return base64_encode(time() . ":" . $userId);
  }

  public function getUserIdFromToken($token) {
    $decodedToken = base64_decode($token);
    $decodedToken = explode(':', $decodedToken);
    $userId = $decodedToken[1];
    return $userId;
  }
  
  public function getProfileTypeData($params = array()) {
    $optionsTable = Engine_Api::_()->fields()->getTable('user', 'options');
    $optionsTableName = $optionsTable->info('name');
    return $optionsTable->select()
                    ->from($optionsTableName, array('type'))
                    ->where($optionsTableName . '.option_id = ?', $params['option_id'])
                    ->query()
                    ->fetchColumn();
  }
  
  public function getProfileFieldValue($params = array()) {
    $valuesTable = Engine_Api::_()->fields()->getTable('user', 'values');
    $valuesTableName = $valuesTable->info('name');
    return $valuesTable->select()
                    ->from($valuesTableName, array('value'))
                    ->where($valuesTableName . '.item_id = ?', $params['user_id'])
                    ->where($valuesTableName . '.field_id = ?', $params['field_id'])
                    ->query()
                    ->fetchColumn();
  }
  
  public function getOptionValue($params = array()) {
    $optionsTable = Engine_Api::_()->fields()->getTable('user', 'options');
    $optionsTableName = $optionsTable->info('name');
    return $optionsTable->select()
            ->from($optionsTableName, array('option_id'))
            ->where($optionsTableName . '.field_id = ?', $params['field_id'])
            ->where($optionsTableName . '.label = ?', $params['label'])
            ->query()
            ->fetchColumn();
  }

  public function getFieldId($profile_type, $typeField = array()) {

    $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
    $metaTableName = $metaTable->info('name');

    $mapsTable = Engine_Api::_()->fields()->getTable('user', 'maps');
    $mapsTableName = $mapsTable->info('name');

    return $metaTable->select()
            ->setIntegrityCheck(false)
            ->from($metaTableName, array('field_id'))
            ->joinLeft($mapsTableName, "$metaTableName.field_id = $mapsTableName.child_id", null)
            ->where($mapsTableName . '.option_id = ?', $profile_type)
            ->where($metaTableName . '.display = ?', '1')
            ->where($metaTableName . '.type IN (?)', (array) $typeField)
            ->query()
            ->fetchColumn();
  }
  
  public function getOptionIdValue($params = array()) {
    $optionsTable = Engine_Api::_()->fields()->getTable('user', 'options');
    $optionsTableName = $optionsTable->info('name');
    return $optionsTable->select()
            ->from($optionsTableName, array('label'))
            ->where($optionsTableName . '.option_id = ?', $params['option_id'])
            ->query()
            ->fetchColumn();
  }
  public function getMutualFriendCount($subject, $viewer) {
    $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
    $friendsName = $friendsTable->info('name');
    $col1 = 'resource_id';
    $col2 = 'user_id';
    $select = new Zend_Db_Select($friendsTable->getAdapter());
    $select
            ->from($friendsName, $col1)
            ->join($friendsName, "`{$friendsName}`.`{$col1}`=`{$friendsName}_2`.{$col1}", null)
            ->where("`{$friendsName}`.{$col2} = ?", $viewer->getIdentity())
            ->where("`{$friendsName}_2`.{$col2} = ?", $subject->getIdentity())
            ->where("`{$friendsName}`.active = ?", 1)
            ->where("`{$friendsName}_2`.active = ?", 1)
    ;
    // Now get all common friends
    $uids = array();
    foreach ($select->query()->fetchAll() as $data) {
      $uids[] = $data[$col1];
    }
    // Do not render if nothing to show
    if (engine_count($uids) <= 0) {
      return 0;
    }
    // Get paginator
    $usersTable = Engine_Api::_()->getItemTable('user');
    $select = $usersTable->select()->from($usersTable->info('name'), new Zend_Db_Expr('COUNT(user_id)'))->where('user_id IN(?)', $uids);
    return $select->query()->fetchColumn();
  }

  public function getPhotoSelect($params = array())
  {
    $table = Engine_Api::_()->getItemTable('photo');
    $select = $table->select();
    
    if( !empty($params['album']) && $params['album'] instanceof Ealbum_Model_Album ) {
      $select->where('album_id = ?', $params['album']->getIdentity());
    } else if( !empty($params['album_id']) && is_numeric($params['album_id']) ) {
      $select->where('album_id = ?', $params['album_id']);
    }
    
    if( !isset($params['order']) ) {
      $select->order('order ASC');
    } else if( is_string($params['order']) ) {
      $select->order($params['order']);
    }

    if(empty($params['pagNator'])){
      if(isset($params['limit_data'])){
        $select->limit($params['limit_data']);
        return $table->fetchAll($select);
      } else
        return $table->fetchAll($select);
    } else
        return Zend_Paginator::factory($select);		
  }
}