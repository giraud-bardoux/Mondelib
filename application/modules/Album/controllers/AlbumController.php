<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_AlbumController extends Core_Controller_Action_Standard
{
    public function init()
    {
        if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid()) {
            return;
        }

        if (0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
            null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id))) {
            Engine_Api::_()->core()->setSubject($photo);
        } elseif (0 !== ($album_id = (int) $this->_getParam('album_id')) &&
            null !== ($album = Engine_Api::_()->getItem('album', $album_id))) {
            Engine_Api::_()->core()->setSubject($album);
        }
    }

    public function editAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }
        if (!$this->_helper->requireSubject('album')->isValid()) {
            return;
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
            return;
        }

        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('album_main');

        // Hack navigation
        foreach ($navigation->getPages() as $page) {
            if ($page->route != 'album_general' || $page->action != 'manage') {
                continue;
            }
            $page->active = true;
        }

        // Prepare data
        $this->view->album = $album = Engine_Api::_()->core()->getSubject();
        
        $this->view->category_id = (isset($album->category_id) && $album->category_id != 0) ? $album->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
        $this->view->subcat_id = (isset($album->subcat_id) && $album->subcat_id != 0) ? $album->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
        $this->view->subsubcat_id = (isset($album->subsubcat_id) && $album->subsubcat_id != 0) ? $album->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

        // Make form
        $this->view->form = $form = new Album_Form_Album_Edit();

        if (!$this->getRequest()->isPost()) {
            $form->populate($album->toArray());
            if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('album', Engine_Api::_()->user()->getViewer(), 'allow_network') && $album->networks)
                $form->networks->setValue(explode(',', $album->networks));
            $auth = Engine_Api::_()->authorization()->context;
            $roles = ['owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone'];
            foreach ($roles as $role) {
                if (1 === $auth->isAllowed($album, $role, 'view') && isset($form->auth_view)) {
                    $form->auth_view->setValue($role);
                }
                if (1 === $auth->isAllowed($album, $role, 'comment') && isset($form->auth_comment)) {
                    $form->auth_comment->setValue($role);
                }
                if (1 === $auth->isAllowed($album, $role, 'tag') && isset($form->auth_tag)) {
                    $form->auth_tag->setValue($role);
                }
            }

            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->status = false;
            $validateFields = Engine_Api::_()->core()->validateFormFields($form);
            if(is_countable($validateFields) && engine_count($validateFields)){
              echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
            }
