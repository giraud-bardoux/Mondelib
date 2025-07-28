<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Menus.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Model_DbTable_Menus extends Engine_Db_Table {

  protected $_rowClass = 'Sesapi_Model_Menu';
	
  public function getMenus($param = array()) {
    $select = $this->select()
                   ->from($this->info('name'));
    if(!empty($param['status']))
      $select->where('status =?',$param['status']);
    $select->where('device =?',$param['device']);
    $select->order("order ASC");
    return $this->fetchAll($select);
  }
}