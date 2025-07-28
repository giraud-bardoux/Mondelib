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
class Group_IndexController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'view')->isValid() )
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $id = $this->_getParam('group_id', $this->_getParam('id', null));
    if( $id ) {
      $group = Engine_Api::_()->getItem('group', $id);
      if( $group ) {
        Engine_Api::_()->core()->setSubject($group);
      }
    }
  }
  
	public function menuAction() {
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('group_main', array());
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
	
  public function rateAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $table = Engine_Api::_()->getDbTable('ratings', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
    
			Engine_Api::_()->getDbTable('ratings', 'core')->setRating(array('resource_id' => $resource_id, 'resource_type' => 'group', 'rating' => $rating));

			$group = Engine_Api::_()->getItem('group', $resource_id);
			$group->rating = Engine_Api::_()->getDbTable('ratings', 'core')->getRating(array('resource_id' => $group->getIdentity(), 'resource_type' => 'group'));
			$group->save();
			
			$owner = Engine_Api::_()->getItem('user', $group->user_id);
			if($owner->user_id != $user_id)
				Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $group, 'group_rating');
			
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("You have successfully rated group.")));
  }
  
  public function createalbumAction(){

      $group_id = $this->_getParam('group_id', false);
      $group = Engine_Api::_()->getItem('group', $group_id);
      $album = $group->getSingletonAlbum();
      $album_id = $album->getIdentity();
      
      if(!$group_id)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
      // set up data needed to check quota
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['user_id'] = $viewer->getIdentity();

      $quota = $quota = 0;
      // Get form
      $form = new Group_Form_Photo_Upload();
      $form->file->setAttrib('data', array('group_id' => $group->getIdentity()));

      // Render
      //$form->populate(array('album' => $album_id));
      if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'group', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
      }
      if (!$form->isValid($this->getRequest()->getPost())){
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (is_countable($validateFields) && engine_count($validateFields))
              $this->validateFormFields($validateFields);
      }
      
      $params = array(
        'user_id' => $viewer->getIdentity(),
      );

      
      // Process
      $photoTable = Engine_Api::_()->getDbTable('photos', 'group');
      $db = $photoTable->getAdapter();
      $db->beginTransaction();
      try {
          // Add action and attachments
          $api = Engine_Api::_()->getDbTable('actions', 'activity');
          $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $group, 'group_photo_upload', null, array(
            'count' => engine_count($_FILES['attachmentImage']['name'])
          ));          
          $count = 0;
          foreach($_FILES['attachmentImage']['name'] as $key => $files) {
            if(!empty($_FILES['attachmentImage']['name'][$key])) {
              $image = array('name' => $_FILES['attachmentImage']['name'][$key], 'type' => $_FILES['attachmentImage']['type'][$key], 'tmp_name' => $_FILES['attachmentImage']['tmp_name'][$key],'error' => $_FILES['attachmentImage']['error'][$key],'size' => $_FILES['attachmentImage']['size'][$key]);
              
              $photo = $photoTable->createRow();
              $photo->setFromArray($params);
              $photo->collection_id = $album->album_id;
              $photo->album_id = $album->album_id;
              $photo->group_id = $group->group_id;
              $photo->setPhoto($image);
              $photo->save();
              
              if( $action instanceof Activity_Model_Action && $count < 100 ) {
                $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
              }
              $count++;
            }
          }

          $db->commit();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), 'album_id' => $album->getIdentity()))));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
  }
  
  public function searchFormAction() {
    $searchaction = $this->_getParam('searchaction', 'browse');
    if($searchaction == 'browse') {
      $form = new Group_Form_Filter_Browse();
    } else {
      $form = new Group_Form_Filter_Manage();
    }
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields,array('resources_type'=>'group'));
  }

  public function browseAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Check create
    $canCreate = Engine_Api::_()->authorization()->isAllowed('group', null, 'create');
    
    // Form
    $formFilter = $formFilter = new Group_Form_Filter_Browse();
    $defaultValues = $formFilter->getValues();

    if( !$viewer || !$viewer->getIdentity() ) {
      $formFilter->removeElement('view');
    }

    // Populate options
    $categories = Engine_Api::_()->getDbTable('categories', 'group')->getCategoriesAssoc();
    $formFilter->category_id->addMultiOptions($categories);

    // Populate form data
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $formValues = $values = $formFilter->getValues();
    } else {
      $formFilter->populate($defaultValues);
      $formValues = $values = array();
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

    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user_id');
    if( $user_id ) {
      $values['user_id'] = $user_id;
    }

    if(!empty($_POST['user_id']))
      $values["user_id"] = $_POST['user_id'];
    if(!empty($_POST['category_id']))
      $values['category'] = $_POST['category_id'];

    // Make paginator
    if ($user_id) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $membership = Engine_Api::_()->getDbTable('membership', 'group');
      $select = $membership->getMembershipsOfSelect($viewer);
      $select->where('group_id IS NOT NULL');
      $table = Engine_Api::_()->getItemTable('group');
      $tName = $table->info('name');
      if ($values['view'] == 2) {
        $select->where("`{$tName}`.`user_id` = ?", $viewer->getIdentity());
      }
      if (!empty($values['text'])) {
        $select->where(
          $table->getAdapter()->quoteInto("`{$tName}`.`title` LIKE ?", '%' . $values['text'] . '%') . ' OR ' .
            $table->getAdapter()->quoteInto("`{$tName}`.`description` LIKE ?", '%' . $values['text'] . '%')
        );
      }
      $paginator = Zend_Paginator::factory($select);
    } else {
      $paginator = Engine_Api::_()->getItemTable('group')->getGroupPaginator($values);
    }
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result = $this->groupResult($paginator, $user_id);

    // Group Manage Page Work based on user_id
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist groups.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  
  function groupResult($paginator, $user_id) {
  
    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    
    foreach($paginator as $item) {
    
      $resource = $item->toArray();
      $description = strip_tags($item['description']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($resource['description']);
      //$resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['user_id'])->getTitle();
      $resource['description'] = $description;   
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();
      
      //Category name
      if(!empty($resource['category_id'])) {
        $category = Engine_Api::_()->getItem('group_category', $resource['category_id']);
        $resource['category_name'] = $category->title;
      }
      
      $resource['member_count'] = $this->view->translate(array('%s member', '%s members', $item->member_count), $this->view->locale()->toNumber($item->member_count));
    
      if($resource['user_id']) {
        $user = Engine_Api::_()->getItem('user', $resource['user_id']);
        $resource['created_by'] = $this->view->translate('led by ') . $user->getTitle();
      }

      if ($user_id)
        if ($item->user_id != $user_id) {
          $resource['leave'] = true;
        }
      $menuoptions= array();
      if(!empty($user_id) && $item->isOwner($viewer)) {
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'group', 'edit');
        $counter = 0;
        if($canEdit) {
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'group', 'delete');
        if($canDelete) {
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete");
          $counter++;
        }
      } elseif(!$item->membership()->isMember($viewer, null)){
        $menuoptions[0]['name'] = "join";
        $menuoptions[0]['label'] = $this->view->translate("Join Group");
      } elseif($item->membership()->isMember($viewer, true) && !$item->isOwner($viewer) ) {
        $menuoptions[0]['name'] = "leave";
        $menuoptions[0]['label'] = $this->view->translate("Leave Group");
      }
      $resource['menus'] = $menuoptions; 
      
      $result['groups'][$counterLoop] = $resource;
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', '');
      if(!engine_count($images))
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';
      $result['groups'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }
  
  public function getCategoryName($params = array()) {
    
    $categoryTable = Engine_Api::_()->getDbTable('categories', 'group');
    $categoryTableName = $categoryTable->info('name');
    
    $select = $categoryTable->select()
            ->from($categoryTableName, $params['column_name']);

    if (isset($params['category_id']))
      $select = $select->where('category_id = ?', $params['category_id']);

    return $select = $select->query()->fetchColumn();
  }
  
  public function categoryAction() {
 
    $params['countClassifieds'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'group')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {

      if($key == '') continue;
      $catgeoryArray["category"][$counter]["category_id"] = $key;
      $catgeoryArray["category"][$counter]["label"] = $this->getCategoryName(array('column_name' => 'title', 'category_id' => $key));

      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');
      
      //Classifieds Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $key, 'table_name' => 'groups', 'module_name' => 'group'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s group', '%s groups', $Itemcount), $this->view->locale()->toNumber($Itemcount));
      
      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }

  public function createAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if( !$this->_helper->requireUser->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        
    if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'create')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Create form
    $form = $form = new Group_Form_Create();
    $form->approval->setMultiOptions(array(
      1 => 'New members must be approved.',
      0 => 'New members can join immediately.',
    ));
    
    // Populate with categories
    $categories = Engine_Api::_()->getDbTable('categories', 'group')->getCategoriesAssoc();
    asort($categories, SORT_LOCALE_STRING);
    $categoryOptions = array('0' => '');
    foreach( $categories as $k => $v ) {
        $categoryOptions[$k] = $v;
    }
    $form->category_id->setMultiOptions($categoryOptions);

    if( engine_count($form->category_id->getMultiOptions()) <= 1 ) {
        $form->removeElement('category_id');
    }
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'group', 'formTitle' => $form->getTitle() ? $this->view->translate($form->getTitle()) : "", 'formDescription' => $form->getDescription() ? $this->view->translate($form->getDescription()) : "" ));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[4]['name'] = "file";
      if(is_countable($validateFields) && engine_count($validateFields))
      $this->validateFormFields($validateFields);
    }
    
    $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('group', $this->view->viewer()->level_id, 'flood');
    if(!empty($itemFlood[0])){
      //get last activity
      $tableFlood = Engine_Api::_()->getDbTable("groups",'group');
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
    
    // Process
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

    $values['view_privacy'] =  $values['auth_view'];

    $db = Engine_Api::_()->getDbTable('groups', 'group')->getAdapter();
    $db->beginTransaction();
    try {
      // Create group
      $table = Engine_Api::_()->getDbTable('groups', 'group');
      $group = $table->createRow();
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
        
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $viewer, 'approve');
      
      $group->setFromArray($values);
      $group->save();

      // Add owner as member
      $group->membership()->addMember($viewer)
          ->setUserApproved($viewer)
          ->setResourceApproved($viewer);

      // Set photo
      if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $group->setPhoto($form->photo);
      }

      // Process privacy
      $auth = Engine_Api::_()->authorization()->context;

      $roles = array('officer', 'member', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);
      $eventMax = array_search(@$values['auth_event'], $roles);
      $groupMax = array_search(@$values['auth_group'], $roles);
      $pollMax = array_search(@$values['auth_poll'], $roles);
      $videoMax = array_search(@$values['auth_video'], $roles);
      $inviteMax = array_search($values['auth_invite'], $roles);

      $officerList = $group->getOfficerList();

      foreach( $roles as $i => $role ) {
        if( $role === 'officer' ) {
            $role = $officerList;
        }
        $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'photo', ($i <= $photoMax));
        $auth->setAllowed($group, $role, 'event', ($i <= $eventMax));
        $auth->setAllowed($group, $role, 'group', ($i <= $groupMax));
        $auth->setAllowed($group, $role, 'poll', ($i <= $pollMax));
        $auth->setAllowed($group, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($group, $role, 'invite', ($i <= $inviteMax));
        // Create some auth stuff for all officers
        $auth->setAllowed($group, $role, 'topic_create', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'topic_edit', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'topic_delete', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_create', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_edit', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_delete', ($i <= $commentMax));
      }

      // Add auth for invited users
      $auth->setAllowed($group, 'member_requested', 'view', 1);

      // Add action
      $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $group, 'group_create', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
      if( $action ) {
          $activityApi->attachActivity($action, $group);
      }
      
      //Start Send Approval Request to Admin
      Engine_Api::_()->core()->contentApprove($group, 'group');

      // Commit
      $db->commit();

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('group_id' => $group->getIdentity(),'message' => $this->view->translate('Group created successfully.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  
  public function editAction() {
  
    if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( !$this->_helper->requireSubject()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $group = Engine_Api::_()->core()->getSubject();
    $officerList = $group->getOfficerList();
    
    $form = new Group_Form_Edit();
    $form->approval->setMultiOptions(array(
      1 => 'New members must be approved.',
      0 => 'New members can join immediately.',
    ));
    
    // Populate with categories
    $categories = Engine_Api::_()->getDbTable('categories', 'group')->getCategoriesAssoc();
    asort($categories, SORT_LOCALE_STRING);
    $categoryOptions = array('0' => '');
    foreach( $categories as $k => $v ) {
        $categoryOptions[$k] = $v;
    }
    $form->category_id->setMultiOptions($categoryOptions);

    if( engine_count($form->category_id->getMultiOptions()) <= 1 ) {
        $form->removeElement('category_id');
    }

    //if( !$this->getRequest()->isPost() ) {
      // Populate auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('officer', 'member', 'registered', 'everyone');
      $actions = array('event', 'view', 'comment', 'invite', 'photo', 'blog', 'video', 'poll');
      $perms = array();
      foreach( $roles as $roleString ) {
          $role = $roleString;
          if( $role === 'officer' ) {
              $role = $officerList;
          }

          foreach( $actions as $action ) {
              if( $auth->isAllowed($group, $role, $action) ) {
                  $perms['auth_' . $action] = $roleString;
              }
          }
      }

      $form->populate($group->toArray());
      $form->populate($perms);
      if (Engine_Api::_()->authorization()->isAllowed('group', Engine_Api::_()->user()->getViewer(), 'allow_network') && $group->networks)
        $form->networks->setValue(explode(',', $group->networks));
      //return;
    //}
    
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields) &&  $group->category_id){
        foreach($formFields as $fields){
          foreach($fields as $field){
            $subcat = array();
            if($fields['name'] == "subcat_id"){ 
              $subcat = Engine_Api::_()->getItemTable('group_category')->getSubcategory(array('category_id'=>$group->category_id,'column_name'=>'*'));
            }else if($fields['name'] == "subsubcat_id"){
              if($group->subcat_id)
              $subcat = Engine_Api::_()->getItemTable('group_category')->getSubSubcategory(array('category_id'=>$group->subcat_id,'column_name'=>'*'));
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
        $formFields[2]['name'] = "file";
				$this->generateFormFields($newFormFieldsArray,array('resources_type'=>'group', 'formTitle' => $form->getTitle() ? $this->view->translate($form->getTitle()) : "", 'formDescription' => $form->getDescription() ? $this->view->translate($form->getDescription()) : "" ));
      }
      
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }

    // Process
    $db = Engine_Api::_()->getItemTable('group')->getAdapter();
    $db->beginTransaction();

    try {
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

      $values['view_privacy'] =  $values['auth_view'];

      // Set group info
      $group->setFromArray($values);
      $group->save();

      if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $group->setPhoto($form->photo);
      }

      // Process privacy
      $auth = Engine_Api::_()->authorization()->context;

      $roles = array('officer', 'member', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);
      $eventMax = array_search(@$values['auth_event'], $roles);
      $blogMax = array_search(@$values['auth_blog'], $roles);
      $pollMax = array_search(@$values['auth_poll'], $roles);
      $videoMax = array_search(@$values['auth_video'], $roles);
      $inviteMax = array_search($values['auth_invite'], $roles);

      foreach( $roles as $i => $role ) {
        if( $role === 'officer' ) {
            $role = $officerList;
        }
        $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'photo', ($i <= $photoMax));
        $auth->setAllowed($group, $role, 'event', ($i <= $eventMax));
        $auth->setAllowed($group, $role, 'blog', ($i <= $blogMax));
        $auth->setAllowed($group, $role, 'poll', ($i <= $pollMax));
        $auth->setAllowed($group, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($group, $role, 'invite', ($i <= $inviteMax));
        // Create some auth stuff for all officers
        $auth->setAllowed($group, $role, 'topic_create', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'topic_edit', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'topic_delete', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_create', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_edit', ($i <= $commentMax));
        $auth->setAllowed($group, $role, 'post_delete', ($i <= $commentMax));
      }

      // Add auth for invited users
      $auth->setAllowed($group, 'member_requested', 'view', 1);

      // Commit
      $db->commit();
    } catch( Engine_Image_Exception $e ) {
      $db->rollBack();
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($group) as $action ) {
          $action->privacy = isset($values['networks'])? $network_privacy : null;
          $action->save();
          $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('group_id' => $group->getIdentity(),'message' => $this->view->translate('Group edited successfully.'))));
  }
  
  public function deleteAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $group = Engine_Api::_()->getItem('group', $this->getRequest()->getParam('group_id'));
    if( !$this->_helper->requireAuth()->setAuthParams($group, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    
    // Make form
    $form = new Group_Form_Delete();
    
//     if( !$group )
//     {
//       $this->view->status = false;
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_("Group doesn't exists or not authorized to delete");
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
//     }

//     if( !$this->getRequest()->isPost() )
//     {
//       $this->view->status = false;
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
//     }

    $db = $group->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      $group->delete();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('The selected group has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$message,'success_message'=>$message)));
  }

  public function listAction()
  {
    
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

    $db = Engine_Api::_()->getDbTable('photos', 'album')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();

      $photoTable = Engine_Api::_()->getDbTable('photos', 'album');
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

      $table = Engine_Api::_()->getDbTable('albums', 'album');
      $album = $table->getSpecialAlbum($viewer, 'group');

      $photo->album_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id )
      {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      $auth = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($photo, 'everyone', 'view',    true);
      $auth->setAllowed($photo, 'everyone', 'comment', true);
      $auth->setAllowed($album, 'everyone', 'view',    true);
      $auth->setAllowed($album, 'everyone', 'comment', true);


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
