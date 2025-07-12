<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Plugin_Core extends Core_Model_Abstract
{
  public function onRenderLayoutDefault($event) {
  
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) return;
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $user = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $moduleName = $request->getModuleName();
    $actionName = $request->getActionName();
    $controllerName = $request->getControllerName();

    //echo $moduleName . $controllerName . $actionName;die;

    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    if ($user->getIdentity() && !$subscriptionsTable->check($user) && ($moduleName != 'payment' && $controllerName != 'subscription' && ($actionName != 'chooose' && $actionName != 'process')) && ($moduleName != 'core' && $controllerName != 'help') && ($controllerName != 'wallet')) {
      $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
      
      //if(empty($_SERVER['HTTP_REFERER'])) {
        $redirector->gotoRoute(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'index'), 'default', false);
        
        //$redirector->gotoRoute(array('module' => 'user', 'controller' => 'wallet', 'action' => 'gateway'), 'default', false);
      // } else {
      //   $url = $view->url(array('module' => 'payment', 'controller' => 'subscription', 'action' => 'index'), 'default', false);
      //   echo json_encode(array('status' => true, 'redirectFullURL' => $url, ''));die; 
      // }
    }
  }
  
  public function onUserCreateBefore($event)
  {
    $payload = $event->getPayload();

    if( !($payload instanceof User_Model_User) ) {
      return;
    }

    // Check if the user should be enabled?
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
//     if( !$subscriptionsTable->check($payload) ) {
//       $payload->enabled = false;
//       // We don't want to save here
//     }
  }

  public function onAuthorizationLevelDeleteBefore($event)
  {
    $payload = $event->getPayload();

    if( $payload instanceof Authorization_Model_Level ) {
      $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');
      $packagesTable->update(array(
        'level_id' => 0,
      ), array(
        'level_id = ?' => $payload->getIdentity(),
      ));
    }
  }
}
