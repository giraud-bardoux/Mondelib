<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 10013 2013-03-27 00:25:17Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_Widget_ListRecentTopicsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Get forums allowed to be viewed by current user
    $viewer = Engine_Api::_()->user()->getViewer();
    $forumIds = array();
    $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
    $perms = $authTable->select()
        ->where('resource_type = ?', 'forum')
        ->where('action = ?', 'view')
        ->query()
        ->fetchAll();
    foreach( $perms as $perm ) {
      if( $perm['role'] == 'everyone' ) {
        $forumIds[] = $perm['resource_id'];
      } else if( $viewer &&
          $viewer->getIdentity() &&
          $perm['role'] == 'authorization_level' && 
          $perm['role_id'] == $viewer->level_id ) {
        $forumIds[] = $perm['resource_id'];
      }
    }
    if( empty($forumIds) ) {
      return $this->setNoRender();
    }
    
    // Get paginator
    $topicsTable = Engine_Api::_()->getDbtable('topics', 'forum');
    $topicsSelect = $topicsTable->select()
      ->where('forum_id IN(?)', $forumIds)
      ->where('approved = ?', 1)
      ->order('creation_date DESC');

    $this->view->paginator = $paginator = Zend_Paginator::factory($topicsSelect);
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }
  }
}
