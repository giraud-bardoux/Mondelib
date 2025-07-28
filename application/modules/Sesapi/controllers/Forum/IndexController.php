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
class Forum_IndexController extends Sesapi_Controller_Action_Standard {

  public function searchAction() {

    $search = $this->_getParam('search', false);
    
    $search_type = $this->_getParam('search_type', 'topics');

    if(empty($search))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

     // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }
    
    if($search_type == 'topics') {
    
      // Make paginator
      $table = Engine_Api::_()->getItemTable('forum_topic');
      $select = $table->select()
        ->order('sticky DESC')
        ->order($order . ' DESC');

      if ($this->_getParam('search', false)) {
        $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
      }

      $paginator = Zend_Paginator::factory($select);

      $page = (int)  $this->_getParam('page', 1);

      $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_forum_pagelength'));
      $paginator->setCurrentPageNumber($page);

      $result = array();
      $counterLoop = 0;

      foreach($paginator as $topics) {

        $topic = $topics->toArray();
        $description = strip_tags($topics['description']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($topic['description']);
        $topic['owner_title'] = Engine_Api::_()->getItem('user',$topics->user_id)->getTitle();
        $topic['description'] = $description;

        $owner = Engine_Api::_()->getItem('user',$topics->user_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
        if(empty($owner->photo_id)) {
          $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
          $ownerimage['main'] = $defPhoto;
          $ownerimage['icon'] = $defPhoto;
          $ownerimage['normal'] = $defPhoto;
          $ownerimage['profile'] = $defPhoto;
        }
        $topic['owner_image'] = $ownerimage; //$this->userImage($topics->user_id,"thumb.icon");
        $topic['resource_type'] = $topics->getType();

        $last_post = $topics->getLastCreatedPost();
        if( $last_post ) {
          $last_user = Engine_Api::_()->getItem('user', $last_post->user_id); //$this->user($last_post->user_id);
        } else {
          $last_user = Engine_Api::_()->getItem('user', $topics->user_id); //$this->user($topics->user_id);
        }
        $lastPostCount = 0;
        if( $last_post) {
          $topic['last_post'][$lastPostCount]['user_images'] = $this->userImage($last_user,"thumb.icon");
          $topic['last_post'][$lastPostCount]['user_id'] = $last_user->getIdentity();
          $topic['last_post'][$lastPostCount]['user_title'] = $last_user->getTitle();
          $topic['last_post'][$lastPostCount]['creation_date'] = $topics->modified_date;
          $lastPostCount++;
        }
        $result['topics'][$counterLoop] = $topic;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($topics,'','');
        if(!engine_count($images))
          $images['main'] = $this->getBaseUrl(true,$topics->getPhotoUrl());
        $result['topics'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }

//       $sesforum = Engine_Api::_()->getItem('sesforum_forum', $topics->forum_id);
//       $canPost = Engine_Api::_()->authorization()->isAllowed('sesforum_forum', $viewer, 'topic_create');
//       $list = $sesforum->getModeratorList();
//       $moderators = $list->getAllChildren();
// 
//       $moderator = $this->view->fluentList($moderators);
//       $moderator_count = 0;
//       $result['moderators'][$moderator_count]['label'] = $this->view->translate("Moderators");
//       $result['moderators'][$moderator_count]['moderators'] = $moderator;
//       if($canPost) {
//         $result['moderators'][$moderator_count]['topic_create'] = $this->view->translate("Post New Topic");
//       }
//       $moderator_count++;
    
    } else {
    
      $postTable = Engine_Api::_()->getDbTable('posts', 'forum');
      $postTableName =  $postTable->info('name');
      
      $postsSelect = $postTable->select()->setIntegrityCheck(false)->from($postTableName);
      if(!empty($search) && isset($search)) {
          $postsSelect->where($postTableName.".body LIKE ? ", '%' . $search . '%');
      }
      $paginator = Zend_Paginator::factory($postsSelect);
      $page = (int)  $this->_getParam('page', 1);
      $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_forum_pagelength'));
      $paginator->setCurrentPageNumber($page);
      
      $result = array();
      $counterLoop = 0;
      
      foreach($paginator as $posts) {
        $post = $posts->toArray();
        $topic = Engine_Api::_()->getItem('forum_topic', $posts->topic_id);
        $description = strip_tags($posts['body']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($post['body']);
        $post['description'] = $description;
        $post['resource_type'] = $posts->getType();
        $post['topic_title'] = $this->view->translate('in the topic %s', $topic->title);
        $result['topics'][$counterLoop] = $post;
        $counterLoop++;
      }
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist topics.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }
  
  public function watchAction() {
  
//     if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }
    $topic_id = $this->_getParam('topic_id', null);
    
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    $forum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($forum, $viewer, 'view')->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }

    $watch = $this->_getParam('watch', true);

    $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'forum');
    $db = $topicWatchesTable->getAdapter();
    $db->beginTransaction();

    try
    {
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;

      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $forum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $forum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      $db->commit();
      
      if($watch) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Watching.'), 'subscribe_id' => $watch, 'watch' => 0, 'unsubscribe' => $this->view->translate('Stop Watching Topic'))));
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Stopped Watching.'), 'subscribe_id' => $watch,'watch' => 1, 'subscribe' => $this->view->translate('Watch Topic'))));
      }
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }

  public function postcreateAction() {

    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $topic_id = $this->_getParam('topic_id', null);

    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    
    if(!$topic) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'post.create')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( $topic->closed) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $form = new Forum_Form_Post_Create();

    // Remove the file element if there is no file being posted
    if( $this->getRequest()->isPost() && empty($_FILES['photo']) ) {
      $form->removeElement('photo');
    }
    
    $quote_id = $this->getRequest()->getParam('quote_id',false);
    if(!empty($quote_id) ) {
      $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_html', 0);
      $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_bbcode', 0);
      $quote = Engine_Api::_()->getItem('forum_post', $quote_id);
      if($quote->user_id == 0) {
          $owner_name = Zend_Registry::get('Zend_Translate')->_('Deleted Member');
      } else {
          $owner_name = $quote->getOwner()->__toString();
      }
      if ( !$allowHtml && !$allowBbcode ) {
        $form->body->setValue( strip_tags($this->view->translate('%1$s said:', $owner_name)) . " ''" . strip_tags($quote->body) . "''\n-------------\n" );
      } elseif( $allowHtml ) {
        $form->body->setValue("<blockquote><strong>" . $this->view->translate('%1$s said:', $owner_name) . "</strong><br />" . $quote->body . "</blockquote><br />");
      } else {
        $form->body->setValue("[quote][b]" . strip_tags($this->view->translate('%1$s said:', $owner_name)) . "[/b]\r\n" . htmlspecialchars_decode($quote->body, ENT_COMPAT) . "[/quote]\r\n");
      }
    }

    // Check if post and populate
    if($this->_getParam('getForm')) {
      if($form->getElement('body')) {
        $form->getElement('body')->setLabel('Body');
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'forum_post', 'formTitle' => "Post Reply"));
    }    

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(is_countable($validateFields) && engine_count($validateFields))
      $this->validateFormFields($validateFields);
    }
     $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('forum', $this->view->viewer()->level_id, 'post.flood');
      if(!empty($itemFlood[0])){
          //get last activity
          $tableFlood = Engine_Api::_()->getDbTable("posts",'forum');
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
    $values['body'] = Engine_Text_BBCode::prepare($values['body']);
    $values['user_id'] = $viewer->getIdentity();
    $values['topic_id'] = $topic->getIdentity();
    $values['forum_id'] = $forum->getIdentity();

    $topicTable = Engine_Api::_()->getDbTable('topics', 'forum');
    $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'forum');
    $postTable = Engine_Api::_()->getDbTable('posts', 'forum');
    $userTable = Engine_Api::_()->getItemTable('user');
    $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
    $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');

    $viewer = Engine_Api::_()->user()->getViewer();
    $topicOwner = $topic->getOwner();
    $isOwnTopic = $viewer->isSelf($topicOwner);

    $watch = (bool) $values['watch'];
    $isWatching = $topicWatchesTable
      ->select()
      ->from($topicWatchesTable->info('name'), 'watch')
      ->where('resource_id = ?', $forum->getIdentity())
      ->where('topic_id = ?', $topic->getIdentity())
      ->where('user_id = ?', $viewer->getIdentity())
      ->limit(1)
      ->query()
      ->fetchColumn(0);

    
    $db = $postTable->getAdapter();
    $db->beginTransaction();

    try {
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('forum', $viewer, 'post.approve');
      if(!empty($values['approved']))
        $values['resubmit'] = 1;
        
      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      if( !empty($values['photo']) ) {
        try {
          $post->setPhoto($form->photo);
        } catch( Engine_Image_Adapter_Exception $e ) {}
      }

      // Watch
      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $forum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $forum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }
     
      $topicLink = '<a href="' . $topic->getHref() . '">' . $topic->getTitle() . '</a>';
      // Activity
      $action = $activityApi->addActivity($viewer, $topic, 'forum_topic_reply',null,  array("topictitle" => $topicLink));
      if( $action ) {
        $action->attach($post, Activity_Model_Action::ATTACH_DESCRIPTION);
      }
      
      if ($post->approved) {
        // Notifications
        $notifyUserIds = $topicWatchesTable->select()
          ->from($topicWatchesTable->info('name'), 'user_id')
          ->where('resource_id = ?', $forum->getIdentity())
          ->where('topic_id = ?', $topic->getIdentity())
          ->where('watch = ?', 1)
          ->query()
          ->fetchAll(Zend_Db::FETCH_COLUMN);
        
        foreach($userTable->find($notifyUserIds) as $notifyUser ) {
          // Don't notify self
          if( $notifyUser->isSelf($viewer) ) {
            continue;
          }
          if( $notifyUser->isSelf($topicOwner) ) {
            $type = 'forum_topic_response';
          } else {
            $type = 'forum_topic_reply';
          }
          $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
            'message' => $this->view->BBCode($post->body),
            'postGuid' => $post->getGuid(),
          ));
        }
      }

      $db->commit();
      
      if(empty($topic_id) && empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'message' => $this->view->translate('Topic created successfully.'))));
      } elseif(empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'message' => $this->view->translate('Reply posted successfully.'))));
      } elseif(!empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'message' => $this->view->translate('Quote successfully.'))));
      }
      die();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    //return $this->_redirectCustom($post);
  }
  
  public function topicviewpageAction() {

    $topic_id = (int) $this->_getParam('topic_id', null);
    if( 0 !== ($topic_id = (int) $this->_getParam('topic_id')) &&
        null !== ($topic = Engine_Api::_()->getItem('forum_topic', $topic_id)) &&
        $topic instanceof Forum_Model_Topic ) {
      Engine_Api::_()->core()->setSubject($topic);
    }
    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    //$topic = Engine_Api::_()->core()->getSubject('forum_topic');
    $forum = $topic->getParent();

    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'view')->isValid() ) {
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    // Settings
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $post_id = (int) $this->_getParam('post_id');
    $decode_bbcode = $settings->getSetting('forum_bbcode');

    // Views
    if( !$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id ) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    // Check watching
    $isWatching = null;
    if( $viewer->getIdentity() ) {
      $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'forum');
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( false === $isWatching ) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    $isWatching = $isWatching;

    // Auth for topic
    $canPost = false;
    $canEdit = false;
    $canDelete = false;
    if( !$topic->closed && Engine_Api::_()->authorization()->isAllowed($forum, null, 'post.create') ) {
      $canPost = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.edit') ) {
      $canEdit = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.delete') ) {
      $canDelete = true;
    }

    $isModeratorPost = $forum->isModerator($viewer);
    if($isModeratorPost) {
        $canPost = true;
        $canEdit = true;
        $canDelete = true;
    }

    $canPost = $canPost;
    $canEdit = $can_edit = $canEdit;
    $canDelete = $can_delete = $canDelete;

    // Auth for posts
    $canEdit_Post = false;
    $canDelete_Post = false;
    if($viewer->getIdentity()){
      $canEdit_Post = Engine_Api::_()->authorization()->isAllowed($forum, $viewer->level_id, 'post.edit');
      $canDelete_Post = Engine_Api::_()->authorization()->isAllowed($forum, $viewer->level_id, 'post.delete');
    }
    $canEdit_Post = $canEdit_Post;
    $canDelete_Post = $canDelete_Post;


    // Make form
    if( $canPost ) {
      $form = new Forum_Form_Post_Quick();
      $form->setAction($topic->getHref(array('action' => 'post-create')));
      $form->populate(array(
        'topic_id' => $topic->getIdentity(),
        'ref' => $topic->getHref(),
        'watch' => ( false === $isWatching ? '0' : '1' ),
      ));
    }

    // Keep track of topic user views to show them which ones have new posts
    if( $viewer->getIdentity() ) {
      $topic->registerView($viewer);
    }

    $table = Engine_Api::_()->getItemTable('forum_post');
    $select = $topic->getChildrenSelect('forum_post', array('order'=>'post_id ASC'));
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($settings->getSetting('forum_topic_pagelength'));

    // set up variables for pages
    $page_param = (int) $this->_getParam('page');
    $post = Engine_Api::_()->getItem('forum_post', $post_id);

    // if there is a post_id
    if( $post_id && $post && !$page_param )
    {
      $icpp = $paginator->getItemCountPerPage();
      $post_page = ceil(($post->getPostIndex() + 1) / $icpp);

      $paginator->setCurrentPageNumber($post_page);
    }
    // Use specified page
    else if( $page_param )
    {
      $paginator->setCurrentPageNumber($page_param);
    }

    //$post_content = $topic->toArray();
    $counterPost =  0;
    foreach( $paginator as $i => $post ) {
      $post_content = $post->toArray();
      
      //$signature = $post->getSignature();
      //$signature_body = $signature->body; 
      $doNl2br = false;
//       if( strip_tags($signature_body) == $signature_body ) {
//         $signature_body = nl2br($signature_body);
//       }
//       if( !$this->decode_html && $this->decode_bbcode ) {
//         $signature_body = $this->BBCode($signature_body, array('link_no_preparse' => true));
//       }
      
        if( strip_tags($post->body) == $post->body ) {
          $post_content['body'] = nl2br( $post->body);
        }
        if( !Engine_Api::_()->getApi('settings', 'core')->getSetting('decode_html') && Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_bbcode') ) {
          $filters = array(
                      'Basic',
                      'Extended',
                      'Links',
                      'Images',
                      'Lists',
                      'Email'
                    );
          $text = nl2br($post->body);
          $parser = new HTML_BBCodeParser2(array_merge(array(
            'filters' => join(',', $filters)
          ), array('link_no_preparse' => true)));
          $parser->setText($text);
          $parser->parse();
        $post_content['body'] = $parser->getParsed();
      }
        
        $body = $this->replaceSrc($post_content['body']);
        $post_content['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    
        $post_content['body'] = $post_content['body']."<link href='".$this->getBaseUrl(true, 'application/modules/Sesapi/externals/styles/style_forum.css')."' media='all' rel='stylesheet' type='text/css' />";
        $isModeratorPost = $forum->isModerator($post->getOwner());

        if( $post->user_id != 0 ) {
          if( $post->getOwner() ) {
            if( $isModeratorPost ) {
              $post_content['moderator_label'] = $this->view->translate('Moderator');
            }
          }

        }
//       if($signature_body) {
//         $post_content['signature'] = $signature_body;
//       }
      
      $post_content['owner_title'] = Engine_Api::_()->getItem('user',$post->user_id)->getTitle();
      $post_content['description'] = $description;
      $post_content['owner_images'] = $this->userImage($post->user_id,"thumb.icon");
      $post_content['resource_type'] = $post->getType();
//       if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.thanks', 1)) {
//         $isThank = Engine_Api::_()->getDbTable('thanks', 'forum')->isThank(array('post_id' => $post->post_id,'resource_id' => $post->user_id));
//         if (empty($isThank) && !empty($viewer_id) && $viewer_id != $post->user_id) {
//             $post_content['isThanks'] = true;
//         } else {
//             $post_content['isThanks'] = false;
//         }
//       }

        $canLike = 1;
        $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($post, $viewer);
        if ($canLike && !empty($viewer_id)) {
            if(empty($isLike)) {
              $post_content['is_content_like'] = false;
            } else {
              $post_content['is_content_like'] = true;
            }
        }

//       if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.thanks', 1)) {
//         $thanks = Engine_Api::_()->getDbTable('thanks', 'forum')->getAllUserThanks($post->user_id);
//         if($thanks) {
//           $post_content['thanks'] = $this->view->translate("%s Thank(s)", $thanks);
//           $post_content['thanks_count'] = $thanks;
//         }
//       }

//       if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.reputation', 1)) {
//         $getIncreaseReputation = Engine_Api::_()->getDbTable('reputations', 'forum')->getIncreaseReputation(array('user_id' => $post->user_id));
//         $getDecreaseReputation = Engine_Api::_()->getDbTable('reputations', 'forum')->getDecreaseReputation(array('user_id' => $post->user_id));
//         $post_content['reputations'] = $this->view->translate("%s - %s", $getIncreaseReputation, $getDecreaseReputation);
//       }

      $signature = $post->getSignature();
      if($signature) {
        $post_content['post_count'] = $signature->post_count;
      }
      
      //$pagedata["share"]["imageUrl"] = $this->getBaseUrl(false, $page->getPhotoUrl());
      $post_content["share"]["url"] = $this->getBaseUrl(false,$post->getHref());
      $post_content["share"]["title"] = '';
      $post_content["share"]["description"] = strip_tags($post->getDescription());
      $post_content["share"]["setting"] = $shareType;
      $post_content["share"]['urlParams'] = array(
        "type" => $post->getType(),
        "id" => $post->getIdentity()
      );
      
      // Auth for topic
      $canPost = false;
      $canEdit = false;
      $canDelete = false;
      if( !$topic->closed && Engine_Api::_()->authorization()->isAllowed($forum, null, 'post.create') ) {
        $canPost = true;
      }
      if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.edit') ) {
        $canEdit = true;
      }
      if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.delete') ) {
        $canDelete = true;
      }
      
//       // Auth for topic
//       $canPost = 0;
//       $canEdit = false;
//       $canDelete = false;
//       if($viewer->getIdentity())
//         $levelId = $viewer->level_id;
//       else
//         $levelId = 5;
//         
//       $canPostPerminsion = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'post.create');
//       if(!$topic->closed && $canPostPerminsion) {
//         $canPost = $canPostPerminsion->value;
//       }
//       $canEditPerminsion = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'topic.edit');
//       if($canEditPerminsion) {
//         $canEdit = $canEditPerminsion->value;
//       }
// 
//       $canDeletePerminsion = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'topic.delete');
//       if($canDeletePerminsion) {
//         $canDelete = $canDeletePerminsion->value;
//       }
// 
//       $isModeratorPost = $forum->isModerator($viewer);
//       if($isModeratorPost) {
//           $canPost = 1;
//           $canEdit = true;
//           $canDelete = true;
//       }

      // Auth for posts
      $canEdit_Post = false;
      $canDelete_Post = false;
      if($viewer->getIdentity()){
        $canEdit_Post = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'post.edit');
        $canDelete_Post = Engine_Api::_()->authorization()->isAllowed('forum', $viewer->level_id, 'post.delete');
      }
      
      
