<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: NotificationSettings.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Model_DbTable_NotificationSettings extends Engine_Db_Table
{
  protected $_name = "activity_notificationSettings";
  public function getEnabledNotifications()
  {
    $types = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();

    $select = $this->select();
    $rowset = $this->fetchAll($select);

    $enabledTypes = array();
    foreach( $types as $type )
    {
      $row = $rowset->getRowMatching('type', $type->type);
      if( null === $row || $row->email == true )
      {
        $enabledTypes[] = $type->type;
      }
    }

    return $enabledTypes;
  }

  /**
   * Set enabled notification types for a user
   *
   * @param User_Model_User $user
   * @param array $types
   * @return Activity_Api_Notifications
   */
  public function setEnabledNotifications(User_Model_User $user, array $enabledTypes)
  {
    $types = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();

    $select = $this->select();
    $rowset = $this->fetchAll($select);

    foreach( $types as $type )
    {
      $row = $rowset->getRowMatching('type', $type->type);
      $value = engine_in_array($type->type, $enabledTypes);
      if( $value && null !== $row )
      {
        $row->delete();
      }
      else if( !$value && null === $row )
      {
        $row = $this->createRow();
        $row->type = $type->type;
        $row->email = (bool) $value;
        $row->save();
      }
    }

    return $this;
  }

  /**
   * Check if a notification is enabled
   *
   * @param User_Model_User $user User to check for
   * @param string $type Notification type
   * @return bool Enabled
   */
  public function checkEnabledNotification(User_Model_User $user, $type)
  {
    $select = $this->select()
      ->where('type = ?', $type)
      ->limit(1);

    $row = $this->fetchRow($select);

    if( null === $row )
    {
      return true;
    }

    return (bool) $row->email;
  }
}
