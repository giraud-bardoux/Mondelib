<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Backgrounds.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Backgrounds extends Engine_Db_Table {

  public function getPaginator($params = array()) {
    return Zend_Paginator::factory($this->getBackgrounds($params));
  }
  
  public function getBackgrounds($params = array()) {

    $select = $this->select();
    
    if(!empty($params['admin'])) {
      $select->where('enabled =?', 1)
        ->where('starttime <= DATE(NOW())')
        ->where("(enableenddate = 0 || endtime IS NULL OR endtime = '0000-00-00' OR endtime >= DATE(NOW() )) ");
    }
    
    if(isset($params['featured']) && !empty($params['featured'])) {
      $select->where('featured =?', 1);
    }

    if(isset($params['featuredbgIds']) && !empty($params['featuredbgIds'])) {
      $select->where('background_id NOT IN (?)', $params['featuredbgIds']);
    }
    
    $select->order('order ASC');
    
    if(isset($params['activityfeedbg_limit_show']) && !empty($params['activityfeedbg_limit_show']))
      $select->limit($params['activityfeedbg_limit_show']);

    if(!empty($params['fetchAll']))
      return $this->fetchAll($select);
    return $select;
  }
}
