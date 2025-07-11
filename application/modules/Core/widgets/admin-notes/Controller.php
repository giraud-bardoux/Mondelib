<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_AdminNotesController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->adminnotes = Engine_Api::_()->getApi('settings', 'core')->getSetting('coreadmin.notes');
    
    // Online users
    $onlineTable = Engine_Api::_()->getDbtable('online', 'user');
    $onlineUserCount = $onlineTable->select()
        ->from($onlineTable->info('name'), new Zend_Db_Expr('COUNT(DISTINCT user_id)'))
        ->where('user_id > ?', 0)
        ->where('active > ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 20 MINUTE)'))
        ->query()
        ->fetchColumn(0);
    $this->view->onlineUserCount = $onlineUserCount;
    
    $this->view->countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers('all');
  }
}
