<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Recipients.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_Model_DbTable_Recipients extends Engine_Db_Table
{
  protected $_rowClass = 'Messages_Model_Recipient';
  
  public function getRecipient($params = array()) {
    $cName = $this->info('name');
    return $this->select()
                  ->from($cName, 'user_id')
                  ->where("`{$cName}`.`conversation_id` = ?", $params['conversation_id'])
                  ->where("`{$cName}`.`inbox_message_id` = ?", $params['message_id'])
                  ->query()
                  ->fetchColumn();

  }
}
