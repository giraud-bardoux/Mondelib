<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Visitors.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
class Warning_Model_DbTable_Visitors extends Engine_Db_Table {

  protected $_rowClass = 'Warning_Model_Visitor';
  
  public function getAllContacts($params = array()) {
    $select = $this->select()->from($this->info('name'));
    return Zend_Paginator::factory($select);
  }
}
