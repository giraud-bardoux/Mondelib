<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: ItemChild.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Model_Helper_ItemChild extends Activity_Model_Helper_Item
{
  public function direct($item, $type = null, $child_id = null,$separator = "")
  { 
    $item = $this->_getItem($item, false);   
    
    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }
    
    $child_type = $item->getType().'_'.$type;
    
    try{
      $item = Engine_Api::_()->getItem($child_type, $child_id);
    }
    catch (Exception $e) {
      // With no alarms and no surprises
      // No alarms and no surprises
      // No alarms and no surprises
      // Silent, silent
    }
    
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      return false;
    }    
    
    return parent::direct($item, $type);
  }
}
