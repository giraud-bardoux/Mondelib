<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Aouthtokens.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Model_DbTable_Aouthtokens extends Engine_Db_Table {
	public function check($code,$params = array()){
      $playform = defined('_SESAPI_PLATFORM_SERVICE') ? _SESAPI_PLATFORM_SERVICE : $_GET['sesapi_platform'];
    $select = $this->select()
								->from($this->info('name'),'*')
                ->where('platform =?',$playform)
                ->limit(1);
   // if(!empty($code))
      $select = $select->where('token =?',$code);
		return $this->fetchRow($select);	
	}
}
