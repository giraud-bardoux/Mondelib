<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9831 2012-11-27 20:42:43Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_AdminMenuMainController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main');
		$request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->moduleName = $request->getModuleName();
    $this->view->controllerName = $request->getControllerName();
    $this->view->actionName = $request->getActionName();
  }
}
