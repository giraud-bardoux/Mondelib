<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Voteupdowns.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_Model_DbTable_Voteupdowns extends Engine_Db_Table
{
  protected $_rowClass = 'Comment_Model_Voteupdown';

  public function isVote($params = array())
  {
    $select = $this->select()
      ->where('resource_id =?', $params['resource_id'])
      ->where('resource_type =?', $params['resource_type'])
      ->where('user_type =?', $params['user_type'])
      ->where('user_id =?', $params['user_id'])
      ->limit(1);
    return $this->fetchRow($select);
  }

}
