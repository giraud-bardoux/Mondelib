<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Users.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Users extends Engine_Db_Table
{
  protected $_name = 'users';

  protected $_rowClass = 'User_Model_User';
  
  public function getAllAdmin() {
  
  	$levelTable = Engine_Api::_()->getDbtable('levels', 'authorization');
  	$levelTableName = $levelTable->info("name");
  	$tableName = $this->info("name");
  	$select = $this->select()->setIntegrityCheck(false)
  		->from($tableName)
  		->joinLeft($levelTableName, "$levelTableName.level_id = $tableName.level_id",null)
  		->where($levelTableName.".type = ?","admin");
  	return $this->fetchAll($select);
  }
  
  public function isUserNameExist($username) {
    $tableName = $this->info("name");
    return $this->select()
                      ->from($tableName, array('user_id'))
                      ->where('username = ?', $username)
                      ->query()
                      ->fetchColumn();
  }
  
  public function isEmailExist($email) {
    $tableName = $this->info("name");
    return $this->select()
                      ->from($tableName, array('user_id'))
                      ->where('email = ?', $email)
                      ->query()
                      ->fetchColumn();
  }

  public function countActiveMembers($show = '') {
  
    $levelIds = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc(array('type' => array('admin', 'moderator')));
    $select = $this->select()
                ->from($this->info('name'), array("COUNT(user_id)"))
                ->where('approved =?', 1)
                ->where('enabled =?', 1);
    if(engine_count($levelIds) > 0 && empty($show)) {
      $select->where("level_id NOT IN (?)", array_keys($levelIds));
    }
    return $select->query()
                ->fetchColumn();
  }
  
  public function getUserExist($userId = '', $referralCode = '') {
  
    if(empty($userId)) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $userId = $viewer->getIdentity();
    } 
    
    $select = $this->select()
            ->from($this->info('name'), array('*'));
    if($userId) {
      $select->where('user_id = ?', $userId);
    }

    if(!empty($referralCode)) {
      $select->where('referral_code =?', $referralCode);
    }
    
    return $this->fetchRow($select);
  }
  
  public function isPhoneNumberExist($phone_number, $country_code, $userId = null) {
    $tableName = $this->info("name");
    $select = $this->select()
                ->from($tableName, 'user_id');
    if(is_numeric($phone_number)) {
      $select->where('phone_number = ?', $phone_number)
            ->where('country_code = ?', $country_code);
  	  if($userId) {
          $select->where('user_id != ?', $userId);
  	  }
    } else {
      $select->where('email = ?', $phone_number);
    }
    return $select->query()->fetchColumn();
  }
}
