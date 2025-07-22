<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10264 2014-06-06 22:08:42Z lucas $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Sami
 */
class Event_IndexController extends Core_Controller_Action_Standard
{
    public function init()
    {
        if( !$this->_helper->requireAuth()->setAuthParams('event', null, 'view')->isValid() ) return;

        $id = $this->_getParam('event_id', $this->_getParam('id', null));
        if( $id ) {
            $event = Engine_Api::_()->getItem('event', $id);
            if( $event ) {
                Engine_Api::_()->core()->setSubject($event);
            }
        }
    }
   
    public function browseAction()
    {
        // Prepare
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('event', null, 'create');


        $filter = $this->_getParam('filter', 'future');
        if( $filter != 'past' && $filter != 'future' ) $filter = 'future';
        $this->view->filter = $filter;

        // Create form
        $this->view->formFilter = $formFilter = new Event_Form_Filter_Browse();
        $defaultValues = $formFilter->getValues();

        if( !$viewer || !$viewer->getIdentity() ) {
            $formFilter->removeElement('view');
        }

        // Populate options
        if($formFilter->category_id){
            foreach( Engine_Api::_()->getDbtable('categories', 'event')->select()->order('title ASC')->query()->fetchAll() as $row ) {
                $formFilter->category_id->addMultiOption($row['category_id'], $row['title']);
            }
            if ($form->category_id && engine_count($formFilter->category_id->getMultiOptions()) <= 1) {
                $formFilter->removeElement('category_id');
            }
        }

        // Populate form data
        $formValues = array_merge($defaultValues, $this->_getAllParams());
        if( $formFilter->isValid($formValues) ) {
            $this->view->formValues = $values = $formFilter->getValues();
        } else {
            $formFilter->populate($defaultValues);
            $this->view->formValues = $values = array();
        }

        // Prepare data
        $this->view->formValues = $values = $formFilter->getValues();

        if( $viewer->getIdentity() && @$values['view'] == 1 ) {
            $values['users'] = array();
            foreach( $viewer->membership()->getMembersInfo(true) as $memberinfo ) {
                $values['users'][] = $memberinfo->user_id;
            }
        }

        $values['search'] = 1;

        if( $filter == "past" ) {
            $values['past'] = 1;
        } else {
            $values['future'] = 1;
        }

        // check to see if request is for specific user's listings
        if( ($user_id = $this->_getParam('user')) ) {
            $values['user_id'] = $user_id;
        }
        // Get paginator
        $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('event')
            ->getEventPaginator($values);
        $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('event_page', 12));
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        // Render
        $this->_helper->content->setEnabled();
    }

    public function manageAction()
    {
        // Create form
        if( !$this->_helper->requireAuth()->setAuthParams('event', null, 'edit')->isValid() ) return;

        // Get navigation
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('event_main');

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        $this->view->formFilter = $formFilter = new Event_Form_Filter_Manage();
        $defaultValues = $formFilter->getValues();

        // Populate form data
        if( $formFilter->isValid($this->_getAllParams()) ) {
            $this->view->formValues = $values = $formFilter->getValues();
        } else {
            $formFilter->populate($defaultValues);
            $this->view->formValues = $values = array();
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $table = Engine_Api::_()->getDbtable('events', 'event');
        $tableName = $table->info('name');

        // Only mine
        if( @$values['view'] == 2 ) {
          $select = $table->select()
                        ->where('user_id = ?', $viewer->getIdentity());
        }
        // All membership
        else {
          $membership = Engine_Api::_()->getDbtable('membership', 'event');
          $select = $membership->getMembershipsOfSelect($viewer);
          
          $select->where("
            CASE
              WHEN $tableName.user_id = {$viewer->getIdentity()} THEN true
              WHEN $tableName.user_id <> {$viewer->getIdentity()} AND approved = 1 THEN true
              ELSE false 
            END
          ");
        }

        if( !empty($values['search_text']) ) {
            $values['text'] = $values['search_text'];
        }
        if( !empty($values['text']) ) {
            $select->where("`{$tableName}`.title LIKE ?", $values['text'].'%');
        }
        $select->where('parent_type = ?', 'user');

        $select->order('starttime ASC');
        //$select->where("endtime > FROM_UNIXTIME(?)", time());

        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $this->view->text = $values['text'];
        $this->view->view = $values['view'];
        $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('event_page', 12));
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        // Check create
        $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('event', null, 'create');
    }

    public function createAction()
    {
        if( !$this->_helper->requireUser->isValid() ) return;
        if( !$this->_helper->requireAuth()->setAuthParams('event', null, 'create')->isValid() ) return;

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        $viewer = Engine_Api::_()->user()->getViewer();
        $parent_type = $this->_getParam('parent_type');
        $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));

        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
            $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
            if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'event') ) {
                return;
            }
        } else {
            $parent_type = 'user';
            $parent_id = $viewer->getIdentity();
        }

        // Create form
        $this->view->parent_type = $parent_type;
        $this->view->form = $form = new Event_Form_Create(array(
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

        // Populate with categories
        $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
        asort($categories, SORT_LOCALE_STRING);
        $categoryOptions = array('0' => '');
        foreach( $categories as $k => $v ) {
            $categoryOptions[$k] = $v;
        }
        if (sizeof($categoryOptions) <= 1) {
            $form->removeElement('category_id');
        } else {
            if($form->category_id)
                $form->category_id->setMultiOptions($categoryOptions);
        }


        // Not post/invalid
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        $itemFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('event', $this->view->viewer()->level_id, 'flood');
        if(!empty($itemFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("events",'event');
            $select = $tableFlood->select()->where("user_id = ?",$this->view->viewer()->getIdentity())->order("creation_date DESC");
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
//                 $form->addError($message);
//                 return;
            }
        }

        // Process
        $values = $form->getValues();
        if (isset($values['networks'])) {
            $network_privacy = 'network_'. implode(',network_', $values['networks']);
            $values['networks'] = implode(',', $values['networks']);
        }
        if( empty($values['auth_view']) ) {
            $values['auth_view'] = 'everyone';
        }

        if( empty($values['auth_comment']) ) {
            $values['auth_comment'] = 'everyone';
        }

        $values['user_id'] = $viewer->getIdentity();
        $values['parent_type'] = $parent_type;
        $values['parent_id'] =  $parent_id;
        $values['view_privacy'] =  $values['auth_view'];
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') && empty($values['host']) ) {
            $values['host'] = $group->getTitle();
        }

        // Convert times
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($viewer->timezone);
        $start = strtotime($values['starttime']);
        $end = strtotime($values['endtime']);

        // check dates
        if( $start > $end ) {
          $errors[] = array('errorMessage' => $this->view->translate('Start Date should be before End Date.'));
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
                
            //$form->starttime->setErrors(array('Start Date should be before End Date.'));
            //return;
        }

        date_default_timezone_set($oldTz);
        $values['starttime'] = date('Y-m-d H:i:s', $start);
        $values['endtime'] = date('Y-m-d H:i:s', $end);

        $db = Engine_Api::_()->getDbtable('events', 'event')->getAdapter();
        $db->beginTransaction();

        try
        {
            // Create event
            $table = Engine_Api::_()->getDbtable('events', 'event');
            $event = $table->createRow();
            
            //approve setting work
            $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('event', $viewer, 'approve');
            
            $event->setFromArray($values);
            $event->save();
            
            //Save editor images
            Engine_Api::_()->core()->saveTinyMceImages($values['description'], $event);
            
            //Location Work
            Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $event);

            // Add owner as member
            $event->membership()->addMember($viewer)
                ->setUserApproved($viewer)
                ->setResourceApproved($viewer);

            // Add owner rsvp
            $event->membership()
                ->getMemberInfo($viewer)
                ->setFromArray(array('rsvp' => 2))
                ->save();

            // Add photo
            if( !empty($values['photo']) ) {
                $event->setPhoto($form->photo);
            }

            // Set auth
            $auth = Engine_Api::_()->authorization()->context;

            if( $values['parent_type'] == 'group' ) {
                $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
            } else {
                $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);

            foreach( $roles as $i => $role ) {
                $auth->setAllowed($event, $role, 'view',    ($i <= $viewMax));
                $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'photo',   ($i <= $photoMax));
                // Create some auth stuff for all officers
                $auth->setAllowed($event, $role, 'topic_create', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'topic_edit', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'topic_delete', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'post_create', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'post_edit', ($i <= $commentMax));
                $auth->setAllowed($event, $role, 'post_delete', ($i <= $commentMax));
            }

            $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);

            // Add an entry for member_requested
            $auth->setAllowed($event, 'member_requested', 'view', 1);
            
            //if($values['approved'] == 1) {
              // Add action
              $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

              if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
                $action = $activityApi->addActivity($viewer, $group, 'group_event_create', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
              } else {
                $action = $activityApi->addActivity($viewer, $event, 'event_create', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
              }
              if( $action ) {
                $activityApi->attachActivity($action, $event);
              }
            //}
            
            //Start Send Approval Request to Admin
            Engine_Api::_()->core()->contentApprove($event, 'event');

            //Send to all group members
            if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
              $members = Engine_Api::_()->group()->groupMembers($group->getIdentity());
              foreach($members as $member) {
                Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($member, $viewer, $group, 'group_eventcreate');
              }
            }
            
            // Commit
            $db->commit();

            // Redirect
            echo json_encode(array('status' => true, 'redirectURL' => $this->view->url(array('id' => $event->getIdentity()), 'event_profile',true)));die;
            
            //return $this->_helper->redirector->gotoRoute(array('id' => $event->getIdentity()), 'event_profile', true);
        } catch( Exception $e ) {
            //return $this->exceptionWrapper($e, $form, $db);
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
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
            $album = $table->getSpecialAlbum($viewer, 'event');

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
    
    public function subcategoryAction() {

      $category_id = $this->_getParam('category_id', null);
      $CategoryType = $this->_getParam('type', null);
      $selected = $this->_getParam('selected', null);
      if ($category_id) {
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'event');
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
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["title"]) . '</option>';
            }
          } else {
            $data .= '<option value=""></option>';
            foreach ($subcategory as $category) {
              $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '" >' . Zend_Registry::get('Zend_Translate')->_($category["title"]) . '</option>';
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
        $categoryTable = Engine_Api::_()->getDbtable('categories', 'event');
        $category_select = $categoryTable->select()
          ->from($categoryTable->info('name'))
          ->where('subsubcat_id = ?', $category_id);
        $subcategory = $categoryTable->fetchAll($category_select);
        $count_subcat = engine_count($subcategory->toarray());

        $data = '';
        if ($subcategory && $count_subcat) {
          $data .= '<option value=""></option>';
          foreach ($subcategory as $category) {
            $data .= '<option ' . ($selected == $category['category_id'] ? 'selected = "selected"' : '') . ' value="' . $category["category_id"] . '">' . Zend_Registry::get('Zend_Translate')->_($category["title"]) . '</option>';
          }

        }
      } else
        $data = '';
      echo $data;
      die;
    }

}
