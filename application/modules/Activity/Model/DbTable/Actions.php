<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Actions.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Model_DbTable_Actions extends Engine_Db_Table
{
  protected $_rowClass = 'Activity_Model_Action';

  protected $_serializedColumns = array('params');

  protected $_actionTypes;

  public function addActivity(
    Core_Model_Item_Abstract $subject,
    Core_Model_Item_Abstract $object,
    $type,
    $body = null,
    array $params = null,
    $postData = null
  ) {
    // Disabled or missing type
    $typeInfo = $this->getActionType($type);
    if (!$typeInfo || !$typeInfo->enabled) {
      return;
    }

    // User disabled publishing of this type
    $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
    if (!$actionSettingsTable->checkEnabledAction($subject, $type)) {
      return;
    }

    // Create action
    $action = $this->createRow();
    if (!empty($postData['scheduled_post'])) {
      $str = str_replace('_', '/', $postData['scheduled_post']);
      $date = DateTime::createFromFormat('d/m/Y H:i:s', $str);
      $scheduled_post = $date->format('Y-m-d H:i:s');
    } else {
      $scheduled_post = '';
    }

    $postingType = !empty($_POST['postingType']) ? $_POST['postingType'] : '';
    if ($postingType) {
      $itemPos = Engine_Api::_()->getItemByGuid($postingType);
    }
    $action->setFromArray(
      array(
        'type' => $type,
        'subject_type' => $subject->getType(),
        'subject_id' => $subject->getIdentity(),
        'object_type' => $object->getType(),
        'object_id' => $object->getIdentity(),
        'body' => (string) $body,
        'params' => (array) $params,
        'date' => date('Y-m-d H:i:s'),
        'resource_id' => !empty($itemPos) ? $itemPos->getIdentity() : "",
        'resource_type' => !empty($itemPos) ? $itemPos->getType() : "",
        'schedule_time' => $scheduled_post,
        'privacy' => !empty($postData['privacy']) ? rtrim($postData['privacy'], ',') : '',
        //'schedule_time' => $scheduled_post,
      )
    );
    $action->save();

    // Add bindings
    if (empty($postData['scheduled_post'])) {
      $this->addActivityBindings($action, $type, $subject, $object);
    }
    // We want to update the subject
    if (isset($subject->modified_date)) {
      $subject->modified_date = date('Y-m-d H:i:s');
      $subject->save();
    }
    return $action;
  }

  public function removeActivities(Core_Model_Item_Abstract $subject, Core_Model_Item_Abstract $object, $type = null)
  {
    $select = $this->select()
      ->where('`subject_id` = ?', $subject->getIdentity())
      ->where('`object_id` = ?', $object->getIdentity())
      ->where('`subject_type` = ?', $subject->getType())
      ->where('`object_type` = ?', $object->getType());

    if ($type) {
      $select->where('`type` = ?', $type);
    }

    foreach ($this->fetchAll($select) as $action) {
      $action->deleteItem();
    }
  }

  public function getActivity(User_Model_User $user, array $params = array())
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $actionTableName = $this->info('name');
    // Proc args
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $streamTableName = $streamTable->info('name');
    extract($this->_getInfo($params)); // action_id, limit, min_id, max_id
    $filterType = !empty($params['filterFeed']) ? $params['filterFeed'] : '';
    $hashTag = !empty($params['hashTag']) && $params['hashTag'] != 'undefined' ? $params['hashTag'] : '';
    $targetPost = !empty($params['targetPost']) ? $params['targetPost'] : '';
    $allvideos = !empty($params['allvideos']) ? $params['allvideos'] : '';
    $action_video_id = !empty($params['action_video_id']) ? $params['action_video_id'] : '';

    $settings = Engine_Api::_()->getApi('settings', 'core');

    if ($filterType == 'my_networks') {
    } else if ($filterType == 'my_friends' || $filterType == "home_friend") {
      $subjectIds = $user->membership()->getMembershipsOfIds();
      if ($filterType == "home_friend" && $viewer_id) {
        $subjectIds[] = $viewer_id;
      }
      if (!$subjectIds)
        return;
    }

    //Following Work
    else if ($filterType == 'member' && $settings->getSetting('core.followenable', 1)) {
      $followersResults = Engine_Api::_()->getDbTable('follows', 'user')->followers(array('user_id' => $viewer_id));
      foreach ($followersResults as $followersResult) {
        $subjectIds[] = $followersResult->user_id;
      }
      if (!$subjectIds)
        return;
    } else if (strpos($filterType, 'network_filter_') !== false) {
      $networkFilterId = str_replace('network_filter_', '', $filterType);
    } else if (strpos($filterType, 'member_list_') !== false) {
      $listFilterId = str_replace('member_list_', '', $filterType);
    } else if ($filterType == 'saved_feeds') {
      $customSelect = $streamTableName . '.action_id IN (SELECT action_id FROM engine4_activity_savefeeds WHERE user_id = ' . $user->getIdentity() . ')';
    }
    // Prepare main query
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);
    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $mainActionTypes = array();
    // Filter out types set as not displayable
    foreach ($masterActionTypes as $type) {
      if ($type->displayable & 4) {
        if ($type->enabled)
          $mainActionTypes[] = $type->type;
      }
    }
    // Filter types based on user request
    if (isset($showTypes) && is_array($showTypes) && !empty($showTypes)) {
      $mainActionTypes = array_intersect($mainActionTypes, $showTypes);
    } else if (isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes)) {
      $mainActionTypes = array_diff($mainActionTypes, $hideTypes);
    }
    // Nothing to show
    if (empty($mainActionTypes)) {
      return null;
    }
    // Show everything
    else if (engine_count($mainActionTypes) == engine_count($masterActionTypes)) {
      $mainActionTypes = true;
    }
    // Build where clause
    else {
      $mainActionTypes = "'" . join("', '", $mainActionTypes) . "'";
    }
    // Prepare sub queries
    if ($filterType == 'my_networks' || !empty($networkFilterId)) {
      $response = array();
      if (empty($networkFilterId)) {
        $networkIds = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfIds($user);
        if (!engine_count($networkIds))
          return;
      } else
        $networkIds = $networkFilterId;
      $response[] = array('type' => 'network', 'data' => $networkIds);
    } else if (!empty($listFilterId)) {
      $response = array();
      $list = Engine_Api::_()->getItem('user_list', $listFilterId);
      $lists = Engine_Api::_()->getDbTable('listitems', 'user');
      $listSelect = $lists->select()->from($lists->info('name'), 'child_id')->where('list_id =?', $listFilterId)->where('child_id =?', $viewer->getIdentity());
      $listUserIds = $lists->fetchAll($listSelect);
      if ($viewer->getIdentity() != $list->owner_id) {
        if (!engine_count($listUserIds)) {
          return null;
        }
      }
      $response[] = array('type' => 'members_list', 'data' => $listFilterId);
    } else {
      $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
        'for' => Engine_Api::_()->getItem('user', $user->getIdentity()),
      )
      );
      $response = (array) $event->getResponses();
    }
    if (empty($response)) {
      return null;
    }

    if (($filterType == 'scheduled_post')) {
      $union = $this->select()
        ->from($this->info('name'), 'action_id')
        ->setIntegrityCheck(false)
        ->where($actionTableName . '.schedule_time IS NOT NULL && schedule_time != ""')
        ->where($this->info('name') . '.subject_id = ' . $user->getIdentity())
        ->limit($limit);

      if (empty($action_id)) {
        $union->where('is_community_ad =?', 0);
      }
      // Add action_id/max_id/min_id
      if (null !== $action_id) {
        $union->where($this->info('name') . '.action_id = ?', $action_id);
      } else {
        if (null !== $min_id) {
          $union->where($this->info('name') . '.action_id >= ?', $min_id);
        } else if (null !== $max_id) {
          $union->where($this->info('name') . '.action_id <= ?', $max_id);
        }
      }
      $response = array();
    }

    if ($hashTag)
      $hashTagTableName = Engine_Api::_()->getDbTable('hashtags', 'activity')->info('name');

    if ($targetPost) {
      /*Target Post*/

      $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias(Engine_Api::_()->user()->getViewer());
      $gender = !empty($fields['gender']) ? $fields['gender'] : '';
      if (!$gender) {
        $genderWomen = $genderMan = 0;
      } else {
        $optionsTable = Engine_Api::_()->fields()->getTable($user->getType(), 'options');
        $optionSelect = $optionsTable->select()->where('option_id =?', $gender);
        $optionSelect = $optionsTable->fetchRow($optionSelect);
        if ($optionSelect) {
          if ($optionSelect->label == 'Male') {
            $genderMan = $optionSelect->option_id;
            $genderWomen = 0;
          } else {
            $genderWomen = $optionSelect->option_id;
            $genderMan = 0;
          }
        } else {
          $genderWomen = $genderMan = 0;
        }
      }

      //Get loggedin user location
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
        $userlocationSelect = Engine_Api::_()->getDbtable('locations', 'core')->select()->where('resource_type =?', 'user')->where('resource_id =?', $viewer_id);
        $userlocation = Engine_Api::_()->getDbtable('locations', 'core')->fetchRow($userlocationSelect);
        if (!$userlocation) {
          $country = '';
          $city = '';
          $address = '';
        } else {
          $country = empty($userlocation->country) ? "(Q*" : $userlocation->country;
          $city = empty($userlocation->city) ? "()*" : $userlocation->city;
          $address = empty($userlocation->address) ? "~!@%^&" : $userlocation->address;
        }
      }
      
      $viewer = Engine_Api::_()->user()->getViewer();
      $age = 0;
      $birthDate = 0;
      if($viewer->getIdentity()) {
        //Get loggedin user DOB
        $birthDate = $viewer->dob ? $viewer->dob : 0; //!empty($fields['birthdate']) ? $fields['birthdate'] : 0;
        $birthDate = $datem = date('m/d/Y', strtotime($birthDate));
        //explode the date to get month, day and year
        $birthDate = explode("/", $birthDate);
        //get age from date or birthdate
        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
          ? ((date("Y") - $birthDate[2]) - 1)
          : (date("Y") - $birthDate[2]));
      }
    }

    

    $attachmentTable = Engine_Api::_()->getDbTable('attachments', 'activity');
    $attachmentTableName = $attachmentTable->info('name');

    $customSelectQuery = array();
    $enabledModuleNames = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
    foreach ($enabledModuleNames as $module) {
      try {
        if ($settings->getSetting($module . '.activityfeed.filter', 0)) {
          $customSelectQuery[] = Engine_Api::_()->$module()->getActivityQuery($viewer_id, $streamTableName);
        }
      } catch (Exception $e) {
        // silence
      }
    }

    $video = engine_in_array("video", $enabledModuleNames);

    $allVideoCase = "";
    if ($allvideos) {
      foreach ($enabledModuleNames as $module) {
        try {
          $allVideoCase .= Engine_Api::_()->$module()->getAllVideosActivity($attachmentTableName);
        } catch (Exception $e) {
          // silence
        }
      }
    }

    foreach ($response as $response) {
      if (empty($response))
        continue;

      $select = $streamTable->select()
        ->from($streamTable->info('name'), array('action_id', 'group_action_id' => new Zend_Db_Expr($streamTableName . '.action_id')));
      $select->where('target_type = ?', $response['type']);

      $whereCondition = "";
      if (empty($action_id)) {
        $select->where($streamTableName . '.action_id > ?', 0);
        $whereCondition = ' AND is_community_ad = 0';
      }
      $select
        ->order($streamTableName . '.action_id DESC')
        ->limit($limit)
        ->setIntegrityCheck(false)
        ->joinLeft($this->info('name'), $this->info('name') . '.action_id = ' . $streamTableName . '.action_id AND approved = 1' . $whereCondition, null);

      if ($hashTag) {
        $select->setIntegrityCheck(false);
        $select
          ->join($hashTagTableName, "$hashTagTableName.action_id = $streamTableName.action_id", null)
          ->where($hashTagTableName . '.title = ?', $hashTag);
      }

      //get all videos 
      if ($allvideos && ($allVideoCase || $video)) {

        $select->where($actionTableName->info('name') . '.action_id' != $action_video_id);
        $select->setIntegrityCheck(false);
        $select
          ->join($attachmentTableName, "$attachmentTableName.action_id = $streamTableName.action_id", null)
          ->group("$attachmentTableName.id");
        $case = "CASE ";
        if ($video) {
          $videoTableName = "engine4_video_videos";
          $case .= "WHEN $attachmentTableName.type = 'video' AND id IN (SELECT video_id FROM " . $videoTableName . " WHERE `status` = 1 AND type = 3) THEN true ";
        }
        if ($allVideoCase) {
          $case .= $allVideoCase;
        }
        $case .= "ELSE false END";
        $select->where($case);
        echo $select;
        die;
      }
      if (empty($response['data'])) {
        // Simple
        $select->where('target_id = ?', 0);
      } else if (is_scalar($response['data']) || engine_count($response['data']) === 1) {
        // Single
        if (is_array($response['data'])) {
          list($response['data']) = $response['data'];
        }
        $select->where('target_id = ?', $response['data']);
      } else if (is_array($response['data'])) {
        // Array
        $select->where('target_id IN(?)', (array) $response['data']);
      } else {
        // Unknown
        continue;
      }

      // Add action_id/max_id/min_id
      if (null !== $action_id) {
        $select->where($actionTableName . '.action_id = ?', $action_id);
      } else {
        if (null !== $min_id) {
          $select->where($actionTableName . '.action_id >= ?', $min_id);
        } else if (null !== $max_id) {
          $select->where($actionTableName . '.action_id <= ?', $max_id);
        }
      }
      if ($mainActionTypes !== true) {
        $select->where($actionTableName . '.type IN(' . $mainActionTypes . ')');
      }
      if (!empty($subjectIds)) {
        $select->where($actionTableName . '.subject_id IN(?)', $subjectIds);
      }

      //Share filter work
      if ($filterType == 'share') {
        $select->where($actionTableName . '.type =?', $filterType);
      }
      //Share filter work

      if (!empty($customSelect)) {
        $select->where($customSelect);
      }
      if (engine_count($customSelectQuery)) {
        foreach ($customSelectQuery as $sqlData) {
          $select->where($sqlData);
        }
      }
      //hide post query work
      $select->where($actionTableName . '.action_id NOT IN (SELECT resource_id FROM engine4_activity_hides WHERE user_id = ' . $user->getIdentity() . ' AND resource_type = "post")');
      $select->where($actionTableName . '.subject_id NOT IN (SELECT resource_id FROM engine4_activity_hides WHERE user_id = ' . $user->getIdentity() . ' AND resource_type = "user")');
      // Add order/limit
      if (!empty($action_video_id)) {
        $select->order('FIELD(' . $actionTableName . '.action_id, ' . $action_video_id . ') DESC');
      }

      if ($targetPost) {
        /*Target Post*/
        $targetTableName = 'engine4_activity_targetpost';
        $select = $select

          ->joinLeft($targetTableName, $targetTableName . '.action_id = ' . $streamTableName . '.action_id', null)
        ;
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
          //location target sql
          $select->where("CASE WHEN " . $targetTableName . ".location_send = 'world' OR " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL THEN true WHEN " . $targetTableName . ".location_send = 'country' THEN " . $targetTableName . ".country_name LIKE concat('%','" . $country . "','%')  WHEN " . $targetTableName . ".location_send = 'city' THEN " . $targetTableName . ".city_name LIKE concat('%','" . $city . "','%') OR " . $targetTableName . ".location_city LIKE concat('%','" . $city . "','%') OR " . $targetTableName . ".location_city LIKE concat('%','" . $address . "','%') OR " . $targetTableName . ".city_name LIKE concat('%','" . $address . "','%') ELSE false END ");
          //location target sql end here
        }
        //gender sql starts here
        $select->where("CASE WHEN " . $targetTableName . ".gender_send = 'all' OR " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL THEN true WHEN " . $targetTableName . ".gender_send = 'women' THEN '" . $gender . "' = " . $genderWomen . " ELSE '" . $gender . "' = " . $genderMan . "  END ")
          //gender sql ends here

          //age sql starts here
          ->where("CASE WHEN " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL OR " . $targetTableName . ".age_min_send = '' OR  " . $targetTableName . ".age_max_send = '' THEN true WHEN " . $age . "  BETWEEN " . $targetTableName . ".age_min_send AND  " . $targetTableName . ".age_max_send THEN true WHEN " . $targetTableName . ".age_max_send >= 99 AND  '" . $age . "' > " . $targetTableName . ".age_max_send THEN true ELSE false  END ");
      }

      // Add to main query
      $union->union(array('(' . $select->__toString() . ')')); // (string) not work before PHP 5.2.0
    }


    // Get actions
    $actions = $db->fetchAll($union->__toString());

    // No visible actions
    if (empty($actions)) {
      return null;
    }

    $ids = array();
    foreach ($actions as $data) {
      if (!empty($data['group_action_id'])) {
        $id = trim(implode(',', array_unique(explode(',', $data['group_action_id']))), ',');
      } else {
        $id = trim($data['action_id'], ',');
      }
      $ids[] = $id;
    }
    $ids = array_filter(array_unique($ids));

    // Finally get activity
    // return $this->fetchAll(
    //   $this->select()
    //     ->from($this->info('name'),'*')
    //     ->setIntegrityCheck(false)
    //     ->where($actionTableName.'.action_id IN('.join(',', $ids).')')
    //     ->order($actionTableName.'.action_id DESC')
    //     ->limit($limit)
    // );

    $idsSelect = $this->select()
      ->from($this->info('name'), '*')
      ->setIntegrityCheck(false)
      ->where($actionTableName . '.action_id IN(' . join(',', $ids) . ')')
      ->limit($limit);
    if (!empty($action_video_id)) {
      $idsSelect->order('FIELD(' . $actionTableName . '.action_id, ' . $action_video_id . ') DESC');
    }

    $idsSelect->order($actionTableName . '.action_id DESC');

    // Finally get activity
    return $this->fetchAll($idsSelect);

  }

  public function getActivityAbout(
    Core_Model_Item_Abstract $about,
    User_Model_User $user,
    array $params = array()
  ) {
    // Proc args
    extract($this->_getInfo($params)); // action_id, limit, min_id, max_id
    $targetPost = !empty($params['targetPost']) ? $params['targetPost'] : '';
    $isOnThisDayPage = !empty($params['isOnThisDayPage']) ? $params['isOnThisDayPage'] : '';
    $filterFeed = !empty($params['filterFeed']) ? $params['filterFeed'] : '';

    $settings = Engine_Api::_()->getApi('settings', 'core');

    //get 200 post for onthisday functionity
    if ($isOnThisDayPage)
      $limit = 200;
    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $streamTableName = $streamTable->info('name');


    $actionTableName = $this->info('name');

    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);

    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $subjectActionTypes = array();
    $objectActionTypes = array();

    // Filter types based on displayable
    foreach ($masterActionTypes as $type) {
      if ($type->displayable & 1) {
        $subjectActionTypes[] = $type->type;
      }
      if ($type->displayable & 2) {
        $objectActionTypes[] = $type->type;
      }
    }

    // Filter types based on user request
    if (isset($showTypes) && is_array($showTypes) && !empty($showTypes)) {
      $subjectActionTypes = array_intersect($subjectActionTypes, $showTypes);
      $objectActionTypes = array_intersect($objectActionTypes, $showTypes);
    } else if (isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes)) {
      $subjectActionTypes = array_diff($subjectActionTypes, $hideTypes);
      $objectActionTypes = array_diff($objectActionTypes, $hideTypes);
    }
    // Nothing to show
    if (empty($subjectActionTypes) && empty($objectActionTypes)) {
      return null;
    }

    if (empty($subjectActionTypes)) {
      $subjectActionTypes = null;
    } else if (engine_count($subjectActionTypes) == engine_count($masterActionTypes)) {
      $subjectActionTypes = true;
    } else {
      $subjectActionTypes = "'" . join("', '", $subjectActionTypes) . "'";
    }

    if (empty($objectActionTypes)) {
      $objectActionTypes = null;
    } else if (engine_count($objectActionTypes) == engine_count($masterActionTypes)) {
      $objectActionTypes = true;
    } else {
      $objectActionTypes = "'" . join("', '", $objectActionTypes) . "'";
    }

    // Prepare sub queries
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
      'for' => Engine_Api::_()->getItem('user', $user->getIdentity()),
      'about' => $about,
    )
    );
    $response = (array) $event->getResponses();

    if (empty($response)) {
      return null;
    }
    $birthDate = 0;
    $age = 0;
    if ($targetPost && $about->getType() == 'user' && $about->getIdentity() != $user->getIdentity()) {
      /*Target Post*/
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias(Engine_Api::_()->user()->getViewer());
      $gender = $viewer->gender ?? 0;
     
      
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
        $locationTable = Engine_Api::_()->getDbtable('locations', 'core');
        //get loggedin user location
        $userlocationSelect = $locationTable->select()->where('resource_type =?', 'user')->where('resource_id =?', $viewer_id);
        $userlocation = $locationTable->fetchRow($userlocationSelect);
        if (!$userlocation) {
          $country = '';
          $city = '';
          $address = '';
        } else {
          $country = $userlocation->country;
          $city = $userlocation->city;
          $address = $userlocation->address;
        }
      }
      $birthDate = $viewer->dob ? $viewer->dob : 0;
      if(!empty($birthDate)){
        //get loggedin user DOB
        $birthDate = $datem = date('m/d/Y', strtotime($birthDate));
        //explode the date to get month, day and year
        $birthDate = explode("/", $birthDate);
        //get age from date or birthdate
        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
          ? ((date("Y") - $birthDate[2]) - 1)
          : (date("Y") - $birthDate[2]));
      }else{
        $birthDate = 0;
      }
    }
    

    //hidden post
    if (($filterFeed == 'hiddenpost')) {
      $hiddenTableName = 'engine4_activity_hides';
      $union = $this->select()
        ->from($this->info('name'), 'action_id')
        ->joinLeft($hiddenTableName, $hiddenTableName . '.resource_id =' . $this->info('name') . '.action_id')
        ->where('hide_id IS NOT NULL')
        ->setIntegrityCheck(false)
        ->where($hiddenTableName . '.resource_type =?', 'post')
        ->where($hiddenTableName . '.user_id = ' . $user->getIdentity())
        ->limit($limit);
      // Add action_id/max_id/min_id
      if (null !== $action_id) {
        $union->where($this->info('name') . '.action_id = ?', $action_id);
      } else {
        if (null !== $min_id) {
          $union->where($this->info('name') . '.action_id >= ?', $min_id);
        } else if (null !== $max_id) {
          $union->where($this->info('name') . '.action_id <= ?', $max_id);
        }
      }
      $response = array();
    }
    if (($filterFeed == 'taggedinpost')) {
      $union = $this->select()
        ->from($this->info('name'), 'action_id')
        ->setIntegrityCheck(false)
        ->where("body  LIKE ?  ", '%' . '@_' . $user->getGuid() . '%')
        ->where('action_id IS NOT NULL');
      // Add action_id/max_id/min_id
      if (null !== $action_id) {
        $union->where($this->info('name') . '.action_id = ?', $action_id);
      } else {
        if (null !== $min_id) {
          $union->where($this->info('name') . '.action_id >= ?', $min_id);
        } else if (null !== $max_id) {
          $union->where($this->info('name') . '.action_id <= ?', $max_id);
        }
      }
      $response = array();
    }

    if (empty($params['selectedFeedBoostPost'])) {
      $pintotop = 1;
      if ($pintotop) {
        $res_type = $about->getType();
        $res_id = $about->getIdentity();
        $table = Engine_Api::_()->getDbTable('pinposts', 'activity');
        $selectPin = $table->select()->where('resource_id	 =?', $res_id)->where('resource_type =?', $res_type);
        $res = $table->fetchRow($selectPin);
      }
    } else if (!empty($params['selectedFeedBoostPost'])) {
      $res = array();
      $res['action_id'] = $params['selectedFeedBoostPost'];
      $pintotop = true;
    }

    foreach ($response as $response) {
      if (empty($response))
        continue;

      // Target info
      $select = $streamTable->select()
        ->from($streamTable->info('name'), 'action_id')
        ->where($streamTableName . '.target_type = ?', $response['type'])
      ;


      if (!empty($res))
        $select->order('FIELD(' . $streamTableName . '.action_id, ' . (is_array($res) ? $res['action_id'] : $res->action_id) . ') DESC');
      if (empty($response['data'])) {
        // Simple
        $select->where($streamTableName . '.target_id = ?', 0);
      } else if (is_scalar($response['data']) || engine_count($response['data']) === 1) {
        // Single
        if (is_array($response['data'])) {
          list($response['data']) = $response['data'];
        }
        $select->where($streamTableName . '.target_id = ?', $response['data']);
      } else if (is_array($response['data'])) {
        // Array
        $select->where($streamTableName . '.target_id IN(?)', (array) $response['data']);
      } else {
        // Unknown
        continue;
      }
      if (empty(@$this->isOnThisDayPage) && !(@$this->isOnThisDayPage)) {
        // Add action_id/max_id/min_id
        if (null !== $action_id) {
          $select->where($streamTableName . '.action_id = ?', $action_id);
        } else {
          if (null !== $min_id) {
            $select->where($streamTableName . '.action_id >= ?', $min_id);
          } else if (null !== $max_id) {
            $select->where($streamTableName . '.action_id <= ?', $max_id);
          }
        }
      }
      // Add order/limit
      $select
        ->order($streamTableName . '.action_id DESC')
        ->limit($limit);

      /* uncomment this if pin feed not work in feed and check
        if(!empty($res))
           $union->order('FIELD(action_id,'.$res->action_id.') DESC');
        // Finish main query
        $union
          ->order('action_id DESC')
          ->limit($limit);
      */
      $select = $select->setIntegrityCheck(false)
        ->joinLeft($this->info('name'), $this->info('name') . '.action_id = ' . $streamTableName . '.action_id', null);

      if (!empty($params['communityads'])) {
        $select->where($this->info('name') . '.type IN (SELECT type from engine4_sescommunityads_feedsettings)');
        $select->where($streamTableName . '.target_type =?', 'everyone');
      }
      if ($filterFeed != "unapprovedfeed") {
        $select->where($this->info('name') . '.approved =?', 1);
      } else {
        $select->where($this->info('name') . '.approved =?', 0);
      }
      if (empty($action_id)) {
        $select->where($this->info('name') . '.is_community_ad =?', 0);
      }
      if ($filterFeed == "own" && empty($action_id)) {
        $select->where('(engine4_activity_actions.subject_type = "user" AND engine4_activity_actions.subject_id =' . $about->getOwner()->getIdentity() . ' )');
      }
      $birthDate = 0;
      $age = 0;
      if ($targetPost && $about->getType() == 'user' && $about->getIdentity() != $user->getIdentity()) {
        /*Target Post*/
        $targetTableName = 'engine4_activity_targetpost';
        $select = $select->joinLeft($targetTableName, $targetTableName . '.action_id = ' . $streamTableName . '.action_id', null);
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
          //location target sql
          $select->where("CASE WHEN " . $targetTableName . ".location_send = 'world' OR " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL THEN true WHEN " . $targetTableName . ".location_send = 'country' THEN '" . $country . "' LIKE concat('%',$targetTableName.country_name,'%')  ELSE '" . $city . "' LIKE concat('%',$targetTableName.city_name,'%') OR '" . $address . "' LIKE concat('%',$targetTableName.city_name,'%')  END ");
          //location target sql end here
        }
        //gender sql starts here
        $select->where("CASE WHEN " . $targetTableName . ".gender_send = 'all' OR " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL THEN true WHEN " . $targetTableName . ".gender_send = '".$gender."' THEN true ELSE false  END ")
          //gender sql ends here

          //age sql starts here
          ->where("CASE WHEN " . $this->info('name') . ".subject_id = '" . $viewer_id . "' OR " . $targetTableName . ".targetpost_id IS NULL THEN true WHEN " . $age . "  BETWEEN " . $targetTableName . ".age_min_send AND  " . $targetTableName . ".age_max_send THEN true WHEN " . $targetTableName . ".age_max_send >= 99 AND  '" . $age . "' > " . $targetTableName . ".age_max_send THEN true ELSE false  END ");
      } else if ($isOnThisDayPage) {
        $select = $select
          ->setIntegrityCheck(false)
          ->joinLeft($this->info('name'), $this->info('name') . '.action_id = ' . $streamTableName . '.action_id', null);

        $date = date('m-d');
        $select->where($actionTableName . '.date LIKE "%' . $date . '%"')
          ->where($actionTableName . '.date  NOT LIKE "%' . date('Y-m-d') . '%"');
        $select->order($actionTableName . '.date DESC');
      }
      //hide post query work

      // Add subject to main query
      $selectSubject = clone $select;
      if ($subjectActionTypes !== null) {
        if ($subjectActionTypes !== true) {
          $selectSubject->where($streamTableName . '.type IN(' . $subjectActionTypes . ')');
        }
        $selectSubject
          ->where($streamTableName . '.subject_type = ?', $about->getType())
          ->where($streamTableName . '.subject_id = ?', $about->getIdentity());
        $union->union(array('(' . $selectSubject->__toString() . ')')); // (string) not work before PHP 5.2.0
      }
      // Add object to main query
      $selectObject = clone $select;
      if ($objectActionTypes !== null) {
        if ($objectActionTypes !== true) {
          $selectObject->where($streamTableName . '.type IN(' . $objectActionTypes . ')');
        }
        $selectObject
          ->where($streamTableName . '.object_type = ?', $about->getType())
          ->where($streamTableName . '.object_id = ?', $about->getIdentity());
        $union->union(array('(' . $selectObject->__toString() . ')')); // (string) not work before PHP 5.2.0
      }
    }
    // Finish main query
    $union
      ->order('action_id DESC')
      ->limit($limit);

    // Get actions
    $actions = $db->fetchAll($union);

    // No visible actions
    if (empty($actions)) {
      return null;
    }

    // Process ids
    $ids = array();
    foreach ($actions as $data) {
      $ids[] = $data['action_id'];
    }
    $ids = array_unique($ids);

    $actionTableName = $this->info('name');
    $select =
      $this->select()
        ->from($this->info('name'))
        ->setIntegrityCheck(false)
        ->where($actionTableName . '.action_id IN(' . join(',', $ids) . ')')
        ->limit($limit);


    if ($pintotop) {
      if (empty($params['selectedFeedBoostPost'])) {
        $res_type = $about->getType();
        $res_id = $about->getIdentity();
        $table = Engine_Api::_()->getDbTable('pinposts', 'activity');
        $selectPin = $table->select()->where('resource_id	 =?', $res_id)->where('resource_type =?', $res_type);
        $res = $table->fetchRow($selectPin);
        if (!empty($res))
          $select->order('FIELD(' . $actionTableName . '.action_id, ' . $res->action_id . ') DESC');
      } else {
        $select->order('FIELD(' . $actionTableName . '.action_id, ' . $params['selectedFeedBoostPost'] . ') DESC');
      }
    }
    $select->order('action_id DESC');
    // Finally get activity

    return $this->fetchAll($select);
  }
  public function getListsIds()
  {
    // get viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $listTable = Engine_Api::_()->getItemTable('user_list');
    $listTableName = $listTable->info('name');

    $listUserTable = Engine_Api::_()->getItemTable('user_list_item');
    $listUserTableName = $listUserTable->info('name');
    $select = $listUserTable->select();
    $select->setIntegrityCheck(false);
    $select
      ->from($listUserTableName, "$listUserTableName.list_id")
      ->join($listTableName, "$listTableName.list_id = $listUserTableName.list_id", null)
      ->where('child_id = ?', $viewer_id);
    // return list_id column
    return $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
  }
  public function attachActivity($action, Core_Model_Item_Abstract $attachment, $mode = 1)
  {
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');

    if (is_numeric($action)) {
      $action = $this->fetchRow($this->select()->where('action_id = ?', $action)->limit(1));
    }

    if (!($action instanceof Activity_Model_Action)) {
      $eInfo = (is_object($action) ? get_class($action) : $action);
      throw new Activity_Model_Exception(sprintf('Invalid action passed to attachActivity: %s', $eInfo));
    }

    $attachmentRow = $attachmentTable->createRow();
    $attachmentRow->action_id = $action->action_id;
    $attachmentRow->type = $attachment->getType();
    $attachmentRow->id = $attachment->getIdentity();
    $attachmentRow->mode = (int) $mode;
    $attachmentRow->save();

    $action->attachment_count++;
    $action->save();

    return $this;
  }

  public function detachFromActivity(Core_Model_Item_Abstract $attachment)
  {
    $attachmentsTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentsTable->select()
      ->where('`type` = ?', $attachment->getType())
      ->where('`id` = ?', $attachment->getIdentity())
    ;

    foreach ($attachmentsTable->fetchAll($select) as $row) {
      $this->update(
        array(
          'attachment_count' => new Zend_Db_Expr('attachment_count - 1'),
        ), array(
          'action_id = ?' => $row->action_id,
        )
      );
      $row->delete();
    }

    return $this;
  }



  // Actions

  public function getActionById($action_id)
  {
    return $this->find($action_id)->current();
  }
  public function getActionsByObjectType(Core_Model_Item_Abstract $object, $type = "")
  {
    $select = $this->select()->where('object_type = ?', $object->getType())
      ->where('object_id = ?', $object->getIdentity())
      ->where('type =?', $type);
    return $this->fetchAll($select);
  }
  public function getActionsByObject(Core_Model_Item_Abstract $object)
  {
    $select = $this->select()->where('object_type = ?', $object->getType())
      ->where('object_id = ?', $object->getIdentity());
    return $this->fetchAll($select);
  }

  public function getActionsBySubject(Core_Model_Item_Abstract $subject)
  {
    $select = $this->select()
      ->where('subject_type = ?', $subject->getType())
      ->where('subject_id = ?', $subject->getIdentity())
    ;

    return $this->fetchAll($select);
  }

  public function getActionsByAttachment(Core_Model_Item_Abstract $attachment)
  {
    // Get all action ids from attachments
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentTable->select()
      ->where('type = ?', $attachment->getType())
      ->where('id = ?', $attachment->getIdentity())
    ;

    $actions = array();
    foreach ($attachmentTable->fetchAll($select) as $attachmentRow) {
      $actions[] = $attachmentRow->action_id;
    }

    // Get all actions
    $select = $this->select()
      ->where('action_id IN(\'' . join("','", $ids) . '\')')
    ;

    return $this->fetchAll($select);
  }



  // Utility

  /**
   * Add an action-privacy binding
   *
   * @param int $action_id
   * @param string $type
   * @param Core_Model_Item_Abstract $subject
   * @param Core_Model_Item_Abstract $object
   * @return int The insert id
   */
  public function addActivityBindings($action)
  {
    // Get privacy bindings
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('addActivity', array(
      'subject' => $action->getSubject(),
      'object' => $action->getObject(),
      'type' => $action->type,
      'privacy' => $action->privacy
    )
    );
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    // check privacy is network base
    $isNetworkBasePost = false;
    $isMemberBasePost = false;
    $isFriendBasePost = false;
    if ($action->privacy) {
      if (strpos($action->privacy, 'network_list_') !== false) {
        $networkIds = explode(',', $action->privacy);
        $isNetworkBasePost = true;
        foreach ($networkIds as $target_id) {
          $streamTable->insert(
            array(
              'action_id' => $action->action_id,
              'type' => $action->type,
              'target_type' => (string) 'network',
              'target_id' => (int) str_replace('network_list_', '', $target_id),
              'subject_type' => $action->subject_type,
              'subject_id' => $action->subject_id,
              'object_type' => $action->object_type,
              'object_id' => $action->object_id,
            )
          );
        }
      }
      // check privacy is member lists based
      else if (strpos($action->privacy, 'members_list_') !== false) {
        $memberlists = explode(',', $action->privacy);
        $isMemberBasePost = true;
        foreach ($memberlists as $target_id) {
          $streamTable->insert(
            array(
              'action_id' => $action->action_id,
              'type' => $action->type,
              'target_type' => (string) 'members_list',
              'target_id' => (int) str_replace('members_list_', '', $target_id),
              'subject_type' => $action->subject_type,
              'subject_id' => $action->subject_id,
              'object_type' => $action->object_type,
              'object_id' => $action->object_id,
            )
          );
        }
      } else if (strpos($action->privacy, 'friends_list_') !== false) {
        $memberIds = explode(',', $action->privacy);
        $isFriendBasePost = true;
        foreach ($memberIds as $target_id) {
          $streamTable->insert(
            array(
              'action_id' => $action->action_id,
              'type' => $action->type,
              'target_type' => (string) 'friend',
              'target_id' => (int) str_replace('friends_list_', '', $target_id),
              'subject_type' => $action->subject_type,
              'subject_id' => $action->subject_id,
              'object_type' => $action->object_type,
              'object_id' => $action->object_id,
            )
          );
        }
      }
    }
    foreach ((array) $event->getResponses() as $response) {
      if (($isNetworkBasePost || $isMemberBasePost || $isFriendBasePost) && ($response['type'] == 'network' || $response['type'] == 'members' || $response['type'] == 'everyone' || $response['type'] == 'registered')) {
        continue;
      } else if ($action->privacy == 'onlyme' && $response['type'] != 'owner')
        continue;
      else if ($action->privacy == 'friends' && ($response['type'] == 'network' || $response['type'] == 'everyone' || $response['type'] == 'registered'))
        continue;
      else if (isset($response['target'])) {
        $target_type = $response['target'];
        $target_id = 0;
      } else if (isset($response['type']) && isset($response['identity'])) {
        $target_type = $response['type'];
        $target_id = $response['identity'];
      } else {
        continue;
      }

      $streamTable->insert(
        array(
          'action_id' => $action->action_id,
          'type' => $action->type,
          'target_type' => (string) $target_type,
          'target_id' => (int) $target_id,
          'subject_type' => $action->subject_type,
          'subject_id' => $action->subject_id,
          'object_type' => $action->object_type,
          'object_id' => $action->object_id,
        )
      );
    }
    return $this;
  }

  public function clearActivityBindings($action)
  {
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $streamTable->delete(
      array(
        'action_id = ?' => $action->getIdentity(),
      )
    );
  }

  public function resetActivityBindings($action)
  {
    if ($action->getObject()) {
      $this->clearActivityBindings($action);
      $this->addActivityBindings($action);
    }
    return $this;
  }



  // Types

  /**
   * Gets action type meta info
   *
   * @param string $type
   * @return Engine_Db_Row
   */
  public function getActionType($type)
  {
    return $this->getActionTypes()->getRowMatching('type', $type);
  }

  /**
   * Gets all action type meta info
   *
   * @param string|null $type
   * @return Engine_Db_Rowset
   */
  public function getActionTypes()
  {
    if (null === $this->_actionTypes) {
      $table = Engine_Api::_()->getDbtable('actionTypes', 'activity');
      $this->_actionTypes = $table->fetchAll();
    }

    return $this->_actionTypes;
  }



  // Utility

  protected function _getInfo(array $params)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $args = array(
      'limit' => $settings->getSetting('activity.length', 20),
      'action_id' => null,
      'max_id' => null,
      'min_id' => null,
      'showTypes' => null,
      'hideTypes' => null,
    );

    $newParams = array();
    foreach ($args as $arg => $default) {
      if (!empty($params[$arg])) {
        $newParams[$arg] = $params[$arg];
      } else {
        $newParams[$arg] = $default;
      }
    }

    return $newParams;
  }


  private function getHashTagActionsIds($search)
  {
    $hashtagIds = $actionIds = array();
    $tagTable = Engine_Api::_()->getDbtable('tags', 'core');
    $tagmapTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $tagTableRow = $tagTable->fetchRow(
      $tagTable->select()
        ->where('text = ?', $search)
    );
    if ($tagTableRow) {
      $hashtagIds[] = $tagTableRow->tag_id;
    }
    if (empty($search)) {
      $hashtagIds = $tagTable->select()->from($tagTable->info('name'), 'tag_id')->query()->fetchAll();
    }

    foreach ($hashtagIds as $hashtagId) {
      $rowsets = $tagmapTable->fetchAll($tagmapTable->select()->where('tag_id = ?', $hashtagId));

      foreach ($rowsets as $row) {
        if ($row->resource_type == 'activity_action') {
          $actionIds[] = $row->resource_id;
          continue;
        }
        if ($row->resource_type == 'activity_comment') {
          try {
            $item = Engine_Api::_()->getItem($row->resource_type, $row->resource_id);
            $item = $item ? $item->getParent() : null;
          } catch (Exception $e) {
            $item = null;
          }
          if (!empty($item)) {
            $actionIds[] = $item->getIdentity();
          }
          continue;
        }
        if ($row->resource_type == 'core_comment') {
          $item = Engine_Api::_()->getItem($row->resource_type, $row->resource_id);
          $row = !empty($item) ? $item : $row;
        }

        $select = $attachmentTable->select()
          ->where('type = ? ', $row->resource_type)
          ->where('id = ? ', $row->resource_id);
        $attachments = $attachmentTable->fetchAll($select);
        foreach ($attachments as $attachment) {
          $action = Engine_Api::_()->getItem('activity_action', $attachment->action_id);
          if (!$action) {
            continue;
          }

          $actionIds[] = $attachment->action_id;
        }
      }
    }

    return $actionIds;
  }

  function deleteActivityFeed($params = array())
  {
    $select = $this->select()
      ->where('type =?', $params['type'])
      ->where('subject_id =?', $params['subject_id'])
      ->where('object_type =?', $params['object_type'])
      ->where('object_id =?', $params['object_id']);
    $actionObject = $this->fetchRow($select);
    if ($actionObject)
      $actionObject->delete();
  }
}
