<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Tagusers.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Tagusers extends Engine_Db_Table
{
  public function getActionMembers($action_id = ''){
    if(!$action_id)
      return array();
    $select = $this->select()->where('action_id =?',$action_id);
    if(!empty($params['paginator']))
      return $select;
    return $this->fetchAll($select);  
  }
}
