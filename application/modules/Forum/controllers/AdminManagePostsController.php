<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManagePostsControllers.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Forum_AdminManagePostsController extends Core_Controller_Action_Admin {
  
  public function indexAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('forum_admin_main', array(), 'forum_admin_main_managepost');

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $item = Engine_Api::_()->getItem('forum_post', $value);
          if($item)
            $item->delete();
        }
      }
    }
    
    // Make paginator
    $table = Engine_Api::_()->getItemTable('forum_post');
    $select = $table->select()
              ->order('post_id DESC');;
      
    if ($this->_getParam('search', false)) {
      $select->where('title LIKE ? OR description LIKE ?', $this->_getParam('search').'%');
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
  }
  
  public function deleteAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->blog_id=$id;
    // Check post
    if( $this->getRequest()->isPost() )
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try
      {
        $item = Engine_Api::_()->getItem('forum_post', $id);
        // delete the topic entry into the database
        $item->delete();
        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-manage-posts/delete.tpl');
  }
  
  //Approved Action
  public function approvedAction() {
  
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('forum_post', $id);
      $item->approved = !$item->approved;
      $item->save();
      
      // Re-index
      Engine_Api::_()->getApi('search', 'core')->index($item);
      
      $topic = $item->getParent();
      if ($item->approved) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $topic, 'forumpost_approvedbyadmin', array('topic_title' => $topic->getTitle(), 'topicpostowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      } else {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $topic, 'forumpost_disapprovedbyadmin', array('topic_title' => $topic->getTitle(), 'topicpostowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      }
    }
    $this->_redirect('admin/forum/manage-posts');
  }
  
  public function readpostAction() {
    $id = $this->_getParam('id', null);
    $this->view->forumPost = Engine_Api::_()->getItem('forum_post', $id);
  }
}
