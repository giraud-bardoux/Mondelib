<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Activity.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
class Sesapi_Api_Activity extends Core_Api_Abstract {
   // get activity title text
   protected $_pluginLoader;
   private function getHelper($name) {
      $name = $this->_helperName($name);
      if (!isset($this->_helpers[$name])) {
          $helper = $this->getPluginLoader()->load($name);
          $this->_helpers[$name] = new $helper;
      }

      return $this->_helpers[$name];
    }
    private function _helperName($name) {
      $name = preg_replace('/[^A-Za-z0-9]/', '', $name);
      $name = ucfirst($name);
      return $name;
    }
    private function getPluginLoader() {
      if (null === $this->_pluginLoader) {
          $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
                  . 'modules' . DIRECTORY_SEPARATOR
                  . 'Sesapi';
          $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
              'Sesapi_Model_Helper_' => $path . '/Model/Helper/'
          ));
      }
      return $this->_pluginLoader;
  }
  public function translatedBody($body)
  {
    //Translate body
    return $this->getHelper('translate')->direct($body);
  }
  public function assemble($body, array $params = array(),$group_feed_id = null)
  {
    //Translate body
    $body = $this->getHelper('translate')->direct($body);
   
    // Do other stuff
    preg_match_all('~\{([^{}]+)\}~', $body, $matches, PREG_SET_ORDER);
    $arrayBody = array();
    $counter = 0;
    foreach( $matches as $match )
    {
      $tag = $match[0];
      $args = explode(':', $match[1]);
      $helper = array_shift($args);
      $helperArgs = array();
      foreach( $args as $arg )
      {
        if( substr($arg, 0, 1) === '$' )
        {
          $valid = true;
          $arg = substr($arg, 1);
          if($arg == "subject" && !empty($params['sesresource_id']) && !empty($params['sesresource_type'])){
            $item = Engine_Api::_()->getItem($params['sesresource_type'],$params['sesresource_id']);
            if($item){
              $helperArgs[] =  $item;
              $valid = false;
            }
          }
          if($valid)
          $helperArgs[] = ( isset($params[$arg]) ? $params[$arg] : null );
        }
        else
        {
          $helperArgs[] = $arg;
        }
      }
      $contentHelper = $helper;
      
      //Verified icon Work
      if($counter > 0 && $contentHelper == 'item') {
        $helperArgs[1] = null;
        $helperArgs[2] = null;
        $helperArgs[3] = false;
      }
      
      $helper = $this->getHelper($helper);
      $r = new ReflectionMethod($helper, 'direct');
      $content = $r->invokeArgs($helper, $helperArgs);
      if ($contentHelper == "var"){
        if(is_string(($content)) && strpos($content,'<a ') !== false && strpos($content,'action_id') !== false){
          preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $content, $result);
          if (!empty($result)) {
            $content1['href'] =  Engine_Api::_()->sesapi()->getBaseUrl('',$result['href'][0]);
            $content1["title"] = strip_tags($content); 
            $content1['id'] = (int) end(explode('/',$content1['href']));
            $content1['type'] = "activity";
            $content = $content1;
          }
        }  
        //Forum Topic Work
        if(is_string(($content)) && strpos($content,'<a ') !== false && strpos($content,'topic') !== false){
          preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $content, $result);
          if (!empty($result)) {
            # Found a link.
            $content1['href'] =  Engine_Api::_()->sesapi()->getBaseUrl('',$result['href'][0]); 
            $content1["title"] = strip_tags($content); 
            $idCont = explode('/',$content1['href']);
            $content1['id'] = (int) $idCont[5];
            if(empty($content1['id'])){
              $content1["id"] = $params["object_id"];
            }
            $content1['type'] = "sesforum_topic";
            $content = $content1;
          }
        }
      }
      if(is_string($content)){
        $content = strip_tags($content);         
      }
      if(_SESAPI_PLATFORM_SERVICE == 2){
        if(is_array($content))
          $arrayBody[$counter] = $content;
        else{
          $arrayBody[$counter]['value'] = $content;  
        }
        $arrayBody[$counter]['key'] = $tag;
      }else{
        $arrayBody[$tag] = $content;
      }
      $counter++;
    }
    return $arrayBody;
  }
}
