<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Branding
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     Charlotte
 */

/**
 * @category   Application_Widget
 * @package    Branding
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Widget_BrandingController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $this->view->version = 0;
    $this->view->showVersion = $showVersion = $this->_getParam('showVersion', 0);
    if(!empty($showVersion)) {
      $table = Engine_Api::_()->getDbTable('modules', 'core');
      $version = $table->select()
                    ->from($table->info('name'), 'version')
                    ->query()
                    ->fetchColumn();
      $this->view->version = $version;
    }
  }
}
