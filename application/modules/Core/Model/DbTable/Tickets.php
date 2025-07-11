<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Reasons.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Tickets extends Engine_Db_Table {

  protected $_rowClass = 'Core_Model_Ticket';
  
  public function isExists($params = array()) {
    return $this->select()
                ->from($this->info('name'), 'ticket_id')
                ->where('resource_type = ?', $params['resource_type'])
                ->where('resource_id = ?', $params['resource_id'])
                ->query()
                ->fetchColumn();
  }
  
  public function isTicketExists($category_id, $categoryType = 'category_id') {
    return $this->select()
            ->from($this->info('name'), 'ticket_id')
            ->where($categoryType . ' = ?', $category_id)
            ->query()
            ->fetchColumn();
  }
}
