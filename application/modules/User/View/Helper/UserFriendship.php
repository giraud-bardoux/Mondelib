<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: UserFriendship.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_View_Helper_UserFriendship extends Zend_View_Helper_Abstract
{
  public function userFriendship($user, $viewer = null, $iconType = '')
  {
    if( null === $viewer ) {
      $viewer = Engine_Api::_()->user()->getViewer();
    }

    if( !$viewer || !$viewer->getIdentity() || $user->isSelf($viewer) ) {
      return '';
    }

    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);

    // Get data
    if( !$direction ) {
       $row = $user->membership()->getRow($viewer);
    }
    else $row = $viewer->membership()->getRow($user);

    // Render

    // Check if friendship is allowed in the network
    $eligible =  (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if($eligible == 0){
      return '';
    }
   
    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $user->getIdentity())
        ;

      $data = $select->query()->fetch();

      if(empty($data)){
        return '';
      }
    }
    
    if( !$direction ) {
      // one-way mode
      if( null === $row ) {
        return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'add', 'user_id' => $user->user_id), $this->view->translate('Follow'), array(
          'class' => 'buttonlink smoothbox icon_friend_add'
        ));
      } else if( $row->resource_approved == 0 ) {
        return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'cancel', 'user_id' => $user->user_id), $this->view->translate('Cancel Follow Request'), array(
          'class' => 'buttonlink smoothbox icon_friend_cancel'
        ));
      } else {
        return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'remove', 'user_id' => $user->user_id), $this->view->translate('Following'), array(
          'class' => 'buttonlink smoothbox icon_friend_remove'
        ));
      }

    } else {
      // two-way mode
      if( null === $row ) {
       
        if($iconType == 'icon') {
          return "<a href='".$this->view->url(array('controller' => 'friends', 'action' => 'add', 'user_id' => $user->user_id), 'user_extended', true)."' class='btn btn-alt smoothbox' data-icontype='".$iconType ."' data-bs-toggle='tooltip' data-bs-title='".$this->view->translate('Add Friend')."'><i class='icon_friend_add'></i></a>";
        } else {
          return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'add', 'user_id' => $user->user_id), $this->view->translate('Add Friend'), array('class' => 'buttonlink smoothbox icon_friend_add'));
        }
      } else if( $row->user_approved == 0 ) {
        if($iconType == 'icon') {
          return "<a href='".$this->view->url(array('controller' => 'friends', 'action' => 'cancel', 'user_id' => $user->user_id), 'user_extended', true)."' class='btn btn-alt smoothbox' data-icontype='".$iconType ."' data-bs-toggle='tooltip' data-bs-title='".$this->view->translate('Cancel Friend')."'><i class='icon_friend_cancel'></i></a>";
        } else {
          return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'cancel', 'user_id' => $user->user_id), $this->view->translate('Cancel Request'), array('class' => 'buttonlink smoothbox icon_friend_cancel'));
        }
      } else if( $row->resource_approved == 0 ) {
        if($iconType == 'icon') {
          return "<a href='".$this->view->url(array('controller' => 'friends', 'action' => 'confirm', 'user_id' => $user->user_id), 'user_extended', true)."' class='btn btn-alt smoothbox' data-icontype='".$iconType ."' data-bs-toggle='tooltip' data-bs-title='".$this->view->translate('Accept Friend')."'><i class='icon_friend_add'></i></a>";
        } else {
          return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'confirm', 'user_id' => $user->user_id), $this->view->translate('Accept Request'), array('class' => 'buttonlink smoothbox icon_friend_add'));
        }
      } else if( $row->active ) {
        if($iconType == 'icon') {
          return "<a href='".$this->view->url(array('controller' => 'friends', 'action' => 'remove', 'user_id' => $user->user_id), 'user_extended', true)."' class='btn btn-alt smoothbox' data-icontype='".$iconType ."' data-bs-toggle='tooltip' data-bs-title='".$this->view->translate('Remove Friend')."'><i class='icon_friend_remove'></i></a>";
        } else {
          return $this->view->htmlLink(array('route' => 'user_extended', 'controller' => 'friends', 'action' => 'remove', 'user_id' => $user->user_id), $this->view->translate('Remove Friend'), array('class' => 'buttonlink smoothbox icon_friend_remove'));
        }
      }
    }

    return '';
  }
}
