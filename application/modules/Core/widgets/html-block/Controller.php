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
class Core_Widget_HtmlBlockController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $localLanguage = $this->view->locale()->getLocale()->__toString();
    $local_language = explode('_', $localLanguage);
    $column = !empty($local_language[0] && $local_language[0] == 'en') ? 'data' : $localLanguage . '_data';
    $data = $this->_getParam($column, null);
    $this->view->data = (isset($data) && !empty($data)) ? $data : $this->_getParam('data', null);
  }
}