//             $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
//             return;
        }

        // Process
        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $values = $form->getValues();
            if (isset($values['networks'])) {
                $network_privacy = 'network_'. implode(',network_', $values['networks']);
                $values['networks'] = implode(',', $values['networks']);
            }
            if (empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            if (empty($values['auth_comment'])) {
                $values['auth_comment'] = 'owner_member';
            }
            if (empty($values['auth_tag'])) {
                $values['auth_tag'] = 'owner_member';
            }

            $values = array_merge(['view_privacy' => $values['auth_view']], $values);
            $album->setFromArray($values);
            $album->save();
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $album, 'edit');

            // CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;
            $roles = ['owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone'];

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $tagMax = array_search($values['auth_tag'], $roles);

            foreach ($roles as $i => $role) {
                $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($album, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($album, $role, 'tag', ($i <= $tagMax));
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        $db->beginTransaction();
        try {
            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach ($actionTable->getActionsByObject($album) as $action) {
                $action->privacy = isset($values['networks'])? $network_privacy : null;
                $action->save();
                $actionTable->resetActivityBindings($action);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        
        echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'album_general',true)));die;
        //return $this->_helper->redirector->gotoRoute(['action' => 'manage'], 'album_general', true);
    }

    public function viewAction()
    {
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $viewer = Engine_Api::_()->user()->getViewer();
        
        if (!$this->_helper->requireSubject('album')->isValid()) {
            return;
        }

        $this->view->album = $album = Engine_Api::_()->core()->getSubject();
        if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid()) {
            return;
        }
        
        if( !$album || !$album->getIdentity() || ((!$album->approved) && !$album->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_helper->requireSubject->forward();
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
        $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($album);
        if(empty($networkPrivacy))
            return $this->_forward('requireauth', 'error', 'core');
        }

        // Prepare params
        $this->view->page = $page = $this->_getParam('page');

        $sorting = 'ASC';
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.defaultsearch', 0) == 1){
            $sorting = 'oldest';
        } else if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.defaultsearch', 0) == 2){
            $sorting = 'newest';
        } 

        $this->view->sorting = $sorting = $this->_getParam('sorting', $sorting);
        $showprivatephoto = 0;
        if($album->getOwner()->getIdentity() == $viewer->getIdentity() || $viewer->isAdmin()){
            $showprivatephoto = 1;
        }
        // Prepare data
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $this->view->paginator = $paginator = $photoTable->getPhotoPaginator([
            'album' => $album,
            'albumvieworder' => $sorting,
            'showprivatephoto' => $showprivatephoto,
            //'action' => 'browsephoto',
        ]);
        $paginator->setItemCountPerPage($settings->getSetting('photo_page', 12));
        $paginator->setCurrentPageNumber($page);

        // Do other stuff
        $this->view->mine = true;
        $this->view->canEdit = $this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->checkRequire();
        if (!$album->getOwner()->isSelf(Engine_Api::_()->user()->getViewer())) {
            $album->getTable()->update([
                'view_count' => new Zend_Db_Expr('view_count + 1'),
            ], [
                'album_id = ?' => $album->getIdentity(),
            ]);
            $this->view->mine = false;
        }
        $this->view->viewer_id = $viewer->getIdentity();
        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function deleteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $album = Engine_Api::_()->getItem('album', $this->getRequest()->getParam('album_id'));
        if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid()) {
            return;
        }

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        $this->view->form = $form = new Album_Form_Album_Delete();

        if (!$album) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Album doesn't exists or not authorized to delete");
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $album->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Album has been deleted.');
        return $this->_forward('success', 'utility', 'core', [
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(['action' => 'manage'], 'album_general', true),
            'messages' => [$this->view->message]
        ]);
    }

    public function editphotosAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }
        if (!$this->_helper->requireSubject('album')->isValid()) {
            return;
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
            return;
        }

        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('album_main');

        // Hack navigation
        foreach ($navigation->getPages() as $page) {
            if ($page->route != 'album_general' || $page->action != 'manage') {
                continue;
            }
            $page->active = true;
        }

        // Prepare data
        $this->view->album = $album = Engine_Api::_()->core()->getSubject();
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $this->view->paginator = $paginator = $photoTable->getPhotoPaginator([
            'album' => $album,
        ]);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(10);

        // Get albums
        $albumTable = Engine_Api::_()->getItemTable('album');
        $myAlbums = $albumTable->select()
            ->from($albumTable, ['album_id', 'title'])
            ->where('owner_type = ?', 'user')
            ->where('owner_id = ?', Engine_Api::_()->user()->getViewer()->getIdentity())
            ->query()
            ->fetchAll();

        $albumOptions = ['' => ''];
        foreach ($myAlbums as $myAlbum) {
            $albumOptions[$myAlbum['album_id']] = $myAlbum['title'];
        }
        if (engine_count($albumOptions) == 1) {
            $albumOptions = [];
        }

        // Make form
        $this->view->form = $form = new Album_Form_Album_Photos();

        foreach ($paginator as $photo) {
            $subform = new Album_Form_Album_EditPhoto(['elementsBelongTo' => $photo->getGuid()]);
            $subform->populate($photo->toArray());
            $form->addSubForm($subform, $photo->getGuid());
            $form->cover->addMultiOption($photo->getIdentity(), $photo->getIdentity());
            if (empty($albumOptions)) {
                $subform->removeElement('move');
            } else {
                $subform->move->setMultiOptions($albumOptions);
            }

            $tagStr = '';
            foreach( $photo->tags()->getTagMaps() as $tagMap ) {
              $tag = $tagMap->getTag();
              if( !isset($tag->text) ) continue;
              if( '' !== $tagStr ) $tagStr .= ', ';
              $tagStr .= $tag->text;
            }

            $subform->populate(array(
                'tags' => $tagStr,
            ));
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = $album->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $values = $form->getValues();
            if (!empty($values['cover'])) {
                $album->photo_id = $values['cover'];
                $album->save();
            }
            // Process
            foreach ($paginator as $photo) {
                $subform = $form->getSubForm($photo->getGuid());
                $values = $subform->getValues();
                $values = $values[$photo->getGuid()];

                // Add tags
                $tags = preg_split('/[,]+/', trim($values['tags']));
                $photo->tags()->setTagMaps(Engine_Api::_()->user()->getViewer(), $tags);
                unset($values['photo_id']);
                if (isset($values['delete']) && $values['delete'] == '1') {
                    $photo->delete();
                } elseif (!empty($values['move'])) {
                    $nextPhoto = $photo->getNextPhoto();

                    $old_album_id = $photo->album_id;
                    $photo->album_id = $values['move'];
                    $photo->save();

                    // Change album cover if necessary
                    if (($nextPhoto instanceof Album_Model_Photo) &&
                        (int) $album->photo_id == (int) $photo->getIdentity()) {
                        $album->photo_id = $nextPhoto->getIdentity();
                        $album->save();
                    }

                    // Remove activity attachments for this photo
                    Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($photo);
                } else {
                    $photo->setFromArray($values);
                    $photo->save();
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_helper->redirector->gotoRoute(['action' => 'view', 'album_id' => $album->album_id], 'album_specific', true);
    }

    public function orderAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }
        if (!$this->_helper->requireSubject('album')->isValid()) {
            return;
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
            return;
        }

        $album = Engine_Api::_()->core()->getSubject();

        $order = $this->_getParam('order');
        if (!$order) {
            $this->view->status = false;
            return;
        }

        // Get a list of all photos in this album, by order
        $photoTable = Engine_Api::_()->getItemTable('album_photo');
        $currentOrder = $photoTable->select()
            ->from($photoTable, 'photo_id')
            ->where('album_id = ?', $album->getIdentity())
            ->order('order ASC')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

        // Find the starting point?
        $start = null;
        $end = null;
        for ($i = 0, $l = engine_count($currentOrder); $i < $l; $i++) {
            if (engine_in_array($currentOrder[$i], $order)) {
                $start = $i;
                $end = $i + (engine_count($order) -1);
                break;
            }
        }

        if (null === $start || null === $end) {
            $this->view->status = false;
            return;
        }

        for ($i = 0, $l = engine_count($currentOrder); $i < $l; $i++) {
            if ($i >= $start && $i <= $end) {
                $photo_id = $order[$i - $start];
            } else {
                $photo_id = $currentOrder[$i];
            }
            $photoTable->update([
                'order' => $i,
            ], [
                'photo_id = ?' => $photo_id,
            ]);
        }

        $this->view->status = true;
    }


    public function composeUploadAction()
    {
        if (!Engine_Api::_()->user()->getViewer()->getIdentity()) {
            $this->_redirect('login');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
            return;
        }

        if (empty($_FILES['Filedata']) && !$this->_getParam("isUrl")) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        // Get album
        $viewer = Engine_Api::_()->user()->getViewer();
        $table = Engine_Api::_()->getDbtable('albums', 'album');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $type = $this->_getParam('type', 'wall');

            if (empty($type)) {
                $type = 'wall';
            }
            if($type == "wall") {
                $activityFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->view->viewer()->level_id, 'activity_flood');
                if (!empty($activityFlood[0])) {
                    //get last activity
                    $tableFlood = Engine_Api::_()->getDbTable("actions", 'activity');
                    $select = $tableFlood->select()->where("subject_id = ?", $this->view->viewer()->getIdentity())->order("date DESC");
                    if ($activityFlood[1] == "minute") {
                        $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
                    } else if ($activityFlood[1] == "day") {
                        $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
                    } else {
                        $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
                    }
                    $floodItem = $tableFlood->fetchAll($select);
                    if (engine_count($floodItem) && $activityFlood[0] <= engine_count($floodItem)) {
                        $message = Engine_Api::_()->core()->floodCheckMessage($activityFlood, $this->view);
                        $this->view->flood = true;
                        $this->view->status = false;
                        $this->view->error = $message;
                        return;
                    }
                }
            }
            $album = $table->getSpecialAlbum($viewer, $type);

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray([
                'owner_type' => 'user',
                'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
            ]);
            $photo->save();
            if ($album->approved) {
              $photo->resubmit = 1;
              $photo->save();
            }
            $photo->approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $viewer, 'photoapprove');
            $photo->save();
            $isUrl = $this->_getParam('isUrl');
            $photo->setPhoto($isUrl ? $_POST['Filedata'] : $_FILES['Filedata'],$isUrl);

            if ($type == 'message') {
                $photo->title = Zend_Registry::get('Zend_Translate')->_('Attached Image');
                $album->view_privacy = 'owner';
                $album->save();
                $photo->album_id = $album->album_id;
                $photo->save();
            }

            $photo->order = $photo->photo_id;
            $photo->album_id = $album->album_id ? $album->album_id : 0;
            $photo->save();

            if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }
            if ($album->approved) {
                $album->resubmit = 1;
                $album->save();
            }

            if ($type != 'message') {
                // Authorizations
                $auth = Engine_Api::_()->authorization()->context;
                $auth->setAllowed($photo, 'everyone', 'view', true);
                $auth->setAllowed($photo, 'everyone', 'comment', true);
            }

            $db->commit();

            $this->view->status = true;
            $this->view->photo_id = $photo->photo_id;
            $this->view->fileName = $_FILES['Filedata']['name'];
            $this->view->album_id = $album->album_id;
            $this->view->src = $photo->getPhotoUrl();
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('Photo saved successfully');
        } catch (Exception $e) {
            $this->exceptionWrapper($e, null, $db);
            $this->view->status = false;
        }
    }
}
