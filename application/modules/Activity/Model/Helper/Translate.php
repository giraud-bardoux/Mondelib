<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Translate.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_Helper_Translate extends Activity_Model_Helper_Abstract {
  /**
   *
   * @param string $value
   * @return string
   */
  public function direct($value, $noTranslate = false,$separator = ' &rarr; ')
  {
    $translate = Zend_Registry::get('Zend_Translate');
    if( !$noTranslate && $translate instanceof Zend_Translate ) {
      $tmp = $translate->translate($value);
      return $tmp;
    } else {
      return $value;
    }
  }
}
