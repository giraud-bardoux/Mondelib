<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Emojiicon.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Emojiicons extends Engine_Db_Table
{

  protected $_rowClass = 'Activity_Model_Emojiicon';

  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getEmojiicons($params));
  }

  public function getEmojiicons($params = array())
  {
    $select = $this->select()->order('order ASC');
    if (!empty($params['limit'])) {
      $select->limit($params['limit']);
    }
    if(isset($params['emoji_id']) && !empty($params['emoji_id'])) {
     $select->where('emoji_id =?', $params['emoji_id']);
    }
    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }

  public function getEmojiFileId($params = array())
  {
    return $this->select()
      ->from($this->info('name'), array($params['column']))
      ->where('emoji_encodecode =?', $params['emoji_encodecode'])
      ->query()
      ->fetchColumn();
  }

  public function getEmojiIconExist($params = array())
  {
    return $this->select()
      ->from($this->info('name'), array('emojiicon_id'))
      ->where('emoji_icon =?', $params['emoji_icon'])
      ->query()
      ->fetchColumn();
  }
}