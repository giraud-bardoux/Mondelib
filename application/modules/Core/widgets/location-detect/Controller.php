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

class Core_Widget_LocationDetectController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) != 1)
      $this->setNoRender();

		$this->view->cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
  }
}
