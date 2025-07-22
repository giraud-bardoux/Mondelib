<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_Model_Topic extends Core_Model_Item_Abstract
{
  protected $_parent_type = 'event';

  protected $_owner_type = 'user';

  protected $_children_types = array('event_post');
  
  protected $_searchTriggers = false;
  
  public function isSearchable()
  {
    $event = $this->getParentEvent();
    if( !($event instanceof Core_Model_Item_Abstract) ) {
      return false;
    }
    return $event->isSearchable();
  }
  
  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'event_topic',
      'controller' => 'topic',
      'action' => 'view',
      'event_id' => $this->event_id,
      'topic_id' => $this->getIdentity(),
      'slug' => $this->getSlug(),
    ), $params);
    $route = @$params['route'];
    unset($params['route']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getDescription()
  {
    $firstPost = $this->getFirstPost();
    $content = '';
    if (null !== $firstPost) {
        $content = $firstPost->body;
        // strip HTML and BBcode
        $content = strip_tags($content);
        $content = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $content);
        $content = ( Engine_String::strlen($content) > 255 ? Engine_String::substr($content, 0, 255) . '...' : $content );
    }
    return $content;
  }
  
  public function getParentEvent()
  {
    return Engine_Api::_()->getItem('event', $this->event_id);
  }

  public function getFirstPost()
  {
    $table = Engine_Api::_()->getDbtable('posts', 'event');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ->order('post_id ASC')
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPost()
  {
    $table = Engine_Api::_()->getItemTable('event_post');
    $select = $table->select()
      ->where('topic_id = ?', $this->getIdentity())
      ->order('post_id DESC')
      ->limit(1);

    return $table->fetchRow($select);
  }

  public function getLastPoster()
  {
    return Engine_Api::_()->getItem('user', $this->lastposter_id);
  }

  public function getAuthorizationItem()
  {
    return $this->getParent('event');
  }

  // Internal hooks
  protected function _insert()
  {
    if( !$this->event_id )
    {
      throw new Exception('Cannot create topic without event_id');
    }

    /*
    $this->getParentEvent()->setFromArray(array(

    ))->save();
    */

    parent::_insert();
  }
  
  protected function _delete()
  {
    if( $this->_disableHooks ) return;

    // Delete all child posts
    $postTable = Engine_Api::_()->getItemTable('event_post');
    $postSelect = $postTable->select()->where('topic_id = ?', $this->getIdentity());
    $storageFilesTable = Engine_Api::_()->getItemTable('storage_file');
    foreach( $postTable->fetchAll($postSelect) as $eventPost ) {
      //Delete tinymce files
      $storageFilesTable->deleteTinyMceFiles($eventPost);
      $eventPost->disableHooks()->delete();
    }

    // delete event_topic_creat and event_topic_reply

    parent::_delete();
  }

  public function canEdit($user)
  {
    return $this->authorization()->isAllowed($user, 'topic_edit'); //$this->authorization()->isAllowed($user, 'edit') || $this->authorization()->isAllowed($user, 'topic.edit');
  }

  public function canDelete($user)
  {
    return $this->authorization()->isAllowed($user, 'topic_delete'); //$this->authorization()->isAllowed($user, 'delete') || $this->authorization()->isAllowed($user, 'topic.delete');
  }

  public function canPostCreate($user)
  {
    return $this->authorization()->isAllowed($user, 'post_create');
  }

  public function canPostEdit($user)
  {
    return $this->authorization()->isAllowed($user, 'post_edit');
  }

  public function canPostDelete($user)
  {
    return $this->authorization()->isAllowed($user, 'post_delete');
  }
  public function follow()
  {
      return true;
  }
}
