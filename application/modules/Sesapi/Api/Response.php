<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesapi
 * @package    Sesapi
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Response.php  2018-08-14 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesapi_Api_Response extends Core_Api_Abstract
{
  public function sendResponse($params = array(),$user_subscription_id = 0,$fastcgi_finish_request = false)
  {
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(!empty($params['result']) && !is_string($params['result']) && (!empty($params['result']['lastlogin_ip']) || !empty($params['result']['creation_ip']))){
      unset($params['result']['lastlogin_ip']);
      unset($params['result']['creation_ip']);
    }
    $data = array(
            'result' => !empty($params['result']) ? $params['result'] : array(),
        );
    if(!empty($params['error'])){
      $data['error'] = $params['error'];
      $data['message'] = $params['error_message'];
      $data['error_message'] = $view->translate($this->validationMessage($params['error_message']));
    }
    /*
     send loggedin user id
      0 for logged out user
    */
   if(!is_scalar($data['result']) && !is_string($params['result']) && !is_string($data['result']))
       $data['result']['loggedin_user_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    else if(is_string($params['result']) && $params['result'] != ""){
       $params['result'] = $view->translate($params['result']);
    }
    if($user_subscription_id){
        $data['user_subscription_id'] = $user_subscription_id;
    }
    if(!empty($params['token'])){
      $data['aouth_token'] = $params['token'];  
    }
    if(session_id())
      $data['session_id'] = session_id();

    if(!empty($_SESSION['requirepassword'] )) {
        $data['change_password_mandatory'] = true;
    }

    if(!empty($params['pagging']))
    {  
      $data['result']['total_page'] = $params['pagging']['total_page']; 
      $data['result']['current_page'] = $params['pagging']['current_page'];
      $data['result']['next_page'] = $params['pagging']['next_page'];
      if(!empty($params['pagging']['total']))
      $data['result']['total'] = $params['pagging']['total'];
        if(!empty($params['pagging']['moduleName']))
            $data['result']['moduleName'] = $params['pagging']['moduleName'];
      
    }
    
    //convert array to utf8
   // $data = $this->array_utf8_encode($data);
   //JSON_INVALID_UTF8_IGNORE add this for UTF 8 error
    $data = str_replace('<null>','',json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    echo $data = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $data);
    if($fastcgi_finish_request){
      if (is_callable('fastcgi_finish_request')) {
        fastcgi_finish_request();
        return;
      }else{
        die;
      }
    }else{
      die;
    }
    echo Zend_Json::encode($data);die;
  }  
  public function array_utf8_encode($dat)
  {       
      if (is_string($dat))
          return utf8_encode($dat);
      if (!is_array($dat))
          return $dat;
      $ret = array();
      foreach ($dat as $i => $d)
          $ret[$i] = self::array_utf8_encode($d);
      return $ret;
  }
  public function validationMessage($message = ''){
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
     switch($message){
       case 'parameter_missing':
        return $view->translate("Required Parameters missing.");
       case 'facebook_login_not_enabled':
        return $view->translate('Facebook Login Currently disabled.');
       case 'database_error':
        return $view->translate('Database error, please try again later.');
       case 'validation_error':
        return $view->translate('Please provide all mandatory fields.');
       break;  
       case 'user_not_autheticate':
        return $view->translate('Invalid User');
       break;
       case 'upload_limit_reach':
        return $view->translate("Upload Limit Reach");
       break;
       case 'uploading_error':
        return $view->translate("Error Uploading file");
       break;
       case 'permission_error':
         return $view->translate("You don't have permission to access the resource.");
       break;
       case 'validation_fail':
         return $view->translate("Form Validation Fail");
       break;
       case 'token_revoked':
         return $view->translate("Your access token revoked by admin. Please contact admin for further details.");
       break;
       case "custom_url_taken":
        return $view->translate("Custom Url already taken, choose different.");
        break;
       case "invalid_request":
        return $view->translate("form method inavalid.");
        break;
     }
     return $message;
  }
}
