<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Bootstrap.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_Bootstrap extends Engine_Application_Bootstrap_Abstract {

  protected function _initFrontController() {
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Warning_Plugin_Core);
		Zend_Controller_Action_HelperBroker::addHelper( new Warning_Controller_Action_Helper_Warnings());
  }
}
