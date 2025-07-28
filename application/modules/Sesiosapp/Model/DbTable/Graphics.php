<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Graphics.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sesiosapp_Model_DbTable_Graphics extends Engine_Db_Table {
	protected $_rowClass = "Sesiosapp_Model_Graphic";
  public function getGraphics($status = false,$params=array()) {
    $tableName = $this->info('name');
    $select = $this->select();
    $select->from($tableName);
	  $select ->order('order ASC');
	  if($status)
			$select = $select->where('status = 1');
    if(!empty($params['fetchAll'])){
      return $this->fetchAll($select);  
    }
    return Zend_Paginator::factory($select);
  }
}
