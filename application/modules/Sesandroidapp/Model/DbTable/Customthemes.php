<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Customthemes.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sesandroidapp_Model_DbTable_Customthemes extends Engine_Db_Table {
	protected $_rowClass = "Sesandroidapp_Model_Customtheme";
  
  public function getThemeKey($params = array()){
      $select = $this->select();
      if(!empty($params['theme_id']))
        $select->where('theme_id =?',$params['theme_id']);
      if(!empty($params['column_key']))
        $select->where('column_key =?',$params['column_key']);
      if(!empty($params['customtheme_id']))
        $select->where('customtheme_id =?',$params['customtheme_id']);
      if(!empty($params['is_custom']))
        $select->where('is_custom =?',$params['is_custom']);
      return $this->fetchAll($select);
  }
      
}
