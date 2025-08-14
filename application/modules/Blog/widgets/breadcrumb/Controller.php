<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9966 2013-03-19 00:00:35Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Blog_Widget_BreadcrumbController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->blog = $blog = Engine_Api::_()->core()->getSubject('blog');
    if(!$blog) 
      return $this->setNoRender();
  }
}
