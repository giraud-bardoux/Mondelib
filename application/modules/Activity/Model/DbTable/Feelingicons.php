<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Fellingicons.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Feelingicons extends Engine_Db_Table {
  
  protected $_rowClass = 'Activity_Model_Feelingicon';
  
  public function getPaginator($params = array()) {
  
    return Zend_Paginator::factory($this->getFeelingicons($params));
  }
  
  public function getFeelingicons($params = array()) {
  
    $select = $this->select()->order('order ASC');
    
    if(!empty($params['limit'])){
      $select->limit($params['limit']);
    }
    if(!empty($params['search']))
       $select->where('title LIKE ("%'.$params['search'].'%")');

    $select->where('feeling_id =?',$params['feeling_id']);
    if(!empty($params['fetchAll'])){
      return $this->fetchAll($select);  
    }
    
    return $select;
  }
  
  public function getFeelingIconExist($params = array()) {
  
    return $this->select()
          ->from($this->info('name'), array('feelingicon_id'))
          ->where('title =?', $params['title'])
          ->query()
          ->fetchColumn();
  }
}
