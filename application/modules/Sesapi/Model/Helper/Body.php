<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Body.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Model_Helper_Body extends Sesapi_Model_Helper_Abstract
{
  /**
   * Body helper
   * 
   * @param string $body
   * @return string
   */
  public function direct($body, $noTranslate = false)
  {
      $emojiFile = APPLICATION_PATH.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'Activity'.DIRECTORY_SEPARATOR.'externals'.DIRECTORY_SEPARATOR.'emoticons'.DIRECTORY_SEPARATOR.'unicode.php';
      if(file_exists($emojiFile)){
          $emotiCons = include $emojiFile;
          if(engine_count($emotiCons))
            $body = str_replace(array_keys($emotiCons),array_values($emotiCons),$body);
      }
    if( Zend_Registry::isRegistered('Zend_View') ) {
      $view = Zend_Registry::get('Zend_View');
      $body = ($body);
    }
    return $body;
  }
}
