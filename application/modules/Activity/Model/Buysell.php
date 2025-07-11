<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Buysell.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_Buysell extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  public function getMediaType(){
    return 'post';
  }
  
  public function getHref(){
    $action = Engine_Api::_()->getItem('activity_action',$this->action_id);
    if(!$action)
      return 'javascript:;';
    return  $action->getHref();
  }
}
