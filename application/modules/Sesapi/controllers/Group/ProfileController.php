<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ProfileController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Group_ProfileController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
  
      if (!$this->_helper->requireAuth()->setAuthParams('group', null, 'view')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    // @todo this may not work with some of the content stuff in here, double-check
    $subject = null;

    if (!Engine_Api::_()->core()->hasSubject() && ($id = $this->_getParam('id'))) {
      $subject = Engine_Api::_()->getItem('group', $id);
      if( $subject && $subject->getIdentity() ) {
        Engine_Api::_()->core()->setSubject($subject);
      }
    } else if (!Engine_Api::_()->core()->hasSubject() && ($id = $this->_getParam('group_id'))) {
      $subject = Engine_Api::_()->getItem('group', $id);
      if( $subject && $subject->getIdentity() ) {
        Engine_Api::_()->core()->setSubject($subject);
      }
    }
    else if (0 !== ($topic_id = (int)$this->_getParam('topic_id'))) {
        $topic = Engine_Api::_()->getItem('group_topic', $topic_id);
        if ($topic)
            Engine_Api::_()->core()->setSubject($topic);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
  }

  public function viewAction()
  {
    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // Network check
		$networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($subject, 'user_id');
		if(empty($networkPrivacy))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

    // Increment view count
    if( !$subject->getOwner()->isSelf($viewer) )
    {
      $subject->view_count++;
      $subject->save();
    }

    // Get styles
    $table = Engine_Api::_()->getDbTable('styles', 'core');
    $select = $table->select()
      ->where('type = ?', $subject->getType())
      ->where('id = ?', $subject->getIdentity())
      ->limit();

    $row = $table->fetchRow($select);

    if( null !== $row && !empty($row->style) ) {
      $this->view->headStyle()->appendStyle($row->style);
    }
    
    
    $result = array();
    $result["group_content"] = $subject->toarray();
    
    $result["group_content"]['member_count'] = $this->view->translate(array('%s member', '%s members', $subject->member_count), $this->view->locale()->toNumber($subject->member_count));
    
    if( !empty($subject->category_id) ) {
      $category = Engine_Api::_()->getItem('group_category', $subject->category_id);
      $result["group_content"]['category_title'] = $category->title;
			if( !empty($subject->subcat_id) ) {
				$category = Engine_Api::_()->getItem('group_category', $subject->subcat_id);
				$result["group_content"]['subcategory_title'] = $category->title;
			}
			if( !empty($subject->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('group_category', $subject->subsubcat_id);
				$result["group_content"]['subsubcategory_title'] = $category->title;
			}
    }
    
    //Cover Photo
    if($subject->photo_id) {
      $pageCover =	Engine_Api::_()->storage()->get($subject->photo_id, ''); 
      if($pageCover)
        $pageCover = $this->getBaseUrl(false,$pageCover->map());
      $result["group_content"]['cover_photo'] = $pageCover;
    } else {
      $result["group_content"]['cover_photo'] = $this->getBaseUrl().'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';
    }

    
    //Share icon
    $result['group_content']["share"]["name"] = "share";
    $result['group_content']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$subject->getPhotoUrl());
    if($photo)
      $result['group_content']["share"]["imageUrl"] = $photo;
		$result['group_content']["share"]["url"] = $this->getBaseUrl(false,$subject->getHref());
    $result['group_content']["share"]["title"] = $subject->getTitle();
    $result['group_content']["share"]["description"] = strip_tags($subject->getDescription());
    $result['group_content']["share"]['urlParams'] = array(
      "type" => $subject->getType(),
      "id" => $subject->getIdentity()
    );
    
    if(is_null($result['group_content']["share"]["title"]))
      unset($result['group_content']["share"]["title"]);
    
    
    $result['group_content']['profile_photo'] = $subject->getPhotoUrl();
    
    $owner = $subject->getOwner();
    if($owner && $owner->photo_id) {
      $photo = $this->getBaseUrl(false,$owner->getPhotoUrl('thumb.icon'));  
      $result['group_content']['owner_photo']  = $photo;
    } else {
      $result['group_content']['owner_photo'] = $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
    }
    $result['group_content']['owner_title'] = $this->view->translate("by ") . $subject->getOwner()->getTitle();
    
		$result['group_content']['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $subject->getIdentity(), 'resource_type' => 'group'));
		$result['group_content']['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('group.enable.rating', 1);
		$result['group_content']['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('group.ratingicon', 'fas fa-star');
    
    if($viewer->getIdentity() > 0) {
			$result['group_content']['permission']['canEdit'] = $canEdit = $viewPermission = $subject->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['group_content']['permission']['canComment'] =  $subject->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['group_content']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'group', 'create') ? true : false;
			$result['group_content']['permission']['can_delete'] = $canDelete  = $subject->authorization()->isAllowed($viewer,'delete') ? true : false;
      
      $result["group_content"]['gutterMenu'] = $this->gutterMenus($subject);
		}
    $result['group_content']['profile_tabs'] = $this->profiletabs($subject);

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
  
  public function photosAction() {

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('group');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get paginator
    $album = $subject->getSingletonAlbum();
    $paginator = $album->getCollectiblesPaginator();
    $canUpload = $subject->authorization()->isAllowed(null,  'photo');

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    $counterPhoto = 0;
    foreach($paginator as $photos) {

      if($photos) {
        $image = $photos->getPhotoUrl();
        if(!$image) continue;
          //$album_photo[$counterPhoto] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id,'','',true);

        $album_photo[$counterPhoto]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id,'',"");

        $album_photo[$counterPhoto]['photo_id'] = $photos['photo_id'];
        $album_photo[$counterPhoto]['album_id'] = $photos['album_id'];
        $album_photo[$counterPhoto]['group_id'] = $photos['group_id'];
        $album_photo[$counterPhoto]['created_by'] = $this->view->translate("By %s", $photos->getOwner()->getTitle());
        $album_photo[$counterPhoto]['user_id'] = $photos['user_id'];
        $counterPhoto++;
      }
    }

    if($counterPhoto > 0) {
      $result['photos'] = $album_photo;
    }
    
    $canUpload = $subject->authorization()->isAllowed(null, 'photo');
    if($canUpload) {
    $result['options']["label"] = $this->view->translate('Upload Photos');
    $result['options']["actionname"] = 'createalbum';
    }

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    //$results['photos'] = $result;

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No photo created by you yet in this group.'), 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }
  }
  
  
    public function discussionsAction()
    {
        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('group');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        $canTopicCreate = $subject->authorization()->isAllowed(null, 'topic');
       
        // Get paginator
        $table = Engine_Api::_()->getItemTable('group_topic');
        $select = $table->select()
            ->where('group_id = ?', $subject->getIdentity())
            ->order('sticky DESC')
            ->order('modified_date DESC');
        $paginator = Zend_Paginator::factory($select);
        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        
        $canPost = Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'topic_create');
        
        if ($viewer->getIdentity()) {
            if ($canPost) {
							$result['label'] = $this->view->translate('Post New Topic');
							$result['name'] = 'pastnewtopic';
            }
        }
        $counter = 0;
        foreach ($paginator as $topic) {
            $data[$counter] = $topic->toArray();
            $lastpost = $topic->getLastPost();
            $lastposter = $topic->getLastPoster();
            $data[$counter]['reply_count'] = $this->view->locale()->toNumber($topic->post_count - 1);
            $data[$counter]['reply_label'] = $this->view->translate(array('reply', 'replies', $topic->post_count - 1));
            $lastposterimagepath = $this->userImage($lastposter->user_id, 'thumb.profile');
            $data[$counter]['last_post_date'] = $lastpost->creation_date;
            $data[$counter]['last_post']['image'] = $this->getBaseUrl(false, $lastposterimagepath);
            $data[$counter]['last_post']['label'] = $this->view->translate('Last Post by %s', $lastposter->getTitle());
            //if($topic->sticky){
            $data[$counter]['post_title'] = $topic->getTitle();
            //}
            $data[$counter]['post_description'] = ($topic->getDescription());
            $counter++;
        }
        $resultdata['discussions'] = $data;
        $resultdata['post_button'] = $result;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $resultdata), $extraParams));
    }
