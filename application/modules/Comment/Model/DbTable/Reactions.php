<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Reactions.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Reactions extends Engine_Db_Table
{

  protected $_rowClass = 'Comment_Model_Reaction';

  public function getPaginator($params = array())
  {

    return Zend_Paginator::factory($this->getReactions($params));
  }

  public function getReactions($params = array())
  {

    $select = ($this->select());

    if (@$params['userside']) {
      $select = $select->where('enabled =?', 1);
    }

    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }

  public function likeImage($type = 1)
  {

    $file_id = $this->select()
      ->from($this->info('name'), 'file_id')
      ->where('enabled = ?', 1)
      ->where('reaction_id = ?', $type)
      ->query()
      ->fetchColumn(0);
    if ($file_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id);
      if ($file)
        return $file->map();
    }
  }

  public function likeWord($type = 1)
  {

    return $this->select()
      ->from($this->info('name'), 'title')
      ->where('enabled = ?', 1)
      ->where('reaction_id = ?', $type)
      ->query()
      ->fetchColumn(0);
  }
}
