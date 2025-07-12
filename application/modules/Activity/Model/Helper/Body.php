<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Body.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_Helper_Body extends Activity_Model_Helper_Abstract
{
  /**
   * Body helper
   * 
   * @param string $body
   * @return string
   */
  public function direct($body, $noTranslate = false,$separator = ' &rarr; ')
  {
    $explode = explode('|||||---|||++', $body);
    if(!empty($explode[0]))
      $body = $explode[0];
    if( Zend_Registry::isRegistered('Zend_View') && empty($_GET["restApi"])) {
      $view = Zend_Registry::get('Zend_View');
      $body = $view->viewMoreActivity($body);
    }
    return 'BODYSTRING' . $body ;
  }
}
