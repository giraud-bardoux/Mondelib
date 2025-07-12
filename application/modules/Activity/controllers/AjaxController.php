<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AjaxController.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_AjaxController extends Core_Controller_Action_Standard {

  public function emojiAction()
  {
    $this->view->edit = $this->_getParam('edit', false);
    $this->renderScript('_emoji.tpl');
  }

  public function commentLikesAction()
  {
    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $this->view->comment_id = $comment_id = $this->_getParam('comment_id');

    if ($resource_type == 'activity_action') {
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($resource_id);
      $resource = $action->likes(true);
    } else {
      $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
    }

    if ($resource->getType() == 'activity_action') {
      $resource_type = 'activity_comment';
      $table = Engine_Api::_()->getItemTable('core_like');
    } else {
      $resource_type = 'core_comment';
      $table = Engine_Api::_()->getItemTable('core_like');
    }
    $tableName = $table->info('name');

    $this->view->title = $this->view->translate('People Who Like This');
    $this->view->page = $page = $this->_getParam('page', 1);

    $select = $table->select()
      ->from($tableName, array('*', 'subject_type' => 'poster_type', 'subject_id' => 'poster_id'))
      ->setIntegrityCheck(false);

    $select->where('resource_id =?', $comment_id);

    if ($resource_type != 'activity_action')
      $select->where('resource_type =?', $resource_type);


    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    $this->view->users = $paginator;
    if ($is_ajax) {
      echo $this->view->partial(
        '_contentlikesuser.tpl',
        'activity',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => 'contentlikeusers', 'resource_id' => $this->view->resource_id, 'resource_type' => $resource_type, 'execute' => true, 'page' => $this->view->page)
      );
      die;
    }
  }

  public function tagPeopleAction()
  {
    $this->view->resource_id = $resource_id = $this->_getParam('action_id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($resource_id);
    $select = Engine_Api::_()->getDbTable('tagusers', 'activity')->getActionMembers($action->getIdentity());
    $resource_type = 'activity_action';
    $this->view->title = $this->view->translate('People Tagged');
    $this->view->page = $page = $this->_getParam('page', 1);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    foreach ($paginator as $data) {

      $users[] = $data['user_id'];

    }
    $users = array_values(array_unique($users));
    $this->view->users = Engine_Api::_()->getItemMulti('user', $users);
    if ($is_ajax) {
      echo $this->view->partial(
        '_contentlikesuser.tpl',
        'activity',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => 'contentlikeusers', 'resource_id' => $this->view->resource_id, 'resource_type' => $resource_type, 'execute' => true, 'page' => $this->view->page)
      );
      die;

    }
  }

  public function likesAction() {
  
    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
    $this->view->typeSelected = $typeSelected = $this->_getParam('type', 'all');
    $this->view->item_id = $item_id = $this->_getParam('item_id', false);
    if (!$typeSelected)
      $this->view->typeSelected = $typeSelected = 'all';
    if ($resource_type == 'activity_action') {
      $table = Engine_Api::_()->getItemTable('activity_like');
    } else {
      $table = Engine_Api::_()->getItemTable('core_like');
    }
    $this->view->page = $page = $this->_getParam('page', 1);

    $select = $table->select()
      ->from($table->info('name'), '*')
      ->setIntegrityCheck(false);

    $select->where($table->info('name') . '.resource_id =?', $item_id);

    if ($resource_type != 'activity_action')
      $select->where($table->info('name') . '.resource_type =?', $resource_type);

    if ($typeSelected != 'all')
      $select->where($table->info('name') . '.type =?', $typeSelected);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    $type = array();
    foreach ($paginator as $data) {
      $type[$data['poster_id'] . '_' . $data['poster_type']] = $data['type'];
      /*if( $data['poster_type'] == 'user' )
      {
        $users[] = $data['poster_id'];
      }*/
    }
    //$users = array_values(array_unique($users));
    $this->view->type = $type;
    if (!$is_ajax) {
      $this->view->action = $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($resource_id);
      $AllTypesCount = Engine_Api::_()->comment()->likesGroup($action);
      $this->view->AllTypesCount = $AllTypesCount['data'];
      $countAllLikes = 0;
      $typesLikeData = array('all' => 'all');
      foreach ($this->view->AllTypesCount as $countlikes) {
        $typesLikeData[$countlikes['type']] = $countlikes['type'];
        $countAllLikes = $countAllLikes + $countlikes['counts'];
      }

      $this->view->typesLikeData = $typesLikeData;
      $this->view->countAll = $countAllLikes;
    }

    $this->view->users = $paginator;

    if ($is_ajax) {
      echo $this->view->partial(
        '_reactionlikesuser.tpl',
        'activity',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => $this->view->typeSelected, 'resource_id' => $this->view->resource_id, 'resource_type' => $this->view->resource_type, 'typeSelected' => $this->view->typeSelected, 'execute' => true, 'page' => $this->view->page, 'type' => $this->view->type, 'item_id' => $item_id)
      );
      die;

    }
  }
  public function feedAction()
  {
    // Get config options for activity
    $config = array(
      'action_id' => (int) $this->_getParam('action_id'),
      'max_id' => (int) $this->_getParam('maxid'),
      'min_id' => (int) $this->_getParam('minid'),
      'limit' => (int) $this->_getParam('limit'),
    );

    $viewer = Engine_Api::_()->user()->getViewer();

    if (!isset($subject) && Engine_Api::_()->core()->hasSubject()) {
      $subject = Engine_Api::_()->core()->getSubject();
    }

    if (!empty($subject)) {
      $activity = Engine_Api::_()->getDbtable('actions', 'activity')->getActivityAbout($subject, $viewer, $config);
      $this->view->subjectGuid = $subject->getGuid(false);
    } else {
      $activity = Engine_Api::_()->getDbtable('actions', 'activity')->getActivity($viewer, $config);
      $this->view->subjectGuid = null;
    }

    $feed = array();
    foreach ($activity as $action) {
      $attachments = array();
      if ($action->attachment_count > 0) {
        foreach ($action->getAttachments() as $attachment) {
          $attachments[] = array(
            'meta' => $attachment->meta->toArray(),
            'item' => $attachment->item->toRemoteArray(),
          );
        }
      }
      $feed[] = array(
        'typeinfo' => $action->getTypeInfo()->toArray(),
        'action' => $action->toArray(),
        'subject' => $action->getSubject()->toRemoteArray(),
        'object' => $action->getObject()->toRemoteArray(),
        'attachments' => $attachments
      );
    }
    $this->view->feed = $feed;
  }
  /*function to set your files*/
  function downloadAction()
  {
    $storage_id = $this->_getParam('storage_id', '');
    $storage = Engine_Api::_()->getItem('storage_file', $storage_id);

    if (!$storage_id || Engine_Api::_()->user()->getViewer()->getIdentity() == 0 || !$storage)
      return $this->_forward('notfound', 'error', 'core');

    $fileOrg = $storage->map();
    $name = $storage->name;
    $mime_type = $storage->mime_major . '/' . $storage->mime_minor;
    echo $body = $storage->temporary();
    die;
    set_time_limit(0);

    header('Content-type: ' . $mime_type);
    echo $body;
    $file = file_get_contents($fileOrg);
    //if(!is_readable($file)) die('File not found or inaccessible!');
    $fileName = time() . '_sesalbum';
    $fileOrg = current(explode('?', $fileOrg));
    $PhotoExtension = '.' . pathinfo($fileOrg, PATHINFO_EXTENSION);
    $filenameInsert = $fileName . $PhotoExtension;
    $copySuccess = @copy($fileOrg, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/' . $filenameInsert);
    $name = rawurldecode($name);
    @ob_end_clean();
    $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary/' . $filenameInsert;
    $size = filesize($file);


    if (ini_get('zlib.output_compression'))
      ini_set('zlib.output_compression', 'Off');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header("Content-Transfer-Encoding: binary");
    header('Accept-Ranges: bytes');

    if (isset($_SERVER['HTTP_RANGE'])) {
      list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
      list($range) = explode(",", $range, 2);
      list($range, $range_end) = explode("-", $range);
      $range = intval($range);
      if (!$range_end) {
        $range_end = $size - 1;
      } else {
        $range_end = intval($range_end);
      }

      $new_length = $range_end - $range + 1;
      header("HTTP/1.1 206 Partial Content");
      header("Content-Length: $new_length");
      header("Content-Range: bytes $range-$range_end/$size");
    } else {
      $new_length = $size;
      header("Content-Length: " . $size);
    }

    $chunksize = 1 * (1024 * 1024);
    $bytes_send = 0;
    if ($file = fopen($file, 'r')) {
      if (isset($_SERVER['HTTP_RANGE']))
        fseek($file, $range);

      while (
        !feof($file) &&
        (!connection_aborted()) &&
        ($bytes_send < $new_length)
      ) {
        $buffer = fread($file, $chunksize);
        echo ($buffer);
        flush();
        $bytes_send += strlen($buffer);
      }
      @unlink($file);
      fclose($file);
    } else
      die('Error - can not open file.');

    die();
  }
  public function feedBuySellAction()
  {
    $this->view->action_id = $action_id = $this->_getParam('action_id', false);
    $this->view->photo_id = $this->_getParam('photo_id', false);
    $this->view->action = Engine_Api::_()->getItem('activity_action', $action_id);
    $this->view->item = $this->view->action->getBuySellItem();
    $this->view->main_action = Engine_Api::_()->getItem('activity_action', $this->_getParam('main_action'));
  }
  public function messageAction()
  {
    $this->view->action_id = $action_id = $this->_getParam('action_id', false);
    $this->view->action = Engine_Api::_()->getItem('activity_action', $action_id);
    $this->view->item = $this->view->action->getBuySellItem();
    // Make form
    $this->view->form = $form = new Activity_Form_Message();

    // Not post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }

    // Not valid
    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Start transaction
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();
      $recipientsUser = Engine_Api::_()->getItem('user', $this->view->action->subject_id);
      $recipients = $recipientsUser->getIdentity();
      $viewer = Engine_Api::_()->user()->getViewer();
      // Create conversation
      $body = $this->view->partial('ajax/message.tpl', 'activity', array('isajax' => true, 'action' => $this->view->action, 'item' => $this->view->item));
      $body = $values['body'] . '<br><br>' . $body;
      $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
        $viewer,
        $recipients,
        $values['title'],
        $body,
        $attachment
      );


      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
        $recipientsUser,
        $viewer,
        $conversation,
        'message_new'
      );
      $db->commit();
      return $this->_forward(
        'success',
        'utility',
        'core',
        array(
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.')),
          'smoothboxClose' => true,
        )
      );

    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }
  public function buysellsoldAction()
  {
    $action_id = $this->_getParam('action_id');
    $action = Engine_Api::_()->getItem('activity_action', $action_id);
    $item = $action->getBuySellItem();
    $item->is_sold = 1;
    $item->save();
    echo true;
    die;
  }
  public function savefeedAction()
  {
    $actionid = $this->_getParam('action_id', false);
    if (!$actionid) {
      echo false;
      die;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $isSaved = Engine_Api::_()->getDbTable('savefeeds', 'activity')->isSaved(array('action_id' => $actionid, 'user_id' => $viewer->getIdentity()));
    if ($isSaved) {
      $isSaved->delete();
      echo json_encode(array('status' => 1, 'issaved' => 0));
      die;
    } else {
      $db = Engine_Db_Table::getDefaultAdapter();
      $data = array(
        'action_id' => $actionid,
        'user_id' => $viewer->getIdentity(),
      );
      $db->insert('engine4_activity_savefeeds', $data);
      echo json_encode(array('status' => 1, 'issaved' => 1));
      die;
    }
  }
  public function commentableAction()
  {
    $action_id = $this->getParam('action_id', false);

    if (!$action_id) {
      echo false;
      die;
    }

    $action = Engine_Api::_()->getItem('activity_action', $action_id);

    $action->commentable = !$action->commentable;
    $action->save();
    $feed = $this->view->activity($action, array('ulInclude' => true));
    echo json_encode(array('status' => 1, 'action_id' => $action_id, 'feed' => $feed), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;
  }
  public function unhidefeedAction()
  {
    $action_id = $this->_getParam('action_id', false);
    $type = $this->_getParam('type', 'post');
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->delete(
      'engine4_activity_hides',
      array(
        'resource_id =?' => $action_id,
        'resource_type =?' => $type,
        'user_id =?' => $viewer->getIdentity(),
      )
    );
    echo true;
    die;
  }
  public function hidefeedAction()
  {
    $action_id = $this->_getParam('action_id', false);
    $subject_id = $this->_getParam('subject_id', false);
    $remove = $this->getParam('remove', false);
    $type = $this->_getParam('type', 'post');
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();
    if (!$action_id) {
      echo false;
      die;
    }
    if ($type != 'user') {
      $resource_id = $action_id;
      $id = Engine_Api::_()->getItem('activity_action', $action_id)->getSubject()->getIdentity();
      $db->delete(
        'engine4_activity_hides',
        array(
          'resource_id =?' => $id,
          'resource_type =?' => 'user',
          'user_id =?' => $viewer->getIdentity(),
        )
      );
    } else {
      $resource_id = Engine_Api::_()->getItem('activity_action', $action_id)->getSubject()->getIdentity();
      $db->delete(
        'engine4_activity_hides',
        array(
          'resource_id =?' => $action_id,
          'resource_type =?' => 'post',
          'user_id =?' => $viewer->getIdentity(),
        )
      );
    }


    if (!$remove) {
      $data = array(
        'resource_id' => $resource_id,
        'subject_id' => $subject_id,
        'resource_type' => $type,
        'user_id' => $viewer->getIdentity(),
      );
      $db->insert('engine4_activity_hides', $data);
    } else {
      $db->delete(
        'engine4_activity_hides',
        array(
          'resource_id =?' => $resource_id,
          'resource_type =?' => $type,
          'user_id =?' => $viewer->getIdentity(),
        )
      );
    }
    $lists = $this->_getParam('lists', false);
    $users = array();
    if ($lists) {
      $lists = explode(',', $lists);
      foreach ($lists as $list) {
        $action = Engine_Api::_()->getItem('activity_action', $list);
        if ($action->getSubject()->getIdentity() == $resource_id)
          $users[] = $list;
      }
    }
    echo json_encode(array('list' => $users));
    ;
    die;
  }
  public function settingsAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->resource_id = $resource_id = $viewer->getIdentity();
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $table = Engine_Api::_()->getDbTable('hides', 'activity');
    $this->view->title = $this->view->translate('See whose activity feeds you have hidden');
    $this->view->page = $page = $this->_getParam('page', 1);
    $select = $table->select()->where('user_id =?', $resource_id);
    $select->where('resource_type =?', 'user');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    foreach ($paginator as $data) {
      $users[] = $data['resource_id'];
    }
    $users = array_values(array_unique($users));
    $this->view->users = Engine_Api::_()->getItemMulti('user', $users);
    if ($is_ajax) {
      echo $this->view->partial(
        '_contentlikesuser.tpl',
        'activity',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => 'contentlikeusers', 'resource_id' => $this->view->resource_id, 'resource_type' => $resource_type, 'execute' => true, 'page' => $this->view->page)
      );
      die;

    }
  }
  public function settingremoveAction()
  {
    $users = $this->_getParam('user', false);
    if (!$users) {
      echo false;
      die;
    }
    $user = explode(',', ltrim($users, ','));
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();
    try {
      foreach (array_filter($user) as $us) {
        $db->delete(
          'engine4_activity_hides',
          array(
            'resource_id =?' => $us,
            'resource_type =?' => 'user',
            'user_id =?' => $viewer->getIdentity(),
          )
        );
      }
    } catch (Exception $e) {
      throw $e;
    }
    echo true;
    die;
  }
  public function friendsAction()
  {

    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer->getIdentity()) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');
      $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
      $select = $table->select();
      $enabledModuleNames = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
      $customSqls = array();
      $exists = false;
      foreach ($enabledModuleNames as $module) {

        try {
          $sqlQuery = Engine_Api::_()->$module()->taggedInFeed($text, (int) $this->_getParam('limit', 20));
          if ($sqlQuery) {
            $customSqls[] = $sqlQuery;
          }
          $exists = true;
        } catch (Exception $e) {
          // silence
        }
      }
      if (!$exists) {
        $select->from($table->info('name'));
      } else {
        $select->from($table->info('name'), array('user_id as item_id', new Zend_Db_Expr('"user" AS item_type')));
      }
      $blockedUserIds = !$viewer->isAdmin() ? $viewer->getAllBlockedUserIds() : array();
      if ($blockedUserIds) {
        $select->where('user_id NOT IN(?)', (array) $blockedUserIds);
      }

      if (0 < ($limit = (int) $this->_getParam('limit', 20))) {
        $select->limit($limit);
      }
      if (null !== ($text = $this->_getParam('query', ''))) {
        $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', $text . '%');
      }

      if ($exists) {
        $selectUnion = '(' . $select . ') UNION ' . implode(" UNION ", $customSqls);
        $db = Engine_Db_Table::getDefaultAdapter();
        foreach ($db->fetchAll($selectUnion) as $friend) {
          $item = Engine_Api::_()->getItem($friend['item_type'], $friend['item_id']);
          if ($item->getType() == "user" && $item->mention != 'registered') {
            if (($item instanceof User_Model_User) && !$item->authorization()->isAllowed($viewer, 'mention') && !$viewer->isAdmin())
              continue;
          }
          $data[] = array(
            'type' => 'user',
            'id' => $item->getGuid() . ' ',
            'name' => $item->getTitle(false),
            'avatar' => $this->view->itemPhoto($item, 'thumb.icon'),
          );
        }
      } else {
        foreach ($table->fetchAll($select) as $friend) {
          if ($friend->mention != 'registered') {
            if (($friend instanceof User_Model_User) && !$friend->authorization()->isAllowed($viewer, 'mention') && !$viewer->isAdmin())
              continue;
          }
          $data[] = array(
            'type' => 'user',
            'id' => $friend->getIdentity() . ' ',
            'name' => $friend->getTitle(false),
            'avatar' => $this->view->itemPhoto($friend, 'thumb.icon'),
          );
        }
      }
    }
    return $this->_helper->json($data);
  }
}
