<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PostController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Event_PostController extends Core_Controller_Action_Standard
{
  public function init()
  {
    if( Engine_Api::_()->core()->hasSubject() ) return;

    if( 0 !== ($post_id = (int) $this->_getParam('post_id')) &&
        null !== ($post = Engine_Api::_()->getItem('event_post', $post_id)) )
    {
      Engine_Api::_()->core()->setSubject($post);
    }
    
    $this->_helper->requireUser->addActionRequires(array(
      'edit',
      'delete',
    ));

    $this->_helper->requireSubject->setActionRequireTypes(array(
      'edit' => 'event_post',
      'delete' => 'event_post',
    ));
  }
  
  public function editAction()
  {
    $post = Engine_Api::_()->core()->getSubject('event_post');
    $event = $post->getParent('event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$event->isOwner($viewer) && !$post->isOwner($viewer) ) {
      if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid() ) {
        return;
      }
    }

    $this->view->form = $form = new Event_Form_Post_Edit();

    if( !$this->getRequest()->isPost() )
    {
      $form->populate($post->toArray());
      $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('event_html', 0);
      $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('event_bbcode', 0);
      $body = $post->body;
      if( $allowHtml ) {
        $body = preg_replace_callback('/href=["\']?([^"\'>]+)["\']?/', function($matches) {
            return 'href="' . str_replace(['&gt;', '&lt;'], '', $matches[1]) . '"';
        }, $body);
      } else {
        $body = htmlspecialchars_decode($body, ENT_COMPAT);
      }
      $form->body->setValue($body);      
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->core()->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields)){
        echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
      }
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $post->setFromArray($form->getValues());
      $post->modified_date = date('Y-m-d H:i:s');
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $allowHtml = (bool) $settings->getSetting('event_html', 0);
      $allowBbcode = (bool) $settings->getSetting('event_bbcode', 0);
      if (!$allowBbcode && !$allowHtml ) {
        $post->body = htmlspecialchars($post->body, ENT_NOQUOTES, 'UTF-8');
      }
      $post->save();
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($post->body, $post);
      
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      $errors[] = array('errorMessage' => $e->getMessage());
      echo json_encode(array('status' => false, 'error_message' => $errors));die;
    }
    // Redirect to the post
    //$this->_redirectCustom($post);
    echo json_encode(array('status' => true, 'redirectURL' => $post->getHref()));die;
  }

  public function deleteAction()
  {
    $post = Engine_Api::_()->core()->getSubject('event_post');
    $event = $post->getParent('event');
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$event->isOwner($viewer) && !$post->isOwner($viewer) ) {
      if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid() ) {
        return;
      }
    }

    $this->view->form = $form = new Event_Form_Post_Delete();

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = $post->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {

      $topic_id = $post->topic_id;
      Engine_Api::_()->getItemTable('storage_file')->deleteTinyMceFiles($post);
      $post->delete();

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Try to get topic
    $topic = Engine_Api::_()->getItem('event_topic', $topic_id);
    $href = ( null === $topic ? $event->getHref() : $topic->getHref() );
    return $this->_forward('success', 'utility', 'core', array(
      'closeSmoothbox' => true,
      'parentRedirect' => $href,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
    ));
  }
}
