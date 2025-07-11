<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Follows.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Follows extends Engine_Db_Table {

  protected $_rowClass = "User_Model_Follow";
  protected $_name = "user_follows";
  
  public function getFollowResourceStatus($subject_id) {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $name = $this->info('name');
    
    $select = $this->select()
            ->from($name, array('*'))
            ->where('resource_id =?', $viewer_id)
            ->where('user_id =?', $subject_id)
            ->limit(1);

    return $this->fetchRow($select);
  }
  
  public function getFollowUserStatus($subject_id) {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $name = $this->info('name');
    $select = $this->select()
            ->from($name, array('*'))
            ->where('user_id =?', $viewer_id)
            ->where('resource_id =?', $subject_id)
            ->limit(1);

    return $this->fetchRow($select);
  }

  public function getFollowStatus($user_id = 0) {
  
    if (!$user_id)
      return 0;
      
    $resource_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if ($resource_id == 0)
      return false;

    $follow = $this->select()
                  ->from($this->info('name'), new Zend_Db_Expr('COUNT(follow_id) as follow'))
                  ->where('resource_id =?', $resource_id)
                  ->where('user_id =?', $user_id)
                  ->limit(1)
                  ->query()
                  ->fetchColumn();
    if ($follow > 0) {
      return true;
    } else {
      return false;
    }
    return false;
  }


  public function followers($params = array()) {
  
    $table = Engine_Api::_()->getItemTable('user');
    $memberTableName = $table->info('name');
    
    $tablenameFollow = $this->info('name');

    $select = $table->select()
                  ->from($memberTableName)
                  ->setIntegrityCheck(false)
                  ->joinLeft($tablenameFollow, $tablenameFollow . '.resource_id = ' . $memberTableName . '.user_id AND ' . $tablenameFollow . '.user_id =  ' . $params['user_id'], null)
                  ->where('follow_id IS NOT NULL')
                  ->where('resource_id !=?', $params['user_id'])
                  ->where($memberTableName . '.user_id IS NOT NULL');
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('core.autofollow', 1)) {
      $select = $select->where('user_approved =?', 1);
    }
    if(isset($params['paginator']) && !empty($params['paginator'])) {
      return Zend_Paginator::factory($select);
    } else {
      return $table->fetchAll($select);
    }
  }

  public function following($params = array()) {

    $table = Engine_Api::_()->getItemTable('user');
    $memberTableName = $table->info('name');

    $tablenameFollow = $this->info('name');

    $select = $table->select()
                ->from($memberTableName)
                ->setIntegrityCheck(false)
                ->joinLeft($tablenameFollow, $tablenameFollow . '.user_id = ' . $memberTableName . '.user_id AND ' . $tablenameFollow . '.resource_id =  ' . $params['user_id'], null)
                ->where('follow_id IS NOT NULL')
                ->where($tablenameFollow . '.user_id !=?', $params['user_id'])
                ->where($memberTableName . '.user_id IS NOT NULL');

    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('core.autofollow', 1)) {
      $select = $select->where('resource_approved =?', 1);
    }
    
    if(Engine_Api::_()->user()->getViewer()->getIdentity() != $params['user_id']) {
      $select = $select->where('user_approved =?', 1);
    }
    
    if(isset($params['paginator']) && !empty($params['paginator'])) {
      return Zend_Paginator::factory($select);
    } else {
      return $table->fetchAll($select);
    }
  }

  public function getFollowers($viewer_id) {
  
    $table = Engine_Api::_()->getItemTable('user');
    $memberTableName = $table->info('name');
    
    $tablenameFollow = $this->info('name');

    $select = $this->select()
                  ->from($tablenameFollow)
                  ->setIntegrityCheck(false)
                  ->joinLeft($memberTableName, $memberTableName . '.user_id = ' . $tablenameFollow . '.user_id', null)
                  ->where($tablenameFollow.'.user_id = ?', $viewer_id);
    return $this->fetchAll($select);
  }
}
