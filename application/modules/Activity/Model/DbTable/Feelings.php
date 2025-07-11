<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Feelings.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Feelings extends Engine_Db_Table {
  
  protected $_rowClass = 'Activity_Model_Feeling';
  
  public function getPaginator($params = array()) {

    return Zend_Paginator::factory($this->getFeelings($params));
  }

  public function getFeelings($params = array()) {

    $select = $this->select()->order('order ASC');
    if(empty($params['admin'])) {
      $select->where('enabled =?', 1);
    }
    if(!empty($params['notin']))
      $select->where('feeling_id !=?',1);
    if(!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
}
