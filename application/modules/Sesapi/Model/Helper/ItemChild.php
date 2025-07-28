<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ItemChild.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Model_Helper_ItemChild extends Sesapi_Model_Helper_Item
{
  public function direct($item, $type = null, $child_id = null)
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
    
    return parent::direct($item, $type, null, false);
  }
}
