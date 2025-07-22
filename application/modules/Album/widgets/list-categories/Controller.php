<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9791 2016-12-08 20:41:41Z pamela $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Widget_ListCategoriesController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $this->view->categories = Engine_Api::_()->getDbTable('categories', 'album')->getCategory();
    if (engine_count($this->view->categories) <= 0)
      return $this->setNoRender();
  }
}
