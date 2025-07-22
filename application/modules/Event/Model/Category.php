<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Category.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Model_Category extends Core_Model_Category
{
  protected $_searchTriggers = false;
  protected $_route = 'event_general';

  public function getTitle()
  {
    return $this->title;
  }

  public function getUsedCount()
  {
    $eventTable = Engine_Api::_()->getItemTable('event');
    return $eventTable->select()
        ->from($eventTable, new Zend_Db_Expr('COUNT(event_id)'))
        ->where('category_id = ?', $this->category_id)
        ->query()
        ->fetchColumn();
  }

  public function isOwner($owner)
  {
    return false;
  }

  public function getOwner($recurseType = null)
  {
    return $this;
  }
}
