<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Comment.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Model_Comment extends Core_Model_Comment
{
  protected $resource_type = 'activity_action';

  public function tags()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  protected function _delete()
  {
    $hashtags = Engine_Api::_()->activity()->getHashTags($this->body);
    $this->tags()->removeTagMaps($hashtags[0]);
    parent::_delete();
  }
}
