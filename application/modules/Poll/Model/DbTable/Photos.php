<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Photos.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Sami
 */
class Poll_Model_DbTable_Photos extends Engine_Db_Table
{
  protected $_rowClass = 'Poll_Model_Photo';

  public function deletePhoto(Core_Model_Item_Abstract $photo)
  {
    $db = $this->getAdapter();
    $db->beginTransaction();

    try {
      // Check activity actions
      $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
      $actions = $attachDB->fetchAll($attachDB->select()->where('type = ?', 'poll_photo')->where('id = ?', $photo->photo_id));
      $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');

      foreach( $actions as $action ) {
        $actionId = $action->action_id;
        $attachDB->delete(array('type = ?' => 'poll_photo', 'id = ?' => $photo->photo_id));

        $action = $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $actionId));
        $count = $action->params['count'];
        if( !is_null($count) && ($count > 1) ) {
          $action->params = array('count' => (integer) $count - 1);
          $action->save();
        } else {
          $action->delete();
        }
      }

      $photo->delete();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
}
