<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Block.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Codes extends Engine_Db_Table {

  public function isExist($code, $email, $type = '') {
    
    $select = $this->select()
                ->from($this->info('name'), 'code_id')
                ->where('code =?',$code)
                ->where('email =?',$email);
                
    if(!empty($type))
      $select->where('type =?', $type);

    return $select->order('code_id DESC')
                ->limit(1)
                ->query()
                ->fetchColumn();
  }
  
  public function isEmailExist($email) {
    $select = $this->select()
              ->from($this->info('name'))
              ->where('email =?', $email)
              ->limit(1);
    return $this->fetchRow($select);
  }
  
  function generateCode($user, $email = '', $type = "login") {
  
    $response['error'] = 0;
    $response['message'] = "";
    $response['code'] = "";
    $translate = Zend_Registry::get('Zend_Translate');
    if(!empty($user) && is_int($user)) {
      $user = Engine_Api::_()->getItem('user', $user);
    }
    if(!$user && !engine_in_array($type, array('signup', 'forgot'))){
      $response['message'] = $translate->translate("Invalid User");
      $response['error'] = 1;
      return $response;
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');

    //get latest row
    $select = $this->select()->where('email = ?', $email)->where('type =?',$type);
    $row = $this->fetchRow($select);
    
    $resendAttemps = 5;
    $resend_count = 0;
    //curent time

    $creationDate = date('Y-m-d H:i:s');
    if($row && !empty($resendAttemps)) {
    
      $resend_count = $row->resend_count;
      $modifiedDate = $row->modified_date;
      $creationDate = $row->creation_date;

      if( $resend_count >= $resendAttemps ) {
        //check block duration
        $blockDuration = 1800;
        $lastRequestedTime = time() - strtotime($modifiedDate);
        if( $blockDuration > $lastRequestedTime ) {
          $blocktime = $blockDuration - $lastRequestedTime;
          $pendingtime = Engine_Api::_()->getApi('otp', 'core')->secondsToTime($blocktime);
          $response['error'] = 1;
          $response['message'] = sprintf($translate->translate('You have reached limit of attempts via OTP. Please wait for %s and try again.'), $pendingtime);
          return $response;
        }
      }
      
      //check reset time
      $reset_attempt = 1800;
      $wait = time() - strtotime($creationDate);
      if( $reset_attempt < $wait ) {
        $resend_count = 0;
        $creationDate = date('Y-m-d H:i:s');
      }      
    }
      
    //delete old record of user
    $this->delete(array(
      'email = ?' => $email,
      'type = ?' => $type
    ));    
    
    //generate code
    $code = Engine_Api::_()->getApi('otp', 'core')->generateCode();

    //inser new record
    $this->insert(array(
      'email' => $email,
      'code' => $code,
      'creation_date' => $creationDate,
      'modified_date' => date('Y-m-d H:i:s'),
      'resend_count' => $resend_count + 1,
      'type' => $type,
    ));
    
    $response['error'] = 0;
    $response['code'] = $code;
    return $response;
  }
}
