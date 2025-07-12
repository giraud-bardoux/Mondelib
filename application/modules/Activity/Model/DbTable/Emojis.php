<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Emojis.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Emojis extends Engine_Db_Table
{
  protected $_rowClass = 'Activity_Model_Emoji';

  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getEmojis($params));
  }

  public function getEmojis($params = array())
  {
    $select = $this->select()
      ->where('emoji_id <> ?', 3)
      ->order('order ASC')
      ;
    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
}