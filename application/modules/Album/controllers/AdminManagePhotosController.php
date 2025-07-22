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
class Album_AdminManagePhotosController extends Core_Controller_Action_Admin
{
  public function indexAction() {
  
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('album_admin_main', array(), 'album_admin_main_managephotos');
      
    $this->view->formFilter = $formFilter = new Album_Form_Admin_ManagePhotos_Filter();
		// Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }
    
    foreach( $_GET as $key => $value ) {
      if( '' === $value ) {
        unset($_GET[$key]);
      } else
				$values[$key]=$value;
    }

    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $photo = Engine_Api::_()->getItem('album_photo', $value);
          $photo->delete();
        }
      }
    }

		$tablePhoto = Engine_Api::_()->getDbtable('photos', 'album');
		$tablePhotoName = $tablePhoto->info('name');
      
    $tableAlbumName = Engine_Api::_()->getDbtable('albums', 'album')->info('name');
		
		$tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
		
    $select = $tablePhoto->select()
              ->from($tablePhotoName)
              ->setIntegrityCheck(false)
              ->joinLeft($tableAlbumName, "$tableAlbumName.album_id = $tablePhotoName.album_id",NULL)
              ->joinLeft($tableUserName, "$tableUserName.user_id = $tablePhotoName.owner_id", 'displayname')
              ->where($tableAlbumName.'.album_id != ?',0)
              ->where($tablePhotoName.'.album_id != ?',0)
              ->order($tablePhotoName.'.photo_id DESC');

    if( !empty($values['title']))
      $select->where($tablePhotoName.'.title LIKE ?',$values['title'] );
    
    if( !empty($values['creation_date']) ) 
      $select->where('date('.$tablePhotoName.'.creation_date) = ?', $values['creation_date'] );
		
		if (!empty($values['owner_name']))
      $select->where($tableUserName . '.displayname LIKE ?', $values['owner_name'] . '%');
      
    if (!empty($values['module'])) {
      $moduleItems = Engine_Api::_()->core()->getModuleItem($values['module']);
      if(engine_count($moduleItems) > 0)
        $select->where($tablePhotoName . '.parent_type IN (?)', $moduleItems);
    }

    $page = $this->_getParam('page', 1);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber( $page );

  }

  public function deleteAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->photo_id=$id;
    // Check post
    if( $this->getRequest()->isPost())
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try
      {
        $album = Engine_Api::_()->getItem('album_photo', $id);
        // delete the album photo in the database
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
    $this->renderScript('admin-manage-photos/delete.tpl');
  }
  
  //Approved Action
  public function approvedAction() {
    $id = $this->_getParam('id');
    if (!empty($id)) {
    
      $item = Engine_Api::_()->getItem('album_photo', $id);
      $item->approved = !$item->approved;
      $item->save();
      
      // Re-index
      Engine_Api::_()->getApi('search', 'core')->index($item);
      
      $album = $item->getParent();
      if ($item->approved) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $album, 'albumphoto_approvedbyadmin', array('album_title' => $album->getTitle(), 'albumphotoowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      } else {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item->getOwner(), $item->getOwner(), $album, 'albumphoto_disapprovedbyadmin', array('album_title' => $album->getTitle(), 'albumphotoowner_title' => $item->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      }
      
    }
    $this->_redirect('admin/album/manage-photos');
  }
}
