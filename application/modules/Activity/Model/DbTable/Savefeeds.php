<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Savefeeds.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Savefeeds extends Engine_Db_Table {

  public function isSaved($params = array()){
    $select = $this->select()->where('action_id =?',$params['action_id'])->where('user_id =?',$params['user_id'])->limit(1);
    return $this->fetchRow($select);  
  }
}