//       // Auth for posts
//       $canEdit_Post = false;
//       $canDelete_Post = false;
//       if($viewer->getIdentity()){
//         $canEdit_Post = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'post_edit')->value;
//         $canDelete_Post = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'post_delete')->value;
//       }
      
      if($topic->closed) {
        $canPost = false;
      }
      $post_content['canPost'] = $canPost;
      $menuoptions = $options = array();
      $counter = $option_counter = 0;
      if($canPost && !$topic->closed) {
        $menuoptions[$counter]['name'] = "quote";
        $menuoptions[$counter]['label'] = $this->view->translate("Quote");
        $counter++;
      }

      if(!empty($viewer->getIdentity())) {

        $canLike = 1;
        $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($post, $viewer);
        
        if ($canLike && !empty($viewer_id)) {
            if(empty($isLike)) {
              $menuoptions[$counter]['name'] = "like";
              $menuoptions[$counter]['label'] = $this->view->translate("Like");
              $counter++;
            } else {
              $menuoptions[$counter]['name'] = "unlike";
              $menuoptions[$counter]['label'] = $this->view->translate("Unlike");
              $counter++;
            }
        }

//         if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.thanks', 1)) {
//           $isThank = Engine_Api::_()->getDbTable('thanks', 'forum')->isThank(array('resource_id' => $post->post_id));
//           if (empty($isThank) && !empty($viewer_id)) {
//               $menuoptions[$counter]['name'] = "thanks";
//               $menuoptions[$counter]['label'] = $this->view->translate("Say Thank");
//               $menuoptions[$counter]['isThanks'] = true;
//               $counter++;
//           } else {
//               $menuoptions[$counter]['isThanks'] = false;
//               $counter++;
//           }
//         }

        if($post->user_id != $viewer->getIdentity() ) {
          $options[$option_counter]['name'] = "report";
          $options[$option_counter]['label'] = $this->view->translate("Report");
          $option_counter++;
        }
        
//         $isReputation = Engine_Api::_()->getDbTable('reputations', 'forum')->isReputation(array('post_id' => $post->getIdentity(), 'resource_id' => $post->user_id));
//         if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.reputation', 1) && empty($isReputation) && $viewer_id != $post->user_id) {
//           $options[$option_counter]['name'] = "reputation";
//           $options[$option_counter]['label'] = $this->view->translate("Add Reputation");
//           $option_counter++;
// 
//         }
        
        
        if( $canEdit && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer)) ) {
          $post_content['canEdit'] = true;
          $post_content['canDelete'] = true;
        } elseif( $post->user_id != 0 && $post->isOwner($viewer) && !$topic->closed  && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer))) {
          $post_content['post_count'] = $signature->post_count;
          $post_content['canEdit'] = true;
          if( $this->canDelete_Post ) {
            $post_content['canDelete'] = true;
          } else {
            $post_content['canDelete'] = false;
          }
        } else {
          $post_content['canEdit'] = false;
          $post_content['canDelete'] = false;
        }
        
        if( $canEdit ) {
          $options[$option_counter]['name'] = "edit";
          $options[$option_counter]['label'] = $this->view->translate("Edit");
          $option_counter++;
          $options[$option_counter]['name'] = "delete";
          $options[$option_counter]['label'] = $this->view->translate("Delete");
          $option_counter++;
        } elseif( $post->user_id != 0 && $post->isOwner($viewer) && !$topic->closed ) {
            if( $canEdit_Post ) {
              $options[$option_counter]['name'] = "edit";
              $options[$option_counter]['label'] = $this->view->translate("Edit");
              $option_counter++;
            }
            if( $canDelete_Post ) {
              $options[$option_counter]['name'] = "delete";
              $options[$option_counter]['label'] = $this->view->translate("Delete");
              $option_counter++;
            }
        }

