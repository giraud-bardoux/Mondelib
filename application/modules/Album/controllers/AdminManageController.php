<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('album_admin_main', array(), 'album_admin_main_manage');

    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $album = Engine_Api::_()->getItem('album', $value);
          $album->delete();
        }
      }
    }
    
    $page = $this->_getParam('page', 1);
    $this->view->paginator = Engine_Api::_()->getItemTable('album')->getAlbumPaginator(array(
      'orderby' => 'admin_id', 'showalbum' => 1
    ));
    $this->view->paginator->setItemCountPerPage(25);
    $this->view->paginator->setCurrentPageNumber($page);

  }

  public function deleteAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->album_id=$id;
    // Check post
    if( $this->getRequest()->isPost())
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();


      try
      {
        $album = Engine_Api::_()->getItem('album', $id);
        // delete the album in the database
        $album->delete();
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
    $this->renderScript('admin-manage/delete.tpl');
  }
  
  
  //Approved Action
  public function approvedAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    
    $this->view->param = $this->_getParam('param');
    $id = $this->_getParam('id');
    // Check post
    if( $this->getRequest()->isPost()) {
      try {
        if (!empty($id)) {
          $item = Engine_Api::_()->getItem('album', $id);
          $item->approved = !$item->approved;
          $item->save();
          
          // Re-index
          Engine_Api::_()->getApi('search', 'core')->index($item);
          
          if ($item->approved) {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $item, 'album_approvedbyadmin', array('album_title' => $item->getTitle(), 'albumowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          } else {
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $item, 'album_disapprovedbyadmin', array('album_title' => $item->getTitle(), 'albumowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          }
          
          if (isset($_POST['photo'])) {
            $photoTable = Engine_Api::_()->getItemTable('album_photo');
            $photos = $photoTable->getPhotoPaginator(array('album_id' => $item->getIdentity()));
            foreach($photos as $photo) {
              $photo->approved = $item->approved;
              $photo->save();
            }
          }
        }
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      if (isset($_POST['photo'])) {
        $message = Zend_Registry::get('Zend_Translate')->_('Album and photos approved successfully.');
      } else {
        $message = Zend_Registry::get('Zend_Translate')->_('Album approved successfully.');
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => [$message]
      ));
    }
    // Output
    $this->renderScript('admin-manage/approved.tpl');
  }
}
