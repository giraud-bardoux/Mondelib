<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: OtpController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_OtpController extends Core_Controller_Action_Standard {

  public function sendotpAction() {
    
    $user_id = $this->_getParam('user_id', '');
    $email = $this->_getParam('email');
    $country_code = $this->_getParam('country_code');
    $country_code = explode('_', $country_code);
    $country_code = $country_code[0];
    $codeTable = Engine_Api::_()->getDbTable('codes', 'user');
    $type = $this->_getParam('type', 'signup');
    
    if(!empty($user_id)) {
      $user = Engine_Api::_()->getItem('user', $user_id);
    }
    
    if(is_numeric($email)) {
      if(empty($email)) {
        echo json_encode(array('status' => false, 'message' => $this->view->translate("Please enter phone number.")));die;
      } else {
        //Send code on mobile number
        $phone_number = "+".$country_code . $email;

        $codes = Engine_Api::_()->getDbTable('codes','user');
        $response = $codes->generateCode($user_id, $email, $type);
        if(!empty($response['error'])){
          echo json_encode(array('status' => false, 'error' => 1, 'message' => $response['message']));die;  
        }
        $code = $response['code'];

        //send code to mobile
        Engine_Api::_()->getApi('otp', 'core')->sendMessage($phone_number, $code, $type);
        
        if($type == 'deleteaccount') {
          if(!empty($user_id) && !empty($user->email)) {
          
            //send code to mobile
            $row = $codeTable->createRow();
            $row->email = $user->email;
            $row->code = $code;
            $row->creation_date = date('Y-m-d H:i:s');
            $row->modified_date = date('Y-m-d H:i:s');
            $row->save();
            
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'user_deleteotp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
          }
        }
        echo json_encode(array('status' => true, 'message' => $this->view->translate("Code Sent Successfully."), 'timerdata' => Engine_Api::_()->getApi('otp', 'core')->getOtpExpire()));die;
      }
    } else {
      if(empty($email)) {
        echo json_encode(array('status' => false, 'message' => $this->view->translate("Please enter email address.")));die;
      } else {
        //$email = $_POST[$this->getForm()->getEmailElementFieldName()];
        
        $isEmailExist = $codeTable->isEmailExist($email);
        if($isEmailExist) {
          $isEmailExist->delete();
        }
        $code = rand(100000, 999999);
        $row = $codeTable->createRow();
        $row->email = $email;
        $row->code = $code;
        $row->creation_date = date('Y-m-d H:i:s');
        $row->modified_date = date('Y-m-d H:i:s');
        $row->save();
        
        if($type == 'deleteaccount') {
          
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_deleteotp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
          
          if(!empty($user_id) && !empty($user->phone_number) && !empty($user->country_code)) {
            //send code to mobile
            $row = $codeTable->createRow();
            $row->email = $user->phone_number;
            $row->code = $code;
            $row->creation_date = date('Y-m-d H:i:s');
            $row->modified_date = date('Y-m-d H:i:s');
            $row->save();
            
            $phone_number = "+".$user->country_code . $user->phone_number;
            Engine_Api::_()->getApi('otp', 'core')->sendMessage($phone_number, $code, $type);
          }
        } else {
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_otp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
        }
        echo json_encode(array('status' => true, 'message' => $this->view->translate("Code Sent Successfully.")));die;
      }
    }
  }
  
  public function validateotpAction() {
  
    $email = $this->_getParam('email');
    $type = $this->_getParam('type', 'signup');
    
    if(is_numeric($email) && empty($email)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("You have not enter phone number.")));die;
    } else if(empty($email)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("You have not enter email address.")));die;
    }
      
    $inputcode = $this->_getParam('code');
    if(empty($inputcode)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("Please enter a valid code.")));die;
    }
    
    if(is_numeric($email) && !empty($email)) {
      $code_id = Engine_Api::_()->getDbTable('codes', 'user')->isExist($inputcode, $email, $type);
    } else {
      $code_id = Engine_Api::_()->getDbTable('codes', 'user')->isExist($inputcode, $email);
    }
    if(empty($code_id)) {
      echo json_encode(array('status' => false, 'message' => $this->view->translate("The security code you have entered is not correct. Please check your code and try again.")));die;
    } else {
      $code = Engine_Api::_()->getItem('user_code', $code_id);
      $code->delete();
      $_SESSION['isValidCode'] = $email; //'validotp';
      echo json_encode(array('status' => true, 'message' => $this->view->translate("The code you entered is valid.")));die;
    }
  }
  
  public function phonenumberexistsAction() {
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $phone_number = $this->_getParam('phone_number', null);
    $country_code = explode('_', $this->_getParam('country_code', null));
    $param = $this->_getParam('param', null);
    
    if(isset($_SESSION['isValidCode']) && !empty($_SESSION['isValidCode'])) {
      unset($_SESSION['isValidCode']);
    } 
    
    if(empty($phone_number)) {
      echo json_encode(array("error" => 1, 'message' => Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));die;
    } else if(!empty($phone_number)) {
      if(!empty($settings->getSetting('otpsms.signup.phonenumber', 0)) && is_numeric($phone_number)) {
        //silience
        if(empty($settings->getSetting('otpsms.signup.phonenumber', 0)) && !$this->isValidEmail($phone_number)) {
          echo json_encode(array("error" => 1, 'message' => Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));die;
        }
      } else if(!$this->isValidEmail($phone_number)) {
        if(!empty($param)) {
          echo json_encode(array("error" => 1, 'message' => Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));die;
        } else {
          echo json_encode(array("error" => 1, 'message' => Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.')));die;
        }
      }
    }
    
	$userID = 0;
	if(Engine_Api::_()->user()->getViewer()) {
	  $userID = Engine_Api::_()->user()->getViewer()->getIdentity();
	}
    $user = Engine_Api::_()->getDbTable('users', 'user')->isPhoneNumberExist($phone_number, $country_code[0], $userID);
    if(!empty($user)) {
      if(is_numeric($phone_number)) {
        echo json_encode(array("error" => 1,'message'=>Zend_Registry::get('Zend_Translate')->_('This phone number is already exists. Please use another one.')));die;
      } else {
        echo json_encode(array("error" => 1,'message'=>Zend_Registry::get('Zend_Translate')->_('This email address is already exists. Please use another one.')));die;
      }
    }
    echo json_encode(array('status' => true));die;
  }
    
  function isValidEmail($email) {
    return preg_match('/\A[a-z0-9]+([-._][a-z0-9]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,4}\z/', $email) && preg_match('/^(?=.{1,64}@.{4,64}$)(?=.{6,100}$).*/', $email);
  }
}
