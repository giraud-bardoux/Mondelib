<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9966 2013-03-19 00:00:35Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Poll_Widget_BreadcrumbController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->poll = $poll = Engine_Api::_()->core()->getSubject('poll');
    if(!$poll) 
      return $this->setNoRender();
  }
}
