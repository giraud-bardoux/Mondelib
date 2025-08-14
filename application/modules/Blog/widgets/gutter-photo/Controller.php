<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Widget_GutterPhotoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Only blog or user as subject
    if( Engine_Api::_()->core()->hasSubject('blog') ) {
      $blog = Engine_Api::_()->core()->getSubject('blog');
      $this->view->owner = $owner = $blog->getOwner();
    } 
    // else if( Engine_Api::_()->core()->hasSubject('user') ) {
    //   $this->view->blog = null;
    //   $this->view->owner = $owner = Engine_Api::_()->core()->getSubject('user');
    // } 
    else {
      return $this->setNoRender();
    }
  }
}
