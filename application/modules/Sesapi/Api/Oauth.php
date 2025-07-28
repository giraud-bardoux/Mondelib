<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Oauth.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Api_Oauth extends Core_Api_Abstract {
  //random generate
  protected $_length = 16;
	public function getAuthRandomString(){
        $length = $this->_length;
        // random string
        $az = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $azr = rand(0, 181);
        $azs = substr($az, $azr, 10);
        $stamp = hash('sha256', time());
        $mt = hash('sha256', mt_rand(5, 20));
        $alpha = hash('sha256', $azs);
        $hash = str_shuffle($stamp . $mt . $alpha);
        return ucfirst(substr($hash, $azr, $length));
	}
	//random Oauth id generator
	public function generateOauthToken()
	{
    $givenToken = !empty($_REQUEST['auth_token']) ? $_REQUEST['auth_token'] : "";
    $table = Engine_Api::_()->getDbTable('aouthtokens', 'sesapi');
    if($givenToken){
        $tokenResult = $table->check($givenToken);
        if($tokenResult){
          $token = $tokenResult->token;
          $tokenResult->delete();  
        }
    }
    if(empty($token)){
      $token = $this->getAuthRandomString().time();
      $check = 0;
      do {
        //check existance
        $check =  $table->check($token);
        if($check)
          $token = $this->getAuthRandomString().time();
      } while ($check != 0);
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer->getIdentity() && !empty($_POST['device_udid'])){
       //remove previous token if any for particular device
      $deviceUdidToken = $db->query('SHOW COLUMNS FROM engine4_sesapi_aouthtokens LIKE \'device_udid\'')->fetch();
      if (empty($deviceUdidToken)) {
        $db->query('ALTER TABLE  `engine4_sesapi_aouthtokens` ADD  `device_udid` VARCHAR( 255 ) NULL DEFAULT "";');
      }
       //remove previous app token
       $table->delete(array('platform'=>_SESAPI_PLATFORM_SERVICE,'device_udid'=>$_POST['device_udid']));
    }    
    $aouthTokenColumn = $db->query('SHOW COLUMNS FROM engine4_sesapi_aouthtokens LIKE \'device_id\'')->fetch();
    if (empty($aouthTokenColumn)) {
      $db->query('ALTER TABLE  `engine4_sesapi_aouthtokens` ADD  `device_id` TINYINT( 1 ) NOT NULL DEFAULT "0";');
    }
    $row = $table->createRow();
    $row->token = $token;
    $row->creation_date = date('Y-m-d H:i:s');
    $row->save();
    $row->platform = _SESAPI_PLATFORM_SERVICE;
    
    $row->device_id = _SESAPI_PLATFORM_SERVICE;
		return $row;
	}
  public function revokeToken($token = ""){
    Engine_Api::_()->getDbTable('aouthtokens','sesapi')->delete(array(
                    'token = ?' => $token
             ));
    return true;
  }
  public function validateToken(){
    $token = $_REQUEST['auth_token'];
    if(!$token)
      return '';
     // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'invalid_token','result'=>array()));
    
    $check =  Engine_Api::_()->getDbTable('aouthtokens', 'sesapi')->check($token);
   
    if(!$check){
      return "";
        //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'token_not_validate','result'=>array()));
    }
    if($check->revoked)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'token_revoked','result'=>array()));
    //return current user id
    return $check->user_id;  
  }
  
    
}
