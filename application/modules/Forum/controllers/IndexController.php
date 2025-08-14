<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10263 2014-06-06 20:33:21Z lucas $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Forum_IndexController extends Core_Controller_Action_Standard {
	
  public function indexAction() {
  
    if ( !$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid() ) {
      return;
    }

    $categoryTable = Engine_Api::_()->getItemTable('forum_category');
    $this->view->categories = $categoryTable->fetchAll($categoryTable->select()->order('order ASC'));
    
    $forumTable = Engine_Api::_()->getItemTable('forum_forum');
    $forumSelect = $forumTable->select()
      ->order('order ASC')
      ;
    $forums = array();
    foreach( $forumTable->fetchAll() as $forum ) {
      if( Engine_Api::_()->core()->isViewPermission($forum->getIdentity()) ) {
        $order = $forum->order;
        while( isset($forums[$forum->category_id][$order]) ) {
          $order++;
        }
        $forums[$forum->category_id][$order] = $forum;
        ksort($forums[$forum->category_id]);
      }
    }
    $this->view->forums = $forums;
    
    // Render
    $this->_helper->content->setEnabled();
  }

  public function uploadPhotoAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->_helper->layout->disableLayout();

    if( !Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') ) {
      return false;
    }

    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ) return;

    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    if( !isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();

      $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'user',
        'owner_id' => $viewer->getIdentity()
      ));
      $photo->save();

      $photo->setPhoto($_FILES['userfile']);

      $this->view->status = true;
      $this->view->name = $_FILES['userfile']['name'];
      $this->view->photo_id = $photo->photo_id;
      $this->view->photo_url = $photo->getPhotoUrl();

      $table = Engine_Api::_()->getDbtable('albums', 'album');
      $album = $table->getSpecialAlbum($viewer, 'forum');

      $photo->album_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id )
      {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      $auth      = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($photo, 'everyone', 'view',    true);
      $auth->setAllowed($photo, 'everyone', 'comment', true);
      $auth->setAllowed($album, 'everyone', 'view',    true);
      $auth->setAllowed($album, 'everyone', 'comment', true);
      
      $photo->order = $photo->photo_id;
      $photo->save();
      
      $db->commit();

    } catch( Album_Model_Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = $this->view->translate($e->getMessage());
      throw $e;
      return;

    } catch( Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      throw $e;
      return;
    }
  }
}
