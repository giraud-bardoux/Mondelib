<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Widget_HashtagSearchResultsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $levelId = ($viewer->getIdentity() ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id);
    if(!Engine_Api::_()->authorization()->getPermission($levelId, 'album', 'view')) 
      return $this->setNoRender();
          
    $tag = Zend_Controller_Front::getInstance()->getRequest()->getParam('search', null);

    $widgetId = Engine_Api::_()->getDbTable('content', 'core')->widgetId('album.hashtag-search-results', 'core_hashtag_index');

    $this->view->formValues = array_filter(array('search' => $tag, 'tab' => $widgetId));

    $excludedLevels = array(1, 2, 3);   // level_id of Superadmin,Admin & Moderator
    $registeredPrivacy = array('everyone', 'registered');
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() && !engine_in_array($levelId, $excludedLevels) ) {
      $viewerId = $viewer->getIdentity();
      $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $this->view->viewerNetwork = $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
      if( !empty($viewerNetwork) ) {
        array_push($registeredPrivacy,'owner_network');
      }

      $friendsIds = $viewer->membership()->getMembersIds();
      $friendsOfFriendsIds = $friendsIds;
      foreach( $friendsIds as $friendId ) {
        $friend = Engine_Api::_()->getItem('user', $friendId);
        $friendMembersIds = $friend->membership()->getMembersIds();
        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
      }
    }

    // Prepare data
    $albumTable = Engine_Api::_()->getItemTable('album');
    $select = $albumTable->select()->from($albumTable->info('name'), 'album_id');

    if( !$viewer->getIdentity() ) {
        $select->where("view_privacy = ?", 'everyone');
    } elseif( !engine_in_array($levelId, $excludedLevels) ) {
      $select->Where("owner_id = ?", $viewerId)
          ->orwhere("view_privacy IN (?)", $registeredPrivacy);
      if( !empty($friendsIds) ) {
          $select->orWhere("view_privacy = 'owner_member' AND owner_id IN (?)", $friendsIds);
      }
      if( !empty($friendsOfFriendsIds) ) {
          $select->orWhere("view_privacy = 'owner_member_member' AND owner_id IN (?)", $friendsOfFriendsIds);
      }
      if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
          $select->orWhere("view_privacy = 'owner_network' AND owner_id IN (?)", $friendsOfFriendsIds);
      }

      $subquery = $select->getPart(Zend_Db_Select::WHERE);
      $select ->reset(Zend_Db_Select::WHERE);
      $select ->where(implode(' ',$subquery));
    }

    $select->where("search = 1");
    $select = Engine_Api::_()->network()->getNetworkSelect($albumTable->info('name'), $select);
    $albums = $albumTable->fetchAll($select);
    $albumIds = array();
    foreach ($albums as $album) {
        $albumIds[] = $album->album_id;
    }

    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    
    $page = 1;
    if(Zend_Controller_Front::getInstance()->getRequest()->getParam('tab') == $widgetId) {
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page', 1);
    }
    
    $this->view->paginator = $paginator = $photoTable->getPhotoPaginator(array_merge(['album_ids' => $albumIds, 'order' => 'modified_date'], array('tag' =>  Engine_Api::_()->getDbTable('tags', 'core')->getTagId($tag))));
    $paginator->setCurrentPageNumber($page);
  }
}
