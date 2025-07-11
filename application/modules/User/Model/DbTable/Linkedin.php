<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Linkedin.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

require_once(APPLICATION_PATH . '/application/modules/User/Api/Linkedin/LinkedIn.php');

class User_Model_DbTable_Linkedin extends Engine_Db_Table {

  protected $_api;

  public static function getLinkedinInstance() {
    return Engine_Api::_()->getDbtable('linkedin', 'user')->getApi();
  }

  public function getApi() {
  
    // Already initialized
    if (null !== $this->_api) {
        return $this->_api;
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Need to initialize
    $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.access', '');
    $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.secret', '');
    $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.enable',false);
    if (empty($settings['linkedin_access']) || empty($settings['linkedin_secret']) || empty($settings['linkedin_enable'])) {
      $this->_api = null;
      Zend_Registry::set('Linkedin_Api', $this->_api);
      return false;
    }

    $this->_api = new LinkedIn(array('appKey' => $settings['linkedin_access'], 'appSecret' => $settings['linkedin_secret']));
    Zend_Registry::set('Linkedin_Api', $this->_api);

    // Try to log viewer in?
    if (!empty($_SESSION['linkedin_uid'])) {
        $_SESSION['linkedin_lock'] = true;
        $lin_uid = Engine_Api::_()->getDbtable('linkedin', 'user')
                ->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
        if ($lin_uid) {
            $_SESSION['linkedin_uid'] = $lin_uid['linkedin_uid'];
            $_SESSION['linkedin_secret'] = $lin_uid['code'];
            $_SESSION['linkedin_token'] = $lin_uid['access_token'];
            $this->_api->setTokenAccess($_SESSION['linkedin_access']);
        }
    } else
        $_SESSION['linkedin_lock'] = '';

    return $this->_api;
  }

  public function isConnected() {
    // Need to initialize
    $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.access', '');
    $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.secret', '');
    $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.enable','0');
    if (!$settings['linkedin_access'] || !$settings['linkedin_secret'] || !$settings['linkedin_enable'])
        return false;
    return true;
  }
  
  /**
   * Generates the button used for Linkedin Connect
   *
   * @param mixed $fb_params A string or array of Linkedin parameters for login
   * @param string $connect_with_facebook The string to display inside the button
   * @return String Generates HTML code for facebook login button
   */
  public static function loginButton($connect_text = 'Connect with Linkedin') {

    $href = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'linkedin'), 'default', true);
    return '<a href="'.$href.'" class="social_login_btn linkedin_login_btn ajaxPrevent"><i><svg viewBox="0 0 448 512" fill="#fff"><path d="M100.3 448H7.4V148.9h92.9zM53.8 108.1C24.1 108.1 0 83.5 0 53.8a53.8 53.8 0 0 1 107.6 0c0 29.7-24.1 54.3-53.8 54.3zM447.9 448h-92.7V302.4c0-34.7-.7-79.2-48.3-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.8V148.9h89.1v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z"/></svg></i><span>Login with Linkedin</span></a>';
  }
  
  public static function signup(User_Form_Account $form)
  {
    
  }
}
