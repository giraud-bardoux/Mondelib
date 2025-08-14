<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Warnings.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_Controller_Action_Helper_Warnings extends Zend_Controller_Action_Helper_Abstract {

  function postDispatch() {

    $front = Zend_Controller_Front::getInstance();

    $module = $front->getRequest()->getModuleName();
    $action = $front->getRequest()->getActionName();
    $controller = $front->getRequest()->getControllerName();
    
    $request = $this->getActionController()->getRequest();
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $getParamrequest = $request->getParams();
    $getModuleName = @$getParamrequest['module'];

    $pagenotfound301redirect = $settings->getSetting('warning.pagenotfound301redirect', 1);
    if($pagenotfound301redirect && empty($_SERVER['HTTP_REFERER']) && $module == 'core' && $controller == 'error' && $action == 'notfound') {
      header("HTTP/1.1 301 Moved Permanently");
      $base_url = ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . Zend_Registry::get('Zend_View')->baseUrl();
      $url = $base_url;
      header("Location:" . $url);
      exit();
    }

    $privateenable = $settings->getSetting('warning.privateenable', 1);
    $pagenotfoundenable = $settings->getSetting('warning.pagenotfoundenable', 1);
    if ($module == 'core' && $controller == 'error' && $action == 'requireauth' && !empty($privateenable)) {
      $request->setModuleName('warning');
      $request->setControllerName('error');
      $request->setActionName('index');
      $request->setParams(array('error' => 'requireauth', 'modulename' => $getModuleName));
    } elseif ($module == 'core' && $controller == 'error' && $action ==  'requiresubject' && !empty($privateenable)) {
      $request->setModuleName('warning');
      $request->setControllerName('error');
      $request->setActionName('index');
      $request->setParams(array('error' => 'requireauth', 'modulename' => $getModuleName));
    } elseif ($module == 'core' && $controller == 'error' && $action == 'notfound' && !empty($pagenotfoundenable)) {
      $request->setModuleName('warning');
      $request->setControllerName('error');
      $request->setActionName('view');
    }
  }
}
