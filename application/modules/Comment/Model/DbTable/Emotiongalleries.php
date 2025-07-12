<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Emotiongalleries.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Emotiongalleries extends Engine_Db_Table
{
  protected $_rowClass = 'Comment_Model_Gallery';
  public function getPaginator($params = array())
  {
    return Zend_Paginator::factory($this->getGallery($params));
  }
  public function getGallery($params = array())
  {
    $select = ($this->select());
    if (!empty($params['type']) && $params['type'] && $params['type'] == 'user') {
      $select->where('enabled =?', 1);
    }
    if (!empty($params['fetchAll'])) {
      return $this->fetchAll($select);
    }
    return $select;

  }
}
