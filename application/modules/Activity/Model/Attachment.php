<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Attachment.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_Attachment extends Core_Model_Item_Abstract {
  protected $_searchTriggers = false;

  public function getNextPhoto()
  {
    $table = Engine_Api::_()->getItemTable('activity_attachment');
    $select = $table->select()
      //->where('id = ?', $this->id)
      //->where('type = ?', $this->type)
      ->where('action_id = ?', $this->action_id)
      ->where('`attachment_id` > ?', $this->attachment_id)
      ->order('attachment_id ASC')
      ->limit(1);
    $photo = $table->fetchRow($select);

    if( !$photo ) {
      // Get first photo instead
      $select = $table->select()
          //->where('id = ?', $this->id)
          //->where('type = ?', $this->type)
          ->where('action_id = ?', $this->action_id)
          ->order('attachment_id ASC')
          ->limit(1);
      $photo = $table->fetchRow($select);
    }

    return $photo;
  }

  public function getPreviousPhoto()
  {
    $table = $this->getTable();
    $select = $table->select()
      //->where('id = ?', $this->id)
      //->where('type = ?', $this->type)
      ->where('action_id = ?', $this->action_id)
      ->where('`attachment_id` < ?', $this->attachment_id)
      ->order('attachment_id DESC')
      ->limit(1);
    $photo = $table->fetchRow($select);

    if( !$photo ) {
      // Get last photo instead
      $select = $table->select()
          //->where('id = ?', $this->id)
          //->where('type = ?', $this->type)
          ->where('action_id = ?', $this->action_id)
          ->order('attachment_id DESC')
          ->limit(1);
      $photo = $table->fetchRow($select);
    }

    return $photo;
  }
}