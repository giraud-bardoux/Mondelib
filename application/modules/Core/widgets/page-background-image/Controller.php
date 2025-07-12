<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 10163 2014-04-11 19:50:10Z andres $
 * @author     John
 */

class Core_Widget_PageBackgroundImageController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->bgimage = $bgimage = $this->_getParam('bgimage', null);
    if(empty($bgimage))
      return $this->setNoRender();
  }
}
