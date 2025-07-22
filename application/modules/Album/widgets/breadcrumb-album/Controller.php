<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9966 2013-03-19 00:00:35Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Album_Widget_BreadcrumbAlbumController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->album = $album = Engine_Api::_()->core()->getSubject('album');
    if(!$album) 
      return $this->setNoRender();
  }
}
