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
class Activity_IndexController extends Sesapi_Controller_Action_Standard {

  public function gifAction() {
    $emessages_gif_search = $this->_getParam('emessages_gif_search', '');

    $giphyApi = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.giphyapi', '');
    $result = array();
    $counterLoop = 0;
    if(empty($giphyApi)) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Gify API key not set!'), 'result' => array()));
    } else {
      if($emessages_gif_search) {
        $url = 'https://api.giphy.com/v1/gifs/search?api_key='.$giphyApi.'&limit='.$limit.'&rating=G&q='.urldecode($emessages_gif_search);
      } else {
        $url = 'https://api.giphy.com/v1/gifs/trending?api_key='.$giphyApi.'&limit='.$limit.'&rating=G';
      }

      $gifData = json_decode(file_get_contents($url),true);
      
      if(engine_count($gifData['data']) > 0) {
        foreach($gifData['data'] as $data) {
          $result['gif'][$counterLoop]['img_url'] = $data['images']['original']['url'];
          $counterLoop++;
        }
      }
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }
  
	function composerOptionsAction(){
		if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
			$this->coreactivity();
		}else{
            if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
                $this->coreactivity();
            }
		}
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => ''));
	}
	
  function coreactivity()
  {
    $contentResponse = array();
	$networkbasedfilter = "";
    $request = Zend_Controller_Front::getInstance()->getRequest();
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = $this->_getParam('resource_type', '');
    $resource_id = $this->_getParam('resource_id', '');
    // Get permission setting
    if ($viewer->getIdentity() != 0) {
      $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
      if (Authorization_Api_Core::LEVEL_DISALLOW === $permission) {
      $messageText = "message_denied";
      }
    }
    if ($subject) {
      // Get subject
      $subject = Engine_Api::_()->getItem($subject, $resource_id);
      if ($subject)
      Engine_Api::_()->core()->setSubject($subject);
      if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => !empty($messageText) ? $messageText : "Invalid Request"));
      }
    }
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
      $recArray = array();
      $reactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getPaginator();
      $counterReaction = 0;
      foreach ($reactions as $reac) {
      if (!$reac->enabled)
        continue;
      $contentResponse['reaction_plugin'][$counterReaction]['reaction_id']  = $reac['reaction_id'];
      $contentResponse['reaction_plugin'][$counterReaction]['title']  = $this->view->translate($reac['title']);
      $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
      $contentResponse['reaction_plugin'][$counterReaction]['image']  = $icon['main'];
      $counterReaction++;
      }
    }
    $emojiText = "";
    $contentResponse['defaultCurrency'] = Engine_Api::_()->payment()->getCurrencySymbol();
    $contentResponse['sesfeelingactivity'] = 0;
    $emojiPluginEnable = 1;
    if ($emojiPluginEnable) {
      if (true) {
      $feelingEnable = true;
      $emojiText = $this->view->translate("Feeling/Activity/Sticker");
      } else {
      $emojiText = $this->view->translate("Activity/Sticker");
      }
      $contentResponse['sesfeelingactivity'] = 1;
    } else {
      $contentResponse['sesfeelingactivity'] = 1;
      $emojiText = $this->view->translate("Sticker");
    }
    if ($viewer->getIdentity()) {
      $contentResponse['user_image'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
      $contentResponse['user_id'] = $viewer->getIdentity();
      $contentResponse['user_title'] = $viewer->getTitle();
    }

    $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    $usersettings =  rtrim(Engine_Api::_()->getApi('settings', 'core')->getSetting($viewer->getIdentity() . '.activity.user.setting', 'everyone'), ',');
    $explodedSettings = explode(',', $usersettings);
    $contentResponse['userSelectedSettings'] = $explodedSettings;

    $this->view->allowprivacysetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.allowprivacysetting', 1);
    if (!$this->view->allowprivacysetting)
      $contentResponse['privacySetting'] =  false;
    else
      $contentResponse['privacySetting'] =  true;
    $contentResponse['privacyOptions'] = Engine_Api::_()->sesapi()->privacyOptions();
	$feedSearchOptions = array();
	if (!$subject) {
	  $allownetworkprivacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy',0);
	  $allowlistprivacy = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.allowlistprivacy', 1);
	  
    if($allownetworkprivacy == 1){
        $select = Engine_Api::_()->getDbTable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
    }
    else if($allownetworkprivacy == 2){
      $select = Engine_Api::_()->getDbTable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
    }else{
      $select = Engine_Api::_()->getDbTable('networks', 'network')->select()->where(0);
    }
	  $usernetworks = Engine_Api::_()->getDbTable('networks', 'network')->fetchAll($select);

	  if (_SESAPI_VERSION_ANDROID >= 1.2) {
      $enableVideo = 1;
	  }
	  if (_SESAPI_VERSION_IOS >= 1.2) {
      $enableVideo = 1;
	  }

	  if ($allownetworkprivacy && engine_count($usernetworks) && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
      $networkOptions = array();
      $counterVal =  0;
      foreach ($usernetworks as $networkfilter) {
        if ($counterVal == 0)
        // $networkOptions[$counterVal]['first'] = 1;
        $networkOptions[$counterVal]['name'] = "network_list_" . $networkfilter->getIdentity();
        $networkOptions[$counterVal]['value'] = $this->view->translate($networkfilter["title"]);
        $counterVal++;
      }
      $contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $networkOptions);
	  }

	  //network based filtering
	  $networkbasedfiltering = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.networkbasedfiltering', 1);
	  if ($networkbasedfiltering != 2) {
      if ($networkbasedfiltering == 1) {
        $select = Engine_Api::_()->getDbTable('membership', 'network')->getMembershipsOfSelect($viewer)->order('engine4_network_networks.title ASC');
      } else {
        $select = Engine_Api::_()->getDbTable('networks', 'network')->select()->order('engine4_network_networks.title ASC');
      }
      $networkbasedfilter = Engine_Api::_()->getDbTable('networks', 'network')->fetchAll($select);
	  }

	  //user list
	  $userlists = Engine_Api::_()->getDbTable('lists', 'user')->fetchAll(Engine_Api::_()->getDbTable('lists', 'user')->select()->order('engine4_user_lists.title ASC')->where('owner_id =?', $viewer->getIdentity()));

	  if (engine_count($userlists) && $allowlistprivacy  && (_SESAPI_VERSION_ANDROID >= 2.3 || _SESAPI_VERSION_IOS >= 1.3)) {
      $listsOptions = array();
      $counterVal =  0;
      foreach ($userlists as $listsOption) {
        //if ($counterVal == 0)
        // $listsOptions[$counterVal]['first'] = 1;
        $listsOptions[$counterVal]['name'] = "members_list_" . $listsOption->getIdentity();
        $listsOptions[$counterVal]['value'] = $this->view->translate($listsOption["title"]);
        $counterVal++;
      }
      $contentResponse['privacyOptions'] = array_merge($contentResponse['privacyOptions'], $listsOptions);
	  }
	  $activeLists = Engine_Api::_()->getDbTable('filterlists', 'activity')->getLists(array());
	  $lists = $activeLists->toArray();
	  $levelAdapter = Engine_Api::_()->authorization()->getAdapter('levels');
      $enableComposers = (array) $levelAdapter->getAllowed('activity', $this->view->viewer(), 'composeroptions');
	  //check module enable
	  $listsArray = array();
	  foreach ($lists as $list) {
		if ($viewer->getIdentity() == 0 && ($list['filtertype'] == "my_friends" || $list['filtertype'] == "scheduled_post"  || $list['filtertype'] == "saved_feeds" || $list['filtertype'] == "my_networks")) {
		  continue;
		}
		if ($list['filtertype'] != 'all' && $list['filtertype'] != 'scheduled_post' && $list['filtertype'] != 'my_networks' && $list['filtertype'] != 'my_friends' && $list['filtertype'] != 'posts' && $list['filtertype'] != 'saved_feeds' && $list['filtertype'] != 'post_self_buysell'  && $list['filtertype'] != 'post_self_file' && !Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled($list['filtertype']))
		  continue;

		// if($list['filtertype'] == "scheduled_post" && !engine_in_array('shedulepost', $enableComposers)){
		//     continue;
		// }
		$listsArray[] = $list;
	  }
	  if ($networkbasedfilter) {
		foreach ($networkbasedfilter as $filterBased) {
		  $listsArray[] = $filterBased;
		}
	  }
	  if (engine_count($userlists)) {
		foreach ($userlists as $filterbased) {
		  $listsArray[] = $filterbased;
		}
	  }
	  $filterFeed = $listsArray[0]['filtertype'];
	  
	  $counter = 0;
	  foreach ($listsArray as $searchOptions) {
		if (isset($searchOptions['network_id'])) {
		  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/networks.png');
		  $feedSearchOptions[$counter]['key'] = 'network_filter_' . $searchOptions['network_id'];
		  $feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		  $counter++;
		  continue;
		} else if (isset($searchOptions['list_id'])) {
		  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
		  $feedSearchOptions[$counter]['key'] = 'member_list_' . $searchOptions['list_id'];
		  $feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		  $counter++;
		  continue;
		}
		if (!empty($searchOptions['file_id'])) {
		  $storage = Engine_Api::_()->storage()->get($searchOptions['file_id'], '');
		  if ($storage) {
			$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', $storage->getPhotoUrl());
		  }
		}
		$feedSearchOptions[$counter]['key'] = $searchOptions['filtertype'];
		$feedSearchOptions[$counter]['value'] =  $this->view->translate($searchOptions['title']);
		$counter++;
	  }
	} else if ($subject && $subject->getType() == "user") {
	  $counter = 0;
	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/all.png');
	  $feedSearchOptions[$counter]['key'] = 'all';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("All Updates");
	  $counter++;

	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/post_self_buysell.png');
	  $feedSearchOptions[$counter]['key'] = 'post_self_buysell';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("Sell Something");
	  $counter++;

	  $feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/list.png');
	  $feedSearchOptions[$counter]['key'] = 'post_self_file';
	  $feedSearchOptions[$counter]['value'] =  $this->view->translate("Files");
	  $counter++;
	  if ($subject->getOwner()->getIdentity() == $this->view->viewer()->getIdentity()) {
		$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/hiddenpost.png');
		$feedSearchOptions[$counter]['key'] = 'hiddenpost';
		$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You've Hidden");
		$counter++;
		$feedSearchOptions[$counter]['image'] = $this->getBaseUrl('', 'application/modules/Sesapi/externals/images/filter/taggedinpost.png');
		$feedSearchOptions[$counter]['key'] = 'taggedinpost';
		$feedSearchOptions[$counter]['value'] =  $this->view->translate("Posts You're Tagged In");
		$counter++;
	  }
	}
	$contentResponse['feedSearchOptions'] = $feedSearchOptions;
	// Get some other info
	if (!empty($subject)) {
	  $contentResponse['subjectGuid'] = $subject->getGuid(false);
	}
	//composer enable options
	$contentResponse['enableComposer'] = false;
	if ($viewer->getIdentity() && !$this->_getParam('action_id')) {
	  if (!$subject || ($subject instanceof Core_Model_Item_Abstract && $subject->isSelf($viewer))) {
		if (Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'user', 'status')) {
		  $contentResponse['enableComposer'] = true;
		}
	  } else if ($subject) {
		if (Engine_Api::_()->authorization()->isAllowed($subject, $viewer, 'comment')) {
		  $contentResponse['enableComposer'] = true;
		}
	  }
	}

	// for live stream enable.
	$contentResponse['enableLivestream'] = false;
	if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('elivestreaming')) {
		$contentResponse['enableLivestream'] = true;
	}
	if (!empty($feelingEnable))
	  $contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Fellings'), 'name' => 'feelings', 'title' => $this->view->translate('How Are You Feeling?'));
	$contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Stickers'), 'name' => 'stickers', 'title' => $this->view->translate('Add a Sticker?'));

	if (($emojiPluginEnable))
	  $contentResponse['activityStikersMenu'][] = array('label' => $this->view->translate('Activities'), 'name' => 'activities', 'title' => $this->view->translate('What Are You Doing?'));

	if ($contentResponse['enableComposer']) {
	  $composerOptions = array('addPhoto' => "Photo", 'addVideo' => 'Video', 'checkIn' => "Check In"/*,'addQuote'=>"Quote"*/, 'addLink' => "Link", 'sellSomething' => "Sell Something", 'scheduledPost' => "Scheduled Post", 'tagPeople' => "Tag People", 'emotions' => $emojiText);
	  $getEnableComposers = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composeroptions', array());
	  // for live streaming.
    if ((defined('_SESAPI_VERSION_IOS') && _SESAPI_PLATFORM_SERVICE == 1  && _SESAPI_VERSION_IOS < 2.1 ) || (_SESAPI_VERSION_ANDROID < 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
		$key = array_search('elivestreaming', $getEnableComposers);
		if($key)
		unset($getEnableComposers[$key]);
	  }
	  $composerOptions = array();
	  //foreach($getEnableComposers as $compose){
	  if ($subject && method_exists($subject, 'activityComposerOptions')) {
		$allowedExtentions =  $subject->activityComposerOptions($subject);
		if (engine_in_array('photo', $getEnableComposers)) {
		  unset($getEnableComposers['photo']);
		}
		if (engine_in_array('sesmusic', $getEnableComposers)) {
		  unset($getEnableComposers['sesmusic']);
		}
		if (engine_in_array('video', $getEnableComposers)) {
		  unset($getEnableComposers['video']);
		}
		$composePartialsArrayDiff = array();

		foreach ($getEnableComposers as $key => $partials) {
		  if (array_key_exists($partials, $allowedExtentions)) {
			$composePartialsArrayDiff[$key] = $partials;
		  }
		}
		if (engine_in_array('sespage_photo', $composePartialsArrayDiff) || engine_in_array('sesgroup_photo', $composePartialsArrayDiff) || engine_in_array('sesbusiness_photo', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['photo'] = "photo";
		} else if (engine_in_array('photo', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['photo']);
		}
		if (engine_in_array('sespagevideo', $composePartialsArrayDiff) || engine_in_array('sesgroupvideo', $composePartialsArrayDiff) || engine_in_array('sesbusiness', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['video'] = "video";
		} else if (engine_in_array('video', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['video']);
		}
		if (engine_in_array('sespagemusic', $composePartialsArrayDiff) || engine_in_array('sesgroupmusic', $composePartialsArrayDiff) || engine_in_array('sesbusinessmusic', $composePartialsArrayDiff)) {
		  $composePartialsArrayDiff['sesmusic'] = "sesmusic";
		} else if (engine_in_array('sesmusic', $composePartialsArrayDiff)) {
		  unset($composePartialsArrayDiff['sesmusic']);
		}
		if (engine_in_array('sespagepoll', $composePartialsArrayDiff) || engine_in_array('sesbusinesspoll', $composePartialsArrayDiff) || engine_in_array('sesgrouppoll', $composePartialsArrayDiff)   && ( !defined('_SESAPI_VERSION_IOS') || _SESAPI_VERSION_IOS >= 9.3)) {
		  $composePartialsArrayDiff['poll'] = "poll";
		}

		$getEnableComposers = $composePartialsArrayDiff;
	  } else {
		if ($subject && $this->view->viewer()->getIdentity() && $subject->getType() != "user" &&  $subject->getIdentity() != $this->view->viewer()->getIdentity()) {
		  unset($composerOptions['sellSomething']);
		}
	  }

	  // for live streaming.
	  if (engine_in_array('elivestreaming', $getEnableComposers)) {
		$composerOptions['elivestreaming'] = $this->view->translate("elive_sesapi_controllers_activity_index");
	  }
    if(engine_in_array('activityfeedgif', $getEnableComposers) && Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.giphyapi', '')){
      $composerOptions['addGif'] = $this->view->translate('GIF');
    }
	  if ($subject && $subject->getType() == 'sespage_page' && engine_in_array('sespagepoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sespagepoll')   && ( !defined('_SESAPI_VERSION_IOS') || _SESAPI_VERSION_IOS >= 9.3)) {
      $composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if ($subject && $subject->getType() == 'sesgroup_group' && engine_in_array('sesgrouppoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sesgrouppoll')   && ( !defined('_SESAPI_VERSION_IOS') || _SESAPI_VERSION_IOS >= 9.3)) {
		$composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if ($subject && $subject->getType() == 'businesses' && engine_in_array('sesbusinesspoll', $getEnableComposers) && Engine_Api::_()->sesapi()->isModuleEnable('sesbusinesspoll')   && ( !defined('_SESAPI_VERSION_IOS') || _SESAPI_VERSION_IOS >= 9.3)) {
		$composerOptions['addPoll'] = $this->view->translate("Add Poll");
	  }
	  if (engine_in_array('photo', $getEnableComposers) && (Engine_Api::_()->sesapi()->isModuleEnable('sesalbum') || Engine_Api::_()->sesapi()->isModuleEnable('album'))) {
		$composerOptions['addPhoto'] = $this->view->translate("Photo");
	  }
	  if (engine_in_array('albumvideo', $getEnableComposers) && (Engine_Api::_()->sesapi()->isModuleEnable('album'))) {
			$composerOptions['addPhoto'] = $this->view->translate("Photo");
	  }
	  if ((engine_in_array('sesmusic', $getEnableComposers) || engine_in_array('sesmusic', $getEnableComposers)) && Engine_Api::_()->sesapi()->isModuleEnable('sesmusic') && _SESAPI_PLATFORM_SERVICE != 1) {
		$composerOptions['addMusic'] = $this->view->translate("Music");
	  }
	  if (engine_in_array('video', $getEnableComposers) && (!$subject || ($subject->getType() != 'sesgroup_group' && $subject->getType() != 'businesses')) &&  (Engine_Api::_()->sesapi()->isModuleEnable('sesvideo') || Engine_Api::_()->sesapi()->isModuleEnable('video'))) {
			$composerOptions['addVideo'] = $this->view->translate("Video");
	  }
	  if (engine_in_array('albumvideo', $getEnableComposers) && (!$subject || ($subject->getType() != 'sesgroup_group' && $subject->getType() != 'businesses')) &&  (Engine_Api::_()->sesapi()->isModuleEnable('video'))) {
			$composerOptions['addVideo'] = $this->view->translate("Video");
		}

		if (engine_in_array('albumvideo', $getEnableComposers) && (!$subject || ($subject->getType() != 'sesgroup_group' && $subject->getType() != 'businesses')) &&  (Engine_Api::_()->sesapi()->isModuleEnable('video'))) {
			$composerOptions['addVideo'] = $this->view->translate("Video");
		}

	  if(engine_in_array('sesgroupvideo', $getEnableComposers) && $subject && $subject->getType() == 'sesgroup_group' && Engine_Api::_()->sesapi()->isModuleEnable('sesgroupvideo')){
		$composerOptions['addVideo'] = $this->view->translate('Add Video');
	  }

	  if(engine_in_array('sesbusinessvideo', $getEnableComposers) && $subject && $subject->getType() == 'businesses' && Engine_Api::_()->sesapi()->isModuleEnable('sesbusinessvideo')){
		$composerOptions['addVideo'] = $this->view->translate('Add Video');
	  }

	  if (engine_in_array('locationactivity', $getEnableComposers)) {
		$composerOptions['checkIn'] = $this->view->translate("Check In");
	  }
	  if (engine_in_array('activitylink', $getEnableComposers)) {
		$composerOptions['addLink'] = $this->view->translate("Link");
	  }
	  if (engine_in_array('buysell', $getEnableComposers)) {
		$composerOptions['sellSomething'] = $this->view->translate("Sell Something");
	  }
	  if (engine_in_array('shedulepost', $getEnableComposers)) {
		$composerOptions['scheduledPost'] = $this->view->translate("Scheduled Post");
	  }
	  if (engine_in_array('tagUseActivity', $getEnableComposers)) {
		$composerOptions['tagPeople'] = $this->view->translate("Tag People");
	  }
	  $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();
	  $currentData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
	  if (engine_in_array('intopenaiimage', $getEnableComposers) && $this->view->viewer()->getIdentity() && Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'image')) {
		$composerOptions['intopenaiimage'] = $this->view->translate("Generate Image");
		$contentResponse["intopenaiimage"] = array("showWallet" => Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'image_price')>0, "title"=>$this->view->translate("Generate Image"),"description"=>$this->view->translate("You can generate and draw your own images using AI. Describe the image you want below and our AI will generate the images for you. You can select the image size and image count as well from below."),"balanceText"=>$this->view->translate("Available Balance"),"imageText"=>$this->view->translate("Images"),"balance"=>Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'image_price') ? floor(($this->view->viewer()->wallet_amount * $currentData->change_rate)/Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'image_price')) : 0,"form"=>array("prompLabel"=>$this->view->translate("Enter your image prompt below."),"imageSize"=>$this->view->translate("Image size"),"imageCount"=>$this->view->translate("Image count"),"submitText"=>$this->view->translate("Generate"),"rechargeWallet"=>$this->view->translate("Recharge Wallet")),"keys"=>"text,aiimage_size,aiimage_count","url"=>"intopenai/index/image");
	  }
	  if (engine_in_array('intopenaiword', $getEnableComposers) && $this->view->viewer()->getIdentity() && Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'content')) {
		$composerOptions['intopenaiword'] = $this->view->translate("Generate Post");
		$contentResponse["intopenaiword"] = array("showWallet" => Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'content_price')>0,"title"=>$this->view->translate("Generate Description"),"balanceText"=>$this->view->translate("Available Balance"),"wordText"=>$this->view->translate("Words"),"balance"=>Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'content_price') ? floor(($this->view->viewer()->wallet_amount * $currentData->change_rate)/Engine_Api::_()->authorization()->getPermission($this->view->viewer()->level_id, 'intopenai', 'content_price')) : 0,"form"=>array("prompLabel"=>$this->view->translate("Enter your prompt below to generate the post."),"wordLength"=>$this->view->translate("Max result length"),"submitText"=>$this->view->translate("Generate"),"rechargeWallet"=>$this->view->translate("Recharge Wallet")),"keys"=>"text,length","url"=>"intopenai/index/content");
	  }
	  if (engine_in_array('smilesActivity', $getEnableComposers) || engine_in_array('feelingssctivity', $getEnableComposers)) {
		$composerOptions['emotions'] = $emojiText;
	  }

	//   $levelAdapter = Engine_Api::_()->authorization()->getAdapter('levels');
    //   $enableComposers = (array) $levelAdapter->getAllowed('activity', $this->view->viewer(), 'composeroptions');
      
    //    if(!engine_in_array('feelingssctivity', $enableComposers)) {
    //        unset($composerOptions['emotions']);
    //    }
    //    if(!engine_in_array('shedulepost', $enableComposers)) {
    //        unset($composerOptions['scheduledPost']);
    //    }
    //    if(!engine_in_array('enablefeedbg', $enableComposers)) {
    //        unset($contentResponse['feedBgStatusPost']);
    //    }
    //    if(!engine_in_array('activitytargetpost', $enableComposers)) {
    //         //unset($composerOptions['scheduledPost']);
    //    }
    //    if(!engine_in_array('fileupload', $enableComposers)) {
    //         //unset($composerOptions['scheduledPost']);
    //    }
    //    if(!engine_in_array('buysell', $enableComposers)) {
    //         unset($composerOptions['sellSomething']);
    //    }
    //    if(!engine_in_array('locationses', $enableComposers)) {
    //         unset($composerOptions['checkIn']);
    //    }
    

	  $composerOptionsCounter = 0;
	  $counter = 0;
	  foreach ($composerOptions as $key => $option) {
		$contentResponse['composerOptions'][$counter]['value'] = $this->view->translate($option);
		$contentResponse['composerOptions'][$counter]['name'] = $key;

		if($key == "addVideo" && $subject &&  $subject->getType() == 'sesgroup_group' && Engine_Api::_()->sesapi()->isModuleEnable('sesgroupvideo')){
			$contentResponse['composerOptions'][$counter]['module'] = 'sesgroupvideo'; 
		}
		if($key == "addVideo" && $subject &&  $subject->getType() == 'businesses' && Engine_Api::_()->sesapi()->isModuleEnable('sesbusinessvideo')){
			$contentResponse['composerOptions'][$counter]['module'] = 'sesbusinessvideo'; 
		}

		$counter++;
	  }
	}

	if ( $viewer->getIdentity()) {
    $sesfeedbg_enablefeedbg = false;
   
	$sesfeedbg_enablefeedbg = true;
	  $sesfeedbg_limit_show = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'activityfeedbg', 'max');
	  if ($sesfeedbg_enablefeedbg) {
		$getFeaturedBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'activity')->getBackgrounds(array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => 5, 'featured' => 1));
		$featured = $backgrounds = array();
		foreach ($getFeaturedBackgrounds as $getFeaturedBackground) {
		  $featured[] = $getFeaturedBackground->background_id;
		}
		if (engine_count($featured) > 0) {
		  $sesfeedbg_limit_show = $sesfeedbg_limit_show - 5;
		}
		$getBackgrounds = Engine_Api::_()->getDbTable('backgrounds', 'activity')->getBackgrounds(array('admin' => 1, 'fetchAll' => 1, 'sesfeedbg_limit_show' => $sesfeedbg_limit_show, 'feedbgorder' => '', 'featuredbgIds' => $featured));
		foreach ($getBackgrounds as $getBackground) {
		  $backgrounds[] = $getBackground->background_id;
		}
		if (engine_count($featured) > 0) {
		  $backgrounds = array_merge($featured, $backgrounds);
		}

		if (engine_count($backgrounds) > 0) {
		  $counter = 0;
		  $contentResponse['feedBgStatusPost'][$counter]['photo'] = $this->getBaseUrl('', "application/modules/Sesfeedbg/externals/images/white.png");
		  $contentResponse['feedBgStatusPost'][$counter]['background_id'] = 0;
		  $counter++;

		  foreach ($backgrounds as $getBackground) {
			$id = $getBackground;
			$getBackground = Engine_Api::_()->getItem('activity_background', $getBackground);
			if ($getBackground->file_id && $getBackground) {
			  $photo = Engine_Api::_()->storage()->get($getBackground->file_id, '');
			  if ($photo) {
				$photo = $this->getBaseUrl('', $photo->getPhotoUrl());
				$contentResponse['feedBgStatusPost'][$counter]['photo'] = $photo;
				$contentResponse['feedBgStatusPost'][$counter]['background_id'] = $id;
				$counter++;
			  }
			}
		  }
		}
	  }
	}
	if (!empty($messageText) && empty($subject))
	  $contentResponse['message_permission'] = $messageText;
	
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $contentResponse));
  }

  public function indexAction()
  {
	$contentResponse = array();
	$request = Zend_Controller_Front::getInstance()->getRequest();
	// Don't render this if not authorized
	$viewer = Engine_Api::_()->user()->getViewer();
	$subject = $this->_getParam('resource_type', '');
	$resource_id = $this->_getParam('resource_id', '');
	if ($subject) {
		// Get subject
		$subject = Engine_Api::_()->getItem($subject, $resource_id);
		if ($subject)
		Engine_Api::_()->core()->setSubject($subject);
	}
	if ($viewer->getIdentity()) {
		$contentResponse['user_image'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
		$contentResponse['user_id'] = $viewer->getIdentity();
		$contentResponse['user_title'] = $viewer->getTitle();
	}
	$contentResponse['feedOnly'] = $feedOnly = $request->getParam('feedOnly', false);
	$getUpdate = $request->getParam('getUpdate');
	$checkUpdate = $request->getParam('checkUpdate');
	$contentResponse['filterFeed'] = $filterFeed = $this->_getParam('filterFeed', 'all');
	$contentResponse['length'] = $length = $request->getParam('limit', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.length', 15));
	$contentResponse['itemActionLimit']  = $itemActionLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.userlength', 5);
	$this->view->action_id = (int) $request->getParam('action_id');
	if ($length > 50) {
      $contentResponse['length'] = $length = 50;
	}
	// Get all activity feed types for custom view?
	$actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
	$groupedActionTypes = $actionTypesTable->getEnabledGroupedActionTypes();
	$actionTypeGroup = $filterFeed;
	$actionTypeFilters = array();
	//SES advanced member plugin followig work
	$isSesmember = $actionTypeGroup == 'sesmember' && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active', 1);
	if (!$isSesmember) {
		if ($actionTypeGroup && isset($groupedActionTypes[$actionTypeGroup])) {
		$actionTypeFilters = $groupedActionTypes[$actionTypeGroup];
		if ($actionTypeGroup == 'sesalbum' || $actionTypeGroup == 'album')
			$actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['photo']);
		else if ($actionTypeGroup == 'sesvideo')
			$actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes['video']);
		}
	}
	if ($actionTypeGroup == 'post_self_buysell')
		$actionTypeFilters = array('post_self_buysell');
	else if ($actionTypeGroup == 'post_self_file')
		$actionTypeFilters = array('post_self_file');
	//else if(strpos($actionTypeGroup , 'network_filter_' ) !== false)
	// $actionTypeFilters = array('network');
	$isOnThisDayPage = false;
	if (!empty($_POST['isOnThisDayPage'])) {
		$isOnThisDayPage = true;
	}
	if (isset($_POST['maxid'])) {
		if ((int) $_POST['maxid'] == 0 && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sescommunityads')) {
			$front = Zend_Controller_Front::getInstance();
			$key = Engine_Api::_()->sescommunityads()->getKey($front);
			if (!empty($_SESSION[$key]))
				unset($_SESSION[$key]);
			$_SESSION[$key] = array();
			$_SESSION[$key . "_stop"] = false;
		}
	}
	$this->view->composerOptions = $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composeroptions', array());
	// Get config options for activity

	$hashTag = isset($_POST['hashtag']) ? str_replace('#', '', $_POST['hashtag']) : '';
	$config = array(
		'action_id' => (isset($_POST['action_id']) ? (int) $_POST['action_id'] : 0),
		'max_id'    => (isset($_POST['maxid']) ? (int) $_POST['maxid'] : 0),
		'min_id'    => (isset($_POST['minid']) ? (int) $_POST['minid'] : 0),
		'limit'     => (int) $length,
		'showTypes' => $actionTypeFilters,
		'filterFeed' => $filterFeed,
		'hashTag' => $hashTag,
		'action_video_id' => $this->_getParam('action_video_id'),
		'targetPost' => engine_in_array('activitytargetpost', $composerOptions),
		'isOnThisDayPage' => $isOnThisDayPage,
		'allvideos' => $this->_getParam('allvideos')
	);
		if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
				$sesAdv = true;
		}else{
				if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
						$sesAdv = false;
				}
		}
		if($sesAdv)
			$actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
		else
			$actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
			// Pre-process feed items
	$selectCount = 0;
	$nextid = null;
	$firstid = null;
	$tmpConfig = $config;
	$activity = array();
	$endOfFeed = false;
	$friendRequests = array();
	$itemActionCounts = array();
	$enabledModules = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
	$counter = 0;
	$backGroundEnable =  true;
	$contentprofilecoverphotoenable =  Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesusercoverphoto');
	$activitytextlimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.textlimit', 120);
	$enableFeedBg = true;
	$activitybigtext = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.bigtext', 1);
	$activityfonttextsize = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.fonttextsize', 24);
	do {
		$request = Zend_Controller_Front::getInstance()->getRequest();
	    if($request->getParam('action_id') && empty($subject)){
	        $action = Engine_Api::_()->getItem($sesAdv ? "activity_action" : "activity_action",$request->getParam('action_id'));
	        if($action){
	            $subject = Engine_Api::_()->user()->getUser($action->subject_id);
                if( $subject->getIdentity() )
                {
                  Engine_Api::_()->core()->setSubject($subject);
                }
	        }
	    }
		// Get current batch
		$actions = null;
		// Where the Activity Feed is Fetched
		if (!empty($subject) && $sesAdv) {
			$actions = $actionTable->getActivityAbout($subject, $viewer, $tmpConfig);
		} elseif(!empty($subject) && !$sesAdv) {
			$actions = $actionTable->getActivity($viewer, $tmpConfig,$subject);
		} else {
			$actions = $actionTable->getActivity($viewer, $tmpConfig);
		}
		$selectCount++;
		// Are we at the end?
    if (is_array($actions) || is_object($actions)) {
      if (engine_count($actions) < $length || engine_count($actions) <= 0) {
        $endOfFeed = true;
      }
    }else{
		$endOfFeed = true;
	}
		$allowDelete = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete');
		if ($viewer->getIdentity()) {
		$activity_moderate =  Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
		} else {
		$activity_moderate = 0;
		}
		$activityTypeTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
		// Pre-process
		$feeling = true;
		
    if (is_array($actions) || is_object($actions)) {
  		if (engine_count($actions) > 0) {
  			foreach ($actions as $action) {
  				$return = false;
  				try {
  				include('activity.php');
  				if (!empty($break))
  					break;
  				} catch (Exception $e) {echo $e->getMessage();die; throw $e;
  				continue;
  				}
  				if (!$return)
  				$counter++;
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
	} while (engine_count($activity) < $length && $selectCount <= 3 && !$endOfFeed);
	if ($checkUpdate) {
    if (is_array($actions) && engine_count($actions) > 0) {
      $count = engine_count($actions);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $count));
    }
  }
	$communityAdsEnable = false;
	//community ads integration
	if (_SESAPI_VERSION_ANDROID >= 2.6)
		$communityAdsEnable = true;
	if (_SESAPI_VERSION_IOS >= 1.6)
		$communityAdsEnable = true;

	$contentCounter = $this->_getParam('contentCounter', 0);
	$activityArrayContent = array();
	$communityadsExecuted = false;
	if ($communityAdsEnable && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sescommunityads') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sescommunityads_advertisement_enable', '1')) {
		$counterActivity = 0;
		$communityadsExecuted = true;
		foreach ($activity as $acti) {
		$content = $this->sescommunityAds($subject, $contentCounter);
		if (engine_count($content)) {
			$activityArrayContent[$counterActivity] = $content;
			if (@$activityArrayContent[$counterActivity]['ad_type'] != "boost_post_cnt")
			$activityArrayContent[$counterActivity]['content_type'] = 'communityads';
			else
			$activityArrayContent[$counterActivity]['content_type'] = 'feed';
			$counterActivity++;
		}
		$activityArrayContent[$counterActivity] = $acti;
		$activityArrayContent[$counterActivity]['content_type'] = 'feed';
		$contentCounter++;
		$counterActivity++;
		}
	}
	$enable = false;
	if (_SESAPI_VERSION_ANDROID >= 2.2)
		$enable = true;
	if (_SESAPI_VERSION_IOS >= 2.4)
		$enable = true;

	if (!$subject && $enable && !$this->_getParam('allvideos', 0)) {
		//get pymk and se default ads
		$counterActivity = 0;
		if ($communityadsExecuted) {
			$activityArrayResult = $activityArrayContent;
		} else {
			$activityArrayResult = $activity;
		}
		$activityArrayContent = array();
		foreach ($activityArrayResult as $acti) {
			$content = $this->canShowAddsAndPeopleYoumayKnow($counterActivity);
			if (engine_count($content['pymk'])) {
				if (engine_count($content['pymk']['users'])) {
					$activityArrayContent[$counterActivity]['result'] = $content['pymk']['users'];
					$activityArrayContent[$counterActivity]['seeall'] = $content['pymk']['sellall'];
					$activityArrayContent[$counterActivity]['content_type'] = 'peopleyoumayknow';
					$counterActivity++;
				}
			}
			if (engine_count($content['ads'])) {
				$activityArrayContent[$counterActivity] = $content['ads'];
				$activityArrayContent[$counterActivity]['content_type'] = 'ads';
				$counterActivity++;
			}
			$activityArrayContent[$counterActivity] = $acti;
			if (empty($activityArrayContent[$counterActivity]['content_type']))
				$activityArrayContent[$counterActivity]['content_type'] = 'feed';
			$contentCounter++;
			$counterActivity++;
		}
	} else if (!$communityadsExecuted)
		$activityArrayContent = $activity;
	$contentResponse['activity'] = $activityArrayContent;
	$contentResponse['activityCount'] = engine_count($activity);
	$contentResponse['nextid'] = $nextid;
	$contentResponse['contentCounter'] = $contentCounter + engine_count($activity);
	$contentResponse['firstid'] = (int) $firstid;
	$contentResponse['endOfFeed'] = $endOfFeed;
	//send user pages in response
	if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sespage')) {
		$pageAttr = $this->getPages();
		if (engine_count($pageAttr))
		$contentResponse['sespage_page'] = $pageAttr;
	}
	if ($subject && $subject->getType() == "sespage_page") {
		$pageAttr = $this->postAttributionSespage($subject);
		if ($pageAttr)
		$contentResponse['activity_attribution'] = $pageAttr;
	}
	//send business in response
	if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesbusiness')) {
		$pageAttr = $this->getBusinesses();
		if (engine_count($pageAttr))
		$contentResponse['businesses'] = $pageAttr;
	}
	if ($subject && $subject->getType() == "businesses") {
		$pageAttr = $this->postAttributionSesbusiness($subject);
		if ($pageAttr)
		$contentResponse['activity_attribution'] = $pageAttr;
	}
	if($config['allvideos'] == "1" && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')){
		$contentResponse['activity'] = $this->loadOnlyVideo($contentResponse['activity']);
	}      
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $contentResponse));
  }

  function getBusinesses()
  {
	$user_id = $this->_getParam('user_id', false);
	$table = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness');
	$selelct = $table->select($table->info('name'), 'business_id')->where('user_id =?', $this->view->viewer()->getIdentity());
	$res = $table->fetchAll($selelct);
	$pageIds = array();
	foreach ($res as $page) {
	  $pageIds[] = $page->business_id;
	}
	if (!$user_id)
	  $user_id = $this->view->viewer()->getIdentity();
	$value['user_id'] = $user_id;
	$value['businessIds'] = $pageIds;
	$value['fetchAll'] = true;
	$pages = Engine_Api::_()->getDbTable('businesses', 'sesbusiness')->getBusinessSelect($value);
	$counter = 0;
	$userPages = array();
	$viewer = $this->view->viewer();
	$userPages[$counter]['guid'] = $viewer->getGuid();
	$userPages[$counter]['photo'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
	$userPages[$counter]['title'] = $viewer->getTitle();
	$counter++;
	foreach ($pages as $page) {
	  $userPages[$counter]['guid'] = $page->getGuid();
	  $userPages[$counter]['photo'] = $this->getBaseUrl(true, $page->getPhotoUrl('thumb.profile'));
	  $userPages[$counter]['title'] = $page->getTitle();
	  $counter++;
	}
	return $userPages;
  }
  function postAttributionSesbusiness($subject)
  {
	$viewer = $this->view->viewer();
	$user_id = $viewer->getIdentity();
	$attributionType = Engine_Api::_()->getDbTable('postattributions', 'sesbusiness')->getBusinessPostAttribution(array('business_id' => $subject->getIdentity()));
	$pageAttributionType = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'seb_attribution');
	$allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'auth_defattribut');
	$enablePostAttribution = Engine_Api::_()->authorization()->isAllowed('businesses', $viewer, 'auth_contSwitch');
	if (!$pageAttributionType || $attributionType == 0) {
	  $pageAttribution = "";
	}
	if ($pageAttributionType && !$allowUserChoosePageAttribution || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if ($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1 || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if (!$enablePostAttribution || !$pageAttributionType || !$user_id) { } else {

	  $isAdmin = Engine_Api::_()->getDbTable('businessroles', 'sesbusiness')->isAdmin(array('business_id' => $subject->getIdentity(), 'user_id' => $this->view->viewer()->getIdentity()));
	  if (!$isAdmin) {
		$pageAttribution = $this->view->viewer();
	  }

	  if (!empty($pageAttribution)) {
		return array('guid' => $pageAttribution->getGuid(), 'photo' => $this->getBaseUrl(true, $pageAttribution->getPhotoUrl('thumb.profile')));
	  } else {
		return array('guid' => $viewer->getGuid(), 'photo' => $this->userImage($viewer, "thumb.profile"));
	  }
	}
	return false;
  }
  
  

  function postAttributionSespage($subject)
  {
	$viewer = $this->view->viewer();
	$user_id = $viewer->getIdentity();
	$attributionType = Engine_Api::_()->getDbTable('postattributions', 'sespage')->getPagePostAttribution(array('page_id' => $subject->getIdentity()));
	$pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'page_attribution');
	$allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'auth_defattribut');
	$enablePostAttribution = Engine_Api::_()->authorization()->isAllowed('sespage_page', $viewer, 'auth_contSwitch');
	if (!$pageAttributionType || $attributionType == 0) {
	  $pageAttribution = "";
	}
	if ($pageAttributionType && !$allowUserChoosePageAttribution || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if ($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1 || !$enablePostAttribution) {
	  $pageAttribution = $subject;
	}
	if (!$enablePostAttribution || !$pageAttributionType || !$user_id) { } else {

	  $isAdmin = Engine_Api::_()->getDbTable('pageroles', 'sespage')->isAdmin(array('page_id' => $subject->getIdentity(), 'user_id' => $this->view->viewer()->getIdentity()));
	  if (!$isAdmin) {
		$pageAttribution = $this->view->viewer();
	  }

	  if (!empty($pageAttribution)) {
		return array('guid' => $pageAttribution->getGuid(), 'photo' => $this->getBaseUrl(true, $pageAttribution->getPhotoUrl('thumb.profile')));
	  } else {
		return array('guid' => $viewer->getGuid(), 'photo' => $this->userImage($viewer, "thumb.profile"));
	  }
	}
	return false;
  }
  function getPages()
  {
	$user_id = $this->_getParam('user_id', false);
	$table = Engine_Api::_()->getDbTable('pageroles', 'sespage');
	$selelct = $table->select($table->info('name'), 'page_id')->where('user_id =?', $this->view->viewer()->getIdentity());
	$res = $table->fetchAll($selelct);
	$pageIds = array();
	foreach ($res as $page) {
	  $pageIds[] = $page->page_id;
	}
	if (!$user_id)
	  $user_id = $this->view->viewer()->getIdentity();
	$value['user_id'] = $user_id;
	$value['pageIds'] = $pageIds;
	$value['fetchAll'] = true;
	$pages = Engine_Api::_()->getDbTable('pages', 'sespage')->getPageSelect($value);
	$counter = 0;
	$userPages = array();
	$viewer = $this->view->viewer();
	$userPages[$counter]['guid'] = $viewer->getGuid();
	$userPages[$counter]['photo'] = $this->userImage($viewer->getIdentity(), "thumb.profile");
	$userPages[$counter]['title'] = $viewer->getTitle();
	$counter++;
	foreach ($pages as $page) {
	  $userPages[$counter]['guid'] = $page->getGuid();
	  $userPages[$counter]['photo'] = $this->getBaseUrl(true, $page->getPhotoUrl('thumb.profile'));
	  $userPages[$counter]['title'] = $page->getTitle();
	  $counter++;
	}
	return $userPages;
  }
  function sescommunityAds($subject, $contentCount = 0)
  {
	$settings = Engine_Api::_()->getApi('settings', 'core');
	$communityAdsEnable = $settings->getSetting('sescommunityads_advertisement_enable', 1);
	$communityAdsDisplay = $settings->getSetting('sescommunityads_advertisement_display', 3);
	$communityAdsDisplayFeed = $settings->getSetting('sescommunityads_advertisement_displayfeed', 1);
	if (!$subject && !$communityAdsDisplayFeed)
	  return array();
	$communityAdsDisplayAds = $settings->getSetting('sescommunityads_advertisement_displayads', 5);
	$communityads = array();
	if ($contentCount && $contentCount % $communityAdsDisplayAds == 0) {
	  $valueAds['communityAdsDisplay'] = $communityAdsDisplay;
	  $view = Engine_Api::_()->authorization()->isAllowed('sescommunityads', null, 'view');
	  if (!$view)
		return array();
	  $valueAds['fetchAll'] = true;
	  $valueAds['limit'] = 1; //Engine_Api::_()->getApi('settings', 'core')->getSetting('sescommunityads.ads.count', 1);
	  $valueAds["fromActivityFeed"] = true;
	  $select = Engine_Api::_()->getDbTable('sescommunityads', 'sescommunityads')->getAds($valueAds);
	  $paginator =  $select;

	  if (is_countable($paginator) && engine_count($paginator) > 0) {
		foreach ($paginator as $ad) {
		  $communityads['ad_id'] = $ad->getIdentity();
		  $communityads['user_id'] = $ad->user_id;
		  $communityads['ad_type'] = $ad->type;
		  if ($ad->user_id != $this->view->viewer()->getIdentity()) {
			$adsItem = Engine_Api::_()->getItem('sescommunityads', $ad->getIdentity());
			$adsItem->views_count++;
			$adsItem->save();

			$campaign = Engine_Api::_()->getItem('sescommunityads_campaign', $adsItem->campaign_id);
			$campaign->views_count++;
			$campaign->save();

			//insert in view table
			Engine_Api::_()->getDbTable('viewstats', 'sescommunityads')->insertrow($adsItem, $this->view->viewer());
			//insert campaign stats
			Engine_Api::_()->getDbTable('campaignstats', 'sescommunityads')->insertrow($adsItem, $this->view->viewer(), 'view');
		  }
		  if ($ad->type == "promote_content_cnt" || $ad->type == "promote_website_cnt") {
			//header data
			$image = Engine_Api::_()->getItem('storage_file', $ad->website_image);
			$imageSrc = "";
			if ($image)
			  $imageSrc = $image->map();
			$communityads['url'] = $this->getBaseUrl(false, $ad->getHref(array('subject' => true)));
			if ($ad->type != "promote_website_cnt" || $imageSrc) {
			  $communityads['header_image'] = $this->getBaseUrl(false, !empty($ad) && $ad->resources_type ? $ad->description : ($imageSrc ? $imageSrc : "application/modules/Sescommunityads/externals/images/transprant-bg.png"));
			}
			$communityads['title'] = $ad->title;

			$dot = "";
			if ($ad->sponsored) {
			  $communityads['sponsored']  =  $this->view->translate('Sponsored');
			}
			if ($ad->featured && !$ad->sponsored) {
			  $communityads['sponsored'] = $this->view->translate('Featured');
			}
			if ($ad->user_id != $this->view->viewer()->getIdentity()) {
			  $menuOptionsCounter = 0;
			  $menuOption[$menuOptionsCounter]['label'] = $menuOption[$menuOptionsCounter]['value'] = $this->view->translate('hide ad');
			  $menuOption[$menuOptionsCounter]['name'] = $this->view->translate('hide_ad');
			  $menuOptionsCounter = 1;
			  $useful = $ad->isUseful();
			  $menuOption[$menuOptionsCounter]['label'] = $menuOption[$menuOptionsCounter]['value'] = !$useful ? $this->view->translate('This ad is useful') : $this->view->translate('Remove from useful');
			  $menuOption[$menuOptionsCounter]['is_useful'] = $useful ? 1 : 0;
			  $menuOption[$menuOptionsCounter]['name'] = $this->view->translate('ad_useful');
			  $communityads['menus'] = $menuOption;
			}
			$communityads['hidden_data'] = array(
			  'heading' => $this->view->translate('Ad hidden'),
			  'description' => $this->view->translate('You Won\'t See this ad and ads like this.') . ' ' . $this->view->translate('Why did you hide it?'),
			  'options' => array(
				'Offensive' => $this->view->translate('Offensive'),
				'Misleading' => $this->view->translate('Misleading'),
				'Inappropriate' => $this->view->translate('Inappropriate'),
				'Licensed Material' => $this->view->translate('Licensed Material'),
				'Other' => $this->view->translate('Other'),
			  ),
			  'other_text' => $this->view->translate('Specify your reason here..'),
			  'submit_button_text' => $this->view->translate('Report'),
			  'success_text' => $this->view->translate('Thanks for your feedback. Your report has been submitted.')
			);

			//get attachments
			$table = Engine_Api::_()->getDbTable('attachments', 'sescommunityads');
			$select = $table->select()->where('sescommunityad_id =?', $ad->getIdentity());
			$attachment = $table->fetchAll($select);

			if ($ad->subtype == "image") {
			  if (is_countable($attachment) && engine_count($attachment)) {
				$attach = $attachment[0];
				$image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				$imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				if ($image)
				  $imageSrc = $image->map();
				$communityads['ad_type'] = 'image';
				$communityads['attachment']['href'] = $this->getBaseUrl(false, $attach->getHref());;
				$communityads['attachment']['src'] = $this->getBaseUrl(false, $imageSrc);
				if ($ad->type == "promote_website_cnt") {
				  $description = $ad->description;
				  $description = str_replace('http://', '', $description);
				  $description = str_replace('https://', '', $description);
				  $description = explode('/', $description);
				  $communityads['attachment']['url_description'] = $description[0];
				}
				$communityads['attachment']['title'] = $attach->title;
				$communityads['attachment']['description'] = $attach->description;
				if ($ad->calltoaction) {
				  $communityads['attachment']['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['attachment']['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				}
			  }
			} else if ($ad->subtype == "video") {
			  if (is_countable($attachment) && engine_count($attachment)) {
				$attach = $attachment[0];
				$image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				$imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				if ($image)
				  $imageSrc = $image->map();

				$video = Engine_Api::_()->getItem('storage_file', $ad->video_src);
				$videosrc = "";
				if ($videosrc)
				  $videosrc = $video->map();
				$communityads['attachment']['image_src'] = $this->getBaseUrl(false, $videosrc);

				$communityads['ad_type'] = 'video';
				$communityads['attachment']['href'] = $this->getBaseUrl(false, $attach->getHref());
				$communityads['attachment']['src'] = $this->getBaseUrl(false, $imageSrc);
				if ($ad->type == "promote_website_cnt") {
				  $description = $ad->description;
				  $description = str_replace('http://', '', $description);
				  $description = str_replace('https://', '', $description);
				  $description = explode('/', $description);
				  $communityads['attachment']['url_description'] = $description[0];
				}
				$communityads['attachment']['title'] = $attach->title;
				$communityads['attachment']['description'] = $attach->description;
				if ($ad->calltoaction) {
				  $communityads['attachment']['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['attachment']['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				}
			  }
			} else {
			  if (is_countable($attachment) && engine_count($attachment)) {
				$counter = 0;
				$communityads['ad_type'] = "carousel";
				foreach ($attachment as $attach) {
				  $image = Engine_Api::_()->getItem('storage_file', $attach->file_id);
				  $imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				  if ($image)
					$imageSrc = $image->map();
				  $communityads['carousel_attachment'][$counter]['href'] = $this->getBaseUrl(false, $attach->getHref());
				  $communityads['carousel_attachment'][$counter]['src'] = $this->getBaseUrl(false, $imageSrc);
				  $communityads['carousel_attachment'][$counter]['title'] = $attach->title;
				  $communityads['carousel_attachment'][$counter]['description'] = $attach->description;
				  if ($ad->calltoaction) {
					$communityads['carousel_attachment'][$counter]['calltoaction']['href'] = $this->getBaseUrl(false, $attach->getHref());
					$communityads['carousel_attachment'][$counter]['calltoaction']['label'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->calltoaction ? $ad->calltoaction : "")));;
				  }
				  if ($ad->call_to_action_overlay) {
					$communityads['carousel_attachment'][$counter]['call_to_action_overlay'] = $this->view->translate(ucwords(str_replace('_', ' ', $ad->call_to_action_overlay ? $ad->call_to_action_overlay : "")));
				  }
				  $counter++;
				}
				if ($ad->more_image) {
				  $image = Engine_Api::_()->getItem('storage_file', $ad->more_image);
				  $imageSrc = "application/modules/Sescommunityads/externals/images/transprant-bg.png";
				  if ($image)
					$imageSrc = $image->map();
				  $communityads['seemore']['href'] = $this->getBaseUrl(false, $ad->getHref());
				  $communityads['seemore']['src'] = $this->getBaseUrl(false, $imageSrc);
				  $communityads['seemore']['title'] = $this->view->translate('See more at');
				  $communityads['seemore']['description'] = $ad->see_more_display_link;
				}
			  }
			}
		  } else {
			$action = Engine_Api::_()->getItem('activity_action', $ad->resources_id);
			if (!$action)
			  return array();
			$allowDelete = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete');
			$viewer = $this->view->viewer();
			if ($viewer->getIdentity()) {
			  $activity_moderate =  Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
			} else {
			  $activity_moderate = 0;
			}
			$activityTypeTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
			// Pre-process
			  $feeling = true;
			
			try {
			  $counter = 0;
			  $activity = array();
			  $length = 1;
			  $sescommunityads = true;
			  $fromActivityFeed = $_SESSION['fromActivityFeed'] = true;
			  include('activity.php');
			  $communityads = $activity[0];
			  $communityads['ad_id'] = $ad->getIdentity();
			  $communityads['hidden_data'] = array(
				'heading' => $this->view->translate('Ad hidden'),
				'description' => $this->view->translate('You Won\'t See this ad and ads like this.') . ' ' . $this->view->translate('Why did you hide it?'),
				'options' => array(
				  'Offensive' => $this->view->translate('Offensive'),
				  'Misleading' => $this->view->translate('Misleading'),
				  'Inappropriate' => $this->view->translate('Inappropriate'),
				  'Licensed Material' => $this->view->translate('Licensed Material'),
				  'Other' => $this->view->translate('Other'),
				),
				'other_text' => $this->view->translate('Specify your reason here..'),
				'submit_button_text' => $this->view->translate('Report'),
				'success_text' => $this->view->translate('Thanks for your feedback. Your report has been submitted.')
			  );
			} catch (Exception $e) { //throw $e;
			  $_SESSION['fromActivityFeed'] = false;
			  return array();
			}
			$_SESSION['fromActivityFeed'] = false;
		  }
		}
	  }
	}
	return $communityads;
  }
  function canShowAddsAndPeopleYoumayKnow($contentCount = 0)
  {
	$ads = array();
	$pymk = array();
	$settings = Engine_Api::_()->getApi('settings', 'core');
	$adsEnable = $settings->getSetting('activity.adsenable', 0);
	$adsRepeat = $settings->getSetting('activity.adsrepeatenable', 0);
	$adsRepeatTime = $settings->getSetting('activity.adsrepeattimes', 15);
	//show campaign ads

	if ($adsEnable && ($contentCount && $contentCount % $adsRepeatTime == 0) && ($adsRepeat || (!$adsRepeat && $contentCount / $adsRepeatTime == 1))) {
	  $ads =  $this->addSEAds();
	}
	//PYMY
	$peopleymkEnable = $settings->getSetting('activity.peopleymk', 1);
	$peopleymkrepeattimes = $settings->getSetting('activity.peopleymkrepeattimes', 5);
	$pymkrepeatenable = $settings->getSetting('activity.pymkrepeatenable', 0);

	if (Engine_Api::_()->sesapi()->isModuleEnable('sespymk') && $peopleymkEnable && ($contentCount && $contentCount % $peopleymkrepeattimes == 0) && ($pymkrepeatenable || (!$pymkrepeatenable && $contentCount / $peopleymkrepeattimes == 1))) {
	  $pymk = $this->pymk();
	}

	return array('pymk' => $pymk, 'ads' => $ads);
  }
  function pymk()
  {

	$viewer = Engine_Api::_()->user()->getViewer();
	if (!$viewer->getIdentity())
	  return array();
	$userIDS = $viewer->membership()->getMembershipsOfIds();
	$userMembershipTable = Engine_Api::_()->getDbTable('membership', 'user');
	$userMembershipTableName = $userMembershipTable->info('name');
	$select_membership = $userMembershipTable->select()
	  ->where('resource_id = ?', $viewer->getIdentity());
	$member_results = $userMembershipTable->fetchAll($select_membership);
	foreach ($member_results as $member_result) {
	  $membershipIDS[] = $member_result->user_id;
	}

	$userTable = Engine_Api::_()->getDbTable('users', 'user');
	$userTableName = $userTable->info('name');
	$select = $userTable->select()
	  ->where('user_id <> ?', $viewer->getIdentity())
	  ->where('approved = ?', 1)
	  ->where('enabled = ?', 1);
	$select->where('photo_id <> ?', 0);
	if ($membershipIDS) {
	  $select->where('user_id NOT IN (?)', $membershipIDS);
	}

	$select->order('rand()');

	$peopleyoumayknow = Zend_Paginator::factory($select);
	$peopleyoumayknow->setItemCountPerPage(15);
	$peopleyoumayknow->setCurrentPageNumber(1);
	if ($peopleyoumayknow->getTotalItemCount() == 0)
	  return array();
	
	$counterLoop = 0;
	$users = array();
	if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember'))
	  $memberEnable = true;
	foreach ($peopleyoumayknow as $member) {
	  if (!empty($memberEnable)) {
		//mutual friends
		$mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
		if (!$member->isSelf($viewer)) {
		  $users[$counterLoop]['mutualFriends'] = $mfriend == 1 ? $mfriend . $this->view->translate(" mutual friend") : $mfriend . $this->view->translate(" mutual friends");
		}
	  }
	  $users[$counterLoop]['user_id'] = $member->getIdentity();
	  $users[$counterLoop]['title'] = $member->getTitle();
	  $users[$counterLoop]['user_image'] = $this->userImage($member->getIdentity(), "thumb.profile");
    if($this->friendRequest($member)) {
      $users[$counterLoop]['membership'] = $this->friendRequest($member);
    }
	  $counterLoop++;
	}
	$result["users"] = $users;
	$result["sellall"] = $peopleyoumayknow->getTotalItemCount() > 15 ? true : false;
	return $result;
  }
  function friendRequest($subject)
  {

	$viewer = Engine_Api::_()->user()->getViewer();

	// Not logged in
	if (!$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false)) {
	  return 0;
	}

	// No blocked
	if ($viewer->isBlockedBy($subject)) {
	  return 0;
	}

	// Check if friendship is allowed in the network
	$eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
	if (!$eligible) {
	  return 0;
	}

	// check admin level setting if you can befriend people in your network
	else if ($eligible == 1) {

	  $networkMembershipTable = Engine_Api::_()->getDbTable('membership', 'network');
	  $networkMembershipName = $networkMembershipTable->info('name');

	  $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
	  $select
		->from($networkMembershipName, 'user_id')
		->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
		->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
		->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity());

	  $data = $select->query()->fetch();

	  if (empty($data)) {
		return 0;
	  }
	}

	// One-way mode
	$direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
	if (!$direction) {
	  $viewerRow = $viewer->membership()->getRow($subject);
	  $subjectRow = $subject->membership()->getRow($viewer);
	  $params = array();

	  // Viewer?
	  if (null === $subjectRow) {
		// Follow
		return array(
		  'label' => $this->view->translate('Follow'),
		  'action' => 'add',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',
		);
	  } else if ($subjectRow->resource_approved == 0) {
		// Cancel follow request
		return array(
		  'label' => $this->view->translate('Cancel Request'),
		  'action' => 'cancel',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',
		);
	  } else {
		// Unfollow
		return array(
		  'label' => $this->view->translate('Unfollow'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',
		);
	  }
	  // Subject?
	  if (null === $viewerRow) {
		// Do nothing
	  } else if ($viewerRow->resource_approved == 0) {
		// Approve follow request
		return array(
		  'label' => $this->view->translate('Approve Request'),
		  'action' => 'confirm',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',

		);
	  } else {
		// Remove as follower?
		return array(
		  'label' => $this->view->translate('Unfollow'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  }
	  if (engine_count($params) == 1) {
		return $params[0];
	  } else if (engine_count($params) == 0) {
		return 0;
	  } else {
		return $params;
	  }
	}

	// Two-way mode
	else {

	  $table =  Engine_Api::_()->getDbTable('membership', 'user');
	  $select = $table->select()
		->where('resource_id = ?', $viewer->getIdentity())
		->where('user_id = ?', $subject->getIdentity());
	  $select = $select->limit(1);
	  $row = $table->fetchRow($select);

	  if (null === $row) {
		// Add
		return array(
		  'label' => $this->view->translate('Add Friend'),
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',
		  'action' => 'add',
		);
	  } else if ($row->user_approved == 0) {
		// Cancel request
		return array(
		  'label' => $this->view->translate('Cancel Friend'),
		  'action' => 'cancel',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  } else if ($row->resource_approved == 0) {
		// Approve request
		return array(
		  'label' => $this->view->translate('Approve Request'),
		  'action' => 'confirm',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/add.png',

		);
	  } else {
		// Remove friend
		return array(
		  'label' => $this->view->translate('Remove Friend'),
		  'action' => 'remove',
		  'icon' => $this->getBaseUrl() . 'application/modules/User/externals/images/friends/remove.png',

		);
	  }
	}
  }
  function addSEAds()
  {
	// Get campaign
	if (
	  !($id = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adcampaignid', '0')) ||
	  !($campaign = Engine_Api::_()->getItem('core_adcampaign', $id))
	) {
	  return array();
	}

	// Check limits, start, and expire
	if (!$campaign->isActive()) {
	  return array();
	}

	// Get viewer
	$viewer = Engine_Api::_()->user()->getViewer();
	if (!$campaign->isAllowedToView($viewer)) {
	  return array();
	}

	// Get ad
	$table = Engine_Api::_()->getDbTable('ads', 'core');
	$select = $table->select()->where('ad_campaign = ?', $id)->order('RAND()');
	$ad =  $table->fetchRow($select);
	if (!($ad)) {
	  return array();
	}
	// Okay
	$campaign->views++;
	$campaign->save();

	$ad->views++;
	$ad->save();
	return array('campaign_id' => $campaign->getIdentity(), 'ad_id' => $ad->getIdentity(), 'ad_content' => $ad->html_code, 'content_type' => 'ads');
  }
  function getMentionTags($content)
  {
	$contentMention = $content;
	$mentions = array();
	preg_match_all('/(^|\s)(@\w+)/', $contentMention, $result);
	$counter = 0;
	foreach ($result[2] as $value) {
	  $user_id = str_replace('@_user_', '', $value);
	  if (intval($user_id) > 0) {
		$user = Engine_Api::_()->getItem('user', $user_id);
		if (!$user)
		  continue;
	  } else {
		$itemArray = explode('_', $user_id);
		$resource_id = $itemArray[count($itemArray) - 1];
		unset($itemArray[count($itemArray) - 1]);
		$resource_type = implode('_', $itemArray);
		try {
		  $user = Engine_Api::_()->getItem($resource_type, $resource_id);
		} catch (Exception $e) {
		  continue;
		}
		if (!$user || !$user->getIdentity())
		  continue;
	  }
	  $mentions[$counter]['word'] = $value;
	  $mentions[$counter]['title'] = $user->getTitle();
	  $mentions[$counter]['module'] = 'user';
	  $mentions[$counter]['href'] = $this->getBaseUrl(false, $user->getHref());
	  $mentions[$counter]['user_id'] = $user->getIdentity();
	  $counter++;
	}
	return $mentions;
  }
  function gethashtags($content)
  {
	$hashTagWords = array();
	preg_match_all("/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u", $content, $matches);
	$searchword = $replaceWord = array();
	foreach ($matches[0] as $value) {
	  $hashTagWords[] = $value;
	}
	return $hashTagWords;
  }

  function loadOnlyVideo($contentResponse)
  {
    $selectedVideos = array();
    foreach ($contentResponse as $key => $value) 
      if($value['object_type'] == "video")
        $selectedVideos[] = $value;
      return $selectedVideos;
  }
  
}
