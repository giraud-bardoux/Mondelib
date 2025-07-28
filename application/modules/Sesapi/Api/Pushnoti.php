<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Pushnoti.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
// Server file
class Sesapi_Api_Pushnoti extends Core_Api_Abstract
{
  // Sends Push notification for Android users
	public function android($data, $reg_id,$userInfo = array(),$id = 0) {
      $tokens = array();
      foreach($reg_id as $token){
          $tokens[] = $token->device_uuid;
      }
      $url = 'https://fcm.googleapis.com/fcm/send';
      if(!empty($data['description']))
        $description = $data['description'];
      else
        $description = " ";
      if($id){
        $message = array(
            'title' => $data['title'],
            'body' => $description,
            // 'msgcnt' => 1,
            // 'vibrate' => 1,
            // 'sound'=>'default',
            // "content_available"=> true
        );
        if(engine_count($userInfo)>0){
            $message = array_merge($message,array('userInfo'=>json_encode($userInfo)));
        }else{
            $message = array_merge($message,array('userInfo'=>"{}"));
        }
      }else{
        $message = array(
          'title' => $data['title'],
          'body' => $description,
        );
        if(engine_count($userInfo)>0){
            $message = array_merge($message,array('userInfo'=>json_encode($userInfo)));
        }else{
            $message = array_merge($message,array('userInfo'=>"{}"));
        }
      }
      
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $API_ACCESS_KEY =  $settings->getSetting('sesandroidapp_server_key', 0);
      if($id)
       $API_ACCESS_KEY =  $settings->getSetting('sesiosapp_server_key', 0);
      if(!$API_ACCESS_KEY){
        return false;
      }
      if($id == 1){
        // $headers = array(
        //   'Authorization:key=' .$API_ACCESS_KEY,
        //   'Content-Type: application/json'
        // );

        $token = $this->getFCMToken(1);
        $authConfigString = $settings->getSetting('sesiosapp_server_key');

        // Parse service account details
        $authConfig = json_decode($authConfigString);

        // Read private key from service account details
        $secret = openssl_get_privatekey($authConfig->private_key);
        if(!$token) return;
        $headers = array(
          'Authorization: Bearer ' .$token,
          'Content-Type: application/json'
        );
        $url = 'https://fcm.googleapis.com/v1/projects/'.$authConfig->project_id.'/messages:send';
        
      }else{
        $token = $this->getFCMToken(2);
        $authConfigString = $settings->getSetting('sesandroidapp_server_key');

        // Parse service account details
        $authConfig = json_decode($authConfigString);

        // Read private key from service account details
        $secret = openssl_get_privatekey($authConfig->private_key);
        if(!$token) return;
        $headers = array(
          'Authorization: Bearer ' .$token,
          'Content-Type: application/json'
        );
        $url = 'https://fcm.googleapis.com/v1/projects/'.$authConfig->project_id.'/messages:send';
      }

      foreach($tokens as $array){
        if(!$id){
          // $fields = array(
          //     'token' => $array,
          //     'priority' => 10,
          //     'data' => $message,
          //     //'notification' => $message,
          // );
          $fields = array("message"=>array(
            'token' => $array,
            'data' => $message,
            // 'notification' => $message,
          ));
        }else{
          $fields = array("message"=>array(
            'token' => $array,
            'data' => $message,
            'notification' => array(
              "title" => $message["title"],
              "body" => $message["body"],
            ),
          ));
          // $fields = array(
          //   'to' => $array,
          //   'priority' => 10,
          //   'data' => $message,
          //   'notification' => $message,
          // );
        }
        $this->useCurl($url, $headers,json_encode($fields), $id,$array);
      }
      
      return true;
 	}
  // Sends Push notification for iOS users
	public function iOS($data, $deviceToken,$userInfo = array()) {  
	  return $this->android($data, $deviceToken,$userInfo,1);
	}
	// Curl 
	private function useCurl($url, $headers, $fields = null,$id = false,$token) {
    // Open connection
    $ch = curl_init();
    if ($url) {
      // Set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch,CURLOPT_TIMEOUT,10);
      // Disabling SSL Certificate support temporarly
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      if ($fields)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      // Execute post
      $result = curl_exec($ch);
      // Close connection
      curl_close($ch);
      return $result;
    }
  }

  function base64UrlEncode($text)
  {
      return str_replace(
          ['+', '/', '='],
          ['-', '_', ''],
          base64_encode($text)
      );
  }

  function getFCMToken($type = 2){
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if($type == 2){
      $tokenfcm = $settings->getSetting('sesandroidapp_tokenfcm', '');
      $tokentime = $settings->getSetting('sesandroidapp_tokentime', '');
      // Read service account details
      $authConfigString = $settings->getSetting('sesandroidapp_server_key');
    }else{
      $tokenfcm = $settings->getSetting('sesiosapp_tokenfcm', '');
      $tokentime = $settings->getSetting('sesiosapp_tokentime', '');
      // Read service account details
      $authConfigString = $settings->getSetting('sesiosapp_server_key');
    }
    if(!empty($tokenfcm)){
      if($tokentime > time() - (50*60)){
        return $tokenfcm;
      }
    }
    
    
    // Parse service account details
    $authConfig = json_decode($authConfigString);
    // Read private key from service account details
    $secret = openssl_get_privatekey($authConfig->private_key);

    // Create the token header
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'RS256'
    ]);

    // Get seconds since 1 January 1970
    $time = time();

    // Allow 1 minute time deviation between client en server (not sure if this is necessary)
    $start = $time - 60;
    $end = $start + 3600;

    // Create payload
    $payload = json_encode([
        "iss" => $authConfig->client_email,
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://oauth2.googleapis.com/token",
        "exp" => $end,
        "iat" => $start
    ]);

    // Encode Header
    $base64UrlHeader = $this->base64UrlEncode($header);

    // Encode Payload
    $base64UrlPayload = $this->base64UrlEncode($payload);
    
    // Create Signature Hash
    $result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $secret, OPENSSL_ALGO_SHA256);

    // Encode Signature to Base64Url String
    $base64UrlSignature = $this->base64UrlEncode($signature);

    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    
    //-----Request token, with an http post request------
    $options = array('http' => array(
        'method'  => 'POST',
        'content' => 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion='.$jwt,
        'header'  => "Content-Type: application/x-www-form-urlencoded"
    ));
    $context  = stream_context_create($options);
   
    $responseText = file_get_contents("https://oauth2.googleapis.com/token", false, $context);
    
    $response = json_decode($responseText);
    if($type == 2){
      if(!empty($response->access_token)){
        if($settings->getSetting('sesandroidapp.tokenfcm')){
          $settings->removeSetting('sesandroidapp.tokenfcm');
          $settings->setSetting('sesandroidapp.tokenfcm', $response->access_token);
          
        }
        if($settings->getSetting('sesandroidapp.tokentime')){
          $settings->removeSetting('sesandroidapp.tokentime');
          $settings->setSetting('sesandroidapp.tokentime', time());
        }
        return $response->access_token;
      }
    }else{
      
      if(!empty($response->access_token)){
        if($settings->getSetting('sesiosapp.tokenfcm')){
          $settings->removeSetting('sesiosapp.tokenfcm');
          $settings->setSetting('sesiosapp.tokenfcm', $response->access_token);
          
        }
        if($settings->getSetting('sesiosapp.tokentime')){
          $settings->removeSetting('sesiosapp.tokentime');
          $settings->setSetting('sesiosapp.tokentime', time());
        }
        return $response->access_token;
      }
    }
  }


}
?>
