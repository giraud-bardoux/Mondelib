<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Plugin_Core
{
  public function onStatistics($event)
  {
    $table  = Engine_Api::_()->getDbTable('blogs', 'blog');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'blog');
  }

  public function onUserDeleteBefore($event)
  {
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
      // Delete blogs
      $blogTable = Engine_Api::_()->getDbtable('blogs', 'blog');
      $blogSelect = $blogTable->select()->where('owner_id = ?', $payload->getIdentity());
      foreach( $blogTable->fetchAll($blogSelect) as $blog ) {
        $blog->delete();
      }
      // Delete subscriptions
      $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'blog');
      $subscriptionsTable->delete(array(
        'user_id = ?' => $payload->getIdentity(),
      ));
      $subscriptionsTable->delete(array(
        'subscriber_user_id = ?' => $payload->getIdentity(),
      ));
    }
  }
}