//         if( $canEdit_Post && $canDelete_Post && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer)) ) {
//           $options[$option_counter]['name'] = "edit";
//           $options[$option_counter]['label'] = $this->view->translate("Edit");
//           $option_counter++;
//           $options[$option_counter]['name'] = "delete";
//           $options[$option_counter]['label'] = $this->view->translate("Delete");
//           $option_counter++;
//         } elseif( $post->user_id != 0 && $post->isOwner($viewer) && !$topic->closed  && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer))) {
//           if( $canEdit_Post ) {
//             $options[$option_counter]['name'] = "edit";
//             $options[$option_counter]['label'] = $this->view->translate("Edit");
//             $option_counter++;
//           }
// 
//           if( $canDelete_Post ) {
//             $options[$option_counter]['name'] = "delete";
//             $options[$option_counter]['label'] = $this->view->translate("Delete");
//             $option_counter++;
//           }
//         } else if(($canDelete_Post || $canEdit_Post || ($post->user_id != $viewer->getIdentity() || $viewer_id)) && $viewer_id) {
//           if(!$post->isOwner($viewer)) {
//             if( $canEdit_Post == 2 ) {
//               $options[$option_counter]['name'] = "edit";
//               $options[$option_counter]['label'] = $this->view->translate("Edit");
//               $option_counter++;
//             }
// 
//             if( $canDelete_Post == 2 ) {
//               $options[$option_counter]['name'] = "delete";
//               $options[$option_counter]['label'] = $this->view->translate("Delete");
//               $option_counter++;
//             }
//           }
//         }
      }
      $post_content['options'] = $options;
      $post_content['menus'] = $menuoptions;

      $result['posts'][$counterPost] = $post_content;

      $counterPost++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;

    //Topic Content
    $topicContent['topic_title'] = $topic->getTitle();
    $topicContent['topic_id'] = $topic->getIdentity();
    //$topicContent['can_rate'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.rating', 1) ? true : false;
    //$topicContent['rating'] = $topic->rating;
    //$topicContent['rating_count'] = Engine_Api::_()->forum()->ratingCount($topic->getIdentity());
    $topicContent['back_to_topics'] = $this->view->translate("Back to Topics");
    if( $canPost && !$topic->closed) {
      $topicContent['post_reply'] = $this->view->translate("Post Reply");
    }
    
    
    // Check watching
    $isWatching = null;
    if( $viewer->getIdentity() ) {
      $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'forum');
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $forum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( false === $isWatching ) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }

    $topicContent['can_subscribe'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.subscribe', 1);
    //if(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum.subscribe', 1)) { 
      if( $viewer->getIdentity() ) {
        //$isSubscribe = Engine_Api::_()->getDbTable('subscribes', 'forum')->isSubscribe(array('resource_id' => $topic->getIdentity()));
        if( !$isWatching ) {
          $topicContent['subscribe'] = $this->view->translate("Watch Topic");
          $topicContent['watch'] = 1;
        } else {
          $topicContent['unsubscribe'] = $this->view->translate("Stop Watching Topic");
          $topicContent['watch'] = 0;
        }
      }
    //}

//     $topicContent['like_count'] = $topic->like_count;
//     if( $viewer->getIdentity() ) {
//       $canLike = 1;
//       $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($topic, $viewer);
//       if ($canLike && !empty($viewer_id)) {
//         if(empty($isLike)) {
//           $topicContent['is_content_like'] = false;
//         } else {
//           $topicContent['is_content_like'] = true;
//         }
//       }
//     }
    
//     $tags = array();
//     foreach ($topic->tags()->getTagMaps() as $tagmap) {
//         $arrayTag = $tagmap->toArray();
//         if(!$tagmap->getTag())
//             continue;
//         $tags[] = array_merge($tagmap->toArray(), array(
//             'id' => $tagmap->getIdentity(),
//             'text' => $tagmap->getTitle(),
//             'href' => $tagmap->getHref(),
//             'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
//         ));
//     }
//     
//     if (is_countable($tags) && engine_count($tags)) {
//       $topicContent['tag'] = $tags;
//     }
    
    if( !$topic->sticky ) {
      $topicContent['sticky'] = true;
    } else {
      $topicContent['sticky'] = false;
    }
    if( !$topic->closed ) {
      $topicContent['close'] = true;
    } else {
      $topicContent['close'] = false;
    }
    //$pagedata["share"]["imageUrl"] = $this->getBaseUrl(false, $page->getPhotoUrl());
    $topicContent["share"]["url"] = $this->getBaseUrl(false,$topic->getHref());
    $topicContent["share"]["title"] = $topic->getTitle();
    $topicContent["share"]["description"] = strip_tags($topic->getDescription());
    $topicContent["share"]["setting"] = $shareType;
    $topicContent["share"]['urlParams'] = array(
      "type" => $topic->getType(),
      "id" => $topic->getIdentity(),
    );
    if( $viewer->getIdentity() ) {
      //$canLike = 1;
      //$isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($topic, $viewer);
      
//       if ($canLike && !empty($viewer_id)) {
         $topic_menuoptions = array();
         $topic_counter = 0;
//         if(empty($isLike)) {
//           $topic_menuoptions[$topic_counter]['name'] = "like";
//           $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Like");
//           $topic_counter++;
//         } else {
//           $topic_menuoptions[$topic_counter]['name'] = "unlike";
//           $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Unlike");
//           $topic_counter++;
//         }
//       }
      $topic_menuoptions[$topic_counter]['name'] = "share";
      $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Share");
      $topic_counter++;
      $topicContent['buttons'] = $topic_menuoptions;
    }
    
    // Auth for topic
    $canEdit = false;
    $canDelete = false;
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.edit') ) {
      $canEdit = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'topic.delete') ) {
      $canDelete = true;
    }

    
