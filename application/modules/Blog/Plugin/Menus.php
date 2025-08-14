<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Plugin_Menus
{
  public function canCreateBlogs()
  {
    // Must be logged in
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Must be able to create blogs
    if( !Engine_Api::_()->authorization()->isAllowed('blog', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function canViewBlogs()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Must be able to view blogs
    if( !Engine_Api::_()->authorization()->isAllowed('blog', $viewer, 'view') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_BlogQuickStyle($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    
    if( $request->getParam('module') != 'blog' || $request->getParam('action') != 'manage' ) {
      return false;
    }
    
    // Must be able to style blogs
    if( !Engine_Api::_()->authorization()->isAllowed('blog', $viewer, 'style') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_BlogGutterList($row)
  {
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject instanceof User_Model_User ) {
      $user_id = $subject->getIdentity();
    } else if( $subject instanceof Blog_Model_Blog ) {
      $user_id = $subject->owner_id;
    } else {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['user_id'] = $user_id;
    return $params;
  }

  public function onMenuInitialize_BlogGutterShare($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return false;
    }

    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }
    
    $subject = Engine_Api::_()->core()->getSubject();
    if( !($subject instanceof Blog_Model_Blog) ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['type'] = $subject->getType();
    $params['params']['id'] = $subject->getIdentity();
    $params['params']['format'] = 'smoothbox';
    return $params;
  }

  public function onMenuInitialize_BlogGutterReport($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return false;
    }

    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }

    $subject = Engine_Api::_()->core()->getSubject();
    if( ($subject instanceof Blog_Model_Blog) &&
        $subject->owner_id == $viewer->getIdentity() ) {
      return false;
    } else if( $subject instanceof User_Model_User &&
        $subject->getIdentity() == $viewer->getIdentity() ) {
      return false;
    }

    // Modify params
    $subject = Engine_Api::_()->core()->getSubject();
    $params = $row->params;
    $params['params']['subject'] = $subject->getGuid();
    return $params;
  }

  public function onMenuInitialize_BlogGutterSubscribe($row)
  {
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return false;
    }

    // Check subject
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return false;
    }
    
    $subject = Engine_Api::_()->core()->getSubject();
    if( $subject instanceof Blog_Model_Blog ) {
      $owner = $subject->getOwner('user');
    } else if( $subject instanceof User_Model_User ) {
      $owner = $subject;
    } else {
      return false;
    }

    if( $owner->getIdentity() == $viewer->getIdentity() ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $subscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'blog');
    if( !$subscriptionTable->checkSubscription($owner, $viewer) ) {
      $params['label'] = 'Subscribe';
      $params['params']['user_id'] = $owner->getIdentity();
      $params['action'] = 'add';
      $params['class'] = 'buttonlink smoothbox icon_blog_subscribe';
    } else {
      $params['label'] = 'Unsubscribe';
      $params['params']['user_id'] = $owner->getIdentity();
      $params['action'] = 'remove';
      $params['class'] = 'buttonlink smoothbox icon_blog_unsubscribe';
    }

    return $params;
  }

  public function onMenuInitialize_BlogGutterCreate($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $owner = Engine_Api::_()->getItem('user', $request->getParam('user_id'));

    if( $viewer->getIdentity() != $owner->getIdentity() ) {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('blog', $viewer, 'create') ) {
      return false;
    }

    return true;
  }

  public function onMenuInitialize_BlogGutterEdit($row)
  {
    if( !Engine_Api::_()->core()->hasSubject('blog') ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $blog = Engine_Api::_()->core()->getSubject('blog');

    if( !$blog->authorization()->isAllowed($viewer, 'edit') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['blog_id'] = $blog->getIdentity();
    return $params;
  }

  public function onMenuInitialize_BlogGutterDelete($row)
  {
    if( !Engine_Api::_()->core()->hasSubject('blog') ) {
      return false;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $blog = Engine_Api::_()->core()->getSubject('blog');

    if( !$blog->authorization()->isAllowed($viewer, 'delete') ) {
      return false;
    }

    // Modify params
    $params = $row->params;
    $params['params']['blog_id'] = $blog->getIdentity();
    return $params;
  }

  public function onMenuInitialize_BlogGutterStyle($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $owner = Engine_Api::_()->getItem('user', $request->getParam('user_id'));

    if( $viewer->getIdentity() != $owner->getIdentity() ) {
      return false;
    }

    if( !Engine_Api::_()->authorization()->isAllowed('blog', $viewer, 'style') ) {
      return false;
    }

    return true;
  }
}
