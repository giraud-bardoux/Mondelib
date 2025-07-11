<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_ParallaxController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->heading = $this->_getParam('heading', 'Engage with people of your interests');
    $this->view->bgphoto = $this->_getParam('bgphoto', null);
    $this->view->height = $this->_getParam('height', 300);
    $this->view->showfull = $this->_getParam('showfull', 0);
    
  }
}