//     // Auth for topic
//     $canEdit = false;
//     $canDelete = false;
// 
//     $canEditPerminsion = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'topic_edit');
//     if($canEditPerminsion) {
//       $canEdit = $canEditPerminsion->value;
//     }
//     // echo $canEdit;
//     $canDeletePerminsion = Engine_Api::_()->forum()->isAllowed($forum,$levelId, 'topic_delete');
//     if($canDeletePerminsion) {
//       $canDelete = $canDeletePerminsion->value;
//     }
// 
//     $isModeratorPost = $forum->isModerator($viewer);
//     if($isModeratorPost) {
//         $canEdit = true;
//         $canDelete = true;
//     }
    
    //if( ($canEdit || $canDelete) && ($viewer_id == $topic->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer)) || (($canEdit == 2) || ($canDelete == 2))) {
      if($canEdit) {
        $topicContent['canEdit'] = true;
      } else {
        $topicContent['canEdit'] = false;
      }
      if($canDelete) {
        $topicContent['canDelete'] = true;
      } else {
        $topicContent['canDelete'] = false;
      }
    //}

    if( ($canEdit || $canDelete) && ($viewer_id == $topic->user_id || $viewer->level_id == '1' || $forum->isModerator($viewer)) || (($canEdit == 2) || ($canDelete == 2))) {
      $topic_options = array();
      $topic_opcounter = 0;

      if(($canEdit && $topic->user_id == $viewer->getIdentity()) || $canEdit == 2) {
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
          $topic_options[$topic_opcounter]['name'] = "forumclose";
          $topic_options[$topic_opcounter]['close'] = "1";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Close");
          $topic_opcounter++;
        } else {
          $topic_options[$topic_opcounter]['name'] = "forumclose";
          $topic_options[$topic_opcounter]['close'] = "0";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Open");
          $topic_opcounter++;
        }
        $topic_options[$topic_opcounter]['name'] = "rename";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Rename");
        $topic_opcounter++;
        $topic_options[$topic_opcounter]['name'] = "move";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Move");
        $topic_opcounter++;
      }
      if( ($canDelete && $topic->user_id == $viewer->getIdentity()) || $canDelete == 2 ) {
        $topic_options[$topic_opcounter]['name'] = "delete";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Delete");
        $topic_opcounter++;
      }

      $topicContent['options'] = $topic_options;
    }

    $result['topic_content'] = $topicContent;

    //Reply Form
