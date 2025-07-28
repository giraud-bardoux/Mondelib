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

class Event_ProfileController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {
    if (!$this->_helper->requireAuth()->setAuthParams('event', null, 'view')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
      
    // @todo this may not work with some of the content stuff in here, double-check
    $subject = null;

    //$id = $this->_getParam('id', null);
    if (!Engine_Api::_()->core()->hasSubject('event') && ($id = $this->_getParam('id'))) {
      $subject = Engine_Api::_()->getItem('event', $id);
      if( $subject && $subject->getIdentity() ) {
        Engine_Api::_()->core()->setSubject($subject);
      }
    } else if (!Engine_Api::_()->core()->hasSubject('event') && ($id = $this->_getParam('event_id'))) {
      $subject = Engine_Api::_()->getItem('event', $id);
      if( $subject && $subject->getIdentity() ) {
        Engine_Api::_()->core()->setSubject($subject);
      }
    }
    else if (0 !== ($topic_id = (int)$this->_getParam('topic_id'))) {
      $topic = Engine_Api::_()->getItem('event_topic', $topic_id);
      if ($topic)
        Engine_Api::_()->core()->setSubject($topic);
      else
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
    }
  }

  public function indexAction() {

    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
			
    if( !$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Check block
    if( $viewer->isBlockedBy($subject) )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Increment view count
    if( !$subject->getOwner()->isSelf($viewer) )
    {
      $subject->view_count++;
      $subject->save();
    }

    if($subject->parent_type == 'group' && $subject->parent_id) {
      $group = Engine_Api::_()->getItem($subject->parent_type, $subject->parent_id);
      
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
    
    if( !$subject || !$subject->getIdentity() || ((!$subject->approved) && !$subject->isOwner($viewer)) ) {
      if(!empty($viewer->getIdentity()) && $viewer->isAdmin()) {
      } else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Network check
    $networkPrivacy = Engine_Api::_()->network()->getViewerNetworkPrivacy($subject, 'user_id');
    if(empty($networkPrivacy))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));


    $result = array();
    $result["event_content"] = $subject->toarray();
    
    $result['is_rated'] = Engine_Api::_()->getDbTable('ratings', 'core')->checkRated(array('resource_id' => $subject->getIdentity(), 'resource_type' => 'event'));
		$result['enable_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('event.enable.rating', 1);
		$result['ratingicon'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('event.ratingicon', 'fas fa-star');
		
    $result["event_content"]['member_count'] = $this->view->translate(array('%s member', '%s members', $subject->member_count), $this->view->locale()->toNumber($subject->member_count));
    
    if( !empty($subject->category_id) ) {
      $category = Engine_Api::_()->getItem('event_category', $subject->category_id);
      $result["event_content"]['category_title'] = $category->title;
			if( !empty($subject->subcat_id) ) {
				$category = Engine_Api::_()->getItem('event_category', $subject->subcat_id);
				$result["event_content"]['subcategory_title'] = $category->title;
			}
			if( !empty($subject->subsubcat_id) ) {
				$category = Engine_Api::_()->getItem('event_category', $subject->subsubcat_id);
				$result["event_content"]['subsubcategory_title'] = $category->title;
			}
    }

    //Cover Photo
    if($subject->photo_id) {
      $pageCover =  Engine_Api::_()->storage()->get($subject->photo_id, '');
      if($pageCover)
        $pageCover = $this->getBaseUrl(false,$pageCover->map());
      $result["event_content"]['cover_photo'] = $pageCover;
    } else {
      $result["event_content"]['cover_photo'] = $this->getBaseUrl().'application/modules/Event/externals/images/nophoto_event_thumb_profile.png';
    }


    //Share icon
    $result['event_content']["share"]["name"] = "share";
    $result['event_content']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$subject->getPhotoUrl());
    if($photo)
      $result['event_content']["share"]["imageUrl"] = $photo;
    $result['event_content']["share"]["url"] = $this->getBaseUrl(false,$subject->getHref());
    $result['event_content']["share"]["title"] = $subject->getTitle();
    $result['event_content']["share"]["description"] = strip_tags($subject->getDescription());
    $result['event_content']["share"]['urlParams'] = array(
      "type" => $subject->getType(),
      "id" => $subject->getIdentity()
    );

    if(is_null($result['event_content']["share"]["title"]))
      unset($result['event_content']["share"]["title"]);


    $result['event_content']['profile_photo'] = $subject->getPhotoUrl();

    $owner = $subject->getOwner();
    if($owner && $owner->photo_id) {
      $photo = $this->getBaseUrl(false,$owner->getPhotoUrl('thumb.icon'));
      $result['event_content']['owner_photo']  = $photo;
    } else {
      $result['event_content']['owner_photo'] = $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
    }
    $result['event_content']['owner_title'] = $this->view->translate("by ") . $subject->getOwner()->getTitle();

    if($viewer->getIdentity() > 0) {
      $result['event_content']['permission']['canEdit'] = $canEdit = $viewPermission = $subject->authorization()->isAllowed($viewer, 'edit') ? true : false;
      $result['event_content']['permission']['canComment'] =  $subject->authorization()->isAllowed($viewer, 'comment') ? true : false;
      $result['event_content']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'group', 'create') ? true : false;
      $result['event_content']['permission']['can_delete'] = $canDelete  = $subject->authorization()->isAllowed($viewer,'delete') ? true : false;

      $result["event_content"]['gutterMenu'] = $this->gutterMenus($subject);
    }
    $result['event_content']['profile_tabs'] = $this->profiletabs($subject);
    
    $canEdit = $subject->authorization()->isAllowed($viewer, 'edit');
    if ($canEdit) {
        $i = 0;
        if (isset($subject->cover_photo) && $subject->cover_photo != 0 && $subject->cover_photo != '') {
            $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Change Cover Photo');
            $result['updateCoverPhoto'][$i]['name'] = 'upload';
            $i++;
            $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Remove Cover Photo');
            $result['updateCoverPhoto'][$i]['name'] = 'removePhoto';
            $i++;
            $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('View Cover Photo');
            $result['updateCoverPhoto'][$i]['name'] = 'view';
            $i++;
        } else {
            $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Add Cover Photo');
            $result['updateCoverPhoto'][$i]['name'] = 'upload';
            $i++;
        }
        $result['updateCoverPhoto'][$i]['label'] = $this->view->translate('Choose From Albums');
        $result['updateCoverPhoto'][$i]['name'] = 'album';
        // photo upload
        $j = 0;
        if (!empty($subject->photo_id)) {
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('View Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'view';
            $j++;
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'removePhoto';
            $j++;
        } else {
            $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Profile Photo');
            $result['updateProfilePhoto'][$j]['name'] = 'upload';
            $j++;
        }
        $result['updateProfilePhoto'][$j]['label'] = $this->view->translate('Choose From Albums');
        $result['updateProfilePhoto'][$j]['name'] = 'album';
        $j++;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
  public function deleteAction()
  {

    $viewer = Engine_Api::_()->user()->getViewer();
    $event = Engine_Api::_()->getItem('event', $this->getRequest()->getParam('id'));
    if( !$this->_helper->requireAuth()->setAuthParams($event, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    $this->view->form = $form = new Event_Form_Delete();

    if( !$event )
    {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Event doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    $db = $event->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $event->delete();
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }

    $this->view->status = true;
    $message = Zend_Registry::get('Zend_Translate')->_('The selected event has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }


  public function infoAction() {

    // Get subject
    if (Engine_Api::_()->core()->hasSubject('event'))
      $subject = Engine_Api::_()->core()->getSubject('event');

    $result = array();
    $result['event_content'] = $subject->toArray();
    
    // Convert the dates for the viewer
    $startDateObject = new Zend_Date(strtotime($subject->starttime));
    $endDateObject = new Zend_Date(strtotime($subject->endtime));
    
    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer && $viewer->getIdentity() ) {
      $tz = $viewer->timezone;
      $startDateObject->setTimezone($tz);
      $endDateObject->setTimezone($tz);
    }
    
    $result['event_content']['starttime'] = $this->view->locale()->toDate($startDateObject) . ' ' . $this->view->locale()->toTime($startDateObject);
    $result['event_content']['endtime'] = $this->view->locale()->toDate($endDateObject) . ' ' . $this->view->locale()->toTime($endDateObject);
    $result['event_content']['created_by'] = $subject->getOwner()->getTitle();
    $result['event_content']['creation_date'] = $this->view->translate( gmdate('M d, Y', strtotime($subject->creation_date))) ;

    $result['event_content']['modified_date'] = $this->view->translate( gmdate('M d, Y', strtotime($subject->modified_date))) ;
    $result['event_content']['view_count'] = $this->view->translate(array('%s total view', '%s total views', $subject->view_count), $this->view->locale()->toNumber($subject->view_count));
    $result['event_content']['member_count'] = $this->view->translate(array('%s total member', '%s total members', $subject->member_count), $this->view->locale()->toNumber($subject->member_count));
    $result['event_content']['location'] = $subject->location;
    if( !empty($subject->host) ) {
      if( $subject->host != $subject->getParent()->getTitle()) {
        $result['event_content']['host'] = $subject->host;
      }
      $result['event_content']['ledby'] = $subject->getParent()->__toString();
    }
    if($subject->category_id) {
      $category = Engine_Api::_()->getItem('event_category', $subject->category_id);
      $result['event_content']['category_name'] = $category->title;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
  
    public function discussionsAction()
    {
        // Don't render this if not authorized
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('event');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        }
        $canTopicCreate = $subject->authorization()->isAllowed(null, 'topic');
       
        // Get paginator
        $table = Engine_Api::_()->getItemTable('event_topic');
        $select = $table->select()
            ->where('event_id = ?', $subject->getIdentity())
            ->order('sticky DESC')
            ->order('modified_date DESC');
        $paginator = Zend_Paginator::factory($select);
        // Set item count per page and current page number
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        if ($viewer->getIdentity()) {
            //if ($canTopicCreate) {
                $result['label'] = $this->view->translate('Post New Topic');
                $result['name'] = 'pastnewtopic';
            //}
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

  public function membersAction() {

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('event');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get params
    $this->view->page = $page = $this->_getParam('page', 1);
    $this->view->search = $search = $this->_getParam('search');
    $this->view->waiting = $waiting = $this->_getParam('waiting', false);

    // Prepare data
    $this->view->event = $event = Engine_Api::_()->core()->getSubject();

     
    $members = null;
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() && $event->isOwner($viewer) ) {
      $this->view->waitingMembers = Zend_Paginator::factory($event->membership()->getMembersSelect(false));
      if( $waiting ) {
        $this->view->members = $members = $this->view->waitingMembers;
      }
    }
    if( !$members ) {
      $select = $event->membership()->getMembersObjectSelect();
      if( $search ) {
        $select->where('displayname LIKE ?', '%' . $search . '%');
      }
      $this->view->members = $members = Zend_Paginator::factory($select);
    }

    $paginator = $members;

    // Set item count per page and current page number
    $paginator->setItemCountPerPage($this->_getParam('itemCountPerPage', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', $page));
    
          
    $result = $this->membersResult($paginator,$event);
    $result['button']['label'] = $this->view->translate('See waiting');  
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
     
        

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist members.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }


  function membersResult($paginator,$event) {

    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($paginator as $item) {

      //$resource = $item->toArray();
      $memberInfo = $event->membership()->getRow($item); 
      $resource['displayname'] = $item->getTitle();
      $resource['user_id'] = $item->user_id;
      if( $memberInfo->rsvp == 0 ) {
        $resource['RSVP'] = $this->view->translate("Not Attending");
      } elseif( $memberInfo->rsvp == 1 ) {
        $resource['RSVP'] = $this->view->translate("Maybe Attending");
      } elseif( $memberInfo->rsvp == 2 ) {
        $resource['RSVP'] = $this->view->translate("Attending");
      } else {
        $resource['RSVP'] = $this->view->translate("Awaiting Reply");
      }

      $result['members'][$counterLoop] = $resource;

      $owner = $item->getOwner();
      if($owner && $owner->photo_id) {
        $photo = $this->getBaseUrl(false,$owner->getPhotoUrl('thumb.icon'));
        $result['members'][$counterLoop]['owner_photo']  = $photo;
      } else {
        $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true,'/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
      }
      $counterLoop++;
    }
    return $result;
  }
  
    public function creatediscussionAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('event')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $event = $event = Engine_Api::_()->core()->getSubject();
        $viewer = $viewer = Engine_Api::_()->user()->getViewer();
        // Make form
        $form = $form = new Event_Form_Topic_Create();

        $form->getElement('body')->setLabel('Description');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'event'));
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
        $values['event_id'] = $event->getIdentity();
        $topicTable = Engine_Api::_()->getDbTable('topics', 'event');
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'event');
        $postTable = Engine_Api::_()->getDbTable('posts', 'event');
        $db = $event->getTable()->getAdapter();
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
                'resource_id' => $event->getIdentity(),
                'topic_id' => $topic->getIdentity(),
                'user_id' => $viewer->getIdentity(),
                'watch' => (bool)$values['watch'],
            ));
            // Add activity
            $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $topic, 'event_topic_create');
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
        if (!$this->_helper->requireSubject('event_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $event = $event = $topic->getParentEvent();
        $canEdit = $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
        $canPost = $canPost = $event->authorization()->isAllowed($viewer, 'comment');
        $canAdminEdit = Engine_Api::_()->authorization()->isAllowed($event, null, 'edit');
        if (!$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id) {
            $topic->view_count = new Zend_Db_Expr('view_count + 1');
            $topic->save();
        }
        $isWatching = null;
        if ($viewer->getIdentity()) {
            $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'event');
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $event->getIdentity())
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
        $table = Engine_Api::_()->getDbTable('posts', 'event');
        $select = $table->select()
            ->where('event_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->order('creation_date ASC');
        $paginator = Zend_Paginator::factory($select);
        $topicdata['label'] = $topic->getTitle();
        $headeroptionscounter = 0;
        if ($canPost) {
            $data[$headeroptionscounter]['name'] = 'postreply';
            $data[$headeroptionscounter]['label'] = $this->view->translate('Post Reply');
            $headeroptionscounter++;
            if ($viewer->getIdentity()) {
                if (!$isWatching) {
                    $data[$headeroptionscounter]['name'] = 'watchtopic';
                    $data[$headeroptionscounter]['label'] = $this->view->translate('Watch Topic');
                    $headeroptionscounter++;
                } else {
                    $data[$headeroptionscounter]['name'] = 'stopwatching';
                    $data[$headeroptionscounter]['label'] = $this->view->translate('Stop Watching Topic');
                    $headeroptionscounter++;
                }
            }
        }

        $topicdata['value'] = $data;
        $counter = 0;
        foreach ($paginator as $post) {
            $posts[$counter] = $post->toArray();
            $user = $this->view->item('user', $post->user_id);
            $isOwner = false;
            $isMember = false;
            if ($event->isOwner($user)) {
                $isOwner = true;
                $isMember = true;
            } else if ($event->membership()->isMember($user)) {
                $isMember = true;
            }
            $posts[$counter]['post_id'] = $post->getIdentity();
            $posts[$counter]['title'] = $user->getTitle();
            $imagepath = $user->getPhotoUrl('thumb.profile');
            if ($imagepath)
                $posts[$counter]['user_photo'] = $this->getBaseUrl(false, $imagepath);

            if ($isOwner) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Host');
            } else if ($isMember) {
                $posts[$counter]['is_owner_label'] = $this->view->translate('Member');
            }
            $optioncounter = 0;
            if ($post->user_id == $viewer->getIdentity() || $event->getOwner()->getIdentity() == $viewer->getIdentity() || $canAdminEdit) {
                $posts[$counter]['options'][$optioncounter]['name'] = 'edit';
                $posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Edit');
                $optioncounter++;
                $posts[$counter]['options'][$optioncounter]['name'] = 'delete';
                $posts[$counter]['options'][$optioncounter]['label'] = $this->view->translate('Delete');
            }

            $posts[$counter]['creation_date'] = $event->creation_date;

            $counter++;
        }
        $result['posts'] = $posts;
        $result['topic'] = $topicdata;
        
         $topic_options = array();
         $topic_opcounter = 0;

   
        if( !$topic->sticky ) {
          $topic_options[$topic_opcounter]['name'] = "sticky";
          $topic_options[$topic_opcounter]['sticky'] = "1";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Make Sticky");
          $topic_opcounter++;
        } else {
          $topic_options[$topic_opcounter]['name'] = "sticky";
          $topic_options[$topic_opcounter]['sticky'] = "0";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Remove Sticky");
          $topic_opcounter++;
        }

        if( !$topic->closed ) {
          $topic_options[$topic_opcounter]['name'] = "close";
          $topic_options[$topic_opcounter]['close'] = "1";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Close");
          $topic_opcounter++;
        } else {
          $topic_options[$topic_opcounter]['name'] = "close";
          $topic_options[$topic_opcounter]['close'] = "0";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Open");
          $topic_opcounter++;
        }
        $topic_options[$topic_opcounter]['name'] = "rename";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Rename");
        $topic_opcounter++;
         $topic_options[$topic_opcounter]['name'] = "quote";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Quote");
        $topic_opcounter++;
        
        
      
     

        $topicContent['options'] = $topic_options;
       
         $result['topic_content'] = $topicContent;
        // Skip to page of specified post
        if (0 !== ($post_id = (int)$this->_getParam('post_id')) &&
            null !== ($post = Engine_Api::_()->getItem('event_post', $post_id))) {
            $icpp = $paginator->getItemCountPerPage();
            $page = ceil(($post->getPostIndex() + 1) / $icpp);
            $paginator->setCurrentPageNumber($page);
        } // Use specified page
        else if (0 !== ($page = (int)$this->_getParam('page'))) {
            $paginator->setCurrentPageNumber($this->_getParam('page'));
        }

        if ($canPost && !$topic->closed) {
            $form = new Event_Form_Post_Create();

            if ($this->_getParam('getForm')) {
                $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
                $this->generateFormFields($formFields, array('resources_type' => 'event'));
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
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
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
        if (!$this->_helper->requireSubject('event_topic')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'comment')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $topic = $topic = Engine_Api::_()->core()->getSubject();
        $event = $event = $topic->getParentEvent();
        if ($topic->closed) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This has been closed for posting.'), 'result' => array()));
            $status = false;
        }
        // Make form
        $form = new Event_Form_Post_Create();
        if($form->body)
          $form->getElement('body')->setLabel('Body');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'event', 'formTitle' => 
            "Post Reply"));
        }
        
        if( !$form->isValid($this->getRequest()->getPost()) ) {
					$validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
					if(is_countable($validateFields) && engine_count($validateFields))
					$this->validateFormFields($validateFields);
				}
				
        // Process
        $viewer = Engine_Api::_()->user()->getViewer();
        $topicOwner = $topic->getOwner();
        $isOwnTopic = $viewer->isSelf($topicOwner);
        $postTable = Engine_Api::_()->getDbTable('posts', 'event');
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'event');
        $userTable = Engine_Api::_()->getItemTable('user');
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
        $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        $values['event_id'] = $event->getIdentity();
        $values['topic_id'] = $topic->getIdentity();
        $watch = (bool)$values['watch'];
        $isWatching = $topicWatchesTable
            ->select()
            ->from($topicWatchesTable->info('name'), 'watch')
            ->where('resource_id = ?', $event->getIdentity())
            ->where('topic_id = ?', $topic->getIdentity())
            ->where('user_id = ?', $viewer->getIdentity())
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        $db = $event->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Create post
            $post = $postTable->createRow();
            $post->setFromArray($values);
            $post->save();
            // Watch
            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $event->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $event->getIdentity(),
                    'topic_id = ?' => $topic->getIdentity(),
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            }
            // Activity
            $action = $activityApi->addActivity($viewer, $topic, 'event_topic_reply');
            if ($action) {
                $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
            }
            // Notifications
            $notifyUserIds = $topicWatchesTable->select()
                ->from($topicWatchesTable->info('name'), 'user_id')
                ->where('resource_id = ?', $event->getIdentity())
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
                    $type = 'event_discussion_response';
                } else {
                    $type = 'event_discussion_reply';
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
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
      $topic->sticky = ( null === $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
      $topic->save();
      $db->commit();
      $temp['message'] = $this->view->translate('Done');
      $temp['sticky'] = $topic->sticky;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp, 'sticky' => $topic->sticky));
    } catch( Exception $e ) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
    public function renametopicAction(){
      $title = $this->_getParam('title');
        
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = new Event_Form_Topic_Rename();
        $form->populate($topic->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'event'));
        }

        if($form->isValid($this->getRequest()->getPost()) ) { 
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (is_countable($validateFields) && engine_count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Check method/data
        if ($this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'invalid_request', 'result' => array()));
        }
        $table = $topic->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
             $title = $this->_getParam('title');
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
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'permission_error', 'result' => array()));
        $form = $form = new Event_Form_Topic_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'event'));
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
            $event = $topic->getParent('event');
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
        $post = Engine_Api::_()->getItem('event_post', $postid);
        $event = $post->getParent('event');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
            }
        }
        $form = new Event_Form_Post_Edit();
        $form->body->setValue(html_entity_decode($post->body));
        $form->populate($post->toArray());
        if ($this->_getParam('getForm')) {
						$formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
						$this->generateFormFields($formFields,array('formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription())));
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
            $allowHtml = (bool)$settings->getSetting('event_html', 0);
            $allowBbcode = (bool)$settings->getSetting('event_bbcode', 0);
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
        $post = Engine_Api::_()->getItem('event_post', $postid);
        $event = $post->getParent('event');
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$event->isOwner($viewer) && !$post->isOwner($viewer)) {
            if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'edit')->isValid()) {
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
        $topic = Engine_Api::_()->getItem('event_topic', $topic_id);
        $href = (null === $topic ? $event->getHref() : $topic->getHref());
        return $this->_forward('success', 'utility', 'core', array(
            'closeSmoothbox' => true,
            'parentRedirect' => $href,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
        ));
    }
    public function watchAction(){
        $topic = Engine_Api::_()->core()->getSubject();
        $event = Engine_Api::_()->getItem('event', $topic->event_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }
        $watch = $this->_getParam('watch', true);
        $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'event');
        $db = $topicWatchesTable->getAdapter();
        $db->beginTransaction();
        try {
            $isWatching = $topicWatchesTable
                ->select()
                ->from($topicWatchesTable->info('name'), 'watch')
                ->where('resource_id = ?', $event->getIdentity())
                ->where('topic_id = ?', $topic->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->limit(1)
                ->query()
                ->fetchColumn(0);

            if (false === $isWatching) {
                $topicWatchesTable->insert(array(
                    'resource_id' => $event->getIdentity(),
                    'topic_id' => $topic->getIdentity(),
                    'user_id' => $viewer->getIdentity(),
                    'watch' => (bool)$watch,
                ));
            } else if ($watch != $isWatching) {
                $topicWatchesTable->update(array(
                    'watch' => (bool)$watch,
                ), array(
                    'resource_id = ?' => $event->getIdentity(),
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
        $photo = Engine_Api::_()->getItem('event_photo', $this->_getParam('photo_id'));
        $event_id = $this->_getparam('event_id', null);
        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        if ($album_id) {
            $album = Engine_Api::_()->getItem('event_album', $album_id);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_request', 'result' => array()));
        }
        if (!$this->_getparam('event_id', null)) {
            $event_id = $album->event_id;
        }
        $event = Engine_Api::_()->getItem('event', $event_id);
        $photo_id = $photo->getIdentity();
        if (!$this->_helper->requireAuth()->setAuthParams($event, null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $albumData = array();
        if ($viewer->getIdentity() > 0) {
            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;
            $canEdit = $event->authorization()->isAllowed($viewer, 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }
            $can_delete = $event->authorization()->isAllowed($viewer, 'delete');
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
            $canComment = $event->authorization()->isAllowed($viewer, 'comment') ? true : false;
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
        $albumData['module_name'] = 'event';
        $albumData['photos'] = $array_merge;
        if (engine_count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }
        public function nextPreviousImage($photo_id, $album_id, $condition = "<="){
        $photoTable = Engine_Api::_()->getItemTable('event_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('event_id !=?', 0)
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
  public function photosAction() {

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('event');
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
          $album_photo[$counterPhoto]['images'] = $this->getBaseUrl(true, $image);


        $album_photo[$counterPhoto]['photo_id'] = $photos['photo_id'];
        $album_photo[$counterPhoto]['album_id'] = $photos['album_id'];
        $album_photo[$counterPhoto]['event_id'] = $photos['event_id'];
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
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No photo created by you yet in this album.'), 'result' => array()));
    else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
    }
  }
    public function inviteAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('event')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'user_not_autheticate', 'result' => array()));
        // @todo auth
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $event = Engine_Api::_()->core()->getSubject();
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
        $form = new Event_Form_Invite();
        $count = 0;
        foreach ($friends as $friend) {
            if ($event->membership()->isMember($friend, null)) {
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
                $form->getElement('all')->setName('event_choose_all');

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
        $table = $event->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $usersIds = $form->getValue('users');
            $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
            foreach ($friends as $friend) {
                if (!engine_in_array($friend->getIdentity(), $usersIds)) {
                    continue;
                }
                $event->membership()->addMember($friend)->setResourceApproved($friend);
                $notifyApi->addNotification($friend, $viewer, $event, 'event_invite');
            }
            if ($count == 1) {
              $message = $this->view->translate('member invited.');
            } else {
              $message = $this->view->translate('All members invited.');
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
    // Check resource approval
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( $subject->membership()->isResourceApprovalRequired() ) {
      $row = $subject->membership()->getReceiver()
        ->select()
        ->where('resource_id = ?', $subject->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->query()
        ->fetch(Zend_Db::FETCH_ASSOC, 0);
        ;
      if (empty($row)) {
        // has not yet requested an invite
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
       //return $this->_helper->redirector->gotoRoute(array('action' => 'request', 'format' => 'smoothbox'));
      } elseif ($row['user_approved'] && !$row['resource_approved']) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      }
    }
    // Process form
    if( 1 )
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $subject = Engine_Api::_()->core()->getSubject();
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $membership_status = $subject->membership()->getRow($viewer)->active;

        $subject->membership()
          ->addMember($viewer)
          ->setUserApproved($viewer);

        $row = $subject->membership()
          ->getRow($viewer);

        $row->rsvp = $_POST['rsvp'];
        $row->save();

        // Add activity if membership status was not valid from before
        if (!$membership_status){
          $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
          $action = $activityApi->addActivity($viewer, $subject, 'event_join');
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
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Event joined'), 'gutterMenu' => $gutterMenu)));
    }
  }

  public function rejectAction()
  {
    // Check auth
    if(!$this->_helper->requireUser()->isValid()){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    if(!$this->_helper->requireSubject('event')->isValid()){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>'user_not_autheticate', 'result' => array()));
    }

    // Make form
    $form = new Event_Form_Member_Reject();
    // Process form
    if ($this->_getParam('getForm')) {
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
    // Process form
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try
    {
      $subject->membership()->removeMember($viewer);
      // Set the request as handled
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $viewer, $subject, 'event_invite');
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
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the event %s');
    $message = sprintf($message, $subject->__toString());
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
  }
  public function leaveAction()
  {
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
    //$this->view->form = $form = new Event_Form_Member_Leave();

    // Process form
    if( 1 )
    {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try
      {
        $subject->membership()->removeMember($viewer);
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
      }
      $gutterMenu = $this->gutterMenus($subject);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Event left'), 'gutterMenu' => $gutterMenu)));
    }
  }

  public function requestAction()
  {
    // Check resource approval
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    $form = new Event_Form_Member_Request();

    // Process form
    if ($this->_getParam('getForm')) {
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

    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $subject->membership()->addMember($viewer)->setUserApproved($viewer);

      // Add notification
      $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'event_approve');

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'databse_error', 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Your invite request has been sent.'))));
  }

  public function cancelAction()
  {
    // Check auth
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    if( !$this->_helper->requireSubject()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Make form
    $form = new Event_Form_Member_Cancel();
    // Process form
    if ($this->_getParam('getForm')) {
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

    $subject = Engine_Api::_()->core()->getSubject('event');
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try
    {
      $subject->membership()->removeMember($user);

      // Remove the notification?
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
        $subject->getOwner(), $subject, 'event_approve');
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
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success_message' => Zend_Registry::get('Zend_Translate')->_('Your invite request has been cancelled.'))));
  }
  public function approveAction()
  {
    if (!$this->_helper->requireUser()->isValid())   
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject('event')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'parameter_missing', 'result' => array()));

    // Get user
    if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) { 
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>$this->view->translate('user does not exist.'), 'result' => array()));
    }
    // Make form
    $form = new Event_Form_Member_Approve();
    // Process form
    if ($this->_getParam('getForm')) {
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
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try { 
        $subject->membership()->setResourceApproved($user);
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'event_accepted');
        $db->commit();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Event request approved'))));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }   
  }
  public function removeAction()
  {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'user_not_autheticate', 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    // Get user
    if (0 === ($user_id = (int) $this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('member does not exist.'), 'result' => array()));
    }
    $event = Engine_Api::_()->core()->getSubject();
    if (!$event->membership()->isMember($user)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Cannot remove a non-member.'), 'result' => array()));
    }
    // Make form
    $form = new Event_Form_Member_Remove();
    // Process form
    if ($this->_getParam('getForm')) {
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
    $db = $event->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      // Remove membership
      $event->membership()->removeMember($user);
      // Remove the notification?
      $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
              $event->getOwner(), $event, 'event_approve');
      if ($notification) {
        $notification->delete();
      }
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message'=>$this->view->translate('Event member removed.'))));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  protected function gutterMenus($subject) {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( ($viewer->getIdentity() && $subject->authorization()->isAllowed($viewer, 'edit')) ) {
      $menu[] = array(
        'label' => $this->view->translate('Edit Event Details'),
        'class' => 'icon_event_edit',
        'route' => 'event_specific',
        'params' => array(
          'controller' => 'event',
          'action' => 'edit',
          'event_id' => $subject->getIdentity(),
          'ref' => 'profile'
        )
      );
    }

    $row = $subject->membership()->getRow($viewer);

    // Not yet associated at all
    if( null === $row ) {
      if( $subject->membership()->isResourceApprovalRequired() ) {
        $menu[] =  array(
          'label' => $this->view->translate('Request Invite'),
          'class' => 'smoothbox icon_invite',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'request',
            'event_id' => $subject->getIdentity(),
          ),
        );
      } else {
        $menu[] =  array(
          'label' => $this->view->translate('Join Event'),
          'class' => 'smoothbox icon_event_join',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'join',
            'event_id' => $subject->getIdentity()
          ),
        );
      }
    } elseif( $row->active ) {
      if( !$subject->isOwner($viewer) ) {
        $menu[] =  array(
          'label' => $this->view->translate('Leave Event'),
          'class' => 'smoothbox icon_event_leave',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'leave',
            'event_id' => $subject->getIdentity()
          ),
        );
      } /*else {
        return false;
      }*/
    } elseif( !$row->resource_approved && $row->user_approved ) {
      $menu[] =  array(
        'label' => $this->view->translate('Cancel Invite Request'),
        'class' => 'smoothbox icon_event_reject',
        'route' => 'event_extended',
        'params' => array(
          'controller' => 'member',
          'action' => 'cancel',
          'event_id' => $subject->getIdentity()
        ),
      );
    } elseif( !$row->user_approved && $row->resource_approved ) {
      $menu[] =  array(
          'label' => $this->view->translate('Accept Event Invite'),
          'class' => 'smoothbox icon_event_accept',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'accept',
            'event_id' => $subject->getIdentity()
          ),
        ); 
        $menu[] = array(
          'label' => $this->view->translate('Ignore Event Invite'),
          'class' => 'smoothbox icon_event_reject',
          'route' => 'event_extended',
          'params' => array(
            'controller' => 'member',
            'action' => 'reject',
            'event_id' => $subject->getIdentity()
          ),
        );
    }
    if( !$viewer->getIdentity() || $subject->isOwner($viewer) ) {
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
        'label' => $this->view->translate('Invite Guests'),
        'class' => 'smoothbox icon_invite',
        'route' => 'event_extended',
        'params' => array(
          //'module' => 'event',
          'controller' => 'member',
          'action' => 'invite',
          'event_id' => $subject->getIdentity(),
          'format' => 'smoothbox',
        ),
      );
    }

    if(( $viewer->getIdentity() && $subject->isOwner($viewer) ) && $subject->getType() == 'event' ) {
      $menu[] = array(
        'label' => $this->view->translate('Message Members'),
        'class' => 'icon_message',
        'route' => 'messages_general',
        'params' => array(
          'action' => 'compose',
          'to' => $subject->getIdentity(),
          'multi' => 'event'
        )
      );
    }
    if($subject->authorization()->isAllowed($viewer, 'delete') ) {
      $menu[] = array(
        'label' => $this->view->translate('Delete Event'),
        'class' => 'smoothbox icon_event_delete',
        'route' => 'event_specific',
        'params' => array(
          'action' => 'delete',
          'event_id' => $subject->getIdentity(),
        //'format' => 'smoothbox',
        ),
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
      'label' => $this->view->translate('Guests'),
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
    return $tabs;
  }
  public function acceptAction()
  {
    // Process form
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
    $db->beginTransaction();
    $rsvp = $this->_getParam('rsvp');
    try {
      $membership_status = $subject->membership()->getRow($viewer)->active;
      $subject->membership()->setUserApproved($viewer);
      $row = $subject->membership()
        ->getRow($viewer);
      $row->rsvp = $rsvp;
      $row->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => $e->getMessage()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('You have accepted event invitation.');
    $message = sprintf($message, $subject->title);
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
  }
}
