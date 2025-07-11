<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: GetUserInfo.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_View_Helper_GetUserInfo extends Zend_View_Helper_Abstract
{
  public function getUserInfo($user)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $userInfo = array();
    if(!($user instanceof Core_Model_Item_Abstract)){
      return json_encode($userInfo,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    } 
    $userInfo = array(
      'type'  => $user->getType(),
      'id'    => $user->getIdentity(),
      'name'  => $user->getTitle(),
      'value' => $user->getTitle(),
      'avatar' => htmlspecialchars(str_replace('"',"'",$this->view->itemPhoto($user, 'thumb.icon')),ENT_QUOTES),
    );
    return json_encode($userInfo,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
  } 
}
