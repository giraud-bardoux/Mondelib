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

class Core_OtpController extends Sesapi_Controller_Action_Standard {

  public function sendotpAction() {
    
    $user_id = $this->_getParam('user_id', '');
    $email = $this->_getParam('email');
    $country_code = $this->_getParam('country_code');
    $codeTable = Engine_Api::_()->getDbTable('codes', 'user');
    $type = $this->_getParam('type', 'signup');
    
    if(is_numeric($email)) {
      if(empty($email)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('Please enter phone number.')));
      } else {
        //Send code on mobile number
        $phone_number = "+".$country_code . $email;

        $codes = Engine_Api::_()->getDbTable('codes','user');
        $response = $codes->generateCode($user_id, $email, $type);
        if(!empty($response['error'])) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $response['message']));
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
        
        $result['message'] = $this->view->translate("Code Sent Successfully.");
        $result['expiretime'] = 300;
        $result['expiretext'] = 'Expire in ';
        //$result['timerdata'] =  Engine_Api::_()->getApi('otp', 'core')->getOtpExpire();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> '', 'result' => $result));
      }
    } else {
      if(empty($email)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->translate("Please enter email address.")));
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
        
        $result['message'] = $this->view->translate("Code Sent Successfully.");
        $result['error'] = '0';
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> '', 'result' => $result));
      }
    }
  }
  
  public function validateotpAction() {
  
    $email = $this->_getParam('email');
    $type = $this->_getParam('type', 'signup');
    
    if(is_numeric($email) && empty($email)) {
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->translate("You have not enter phone number.")));
    } else if(empty($email)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->translate("You have not enter email address.")));
    }
      
    $inputcode = $this->_getParam('code');
    if(empty($inputcode)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->translate("Please enter a valid code.")));
    }
    
    if(is_numeric($email) && !empty($email)) {
      $code_id = Engine_Api::_()->getDbTable('codes', 'user')->isExist($inputcode, $email, $type);
    } else {
      $code_id = Engine_Api::_()->getDbTable('codes', 'user')->isExist($inputcode, $email);
    }
    if(empty($code_id)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $this->view->translate("The code you entered is invalid. Please enter the correct code.")));
    } else {
      $code = Engine_Api::_()->getItem('user_code', $code_id);
      $code->delete();
      $_SESSION['isValidCode'] = $email; //'validotp';
      
      
      $result['message'] = $this->view->translate("The code you entered is valid.");
      $result['error'] = '0';
      $result['status'] = true;
      $result['verify_text'] = $this->view->translate('Verified');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> '', 'result' => $result));
    }
  }
  
  public function phonenumberexistsAction() {
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $phone_number = $this->_getParam('email', null);
    $country_code = $this->_getParam('country_code', null);
    
    if(empty($phone_number)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));
    } else if(!empty($phone_number)) {
      if(!empty($settings->getSetting('otpsms.signup.phonenumber', 0)) && is_numeric($phone_number)) {
        //silience
        if(empty($settings->getSetting('otpsms.signup.phonenumber', 0)) && !$this->isValidEmail($phone_number)) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));
        }
      } else if(!$this->isValidEmail($phone_number)) {
        if(!empty($param)) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('Please enter a valid phone number.')));
        } else {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('Please enter a valid email address.')));
        }
      }
    }

    $user = Engine_Api::_()->getDbTable('users', 'user')->isPhoneNumberExist($phone_number, $country_code);
    if(!empty($user)) {
      if(is_numeric($phone_number)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('This phone number is already exists. Please use another one.')));
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> Zend_Registry::get('Zend_Translate')->_('This email address is already exists. Please use another one.')));
      }
    }
    
    $result['verify_text'] = $this->view->translate('Verify');
    $result['error'] = 0;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '0','error_message'=> '', 'result' => $result));
  }
    
  function isValidEmail($email) {
    return preg_match('/\A[a-z0-9]+([-._][a-z0-9]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,4}\z/', $email) && preg_match('/^(?=.{1,64}@.{4,64}$)(?=.{6,100}$).*/', $email);
  }
}
