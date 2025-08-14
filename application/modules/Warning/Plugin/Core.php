<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Core.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Warning_Plugin_Core extends Zend_Controller_Plugin_Abstract {

  public function onRenderLayoutDefault($event, $mode = null) {
  
    $view = $event->getPayload();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $isAdmin = false;
    //CHECK IF ADMIN
    if (substr($request->getPathInfo(), 1, 5) == "admin") {
      $isAdmin = true;
    }
    
    if( defined('_ENGINE_ADMIN_NEUTER') && !_ENGINE_ADMIN_NEUTER ) {
      
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $viewer = Engine_Api::_()->user()->getViewer();
      $comingsoon_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('warning.comingsoonenable', 0);
      $comingsoondate = Engine_Api::_()->getApi('settings', 'core')->getSetting('warning.comingsoondate', "");
      
      $flag = 1;
      if($viewer->getIdentity()) {
        if($viewer->level_id == 1)
          $flag = 0;
      }

      $time = time();
      if($time > strtotime($comingsoondate)) {
				$flag = 0;
      }

      if($comingsoon_enable && $flag && $viewer->getIdentity() == 0) {
        $moduleName = $request->getModuleName();
        $actionName = $request->getActionName();
        $controllerName = $request->getControllerName();
        if(!in_array($actionName, array('comingsoon','contact'))) {

          $script = "var comingSoonEnable = 1;";
          $view->headScript()->appendScript($script);

          if(!$isAdmin) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoRoute(array('module' => 'warning', 'controller' => 'error', 'action' => 'comingsoon'), 'warning_comingsoon', false);
          }
        }
      }
    }
  }
}
