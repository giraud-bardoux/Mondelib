<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PostController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_PostController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( 0 !== ($post_id = (int) $this->_getParam('post_id')) &&
        null !== ($post = Engine_Api::_()->getItem('forum_post', $post_id)) &&
        $post instanceof Forum_Model_Post ) {
      Engine_Api::_()->core()->setSubject($post);
    }
  }

  public function deleteAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject('forum_post')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
    $this->view->topic = $topic = $post->getParent();
    $this->view->forum = $forum = $topic->getParent();
    $postEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'post.delete');
    $topicEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'topic.delete');
    if(!$postEdit) {
      return $this->_helper->requireAuth()->forward();
    }
    if(!$topicEdit) {
      return $this->_helper->requireAuth()->forward();
    }

    $this->view->form = $form = new Forum_Form_Post_Delete();

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();

    $topic_id = $post->topic_id;

    try
    {
      Engine_Api::_()->getDbTable('actions', 'activity')->deleteActivityFeed(array('type' => 'forum_topic_reply', "subject_id" => $viewer->getIdentity(), "object_type" => $topic->getType(), "object_id" => $topic->getIdentity()));
      
      Engine_Api::_()->getItemTable('storage_file')->deleteTinyMceFiles($post);
      $post->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    $href = ( null === $topic ? $forum->getHref() : $topic->getHref() );
    return $this->_forward('success', 'utility', 'core', array(
      'closeSmoothbox' => true,
      'parentRedirect' => $href,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
      'format' => 'smoothbox'
    ));
  }

  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }
    if( !$this->_helper->requireSubject('forum_post')->isValid() ) {
      return;
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->post = $post = Engine_Api::_()->core()->getSubject('forum_post');
    $this->view->topic = $topic = $post->getParent();
    $this->view->forum = $forum = $topic->getParent();
    
    $postEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'post.edit');
    $topicEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'topic.edit');
    
    if(!$postEdit) {
      return $this->_helper->requireAuth()->forward();
    }
    
//     if(!$topicEdit) {
//       return $this->_helper->requireAuth()->forward();
//     }

    $this->view->form = $form = new Forum_Form_Post_Edit(array('post'=>$post));

    $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_html', 0);
    $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_bbcode', 0);

    if( $allowHtml ) {
      $body = $post->body;
      $body = preg_replace_callback('/href=["\']?([^"\'>]+)["\']?/', function($matches) {
          return 'href="' . str_replace(['&gt;', '&lt;'], '', $matches[1]) . '"';
      }, $body);
    } else {
      $body = htmlspecialchars_decode($post->body, ENT_COMPAT);
    }
    $form->body->setValue($body);
    $form->photo->setValue($post->file_id);

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $post->body = $values['body'];
      $post->body = Engine_Text_BBCode::prepare($post->body);

      $post->edit_id = $viewer->getIdentity();

      //DELETE photo here.
      if( !empty($values['photo_delete']) && $values['photo_delete'] ) {
        $post->deletePhoto();
      }

      if( !empty($values['photo']) ) {
        $post->setPhoto($form->photo);
      }

      $post->save();
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($post->body, $post);

      $db->commit();

      return $this->_helper->redirector->gotoRoute(array('post_id'=>$post->getIdentity(), 'topic_id' => $post->getParent()->getIdentity()), 'forum_topic', true);
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }
}
