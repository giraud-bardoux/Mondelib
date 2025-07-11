<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Filterlists.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Filterlists extends Engine_Db_Table {

  protected $_rowClass = 'Activity_Model_Filterlist';
  
  public function getLists($notArray = ''){
    $select = $this->select()->where('active =?',1)->order('order ASC');
    if($notArray)
      $select->where('filtertype NOT IN(?)',implode(',',$notArray));
    return $this->fetchAll($select);  
  }

  public function isFilterExists($params = array())
  {
    return $this->select()
              ->from($this->info('name'), 'filterlist_id')
              ->where('filtertype =?', $params['filtertype'])
              ->query()
              ->fetchColumn();
  }
  
}
