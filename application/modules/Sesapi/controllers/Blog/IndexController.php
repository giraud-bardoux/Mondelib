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
class Blog_IndexController extends Sesapi_Controller_Action_Standard {

  protected $_blogEnabled;

  public function init() {

    //Only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $this->isBlogEnable();
  }

  protected function isBlogEnable() {
    $this->_blogEnabled = true;
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
      $blog = Engine_Api::_()->getItem('blog', $resource_id);

			Engine_Api::_()->getDbTable('ratings', 'core')->setRating(array('resource_id' => $resource_id, 'resource_type' => $blog->getType(), 'rating' => $rating));
			
			$blog->rating = Engine_Api::_()->getDbTable('ratings', 'core')->getRating(array('resource_id' => $blog->getIdentity(), 'resource_type' => $blog->getType()));
			$blog->save();
			
			$owner = Engine_Api::_()->getItem('user', $blog->owner_id);
			if($owner->user_id != $user_id)
				Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $blog, 'blog_rating');
			
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("You have successfully rated blog.")));
  }

  public function searchFormAction() {
    $form = new Blog_Form_Search();
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields,array('resources_type'=>'blog'));
  }


  public function browseAction() {
 
    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Permissions
    $canCreate = $this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->checkRequire();

    // Make form
    // Note: this code is duplicated in the blog.browse-search widget
    $form = new Blog_Form_Search();

    $form->removeElement('draft');
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }
    
    //In case of My Blog Entry
    $user_id = $this->_getParam('user_id', null);

    // Process form
    $defaultValues = $form->getValues();
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = $defaultValues;
    }
    //$this->view->formValues = array_filter($values);
    
    if(empty($user_id))
			$values['draft'] = "0";
    $values['visible'] = "1";

    // Do the show thingy
    if( @$values['show'] == 2 ) {
      // Get an array of friend ids
      $table = Engine_Api::_()->getItemTable('user_id');
      $select = $viewer->membership()->getMembersSelect('user_id');
      $friends = $table->fetchAll($select);
      // Get stuff
      $ids = array();
      foreach( $friends as $friend )
      {
        $ids[] = $friend->user_id;
      }
      //unset($values['show']);
      $values['users'] = $ids;
    }

    //$this->view->assign($values);

    if(!empty($_POST['user_id']))
      $values["user_id"] = $_POST['user_id'];
    if(!empty($_POST['category_id']))
      $values['category'] = $_POST['category_id'];
    if(!empty($_POST['search']))
         $values['text'] = $_POST['search'];
    // Get blogs
    $paginator = Engine_Api::_()->getItemTable('blog')->getblogsPaginator($values);
    $items_per_page = Engine_Api::_()->getApi('settings', 'core')->blog_page;
    $paginator->setItemCountPerPage($items_per_page);
    $paginator->setCurrentPageNumber( $values['page'] );
    $result = $this->blogResult($paginator);

    if(!empty($_POST['user_id'])) {

      $viewer = Engine_Api::_()->user()->getViewer();
      $menuoptions = array();
      $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'blog', 'edit');
      $counter = 0;
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Entry");
        $counter++;
      }

      $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'blog', 'delete');
      if($canDelete) {
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Entry");
      }
      $result['menus'] = $menuoptions;
    }
    
    if(!empty($viewer->getIdentity())) {
			$result['canCreate'] = $canCreate;
    }

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist blogs.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function categoryAction() {

    $params['countBlogs'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'blog')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {

      if($key == '') continue;

      $category = Engine_Api::_()->getItem('blog_category', $key);

      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;

      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');

      //Blogs Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'blogs', 'module_name' => 'blog'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s blog', '%s blogs', $Itemcount), $this->view->locale()->toNumber($Itemcount));

      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array()));
  }

  function blogResult($paginator) {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();

    foreach($paginator as $item) {

      $resource = $item->toArray();
      $description = strip_tags($item['body']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($resource['body']);
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['body'] = $description;
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();

      //Category name
      if(!empty($resource['category_id'])) {
        $category = Engine_Api::_()->getItem('blog_category', $resource['category_id']);
        $resource['category_name'] = $category->category_name;
      }

      // Check content like or not and get like count
      if($this->_blogEnabled) {
        if($viewer->getIdentity() != 0) {
          $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
          $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
        }
      }
      
			$result['blogs'][$counterLoop] = $resource;
			
      $images = array();
			$images = Engine_Api::_()->sesapi()->getPhotoUrls($item,'',"");
			if(!engine_count($images))
				$images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl());

			$images['blog_images'] = $images;
			$images['user_images'] = $this->userImage($item->owner_id,"thumb.profile");
      $result['blogs'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }

  public function viewAction() {

    // Check permission
    $viewer = Engine_Api::_()->user()->getViewer();

    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( $blog ) {
      Engine_Api::_()->core()->setSubject($blog);
    }

    if( !$this->_helper->requireSubject()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    
    if( !$blog || !$blog->getIdentity() || (($blog->draft || !$blog->approved) && !$blog->isOwner($viewer)) ) {
      if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
      } else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if($blog->parent_type == 'group' && $blog->parent_id) {
      $group = Engine_Api::_()->getItem($blog->parent_type, $blog->parent_id);
      
      if( !$group || !$group->getIdentity() || ((!$group->approved) && !$group->isOwner($viewer)) ) {
        if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
        } else
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      }
      
      $viewPermission = $group->authorization()->isAllowed($viewer, 'view');
      if(empty($viewPermission)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      }
    }
    
    // Network check
    $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($blog);
    if(empty($networkPrivacy))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    
    // Prepare data
    $blogTable = Engine_Api::_()->getDbTable('blogs', 'blog');
    
    $blog_content = $blog->toArray();

		$body = $this->replaceSrc($blog_content['body']);
    $blog_content['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $blog_content['owner_title'] = Engine_Api::_()->getItem('user', $blog_content['owner_id'])->getTitle();
    $blog_content['resource_type'] = $blog->getType();
    $blog_content['resource_id'] = $blog->getType();
    $blog_content['category_id'] = $blog->category_id;

    if( !$blog->isOwner($viewer) ) {
      $blogTable->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'blog_id = ?' => $blog->getIdentity(),
      ));
    }

    // Get tags
    $blogTags = $blog->tags()->getTagMaps();
    if (!empty($blogTags)) {
      foreach ($blogTags as $tag) {
        $blog_content['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
      }
    }

    // Get category
    if( !empty($blog->category_id) ) {
      $category = Engine_Api::_()->getItem('blog_category', $blog->category_id);
      $blog_content['category_title'] = $category->category_name;
			if( !empty($blog->subcat_id) ) {
				$category = Engine_Api::_()->getItem('blog_category', $blog->subcat_id);
				$blog_content['subcategory_title'] = $category->category_name;
			}
			if( !empty($blog->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('blog_category', $blog->subsubcat_id);
				$blog_content['subsubcategory_title'] = $category->category_name;
			}
    }

    if($this->_blogEnabled) {
      if($viewer->getIdentity() != 0) {
        $blog_content['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($blog);
        $blog_content['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($blog);
      }
    }

    $blog_content['content_url'] = $this->getBaseUrl(false,$blog->getHref());
    $blog_content['can_favorite'] = false;
    $blog_content['can_share'] = false;
		$blog_content['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $blog->getIdentity(), 'resource_type' => $blog->getType()));
		
		$blog_content['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.rating', 1);
		$blog_content['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.ratingicon', 'fas fa-star');
		
    $result['blog'] = $blog_content;

    if($viewer->getIdentity() > 0) {
      $result['blog']['permission']['canEdit'] = $canEdit = $viewPermission = $blog->authorization()->isAllowed($viewer, 'edit') ? true : false;
      $result['blog']['permission']['canComment'] =  $blog->authorization()->isAllowed($viewer, 'comment') ? true : false;
      $result['blog']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesblog_blog', 'create') ? true : false;
      $result['blog']['permission']['can_delete'] = $canDelete  = $blog->authorization()->isAllowed($viewer,'delete') ? true : false;

      $menuoptions= array();
      $counter = 0;
    
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit This Entry");
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete This Entry");
        $counter++;
      }
      if (!$blog->isOwner($viewer)) {
        $menuoptions[$counter]['name'] = "report";
        $menuoptions[$counter]['label'] = $this->view->translate("Report");
      }
      $result['menus'] = $menuoptions;
    }
    
    $result['blog']["share"]["name"] = "share";
    $result['blog']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$blog->getPhotoUrl());
    if($photo)
      $result['blog']["share"]["imageUrl"] = $photo;
    $url = "blogs/" . $blog->getOwner()->getIdentity() . "/" . $blog->getIdentity() . "/" . strtolower(str_replace(" ", "-", $blog->getTitle()));
    $result['blog']["share"]["url"] = $this->getBaseUrl(false,$url);
    $result['blog']["share"]["title"] = $blog->getTitle();
    $result['blog']["share"]["description"] = strip_tags($blog->getDescription());
    $result['blog']["share"]['urlParams'] = array(
        "type" => $blog->getType(),
        "id" => $blog->getIdentity()
    );

    if(is_null($result['blog']["share"]["title"]))
      unset($result['blog']["share"]["title"]);
      
		$owner = $blog->getOwner();
		if( $owner->getIdentity() == $viewer->getIdentity()){ } else {
			$subscriptionTable = Engine_Api::_()->getDbTable('subscriptions', 'blog');
			if( !$subscriptionTable->checkSubscription($owner, $viewer) ) {
				$result['blog']['subscribe']['label'] = $this->view->translate('Subscribe');
				$result['blog']['subscribe']['user_id'] = $owner->getIdentity();
				$result['blog']['subscribe']['action'] = 'add';
			} else {
				$result['blog']['subscribe']['label'] = $this->view->translate('Unsubscribe');
				$result['blog']['subscribe']['user_id'] = $owner->getIdentity();
				$result['blog']['subscribe']['action'] = 'remove';
			}
		}

    $images = Engine_Api::_()->sesapi()->getPhotoUrls($blog,'',"");
    if(!engine_count($images))
      $images['main'] = $this->getBaseUrl(true, $blog->getPhotoUrl());

    $result['blog']['blog_images'] = $images;

    $result['blog']['user_images'] = $this->userImage($blog->owner_id,"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
  }
  
  function replaceSrc($html = ""){
      
    preg_match_all( '@src="([^"]+)"@' , $html, $match );

    foreach(array_pop($match) as $src){
			//$url = explode("data:image",$src);
			
			if(strpos($src,'data:') !== false){
				
			}else if(strpos($src,'http://') === false && strpos($src,'https://') === false && strpos($src,'//') === false){
				if(Zend_Registry::get('StaticBaseUrl') != "/")
				$baseUrl = str_replace(Zend_Registry::get('StaticBaseUrl'),'',$this->getBaseUrl());
				else
				$baseUrl = $this->getBaseUrl();
				if(end(explode("",$baseUrl)) != '/')
					$baseUrl .= '/';
				$html = str_replace($src,$baseUrl.$src,$html);
			}else if(strpos($src,'http://') === false && strpos($src,'https://') === false){
					$html = str_replace($src,'https://'.$src,$html);
			}
    }
    return $html;
	}

  public function createAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams('blog', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getItemTable('blog')->getBlogsPaginator($values);

    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'blog', 'max');
    $current_count = $paginator->getTotalItemCount();
    if (($current_count >= $quota) && !empty($quota)) {
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of entries allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => $message, 'result' => array()));
    }

    $parent_type = $this->_getParam('parent_type');
    $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));

    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
      $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
      if( !Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'blog') ) {
          return;
      }
    } else {
      $parent_type = 'user';
      $parent_id = $viewer->getIdentity();
    }
    
    $category_id = (isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0;
    $subcat_id = (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0;
    $subsubcat_id = (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0;

    // Prepare form
    $this->view->form = $form = new Blog_Form_Create(array(
        'parent_type' => $parent_type,
        'parent_id' => $parent_id
    ));
    $form->removeElement('token');
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
      if($group->view_privacy == 'member')
        $view_privacy = 'parent_member';
      else 
        $view_privacy = $group->view_privacy;
      $form->getElement('auth_view')->setValue($view_privacy);
    }

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'blog', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }

    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[4]['name'] = "file";
      if(is_countable($validateFields) && engine_count($validateFields))
      $this->validateFormFields($validateFields);
    }
    
    $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('blog', $this->view->viewer()->level_id, 'flood');
    if(!empty($itemFlood[0])){
      //get last activity
      $tableFlood = Engine_Api::_()->getDbTable("blogs",'blog');
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
          $form->addError($message);
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $message));
      }
    }
    
    // Process
    $table = Engine_Api::_()->getItemTable('blog');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
    
      // Create blog
      $viewer = Engine_Api::_()->user()->getViewer();
      $formValues = $form->getValues();

      if (isset($formValues['networks'])) {
          $network_privacy = 'network_'. implode(',network_', $formValues['networks']);
          $formValues['networks'] = implode(',', $formValues['networks']);
      }

      if( empty($formValues['auth_view']) ) {
          $formValues['auth_view'] = 'everyone';
      }

      if( empty($formValues['auth_comment']) ) {
          $formValues['auth_comment'] = 'everyone';
      }

      $values = array_merge($formValues, array(
          'owner_type' => $viewer->getType(),
          'owner_id' => $viewer->getIdentity(),
          'view_privacy' => $formValues['auth_view'],
      ));
      
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $viewer, 'approve');

      $values['parent_type'] = $parent_type;
      $values['parent_id'] =  $parent_id;
      
      $blog = $table->createRow();
      
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;
        
      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
        
      $blog->setFromArray($values);
      $blog->save();
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($values['body'], $blog);

      if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $blog->setPhoto($form->photo);
      }

      // Auth
      $auth = Engine_Api::_()->authorization()->context;

      if( $values['parent_type'] == 'group' ) {
          $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
          $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->addTagMaps($viewer, $tags);

      // Add activity only if blog is published
      if( $values['draft'] == 0) {
        if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $group, 'group_blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        } else {
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        }
        // make sure action exists before attaching the blog to the activity
        if( $action ) {
            Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $blog);
        }
      }
      
      //Start Send Approval Request to Admin
      Engine_Api::_()->core()->contentApprove($blog, 'blog');

      // Send notifications for subscribers
      Engine_Api::_()->getDbTable('subscriptions', 'blog')->sendNotifications($blog);

      //Send to all group members
      if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
        $members = Engine_Api::_()->group()->groupMembers($group->getIdentity());
        foreach($members as $member) {
          Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($member, $viewer, $group, 'group_blogcreate');
        }
      }
      
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id' => $blog->getIdentity(),'message' => $this->view->translate('Blog created successfully.'))));
  }

  public function editAction() {

    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( !Engine_Api::_()->core()->hasSubject('blog') ) {
        Engine_Api::_()->core()->setSubject($blog);
    }
    
    $category_id = (isset($blog->category_id) && $blog->category_id != 0) ? $blog->category_id : ((isset($_POST['category_id']) && $_POST['category_id'] != 0) ? $_POST['category_id'] : 0);
    $subcat_id = (isset($blog->subcat_id) && $blog->subcat_id != 0) ? $blog->subcat_id : ((isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0) ? $_POST['subcat_id'] : 0);
    $subsubcat_id = (isset($blog->subsubcat_id) && $blog->subsubcat_id != 0) ? $blog->subsubcat_id : ((isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0) ? $_POST['subsubcat_id'] : 0);

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams($blog, $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $parent_type = $blog->parent_type;
    $parent_id = $blog->parent_id;

    // Prepare form
    $this->view->form = $form = new Blog_Form_Edit(array(
        'parent_type' => $parent_type,
        'parent_id' => $parent_id
    ));
    $form->removeElement('token');

    // Populate form
    $form->populate($blog->toArray());

    $tagStr = '';
    foreach( $blog->tags()->getTagMaps() as $tagMap ) {
        $tag = $tagMap->getTag();
        if( !isset($tag->text) ) continue;
        if( '' !== $tagStr ) $tagStr .= ', ';
        $tagStr .= $tag->text;
    }
    
    $form->populate(array(
      'tags' => $tagStr,
      'networks' => explode(',', $blog->networks),
    ));
    $this->view->tagNamePrepared = $tagStr;

    $auth = Engine_Api::_()->authorization()->context;
    if( $parent_type == 'group' ) {
      $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
    } else {
      $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    }

    foreach( $roles as $role ) {
      if ($form->auth_view){
          if( $auth->isAllowed($blog, $role, 'view') ) {
              $form->auth_view->setValue($role);
          }
      }

      if ($form->auth_comment){
          if( $auth->isAllowed($blog, $role, 'comment') ) {
              $form->auth_comment->setValue($role);
          }
      }
    }
    
    // hide status change if it has been already published
    if( $blog->draft == "0" ) {
        $form->removeElement('draft');
    }
    
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields) &&  $blog->category_id){
        foreach($formFields as $fields){
          foreach($fields as $field){
            $subcat = array();
            if($fields['name'] == "subcat_id"){ 
              $subcat = Engine_Api::_()->getItemTable('blog_category')->getSubcategory(array('category_id'=>$blog->category_id,'column_name'=>'*'));
            }else if($fields['name'] == "subsubcat_id"){
              if($blog->subcat_id)
              $subcat = Engine_Api::_()->getItemTable('blog_category')->getSubSubcategory(array('category_id'=>$blog->subcat_id,'column_name'=>'*'));
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
				//$newFormFieldsArray[6]['name'] = "file";
        $this->generateFormFields($newFormFieldsArray,array('resources_type'=>'blog', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
      }
      $this->generateFormFields($formFields,array('resources_type'=>'blog', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }
    
    // Check post/form
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
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

      if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
          $values['auth_comment'] = 'everyone';
      }

      $values['view_privacy'] = $values['auth_view'];
      $blog->setFromArray($values);
      $blog->modified_date = date('Y-m-d H:i:s');
      $blog->save();
      
      Engine_Api::_()->core()->saveTinyMceImages($values['body'], $blog);

      // Add photo
      if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $blog->setPhoto($form->photo);
      }

      // Auth
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($blog, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($blog, $role, 'comment', ($i <= $commentMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $blog->tags()->setTagMaps($viewer, $tags);

      // insert new activity if blog is just getting published
      $action = Engine_Api::_()->getDbTable('actions', 'activity')->getActionsByObject($blog);
      if( engine_count($action->toArray()) <= 0 && $values['draft'] == '0' ) {
        $blog->creation_date = date('Y-m-d H:i:s');
        $blog->save();
        
        if( $parent_type == 'group') {
          $group = Engine_Api::_()->getItem($parent_type, $parent_id);
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $group, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        } else {
          $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $blog, 'blog_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
        }
        // make sure action exists before attaching the blog to the activity
        if( $action != null ) {
            Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $blog);
        }
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($blog) as $action ) {
        $action->privacy = isset($values['networks'])? $network_privacy : null;
        $action->save();
        $actionTable->resetActivityBindings($action);
      }

      // Send notifications for subscribers
      Engine_Api::_()->getDbTable('subscriptions', 'blog')
          ->sendNotifications($blog);

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('blog_id' => $blog->getIdentity(),'message' => $this->view->translate('Blog edited successfully.'))));
  }


  public function deleteAction() {

    $blog = Engine_Api::_()->getItem('blog', $this->getRequest()->getParam('blog_id'));

    if( !$this->_helper->requireAuth()->setAuthParams($blog, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $form = new Blog_Form_Delete();
    if( !$blog ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Blog entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    $db = $blog->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $blog->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Your blog entry has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  
  public function addAction()
  {
    $blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
    if( !$this->_helper->requireUser()->isValid() ) {   

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $user = $blog->getOwner('user');

    if (!$blog) {
      $error = Zend_Registry::get('Zend_Translate')->_("Blog doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }
    // Get subscription table
    $subscriptionTable = Engine_Api::_()->getDbTable('subscriptions', 'blog');
  
    // Check if they are already subscribed
		/*  if( $subscriptionTable->checkSubscription($blog, $viewer) ) {
				$this->view->status = true;
				$this->view->message = Zend_Registry::get('Zend_Translate')
						->_('You are already subscribed to this member\'s blog.');

				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
		}*/
			// Make form
		$this->view->form = $form = new Core_Form_Confirm(array(
			'title' => 'Subscribe?',
			'description' => 'Would you like to subscribe to this member\'s blog?',
			'class' => 'global_form_popup',
			'submitLabel' => 'Subscribe',
			'cancelHref' => 'javascript:parent.Smoothbox.close();',
		));

		if($this->_getParam('getForm')) {
				$formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
				$this->generateFormFields($formFields,array('resources_type'=>'blog'));
			}

			// Check method
		if( !$this->getRequest()->isPost() ) { 
			$status = false;
			$error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
		}

			// Check valid
		if( !$form->isValid($this->getRequest()->getPost()) ) {
			$validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
			if(is_countable($validateFields) && engine_count($validateFields))
				$this->validateFormFields($validateFields);
		}
			// Process
		$db = $user->getTable()->getAdapter();
		$db->beginTransaction();
		try {
			$subscriptionTable->createSubscription($user,$viewer);  
			$db->commit();
		} catch( Exception $e ) { 
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->getMessage(), 'result' => array()));
		}

		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Subscribed successfully.'), 'result' => array()));
	}
	
	public function removeAction() {
	
		$blog = Engine_Api::_()->getItem('blog', $this->_getParam('blog_id'));
		if( !$this->_helper->requireUser()->isValid() ) {   

			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$user = $blog->getOwner('user');
		if (!$blog) {
			$error = Zend_Registry::get('Zend_Translate')->_("Blog doesn't exist or not authorized to delete");
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
		}
		$subscriptionTable = Engine_Api::_()->getDbTable('subscriptions', 'blog');

		/* if( !$subscriptionTable->checkSubscription($user, $viewer) ) {
				$this->view->status = true;
				$this->view->message = Zend_Registry::get('Zend_Translate')
						->_('You are already not subscribed to this member\'s blog.');
				Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
		}*/

			// Make form
		$this->view->form = $form = new Core_Form_Confirm(array(
			'title' => 'Unsubscribe?',
			'description' => 'Would you like to unsubscribe from this member\'s blog?',
			'class' => 'global_form_popup',
			'submitLabel' => 'Unsubscribe',
			'cancelHref' => 'javascript:parent.Smoothbox.close();',
		));

			// Check method
		if( !$this->getRequest()->isPost() ) {
			$status = false;
			$error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
		}
		if($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields,array('resources_type'=>'blog'));
		}

			// Check valid
		if( !$form->isValid($this->getRequest()->getPost()) ) {
			$validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
			if(is_countable($validateFields) && engine_count($validateFields))
				$this->validateFormFields($validateFields);
		}
		
			// Process
		$db = $user->getTable()->getAdapter();
		$db->beginTransaction();

		try {
			$subscriptionTable->removeSubscription($user, $viewer);
			$db->commit();
		} catch( Exception $e ) {
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->getMessage(), 'result' => array()));
		}

		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('UnSubscribed successfully.'), 'result' => array()));
	}
	
	public function menuAction() {
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('blog_main', array());
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
}
