<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Widget_ListRequestsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return $this->setNoRender();
    }

    // Get requests
    $this->view->requests = Engine_Api::_()->getDbtable('notifications', 'activity')->getRequestCountsByType($viewer);

    // If no requests, just skip rendering
    if( empty($this->view->requests) ) {
      return $this->setNoRender();
    }
  }
}
