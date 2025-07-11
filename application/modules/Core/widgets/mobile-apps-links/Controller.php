<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_MobileAppsLinksController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->androidlink = $this->_getParam('androidlink', null);
    $this->view->iOSlink = $this->_getParam('iOSlink', null);
    if(empty($this->view->androidlink) && empty($this->view->iOSlink))
      return $this->setNoRender();
      
    $this->view->mobile = Engine_Api::_()->core()->isMobile();
  }
}