public function blogviewAction() { 
     $viewer = Engine_Api::_()->user()->getViewer();
     $group = Engine_Api::_()->core()->getSubject('group');

     $params['user_id'] = $viewer->getIdentity();
     $params['owner_id'] = $group->user_id;

     $this->view->paginator = $paginator = $group->getBlogsPaginator($params);
     $this->view->canAdd = $canAdd = $group->authorization()->isAllowed(null,  'blog') && Engine_Api::_()->authorization()->isAllowed('blog', null, 'create');

     $paginator->setItemCountPerPage($items_per_page);
     $paginator->setCurrentPageNumber( $values['page'] );
     $result = $this->blogResult($paginator);

     $result['post_button']['label'] = $this->view->translate('Add Blogs');
     $result['post_button']['name'] = 'pastnewblog';


     $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
     $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
     $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
     $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
     if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist blogs.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
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

      if(!empty($resource['category_id'])) {
        $category = Engine_Api::_()->getItem('blog_category', $resource['category_id']);
        $resource['category_name'] = $category->category_name;
      }

      if($this->_blogEnabled) {
        if($viewer->getIdentity() != 0) {
          $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
          $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
        }
      }
      
      $result['blogs'][$counterLoop] = $resource;
      $images = array();

      if(!engine_count($images))
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      $images['user_images'] = $this->userImage($item->owner_id,"thumb.profile");    
      $images['blog_images'] = $this->getBaseUrl(true, "/application/modules/Blog/externals/images/nophoto_blog_thumb_normal.png");
      
      $result['blogs'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }
   public function videoviewAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
      
    $group = Engine_Api::_()->core()->getSubject('group');

    $this->view->paginator = $paginator = $group->getVideosPaginator();  
    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 12);
    $paginator->setItemCountPerPage($items_count);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $result['videos'] = $this->getVideos($paginator);
     
     $result['post_button']['label'] = $this->view->translate('Add Videos');
     $result['post_button']['name'] = 'pastnewvideo';


    $extraParams = array();
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    $extraParams['pagging']['moduleName'] = "core_video";
    
    if(engine_count($result) <= 0) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'No video uploaded yet.', 'result' => array())); 
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }
  }
  protected function getVideos($paginator) {

    $result = array();
    $counter = 0;
    foreach($paginator as $videos) {

      $video = $videos->toArray();
      $video["description"] = preg_replace('/\s+/', ' ', $video["description"]);
      $video['user_title'] = $videos->getOwner()->getTitle();
      if($this->view->viewer()->getIdentity() != 0){
        try{
          $video['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($videos);
          $video['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($videos);
        }catch(Exception $e){}
      }        
     
    if( $videos->duration >= 3600 ) {
      $duration = gmdate("H:i:s", $videos->duration);
    } else {
      $duration = gmdate("i:s", $videos->duration);
    }
    $video['duration'] = $duration;
    if($this->_permission["watchLater"] && $this->view->viewer()->getIdentity()){
      if(empty($video["watchlater_id"]) && is_null($video["watchlater_id"])){
        $video["watchlater_id"] = 0;
      }
      $video["canWatchlater"] = true;
    }else{
      $video["canWatchlater"] = false;  
    }
    $video['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($videos,'',"");
    if(!engine_count($video['images']))
      $video['images']['main'] = $this->getBaseUrl(false,$videos->getPhotoUrl());

    if ($videos instanceof Sesvideo_Model_Chanelvideo){
     $videoV = Engine_Api::_()->getItem('video',$videos->video_id);
     if ($videoV->type == 3) {
      if (!empty($videoV->file_id)) {
        $storage_file = Engine_Api::_()->getItem('storage_file', $videoV->file_id);
        $video['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
        $video['video_extension'] = $storage_file->extension;  
      }
    }else{
      $embedded = $videoV->getRichContent(true,array(),'',true);

      preg_match('/src="([^"]+)"/', $embedded, $match);
      if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
        $video['iframeURL'] = str_replace('//','https://',$match[1]);
      }else{
        $video['iframeURL'] = $match[1];
      }
    }
  }
  $result[$counter] = array_merge($video,array());
  $counter++;
}
return $result;
}
  public function membersAction() {

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('group');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get params
    $this->view->page = $page = $this->_getParam('page', 1);
    $this->view->search = $search = $this->_getParam('search');
    $this->view->waiting = $waiting = $this->_getParam('waiting', false);

    // Prepare data
    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();

    $viewer = Engine_Api::_()->user()->getViewer();
    
    $members = null;
    $result = array();

    if( $viewer->getIdentity() && ( $group->isOwner($viewer) || $list->has($viewer) ) ) {
      $waitingMembers = Zend_Paginator::factory($group->membership()->getMembersSelect(false));
    }

    // if not showing waiting members, get full members
    $select = $group->membership()->getMembersObjectSelect();
    if( $search ) {
      $select->where('displayname LIKE ?', '%' . $search . '%');
    }
    $fullMembers = Zend_Paginator::factory($select);
  
      if( ($viewer->getIdentity() && ( $group->isOwner($viewer) || $list->has($viewer) )) && ($waiting || ($fullMembers->getTotalItemCount() <= 0 && $search == '')) ) {
      
        $paginator = $waitingMembers;
        $waiting = true;
      } else {
        $paginator = $fullMembers;
        $waiting = false;
      }

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', $page));

    $result = $this->membersResult($paginator,$group,$waiting);

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist members.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }


  function membersResult($paginator,$group,$waiting) {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    $list = $group->getOfficerList();
    
    // if showing waiting members, or no full members
    if( ($viewer->getIdentity() && ( $group->isOwner($viewer) || $list->has($viewer) )) && (!$waiting || ($paginator->getTotalItemCount() <= 0 && $search == '')) ) {
      $result['options']["label"] = 'See Waiting';
      $result['options']["name"] = 'waiting';
      $result['options']["value"] = '1';
    }
    
    if ($paginator->getTotalItemCount() > 0 && ($viewer->getIdentity() && ($group->isOwner($viewer))) && $waiting) {
        $result['options']["label"] = $this->view->translate('View all approved members');
        $result['options']["name"] = 'waiting';
        $result['options']["value"] = '0';
    }
    
    
    foreach($paginator as $item) {
      if (!empty($item->resource_id)) {
          $memberInfo = $item;
          $item = Engine_Api::_()->getItem('user', $memberInfo->user_id);
      } else {
          $memberInfo = $group->membership()->getMemberInfo($item);
      }
      //$resource = $item->toArray();
      //$memberInfo = $group->membership()->getMemberInfo($item); 
      $resource['displayname'] = $item->getTitle() . (($group->getOwner()->getIdentity() == $item->getIdentity()) ? " (Owner)" : " (Member)");
      $resource['user_id'] = $item->user_id;


      $result['members'][$counterLoop] = $resource;

      $owner = $item->getOwner();
      if($owner && $owner->photo_id) {
        $photo = $this->getBaseUrl(false,$owner->getPhotoUrl('thumb.icon'));
        $result['members'][$counterLoop]['owner_photo']  = $photo;
      } else {
        $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
      }
      
      if ($group->isOwner($viewer) && !$group->isOwner($item)) {
          $optionCounter = 0;
          if (!$group->isOwner($item) && $memberInfo->active == true) {
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'removemember';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Remove Member');
              $optionCounter++;
          }

          if ($memberInfo->active == false && $memberInfo->resource_approved == false) {
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'approverequest';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Approve Request');
              $optionCounter++;
              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'rejectrequest';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Reject Request');
              $optionCounter++;
          }
          if ($memberInfo->active == false && $memberInfo->resource_approved == true) {

              $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'cancelinvite';
              $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Cancel Invite');
              $optionCounter++;
          }
      }
      
      
      
      $counterLoop++;
    }
    return $result;
  }
  
    public function creatediscussionAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('group')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $group = $group = Engine_Api::_()->core()->getSubject();
        $viewer = $viewer = Engine_Api::_()->user()->getViewer();
        // Make form
        $form = $form = new Group_Form_Topic_Create();

        $form->getElement('body')->setLabel('Description');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'group', 'formTitle' => $form->getTitle(), 'formDescription' => $form->getDescription()));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['group_id'] = $group->getIdentity();
        $topicTable = Engine_Api::_()->getDbTable('topics', 'group');
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'group');
        $postTable = Engine_Api::_()->getDbTable('posts', 'group');
        $db = $group->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Create topic
            $topic = $topicTable->createRow();
            $topic->setFromArray($values);
            $topic->save();
            // Create post
            $values['topic_id'] = $topic->topic_id;
            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();
            // Create topic watch
            $topicWatchesTable->insert(array(
                'resource_id' => $group->getIdentity(),
                'topic_id' => $topic->getIdentity(),
                'user_id' => $viewer->getIdentity(),
                'watch' => (bool)$values['watch'],
            ));
            // Add activity
            $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $topic, 'group_topic_create');
            if ($action) {
                $action->attach($topic);
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succussfully Topic created.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
  
    public function discussionviewAction(){
        if (!$this->_helper->requireSubject('group_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $group = $group = $topic->getParentGroup();
        $canEdit = $canEdit = $group->authorization()->isAllowed($viewer, 'edit');
        $canPost = $group->authorization()->isAllowed(null,  'topic_create');
        
        $canPostCreate = $topic->canPostCreate(Engine_Api::_()->user()->getViewer());
        
        $canAdminEdit = Engine_Api::_()->authorization()->isAllowed($group, null, 'edit');
        if (!$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id) {
            $topic->view_count = new Zend_Db_Expr('view_count + 1');
            $topic->save();
        }
        $isWatching = null;
        if ($viewer->getIdentity()) {
            $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'group');
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $group->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0);
            if (false === $isWatching) {
                $isWatching = null;
            } else {
                $isWatching = (bool)$isWatching;
            }
        }
        // @todo implement scan to post
        $post_id = (int)$this->_getParam('post');
        $table = Engine_Api::_()->getDbTable('posts', 'group');
        $select = $table->select()
            ->where('group_id = ?', $group->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date ASC');
        $paginator = Zend_Paginator::factory($select);
        $topicdata['label'] = $topic->getTitle();
        $headeroptionscounter = 0;
        if ($canPostCreate) {
            $data[$headeroptionscounter]['name'] = 'postreply';
            $data[$headeroptionscounter]['label'] = $this->view->translate('Post Reply');
            $headeroptionscounter++;
            //For v2 version core discussions
            // if ($viewer->getIdentity()) {
            //     if (!$isWatching) {
            //         $data[$headeroptionscounter]['name'] = 'watchtopic';
            //         $data[$headeroptionscounter]['label'] = $this->view->translate('Watch Topic');
            //         $headeroptionscounter++;
            //     } else {
            //         $data[$headeroptionscounter]['name'] = 'stopwatching';
            //         $data[$headeroptionscounter]['label'] = $this->view->translate('Stop Watching Topic');
            //         $headeroptionscounter++;
            //     }
            // }
        }

        $topicdata['value'] = $data;
        $counter = 0;
        foreach ($paginator as $post) {
            $posts[$counter] = $post->toArray();
            $user = $this->view->item('user', $post->user_id);
            $isOwner = false;
            $isMember = false;
            if ($group->isOwner($user)) {
                $isOwner = true;
                $isMember = true;
            } else if ($group->membership()->isMember($user)) {
                $isMember = true;
            }
            $posts[$counter]['post_id'] = $post->getIdentity();
            $posts[$counter]['title'] = $user->getTitle();
            $posts[$counter]['user_photo'] = $this->userImage($user->user_id,"thumb.profile");

            if ($isOwner) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Host');
            } else if ($isMember) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Member');
            }
            $optioncounter = 0;
            
            $canPostEdit = $topic->canPostEdit(Engine_Api::_()->user()->getViewer());
            $canPostDelete = $topic->canPostDelete(Engine_Api::_()->user()->getViewer());
            
            if ($post->user_id == $viewer->getIdentity() || $group->getOwner()->getIdentity() == $viewer->getIdentity() || $canAdminEdit) {
								if($canPostEdit) {
									$posts[$counter]['options'][$optioncounter]['name'] = 'edit';
									$posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Edit');
									$optioncounter++;
                }
                if($canPostDelete) { 
									$posts[$counter]['options'][$optioncounter]['name'] = 'delete';
									$posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Delete');
									$optioncounter++;
                }
            }

            $posts[$counter]['creation_date'] = $group->creation_date;

            $counter++;
        }
        $result['posts'] = $posts;
        $result['topic'] = $topicdata;
        // Skip to page of specified post
        if (0 !== ($post_id = (int)$this->_getParam('post_id')) &&
            null !== ($post = Engine_Api::_()->getItem('group_post', $post_id))) {
            $icpp = $paginator->getItemCountPerPage();
            $page = ceil(($post->getPostIndex() + 1) / $icpp);
            $paginator->setCurrentPageNumber($page);
        } // Use specified page
        else if (0 !== ($page = (int)$this->_getParam('page'))) {
            $paginator->setCurrentPageNumber($this->_getParam('page'));
        }

        if ($canPost && !$topic->closed) {
            $form = new Group_Form_Post_Create();

            if ($this->_getParam('getForm')) {
                $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
                $this->generateFormFields($formFields, array('resources_type' => 'group'));
            }
            $form->populate(array(
                'topic_id' => $topic->getIdentity(),
                'ref' => $topic->getHref(),
                'watch' => (false === $isWatching ? '0' : '1'),
            ));
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    
    public function closeAction()
    {
        $topic = Engine_Api::_()->core()->getSubject();
        $group = Engine_Api::_()->getItem('group', $topic->group_id);
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->closed = (null === $this->_getParam('closed') ? !$topic->closed : (bool)$this->_getParam('closed'));
            $topic->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'Action performed successfully.')));
    }
    public function commentonpostAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('group_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $group = $group = $topic->getParentGroup();
        if ($topic->closed) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This has been closed for posting.'), 'result' => array()));
            $status = false;
        }
        // Make form
        $form = $form = new Group_Form_Post_Create();
        if($form->body)
          $form->getElement('body')->setLabel('Body');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'group', 'formTitle' => 
            "Post Reply"));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $viewer = Engine_Api::_()->user()->getViewer();
        $topicOwner = $topic->getOwner();
        $isOwnTopic = $viewer->isSelf($topicOwner);
        $postTable = Engine_Api::_()->getDbTable('posts', 'group');
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'group');
        $userTable = Engine_Api::_()->getItemTable('user');
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
        $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['group_id'] = $group->getIdentity();
        $values['topic_id'] = $topic->getIdentity();
        $watch = (bool)$values['watch'];
        $isWatching = $topicWatchesTable
            ->select()
            ->from($topicWatchesTable->info('name'), 'watch')
            ->where('resource_id = ?', $group->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        $db = $group->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Create post
            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();
            // Watch
            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $group->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $group->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }
            // Activity
            $action = $activityApi->addActivity($viewer, $topic, 'group_topic_reply');
            if ($action) {
                $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
            }
            // Notifications
            $notifyUserIds = $topicWatchesTable->select()
                ->from($topicWatchesTable->info('name'), 'user_id')
                ->where('resource_id = ?', $group->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('watch = ?', 1)
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
            foreach ($userTable->find($notifyUserIds) as $notifyUser) {
                // Don't notify self
                if ($notifyUser->isSelf($viewer)) {
                    continue;
                }
                if ($notifyUser->isSelf($topicOwner)) {
                    $type = 'group_discussion_response';
                } else {
                    $type = 'group_discussion_reply';
                }
                $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
                    'message' => $this->view->BBCode($post->body),
                ));
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have succussfully commented on this topic.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function stickyAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $group = Engine_Api::_()->getItem('group', $topic->group_id);
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->sticky = (null === $this->_getParam('sticky') ? !$topic->sticky : (bool)$this->_getParam('sticky'));
            $topic->save();
            $db->commit();
            if($topic->sticky){
              $message = $this->view->translate('Succussfully maked Sticky to this Topic.');
            } else {
              $message = $this->view->translate('Succussfully removed Sticky to this Topic.');
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' =>$message)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

       public function renametopicAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $group = Engine_Api::_()->getItem('group', $topic->topic_id);
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = $form = new Group_Form_Topic_Rename();
        $form->populate($topic->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'group'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $title = $form->getValue('title');
            $topic = Engine_Api::_()->core()->getSubject();
            $topic->title = htmlspecialchars($title);
            $topic->save();

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully topic renamed.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
    }
   public function deletetopicAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $group = Engine_Api::_()->getItem('group', $topic->topic_id);
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = $form = new Group_Form_Topic_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'group'));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $topic = Engine_Api::_()->core()->getSubject();
            $group = $topic->getParent('group');
            $topic->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully deleted.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }
    public function editpostAction(){

        $postid = $this->_getParam('post_id', $this->_getParam('topic_id' . null));
        
        if (!$postid)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'parameter_missing', 'result' => array()));
        $post = Engine_Api::_()->getItem('group_post', $postid);
        $group = $post->getParent('group');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$group->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid()) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
            }
        }
        $form = new Group_Form_Post_Edit();
        $form->body->setValue(html_entity_decode($post->body));
        $form->populate($post->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
						$this->generateFormFields($formFields,array('formTitle' => $form->getTitle(), 'formDescription' => $form->getDescription()));
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        // Process
        $table = $post->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $post->setFromArray($form->getValues());
            $post->modified_date = date('Y-m-d H:i:s');
            $settings = Engine_Api::_()->getApi('settings', 'core');
            $allowHtml = (bool)$settings->getSetting('group_html', 0);
            $allowBbcode = (bool)$settings->getSetting('group_bbcode', 0);
            if (!$allowBbcode && !$allowHtml) {
                $post->body = htmlspecialchars($post->body, ENT_NOQUOTES, 'UTF-8');
            }
            $post->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Post edited.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function deletepostAction(){
        $postid = $this->_getParam('post_id', null);
        if (!$postid)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        $post = Engine_Api::_()->getItem('group_post', $postid);
        $group = $post->getParent('group');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$group->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'edit')->isValid()) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
            }
        }
        // Process
        $table = $post->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $topic_id = $post->topic_id;
            $post->delete();

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Succuessfully Post deleted.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        // Try to get topic
        $topic = Engine_Api::_()->getItem('group_topic', $topic_id);
        $href = (null === $topic ? $group->getHref() : $topic->getHref());
        return $this->_forward('success', 'utility', 'core', array(
            'closeSmoothbox' => true,
            'parentRedirect' => $href,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
        ));
    }
    public function watchAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $group = Engine_Api::_()->getItem('group', $topic->group_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'view')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        $watch = $this->_getParam('watch', true);
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'group');
        $db = $topicWatchesTable->getAdapter();
        $db->beginTransaction();
        try {
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $group->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0);

            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $group->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $group->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Watched.'))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }

    public function lightboxAction(){
        $photo = Engine_Api::_()->getItem('group_photo', $this->_getParam('photo_id'));
        $group_id = $this->_getparam('group_id', null);
        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        if ($album_id) {
            $album = Engine_Api::_()->getItem('group_album', $album_id);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_request', 'result' => array()));
        }
        if (!$this->_getparam('group_id', null)) {
            $group_id = $album->group_id;
        }
        $group = Engine_Api::_()->getItem('group', $group_id);
        $photo_id = $photo->getIdentity();
        if (!$this->_helper->requireAuth()->setAuthParams($group, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $albumData = array();
        if ($viewer->getIdentity() > 0) {
            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;
            $canEdit = $group->authorization()->isAllowed($viewer, 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }
            $can_delete = $group->authorization()->isAllowed($viewer, 'delete');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "delete";
                $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
                $counterMenu++;
            }
            $menu[$counterMenu]["name"] = "report";
            $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");
            $counterMenu++;
            $menu[$counterMenu]["name"] = "makeprofilephoto";
            $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
            $albumData['menus'] = $menu;
            $canComment = $group->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $albumData['can_comment'] = $canComment;
            $sharemenu = array();
            if ($viewer->getIdentity() > 0) {
                $sharemenu[0]["name"] = "siteshare";
                $sharemenu[0]["label"] = $this->view->translate("Share");
            }
            $sharemenu[1]["name"] = "share";
            $sharemenu[1]["label"] = $this->view->translate("Share Outside");
            $albumData['share'] = $sharemenu;
        }
        $condition = $this->_getParam('condition');
        if (!$condition) {
            $next = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, ">="), true);
            $previous = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, "<"), true);
            $array_merge = array_merge($previous, $next);
            if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
                $recArray = array();
                $reactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getPaginator();
                $counterReaction = 0;
                foreach ($reactions as $reac) {
                    if (!$reac->enabled)
                        continue;
                    $albumData['reaction_plugin'][$counterReaction]['reaction_id'] = $reac['reaction_id'];
                    $albumData['reaction_plugin'][$counterReaction]['title'] = $this->view->translate($reac['title']);
                    $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                    $albumData['reaction_plugin'][$counterReaction]['image'] = $icon['main'];
                    $counterReaction++;
                }
            }
        } else {
            $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, $condition), true);
        }
        $albumData['module_name'] = 'group';
        $albumData['photos'] = $array_merge;
        if (engine_count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }
        public function nextPreviousImage($photo_id, $album_id, $condition = "<="){
        $photoTable = Engine_Api::_()->getItemTable('group_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('group_id !=?', 0)
            ->where('photo_id ' . $condition . ' ?', $photo_id)
            ->order('photo_id ASC')
            ->limit(20);
        return $photoTable->fetchAll($select);
    }
    
      public function getPhotos($paginator, $updateViewCount = false){
        $result = array();
        $counter = 0;
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        foreach ($paginator as $photos) {
            $photo = $photos->toArray();
            $photos->view_count = new Zend_Db_Expr('view_count + 1');
            $photos->save();
            $photo['user_title'] = $photos->getOwner()->getTitle();
            if ($viewer_id != 0) {
                $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $photo['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photos);
            }
            $attachmentItem = $photos;
            if ($attachmentItem->getPhotoUrl())
                $photo["shareData"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
            $photo["shareData"]["title"] = $attachmentItem->getTitle();
            $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());
            $photo["shareData"]['urlParams'] = array(
                "type" => $photos->getType(),
                "id" => $photos->getIdentity()
            );
            if (is_null($photo["shareData"]["title"]))
                unset($photo["shareData"]["title"]);
            $owner = $photos->getOwner();
            $photo['owner']['title'] = $owner->getTitle();
            $photo['owner']['id'] = $owner->getIdentity();
            $photo["owner"]['href'] = $owner->getHref();
            if ($attachmentItem->getPhotoUrl())
                $album_photo['images']['main'] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
            $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $photo['module_name'] = 'album';
            if ($photo['can_comment']) {
                if ($viewer_id) {
                    $itemTable = Engine_Api::_()->getItemTable($photos->getType(), $photos->getIdentity());
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
                $table = Engine_Api::_()->getDbTable('likes', 'core');
                $recTable = Engine_Api::_()->getDbTable('reactions', 'comment')->info('name');
                $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $photos->getIdentity())->group('type')->setIntegrityCheck(false);
                $select->where('resource_type =?', $photos->getType());
                $select->joinLeft($recTable, $recTable . '.reaction_id =' . $table->info("name") . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                $resultData = $table->fetchAll($select);
                $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $reactionData = array();
                $reactionCounter = 0;
                if(is_countable($resultData) && engine_count($resultData)) {
                    foreach ($resultData as $type) {
                        $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
                        $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
                        $reactionCounter++;
                    }
                    $photo['reactionData'] = $reactionData;
                }
                if ($photo['is_like']) {
                    $photo['is_like'] = true;
                    $like = true;
                    $type = $photo['reaction_type'];
                    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) { 
                    $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
                    $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
                    }
                } else {
                    $photo['is_like'] = false;
                    $like = false;
                    $type = '';
                    $imageLike = '';
                    $text = 'Like';
                }
                if (empty($like)) {
                    $photo["like"]["name"] = "like";
                } else {
                    $photo["like"]["name"] = "unlike";
                }
                // Get tags
                $tags = array();
                foreach ($photos->tags()->getTagMaps() as $tagmap) {

                    $tag = $tagmap->getTag();
                    if (!isset($tag->text))
                        continue;
                    $tags[] = array_merge($tagmap->toArray(), array(
                        'id' => $tagmap->getIdentity(),
                        'text' => $tagmap->getTitle(),
                        'href' => $tagmap->getHref(),
                        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                    ));

                }
                if ($tags)
                    $photo["tags"] = $tags;
                if ($type)
                    $photo["like"]["type"] = $type;
                if ($imageLike)
                    $photo["like"]["image"] = $imageLike;
                $photo["like"]["label"] = $this->view->translate($text);
                $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(), '', $photos->likes()->getLike($viewer), $viewer);
            }
            if (!engine_count($album_photo['images']))
                $album_photo['images']['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl());
            $result[$counter] = array_merge($photo, $album_photo);
            $counter++;
        }
        return $result;
    }
  
  public function infoAction() {

    // Get subject
    if (Engine_Api::_()->core()->hasSubject('group'))
      $subject = Engine_Api::_()->core()->getSubject('group');
  
    $result = array();
    $result = $subject->toArray();
    
    $result['created_by'] = $subject->getOwner()->getTitle();
    $result['creation_date'] = $this->view->translate( gmdate('M d, Y', strtotime($subject->creation_date))) ;
    $result['modified_date'] = $this->view->translate( gmdate('M d, Y', strtotime($subject->modified_date))) ;
    
    
    $result['view_count'] = $this->view->translate(array('%s total view', '%s total views', $subject->view_count), $this->view->locale()->toNumber($subject->view_count));
    $result['member_count'] = $this->view->translate(array('%s total member', '%s total members', $subject->member_count), $this->view->locale()->toNumber($subject->member_count));
    
    if($subject->category_id) {
      $category = Engine_Api::_()->getItem('group_category', $subject->category_id);
      $result['category_name'] = $category->title;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
  public function inviteAction(){
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject('group')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'user_not_autheticate', 'result' => array()));
    // @todo auth
    // Prepare data
    $viewer = Engine_Api::_()->user()->getViewer();
    $group = Engine_Api::_()->core()->getSubject();
    // Prepare friends
    $friendsTable = Engine_Api::_()->getDbTable('membership', 'user');
    $friendsIds = $friendsTable->select()
        ->from($friendsTable, 'user_id')
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('active = ?', true)
        ->limit(100)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
    if (!empty($friendsIds)) {
        $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
    } else {
        $friends = array();
    }
    // Prepare form
    $form = new Group_Form_Invite();
    $count = 0;
    foreach ($friends as $friend) {
        if ($group->membership()->isMember($friend, null)) {
            continue;
        }
        $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
        $count++;
    }
    if ($count == 1)
        $form->removeElement('all');
    else if (!$count)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have no friends you can invite.'))));
    if ($this->_getParam('getForm')) {
        if ($form->getElement('all'))
            $form->getElement('all')->setName('group_choose_all');

        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
    }
    // Not posting
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }
    // Process
    $table = $group->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
        $usersIds = $form->getValue('users');
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
        foreach ($friends as $friend) {
            if (!engine_in_array($friend->getIdentity(), $usersIds)) {
                continue;
            }
            $group->membership()->addMember($friend)->setResourceApproved($friend);
            $notifyApi->addNotification($friend, $viewer, $group, 'group_invite');
        }
        if ($count > 1) {
            $message = $this->view->translate('All members invited.');
        } else {
            $message = $this->view->translate('member invited.');
        }
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function joinAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Make form
    $form = new Group_Form_Member_Join();
    // If member is already part of the group
    if( $subject->membership()->isMember($viewer) ) {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        // Set the request as handled
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
          $viewer, $subject, 'group_invite');
        if( $notification )
        {
          $notification->mitigated = true;
          $notification->save();
        }
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        //throw $e;
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "You are already a member of this group.", "is_hidden" => 0)));
    }

    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array());
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->addMember($viewer)->setUserApproved($viewer);
      // Set the request as handled
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'group_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }
      // Add activity
      $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $subject, 'group_join');
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_("You are now a member of this group."))));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  
  public function promoteAction()
  {
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();
    $viewer = Engine_Api::_()->user()->getViewer();

    if( !$group->membership()->isMember($user) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Cannot add a non-member as an officer', 'result' => array()));
    }
    $form = new Group_Form_Member_Promote();
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }
    $table = $list->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $list->add($user);
      // Add notification
      $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      $notifyApi->addNotification($user, $viewer, $group, 'group_promote');

      // Add activity
      $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
      $action = $activityApi->addActivity($user, $group, 'group_promote');
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "Member Promoted")));
  }

  public function demoteAction()
  {
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $group = Engine_Api::_()->core()->getSubject();
    $list = $group->getOfficerList();

    if( !$group->membership()->isMember($user) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Cannot remove a non-member as an officer', 'result' => array()));
    }

    $form = new Group_Form_Member_Demote();
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }
    $table = $list->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $list->remove($user);
      $db->commit();
    }catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "Member Demoted")));
  }

  public function leaveAction() {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireSubject()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if( $subject->isOwner($viewer) )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    $form = new Group_Form_Member_Leave();

    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array());
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }    
    $list = $subject->getOfficerList();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try
    {
      // remove from officer list
      $list->remove($viewer);
      $subject->membership()->removeMember($viewer);
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_("You have successfully left this group."))));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }

  public function removeAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $subject = Engine_Api::_()->core()->getSubject();
    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $group = Engine_Api::_()->core()->getSubject();

    if( !$group->membership()->isMember($user) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Cannot remove a non-member'))));
    }
    // Make form
    $form = new Group_Form_Member_Remove();

    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array());
    }
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (is_countable($validateFields) && engine_count($validateFields))
            $this->validateFormFields($validateFields);
    }
    $db = $group->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try
    {
      // Remove membership
      $group->membership()->removeMember($user);
      // Remove the notification?
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $group->getOwner(), $group, 'group_approve');
      if( $notification ) {
        $notification->delete();
      }
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Group member removed.'))));
  }
  
  public function requestAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    //$this->view->form = $form = new Group_Form_Member_Request();
    // Process form
    if( 1 )
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject();
      $owner = $subject->getOwner();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->addMember($viewer)->setUserApproved($viewer);
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'group_approve');
        $db->commit();
        $gutterMenu = $this->gutterMenus($subject);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "Group membership request sent", 'gutterMenu' => $gutterMenu)));
      }
      catch( Exception $e )
      {
        $db->rollBack();
        //throw $e;
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
    }
  }
  
  public function cancelAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    //$this->view->form = $form = new Group_Form_Member_Cancel();

    // Process form
    if( 1 ) {

      $user_id = $this->_getParam('user_id');
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject();
      if( !$subject->authorization()->isAllowed($viewer, 'invite') &&
          $user_id != $viewer->getIdentity() &&
          $user_id ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      }

      if( $user_id ) {
        $user = Engine_Api::_()->getItem('user', $user_id);
        if( !$user ) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        }
      } else {
        $user = $viewer;
      }

      $subject = Engine_Api::_()->core()->getSubject('group');
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();
      try
      {
        $subject->membership()->removeMember($user);

        // Remove the notification?
        $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
          $subject->getOwner(), $subject, 'group_approve');
        if( $notification ) {
          $notification->delete();
        }

        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        //throw $e;
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
      }
      $gutterMenu = $this->gutterMenus($subject);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Your invite request has been cancelled.'), 'gutterMenu' => $gutterMenu)));
    }
  }


      public function approveAction()
    {

        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('group')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        // Get user
        if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('user does not exist.'), 'result' => array()));
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $subject->membership()->setResourceApproved($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'group_accepted');
            $db->commit();
            $gutterMenu = $this->gutterMenus($subject);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Group request approved'),'gutterMenu' => $gutterMenu)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }

    }

  
  public function acceptAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireSubject('group')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    //$this->view->form = $form = new Group_Form_Member_Accept();

    // Process form
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Process 
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->setUserApproved($viewer);

      // Set the request as handled
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'group_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }

      // Add activity
      $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $subject, 'group_join');

      $db->commit();
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "You have accepted the invite to the group.", "is_hidden" => 0)));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  

  public function rejectAction()
  {

    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireSubject('group')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Get user
    if( 0 === ($user_id = (int) $this->_getParam('user_id')) ||
        null === ($user = Engine_Api::_()->getItem('user', $user_id)) )
    {
      $user = Engine_Api::_()->user()->getViewer();
      //return $this->_helper->requireSubject->forward();
    }

    // Make form
    //$this->view->form = $form = new Group_Form_Member_Reject();

    // Process form
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Invalid Method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Process
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->removeMember($user);

      // Set the request as handled
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $user, $subject, 'group_invite');
      if( $notification )
      {
        $notification->mitigated = true;
        $notification->save();
      }

      $db->commit();
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => "You have ignored the invite to the group", "is_hidden" => 0)));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

  }
    
  protected function gutterMenus($subject) {
  
    $viewer = Engine_Api::_()->user()->getViewer();

    $row = $subject->membership()->getRow($viewer);

    // Not yet associated at all
    if( null === $row )
    {
      if( $subject->membership()->isResourceApprovalRequired() ) {
        $menu[] =  array(
          'label' => $this->view->translate('Request Membership'),
          'class' => 'smoothbox icon_group_join',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'group_id' => $subject->getIdentity(),
          ),
        );
      } else {
        $menu[] =  array(
          'label' => $this->view->translate('Join Group'),
          'class' => 'smoothbox icon_group_join',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'group_id' => $subject->getIdentity()
          ),
        );
      }
    }

    // Full member
    // @todo consider owner
    else if( $row->active )
    {
      if( !$subject->isOwner($viewer) ) {
        $menu[] =  array(
          'label' => $this->view->translate('Leave Group'),
          'class' => 'smoothbox icon_group_leave',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'group_id' => $subject->getIdentity()
          ),
        );
      }
    }

    else if( !$row->resource_approved && $row->user_approved )
    {
      $menu[] =  array(
        'label' => $this->view->translate('Cancel Membership Request'),
        'class' => 'smoothbox icon_group_cancel',
        'route' => 'group_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'group_id' => $subject->getIdentity()
        ),
      );
    }

    else if( !$row->user_approved && $row->resource_approved )
    {
      $menu[] = array(
          'label' => $this->view->translate('Accept Membership Request'),
          'class' => 'smoothbox icon_group_accept',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'group_id' => $subject->getIdentity()
          ),
      );

      $menu[] =  array(
          'label' => $this->view->translate('Ignore Membership Request'),
          'class' => 'smoothbox icon_group_reject',
          'route' => 'group_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'group_id' => $subject->getIdentity()
          ),
      );
    }

    $canDelete = Engine_Api::_()->authorization()->isAllowed($subject, null, 'delete');
    if( $canDelete ) {
      $menu[] = array(
        'label' => $this->view->translate('Delete Group'),
        'class' => 'smoothbox icon_group_delete',
        'route' => 'group_specific',
        'params' => array(
          'action' => 'delete',
          'group_id' => $subject->getIdentity()
        ),
      );
    }
    
    if( !$viewer->getIdentity() ||
        $subject->isOwner($viewer) ) {
      //return false;
    } else {
      $menu[] = array(
        'label' => $this->view->translate('Report'),
        'class' => 'smoothbox icon_report',
        'route' => 'default',
        'params' => array(
          'module' => 'core',
          'controller' => 'report',
          'action' => 'create',
          'subject' => $subject->getGuid(),
          'format' => 'smoothbox',
        ),
      );
    }
    
    if( $subject->authorization()->isAllowed($viewer, 'invite') ) {
      $menu[] = array(
        'label' => $this->view->translate('Invite Members'),
        'class' => 'smoothbox icon_invite',
        'route' => 'group_extended',
        'params' => array(
          //'module' => 'group',
          'controller' => 'member',
          'action' => 'invite',
          'group_id' => $subject->getIdentity(),
          'format' => 'smoothbox',
        ),
      );
    }
   
    if( $viewer->getIdentity() && $subject->isOwner($viewer) ) {
    
      $menu[] = array(
        'label' => $this->view->translate('Message Members'),
				'class' => 'icon_message',
				'route' => 'messages_general',
				'params' => array(
					'action' => 'compose',
					'to' => $subject->getIdentity(),
					'multi' => 'group',
					'title' => $subject->getTitle(),
				),
      );
    }

    if($subject->authorization()->isAllowed($viewer, 'edit') )
    {
      $menu[] = array(
        'label' => $this->view->translate('Edit Group Details'),
        'class' => 'icon_group_edit',
        'route' => 'group_specific',
        'params' => array(
          'controller' => 'group',
          'action' => 'edit',
          'group_id' => $subject->getIdentity(),
          'ref' => 'profile'
        )
      );
    }
    return $menu;
  }
  
  
  protected function profiletabs($subject) {
  
    $tabs = array();
  
    $tabs[] = array(
      'label' => $this->view->translate('Updates'),
      'name' => 'updates'
    );
    $tabs[] = array(
      'label' => $this->view->translate('Info'),
      'name' => 'info'
    );
    $tabs[] = array(
      'label' => $this->view->translate('Members'),
      'name' => 'members'
    );
    $tabs[] = array(
      'label' => $this->view->translate('Photos'),
      'name' => 'photos'
    );
    $tabs[] = array(
      'label' => $this->view->translate('Discussions'),
      'name' => 'discussions'
    );
//     if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('event')) {
// 			$tabs[] = array(
// 				'label' => $this->view->translate('Events'),
// 				'name' => 'events'
// 			);
//     }
//     if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('blog')) {
// 			$tabs[] = array(
// 				'label' => $this->view->translate('Blogs'),
// 				'name' => 'blogs'
// 			);
//     }
//     if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poll')) {
// 			$tabs[] = array(
// 				'label' => $this->view->translate('Polls'),
// 				'name' => 'polls'
// 			);
//     }
//     if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')) {
// 			$tabs[] = array(
// 				'label' => $this->view->translate('Videos'),
// 				'name' => 'videos'
// 			);
// 		}
    return $tabs;
  }
}
