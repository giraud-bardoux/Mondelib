<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Block.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Block extends Engine_Db_Table {

  public function anyOneBlocked($params = array()) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $rName = $this->info('name');
    $select = $this->select()
              ->where("user_id = ".$viewer->getIdentity().' || user_id = '.$params['user_id'])
              ->Where("blocked_user_id = ".$viewer->getIdentity().' || blocked_user_id = '.$params['user_id']);
    return $this->fetchRow($select);
  }

  public function isBlocked($params = array()) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $rName = $this->info('name');
    $select = $this->select()
              ->where("user_id =?",$viewer->getIdentity())
              ->where("blocked_user_id =?",$params["user_id"]);

    $row = $this->fetchRow($select);
    if(!empty($params["remove"])){
      if($row){
        $row->delete();
        return "delete";
      }
    }
    return $row;
  }
}
