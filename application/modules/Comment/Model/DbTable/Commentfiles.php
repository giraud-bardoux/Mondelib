<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Commentfiles.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Commentfiles extends Engine_Db_Table
{
  public function getFiles($params = array())
  {
    $select = $this->select()->where('comment_id =?', $params['comment_id']);
    return $this->fetchAll($select);
  }
}