//     if( $canPost && $form ) {
//       $result['reply_form'] = $topicContent;
//
//     }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

  }
  function replaceSrc($html = ""){
      preg_match_all( '@src="([^"]+)"@' , $html, $match );
      foreach(array_pop($match) as $src){
          if(strpos($src,'http://') === false && strpos($src,'https://') === false && strpos($src,'//') === false){
              $baseUrl = str_replace(Zend_Registry::get('StaticBaseUrl'),'',$this->getBaseUrl());
              $html = str_replace($src,preg_replace('/([^:])(\/{2,})/', '$1/', $baseUrl.$src),$html);
          }else if(strpos($src,'http://') === false && strpos($src,'https://') === false){
              $html = str_replace($src,'https://'.$src,$html);
          }
      }
      return $html;
  }
  
  public function stickyAction() {

    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    
    $topic_id = $this->_getParam('topic_id', null);
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $topic =  Engine_Api::_()->getItem('forum_topic', $topic_id);
    $forum = $topic->getParent();
    
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit')->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

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
  
  public function moveAction() {
  
    if( !$this->_helper->requireSubject('forum_topic')->isValid() ) {
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    
    $topic_id = $this->_getParam('topic_id', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Forum_Form_Topic_Move();

    // Populate with options
    $multiOptions = array();
    foreach( Engine_Api::_()->getItemTable('forum')->fetchAll() as $forum ) {
      $multiOptions[$forum->getIdentity()] = $this->view->translate($forum->getTitle());
    }
    
    $form->getElement('forum_id')->setMultiOptions($multiOptions);
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    
    if( !$this->getRequest()->isPost() ) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $values = $form->getValues();

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      // Update topic
      $topic->forum_id = $values['forum_id'];
      $topic->save();

      $db->commit();

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Topic moved.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
  public function editpostAction() {

    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $post_id = $this->_getParam('post_id', null);

    $post = Engine_Api::_()->getItem('forum_post', $post_id);

    if(!$post) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $post = $post;
    $topic = $post->getParent();
    $forum = $topic->getParent();
    
    $postEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'post.edit');
    $topicEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'topic.edit');
    if(!$postEdit) {
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    if(!$topicEdit) {
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
//     if( !$this->_helper->requireAuth()->setAuthParams($post, null, 'edit')->checkRequire() &&
//         !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit')->checkRequire() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }

    $this->view->form = $form = new Forum_Form_Post_Edit(array('post'=>$post));

    $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_html', 0);
    $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_bbcode', 0);

    if( $allowHtml ) {
      $body = $post->body;
      $body = preg_replace_callback('/href=["\']?([^"\'>]+)["\']?/', function($matches) {
          return 'href="' . str_replace(['&gt;', '&lt;'], '', $matches[1]) . '"';
      }, $body);
    } else {
      $body = htmlspecialchars_decode($post->body, ENT_COMPAT);
    }
    $form->body->setValue($body);
    if($post->file_id)
    $form->photo->setValue($post->file_id);

    if($this->_getParam('getForm')) {
      if($form->getElement('body')) {
        $form->getElement('body')->setLabel('Body');
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $formFields[1]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'forum_post', 'formTitle' => "Edit Post"));
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
    $table = Engine_Api::_()->getItemTable('forum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $post->body = $values['body'];
      $post->body = Engine_Text_BBCode::prepare($post->body);

      $post->edit_id = $viewer->getIdentity();

      //DELETE photo here.
      if( !empty($values['photo_delete']) && $values['photo_delete'] ) {
        $post->deletePhoto();
      }

      if( !empty($_FILES['file']['name']) &&  !empty($_FILES['file']['size']) ) {
        $post->setPhoto($_FILES['file']);
      }

      $post->save();

      $db->commit();

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('post_id' => $post->getIdentity(),'message' => $this->view->translate('Post edited successfully.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  
  public function closeAction() {
  
    $topic_id = $this->_getParam('topic_id', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.edit')->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $topic->closed = ( null === $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
      $topic->save();
      $db->commit();
      $temp['message'] = $this->view->translate('Done');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
  public function renameAction() {

    $topic_id = $this->_getParam('topic_id', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    //$forum = $topic->getParent();
    
    $this->view->form = $form = new Forum_Form_Topic_Rename();
    
    if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    
    if( !$this->getRequest()->isPost() ) {
    
      $form->title->setValue(htmlspecialchars_decode(($topic->title)));
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $title = $form->getValue('title');
      $topic = $topic;
      $topic->title = $title;
      $topic->save();
      $db->commit();

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have rename topic.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
  public function deletetopicAction() {
  
    $topic_id = $this->_getParam('topic_id', null);

    $viewer = Engine_Api::_()->user()->getViewer();
    $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
    $forum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.delete')->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    $this->view->form = $form = new Forum_Form_Topic_Delete();

    if (!$this->getRequest()->isPost()) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_topic');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $topic->delete();
      $db->commit();
      $status['status'] = true;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this topic.'), $status, 'href' => $forum->getHref())));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
  public function deletepostAction() {
  
    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    $post_id = $this->_getParam('post_id', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    $post = Engine_Api::_()->getItem('forum_post',$post_id);
    $topic = $post->getParent();
    $forum = $topic->getParent();
    
    if(!$post) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    
    $postEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'post.delete');
    $topicEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'forum', 'topic.delete');
    if(!$postEdit) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    if(!$topicEdit) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    $form = new Forum_Form_Post_Delete();
    if (!$this->getRequest()->isPost()) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    // Process
    $table = Engine_Api::_()->getItemTable('forum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $post->delete();
      $db->commit();

      $status['status'] = true;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this post.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
  public function topiccreateAction() {

    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $forum_id = $this->_getParam('forum_id', null);
    $forum = Engine_Api::_()->getItem('forum', $forum_id);

    if( !$forum ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $forum = $forum;
    if (!$this->_helper->requireAuth()->setAuthParams($forum, null, 'topic.create')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Forum_Form_Topic_Create();

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $form->getElement('body')->setLabel('Body');
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'forum_topic', 'formTitle' => "Post Topic"));
    }

    // Remove the file element if there is no file being posted
    if( $this->getRequest()->isPost() && empty($_FILES['photo']) ) {
      $form->removeElement('photo');
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(is_countable($validateFields) && engine_count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    $itemFlood = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('forum', $this->view->viewer()->level_id, 'topic.flood');
    if(!empty($itemFlood[0])){
      //get last activity
      $tableFlood = Engine_Api::_()->getDbTable("topics",'forum');
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
    $values['forum_id'] = $forum->getIdentity();

    $topicTable = Engine_Api::_()->getDbTable('topics', 'forum');
    $topicWatchesTable = Engine_Api::_()->getDbTable('topicWatches', 'forum');
    $postTable = Engine_Api::_()->getDbTable('posts', 'forum');
    
    $db = $topicTable->getAdapter();
    $db->beginTransaction();
    try {
    
      //approve setting work
      $values['approved'] = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('forum', $viewer, 'approve');
            
      // Create topic
      $topic = $topicTable->createRow();
      $topic->setFromArray($values);
      $topic->title = $values['title'];
      $topic->description = $values['body'];
      $topic->save();
      if($topic->approved) {
        $topic->resubmit = 1;
        $topic->save();
      }
      
      //Save editor images
      Engine_Api::_()->core()->saveTinyMceImages($values['body'], $topic);
      

      // Create post
      $values['topic_id'] = $topic->getIdentity();

      $post = $postTable->createRow();
      $values['body'] = Engine_Text_BBCode::prepare($values['body']);
      $post->setFromArray($values);
      $post->save();
      if($topic->approved) {
        $post->resubmit = 1;
        $post->save();
      }

      if( !empty($values['photo']) ) {
        $post->setPhoto($form->photo);
      }

      $auth = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($topic, 'registered', 'create', true);

      // Create topic watch
      $topicWatchesTable->insert(array(
        'resource_id' => $forum->getIdentity(),
        'topic_id' => $topic->getIdentity(),
        'user_id' => $viewer->getIdentity(),
        'watch' => (bool) $values['watch'],
      ));

      // Add activity
      $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $topic, 'forum_topic_create');
      if( $action ) {
        $action->attach($topic);
      }

      //Start Send Approval Request to Admin
      Engine_Api::_()->core()->contentApprove($topic, 'forum topic');
      
      $db->commit();

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'success_message' => $this->view->translate('Topic created successfully.'))));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
  }
  
  public function forumviewAction() {

    $forum_id = $this->_getParam('forum_id', null);
    //$query = $this->_getParam('query', null);
    if(empty($forum_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

    $forum = Engine_Api::_()->getItem('forum', $forum_id);

    if(!$this->_helper->requireAuth->setAuthParams($forum, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    
    $canPost = $forum->authorization()->isAllowed(null, 'topic.create');
    
     // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }

    // Make paginator
    $table = Engine_Api::_()->getItemTable('forum_topic');
    $select = $table->select()
      ->where('forum_id = ?', $forum->getIdentity())
      ->order('sticky DESC')
      ->order($order . ' DESC');

    if ($this->_getParam('search', false)) {
      $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
    }

    $paginator = Zend_Paginator::factory($select);

    $page = (int)  $this->_getParam('page', 1);

    $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_forum_pagelength'));
    $paginator->setCurrentPageNumber($page);

    $result = array();
    $counterLoop = 0;

    foreach($paginator as $topics) {

      $topic = $topics->toArray();
      $description = strip_tags($topics['description']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($topic['description']);

      $owner = Engine_Api::_()->getItem('user',$topics->user_id);
      $topic['owner_title'] = $owner->getTitle();
      $topic['description'] = $description;
      $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
      if(empty($owner->photo_id)) {
        $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
        $ownerimage['main'] = $defPhoto;
        $ownerimage['icon'] = $defPhoto;
        $ownerimage['normal'] = $defPhoto;
        $ownerimage['profile'] = $defPhoto;
      }
      $topic['owner_image'] = $ownerimage; //$this->userImage($topics->user_id,"thumb.icon");
      $topic['resource_type'] = $topics->getType();

      $last_post = $topics->getLastCreatedPost();
      if( $last_post ) {
        $last_user = Engine_Api::_()->getItem('user', $last_post->user_id); //$this->user($last_post->user_id);
      } else {
        $last_user = Engine_Api::_()->getItem('user', $topics->user_id); //$this->user($topics->user_id);
      }
      $lastPostCount = 0;
      if( $last_post) {
        $topic['last_post'][$lastPostCount]['user_images'] = $this->userImage($last_user->user_id,"thumb.icon");
        $topic['last_post'][$lastPostCount]['user_id'] = $last_user->getIdentity();
        $topic['last_post'][$lastPostCount]['user_title'] = $last_user->getTitle();
        $topic['last_post'][$lastPostCount]['creation_date'] = $topics->modified_date;
        $lastPostCount++;
      }
      $result['topics'][$counterLoop] = $topic;
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($topics,'','');
      if(!engine_count($images))
        $images['main'] = $this->getBaseUrl(true,$topics->getPhotoUrl());
      $result['topics'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }

    $list = $forum->getModeratorList();
    $moderators = $list->getAllChildren();

    $moderator = $this->view->fluentList($moderators);
    $moderator_count = 0;
    $result['moderators'][$moderator_count]['label'] = $this->view->translate("Moderators");
    $result['moderators'][$moderator_count]['moderators'] = $moderator;
    if($canPost) {
      $result['moderators'][$moderator_count]['topic_create'] = $this->view->translate("Post New Topic");
    }
    $result['moderators'][$moderator_count]['forum_title'] = $forum->title;
    $moderator_count++;
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist topics.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function indexAction() {
  
    if ( !$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    
    $categoryTable = Engine_Api::_()->getItemTable('forum_category');
    $categories = $categoryTable->fetchAll($categoryTable->select()->order('order ASC'));
    
    $forumTable = Engine_Api::_()->getItemTable('forum_forum');
    $forumSelect = $forumTable->select()->order('order ASC');

    $forums = array();
    foreach( $forumTable->fetchAll() as $forum ) {
      if( Engine_Api::_()->authorization()->isAllowed($forum, null, 'view') ) {
        $order = $forum->order;
        while( isset($forums[$forum->category_id][$order]) ) {
          $order++;
        }
        $forums[$forum->category_id][$order] = $forum;
        ksort($forums[$forum->category_id]);
      }
    }
    $forums = $forums;

    $results = array();
    $counter = 0;
    
    foreach( $categories as $category ) {
      if( empty($forums[$category->category_id]) ) {
        continue;
      }
      $results['categories'][$counter]['category_name'] = $this->view->translate($category->title);
      $results['categories'][$counter]['category_id'] = $category->getIdentity();
      $results['categories'][$counter]['type'] = 'category';
    
      $forumCounter = 0;
      foreach( $forums[$category->category_id] as $forum ) {
        $results['categories'][$counter]['forums'][$forumCounter]['forum_id'] = $forum->getIdentity();
        $results['categories'][$counter]['forums'][$forumCounter]['title'] = $this->view->translate($forum->getTitle());
        $results['categories'][$counter]['forums'][$forumCounter]['icon'] = $this->getBaseUrl(true, 'application/modules/Forum/externals/images/forum.png');
        $results['categories'][$counter]['forums'][$forumCounter]['description'] = $forum->description;
        $results['categories'][$counter]['forums'][$forumCounter]['topic_count'] = $forum->topic_count;
        $results['categories'][$counter]['forums'][$forumCounter]['post_count'] = $forum->post_count;
        $forumCounter++;
      }
      $counter++;
    }

    if($results <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $results)));
  }

}
