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
class Classified_IndexController extends Sesapi_Controller_Action_Standard {

  protected $_classifiedEnabled;
  
  public function init() {

		//Only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    $this->isClassifiedEnable();
  }
  
  protected function isClassifiedEnable() {
    $this->_classifiedEnabled = true;
  }
  
  public function closeAction() {

    $data = array();
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject())
      $classified = Engine_Api::_()->getItem('classified', $this->getRequest()->getParam('classified_id'));
    else
      $classified = Engine_Api::_()->core()->getSubject();
    if (!$classified) {
      $error = Zend_Registry::get('Zend_Translate')->_("Classified doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => array()));
    }

    // Check auth
    if (!$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'edit')->isValid()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
      $data['status'] = false;
      $data['message'] = $this->view->translate('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $data['message'], 'result' => $data));
    }
    // @todo convert this to post only
    $table = $classified->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $classified->closed = $classified->closed == 1 ? 0 : 1;
      $classified->save();
      $db->commit();
      $data['status'] = true;
      $data['message'] = $classified->closed == 1 ? $this->view->translate('Successfully Closed') : $this->view->translate('Successfully Unclosed');
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
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
    
      $classified = Engine_Api::_()->getItem('classified', $resource_id);

			Engine_Api::_()->getDbTable('ratings', 'core')->setRating(array('resource_id' => $resource_id, 'resource_type' => 'classified', 'rating' => $rating));
			
			$classified->rating = Engine_Api::_()->getDbTable('ratings', 'core')->getRating(array('resource_id' => $classified->getIdentity(), 'resource_type' => 'classified'));
			$classified->save();
			
			$owner = Engine_Api::_()->getItem('user', $classified->owner_id);
			if($owner->user_id != $user_id)
				Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $classified, 'classified_rating');
			
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->translate("You have successfully rated classified.")));
  }
  
	public function menuAction() {
		$menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('classified_main', array());
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
  

  // NONE USER SPECIFIC METHODS
  public function browseAction() {

    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();

    // Prepare form
    $form = new Classified_Form_Search();
    
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }

    // Populate form
    $categories = Engine_Api::_()->getDbTable('categories', 'classified')->getCategoriesAssoc();
    if( !empty($categories) && is_array($categories) && $form->getElement('category') ) {
      $form->getElement('category')->addMultiOptions($categories);
    }

    // Process form
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    //$this->view->formValues = array_filter($values);

    
    $customFieldValues = array_intersect_key($values, $form->getFieldElements());
    
    // Process options
    $tmp = array();
    foreach( $customFieldValues as $k => $v ) {
      if( null == $v || '' == $v || (is_array($v) && engine_count(array_filter($v)) == 0) ) {
        continue;
      } elseif( false !== strpos($k, '_field_') ) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } elseif( false !== strpos($k, '_alias_') ) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $customFieldValues = $tmp;
    
    // Do the show thingy
    if( @$values['show'] == 2 ) {
      // Get an array of friend ids to pass to getClassifiedsPaginator
      $table = Engine_Api::_()->getItemTable('user');
      $select = $viewer->membership()->getMembersSelect('user_id');
      $friends = $table->fetchAll($select);
      // Get stuff
      $ids = array();
      foreach( $friends as $friend ) {
        $ids[] = $friend->user_id;
      }
      //unset($values['show']);
      $values['users'] = $ids;
    }

    // check to see if request is for specific user's listings
    if( ($userId = $this->_getParam('user_id')) ) {
      $values['user_id'] = $userId;
    }

    //$this->view->assign($values);
    
    if(!empty($_POST['user_id']))
      $values["user_id"] = $_POST['user_id'];
      
    if(!empty($_POST['category_id']))
      $values['category'] = $_POST['category_id'];

    // items needed to show what is being filtered in browse page
//     if( !empty($values['tag']) ) {
//       $tag_text = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
//     }
    
//     $view = $this->view;
//     $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    $paginator = Engine_Api::_()->getItemTable('classified')->getClassifiedsPaginator($values);
    $itemsCount = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.page', 10);
    $paginator->setItemCountPerPage($itemsCount);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    $result = $this->resourceResults($paginator);
    
    if(!empty($_POST['user_id'])) {
    
      $viewer = Engine_Api::_()->user()->getViewer();
      $menuoptions= array();
      $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'classified', 'edit');
      $counter = 0;
       if ($canEdit) {
//          $menuoptions[$counter]['name'] = "addphoto";
//          $menuoptions[$counter]['label'] = $this->view->translate("Add Photos");
//          $counter++;
         $menuoptions[$counter]['name'] = "edit";
         $menuoptions[$counter]['label'] = $this->view->translate("Edit Classified");
         $counter++;
       }

			$canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'classified', 'delete');
			if ($canDelete) {
				$menuoptions[$counter]['name'] = "delete";
				$menuoptions[$counter]['label'] = $this->view->translate("Delete Classified");
				$counter++;
			}
      $result['menus'] = $menuoptions;  
    }
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist classifieds.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams)); 
    
  }
  
  public function categoryAction() {
 
    $params['countClassifieds'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'classified')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {
    
      if($key == '') continue;
      $catgeoryArray["category"][$counter]["category_id"] = $key;
      $catgeoryArray["category"][$counter]["label"] = $this->getCategoryName(array('column_name' => 'category_name', 'category_id' => $key));

      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');
      
      //Classifieds Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'classifieds', 'module_name' => 'classified'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s classified', '%s classifieds', $Itemcount), $this->view->locale()->toNumber($Itemcount));
      
      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }
  
  function resourceResults($paginator) {
  
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
      
      // custom fields values
//       $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($item);
//       $customFieldsValues = $this->view->fieldValueLoop($item, $fieldStructure);
//       if($customFieldsValues) {
//         $resource['custom_fields_values'] = $customFieldsValues;
//       }
      
      //Category name
      if(!empty($resource['category_id'])) {
        $category_name = $this->getCategoryName(array('column_name' => 'category_name', 'category_id' => $item->category_id));
        $resource['category_name'] = $category_name;
      }

      // Check content like or not and get like count
      if($this->_classifiedEnabled) {
        if($viewer->getIdentity() != 0) {
          $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
          $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
        }
      }
      $result['classifieds'][$counterLoop] = $resource;
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', '');
      if(!engine_count($images))
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl()). 'application/modules/Classified/externals/images/nophoto_classified_thumb_normal.png';
      $result['classifieds'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }

    return $result;
  }
  
  public function getCategoryName($params = array()) {
    
    $categoryTable = Engine_Api::_()->getDbTable('categories', 'classified');
    $categoryTableName = $categoryTable->info('name');
    
    $select = $categoryTable->select()
            ->from($categoryTableName, $params['column_name']);

    if (isset($params['category_id']))
      $select = $select->where('category_id = ?', $params['category_id']);

    return $select = $select->query()->fetchColumn();
  }
  
  public function getProfileTypeValue($params = array()) {
    $valuesTable = Engine_Api::_()->fields()->getTable('classified', 'values');
    $valuesTableName = $valuesTable->info('name');
    return $valuesTable->select()
                    ->from($valuesTableName, array('value'))
                    ->where($valuesTableName . '.item_id = ?', $params['classified_id'])
                    ->where($valuesTableName . '.field_id = ?', $params['field_id'])->query()
                    ->fetchColumn();
  }
  
  public function viewAction() {
  
    // Check permission
    $viewer = Engine_Api::_()->user()->getViewer();

    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if( $classified ) {
      Engine_Api::_()->core()->setSubject($classified);
    }

    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }
    
    if( !$classified || !$classified->getIdentity() || ((!$classified->approved) && !$classified->isOwner($viewer)) ) {
      if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
      } else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }

    // Network check
		$networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($classified, 'owner_id');
		if(empty($networkPrivacy))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    
    // Prepare data
    $classifiedTable = Engine_Api::_()->getDbTable('classifieds', 'classified');
    
    $classified_content = $classified->toArray();
    
    $body = @str_replace('src="/', 'src="' . $this->getBaseUrl() . '/', $classified_content['body']);
    $body = preg_replace('/<\/?a[^>]*>/','',$body);
    $classified_content['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $classified_content['owner_title'] = Engine_Api::_()->getItem('user', $classified_content['owner_id'])->getTitle();
    $classified_content['resource_type'] = $classified->getType();
    $classified_content['resource_id'] = $classified->getType();
    $classified_content['category_id'] = $classified->category_id;
		$classified_content['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $classified->getIdentity(), 'resource_type' => 'classified'));
    if( !$classified->isOwner($viewer) ) {
      $classifiedTable->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'classified_id = ?' => $classified->getIdentity(),
      ));
    }
    
    // Get tags
    $classifiedTags = $classified->tags()->getTagMaps();
    if (!empty($classifiedTags)) {
      foreach ($classifiedTags as $tag) {
        $classified_content['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
      }
    }
    
    //Location and Price profile field values
    $location = $this->getProfileTypeValue(array('classified_id' => $classified->getIdentity(), 'field_id' => 3));
    if($location) {
      $classified_content['location'] = $location;
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $price = $this->getProfileTypeValue(array('classified_id' => $classified->getIdentity(), 'field_id' => 2));
    $givenSymbol = $settings->getSetting('payment.currency', 'USD');
    if($price) {
      $classified_content['price'] =  Engine_Api::_()->payment()->getCurrencyPrice($price,'','','');
    }

    // Get category
    if( !empty($classified->category_id) ) {
      $category = Engine_Api::_()->getItem('classified_category', $classified->category_id);
      $classified_content['category_title'] = $category->category_name;
			if( !empty($classified->subcat_id) ) {
				$category = Engine_Api::_()->getItem('classified_category', $classified->subcat_id);
				$classified_content['subcategory_title'] = $category->category_name;
			}
			if( !empty($classified->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('classified_category', $classified->subsubcat_id);
				$classified_content['subsubcategory_title'] = $category->category_name;
			}
    }
    
    if($this->_classifiedEnabled) {
      if($viewer->getIdentity() != 0) {
        $classified_content['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($classified);
        $classified_content['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($classified);
      }
    }
    
    $classified_content['content_url'] = $this->getBaseUrl(false,$classified->getHref());
    $classified_content['can_favorite'] = false;
    $classified_content['can_share'] = false;
    
		$classified_content['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $classified->getIdentity(), 'resource_type' => 'classified'));
		$classified_content['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.enable.rating', 1);
		$classified_content['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.ratingicon', 'fas fa-star');

    $result['classified'] = $classified_content;
    
    if($viewer->getIdentity() > 0) {
    
			$result['classified']['permission']['canEdit'] = $canEdit = $viewPermission = $classified->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['classified']['permission']['canComment'] =  $classified->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['classified']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesclassified_classified', 'create') ? true : false;
			$result['classified']['permission']['can_delete'] = $canDelete  = $classified->authorization()->isAllowed($viewer,'delete') ? true : false;
      
      $menuoptions= array();
      $counter = 0;
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete");
        $counter++;
      }
      
      if($classified->owner_id == $viewer->getIdentity()) {
				if( !$classified->closed ) {
					$menuoptions[$counter]['name'] = "close";
					$menuoptions[$counter]['close'] = "1";
					$menuoptions[$counter]['label'] = $this->view->translate("Close");
					$counter++;
				} else {
					$menuoptions[$counter]['name'] = "close";
					$menuoptions[$counter]['close'] = "0";
					$menuoptions[$counter]['label'] = $this->view->translate("Open");
					$counter++;
				}
			}
			$menuoptions[$counter]['name'] = "report";
			$menuoptions[$counter]['label'] = $this->view->translate("Report");
      $result['menus'] = $menuoptions;
		}
    
    $result['classified']["share"]["name"] = "share";
    $result['classified']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$classified->getPhotoUrl());
    if($photo)
      $result['classified']["share"]["imageUrl"] = $photo;
			$result['classified']["share"]["url"] = $this->getBaseUrl(false,$classified->getHref());
      
    $result['classified']["share"]["title"] = $classified->getTitle();
    $result['classified']["share"]["description"] = strip_tags($classified->getDescription());
    $result['classified']["share"]['urlParams'] = array(
        "type" => $classified->getType(),
        "id" => $classified->getIdentity()
    );

		//Classified multiple photo work
		$album = $classified->getSingletonAlbum();
		$photoPaginator = $album->getCollectiblesPaginator();
		$photoPaginator->setCurrentPageNumber(1);
		$photoPaginator->setItemCountPerPage(100);
		$photoCounter = 0;
		if(engine_count($photoPaginator)){
			foreach($photoPaginator as $photo){
				$file = Engine_Api::_()->getItem('storage_file',$photo->file_id);
				$result['images'][$photoCounter]['image_type'] = 'extra';
				if($photo->file_id == $classified->photo_id){
					$result['images'][$photoCounter]['image_type'] = 'main';
				}
				$result['images'][$photoCounter]['image_url'] = $this->getBaseUrl(true,$file->map());
				$photoCounter++;
			}
		}
    
    if(is_null($result['classified']["share"]["title"]))
      unset($result['classified']["share"]["title"]);
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
  }

  public function createAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'create')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getItemTable('classified')->getClassifiedsPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'max');
    $paginator->getTotalItemCount();
    $current_count = $paginator->getTotalItemCount();
    if (($current_count >= $quota) && !empty($quota)) {
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of classifieds allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => $message, 'result' => array()));
    }
        
    // Prepare form
    $form = new Classified_Form_Create();
    //$form->removeElement('token');

    $customfieldform = $form->getSubForm('fields');
    $formFields = array();
    if($form->getElement("cancel")){
      $formFields[] = $form->getElement("cancel");
      $form->removeElement("cancel");
    }
    if($form->getElement("execute")){
      $formFields[] = $form->getElement("execute");
      $form->removeElement("execute");
    }
    $form->setElements(array_merge($form->getElements(),$customfieldform->getElements(),$formFields));
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'classified', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }
        
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('classified', $this->view->viewer()->level_id, 'flood');
    if(!empty($itemFlood[0])){
        //get last activity
        $tableFlood = Engine_Api::_()->getDbTable("classifieds",'classified');
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
    $table = Engine_Api::_()->getItemTable('classified');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
    
      // Create classified
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

      $values = array_merge($values, array(
          'owner_type' => $viewer->getType(),
          'owner_id' => $viewer->getIdentity(),
          'view_privacy' => $values['auth_view'],
      ));
      
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('classified', $viewer, 'approve');

      $classified = $table->createRow();
      
      if (is_null($values['subcat_id']))
        $values['subcat_id'] = 0;

      if (is_null($values['subsubcat_id']))
        $values['subsubcat_id'] = 0;
        
      $classified->setFromArray($values);
      $classified->save();
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($values['body'], $classified);

      // Set photo
      if( !empty($_FILES['photo']['name']) &&  !empty($_FILES['photo']['size']) ) {
        $classified->setPhoto($form->photo);
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $tags = array_filter(array_map("trim", $tags));
      $classified->tags()->addTagMaps($viewer, $tags);

      // Add fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($classified);
      $customfieldform->saveValues();

      // Set privacy
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($classified, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }
      
      $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $classified, 'classified_new', '', array('privacy' => isset($values['networks'])? $network_privacy : null));

      if( $action != null ) {
          Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $classified);
      }
      
      //Start Send Approval Request to Admin
      Engine_Api::_()->core()->contentApprove($classified, 'classified');

      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('classified_id' => $classified->getIdentity(),'message' => $this->view->translate('Classified created successfully.'))));
  }

  public function editAction() {
  
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if(!Engine_Api::_()->core()->hasSubject('classified') ) {
      Engine_Api::_()->core()->setSubject($classified);
    }
    
    if( !$this->_helper->requireSubject()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Prepare form
    $form = new Classified_Form_Edit(array(
      'item' => $classified
    ));
    $form->removeElement('photo');
    
    $form->removeElement('cover');
    
    // Populate form
    $form->populate($classified->toArray());

    $tagStr = '';
    foreach( $classified->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      if( '' !== $tagStr ) $tagStr .= ', ';
      $tagStr .= $tag->text;
    }
    $form->populate(array(
      'tags' => $tagStr,
    ));
    $this->view->tagNamePrepared = $tagStr;

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($classified, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($classified, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
    }

    $customfieldform = $form->getSubForm('fields');
    $formFields = array();
    if($form->getElement("cancel")){
      $formFields[] = $form->getElement("cancel");
      $form->removeElement("cancel");
    }
    if($form->getElement("execute")){
      $formFields[] = $form->getElement("execute");
      $form->removeElement("execute");
    }
    $form->setElements(array_merge($form->getElements(),$customfieldform->getElements(),$formFields));

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields) &&  $classified->category_id){
        foreach($formFields as $fields){
          foreach($fields as $field){
            $subcat = array();
            if($fields['name'] == "subcat_id"){ 
              $subcat = Engine_Api::_()->getItemTable('classified_category')->getSubcategory(array('category_id'=>$classified->category_id,'column_name'=>'*'));
            }else if($fields['name'] == "subsubcat_id"){
              if($classified->subcat_id)
              $subcat = Engine_Api::_()->getItemTable('classified_category')->getSubSubcategory(array('category_id'=>$classified->subcat_id,'column_name'=>'*'));
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
				//$newFormFieldsArray[4]['name'] = "file";
        $this->generateFormFields($newFormFieldsArray,array('resources_type'=>'classified', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
      }
      $this->generateFormFields($formFields,array('resources_type'=>'classified', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }
        
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    // Process
    // handle save for tags
    $values = $form->getValues();
    $tags = preg_split('/[,]+/', $values['tags']);
    $tags = array_filter(array_map("trim", $tags));
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      if (isset($values['networks'])) {
          $network_privacy = 'network_'. implode(',network_', $values['networks']);
          $values['networks'] = implode(',', $values['networks']);
      }

      if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
      }
      $values['view_privacy'] = $values['auth_view'];
      $classified->setFromArray($values);
      $classified->modified_date = date('Y-m-d H:i:s');

      $classified->tags()->setTagMaps($viewer, $tags);
      $classified->save();
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($values['body'], $classified);

      $cover = $values['cover'];
    

      // Add photo
//       if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
//         $this->setPhoto($_FILES['image'],$classified);
//       }

      // Save custom fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($classified);
      $customfieldform->saveValues();

      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if( !empty($values['auth_view']) ) {
          $authView = $values['auth_view'];
      } else {
          $authView = "everyone";
      }
      $viewMax = array_search($authView, $roles);

      foreach( $roles as $i => $role ) {
          $auth->setAllowed($classified, $role, 'view', ($i <= $viewMax));
      }

      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if( !empty($values['auth_comment']) ) {
          $authComment = $values['auth_comment'];
      } else {
          $authComment = "everyone";
      }
      $commentMax = array_search($authComment, $roles);

      foreach( $roles as $i=>$role ) {
          $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($classified) as $action ) {
          $action->privacy = isset($values['networks'])? $network_privacy : null;
          $action->save();
          $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('classified_id' => $classified->getIdentity(),'message' => $this->view->translate('Classified edited successfully.'))));
  }
  

  public function deleteAction() {

    $classified = Engine_Api::_()->getItem('classified', $this->getRequest()->getParam('classified_id'));
    
    if( !$this->_helper->requireAuth()->setAuthParams($classified, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 

    $form = new Classified_Form_Delete();
    
    if( !$classified ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Classified entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
    }

    if( !$this->getRequest()->isPost() ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
    }
    
//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
//       //$formFields[4]['name'] = "file";
//       if(is_countable($validateFields) && engine_count($validateFields))
//       $this->validateFormFields($validateFields);
//     }

    $db = $classified->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $classified->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Your classified listing has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }

   public function uploadAction(){
      $classified_id = $this->_getParam('classified_id', false);
      $classified = Engine_Api::_()->getItem('classified', $classified_id);
      $album = $classified->getSingletonAlbum();
      $album_id = $album->getIdentity();
      
      if(!$classified_id)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
      // set up data needed to check quota
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['user_id'] = $viewer->getIdentity();

      $quota = $quota = 0;
      // Get form
      $form = new Classified_Form_Photo_Upload();
      $form->file->setAttrib('data', array('classified_id' => $classified->getIdentity()));

      // Render
      //$form->populate(array('album' => $album_id));
      if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'classified'));
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
      $photoTable = Engine_Api::_()->getDbTable('photos', 'classified');
      $db = $photoTable->getAdapter();
      $db->beginTransaction();
      try {

          // Add action and attachments
        $api = Engine_Api::_()->getDbTable('actions', 'activity');
        $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $classified, 'classified_photo_upload', null, array(
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
            $photo->classified_id = $classified->classified_id;
            $photo->save();
            $photo->setPhoto($image);
            
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

    $form = new Classified_Form_Search();

    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields,array('resources_type'=>'classified'));
  }

}
