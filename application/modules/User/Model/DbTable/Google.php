<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Google.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Google extends Engine_Db_Table {

  protected $_api;

  public static function getGoogleInstance() {
    return Engine_Api::_()->getDbTable('google', 'user')->getApi();
  }
  
  public function enable() {

    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientsecret','');
    
    $enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.enable',0);
    if( empty($settings['google_client']) || empty($settings['google_secret']) || !$enable) {
      return false;
    }
    return true;
  }
  
  public function getApi() {
  
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Need to initialize
    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientsecret','');
    $settings['google_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.enable',0);
    
    if( empty($settings['google_client']) || empty($settings['google_secret']) || empty($settings['google_enable'])) {
      $this->_api = null;
      Zend_Registry::set('Google_Api', $this->_api);
      return false;
    }
    
    // Try to log viewer in?
    if (!empty($_SESSION['core_google'])) {
    
      $_SESSION['google_lock'] = true;
      
      $inst_uid = Engine_Api::_()->getDbtable('google', 'user')->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
      
      if($inst_uid) {
      
       $postBody = 'client_id='.urlencode($settings['google_client'])
              .'&client_secret='.urlencode($settings['google_secret'])
              .'&refresh_token='.urlencode($inst_uid->access_token)
              .'&grant_type=refresh_token';
        
        $siteURL = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']).Zend_Registry::get('StaticBaseUrl').'core/auth/google';
        $curl = curl_init();
        curl_setopt_array( $curl,
          array( CURLOPT_CUSTOMREQUEST => 'POST'
          , CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/token'
          , CURLOPT_HTTPHEADER => array( 'Content-Type: application/x-www-form-urlencoded'
                                        , 'Content-Length: '.strlen($postBody)
                                        , 'User-Agent: HoltstromLifeCounter/0.1 +http://holtstrom.com/michael'
                                        )
          , CURLOPT_POSTFIELDS => $postBody                              
          , CURLOPT_REFERER => $siteURL
          , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
          , CURLOPT_TIMEOUT => 12 // max seconds to wait
          , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
          , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
          , CURLOPT_SSL_VERIFYPEER => 1 // do verify the SSL of CURLOPT_URL, this is for security
          , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
          ) 
        );
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);  
    
        if (strlen($response) < 1)
          return false;
    
        $response = json_decode($response, true); // convert returned objects into associative arrays
        $expires = time() - 60 + (int) ($response['expires_in']);
        if ( empty($response['access_token']) || $expires <= time() )
        { return false; }
        return $response['access_token'];
        // store the updated token/expiry in your db
        // pass our the updated token for use
      }
    } 
    else
     $_SESSION['google_lock']  = '';

    return $this->_api;
  }
  
  public function isConnected(){
    // Need to initialize
    $settings['google_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientid','');
    $settings['google_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientsecret','');
    $settings['google_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.enable',0);
    if( empty($settings['google_client']) || empty($settings['google_secret']) || empty($settings['google_enable'])) 
      return false;
    return true;
  }
  
  /**
   * Generates the button used for Google Connect
   *
   * @param mixed $fb_params A string or array of Google parameters for login
   * @param string $connect_with_facebook The string to display inside the button
   * @return String Generates HTML code for facebook login button
   */
  public static function loginButton($connect_text = 'Connect with Google') {

    $href = Zend_Controller_Front::getInstance()->getRouter()
        ->assemble(array('module' => 'user', 'controller' => 'auth',
          'action' => 'google'), 'default', true);
    return '
      <a href="'.$href.'" class="social_login_btn google_login_btn ajaxPrevent">
        <i><svg viewBox="0 0 256 262" id="google"><path fill="#4285F4" d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"></path><path fill="#34A853" d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"></path><path fill="#FBBC05" d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782"></path><path fill="#EB4335" d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"></path></svg></i><span>Login with Google</span></a>
    ';
  }
  
  public static function signup(User_Form_Account $form)
  {
    
  }
}
