<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Widget_ListSignupsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $table->select()
      ->where('search = ?', 1)
      ->where('enabled = ?', 1)
      ->order('creation_date DESC')
      ->limit($this->_getParam('itemCountPerPage', 4))
      ;

    $this->view->paginator = $paginator = $table->fetchAll($select);

    // Do not render if nothing to show
    if( engine_count($paginator) <= 0 ) {
      return $this->setNoRender();
    }
  }

  public function getCacheKey()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $translate = Zend_Registry::get('Zend_Translate');
    $locale = Zend_Registry::get('Locale');
    return $viewer->getIdentity() . $translate->getLocale() . $locale->toString();
  }

  public function getCacheSpecificLifetime()
  {
    return 120;
  }
}
