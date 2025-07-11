<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Controller.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Widget_FeedController extends Engine_Content_Widget_Abstract
{
  private $_blockedUserIds = array();

  public function indexAction() {
  
    $widgetIds = $this->_getParam('widgetIds', 0);
    if ($widgetIds) {
      $params = Engine_Api::_()->sescommunityads()->getWidgetParams($widgetIds);
      $request = $this->getRequest();
      $request->setParams($params);
      $_SESSION['fromActivityFeed'] = 1;
    }
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    //community ads ids
    $this->view->communityadsIds = $this->_getParam('ads_ids', false);
    $this->view->isGoogleApiKeySaved = ($settings->getSetting('core.mapApiKey', '') && $settings->getSetting('enableglocation', '1')) ? true : false;
    
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = null;
    if( Engine_Api::_()->core()->hasSubject() ) {
      // Get subject
      $subject = Engine_Api::_()->core()->getSubject();
      
      if($subject->getType() == 'group') { 
        if( !$subject->authorization()->isAllowed($viewer, 'view') && !Engine_Api::_()->network()->getViewerNetworkPrivacy($subject, 'user_id')) {
          return $this->setNoRender();
        }
      } else {
        if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
          return $this->setNoRender();
        }
      }
    }
    
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $requestParams = $request->getParams();

    // Pinfeed
    $this->view->feeddesign = $this->_getParam('feeddesign', 1);
    $this->view->widthPa = $this->_getParam('widthpinboard', '250');
    $this->view->widgetTitle = $this->_getParam('title', '');
    $this->view->isMemberHomePage = isset($_REQUEST['isMemberHomePage']) ? $_REQUEST['isMemberHomePage'] : $requestParams['action'] == 'home' && $requestParams['module'] == 'user' && $requestParams['controller'] == 'index';
    $this->view->isLandingPage = isset($_REQUEST['isOnLandingPage']) ? $_REQUEST['isOnLandingPage'] : $requestParams['action'] == 'index' && $requestParams['module'] == 'core' && $requestParams['controller'] == 'index';
    $this->view->isOnThisDayPage = isset($_REQUEST['isOnThisDayPage']) ? $_REQUEST['isOnThisDayPage'] : $requestParams['action'] == 'onthisday' && $requestParams['module'] == 'activity' && $requestParams['controller'] == 'index';
    
    if ($this->view->isOnThisDayPage)
      $subject = Engine_Api::_()->user()->getViewer();
      
    $this->view->advcomment = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('comment');
    
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    
    $this->view->usersettings = $settings->getSetting($viewer->getIdentity() . '.activity.user.setting', 'everyone');
    
    // Get some options
    $this->view->design = $this->_getParam('design', 2);
    $this->view->design = $this->view->design > 2 ? 2 : $this->view->design;
    $this->view->enablestatusbox = $enablestatusbox = $this->_getParam('enablestatusbox', 2);
    if (@$enablestatusbox == '') {
      $this->view->enablestatusbox = 2;
    }
    $this->view->userphotoalign = $this->_getParam('userphotoalign', 'left');
    $this->view->activity_pinboard_width = $this->_getParam('activity_pinboard_width', '300');

    $this->view->allowprivacysetting = 1;
    $this->view->contentCount = $this->_getParam('contentCount', 0);
    $this->view->autoloadfeed = $settings->getSetting('activity.autoloadfeed', 1);
    $this->view->submitWithAjax = true;
    $this->view->filterFeed = $filterFeed = $this->_getParam('filterFeed', 'all');
    if ($subject && empty($_POST) && $filterFeed == "all") {
      $this->view->filterFeed = $filterFeed = "own";
    }
    $this->view->scrollfeed = $this->_getParam('scrollfeed', 1);
    $this->view->autoloadTimes = $this->_getParam('autoloadTimes', 3);
    if (!$this->view->autoloadTimes)
      $this->view->autoloadTimes = 100000000;
    $this->view->enableStatusBoxHighlight = $settings->getSetting('activity.highlightstatusbox', 0);
    
    $this->view->feedOnly = $feedOnly = $request->getParam('feedOnly', false);
    $this->view->length = $length = $request->getParam('limit', $settings->getSetting('activity.length', 15));
    $this->view->itemActionLimit  = $itemActionLimit = 1000;
    $this->view->updateSettings = 120000;
    $this->view->viewAllLikes = $request->getParam('viewAllLikes', $request->getParam('show_likes', false));
    $this->view->viewAllComments = $request->getParam('viewAllComments', $request->getParam('show_comments', false));
    $this->view->getUpdate = $request->getParam('getUpdate');
    $this->view->checkUpdate = $request->getParam('checkUpdate');
    $this->view->action_id = $this->_getParam('action_id', (int) $request->getParam('action_id'));
    $this->view->post_failed = (int) $request->getParam('pf');
    $composePartials = $composerOptions = array();
    if ($feedOnly) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    
    $this->view->allowlistprivacy = 1;
    $this->view->allownetworkprivacy = $allownetworkprivacytype = $settings->getSetting('activity.network.privacy', 0);
    if ($allownetworkprivacytype == 1) {
      $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
    } else if ($allownetworkprivacytype == 2) {
      $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
    } else {
      $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->where(0);
    }
    
    $this->view->usernetworks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
    $this->view->userlists = Engine_Api::_()->getDbtable('lists', 'user')->fetchAll(Engine_Api::_()->getDbtable('lists', 'user')->select()->order('engine4_user_lists.title ASC')->where('owner_id =?', $viewer->getIdentity()));
    $this->view->networkbasedfilter = false;
    
    //network based filtering
    if ($allownetworkprivacytype != 2) {
      if ($allownetworkprivacytype == 1) {
        $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
      } else {
        $select = Engine_Api::_()->getDbtable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
      }
      $this->view->networkbasedfilter = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
    }
    
    // Assign the composing values
    $this->view->composerOptions = $composerOptions = $settings->getSetting('activity.composeroptions', array());
    
    $notArray = array();
    if (!engine_in_array('buysell', $composerOptions)) {
      $notArray[] = 'post_self_buysell';
    }
    if (!engine_in_array('fileupload', $composerOptions)) {
      $notArray[] = 'post_self_file';
    }
    $networkbasedfilter = $this->view->networkbasedfilter;
    $activeLists = Engine_Api::_()->getDbTable('filterlists', 'activity')->getLists($notArray);

    $lists = $activeLists->toArray();
    //check module enable
    $listsArray = array();
    foreach ($lists as $list) {
      if (!$this->view->viewer()->getIdentity() && ($list['filtertype'] == "scheduled_post" || $list['filtertype'] == "my_networks" || $list['filtertype'] == "my_friends" || $list['filtertype'] == "saved_feeds" || $list['filtertype'] == "member" || $list['filtertype'] == "share"))
        continue;
      if ($list['filtertype'] != 'all' && $list['filtertype'] != 'scheduled_post' && $list['filtertype'] != 'my_networks' && $list['filtertype'] != 'my_friends' && $list['filtertype'] != 'posts' && $list['filtertype'] != 'saved_feeds' && $list['filtertype'] != 'share' && $list['filtertype'] != 'post_self_buysell' && $list['filtertype'] != 'post_self_file' && !Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($list['filtertype']))
        continue;
      $listsArray[] = $list;
    }
    if ($networkbasedfilter) {
      $networkbasedfilter = $networkbasedfilter->toArray();
      foreach ($networkbasedfilter as $networkbased) {
        $listsArray[] = $networkbased;
      }
    }

    if (engine_count($this->view->userlists)) {
      $listbasedfilter = $this->view->userlists->toArray();
      foreach ($listbasedfilter as $listbased) {
        $listsArray[] = $listbased;
      }
    }
    
    if (empty($_POST))
      $this->view->filterFeed = $filterFeed = $listsArray[0]['filtertype'];
    if ($subject && empty($_POST) && $filterFeed == "all") {
      $this->view->filterFeed = $filterFeed = "own";
    }
    
    $this->view->lists = $listsArray;

    if ($length > 50) {
      $this->view->length = $length = 50;
    }
    
    if ($viewer && !$viewer->isAdmin()) {
      $this->_blockedUserIds = $viewer->getAllBlockedUserIds();
    }

    // Get all activity feed types for custom view?
    $actionTypesTable = Engine_Api::_()->getDbtable('actionTypes', 'activity');
    $this->view->groupedActionTypes = $groupedActionTypes = $actionTypesTable->getEnabledGroupedActionTypes();
    $actionTypeGroup = $filterFeed;
    $actionTypeFilters = array();

    //followig work
    $isMember = $actionTypeGroup == 'member' && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.followenable', 1);
    if (!$isMember) {
      if ($actionTypeGroup && isset($groupedActionTypes[$actionTypeGroup])) {
        $actionTypeFilters = $groupedActionTypes[$actionTypeGroup];
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') &&  ($actionTypeGroup == 'sesalbum' || $actionTypeGroup == 'album'))
          $actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['photo']);
        else if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesvideo') && $actionTypeGroup == 'sesvideo')
          $actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['video']);
      }
    }

    if ($actionTypeGroup == 'post_self_buysell')
      $actionTypeFilters = array('post_self_buysell');
    else if ($actionTypeGroup == 'post_self_file')
      $actionTypeFilters = array('post_self_file');
    //else if(strpos($actionTypeGroup , 'network_filter_' ) !== false)
    // $actionTypeFilters = array('network');

    // Get config options for activity
    $hashTag = isset($_GET['hashtag']) ? $_GET['hashtag'] : (@$_GET['search'] ? @$_GET['search'] : '');
    
    $config = array(
      'action_id' => $this->view->action_id,
      'max_id' => (int) $request->getParam('maxid'),
      'min_id' => (int) $request->getParam('minid'),
      'limit' => (int) $length,
      'showTypes' => $actionTypeFilters,
      'filterFeed' => $filterFeed,
      'hashTag' => $hashTag,
      'targetPost' => engine_in_array('activitytargetpost', $composerOptions),
      'isOnThisDayPage' => $this->view->isOnThisDayPage,
    );

    // Pre-process feed items
    $selectCount = 0;
    $nextid = null;
    $firstid = null;
    $tmpConfig = $config;
    $activity = array();
    $endOfFeed = false;
    $friendRequests = array();
    $itemActionCounts = array();
    $enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    $actions = null;
    do {
      // Get current batch
      if ($this->_getParam("getUpdates") || $this->view->action_id) {

        $this->view->getUpdates = 1;
        // Where the Activity Feed is Fetched
        if ($this->_getParam("myAdContent")) {

          $actionTableName = $actionTable->info('name');
          $actions = $actionTable->fetchAll(
                    $actionTable->select()
                      ->from($actionTable->info('name'), '*')
                      ->setIntegrityCheck(false)
                      ->where($actionTableName . '.action_id IN(' . $this->view->action_id . ')')
                      ->order($actionTableName . '.action_id DESC')
                      ->limit(1)
          );
        } else if (!empty($subject)) {
          $actions = $actionTable->getActivityAbout($subject, $viewer, $tmpConfig);
        } else {
          $actions = $actionTable->getActivity($viewer, $tmpConfig);
        }
      } else {
        Engine_Hooks_Dispatcher::getInstance()->callEvent('onLoadFeedWidget', $this->view);
        $this->view->getUpdates = 0;
      }
      $selectCount++;
     
      // Are we at the end?
      if( !$actions || engine_count($actions) < $length || engine_count($actions) <= 0 ) {
        $endOfFeed = true;
      }

      // Pre-process
      if (!empty($actions) && engine_count($actions) > 0) {
        foreach ($actions as $action) {
          try{
          if (isset($action->group_action_id)) {
            $action_id = $action->group_action_id;
            $explodedData = explode(',', $action_id);
            if ($explodedData > 1) {
              $action_id = max($explodedData);
              $action = Engine_Api::_()->getItem('activity_action', $action_id);
            }
          } else {
            $action_id = $action->action_id;
          }

          $isPinned = false;
          $pinSubject = !$this->view->subject() ? false : $this->view->subject();
          if ($pinSubject)
            $isPinned = $action->isPinPost(array('resource_type' => $pinSubject->getType(), 'resource_id' => $pinSubject->getIdentity(), 'action_id' => $action->getIdentity()));
          // get next id
          if ((null === $nextid || $action_id <= $nextid) && !$isPinned) {
            $nextid = $action->action_id - 1;
          }
          // get first id
          if (null === $firstid || $action_id > $firstid) {
            $firstid = $action_id;
          }
          // skip disabled actions
          if( !$action->getTypeInfo() || !$action->getTypeInfo()->enabled ) continue;
          // skip items with missing items
          if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
          if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
          
          // remove items with disabled module attachments
          try {
            $attachments = $action->getAttachments();
          } catch( Exception $e ) {
            // if a module is disabled, getAttachments() will throw an Engine_Api_Exception; catch and continue
            continue;
          }
          
          //View Permission
          if($action->getObject()) {
            $object = $action->getObject();
            if(!empty($action->attachment_count) && $action->getFirstAttachment() && $action->getFirstAttachment()->item->getType() != 'storage_file') {
              $object = $action->getFirstAttachment()->item;
            }
            $objectParent = null;
            //Check object is approved
            if(isset($object->approved) && empty($object->approved)) continue;
            
            if($object->getType() == 'album_photo') {
              $objectParent = $object->getParent();
              if(isset($objectParent->approved) && empty($objectParent->approved)) continue;
            } else if($object->getType() == 'music_playlist_song') {
              $objectParent = $object->getParent();
              if(isset($objectParent->approved) && empty($objectParent->approved)) continue;
            } else if($object->getType() == 'sesforum_topic') {
              $objectParent = $object->getParent();
            } else if($object->getType() == 'sesforum_post') {
              $objectParent = $object->getParent();
              $objectParent = $objectParent->getParent();
            } else if($object->getType() == 'activity_action') {
              unset($tmpConfig['filterFeed']);
              $tmpConfig['action_id'] = $object->action_id;
              $actionsTemp = $actionTable->getActivity($viewer, $tmpConfig);
              unset($tmpConfig['action_id']);
              if(!$actionsTemp) {
                continue;
              }
            }
            
            if(@$objectParent) {
              $viewPermission = $objectParent->authorization()->isAllowed($viewer, 'view');
            } else {
              $viewPermission = $object->authorization()->isAllowed($viewer, 'view');
            }
            // object which don't have view page must be mentioned here.

            if(!$viewPermission && $object->getType() != "core_link" && $object->getType() != 'activity_buysell' && $object->getType() != 'activity_file' && $object->getType() != 'sesmusic_playlist') {
              continue;
            }            
            
            if(isset($object->networks) && !empty($object->networks)) {
              if(isset($object->user_id)) 
                $userId = 'user_id';
              else
                $userId = 'owner_id';
              if(!Engine_Api::_()->network()->getViewerNetworkPrivacy($object, $userId)) continue;
            }
          }

          // track/remove users who do too much (but only in the main feed)
          $actionObject = $action->getObject();
          if( empty($subject) ) {
            $actionSubject = $action->getSubject();
            if( !isset($itemActionCounts[$actionSubject->getGuid()]) ) {
              $itemActionCounts[$actionSubject->getGuid()] = 1;
            } elseif( $itemActionCounts[$actionSubject->getGuid()] >= $itemActionLimit ) {
              continue;
            } else {
              $itemActionCounts[$actionSubject->getGuid()]++;
            }
          }

          if( $this->isBlocked($action) ) {
            continue;
          }

          //privacy for fourm
          if(in_array($action->type, array('forum_topic_create','forum_topic_reply'))) {
						if($viewer->getIdentity())
							$levelId = $viewer->level_id;
						else
							$levelId = 5;
							
						if($action->getObject()->getType() == 'forum_topic') {
							$forumItem = Engine_Api::_()->getItem('forum_forum', $action->getObject()->forum_id);
              if(!empty($forumItem->levels)){
                $levels = explode(',', $forumItem->levels);
                if(!engine_in_array($levelId, $levels)) continue;
              }
						} else if($action->getObject()->getType() == 'forum_post') {
							$forumItem = Engine_Api::_()->getItem('forum_forum', $action->getObject()->forum_id);
              if(!empty($forumItem->levels)){
                $levels = explode(',', $forumItem->levels);
                if(!engine_in_array($levelId, $levels)) continue;
              }
						}
          }

          // remove duplicate friend requests
          if( $action->type == 'friends' ) {
            $id = $action->subject_id . '_' . $action->object_id;
            $rev_id = $action->object_id . '_' . $action->subject_id;
            if( engine_in_array($id, $friendRequests) || engine_in_array($rev_id, $friendRequests) ) {
              continue;
            } else {
              $friendRequests[] = $id;
              $friendRequests[] = $rev_id;
            }
          }
          
          
          $similarFeedType = $action->type . '_' . $actionObject->getGuid();
          if( $action->canMakeSimilar() ) {
            $similarActivities[$similarFeedType][] = $action;
          }
          if( isset($similarActivities[$similarFeedType]) && engine_count($similarActivities[$similarFeedType]) > 1 ) {
            continue;
          }
          // add to list
          if (engine_count($activity) < $length) {
            $activity[] = $action;
            if (engine_count($activity) == $length) {
              break;
            }
          }
          }catch(Exception $e) {
            echo $e->getMessage();die;
          }
        }
      
      }
      
      // Set next tmp max_id
      if ($nextid) {
        $tmpConfig['max_id'] = $nextid;
      }
      if (!empty($tmpConfig['action_id'])) {
        $actions = array();
      }
    }
    while (engine_count($activity) < $length && $selectCount <= 3 && !$endOfFeed);

    $this->view->activity = $activity;
    $this->view->activityCount = engine_count($activity);
    $this->view->nextid = $nextid;
    $this->view->firstid = $firstid;
    $this->view->endOfFeed = $endOfFeed;
    $this->view->hashtag = $this->getHashtagNames($activity);
    
    // Get some other info
    if (!empty($subject)) {
      $this->view->subjectGuid = $subject->getGuid(false);
    }

    $this->view->enableComposer = false;
    if( $viewer->getIdentity() && !$this->_getParam('action_id') && !$this->view->action_id ) {
      if( !$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($viewer)) ) {
        if( Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status') ) {
          $this->view->enableComposer = true;
        }
      } elseif( $subject ) {
        if( Engine_Api::_()->authorization()->isAllowed($subject, $viewer, 'comment') ) {
          $this->view->enableComposer = true;
        }

        $postActionType = 'post_' . $subject->getType();
        $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
        if ($actionType && !$actionType->enabled) {
          $this->view->enableComposer = false;
        }
      }
      //status privacy check
      if($subject && $subject->getType() == 'user' && !Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status') && !$subject->isSelf($viewer)) {
        $this->view->enableComposer = false;
      }
    }

    foreach (Zend_Registry::get('Engine_Manifest') as $key => $data) {
      if (empty($data['composer'])) {
        continue;
      }
      foreach ($data['composer'] as $type => $config) {
        if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
          continue;
        }
        // if ($type == 'photo' && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
        //   $config['script'][0] = '_composeadvancedactivityphoto.tpl';
        //   $config['script'][1] = 'activity';
        // }
        $composePartials[$type] = $config['script'];
      }
    }

    $composePartialsArray = array();
    //default set the values and attachment removes from the view tpl.
    foreach ($composerOptions as $composerSetting) {
      if (isset($composePartials[$composerSetting]))
        $composePartialsArray[$composerSetting] = $composePartials[$composerSetting];
    }

    //remove Key from array
    $arrayRemove = array("album" => 'album', "buysell" => 'buysell', "activitytargetpost" => 'activitytargetpost', "fileupload" => 'fileupload', "albumvideo" => 'albumvideo');

    if ($subject && method_exists($subject, 'activityComposerOptions')) {
      $allowedExtentions = $subject->activityComposerOptions($subject);
      $composePartialsArrayDiff = array();
      foreach ($composePartialsArray as $key => $partials) {
        if (array_key_exists($key, $allowedExtentions)) {
          $composePartialsArrayDiff[$key] = $partials;
        }
      }
      $composePartialsArray = $composePartialsArrayDiff;
      $this->view->composerOptions = $allowedExtentions;
    } else {
      if ((!$this->view->isMemberHomePage && ($subject && !engine_in_array($subject->getType(), array('user', 'group', 'event'))))) {
        foreach ($arrayRemove as $key) {
          unset($composePartialsArray[$key]);
        }
        $this->view->composerOptions = array_diff($composerOptions, array("tagUseActivity", "locationactivity", "shedulepost"));
      }
    }

    if (empty($subject) || $viewer->isSelf($subject)) {
      // Get Feed Privacy List
      $defaultViewPrivacy = array(
        'everyone' => 'Everyone',
        'networks' => 'Friends or Networks',
        'friends' => 'Friends Only',
        'onlyme' => 'Only Me',
      );
      $viewPrivacyLists = $settings->getSetting('activity.view.privacy');
      if (!empty($viewPrivacyLists)) {
        foreach ($viewPrivacyLists as $viewPrivacy) {
          if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1) && $viewPrivacy == 'networks') continue;
          $privacyArray[$viewPrivacy] = $defaultViewPrivacy[$viewPrivacy];
        }
        $this->view->defaultPrivacyLabel = reset($privacyArray);
      }

      $enableNetworkList = $settings->getSetting('activity.network.privacy', 0);
      if ($enableNetworkList) {
        $networkLists = Engine_Api::_()->activity()->getNetworks($enableNetworkList, $viewer);

        if ((is_array($networkLists) || is_object($networkLists)) && engine_count($networkLists)) {
          foreach ($networkLists as $network) {
            $networkArray["network_" . $network->getIdentity()] = $network->getTitle();
          }
          $this->view->defaultPrivacyLabel = $this->view->defaultPrivacyLabel ?: reset($networkArray);
        }
      }
    }
    if (!empty($composePartialsArray['elivestreaming'])) {
      $data = $composePartialsArray['elivestreaming'];
      unset($composePartialsArray['elivestreaming']);
      array_unshift($composePartialsArray, $data);
    }
    
    $this->view->composePartials = $composePartialsArray;
    $this->view->photoActivator = array_key_exists('photo', $composePartialsArray);
    $this->view->albumActivator = array_key_exists('album', $composePartialsArray);
    $this->view->videoActivator = array_key_exists('video', $composePartialsArray);
    $this->view->liveStreamActivator = array_key_exists('elivestreaming', $composePartialsArray);
    // Form token
    $session = new Zend_Session_Namespace('ActivityFormToken');
    //$session->setExpirationHops(10);
    if (empty($session->token)) {
      $this->view->formToken = $session->token = md5(time() . $viewer->getIdentity() . get_class($this));
    } else {
      $this->view->formToken = $session->token;
    }
  }
  
  private function isBlocked($action)
  {

    if( empty($this->_blockedUserIds) ) {
      return false;
    }
    $actionObjectOwner = $action->getObject()->getOwner();
    $actionSubjectOwner = $action->getSubject()->getOwner();
    if( $actionSubjectOwner instanceof User_Model_User && engine_in_array($actionSubjectOwner->getIdentity(), $this->_blockedUserIds) ) {
      return true;
    }
    if( $actionObjectOwner instanceof User_Model_User && engine_in_array($actionObjectOwner->getIdentity(), $this->_blockedUserIds) ) {
      return true;
    }
    return false;
  }

  private function getHashtagNames($activity)
  {
    if (!empty(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')) && !engine_in_array('hashtags', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return;
    }

    $hashTagMapTable = Engine_Api::_()->getDbtable('tagMaps', 'core');
    $hashtagNames = array();
    foreach( $activity as $action ) {
      $hashtagName = array();
      $hashtagmaps = array();
      $object = Engine_Api::_()->getItem($action->object_type, $action->object_id);
      if (method_exists($object, 'tags')) {
        $hashtagmaps = $object->tags()->getTagMaps()->toArray();
      }

      if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') && $action->object_type == 'album') {
        $hashtagmaps = $this->getAlbumPhotoTagMaps($action);
      }

      if (empty($hashtagmaps)) {
        continue;
      }

      foreach( $hashtagmaps as $hashtagmap ) {
        $tag = Engine_Api::_()->getItem('core_tag', $hashtagmap['tag_id']);
        if ($tag && false === array_search('#' . $tag->text, $hashtagName)) {
          $hashtagName[] = '#' . $tag->text;
        }
      }
      $hashtagNames[$action['action_id']] = $hashtagName;
    }
    return $hashtagNames;
  }

  private function getAlbumPhotoTagMaps($action)
  {
    if (empty($action)) {
      return;
    }

    $tagMapsArray = array();
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentTable->select()
      ->where('action_id = ? ', $action->action_id);
    $attachments = $attachmentTable->fetchAll($select);

    foreach ($attachments as $attachment) {
      $tagMaps = array();
      $object = Engine_Api::_()->getItem($attachment->type, $attachment->id);
      if ($object && method_exists($object, 'tags')) {
        $tagMaps = $object->tags()->getTagMaps()->toArray();
      }

      $tagMapsArray = array_merge($tagMaps, $tagMapsArray);
    }
    return $tagMapsArray;
  }
}
