<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Subscriptions.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Model_DbTable_Subscriptions extends Engine_Db_Table
{
  public function sendNotifications(Blog_Model_Blog $blog)
  {
    if( !empty($blog->draft) || $blog->owner_type != 'user' ) {
      return $this;
    }

    // Get blog owner
    $owner = $blog->getOwner('user');

    // Get notification table
    $notificationTable = Engine_Api::_()->getDbtable('notifications', 'activity');

    // Get all subscribers
    $identities = $this->select()
      ->from($this, 'subscriber_user_id')
      ->where('user_id = ?', $blog->owner_id)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN);

    if( empty($identities) || engine_count($identities) <= 0 ) {
      return $this;
    }

    $users = Engine_Api::_()->getItemMulti('user', $identities);

    if( empty($users) || engine_count($users) <= 0 ) {
      return $this;
    }

    // Send notifications
    foreach( $users as $user ) {
      $notificationTable->addNotification($user, $owner, $blog, 'blog_subscribed_new');
    }

    return $this;
  }

  public function checkSubscription(User_Model_User $user, User_Model_User $subscriber)
  {
    return (bool) $this->select()
        ->from($this, new Zend_Db_Expr('TRUE'))
        ->where('user_id = ?', $user->getIdentity())
        ->where('subscriber_user_id = ?', $subscriber->getIdentity())
        ->query()
        ->fetchColumn();
  }

  public function createSubscription(User_Model_User $user, User_Model_User $subscriber)
  {
    // Ignore if already subscribed
    if( $this->checkSubscription($user, $subscriber) ) {
      return $this;
    }

    // Create
    $this->insert(array(
      'user_id' => $user->getIdentity(),
      'subscriber_user_id' => $subscriber->getIdentity(),
    ));

    return $this;
  }

  public function removeSubscription(User_Model_User $user, User_Model_User $subscriber)
  {
    // Ignore if already not subscribed
    if( !$this->checkSubscription($user, $subscriber) ) {
      return $this;
    }

    // Delete
    $this->delete(array(
      'user_id = ?' => $user->getIdentity(),
      'subscriber_user_id = ?' => $subscriber->getIdentity(),
    ));

    return $this;
  }
}
