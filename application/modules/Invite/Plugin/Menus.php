<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_Plugin_Menus
{
  public function canInvite()
  {
    // Check if admins only
//     if( Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.inviteonly') == 1 ) {
//       return (bool) Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view');
//     } else {
//       return (bool) Engine_Api::_()->user()->getViewer()->getIdentity();
//     }
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1))
      return false;
    else {
      $viewer = Engine_Api::_()->user()->getViewer();
      if($viewer->getIdentity()) {
        $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
        $levelsvalue = unserialize($levels);
        if(!engine_in_array($viewer->level_id, $levelsvalue))
          return false;
        else 
          return true;
      }
    }
  }
  
  public function onMenuInitialize_UserSettingsInvites($row)
  {
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1))
      return false;
    else {
      $viewer = Engine_Api::_()->user()->getViewer();
      if($viewer->getIdentity()) {
        $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
        $levelsvalue = unserialize($levels);
        if(!engine_in_array($viewer->level_id, $levelsvalue))
          return false;
        else 
          return true;
      }
    }
  }
}
