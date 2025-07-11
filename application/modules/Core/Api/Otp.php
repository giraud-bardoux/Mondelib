<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Otp.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
    
class Core_Api_Otp extends Core_Api_Abstract {
  
  protected $_expiretime;
  
  protected $_length;
  protected $_settings;
  protected $_translate;
  
  public function __construct()
  {
    $this->_translate = Zend_Registry::get('Zend_Translate');
    $this->_expiretime = (int) 300;
    $this->_length = 6;
    $this->_settings = Engine_Api::_()->getApi('settings', 'core');
  }

  function getDomain($url) {
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
      return $regs['domain'];
    }
    return false;
  }
  
  function amazon() {
    $settingsAmazon = (array) $this->_settings->otpsms_amazon;
    $clientId = isset($settingsAmazon['clientId']) ? $settingsAmazon['clientId'] : 0;
    $clientSecret = isset($settingsAmazon['clientSecret']) ? $settingsAmazon['clientSecret'] : 0;
    return !empty($clientId) && !empty($clientSecret);
  }
  
  function twillio() {
    $settingsTwillio = (array) $this->_settings->otpsms_twilio;
    $clientId = isset($settingsTwillio['clientId']) ? $settingsTwillio['clientId'] : 0;
    $phoneNumber = isset($settingsTwillio['phoneNumber']) ? $settingsTwillio['phoneNumber'] : 0;
    $clientSecret = isset($settingsTwillio['clientSecret']) ? $settingsTwillio['clientSecret'] : 0;
    return !empty($clientId) && !empty($phoneNumber) && !empty($clientSecret);
  }
  
  function message91() {
    $settings= (array) $this->_settings->otpsms_message91;
    $clientId = isset($settings['clientId']) ? $settings['clientId'] : 0;
    $senderId = isset($settings['senderId']) ? $settings['senderId'] : 0;
    $clientSecret = isset($settings['clientSecret']) ? $settings['clientSecret'] : 0;
    return !empty($clientId) && !empty($senderId) ;
  }
    
  function generateCode() {

    $length = $this->_length;
    
    $pass = str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
    do {
      $pass = str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
      //store code in table
      $forgotTable = Engine_Api::_()->getDbtable('codes', 'user');
      $forgotSelect = $forgotTable->select()
          ->where('code = ?', $pass);
      $forgotRow = $forgotTable->fetchRow($forgotSelect);
    } while( !empty($forgotRow) );

    return $pass;
  }
  
  function secondsToTime($seconds) {
    // extract hours
    $hours = floor($seconds / (60 * 60));
    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);
    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);
    // return the final array
    $string = "";
    if($hours > 0)
        $string .= $hours.($hours != 1 ? " hours " : " hour ");
    if($minutes > 0)
        $string .= $minutes.($minutes != 1 ? " minutes " : " minute ");
    if($seconds > 0)
        $string .= $seconds.($seconds != 1 ? " seconds " : " second ");
    return trim($string," ");
  }
  
  function sendMessage($phone_number, $code,$type = "signup", $customParams = array()){
    $message = $this->generateMessage($phone_number, $code, $type, $customParams);
    return $this->sendMessageCode($phone_number, $message, $code, false, $type);
  }

  function generateMessage($phone_number, $code, $type = "signup", $customParams = array()) {
  
    $expiretime = $this->_expiretime;
    
    $username = Engine_Api::_()->user()->getViewer()->getIdentity() ? Engine_Api::_()->user()->getViewer()->getTitle() : '';
    $website = $this->_settings->getSetting('core.general.site.title', '');

    if($type == 'signup') {
      $template = $this->_translate->translate('Use [code] to verify your registration. This code will get expired in [expirytime].');
    } else if($type == 'forgot') {
      $template = $this->_translate->translate('Use [code] for verification and reset your password. This code will get expired in [expirytime].');
    } else if($type == 'editnumber') {
      $template = $this->_translate->translate('Use [code] for verification and editing your phone number. This code will get expired in [expirytime].');
    } else if($type == 'addnumber') {
      $template = $this->_translate->translate('Use [code] for verification and adding your phone number. This code will get expired in [expirytime].');
    } else if($type == 'login') {
      $template = $this->_translate->translate('Use [code] for verification and login. This code will get expired in [expirytime].');
    } else if($type == 'deleteaccount') {
      $template = $this->_translate->translate('Use [code] for delete your account. This code will get expired in [expirytime].');
    }
    $timestring = $this->secondsToTime($expiretime);
    $message = str_replace(array_merge(array("[code]", "[website_name]","[expirytime]"), array_keys($customParams)), array_merge(array($code, $website, $timestring),$customParams), $template);
    
    return $message;
  }
  
  function sendMessageCode($phone_number,$message,$code,$user = 0,$type = "signup",$direct = false) {
  
    $service = $this->_settings->getSetting('otpsms.integration');

    if( $service == "twilio" && $this->twillio()) {
      $status = $this->sendMessageUsingTwilo($phone_number, $message,$code);
    } elseif( $service == "amazon"  && $this->amazon()) {
      $status = $this->sendMessageUsingAmazon($phone_number, $message,$code);
    } elseif( $service == "message91"  && $this->message91()) {
      $status = $this->sendMessageToMessage91($phone_number, $message,$code);
    }

    return $status;
  }
  
  function sendMessageToMessage91($phone_number, $message, $code = "") {
  
    $curl = curl_init();
    $settingsMessage = (array) $this->_settings->otpsms_message91;
    $authentication_key = $settingsMessage['clientId'];
    $template_id = $settingsMessage['clientSecret'];
    $senderId = $settingsMessage['senderId'];
    
    if(!empty($code)) {
      $params = ('authkey='.$authentication_key.'&template_id='.$template_id.'&extra_param={"OTP":"' . $code . '"}&mobile=' . $phone_number);
      $url = "https://api.msg91.com/api/v5/otp?" . $params;
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER => array(
          "content-type: application/json"
        ),
      ));
    } else {
      $params = array();
      $params['sender'] = $senderId;
      $params['route'] = 4;
      $number = explode('-',$phone_number);
      $params['country'] = str_replace('+',$number[0]);
      $params['sms'][0]['message'] = $message ;
      $params['sms'][0]['to'][0] = $number[1];

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.msg91.com/api/v2/sendsms",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER => array(
          "authkey: $authentication_key",
          "content-type: application/json"
        ),
      ));
    }
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return false;
    } else {
      return true;
    }
  }
  
  function sendMessageUsingTwilo($phone_number, $message,$code = "") {

    require_once APPLICATION_PATH . '/application/libraries/Twilio/autoload.php';
    
    $settingsTwillio = (array) $this->_settings->otpsms_twilio;
    $clientId = $settingsTwillio['clientId'];
    $clientSecret = $settingsTwillio['clientSecret'];
    $phoneNumber = $settingsTwillio['phoneNumber'];
    
    try {
      $client = new \Twilio\Rest\Client($clientId, $clientSecret);
      // Use the client to do fun stuff like send text messages!
      $client->messages->create(
        // the number you'd like to send the message to
        $phone_number, array(
          // A Twilio phone number you purchased at twilio.com/console
          'from' => $phoneNumber,
          // the body of the text message you'd like to send
          'body' => $message
        )
      );
      return true;
    } catch( \Twilio\Exceptions\RestException $e) {
      return false;
    }
  }
  
  function sendMessageUsingAmazon($phone_number, $message,$code = "") {
    
    require_once 'application/libraries/Aws/aws-autoloader.php';
    
    $settingsAmazon = (array) $this->_settings->otpsms_amazon;
    $clientId = $settingsAmazon['clientId'];
    $clientSecret = $settingsAmazon['clientSecret'];
    $region = $settingsAmazon['region'];

    try {
      $client = new \Aws\Sns\SnsClient([
        'credentials' => array(
          'key' => $clientId,
          'secret' => $clientSecret,
        ),
        'version' => '2010-03-31',
        'region' => $region,
      ]);
//       $client = SnsClient::factory([
//         'credentials' => [
//           'key'    => $clientId,
//           'secret' => $clientSecret,
//         ],
//         'region' => 'ap-southeast-1', //$config['region'],
//         'version' => '2010-03-31',
//       ]);

      // $array = array('attributes' => array('DefaultSenderID' => 'test', 'DefaultSMSType' => 'Transactional'));
      //$client->setSMSAttributes($array);
      $data = $client->publish(array(
        "SenderID" => "SenderName",
        "SMSType" => "Transational",
        'Message' => $message, // REQUIRED
        'PhoneNumber' => $phone_number,
        'Subject' => 'Test',
      ));
      return true;
    } catch( Exceptions $e ) {
      return false;
    }
  }

  public function getOtpExpire() {
  
    //set timer for login otp
    $expiretime = $this->_expiretime;
    $endtime = date('Y-m-d H:i:s',strtotime('+'.$expiretime.' seconds'));
    
    $time = $expiretime;
    $minutes = floor($time / 60);
    $time -= $minutes * 60;

    $seconds = floor($time);
    $time -= $seconds;

    $endtimeMin = ($minutes < 10 ? "0".$minutes : $minutes).':'.($seconds < 10 ? "0".$seconds : $seconds);

    return '<div id="timer">'.$this->_translate->translate('Expires in ') . "<span class='otpsms_timer_class' data-time='".$expiretime."' data-endtime='".$endtime."' data-created=''>".$endtimeMin."</span></div>";
  }
}
