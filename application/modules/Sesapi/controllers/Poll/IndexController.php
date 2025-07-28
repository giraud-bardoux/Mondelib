<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Poll_IndexController extends Sesapi_Controller_Action_Standard {

  public function init() {
    // Get subject
    $poll = null;
    if (null !== ($pollIdentity = $this->_getParam('poll_id'))) {
      $poll = Engine_Api::_()->getItem('poll', $pollIdentity);
      if (null !== $poll) {
        Engine_Api::_()->core()->setSubject($poll);
      }
    }

    // Get viewer
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // only show polls if authorized
    $resource = ($poll ? $poll : 'poll');
    $viewer = ($viewer && $viewer->getIdentity() ? $viewer : null);
    if (!$this->_helper->requireAuth()->setAuthParams($resource, $viewer, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
  }
  
  
  public function rateAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $table = Engine_Api::_()->getDbTable('ratings', 'poll');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
    
			Engine_Api::_()->getDbTable('ratings', 'poll')->setRating($resource_id, $user_id, $rating);

			$poll = Engine_Api::_()->getItem('poll', $resource_id);
			$poll->rating = Engine_Api::_()->getDbTable('ratings', 'poll')->getRating($poll->getIdentity());
			$poll->save();
			
			$owner = Engine_Api::_()->getItem('user', $poll->user_id);
			if($owner->user_id != $user_id)
				Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $poll, 'poll_rating');
			
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("You have successfully rated poll.")));
  }
  
	public function menuAction() {
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('poll_main', array());
		$menu_counter = 0;
		foreach ($menus as $menu) {
			$class = end(explode(' ', $menu->class));
			$result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
			$result_menu[$menu_counter]['action'] = $class;
			$result_menu[$menu_counter]['isActive'] = $menu->active;
			$menu_counter++;
		}
		$result['menus'] = $result_menu;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
	}

  public function browseAction() {

    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('poll', null, 'create');

    // Get form
    $this->view->form = $form = new Poll_Form_Search();

    // Process form
    $values = array();
    if ($form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
    }

    if (empty($this->_getParam('user_id'))) 
      $values['browse'] = 1;

    $this->view->formValues = array_filter($values);

    if (@$values['show'] == 2 && $viewer->getIdentity()) {
      // Get an array of friend ids
      $values['users'] = $viewer->membership()->getMembershipsOfIds();
    }
    unset($values['show']);

    // Make paginator
    $currentPageNumber = $this->_getParam('page', 1);
    $itemCountPerPage = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.perpage', 10);

    // check to see if request is for specific user's listings
    if (($user_id = $this->_getParam('user_id'))) {
      $values['user_id'] = $user_id;
    }

    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('poll')->getPollsPaginator($values);
    $paginator->setItemCountPerPage($itemCountPerPage)->setCurrentPageNumber($currentPageNumber);

    $result = $this->pollsResult($paginator);
    foreach ($result['polls'] as $key => $value) {
      $user = Engine_Api::_()->getItem('user', $value['user_id']);
      if ($user) {
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($user, "", "");
        if ($ownerimage) {
          $result['polls'][$key]['owner_image'] = $ownerimage;
        } else {
          $userMainTempProfile = array(
            "main" => $value['owner_photo'],
            "icon" => $value['owner_photo'],
            "normal" => $value['owner_photo'],
            "profile" => $value['owner_photo'],
          );
          $result['polls'][$key]['owner_image'] = $userMainTempProfile;
        }
      }
      if (!empty($this->_getParam('user_id'))) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions = array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'edit');
        $counter = 0;
        if ($canEdit) {
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit Privacy");
          $counter++;
        }

        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'delete');
        if ($canDelete) {
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete Poll");
          $counter++;
        }

				$menuoptions[$counter]['name'] = "close";
        $menuoptions[$counter]['label'] = $this->view->translate("Open Poll");
        $menuoptions[$counter]['cl'] = $value['closed'];
        if ($value['closed'] == "0") {
          $menuoptions[$counter]['label'] = $this->view->translate("Close Poll");
        }
         if ($value['closed'] == "1") {
          $menuoptions[$counter]['label'] = $this->view->translate("Open Poll");
        }

        $result['polls'][$key]['menus'] = $menuoptions;
      }
    }

    $canCreate = false;
    if (!empty($this->_getParam('user_id'))) {
      $canCreate = Engine_Api::_()->authorization()->getPermission($viewer, 'poll', 'create');
    }
    $result['can_create'] =$canCreate;

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if ($result <= 0)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('Does not exist polls.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }

  function pollsResult($paginator)
  {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();

    foreach ($paginator as $item) {

      $resource = $item->toArray();
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();

      // Check content like or not and get like count
      if ($viewer->getIdentity() != 0) {
        $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
        $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
      }

      $resource['owner_title'] = $this->view->translate("Posted by ") . $item->getOwner()->getTitle();
      
      $resource['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'poll')->checkRated($item->getIdentity(), $viewer->getIdentity());
			$resource['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.rating', 1);
			$resource['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.ratingicon', 'fas fa-star');
      
      $result['polls'][$counterLoop] = $resource;
      
      $images = array();
      if (!empty($item->photo_id)) {
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      } else {
        $images['main'] = $this->getBaseUrl(true, "/application/modules/Poll/externals/images/nophoto_poll_thumb_main.png");
      }
      $result['polls'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }

  public function createAction()
  {
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams('poll', null, 'create')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    $this->view->options = array();
    $this->view->maxOptions = $max_options = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.maxoptions', 15);
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $parent_type = $this->_getParam('parent_type');
    $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
        if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'poll') ) {
            return;
        }
    } else {
        $parent_type = 'user';
        $parent_id = $viewer->getIdentity();
    }
    $this->view->parent_type = $parent_type;
    $this->view->form = $form = new Poll_Form_Create(array(
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

    // Check if post and populate
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $formFields[5]['name'] = "polloptions";
      $this->generateFormFields($formFields, array('resources_type' => 'poll', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription()), 'maxOptions' => $max_options));
    }

    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'post data error', 'result' => array()));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[6]['name'] = "file";
      
      if(is_countable($validateFields) && engine_count($validateFields))
      $this->validateFormFields($validateFields);
    }
    
    $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('poll', $this->view->viewer()->level_id, 'flood');
    if(!empty($itemFlood[0])){
      //get last activity
      $tableFlood = Engine_Api::_()->getDbTable("polls",'poll');
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
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
      }
    }

    // Check options
    $options = (array) $this->_getParam('optionsArray');
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, $max_options);
    $this->view->options = $options;
    if( empty($options) || !is_array($options) || engine_count($options) < 2 ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'You must provide at least two possible answers.', 'result' => array()));
    }
    foreach( $options as $index => $option ) {
        if( strlen($option) > 300 ) {
            $options[$index] = Engine_String::substr($option, 0, 300);
        }
    }

    // Process
    $pollTable = Engine_Api::_()->getItemTable('poll');
    $pollOptionsTable = Engine_Api::_()->getDbTable('options', 'poll');
    $db = $pollTable->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      $values['user_id'] = $viewer->getIdentity();

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

      $values['view_privacy'] = $values['auth_view'];

      $values['parent_type'] = $parent_type;
      $values['parent_id'] =  $parent_id;

      // Create poll
      $poll = $pollTable->createRow();
      
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
        
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
        
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('poll', $viewer, 'approve');
        
      $poll->setFromArray($values);
      $poll->save();

			if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $poll->setPhoto($form->photo);
      }
      
      // Create options
      $censor = new Engine_Filter_Censor();
      $html = new Engine_Filter_Html(array('AllowedTags'=> array('a')));
      foreach( $options as $option ) {
          $option = $censor->filter($html->filter($option));
          $pollOptionsTable->insert(array(
              'poll_id' => $poll->getIdentity(),
              'poll_option' => $option,
          ));
      }

      // Privacy
      $auth = Engine_Api::_()->authorization()->context;

      if( $values['parent_type'] == 'group' ) {
          $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $auth->setAllowed($poll, 'registered', 'vote', true);
      
      //Send to all group members
      if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $members = Engine_Api::_()->group()->groupMembers($group->getIdentity());
        foreach($members as $member) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($member, $viewer, $group, 'group_pollcreate');
        }
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    // Process activity
    $db = Engine_Api::_()->getDbTable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $group, 'group_poll_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
      } else {
        $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity(Engine_Api::_()->user()->getViewer(), $poll, 'poll_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
      }
      if( $action ) {
          Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $poll);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    
    //Start Send Approval Request to Admin
    Engine_Api::_()->core()->contentApprove($poll, 'poll');

    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('poll_id' => $poll->getIdentity(), 'message' => $this->view->translate('Poll created successfully.'))));
  }

  public function editAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
    else
      $poll = Engine_Api::_()->core()->getSubject();
      
    if (!$poll) {
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    if (!$this->_helper->requireSubject()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }

    // Setup
    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->core()->getSubject('poll');
    $parent_type = $poll->parent_type;
    $parent_id = $poll->parent_id;
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
        if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'poll') ) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
    } else {
        $parent_type = 'user';
        $parent_id = $viewer->getIdentity();
    }
    
    // Get form
    $this->view->form = $form = new Poll_Form_Edit(array(
        'parent_type' => $parent_type,
        'parent_id' => $parent_id
    ));
    $form->removeElement('title');
    $form->removeElement('description');
    $form->removeElement('options');
    // Populate form
    $form->populate($poll->toArray());
    
    // Prepare privacy
    $auth = Engine_Api::_()->authorization()->context;
    
    if($parent_type == 'user') {
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    } else if($parent_type = 'group') {
        if(engine_in_array($group->view_privacy, array('member', 'officer'))) {
          $roles = array('owner', 'member', 'parent_member');
        } else {
          $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
        }
    }
    
    // Populate form with current settings
    $form->search->setValue($poll->search);

    $form->populate(array(
        'networks' => explode(',', $poll->networks),
    ));
    if (Engine_Api::_()->authorization()->isAllowed('poll', Engine_Api::_()->user()->getViewer(), 'allow_network') && $poll->networks)
        $form->networks->setValue(explode(',', $poll->networks));

    foreach( $roles as $role ) {
        if( 1 === $auth->isAllowed($poll, $role, 'view') ) {
            $form->auth_view->setValue($role);
        }
        if( 1 === $auth->isAllowed($poll, $role, 'comment') ) {
            $form->auth_comment->setValue($role);
        }
    }
    
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields) &&  $poll->category_id){
        foreach($formFields as $fields){
          foreach($fields as $field){
            $subcat = array();
            if($fields['name'] == "subcat_id"){ 
              $subcat = Engine_Api::_()->getItemTable('poll_category')->getSubcategory(array('category_id'=>$poll->category_id,'column_name'=>'*'));
            }else if($fields['name'] == "subsubcat_id"){
              if($poll->subcat_id)
              $subcat = Engine_Api::_()->getItemTable('poll_category')->getSubSubcategory(array('category_id'=>$poll->subcat_id,'column_name'=>'*'));
            }
            if(is_countable($subcat) && engine_count($subcat)){
              $arrayCat = array();
              foreach($subcat as $cat){
                $arrayCat[$cat->getIdentity()] = $cat->getTitle(); 
              }
              $fields["multiOptions"] = $arrayCat;  
            }
          }
          $newFormFieldsArray[] = $fields;
        }
        if(!engine_count($newFormFieldsArray))
          $newFormFieldsArray = $formFields;
        //$formFields[4]['name'] = "file";
				$this->generateFormFields($newFormFieldsArray,array('resources_type'=>'poll', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
      }
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }

    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();

      if (isset($values['networks'])) {
          $network_privacy = 'network_'. implode(',network_', $values['networks']);
          $values['networks'] = implode(',', $values['networks']);
      }

      // CREATE AUTH STUFF HERE
      if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
          $values['auth_comment'] = 'everyone';
      }

      if( $parent_type == 'group' ) {
          $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
          $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($poll, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($poll, $role, 'comment', ($i <= $commentMax));
      }

      $poll->search = (bool) $values['search'];
      $poll->view_privacy = $values['auth_view'];
      $poll->networks = $values['networks'];
      $poll->setFromArray($values);
      $poll->save();
      
      // Add photo
			if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $poll->setPhoto($form->photo);
      }
      if( !empty($_FILES['file']['name']) &&  !empty($_FILES['file']['size']) ) {
        $poll->setPhoto($form->file);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($poll) as $action ) {
          $action->privacy = isset($values['networks'])? $network_privacy : null;
          $action->save();
          $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('poll_id' => $poll->getIdentity(), 'message' => $this->view->translate('Poll edited successfully.'))));
  }

  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));

    if (!$this->_helper->requireAuth()->setAuthParams($poll, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

    if (!$poll) {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $db = $poll->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $poll->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'databse_error', 'result' => array()));
    }

    $this->view->status = true;
    $message = Zend_Registry::get('Zend_Translate')->_('Your poll has been deleted.');
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
  }

  public function closeAction()
  {
    $data = array();
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->getRequest()->getParam('poll_id'));
    else
      $poll = Engine_Api::_()->core()->getSubject();
    if (!$poll) {
      $error = Zend_Registry::get('Zend_Translate')->_("Poll doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams($poll, $viewer, 'edit')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
      $data['status'] = false;
      $data['message'] = $this->view->translate('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $data['message'], 'result' => $data));
    }
    // @todo convert this to post only
    $table = $poll->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $poll->closed = $poll->closed == 1 ? 0 : 1;
      $poll->save();
      $db->commit();
      $data['status'] = true;
      $data['message'] = $poll->closed == 1 ? $this->view->translate('Successfully Closed') : $this->view->translate('Successfully Unclosed');
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
  }

  public function viewAction()
  {
    // Check auth
    if (!Engine_Api::_()->core()->hasSubject())
      $poll = Engine_Api::_()->getItem('poll', $this->_getParam('poll_id', null));
    else
      $poll = Engine_Api::_()->core()->getSubject('poll');
    if (!$poll)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This poll does not seem to exist anymore.'), 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }

    // Network check
		$networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($poll, 'user_id');
		if(empty($networkPrivacy))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
			
    $result = array();
    $owner = $poll->getOwner();
    $keyPoll = 'poll';
    $viewer = Engine_Api::_()->user()->getViewer();
    $pollOptions = $poll->getOptions();
    $data['owner_title'] = $owner->getTitle();
    if ($owner && $owner->photo_id) {
      $photo = $this->getBaseUrl(false, $owner->getPhotoUrl('thumb.profile'));
      $data['owner_photo']  = $photo;
    } else {
      $owner_photo = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_profile.png');
      // $userMainTempProfile = array(
      //   "main" => $owner_photo,
      //   "icon" => $owner_photo,
      //   "normal" => $owner_photo,
      //   "profile" => $owner_photo,
      // );
      $data['owner_photo'] = $owner_photo;
    }
    $data['has_voted'] = $poll->viewerVoted() ? 'true' : 'false';
    $data['can_vote'] = $poll->authorization()->isAllowed(null, 'vote') ? 'true' : 'false';
    $data['can_delete'] = Engine_Api::_()->authorization()->isAllowed(null, null, 'delete') ? 'true' : 'false';
    $data['can_edit'] = Engine_Api::_()->authorization()->isAllowed(null, null, 'edit') ? 'true' : 'false';
    $data['can_change_votes'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false) ? 'true' : 'false';
    
    $data['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'poll')->checkRated($poll->getIdentity(), $viewer->getIdentity());
    $data['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.rating', 1);
		$data['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.ratingicon', 'fas fa-star');
		
    // $data["vote_count"] = $this->view->translate(array('%s vote', '%s votes', $poll->vote_count), $this->view->locale()->toNumber($poll->vote_count));
    $data["vote_count"] = $poll->vote_count;
    // $data["view_count"]  =   $this->view->translate(array('%s view', '%s views', $poll->view_count), $this->view->locale()->toNumber($poll->view_count));
    $data["view_count"]  = $poll->view_count;
    
    if( !empty($poll->category_id) ) {
      $category = Engine_Api::_()->getItem('poll_category', $poll->category_id);
      $data['category_title'] = $category->category_name;
			if( !empty($poll->subcat_id) ) {
				$category = Engine_Api::_()->getItem('poll_category', $poll->subcat_id);
				$data['subcategory_title'] = $category->category_name;
			}
			if( !empty($poll->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('poll_category', $poll->subsubcat_id);
				$data['subsubcategory_title'] = $category->category_name;
			}
    }

    $data['options'] = $pollOptions->toArray();
    foreach ($pollOptions as $key => $option) {
      $pct = $poll->vote_count
        ? floor(100 * ($option['votes'] / $poll->vote_count))
        : 0;
      if (!$pct)
        $pct = 1;
      $data['options'][$key]['vote_percent'] = $this->view->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->view->locale()->toNumber($option->votes)) . '(' . $this->view->translate('%1$s%%', $this->view->locale()->toNumber($option->votes ? $pct : 0)) . ')';
      
      if( $poll->viewerVoted() == $option->poll_option_id ) {
				$data['options'][$key]['voted_user'] = true;
      } else {
				$data['options'][$key]['voted_user'] = false;
      }
    }
    $data["share"]["imageUrl"] = $data['owner_photo'];
    $data["share"]["url"] = $this->getBaseUrl(false, $poll->getHref());
    $data["share"]["title"] = $poll->title;
    $data["share"]["description"] = strip_tags($poll->getTitle());
    $data["share"]['urlParams'] = array(
      "type" => $poll->getType(),
      "id" => $poll->getIdentity()
    );
    $result[$keyPoll] = array_merge($poll->toArray(), $data);

    $counterOpt = 0;
    $optionData = array();
    if(!$poll->isOwner($viewer)){
      $optionData[$counterOpt]['name'] = 'report';
      $optionData[$counterOpt]['label'] = $this->view->translate('Report');
      $counterOpt++;
    }
    $optionData[$counterOpt]['name'] = 'share';
    $optionData[$counterOpt]['label'] = $this->view->translate('Share');
    if (filter_var($data['can_edit'], FILTER_VALIDATE_BOOLEAN)) {
      $counterOpt++;
      $optionData[$counterOpt]['name'] = 'edit_privacy';
      $optionData[$counterOpt]['label'] = $this->view->translate('Edit Privacy');
    }
    if (filter_var($data['can_delete'], FILTER_VALIDATE_BOOLEAN)) {
      $counterOpt++;
      $optionData[$counterOpt]['name'] = 'delete';
      $optionData[$counterOpt]['label'] = $this->view->translate('Delete');
    }

    $result['options'] = $optionData;
    if (!$owner->isSelf($viewer)) {
      $poll->view_count++;
      $poll->save();
    }
    
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($poll,'',"");
    if(!engine_count($images))
      $images['main'] = $this->getBaseUrl(true, $poll->getPhotoUrl());

    $result['poll_images'] = $images;

    $result['user_images'] = $this->userImage($poll->user_id,"thumb.profile");
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result)));
  }

  public function searchAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    // Get form
    $form = new Poll_Form_Search();
    if (!$viewer->getIdentity()) {
      $form->removeElement('show');
    }
    // Process form
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields,array('resources_type'=>'poll'));
    } else {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
  }

  public function voteAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'You do not have permission to view this private page.', 'result' => array()));
    }
    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'vote')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'You do not have permission to vote on poll.', 'result' => array()));
    }

    // Check method
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'post data error', 'result' => array()));
    }

    $option_id = $this->_getParam('option_id');
    $canChangeVote = Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.canchangevote', false);

    $poll = Engine_Api::_()->core()->getSubject('poll');
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$poll) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_("This poll does not seem to exist anymore.");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if ($poll->closed) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_('This poll is closed.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    if ($poll->hasVoted($viewer) && !$canChangeVote) {
      $this->view->success = false;
      $error = Zend_Registry::get('Zend_Translate')->_('You have already voted on this poll, and are not permitted to change your vote.');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $data = array();
    $db = Engine_Api::_()->getDbTable('polls', 'poll')->getAdapter();
    $db->beginTransaction();
    try {
      $poll->vote($viewer, $option_id);

      $db->commit();
    } catch (Exception $error) {
      $db->rollback();
      $this->view->success = false;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    $data['success'] = true;
    $pollOptions = array();
    foreach ($poll->getOptions()->toArray() as $option) {
      $option['votesTranslated'] = $this->view->translate(array('%s vote', '%s votes', $option['votes']), $this->view->locale()->toNumber($option['votes']));
      $pollOptions[] = $option;
    }
    $data['options'] = $pollOptions;
    $data['votes_total'] = $poll->vote_count;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
  }
}
