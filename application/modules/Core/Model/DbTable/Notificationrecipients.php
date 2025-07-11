<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Notificationrecipients.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Notificationrecipients extends Engine_Db_Table {

  protected $_rowClass = 'Core_Model_Notificationrecipient';
  
  public function addRecipientNotification(User_Model_User $user, Core_Model_Item_Abstract $subject, Core_Model_Item_Abstract $object, $type, array $params = null) {

    $row = $this->createRow();
    $row->user_id = $user->getIdentity();
    $row->subject_type = $subject->getType();
    $row->subject_id = $subject->getIdentity();
    $row->object_type = $object->getType();
    $row->object_id = $object->getIdentity();
    $row->type = $type;
    $row->params = json_encode((object) $params);
    $row->date = date('Y-m-d H:i:s');
    $row->save();
  }
  
  public function getEntries() {

    $name = $this->info('name');
    $select = $this->select()
                  ->from($name)
                  ->limit(25);
    $results = $this->fetchAll($select);
    foreach($results as $result) {
      $user = Engine_Api::_()->getItem('user', $result->user_id);
      $subject = Engine_Api::_()->getItem($result->subject_type, $result->subject_id);
      $object = Engine_Api::_()->getItem($result->object_type, $result->object_id);
      
      if(empty($result->params) && $object) {
        $params = array("itemtype" => $object->getShortType());
      } else if(!empty($result->params)) {
        $params = (array) json_decode($result->params);
      }
      $params['canSend'] = true;
      
      if($result->subject_type == 'user') {
        Zend_Auth::getInstance()->getStorage()->write($result->subject_id);
        Engine_Api::_()->user()->setViewer($subject);
      } else {
        $owner = $subject->getOwner();
        if($owner) {
          Zend_Auth::getInstance()->getStorage()->write($owner->getIdentity());
          Engine_Api::_()->user()->setViewer($owner);
        }
      }
      
      if($object && $subject) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $subject, $object, $result->type, $params);
      }
      $result->delete();
    } 
  }
}
