<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Abstract.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
abstract class Activity_Model_Helper_Abstract
{
  /**
   * Currently set action
   * 
   * @var Activity_Model_Action
   */
  protected $_action;

  /**
   * Set the current action
   * 
   * @param Activity_Model_Action $action
   * @return Activity_Model_Action
   */
  public function setAction(Activity_Model_Action $action)
  {
    $this->_action = $action;
    return $this;
  }

  /**
   * Get the currently set action
   * @return Activity_Model_Action
   */
  public function getAction()
  {
    return $this->_action;
  }

  

  protected function _getItem($item, $throw = true)
  {
    // Accept string in form <type>_<id>
    if( is_string($item) && strpos($item, '_') !== false )
    {
      $item = explode('_', $item);
      $id = array_pop($item);
      $type = implode('_', $item);
      $item = array($type, $id);
    }

    // Accept array in form array(<type>, <id>)
    if( is_array($item) && engine_count($item) === 2 && is_string($item[0]) && is_numeric($item[1]) )
    {
      $item = Engine_Api::_()->getItem($item[0], $item[1]);
    }

    // Check to make sure we have an item
    if( !($item instanceof Core_Model_Item_Abstract) )
    {
      if( $throw ) {
        throw new Activity_Model_Exception('Not an item');
      } else {
        return false;
      }
    }

    return $item;
  }
}
