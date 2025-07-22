<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhotoController.php 10174 2014-04-21 17:01:46Z lucas $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_PhotoController extends Core_Controller_Action_Standard
{
    public function init()
    {
        if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid() ) return;

        if( 0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
            null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id)) )
        {
            Engine_Api::_()->core()->setSubject($photo);
        }
    }

    public function viewAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;

        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->photo = $photo = Engine_Api::_()->core()->getSubject();
        $this->view->album = $album = $photo->getAlbum();

        if(!empty($photo->feedupload))
        return $this->_forward('notfound', 'error', 'core');
        
        if( !$album || !$album->getIdentity() || ((!$album->approved) && !$album->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_helper->requireSubject->forward();
        }
        if( !$photo || !$photo->getIdentity() || ((!$photo->approved) && !$photo->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_helper->requireSubject->forward();
        }

        if( !$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer) ) {
            $photo->view_count = new Zend_Db_Expr('view_count + 1');
            $photo->save();
        }

        //Check for feed photo privacy
        if(!empty($photo->parent_type) && !empty($photo->parent_id) && $photo->parent_type == 'activity_action') {
          $action = Engine_Api::_()->getItem($photo->parent_type, $photo->parent_id);
          if($action) {
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            $actions = $actionTable->getActivity($viewer, array('action_id' => $photo->parent_id));
            if(!$actions) {
              return $this->_forward('requireauth', 'error', 'core');
            }
          }
        }

        // if this is sending a message id, the user is being directed from a coversation
        // check if member is part of the conversation
        $message_id = $this->getRequest()->getParam('message');
        $message_view = false;
        if ($message_id){
            $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
            if($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) $message_view = true;
        }
        $this->view->message_view = $message_view;
        $this->view->isprivate = 0;
        if(engine_in_array($album->type, array("group","event"))){
            $this->view->isprivate = 1;
            if($album->getOwner()->getIdentity() == $viewer->getIdentity()){
                $this->view->isprivate = 0;
            }
        }
       
        //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid() ) return;
        if(!$message_view && !$this->_helper->requireAuth()->setAuthParams($photo, null, 'view')->isValid() ) return;

        $checkAlbum = Engine_Api::_()->getItem('album', $this->_getParam('album_id'));
        if( !($checkAlbum instanceof Core_Model_Item_Abstract) || !$checkAlbum->getIdentity() || $checkAlbum->album_id != $photo->album_id )
        {
            $this->_forward('requiresubject', 'error', 'core');
            return;
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
        $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($checkAlbum);
        if(empty($networkPrivacy))
            return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->canEdit = $canEdit = $album->authorization()->isAllowed($viewer, 'edit');
        $this->view->canDelete = $canDelete = $album->authorization()->isAllowed($viewer, 'delete');
        $this->view->canTag = $canTag = $album->authorization()->isAllowed($viewer, 'tag');
        $this->view->canUntagGlobal = $canUntag = $album->isOwner($viewer);

        $this->view->photoTags = $photo->tags()->getTagMaps();
        $this->view->nextPhoto = $photo->getNextPhoto();
        $this->view->previousPhoto = $photo->getPreviousPhoto();

        // Get tags
        $tags = array();
        foreach( $photo->tags()->getTagMaps() as $tagmap ) {
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        $this->view->tags = $tags;
        $this->view->viewer_id = $viewer->getIdentity();
        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function deleteAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid() ) return;

        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->core()->getSubject('album_photo');
        $album = $photo->getParent();
        
        $owner = Engine_Api::_()->getItem('user', $album->owner_id);

        $this->view->form = $form = new Album_Form_Photo_Delete();

        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        try {
            // delete files from server
            $filesDB = Engine_Api::_()->getDbtable('files', 'storage');
            
            $thumbPath = $filesDB->fetchRow($filesDB->select()->where('parent_file_id = ?', $photo->file_id))->storage_path;
            Engine_Api::_()->storage()->deleteExternalsFiles($thumbPath);

            $filePath = $filesDB->fetchRow($filesDB->select()->where('file_id = ?', $photo->file_id))->storage_path;
            Engine_Api::_()->storage()->deleteExternalsFiles($filePath);

            // Delete image and thumbnail
            $filesDB->delete(array('file_id = ?' => $photo->file_id));
            $filesDB->delete(array('parent_file_id = ?' => $photo->file_id));

            // Check activity actions
            $attachDB = Engine_Api::_()->getDbtable('attachments', 'activity');
            $actions =  $attachDB->fetchAll($attachDB->select()->where('type = ?', 'album_photo')->where('id = ?',$photo->photo_id));
            $actionsDB = Engine_Api::_()->getDbtable('actions', 'activity');

            foreach($actions as $action){
                $action_id = $action->action_id;
                $attachDB->delete(array('type = ?' => 'album_photo', 'id = ?' => $photo->photo_id));

                $action =  $actionsDB->fetchRow($actionsDB->select()->where('action_id = ?', $action_id));
								if(is_array($action->params))
                $count = $action->params['count'];
                if( !is_null($count) && ($count > 1) ) {
                    $action->params = array('count' => (integer)$count-1);
                    $action->save();
                }
                else {
                    $action->delete();
                }
            }
            
            //If album photo delete then check profile photo also setup to zero.
            if($owner->photo_id == $photo->file_id) {
							$owner->photo_id = 0;
							$owner->save();
            }

            // delete photo
            Engine_Api::_()->getItem('album_photo', $photo->photo_id)->delete();
        } catch( Exception $e ) {
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
            'layout' => 'default-simple',
            'parentRedirect' => $album->getHref(),
        ));
    }

    public function editAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->core()->getSubject('album_photo');

        $this->view->form = $form = new Album_Form_Photo_Edit();

        $form->populate($photo->toArray());
        
        $tagStr = '';
        foreach( $photo->tags()->getTagMaps() as $tagMap ) {
            $tag = $tagMap->getTag();
            if( !isset($tag->text) ) continue;
            if( '' !== $tagStr ) $tagStr .= ', ';
            $tagStr .= $tag->text;
        }

        $form->populate(array(
            'tags' => $tagStr,
        ));

        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = $form->getValues();

        $db = $photo->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $tags = preg_split('/[,]+/', trim($values['tags']));
            $photo->tags()->setTagMaps(Engine_Api::_()->user()->getViewer(), $tags);
            $photo->setFromArray($values);
            $photo->save();
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
            'layout' => 'default-simple',
            'parentRefresh' => true,
        ));
    }

    public function rotateAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

        if( !$this->getRequest()->isPost() ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid method');
            return;
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->core()->getSubject('album_photo');

        $angle = (int) $this->_getParam('angle', 90);
        if( !$angle || !($angle % 360) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid angle, must not be empty');
            return;
        }
        if( !engine_in_array((int)$angle, array(90, 270)) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid angle, must be 90 or 270');
            return;
        }

        // Get file
        $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        if( !($file instanceof Storage_Model_File) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Could not retrieve file');
            return;
        }

        // Pull photo to a temporary file
        $tmpFile = $file->temporary();

        // Operate on the file
        $image = Engine_Image::factory();
        $image->open($tmpFile)
            ->rotate($angle)
            ->write()
            ->destroy()
        ;

        // Set the photo
        $db = $photo->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $photo->setPhoto($tmpFile);
            @unlink($tmpFile);
            $db->commit();
        } catch( Exception $e ) {
            @unlink($tmpFile);
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->href = $photo->getPhotoUrl();
    }

    public function flipAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

        if( !$this->getRequest()->isPost() ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid method');
            return;
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->core()->getSubject('album_photo');

        $direction = $this->_getParam('direction');
        if( !engine_in_array($direction, array('vertical', 'horizontal')) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid direction');
            return;
        }

        // Get file
        $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        if( !($file instanceof Storage_Model_File) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Could not retrieve file');
            return;
        }

        // Pull photo to a temporary file
        $tmpFile = $file->temporary();

        // Operate on the file
        $image = Engine_Image::factory();
        $image->open($tmpFile)
            ->flip($direction != 'vertical')
            ->write()
            ->destroy()
        ;

        // Set the photo
        $db = $photo->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $photo->setPhoto($tmpFile);
            @unlink($tmpFile);
            $db->commit();
        } catch( Exception $e ) {
            @unlink($tmpFile);
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->href = $photo->getPhotoUrl();
    }

    public function cropAction()
    {
        if( !$this->_helper->requireSubject('album_photo')->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) return;

        if( !$this->getRequest()->isPost() ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid method');
            return;
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $photo = Engine_Api::_()->core()->getSubject('album_photo');

        $x = (int) $this->_getParam('x', 0);
        $y = (int) $this->_getParam('y', 0);
        $w = (int) $this->_getParam('w', 0);
        $h = (int) $this->_getParam('h', 0);

        // Get file
        $file = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        if( !($file instanceof Storage_Model_File) ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Could not retrieve file');
            return;
        }

        // Pull photo to a temporary file
        $tmpFile = $file->temporary();

        // Open the file
        $image = Engine_Image::factory();
        $image->open($tmpFile);

        $curH = $image->getHeight();
        $curW = $image->getWidth();

        // Check the parameters
        if( $x < 0 ||
            $y < 0 ||
            $w < 0 ||
            $h < 0 ||
            $x + $w > $curW ||
            $y + $h > $curH ) {
            $this->view->status = false;
            $this->view->error = $this->view->translate('Invalid size');
            return;
        }

        $image->open($tmpFile)
            ->crop($x, $y, $w, $h)
            ->write()
            ->destroy()
        ;

        // Set the photo
        $db = $photo->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $photo->setPhoto($tmpFile);
            @unlink($tmpFile);
            $db->commit();
        } catch( Exception $e ) {
            @unlink($tmpFile);
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->href = $photo->getPhotoUrl();
    }
    
		public function removePhotoAction() {
		
			if(empty($_GET['photo_id'])) die('error');
			$photo = Engine_Api::_()->getItem('album_photo', $_GET['photo_id']);
			$db = Engine_Api::_()->getDbTable('photos', 'album')->getAdapter();
			$db->beginTransaction();
			try {
				$photo->delete();
				$db->commit();
				echo json_encode(array('status'=>"true"));die;
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}
		}
}
