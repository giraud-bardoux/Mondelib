<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10248 2014-05-30 21:48:38Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_IndexController extends Core_Controller_Action_Standard
{
    public function init()
    {
        //$this->getNavigation();

        // only show videos if authorized
        if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) {
            return;
        }

        

        $id = $this->_getParam('video_id', $this->_getParam('id', null));
        if ($id) {
            $video = Engine_Api::_()->getItem('video', $id);
            if ($video) {
                Engine_Api::_()->core()->setSubject($video);
            }
        }
        if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'view')->isValid()) {
            return;
        }
        
    }

    public function browseAction()
    {
        // Permissions
        $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();

        // Prepare
        $viewer = Engine_Api::_()->user()->getViewer();

        // Make form
        // Note: this code is duplicated in the video.browse-search widget
        $this->view->form = $form = new Video_Form_Search();

        // Process form
        if ($form->isValid($this->_getAllParams())) {
            $values = $form->getValues();
        } else {
            $values = array();
        }
        $this->view->formValues = $values;

        $values['status'] = 1;
        $values['search'] = 1;

        $this->view->category = @$values['category'];
        $this->view->text = @$values['text'];


        if (!empty($values['tag'])) {
            $this->view->tag = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
        }
        
        if(!empty($_GET['tag_id']) && isset($_GET['tag_id'])) {
          $values['tag'] = $_GET['tag_id'];
          $this->view->tag = Engine_Api::_()->getItem('core_tag', $_GET['tag_id'])->text;
        }
        
        // check to see if request is for specific user's listings
        $user_id = $this->_getParam('user');
        if ($user_id) {
            $values['user_id'] = $user_id;
        }

        // Get videos
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);
        $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 12);
        $paginator->setItemCountPerPage($items_count);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function createAction()
    {
        if (!$this->_helper->requireUser->isValid()) {
            return;
        }
        if (!$this->_helper->requireAuth()->setAuthParams('video', null, 'create')->isValid()) {
            return;
        }

        // Upload video
        if (isset($_GET['ul'])) {
            return $this->_forward('upload-video', null, null, array('format' => 'json'));
        }


        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);

        $this->view->quota = $quota = Engine_Api::_()
            ->authorization()
            ->getPermission($viewer->level_id, 'video', 'max');
        $this->view->current_count = $paginator->getTotalItemCount();

        $parent_type = $this->_getParam('parent_type');
        $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'video') ) {
                return;
            }
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }
        $this->view->parent_type = $parent_type;
        
        $this->view->category_id = (isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0;
        $this->view->subcat_id = (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0;
        $this->view->subsubcat_id = (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0;
        
        // Create form
        $this->view->form = $form = new Video_Form_Video(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id
        ));
        
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
          if($group->view_privacy == 'member')
            $view_privacy = 'parent_member';
          else 
            $view_privacy = $group->view_privacy;
          $form->getElement('auth_view')->setValue($view_privacy);
        }

        if ($this->_getParam('type', false)) {
            $form->getElement('type')->setValue($this->_getParam('type'));
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        
        $itemFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('video', $this->view->viewer()->level_id, 'flood');
        if(!empty($itemFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("videos",'video');
            $select = $tableFlood->select()->where("owner_id = ?",$this->view->viewer()->getIdentity())->order("creation_date DESC");
            if($itemFlood[1] == "minute"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($itemFlood[1] == "day"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(engine_count($floodItem) && $itemFlood[0] <= engine_count($floodItem)){
                $message = Engine_Api::_()->core()->floodCheckMessage($itemFlood,$this->view);
                $errors[] = array('errorMessage' => $message);
                echo json_encode(array('status' => false, 'error_message' => $errors));die;
            }
        }
        if (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name'])) {
            $_POST['id'] = $this->uploadVideoAction();
        }
        // Process
        $values = $form->getValues();
        if ($values['type'] == 'upload' && empty($_FILES['Filedata']['name'])) {
          $errors[] = array('errorMessage' => $this->view->translate('Please choose a video.'));
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        $values['owner_id'] = $viewer->getIdentity();

        $insertAction = false;

        $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
        $db->beginTransaction();

        try {
            // Create video
            $table = Engine_Api::_()->getDbtable('videos', 'video');
            if ($values['type'] == 'upload') {
                $video = Engine_Api::_()->getItem('video', $this->_getParam('id'));
                unset($values['duration']);
            } else {
                $information = $this->handleIframelyInformation($values['url']);

                if (empty($information)) {
                    $form->addError('We could not find a video there - please check the URL and try again.');
                }
                $values['code'] = $information['code'];
                $values['type'] = $information['type'];
                $values['thumbnail'] = $information['thumbnail'];
                $values['duration'] = $information['duration'];
                $video = $table->createRow();

                if (is_null($values['subcat_id']))
                  $values['subcat_id'] = 0;
                if (is_null($values['subsubcat_id']))
                  $values['subsubcat_id'] = 0;
            }
            
            if (isset($values['networks'])) {
                $network_privacy = 'network_'. implode(',network_', $values['networks']);
                $values['networks'] = implode(',', $values['networks']);
            }

            if (empty($values['auth_view'])) {
                $values['auth_view'] = 'everyone';
            }
            
            //approve setting work
            $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'approve');
            
            $values['view_privacy'] = $values['auth_view'];
            $values['parent_type'] = $parent_type;
            $values['parent_id'] =  $parent_id;
            $video->setFromArray($values);
            $video->save();
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $video);

            // Now try to create thumbnail
            if ($values['type'] !== 'upload') {
                $thumbnail = $values['thumbnail'];
                $thumbnailUrl = explode("?", $thumbnail)[0];
                $ext = ltrim(strrchr($thumbnailUrl, '.'), '.');
                if(strpos($thumbnailUrl,'vimeocdn') !== false){
                    $ext = "png";
                } else if(strpos($thumbnailUrl,'dmcdn') !== false){
                    $ext = "jpeg";
                }
                $thumbnail_parsed = true;
                $content = $this->url_get_contents($thumbnail);
                $tmpFile = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                if ($content) {
                    $valid_thumb = true;
                    file_put_contents($tmpFile, $content);
                } else {
                    $valid_thumb = false;
                }
                if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed) {

                    $thumbFile = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;

                    $image = Engine_Image::factory();
                    $image->open($tmpFile)
                        ->resize(330, 240)
                        ->write($thumbFile)
                        ->destroy();

                    try{
                        $thumbFileRow = Engine_Api::_()->storage()->create($thumbFile, array(
                            'parent_type' => $video->getType(),
                            'parent_id' => $video->getIdentity()
                        ));
                        $video->photo_id = $thumbFileRow->file_id;
                        // Remove temp file
                        @unlink($thumbFile);
                        @unlink($tmpFile);
                    } catch (Exception $e) {
                    }
                }
                $video->status = 1;
                $video->save();
                // Insert new action item
                $insertAction = true;
            }

            // CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;
            if( $values['parent_type'] == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            $authView = $values['auth_view'];
            $viewMax = array_search($authView, $roles);
            foreach ($roles as $i => $role) {
                $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
            }

            if (isset($values['auth_comment'])) {
                $authComment = $values['auth_comment'];
            } else {
                $authComment = "everyone";
            }
            $commentMax = array_search($authComment, $roles);
            foreach ($roles as $i=>$role) {
                $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
            }

            // Add tags
            $tags = preg_split('/[,]+/', $values['tags']);
            $video->tags()->addTagMaps($viewer, $tags);
            
            //Send to all group members
            if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
              $members = Engine_Api::_()->group()->groupMembers($group->getIdentity());
              foreach($members as $member) {
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($member, $viewer, $group, 'group_videocreate');
              }
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
            if ($insertAction) {
              $owner = $video->getOwner();
              
              if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $group, 'group_video_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
              } else {
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'video_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
              }
              if ($action != null) {
                  Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
              }
            }

            // Rebuild privacy
            $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
            foreach ($actionTable->getActionsByObject($video) as $action) {
                $actionTable->resetActivityBindings($action);
            }


            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        
        //Start Send Approval Request to Admin
        Engine_Api::_()->core()->contentApprove($video, 'video');

        if ($video->type == 'upload') {
          echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'video_general',true)));die;
        }
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
          echo json_encode(array('status' => true, 'redirectURL' => $group->getHref()));die;
        } else {
          echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('user_id' => $viewer->getIdentity(), 'video_id' => $video->getIdentity()), 'video_view',true)));die;
        }
    }
    function url_get_contents ($Url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    public function uploadVideoAction()
    {
        if (!$this->_helper->requireUser()->checkRequire()) {
            $this->view->status = false;
            $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $values = $this->getRequest()->getPost();
        $composerType = $this->_getParam('c_type', null);

        if (empty($_FILES['Filedata'])) {
            $this->view->status = false;
            $this->view->error  = Zend_Registry::get('Zend_Translate')->_('No file');
            return;
        }

        if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
            $this->view->status = false;
            $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid Upload').print_r($_FILES, true);
            return;
        }

        $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt');
        if (engine_in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
            $this->view->status = false;
            $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }

        $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $values['owner_id'] = $viewer->getIdentity();

            $params = array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            );
            
            $video = Engine_Api::_()->video()->createVideo($params, $_FILES['Filedata'], $values);

            $this->view->status   = true;
            $this->view->name     = $_FILES['Filedata']['name'];
            $this->view->code = $video->code;
            $this->view->video_id = $video->video_id;
            if(engine_in_array($composerType, array('wall', 'message'))) {
                $this->view->src = "application/modules/Video/externals/images/nophoto_video_thumb_normal.png";
            }

            // sets up title and owner_id now just incase members switch page as soon as upload is completed
            $video->title = $_FILES['Filedata']['name'];
            $video->owner_id = $viewer->getIdentity();

            if($composerType == 'message') {
              $video->parent_type = "messages_message";
            }

            if($composerType == 'wall') {
              $video->uploadtype = "wall";
            }

            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            $auth_view = "everyone";
            $viewMax = array_search($auth_view, $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
            }
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            $auth_comment = "everyone";
            $commentMax = array_search($auth_comment, $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
            }

            $video->save();
            if($video->approved) {
              $video->resubmit = 1;
              $video->save();
            }
            $db->commit();
            return $video->video_id;
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.').$e;
            // throw $e;
            return;
        }
    }

    public function deleteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $video = Engine_Api::_()->getItem('video', $this->getRequest()->getParam('video_id'));
        if (!$this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->isValid()) {
            return;
        }

        // In smoothbox
        $this->_helper->layout->setLayout('default-simple');

        $this->view->form = $form = new Video_Form_Delete();

        if (!$video) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_("Video doesn't exists or not authorized to delete");
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }

        $db = $video->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            Engine_Api::_()->getApi('core', 'video')->deleteVideo($video);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video has been deleted.');
        return $this->_forward('success', 'utility', 'core', array(
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'video_general', true),
            'messages' => array($this->view->message)
        ));
    }

    public function editAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }
        $viewer = Engine_Api::_()->user()->getViewer();

        $video = Engine_Api::_()->getItem('video', $this->_getParam('video_id'));
        //Engine_Api::_()->core()->setSubject($video);
        if (!$this->_helper->requireSubject()->isValid()) {
            return;
        }
        
        $this->view->category_id = (isset($video->category_id) && $video->category_id != 0) ? $video->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
        $this->view->subcat_id = (isset($video->subcat_id) && $video->subcat_id != 0) ? $video->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
        $this->view->subsubcat_id = (isset($video->subsubcat_id) && $video->subsubcat_id != 0) ? $video->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

        if ($viewer->getIdentity() != $video->owner_id && !$this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->isValid()) {
            return $this->_forward('requireauth', 'error', 'core');
        }
        $parent_type = $video->parent_type;
        $parent_id = $video->parent_id;
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'video') ) {
                return;
            }
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }
        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('video_main', array(), 'video_main_manage');

        $this->view->video = $video;
        $this->view->form = $form = new Video_Form_Edit(array(
            'parent_type' => $parent_type,
            'parent_id' => $parent_id
        ));
        $form->getElement('search')->setValue($video->search);
        $form->getElement('title')->setValue($video->title);
        $form->getElement('description')->setValue($video->description);
        $form->getElement('category_id')->setValue($video->category_id);

        // authorization
        $auth = Engine_Api::_()->authorization()->context;
        if($parent_type == 'user') {
          $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        } else if($parent_type = 'group') {
            if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
              $roles = array('owner', 'member', 'parent_member');
            } else {
              $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            }
        }
        
        foreach ($roles as $role) {
            if (1 === $auth->isAllowed($video, $role, 'view')) {
                $form->auth_view->setValue($role);
            }
            if (1 === $auth->isAllowed($video, $role, 'comment')) {
                $form->auth_comment->setValue($role);
            }
        }

        // prepare tags
        $videoTags = $video->tags()->getTagMaps();

        $tagString = '';
        foreach ($videoTags as $tagmap) {
            if ($tagString !== '') {
                $tagString .= ', ';
            }
            $tagString .= $tagmap->getTag()->getTitle();
        }

        $this->view->tagNamePrepared = $tagString;
        $form->tags->setValue($tagString);
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && Engine_Api::_()->authorization()->isAllowed('video', Engine_Api::_()->user()->getViewer(), 'allow_network') && $video->networks)
            $form->networks->setValue(explode(',', $video->networks));

        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
          $this->view->status = false;
        }

        // Process
        $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
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
            $values = array_merge(array('view_privacy' => $values['auth_view']), $values);
            $video->setFromArray($values);
            $video->save();
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $video, 'edit');

            // CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;
            if( $parent_type == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }            
            $authView =$values['auth_view'];
            $viewMax = array_search($authView, $roles);
            foreach ($roles as $i=>$role) {
                $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
            }

            //$roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            if ($values['auth_comment']) {
                $authComment =$values['auth_comment'];
            } else {
                $authComment = "everyone";
            }
            $commentMax = array_search($authComment, $roles);
            foreach ($roles as $i=>$role) {
                $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
            }

            // Add tags
            $tags = preg_split('/[,]+/', $values['tags']);
            $video->tags()->setTagMaps($viewer, $tags);

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
            foreach ($actionTable->getActionsByObject($video) as $action) {
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

        echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('action' => 'manage'), 'video_general',true)));die;
        
        //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'), 'video_general', true);
    }

    public function uploadAction()
    {
        if (isset($_GET['ul']) || isset($_FILES['Filedata'])) {
            return $this->_forward('upload-video', null, null, array('format' => 'json'));
        }

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $this->view->form = $form = new Video_Form_Video();
        $this->view->navigation = $this->getNavigation();

        if (!$this->getRequest()->isPost()) {
            if (null !== ($album_id = $this->_getParam('album_id'))) {
                $form->populate(array(
                    'album' => $album_id
                ));
            }
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $album = $form->saveValues();
        //$this->_helper->redirector->gotoRoute(array('album_id'=>$album->album_id), 'album_editphotos', true);
    }

    public function viewAction()
    {
        //$video_id = $this->_getParam('video_id');
        //$video = Engine_Api::_()->getItem('video', $video_id);
        //if( $video ) Engine_Api::_()->core()->setSubject($video);
        if (!$this->_helper->requireSubject()->isValid()) {
            return;
        }

        $video = Engine_Api::_()->core()->getSubject('video');
        $viewer = Engine_Api::_()->user()->getViewer();

        // if this is sending a message id, the user is being directed from a coversation
        // check if member is part of the conversation
        $message_id = $this->getRequest()->getParam('message');
        $message_view = false;
        if ($message_id) {
            $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
            if ($conversation->hasRecipient(Engine_Api::_()->user()->getViewer())) {
                $message_view = true;
            }
        }
        $this->view->message_view = $message_view;
        if (!$message_view &&
            !$this->_helper->requireAuth()->setAuthParams($video, null, 'view')->isValid()) {
            return;
        }
        
        if( !$video || !$video->getIdentity() || ((!$video->approved) && !$video->isOwner($viewer)) ) {
					if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
					} else
            return $this->_forward('requireauth', 'error', 'core');
        }
        
        if($video->parent_type == 'group' && $video->parent_id) {
          $group = Engine_Api::_()->getItem($video->parent_type, $video->parent_id);
          if( !$group || !$group->getIdentity() || ((!$group->approved) && !$group->isOwner($viewer)) ) {
            if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
            } else
              return $this->_forward('requireauth', 'error', 'core');
          }
          $viewPermission = $group->authorization()->isAllowed($viewer, 'view');
          if(empty($viewPermission)) {
            return $this->_forward('requireauth', 'error', 'core');
          }
        }

        // Network check
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
            $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($video);
            if(empty($networkPrivacy))
                return $this->_forward('requireauth', 'error', 'core');
        }

        $this->view->videoTags = $video->tags()->getTagMaps();

        // Check if edit/delete is allowed
        $this->view->can_edit = $can_edit = $this->_helper->requireAuth()->setAuthParams($video, null, 'edit')->checkRequire();
        $this->view->can_delete = $can_delete = $this->_helper->requireAuth()->setAuthParams($video, null, 'delete')->checkRequire();

        // check if embedding is allowed
        $can_embed = true;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
            $can_embed = false;
        } elseif (isset($video->allow_embed) && !$video->allow_embed) {
            $can_embed = false;
        }
        $this->view->can_embed = $can_embed;

        // increment count
        $embedded = "";
        if ($video->status == 1) {
            if (!$video->isOwner($viewer)) {
                $video->view_count++;
                $video->save();
            }
            $embedded = $video->getRichContent(true);
        }

        if ($video->type == 'upload' && $video->status == 1) {
            if (!empty($video->file_id)) {
                $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
                if ($storage_file) {
                    $this->view->video_location = $storage_file->map();
                    $this->view->video_extension = $storage_file->extension;
                }
            }
        }

        $this->view->viewer_id = $viewer->getIdentity();
        $this->view->video = $video;
        $this->view->videoEmbedded = $embedded;
        if ($video->category_id) {
            $this->view->category = Engine_Api::_()->video()->getCategory($video->category_id);
        }

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function manageAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }
        $this->view->can_create = $this->_helper->requireAuth()->setAuthParams('video', null, 'create')->checkRequire();

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // prepare categories
        $this->view->form = $form = new Video_Form_Search();
        // Populate form
        $this->view->categories = $categories = Engine_Api::_()->video()->getCategories();

        // Process form
        $form->isValid($this->_getAllParams());
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $this->view->category = $values['category'];
        $values['showvideo'] = 1;
        $values['actionName'] = 'manage';
        
        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);

        $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 12);
        $this->view->paginator->setItemCountPerPage($items_count);

        $this->view->paginator->setCurrentPageNumber($this->_getParam('page', 1));

        // maximum allowed videos
        $this->view->quota = $quota = (int) Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
        $this->view->current_count = $paginator->getTotalItemCount();
    }

    public function composeUploadAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$viewer->getIdentity()) {
            $this->_redirect('login');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
            return;
        }

        $video_title = $this->_getParam('title');
        $video_url = $this->_getParam('uri');
        $video_type = $this->_getParam('type');
        $composerType = $this->_getParam('c_type', 'wall');
        if($composerType == "wall") {
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
                    $this->view->message = $message;
                    return;
                }
            }
        }
        // check to make sure the user has not met their quota of # of allowed video uploads
        // set up data needed to check quota
        $values['user_id'] = $viewer->getIdentity();
        $paginator = Engine_Api::_()->getDbTable('videos', 'video')->getVideosPaginator($values);
        $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');
        $current_count = $paginator->getTotalItemCount();

        if (($current_count >= $quota)&& !empty($quota)) {
            // return error message
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You have already uploaded the maximum number of videos allowed. If you would like to upload a new video, please delete an old one first.');
            return;
        }
        $information = $this->handleIframelyInformation($video_url);
        if (empty($information)) {
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('We could not find a video there - please check the URL and try again.');
            return;
        }
        $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
        $db->beginTransaction();

        try {
        
            // create video
            $table = Engine_Api::_()->getDbtable('videos', 'video');
            $video = $table->createRow();
            $video->title = $information['title'];
            $video->description = $information['description'];
            $video->duration = $information['duration'];
            $video->owner_id = $viewer->getIdentity();
            $video->code = $information['code'];
            $video->type = $information['type'] ? $information['type'] : $video_type;
            
            //approve setting work
            $video->approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $viewer, 'approve');
            $video->save();
            
            if($video->approved) {
              $video->resubmit = 1;
              $video->save();
            }
              
            // Now try to create thumbnail
            if ($information['thumbnail']) {
                $thumbnail = $information['thumbnail'];
                $thumbnailUrl = explode("?", $thumbnail)[0];
                $ext = ltrim(strrchr($thumbnailUrl, '.'), '.');
                if(!$ext || strpos($thumbnailUrl,'vimeocdn') !== false){
                    $ext = "png";
                }
                if(!$ext || strpos($thumbnailUrl,'dmcdn') !== false){
                    $ext = "jpg";
                }
                $content = $this->url_get_contents($thumbnail);
                $tmpFile = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                if ($content) {
                    $valid_thumb = true;
                    file_put_contents($tmpFile, $content);
                } else {
                    $valid_thumb = false;
                }

                $thumbnailParsed = true;
                $validThumb = @GetImageSize($tmpFile) ? true : false;
                if ($validThumb && $thumbnail && $ext && $thumbnailParsed) {
                    $thumbFile = APPLICATION_PATH . '/temporary/link_thumb_'.md5($thumbnail).'.'.$ext;

                    $image = Engine_Image::factory();
                    $image->open($tmpFile)
                        ->resize(330, 240)
                        ->write($thumbFile)
                        ->destroy();

                    $thumbFileRow = Engine_Api::_()->storage()->create($thumbFile, array(
                        'parent_type' => $video->getType(),
                        'parent_id' => $video->getIdentity()
                    ));
                    $video->photo_id = $thumbFileRow->file_id;
                }
            }
            // If video is from the composer, keep it hidden until the post is complete
            if ($composerType) {
                $video->search = 0;
            }
            $video->status = 1;
            $video->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }


        // make the video public
        if ($composerType === 'wall') {
            // CREATE AUTH STUFF HERE
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            foreach ($roles as $i => $role) {
                $auth->setAllowed($video, $role, 'view', ($i <= $roles));
                $auth->setAllowed($video, $role, 'comment', ($i <= $roles));
            }
        }

        $this->view->status = true;
        $this->view->video_id = $video->video_id;
        $this->view->photo_id = $video->photo_id;
        $this->view->title = $video->title;
        $this->view->description = $video->description;
        $photoUrl = $video->getPhotoUrl();
        $this->view->src = $photoUrl ? $photoUrl : '';
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video posted successfully');
    }

    public function getIframelyInformationAction()
    {
        $url = trim(strip_tags($this->_getParam('uri')));
        $ajax = $this->_getParam('ajax', false);
        $information = $this->handleIframelyInformation($url);
        $this->view->ajax = $ajax;
        $this->view->valid = !empty($information['code']);
        $this->view->iframely = $information;
    }

    public function getNavigation()
    {
        $this->view->navigation = $navigation = new Zend_Navigation();
        $navigation->addPage(array(
            'label' => 'Browse Videos',
            'route' => 'video_general',
            'action' => 'browse',
            'controller' => 'index',
            'module' => 'video'
        ));

        if (Engine_Api::_()->user()->getViewer()->getIdentity()) {
            $navigation->addPages(array(
                array(
                    'label' => 'My Videos',
                    'route' => 'video_general',
                    'action' => 'manage',
                    'controller' => 'index',
                    'module' => 'video'
                ),
                array(
                    'label' => 'Post New Video',
                    'route' => 'video_general',
                    'action' => 'create',
                    'controller' => 'index',
                    'module' => 'video'
                )
            ));
        }

        return $navigation;
    }

    // HELPER FUNCTIONS

    public function handleIframelyInformation($uri) {

			$iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
			if (parse_url($uri, PHP_URL_SCHEME) === null) {
					$uri = "http://" . $uri;
			}
			$uriHost = Zend_Uri::factory($uri)->getHost();
			if ($iframelyDisallowHost && engine_in_array($uriHost, $iframelyDisallowHost)) {
					return;
			}

			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey') && engine_in_array($uriHost, array('youtube.com','www.youtube.com','youtube', 'youtu.be'))){
				return $this->YoutubeVideoInfo($uri);
			} else {
				$config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
				$iframely = Engine_Iframely::factory($config)->get($uri, 'video');
			}

			if (!engine_in_array('player', array_keys($iframely['links']))) {
					return;
			}
			
			$information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
			if (!empty($iframely['links']['thumbnail'])) {
					$information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
					if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
							$information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
							$information['thumbnail'] = "http://" . $information['thumbnail'];
					}
			}
			if (!empty($iframely['meta']['title'])) {
					$information['title'] = $iframely['meta']['title'];
			}
			if (!empty($iframely['meta']['description'])) {
					$information['description'] = $iframely['meta']['description'];
			}
			if (!empty($iframely['meta']['duration'])) {
					$information['duration'] = $iframely['meta']['duration'];
			}
			if (!empty($iframely['meta']['site_name'])) {
					$information['type'] = $iframely['meta']['site_name'];
			} else {
        $information['type'] = 'iframely';
			}
			$information['code'] = $iframely['html'];
			return $information;
    }
    
    public function getYoutubeIdFromUrl($url) {
      $parts = parse_url($url);
      if(isset($parts['query'])) {
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
          return $qs['v'];
        } else if(isset($qs['vi'])){
          return $qs['vi'];
        }
      }
      if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
      }
      return false;
    }
    
    public function geLinkContents($url) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
    }
    
    public function getLinkData($uri) {
    
			$doc = new DOMDocument("1.0", 'utf-8');
			$html = $this->geLinkContents($uri);

			$encoding = 'utf-8';
			preg_match('/<html(.*?)>/i', $html, $regMatches);
			preg_match('/<meta[^<].*charset=["]?([\w-]*)["]?/i', $html, $charSetMatches);
			if (isset($charSetMatches[1])) {
				$encoding = $charSetMatches[1];
			} elseif(isset($regMatches[1])) {
				preg_match('/lang=["|\'](.*?)["|\']/is', $regMatches[1], $languages);
				if(isset($languages[1]) && in_array($languages[1], ['uk'])) {
					$encoding = 'Windows-1251';
				}
			}
			$contentType = '<meta http-equiv="Content-Type" content="text/html; charset=' . $encoding . '">';
			$html = str_replace('<head>', '<head>' . $contentType, $html);

			if (function_exists('mb_convert_encoding')) {
				@$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $encoding));
			} else {
				@$doc->loadHTML($html);
			}
			
			$metaList = $doc->getElementsByTagName("meta");
			foreach ($metaList as $iKey => $meta) {
				$type = $meta->getAttribute('property');
				$content = $meta->getAttribute('content');
				if(empty($type)) {
					$type = $meta->getAttribute('name');
				}
				$iframely[$type] = $content;
			}

			$information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
			
			//Get OG Title
			if(!empty($iframely['og:title'])) {
				$information['title'] = $iframely['og:title'];
			} else {
				$titleList = $doc->getElementsByTagName("title");
				if ($titleList->length > 0) {
					$information['title'] = $titleList->item(0)->nodeValue;
				} else {
					$information['title'] = '';
				}
			}
			
			//Get OG Description
			if(!empty($iframely['og:description'])) {
				$information['description'] = $iframely['og:description'];
			} else {
				$titleList = $doc->getElementsByTagName("description");
				if ($titleList->length > 0) {
					$information['description'] = $titleList->item(0)->nodeValue;
				} else {
					$information['description'] = '';
				}
			}

			//Get OG Image
			if (!empty($iframely['og:image'])) {
				$information['thumbnail'] = $iframely['og:image'];
			}

			//Get video duration for Dailymotion and Vimeo for special case
			if(preg_match('/dailymotion/',$sUrl)) {
				$information['duration'] = isset($iframely['video:duration']) ? $iframely['video:duration'] : (isset($iframely['duration']) ? $iframely['duration'] : '');
			} elseif (preg_match('/vimeo/', $sUrl)) {
				$aScript = $doc->getElementsByTagName('script');
				$iVimeoDuration = 0;
				foreach($aScript as $script) {
					if(preg_match('/(.*?)duration":{"raw":(.*?),/',$script->textContent, $aHtmlMatch)) {
						$iVimeoDuration = (int)$aHtmlMatch[2];
						break;
					}
				}
				if(!empty($iVimeoDuration)) {
					$information['duration'] = $iVimeoDuration;
				}
			}
			//Get OG Enbed URL
			$embedUrl = $iframely['og:video:url'] ? $iframely['og:video:url'] : $uri;
			$information['code'] = '<iframe width="480" height="270" src="'.$embedUrl.'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

			return $information;
    }

    public function YoutubeVideoInfo($uri) {
    
			$key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
			if(empty($key)) {
				$information = $this->getLinkData($uri);
			} else {
				$video_id = $this->getYoutubeIdFromUrl($uri);
				$url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key='.$key.'&part=snippet,player,contentDetails';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$response = curl_exec($ch);
				curl_close($ch);
				$response_a = json_decode($response,TRUE);    
				$iframely =  $response_a['items'][0];
				if (!engine_in_array('player', array_keys($iframely))) {
						return;
				}
				$information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
				if (!empty($iframely['snippet']['thumbnails'])) {
					$information['thumbnail'] = $iframely['snippet']['thumbnails']['high']['url'];
					if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
						$information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
						$information['thumbnail'] = "http://" . $information['thumbnail'];
					}
				}
				if (!empty($iframely['snippet']['title'])) {
						$information['title'] = $iframely['snippet']['title'];
				}
				if (!empty($iframely['snippet']['description'])) {
						$information['description'] = $iframely['snippet']['description'];
				}
				if (!empty($iframely['contentDetails']['duration'])) {
						$information['duration'] =  Engine_Date::convertISO8601IntoSeconds($iframely['contentDetails']['duration']);
				}
				$information['code'] = $iframely['player']['embedHtml'];
				$information['type'] = "YouTube";
			}
			return $information; 
    }
    
    public function subcategoryAction() {

      $category_id = $this->_getParam('category_id', null);
      $CategoryType = $this->_getParam('type', null);
      $selected = $this->_getParam('selected', null);
      if ($category_id) {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'video');
        $category_select = $categoryTable->select()
                                        ->from($categoryTable->info('name'))
                                        ->where('subcat_id = ?', $category_id);
        $subcategory = $categoryTable->fetchAll($category_select);
        $count_subcat = engine_count($subcategory->toarray());

        $data = '';
        if ($subcategory && $count_subcat) {
          if ($CategoryType == 'search') {
            $data .= '<option value="0">' . Zend_Registry::get('Zend_Translate')->_("Choose 2nd Level Category") . '</option>';
            foreach ($subcategory as $category) {
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
            }
          } else {
            $data .= '<option value=""></option>';
            foreach ($subcategory as $category) {
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
            }

          }
        }
      } else
        $data = '';
      echo $data;die;
    }

    public function subsubcategoryAction() {

      $category_id = $this->_getParam('subcategory_id', null);
      $CategoryType = $this->_getParam('type', null);
      $selected = $this->_getParam('selected', null);
      if ($category_id) {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'video');
        $category_select = $categoryTable->select()
          ->from($categoryTable->info('name'))
          ->where('subsubcat_id = ?', $category_id);
        $subcategory = $categoryTable->fetchAll($category_select);
        $count_subcat = engine_count($subcategory->toarray());

        $data = '';
        if ($subcategory && $count_subcat) {
          $data .= '<option value=""></option>';
          foreach ($subcategory as $category) {
            $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '">' . Zend_Registry::get('Zend_Translate')->_($category["category_name"]) . '</option>';
          }

        }
      } else
        $data = '';
      echo $data;
      die;
    }

    //ACTION FOR Video DELETE
    public function removeAction()
    {
      if (empty($_GET['video_id']))
        die('error');

      $id = (int) $this->_getParam('video_id');
      $item = Engine_Api::_()->getItem('video', $id);
      $db = Engine_Api::_()->getDbTable('videos', 'video')->getAdapter();
      $db->beginTransaction();
      try {
        $item->delete();
        $db->commit();
        echo json_encode(array('status' => "true"));
        die;
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
}
