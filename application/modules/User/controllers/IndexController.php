<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10075 2013-07-30 21:51:18Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {

  }

  public function homeAction()
  {
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }

    if( !Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled()
        ;
  }

  public function browseAction()
  {
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }
    if( !$this->_executeSearch() ) {
      // throw new Exception('error');
    }

    $this->view->isAjaxSearch = $this->_getParam('ajax');

    if ($this->view->isAjaxSearch) {
      $this->renderScript('_browseUsers.tpl');
    }

    if( !$this->view->isAjaxSearch ) {
      // Render
      $this->_helper->content
          ->setEnabled()
          ;
    }
  }

  protected function _executeSearch()
  {
    // Check form
    $form = new User_Form_Search(array(
      'type' => 'user'
    ));

    if (!$form->isValid($this->_getAllParams())) {
      $this->view->error = true;
      $this->view->totalUsers = 0; 
      $this->view->userCount = 0; 
      $this->view->page = 1;
      return false;
    }

    $this->view->form = $form;

    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $ajax = (bool) $this->_getParam('ajax', false);
    $options = $form->getValues();
    
    // Process options
    $tmp = array();
    $originalOptions = $options;
    foreach ($options as $k => $v) {
      if (null == $v || '' == $v || (is_array($v) && engine_count(array_filter($v)) == 0)) {
        continue;
      } elseif (false !== strpos($k, '_field_')) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } elseif (false !== strpos($k, '_alias_')) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $options = $tmp;

    // Get table info
    $table = Engine_Api::_()->getItemTable('user');
    $userTableName = $table->info('name');

    $searchTable = Engine_Api::_()->fields()->getTable('user', 'search');
    $searchTableName = $searchTable->info('name');

    
    //extract($options); // displayname
    $profile_type = @$options['profile_type'];
    $displayname = @$options['displayname'];
    if (!empty($options['extra'])) {
      extract($options['extra']); // is_online, has_photo, submit
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $excludedLevels = array(1, 2, 3);
    $allBlockedUsers = array();

    if( $viewerId ) {
      $blockTable = Engine_Api::_()->getDbtable('block', 'user');
      $blockedSelect = $blockTable->select()
        ->from('engine4_user_block', 'blocked_user_id')
        ->where('user_id = ?', $viewerId);
      $blockedUsers = $blockTable->fetchAll($blockedSelect)->toArray();

      foreach( $blockedUsers as $blockedUser ) {
        array_push($allBlockedUsers, $blockedUser['blocked_user_id']);
      }
      $this->view->blockedUserIds = $allBlockedUsers;

      if( !engine_in_array($viewer->level_id, $excludedLevels) ) {
        $blockedBySelect = $blockTable->select()
          ->from('engine4_user_block', 'user_id')
          ->where('blocked_user_id = ?', $viewerId);
        $blockedByUsers = $blockTable->fetchAll($blockedBySelect)->toArray();

        foreach( $blockedByUsers as $blockedByUser ) {
          array_push($allBlockedUsers, $blockedByUser['user_id']);
        }
      } else {

        unset($allBlockedUsers);
      }
    }

    // Contruct query
    $select = $table->select()
      ->setIntegrityCheck(false)
      ->from($userTableName)
      ->joinLeft($searchTableName, "`{$searchTableName}`.`item_id` = `{$userTableName}`.`user_id`", null)
      //->group("{$userTableName}.user_id")
      ->where("{$userTableName}.search = ?", 1)
      ->where("{$userTableName}.enabled = ?", 1);
      
    //Location search
    $primaryId = current($table->info("primary"));
    
    $tableLocationName = Engine_Api::_()->getDbtable('locations', 'core')->info('name');
    $location = $this->_getParam('location', '');
    $lat = $this->_getParam('lat', '');
    $lng = $this->_getParam('lng', '');
    $miles = $this->_getParam('miles', 50);
    
    $enablesigupfields = (array) json_decode(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.enablesigupfields', '["confirmpassword","dob","gender","profiletype","timezone","language","location"]'));
    
    //Location Based search
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) && isset($enablesigupfields) && engine_in_array('location', $enablesigupfields)) {

      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 2 && !empty($location)) {
        $select->where($userTableName . '.location LIKE ?', $location . '%');
      } else if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {

        if(empty($lat) && empty($lng) && !empty($_COOKIE['location_data'])) {
          $location = $_COOKIE['location_data'];
          $lat = $_COOKIE['location_lat'];
          $lng = $_COOKIE['location_lng'];
          $miles = 50;
        }

        if(!empty($lat) && !empty($lng) && !empty($location) && $lat != 'undefined') {

          //This is the maximum distance (in miles) away from $origLat, $origLon in which to search
          $dist = !empty($miles) ? $miles : 50;

          $searchType = !empty(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.search.type', 1)) ? 3956 : 6371;

          $origLat = $lat;
          $origLon = $lng;

          $asinSort = array('lat', 'lng', 'distance' => new Zend_Db_Expr(($searchType . " * 2 * ASIN(SQRT( POWER(SIN(($origLat - abs(lat))*pi()/180/2),2) + COS($origLat*pi()/180 )*COS(abs(lat)*pi()/180) *POWER(SIN(($origLon-lng)*pi()/180/2),2)))")));
          $select->joinLeft($tableLocationName, $tableLocationName . '.resource_id = ' . $userTableName . '.'.$primaryId .' AND ' . $tableLocationName . '.resource_type = "user" ', $asinSort);
          $select->where($tableLocationName . ".lng between ($origLon-$dist/abs(cos(radians($origLat))*69)) and ($origLon+$dist/abs(cos(radians($origLat))*69)) and " . $tableLocationName . ".lat between ($origLat-($dist/69)) and ($origLat+($dist/69))");
          $select->order('distance');
          $select->having("distance < $dist");
        }
      }
    }
    
    if( !empty($allBlockedUsers) ) {
      $select->where("user_id NOT IN (?)", $allBlockedUsers);
    }
    $searchDefault = true;

    // Build the photo and is online part of query
    if (isset($has_photo) && !empty($has_photo)) {
      $select->where($userTableName.'.photo_id != ?', "0");
      $searchDefault = false;
    }

    if (isset($is_online) && !empty($is_online)) {
      $select
        ->joinRight("engine4_user_online", "engine4_user_online.user_id = `{$userTableName}`.user_id", null)
        ->group("engine4_user_online.user_id")
        ->where($userTableName.'.user_id != ?', "0");
      $searchDefault = false;
    }

    // Add displayname
    if (!empty($displayname)) {
      $select->where("(`{$userTableName}`.`username` LIKE ? || `{$userTableName}`.`displayname` LIKE ?)", "%{$displayname}%");
      $searchDefault = false;
    }
    
    if(!empty($options['gender'])) {
      $select->where($userTableName.'.gender =?', $options['gender']);
    }

    // Build search part of query
    $searchParts = Engine_Api::_()->fields()->getSearchQuery('user', $options);
    foreach ($searchParts as $k => $v) {
      if (strpos($k, 'FIND_IN_SET') !== false) {
        $select->where("{$k}", $v);
        continue;
      }

      $select->where("`{$searchTableName}`.{$k}", $v);

      if (isset($v) && $v != "") {
        $searchDefault = false;
      }
    }

    $orderby = $this->getParam('orderby');
    $orderbyOptions = array('member_count', 'creation_date');

    if (!empty($orderby) && engine_in_array($orderby, $orderbyOptions)) {
      $select->order($orderby . " DESC");
    } elseif ($searchDefault) {
      $select->order("{$userTableName}.lastlogin_date DESC");
    } else {
      $select->order("{$userTableName}.displayname ASC");
    }

    // Build paginator
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(12);
    $paginator->setCurrentPageNumber($page);
    
    $this->view->page = $page;
    $this->view->ajax = $ajax;
    $this->view->users = $paginator;
    $this->view->totalUsers = $paginator->getTotalItemCount();
    $this->view->userCount = $paginator->getCurrentItemCount();
    $this->view->topLevelId = $form->getTopLevelId();
    $this->view->topLevelValue = $form->getTopLevelValue();
    $this->view->formValues = array_filter($originalOptions);

    return true;
  }

  public function getusersAction() {
    
    $extraParamsObj = $this->_getParam('extraParamsObj', null);
    
    $type = $this->_getParam('type', null);
    $data = array();
    $users_table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $users_table->select()
                    ->where('displayname LIKE ? ', $this->_getParam('text') . '%');
    if(!empty($type) && $type == 'phone') {
      $select->where('phone_number <> ?', '');
    }

    if(!empty($type) && $type == 'phonenumber') {
      $select->where('phone_number <> ?', '');
    }
    
    if(engine_count($extraParamsObj) > 0) {
      if(!empty($extraParamsObj['memberlevel'])) {
        $select->where('level_id =?', $extraParamsObj['memberlevel']);
      }
    }
    $select->order('displayname ASC')->limit('40');

    $users = $users_table->fetchAll($select);

    foreach ($users as $user) {
      $user_icon_photo = $this->view->itemPhoto($user, 'thumb.icon');
      if(!empty($type) && $type == 'phonenumber') {
        $data[] = array(
          'id' => $user->user_id,
          'label' => $user->getTitle(false)  . ' - (+'.$user->country_code.'-' . $user->phone_number .')',
          'photo' => $user_icon_photo
        );
      } else {
        $data[] = array(
            'id' => $user->user_id,
            'label' => $user->getTitle(false),
            'photo' => $user_icon_photo
        );
      }
    }
    return $this->_helper->json($data);
  }
}
