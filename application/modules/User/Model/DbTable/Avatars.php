<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Verificationrequests.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Avatars extends Engine_Db_Table {
  
  protected $_rowClass = 'User_Model_Avatar';
  
  public function getPaginator($params = array()) {
    return Zend_Paginator::factory($this->getAvatars($params));
  }
  
  public function getAvatars($params = array()) {
  
    $select = $this->select()->where('file_id <>?', 0)->order('order ASC');
    
    if(isset($params) && $params['enabled'] == 1)
      $select->where('enabled =?', 1);

    if(!empty($params['limit'])) {
      $select->limit($params['limit']);
    }
    if(!empty($params['fetchAll']))
      return $this->fetchAll($select);
    return $select;
  }
}
