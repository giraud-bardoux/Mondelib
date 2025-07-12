<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Twitter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_Dbtable_Twitter extends Engine_Db_Table
{
  protected $_api;

  protected $_oauth;

  public function getApi()
  {
    if( null === $this->_api ) {
      $this->_initializeApi();
    }

    return $this->_api;
  }

  public function getOauth()
  {
    if( null === $this->_oauth ) {
      $this->_initializeApi();
    }
    
    return $this->_oauth;
  }

  public function clearApi()
  {
    $this->_api = null;
    $this->_oauth = null;
    return $this;
  }

  public function isConnected()
  {
    // @todo make sure that info is validated
    return ( !empty($_SESSION['twitter_token2']) && !empty($_SESSION['twitter_secret2']) );
  }

  protected function _initializeApi()
  {
    // Load classes
    include_once 'Services/Twitter.php';
    include_once 'HTTP/OAuth/Consumer.php';

    if( !class_exists('Services_Twitter', false) ||
        !class_exists('HTTP_OAuth_Consumer', false) ) {
      throw new Core_Model_Exception('Unable to load twitter API classes');
    }

    // Load settings
    $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
    if( empty($settings['key']) ||
        empty($settings['secret']) ||
        empty($settings['enable']) ||
        $settings['enable'] == 'none' ) {

      $this->_api = null;
      Zend_Registry::set('Twitter_Api', $this->_api);
    }

    // Try to log viewer in?
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !isset($_SESSION['twitter_uid']) ||
        @$_SESSION['twitter_lock'] !== $viewer->getIdentity() ) {
      $_SESSION['twitter_lock'] = $viewer->getIdentity();
      if( $viewer && $viewer->getIdentity() ) {
        // Try to get from db
        $info = $this->select()
            ->from($this)
            ->where('user_id = ?', $viewer->getIdentity())
            ->query()
            ->fetch();
        if( is_array($info) &&
            !empty($info['twitter_secret']) &&
            !empty($info['twitter_token']) ) {
          $_SESSION['twitter_uid'] = $info['twitter_uid'];
          $_SESSION['twitter_secret2'] = $info['twitter_secret'];
          $_SESSION['twitter_token2'] = $info['twitter_token'];
        } else {
          $_SESSION['twitter_uid'] = false; // @todo make sure this gets cleared properly
        }
      } else {
        // Could not get
        //$_SESSION['twitter_uid'] = false;
      }
    }
    
    $this->_api = new Services_Twitter();

    // Get oauth
    if( isset($_SESSION['twitter_token2'], $_SESSION['twitter_secret2']) ) {
      $this->_oauth = new HTTP_OAuth_Consumer($settings['key'], $settings['secret'],
          $_SESSION['twitter_token2'], $_SESSION['twitter_secret2']);
    } else if( isset($_SESSION['twitter_token'], $_SESSION['twitter_secret']) ) {
      $this->_oauth = new HTTP_OAuth_Consumer($settings['key'], $settings['secret'],
          $_SESSION['twitter_token'], $_SESSION['twitter_secret']);
    } else {
      $this->_oauth = new HTTP_OAuth_Consumer($settings['key'], $settings['secret']);
    }
    $this->_api->setOAuth($this->_oauth);
  }

  /**
   * Generates the button used for Twitter Connect
   */
  public static function loginButton($connect_text = 'Sign-in with Twitter')
  {
    $href = Zend_Controller_Front::getInstance()->getRouter()
        ->assemble(array('module' => 'user', 'controller' => 'auth',
          'action' => 'twitter'), 'default', true);
    return '
      <a href="'.$href.'" class="social_login_btn twitter_login_btn ajaxPrevent">
        <i><svg xmlns="http://www.w3.org/2000/svg" fill="#fff" x="0px" y="0px" width="32" height="32" viewBox="0 0 50 50">
        <path d="M 5.9199219 6 L 20.582031 27.375 L 6.2304688 44 L 9.4101562 44 L 21.986328 29.421875 L 31.986328 44 L 44 44 L 28.681641 21.669922 L 42.199219 6 L 39.029297 6 L 27.275391 19.617188 L 17.933594 6 L 5.9199219 6 z M 9.7167969 8 L 16.880859 8 L 40.203125 42 L 33.039062 42 L 9.7167969 8 z"></path></svg></i><span>Login with X</span></a>
    ';
  }
}
