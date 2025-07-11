<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Facebook.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Facebook extends Engine_Db_Table
{
  protected $_api;

  public static function getFBInstance()
  {
    return Engine_Api::_()->getDbtable('facebook', 'user')->getApi();
  }

  public function getApi()
  {
    // Already initialized
    if( null !== $this->_api ) {
      return $this->_api;
    }

    // Need to initialize
    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    if( empty($settings['secret']) || empty($settings['appid'])) {
      $this->_api = null;
      Zend_Registry::set('Facebook_Api', $this->_api);
      return false;
    }

    $this->_api = new Facebook_Api(array(
      'appId'  => $settings['appid'],
      'secret' => $settings['secret'],
      'cookie' => false, // @todo make sure this works
      'baseDomain' => $_SERVER['HTTP_HOST'],
    ));
    Zend_Registry::set('Facebook_Api', $this->_api);

    // Try to log viewer in?
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !isset($_SESSION['facebook_uid']) ||
        @$_SESSION['facebook_lock'] !== $viewer->getIdentity() ) {
      $_SESSION['facebook_lock'] = $viewer->getIdentity();
      if( $this->_api->getUser() ) {
        $_SESSION['facebook_uid'] = $this->_api->getUser();
      } else if( $viewer && $viewer->getIdentity() ) {
        // Try to get from db
        $info = $this->select()
            ->from($this)
            ->where('user_id = ?', $viewer->getIdentity())
            ->query()
            ->fetch();
        if( is_array($info) && !empty($info['facebook_uid']) &&
            !empty($info['access_token']) && !empty($info['code']) ) {
          $_SESSION['facebook_uid'] = $info['facebook_uid'];
          $this->_api->setPersistentData('code', $info['code']);
          $this->_api->setPersistentData('access_token', $info['access_token']);
        } else {
          // Could not get
          $_SESSION['facebook_uid'] = false;
        }
      } else {
        // Could not get
        //$_SESSION['facebook_uid'] = false;
      }
    }
    
    return $this->_api;
  }

  public function isConnected()
  {
    if( ($api = $this->getApi()) ) {
      return (bool) $api->getUser();
    } else {
      return false;
    }
  }

  public function checkConnection(User_Model_User $user = null)
  {
    if( null === $user ) {
      $user = Engine_Api::_()->user()->getViewer();
    }
    try {
      $this->getApi()->api('/me');
      $fb_uid = Engine_Api::_()->getDbtable('facebook', 'user')
          ->fetchRow(array('user_id = ?' => $user->getIdentity()));
    } catch( Exception $e ) {
      return false;
    }
    
    if( !$fb_uid || !$fb_uid->facebook_uid || $fb_uid->facebook_uid != $this->getApi()->getUser() ) {
      return false;
    } else {
      return true;
    }
  }
  
  /**
   * Generates the button used for Facebook Connect
   *
   * @param mixed $fb_params A string or array of Facebook parameters for login
   * @param string $connect_with_facebook The string to display inside the button
   * @return String Generates HTML code for facebook login button
   */
  public static function loginButton($connect_text = 'Connect with Facebook')
  {
    $settings  = Engine_Api::_()->getApi('settings', 'core');
    $facebook  = self::getFBInstance();

    if( !$facebook ) {
      return;
    }

    $href = Zend_Controller_Front::getInstance()->getRouter()
        ->assemble(array('module' => 'user', 'controller' => 'auth',
          'action' => 'facebook'), 'default', true);
    return '
      <a href="'.$href.'" class="social_login_btn facebook_login_btn ajaxPrevent">
        <i><svg xmlns="http://www.w3.org/2000/svg" fill="#fff" x="0px" y="0px" width="32" height="32" viewBox="0 0 50 50">
        <path d="M32,11h5c0.552,0,1-0.448,1-1V3.263c0-0.524-0.403-0.96-0.925-0.997C35.484,2.153,32.376,2,30.141,2C24,2,20,5.68,20,12.368 V19h-7c-0.552,0-1,0.448-1,1v7c0,0.552,0.448,1,1,1h7v19c0,0.552,0.448,1,1,1h7c0.552,0,1-0.448,1-1V28h7.222 c0.51,0,0.938-0.383,0.994-0.89l0.778-7C38.06,19.518,37.596,19,37,19h-8v-5C29,12.343,30.343,11,32,11z"></path></svg></i><span>Login with Facebook</span></a>
    ';
  }
  
  public static function signup(User_Form_Account $form)
  {
    
  }
}
