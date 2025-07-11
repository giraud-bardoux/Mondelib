<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FavouriteController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

class Core_FavouriteController extends Core_Controller_Action_Standard
{
  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id)) {
      echo json_encode(array('status' => 'false', 'error' => 'Login'));die;
    }

    $type = $this->_getParam('type', null);
    if (empty($type)) {
      echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
      die;
    }

    $id = $this->_getParam('id', 0);
    if (intval($id) == 0) {
      echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
      die;
    }

    $resource = Engine_Api::_()->getItem($type, $id);
    $owner = $resource->getOwner();
    if (!$resource) {
      echo json_encode(array('status' => 'false', 'error' => 'Invalid argument supplied.'));
      die;
    }

    $favouriteTable = Engine_Api::_()->getDbTable('favourites', 'core');

    //get current content type tabel name and content id
    $contentTypeTable = Engine_Api::_()->getItemTable($type);
    $contentTypePrimaryId = current($contentTypeTable->info("primary"));

    $favouriteItem = $favouriteTable->getItemfav($type, $id);
    //Already favourite
    if (!empty($favouriteItem)) {
      //delete
      $db = $favouriteItem->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $favouriteItem->delete();
        if(isset($resource->favourite_count)) {
          $resource->favourite_count--;
         $resource->save();
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      
      //Delete notification
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'favourite', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $resource->getType(), "object_id = ?" => $resource->getIdentity()));

      echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'reduced', 'count' => $resource->favourite_count, 'message' => $this->view->string()->escapeJavascript(Zend_Registry::get('Zend_Translate')->_('Unfavourite Successfully.'))));exit();
    } else {
      //insert
      $db = $favouriteTable->getAdapter();
      $db->beginTransaction();
      try {
        $row = $favouriteTable->createRow();
        $row->user_id = $viewer_id;
        $row->resource_type = $type;
        $row->resource_id = $id;
        $row->save();

        if(isset($resource->favourite_count)) {
          $resource->favourite_count++;
          $resource->save();
        }
        // Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      
      //Add Notification
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $resource, 'favourite');
      }

      echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'increment', 'count' => $resource->favourite_count, 'message' => $this->view->string()->escapeJavascript(Zend_Registry::get('Zend_Translate')->_('Favourite Successfully.'))));exit();
    }
  }
}
