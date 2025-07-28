<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Themes.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Model_DbTable_Themes extends Engine_Db_Table {  
  public function getTheme($params = array()){
      $select = $this->select();
      if(!empty($params['theme_id']))
        $select->where('theme_id =?',$params['theme_id']);      
      return $this->fetchAll($select);
  }
      
}
