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
class Core_Widget_MenuFooterController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer->getIdentity();

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_footer');
    
    //Languages
    $this->view->languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();
  }

  public function getCacheKey()
  {
    //return true;
  }

  public function setLanguage()
  {
  }
}
