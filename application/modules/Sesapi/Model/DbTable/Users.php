<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Users.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_Model_DbTable_Users extends Engine_Db_Table {
  public function getToken($user_id = 0,$platform = 1){
      if(!$user_id)
        return array();
      
      $select = $this->select()->from($this->info('name'),'*')->where('resource_id =?',$user_id);
      if($platform == 2){
        $select->where('device_id = "2"');
      }else{
        $select->where('device_id = "'.$platform.'"');
      }
      return $this->fetchAll($select);
  }
  function getTokens($params = array(),$platform = 1){    
    $select = $this->select()->from($this->info('name'),'*');;
    if(!empty($params['user_id']))
      $select->where('user_id =?',$params['resource_id']);
    if(!empty($params['level'])){
      $user = Engine_Api::_()->getItemTable('user')->info('name');
      $select->setIntegrityCheck(false)
            ->joinLeft($user,$user.'.user_id = '.$this->info('name').'.resource_id',null)
            ->where($user.'.level_id =?',$params['level'])
            ->where($this->info('name').'.resource_id !=?','0');  
    }else if(!empty($params['network'])){
      $network = 'engine4_network_membership';
      $select->setIntegrityCheck(false)
            ->joinLeft($network,$network.'.user_id = '.$this->info('name').'.resource_id',null)
            ->where($network.'.resource_id =?',$params['network'])
            ->where($this->info('name').'.resource_id !=?','0')
            ->where($network.'.active =?',1)
             ->where($network.'.resource_approved =?',1)
              ->where($network.'.user_approved =?',1);  
    }else if(!empty($params['user_ids'])){
      $select->where('resource_id IN('.$params['user_ids'].')');  
    }else if(!empty($params['browser']))
      $select->where('browser =?',$params['browser']);
    else if(!empty($params['aouthtoken_id']))
      $select->where('user_id =?',$params['aouthtoken_id']);
    if(!empty($params['platform']))
      $select->where('device_id = "'.$params['platform'].'"');
    else
      $select->where('device_id =?',1); 
    $result = $this->fetchAll($select);
    return $result;  
  }
	public function register($params = array()){
    if(empty($params['device_uuid']))
      return ;
      if (!isset($params['user_id']) && empty($params['user_id']))
        $params['user_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
     $this->delete(array(
          'device_uuid = ?' => $params['device_uuid']
     ));
    //insert
    $row = $this->createRow();
    $row->device_uuid = $params['device_uuid'];
    $row->device_id = _SESAPI_PLATFORM_SERVICE;
    $row->resource_id = $params['user_id'];
    $row->save();
    return $row;
	}
}