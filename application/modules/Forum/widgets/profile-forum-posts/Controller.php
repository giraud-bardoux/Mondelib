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
class Forum_Widget_ProfileForumPostsController extends Engine_Content_Widget_Abstract
{
  protected $_childCount;

  public function indexAction()
  {
    
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject();
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      return $this->setNoRender();
    }
    
    // Get forums allowed to be viewed by current user
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
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('user');
    $postsTable = Engine_Api::_()->getDbtable('posts', 'forum');
    $postsSelect = $postsTable->select()
      ->where('forum_id IN(?)', $forumIds)
      ->where('user_id = ?', $subject->getIdentity())
      ->where('approved = ?', 1)
      ->order('creation_date DESC');

    $this->view->paginator = $paginator = Zend_Paginator::factory($postsSelect);

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 5));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    // Do not render if nothing to show
    if( $paginator->getTotalItemCount() <= 0 ) {
      return $this->setNoRender();
    }

    // Add count to title if configured
    if( $this->_getParam('titleCount', false) && $paginator->getTotalItemCount() > 0 ) {
      $this->_childCount = $paginator->getTotalItemCount();
    }
  }

  public function getChildCount()
  {
    return $this->_childCount;
  }
}
