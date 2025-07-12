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
class Core_Widget_AdminNotificationController extends Engine_Content_Widget_Abstract
{
  public function indexAction()  {
    // Notifications
    // Hook-based
    $event = Engine_Hooks_Dispatcher::_()->callEvent('getAdminNotifications');
    $this->view->notifications = $event->getResponses();
    // Database-based
    $select = Engine_Api::_()->getDbtable('log', 'core')->select()
      ->where('domain = ?', 'admin')
      ->order('timestamp DESC')
      ;
      
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(4);
  }
}
