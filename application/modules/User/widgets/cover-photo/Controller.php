<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Widget_CoverPhotoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $front = Zend_Controller_Front::getInstance();
    $this->view->module = $module = $front->getRequest()->getModuleName();
    $this->view->controller = $controller = $front->getRequest()->getControllerName();
    $this->view->action = $action = $front->getRequest()->getActionName();

    // Don't render this if not authorized
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    if ($module == 'user' && $controller == 'index' && $action == 'home') {
      $this->view->subject = $subject = $viewer;
    } else {
      // Get subject and check auth
      if (!Engine_Api::_()->core()->hasSubject('user'))
        return $this->setNoRender();
      $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('user');

      // Get subject and check auth
      if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
        return $this->setNoRender();
      }
    }

    if (!$subject->getIdentity()) {
      return $this->setNoRender();
    }

    $authApi = Engine_Api::_()->authorization();
    $this->view->can_edit = $subject->authorization()->isAllowed($viewer, 'edit');
    $this->view->tab = $authApi->getPermission($subject, 'user', 'tab');
    $this->view->height = '400';
    $this->view->is_fullwidth = $authApi->getPermission($subject, 'user', 'is_fullwidth');
    $this->view->defaultCoverPhoto = $authApi->getPermission($subject, 'user', 'coverphoto');
    $this->view->auth = $subject->authorization()->isAllowed($viewer, 'view');
    $this->view->userNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_profile');
  }
}
