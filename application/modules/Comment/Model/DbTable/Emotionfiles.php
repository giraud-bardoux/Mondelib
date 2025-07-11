<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Emotionfiles.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Emotionfiles extends Engine_Db_Table
{
  protected $_rowClass = 'Comment_Model_Files';
  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getFiles($params));
  }
  public function getFiles($params = array())
  {
    $select = ($this->select());
    if (!empty($params['limit'])) {
      $select->limit($params['limit']);
    }
    $select->where('gallery_id =?', $params['gallery_id']);
    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;
  }
}
