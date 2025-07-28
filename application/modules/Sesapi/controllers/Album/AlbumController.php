<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AlbumController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

 class Album_AlbumController extends Sesapi_Controller_Action_Standard {

  // Album constructor function
  public function init() {

    if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( 0 !== ($photo_id = (int) $this->_getParam('photo_id')) &&
      null !== ($photo = Engine_Api::_()->getItem('album_photo', $photo_id)) )
    {
      Engine_Api::_()->core()->setSubject($photo);
    }

    else if( 0 !== ($album_id = (int) $this->_getParam('album_id')) &&
      null !== ($album = Engine_Api::_()->getItem('album', $album_id)) )
    {
      Engine_Api::_()->core()->setSubject($album);
    }
  }
  
  
  public function nextPreviousImage($photo_id,$album_id,$condition = "<=") {

    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $select = $photoTable->select()
    ->where('album_id =?', $album_id)
    ->where('photo_id '.$condition.' ?',$photo_id)
    ->order('order ASC')
    ->limit(20);
    return $photoTable->fetchAll($select);
  }
  
  // Album view function.
  public function viewAction() {

    if( !$this->_helper->requireSubject('album')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $album = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $albumData = array();
    $albumData['album'] = $album->toArray();

    $menuoptions= array();
    $counter = 0;
    $canEdit = $this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->isValid();
    if($canEdit) {
      $menuoptions[$counter]['name'] = "addmorephotos";
      $menuoptions[$counter]['label'] = $this->view->translate("Add More Photos");
      $counter++;
      $menuoptions[$counter]['name'] = "edit";
      $menuoptions[$counter]['label'] = $this->view->translate("Edit Settings"); 
      $counter++;
    }
//     $editphotos = $this->_helper->requireAuth()->setAuthParams($album, null, 'edit')->isValid();
//     if($editphotos){
//       $menuoptions[$counter]['name'] = "editphotos";
//       $menuoptions[$counter]['label'] = $this->view->translate('Manage Album');
//       $counter++;
//     }
    $canDelete = $this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid();
    if($canDelete) {
      $menuoptions[$counter]['name'] = "delete";
      $menuoptions[$counter]['label'] = $this->view->translate("Delete Album");
      $counter++;
    }
    if(!$album->isOwner($viewer)){
      $menuoptions[$counter]['name'] = "report";
      $menuoptions[$counter]['label'] = $this->view->translate("Report Album");
    }      
   
    $albumData['menus'] = $menuoptions;

    if( !empty($album->category_id) ) {
      $category = Engine_Api::_()->getItem('album_category', $album->category_id);
      $albumData['album']['category_title'] = $category->category_name;
			if( !empty($album->subcat_id) ) {
				$category = Engine_Api::_()->getItem('album_category', $album->subcat_id);
				$albumData['album']['subcategory_title'] = $category->category_name;
			}
			if( !empty($album->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('album_category', $album->subsubcat_id);
				$albumData['album']['subsubcategory_title'] = $category->category_name;
			}
    }
		$albumData['album']['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_type' => $album->getType(), 'resource_id' => $album->getIdentity()));
		
		$albumData['album']['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1);
		$albumData['album']['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('album.ratingicon', 'fas fa-star');
    $albumData['album']['module_name'] = 'album';
    $albumData['album']['user_title'] = $album->getOwner()->getTitle();
    $owner = $album->getOwner();
    if($owner && $owner->photo_id){
      $photo= $this->getBaseUrl(false,$owner->getPhotoUrl());  
      $albumData['album']['user_image']  = $photo;
    } else
    $albumData['album']['user_image'] =  $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_profile.png');

    if($this->view->viewer()->getIdentity() != 0) {

      $albumData['album']['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($album);
      $albumData['album']['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($album);
    }
    
    if (!$viewer || !$viewer->getIdentity() || !$album->isOwner($viewer)) {
      $album->view_count = new Zend_Db_Expr('view_count + 1');
      $album->save();
    }
    
    $albumData['album']["share"]["name"] = "share";
    $albumData['album']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$album->getPhotoUrl());
    
    if($photo)
      $albumData['album']["share"]["imageUrl"] = $photo;
    $albumData['album']["share"]["url"] = $this->getBaseUrl(false,$this->getHref($album->getIdentity()));

    $albumData['album']["share"]["title"] = $album->getTitle();
    $albumData['album']["share"]["description"] = strip_tags($album->getDescription());
    $albumData['album']["share"]['urlParams'] = array(
      "type" => $album->getType(),
      "id" => $album->getIdentity()
    );
    
    if(is_null($albumData['album']["share"]["title"]))
      unset($albumData['album']["share"]["title"]);

    $photoTable = Engine_Api::_()->getItemTable('album_photo');
    $paginator = $photoTable->getPhotoPaginator(array(
      'album' => $album,
    ));
    
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    
    $albumData['photos'] = $this->getPhotos($paginator);

    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    //echo "<pre>";var_dump($albumData);die;
    if($albumData['photos'] <= 0) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('No photo created in this album yet.'), 'result' => array())); 
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData),$extraParams));
    }
  }

  protected function getHref($album_id){
    return str_replace("album", "albums",$this->view->url(array('album_id' => $album_id), 'album_specific'));
  }

  // Album view function.
  public function lightboxAction() {

    $photo = Engine_Api::_()->core()->getSubject();
    if($photo && !$this->_getParam('album_id',null)){
      $album_id = $photo->album_id;  
    } else {
      $album_id = $this->_getParam('album_id',null);  
    }
    
    if ($album_id && null !== ($album = Engine_Api::_()->getItem('album', $album_id))) {
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    }
    
    $photo_id = $photo->getIdentity();
    if (!$this->_helper->requireSubject('album_photo')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 

    $viewer = Engine_Api::_()->user()->getViewer();

    $albumData = array();    
    if($viewer->getIdentity() > 0) {

      $menu = array();
      $counterMenu = 0;
      $menu[$counterMenu]["name"] = "save";
      $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");  
      $counterMenu++;
      $canEdit  = $album->authorization()->isAllowed($viewer, 'edit') ? true : false;
      if($canEdit){
        $menu[$counterMenu]["name"] = "edit";
        $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");  
        $counterMenu++;
      }

      $can_delete  = $album->authorization()->isAllowed($viewer,'delete') ? true : false;
      if($canEdit) {
        $menu[$counterMenu]["name"] = "delete";
        $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");  
        $counterMenu++;
      }
      $menu[$counterMenu]["name"] = "report";
      $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");  
      $counterMenu++;
      
      $menu[$counterMenu]["name"] = "makeprofilephoto";
      $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");  
      $counterMenu++;
      $albumData['menus'] = $menu;
      $can_tag = $album->authorization()->isAllowed($viewer, 'tag') ? true : false;
      $canUntagGlobal = $album->isOwner($viewer) ? true : false;
      $canComment =  $album->authorization()->isAllowed($viewer, 'comment') ? true : false;
      
      $albumData['can_comment'] = $canComment;
      $albumData['can_tag'] = $can_tag;
      $albumData['can_untag'] = $canUntagGlobal;
      
      $sharemenu = array();
      if($viewer->getIdentity() > 0){
        $sharemenu[0]["name"] = "siteshare";
        $sharemenu[0]["label"] = $this->view->translate("Share");
      }
      $sharemenu[1]["name"] = "share";
      $sharemenu[1]["label"] = $this->view->translate("Share Outside");  
      $albumData['share'] = $sharemenu;      
    }

    $condition = $this->_getParam('condition');
    if(!$condition) {
      $next = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,">="),true);
      $previous = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,"<"),true);
      $array_merge = array_merge($previous,$next);
      
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
        $recArray = array();
        $reactions = Engine_Api::_()->getDbTable('reactions','comment')->getPaginator();
        $counterReaction = 0;
        
        foreach($reactions as $reac) {
          if(!$reac->enabled)
            continue;
          $albumData['reaction_plugin'][$counterReaction]['reaction_id']  = $reac['reaction_id'];
          $albumData['reaction_plugin'][$counterReaction]['title']  = $this->view->translate($reac['title']);
          $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
          $albumData['reaction_plugin'][$counterReaction]['image']  = $icon['main'];
          $counterReaction++;
        }
      }
    } else {
      $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id,$album_id,$condition),true);
    }
    $albumData['module_name'] = 'album';
    $albumData['photos'] = $array_merge;
    
    if(engine_count($albumData['photos']) <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('No photo created in this album yet.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $albumData)));
  }
  
  public function getPhotos($paginator,$updateViewCount = false) {

    $result = array();
    $counter = 0;

    foreach($paginator as $photos) {

      $photo = $photos->toArray();
      $photos->view_count = new Zend_Db_Expr('view_count + 1');
      $photos->save();
      $photo['user_title'] = $photos->getOwner()->getTitle();
      
      if($this->view->viewer()->getIdentity() != 0) {
        $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
        $photo['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($photos);
      }

      $attachmentItem = $photos;
      if($attachmentItem->getPhotoUrl())
        $photo["shareData"]["imageUrl"] = $this->getBaseurl(false,$attachmentItem->getPhotoUrl());

      $photo["shareData"]["title"] = $attachmentItem->getTitle();
      $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());
      
      $photo["shareData"]['urlParams'] = array(
        "type" => $photos->getType(),
        "id" => $photos->getIdentity()
      );
      
      if(is_null($photo["shareData"]["title"]))
        unset($photo["shareData"]["title"]);

      $owner = $photos->getOwner();
      $photo['owner']['title'] = $owner ->getTitle();
      $photo['owner']['id'] =  $owner->getIdentity();
      $photo["owner"]['href'] = $owner->getHref();
      $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos,'',"");
      
      $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($this->view->viewer(), 'comment') ? true : false;
      $photo['module_name'] = 'album';
      if ($photo['can_comment']) {

        $viewer_id = $this->view->viewer()->getIdentity();
        if($viewer_id) {
          $itemTable = Engine_Api::_()->getItemTable($photos->getType(),$photos->getIdentity());
          $tableLike = Engine_Api::_()->getDbTable('likes', 'core');
          $tableMainLike = $tableLike->info('name');
          $select = $tableLike->select()
          ->from($tableMainLike)
          ->where('resource_type = ?', $photos->getType())
          ->where('poster_id = ?', $viewer_id)
          ->where('poster_type = ?', 'user')
          ->where('resource_id = ?', $photos->getIdentity());
          $resultData = $tableLike->fetchRow($select);
          if ($resultData) {
            $photo['reaction_type'] = $resultData->type;
          }
        }
        
        $photo['resource_type'] = $photos->getType();
        $photo['resource_id'] = $photos->getIdentity();
        
        $table = Engine_Api::_()->getDbTable('likes','core');
        $select = $table->select()->from($table->info('name'),array('type'=>'type','total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$photos->getIdentity())->group('type')->setIntegrityCheck(false);
        
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
          $select->where('resource_type =?',$photos->getType());

          $recTable = Engine_Api::_()->getDbTable('reactions','comment')->info('name');
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$table->info("name").'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
          $resultData =  $table->fetchAll($select);
        }
        $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
        $reactionData = array();
        $reactionCounter = 0;
        if(is_countable($resultData) && engine_count($resultData)){
          foreach($resultData as $type){
            if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
              $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['total'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
              $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
            }
            $reactionCounter++;
          } 
          $photo['reactionData'] = $reactionData;
        }
        if($photo['is_like']) {
          $photo[$counter]['is_like'] = true;
          $like = true;
          $type = $photo['reaction_type'];
          if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
            $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
            $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
          }
        } else {
          $photo[$counter]['is_like'] = false;
          $like = false;
          $type = '';
          $imageLike = '';
          $text = 'Like';
        }
        if(empty($like)) {
          $photo[$counter]["like"]["name"] = "like";
        } else {
          $photo[$counter]["like"]["name"] = "unlike";
        }
        // Get tags
        $tags = array();
        foreach ($photos->tags()->getTagMaps() as $tagmap) {
          $tags[] = array_merge($tagmap->toArray(), array(
            'id' => $tagmap->getIdentity(),
            'text' => $tagmap->getTitle(),
            'href' => $tagmap->getHref(),
            'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
          ));
        }
        
        $photo["tags"] = $tags;
        $photo["like"]["type"] = $type;
        $photo["like"]["image"] = $imageLike;
        $photo["like"]["title"] = $this->view->translate($text);            
        $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(),'',$photos->likes()->getLike($this->view->viewer()),$this->view->viewer());
      }
      if(!engine_count($album_photo['images']))
        $album_photo['images']['main'] = $this->getBaseUrl(true,$photos->getPhotoUrl());
      $result[$counter] = array_merge($photo,$album_photo);
      $counter++;
    }
    return $result;
  }
  

  //function for autosuggest album
  public function getAlbumAction() {

    $sesdata = array();
    $value['text'] = $this->_getParam('text');
    $albums = Engine_Api::_()->getDbTable('albums', 'sesalbum')->getAlbumsAction($value);
    foreach ($albums as $album) {
      $album_icon_photo = $this->view->itemPhoto($album, 'thumb.icon');
      $sesdata[] = array(
        'id' => $album->album_id,
        'label' => $album->title,
        'photo' => $album_icon_photo
      );
    }
    return $this->_helper->json($sesdata);
  }

  //album edit action
  public function editAction() {

    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if (!$this->_helper->requireSubject('album')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Prepare data
    $album = Engine_Api::_()->core()->getSubject();
    
    // Make form
    $form = new Album_Form_Album_Edit();

     // Check if post and populate
    if($this->_getParam('getForm')) {

      $form->populate($album->toArray());
      
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
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
      
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);

      //set subcategory and 3rd category populated work
      $newFormFieldsArray = array();
      if(is_countable($formFields) && engine_count($formFields) &&  $album->category_id){
        foreach($formFields as $fields){
          foreach($fields as $field){
            $subcat = array();
            if($fields['name'] == "subcat_id"){ 
              $subcat = Engine_Api::_()->getItemTable('album_category')->getSubcategory(array('category_id'=>$album->category_id,'column_name'=>'*'));
            }else if($fields['name'] == "subsubcat_id"){
              if($album->subcat_id)
              $subcat = Engine_Api::_()->getItemTable('album_category')->getSubSubcategory(array('category_id'=>$album->subcat_id,'column_name'=>'*'));
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
        $this->generateFormFields($newFormFieldsArray);
      }
      //$formFields[4]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'album', 'formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
    }
    
    if(!$form->isValid($this->getRequest()->getPost())) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
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
      $album->setFromArray($values);
      $album->save();

      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if (empty($values['auth_view'])) {
        $values['auth_view'] = key($form->auth_view->options);
        if (empty($values['auth_view'])) {
          $values['auth_view'] = 'everyone';
        }
      }
      
      if (empty($values['auth_comment'])) {
        $values['auth_comment'] = key($form->auth_comment->options);
        if (empty($values['auth_comment'])) {
          $values['auth_comment'] = 'owner_member';
        }
      }
      
      if (empty($values['auth_tag'])) {
        $values['auth_tag'] = key($form->auth_tag->options);
        if (empty($values['auth_tag'])) {
          $values['auth_tag'] = 'owner_member';
        }
      }
      
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $tagMax = array_search($values['auth_tag'], $roles);
      
      //set roles
      foreach ($roles as $i => $role) {
        $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($album, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($album, $role, 'tag', ($i <= $tagMax));
      }
      
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($album) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('album_id'=>$album->getIdentity(),'message'=>$this->view->translate('Album Edit successfully.'))));
  }

  // Album delete action
  public function deleteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $album = Engine_Api::_()->getItem('album', $this->getRequest()->getParam('album_id'));
    
    if (!$this->_helper->requireAuth()->setAuthParams($album, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));

    // In smoothbox
    $this->view->form = $form = new Album_Form_Album_Delete();
    
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    }
    
    $db = $album->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $album->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('The selected albums have been successfully deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }

  public function uploadsAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    
    $album_id = $this->_getParam('album_id','');
    $album = Engine_Api::_()->getItem('album',$album_id);
    
    if(!$album || !$album->authorization()->isAllowed($viewer, 'edit'))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid Request'), 'result' => array()));
    
    ini_set("memory_limit","240M");

    if(!empty($_FILES["attachmentImage"]) && engine_count($_FILES["attachmentImage"]) > 0) {

      // Get album
      $viewer = Engine_Api::_()->user()->getViewer();
      $table = Engine_Api::_()->getItemTable('album');
      
      $type = 'wall';
      $photoTable = Engine_Api::_()->getItemTable('photo');
      
      $auth = Engine_Api::_()->authorization()->context;
      try {

        if(engine_count($_FILES['attachmentImage']['name'])){
          $api = Engine_Api::_()->getDbTable('actions', 'activity');
          $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $album, 'album_photo_new', null, array('count' =>  engine_count($_FILES['attachmentImage']['name'])));
        }
        $counter = 0;
        foreach($_FILES['attachmentImage']['name'] as $image) {

          $uploadimage = array();
          
          if ($_FILES['attachmentImage']['name'][$counter] == "")
           continue;

         $uploadimage["name"] = $_FILES['attachmentImage']['name'][$counter];
         $uploadimage["type"] = $_FILES['attachmentImage']['type'][$counter];
         $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$counter];
         $uploadimage["error"] = $_FILES['attachmentImage']['error'][$counter];
         $uploadimage["size"] = $_FILES['attachmentImage']['size'][$counter];

         $photo = $photoTable->createRow();
         $photo->setFromArray(array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
        ));

         $photo->save();
         $photo->setPhoto($uploadimage);              
         $photo->order = $photo->photo_id;
         $photo->album_id = $album->album_id;
         $photo->save();

         if (!$album->photo_id) {
          $album->photo_id = $photo->getIdentity();
          $album->save();
        }

          // Authorizations
        $auth->setAllowed($photo, 'everyone', 'view', true);
        $auth->setAllowed($photo, 'everyone', 'comment', true);
        if( $action instanceof Activity_Model_Action && $counter < 9) {
          $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
        }
        $counter++;
      }
    } catch(Exception $e) {
      $this->view->error =  $e->getMessage();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
    }
  }
  $this->view->message = Zend_Registry::get('Zend_Translate')->_('Photo uploaded successfully.');
  Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $this->view->message));
}
}
