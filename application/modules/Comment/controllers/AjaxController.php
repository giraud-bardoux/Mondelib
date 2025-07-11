<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AjaxController.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Comment_AjaxController extends Core_Controller_Action_Standard
{

  public function emojiAction()
  {
    $this->view->edit = $this->_getParam('edit', false);
    $this->renderScript('_emoji.tpl');
  }
  public function emojiContentAction()
  {
    $galleryId = $this->_getParam('gallery_id');
    $this->view->files = Engine_Api::_()->getDbTable('emotionfiles', 'comment')->getFiles(array('fetchAll' => true, 'gallery_id' => $galleryId));
  }
  public function searchReactionAction()
  {
    $text = $this->_getParam('text', '');
    $this->view->files = Engine_Api::_()->getDbTable('emotioncategories', 'comment')->searchResult($text);
    return $this->render('emoji-content');
  }
  public function reactionAddAction()
  {

    $this->view->storepopupTitle = $this->view->translate('Sticker Store');
    $this->view->storepopupDesciption = $this->view->translate('Find new stickers to send to friends');
    $this->view->storebackgroundimage = 'public/admin/store-header-bg.png';
    $this->view->stickerstextcolor = '000000';

    $this->view->gallery = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->getGallery(array('fetchAll' => true, 'type' => 'user'));
    $useremotions = Engine_Api::_()->getDbTable('useremotions', 'comment')->getEmotion(array('type' => 'user'));
    $useremotionsArray = array();
    foreach ($useremotions as $userEmo)
      $useremotionsArray[] = $userEmo->gallery_id;
    $this->view->useremotions = $useremotionsArray;
  }
  public function actionReactionAction()
  {
    $action = $this->_getParam('actionD', false);
    $gallery_id = $this->_getParam('gallery_id', false);
    $viewer = Engine_Api::_()->user()->getViewer();
    $db = Engine_Db_Table::getDefaultAdapter();
    if ($action == 'add') {
      $data = array(
        'gallery_id' => $gallery_id,
        'user_id' => $viewer->getIdentity(),
      );
      $db->insert('engine4_comment_useremotions', $data);
    } else {
      $db->delete(
        'engine4_comment_useremotions',
        array(
          'gallery_id =?' => $gallery_id,
          'user_id =?' => $viewer->getIdentity(),
        )
      );
    }
    echo true;
    die;
  }
  public function previewReactionAction()
  {
    $gallery_id = $this->_getParam('gallery_id', false);
    $this->view->gallery = Engine_Api::_()->getItem('comment_emotiongallery', $gallery_id);
    $this->view->useremotions = (int) engine_count(Engine_Api::_()->getDbTable('useremotions', 'comment')->getEmotion(array('gallery_id' => $gallery_id)));
    $this->view->files = Engine_Api::_()->getDbTable('emotionfiles', 'comment')->getFiles(array('fetchAll' => true, 'gallery_id' => $gallery_id));
  }
  public function commentLikesAction()
  {
    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $this->view->comment_id = $comment_id = $this->_getParam('comment_id');
    $resource_type = 'core_comment';
    $action = Engine_Api::_()->getItem($resource_type, $resource_id);

    $table = Engine_Api::_()->getItemTable('core_like');
    $tableName = $table->info('name');

    $this->view->title = $this->view->translate('People Who Like This');
    $this->view->page = $page = $this->_getParam('page', 1);


    $select = $table->select()
      ->from($tableName, '*')
      ->setIntegrityCheck(false)
      ->where($tableName . '.resource_id =?', $comment_id)
      ->where($tableName . '.resource_type =?', $resource_type);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    foreach ($paginator as $data) {
      $type[$data['poster_id'] . '_' . $data['poster_type']] = $data['type'];
    }
    $this->view->users = $paginator;
    if ($is_ajax) {
      echo $this->view->partial(
        '_contentlikesuser.tpl',
        'comment',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => 'contentlikeusers', 'resource_id' => $this->view->resource_id, 'resource_type' => $resource_type, 'execute' => true, 'page' => $this->view->page)
      );
      die;

    }
  }
  public function likesAction()
  {
    $this->view->resource_id = $resource_id = $this->_getParam('id');
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax_content', false);
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');
    $this->view->typeSelected = $typeSelected = $this->_getParam('type', 'all');
    $this->view->item_id = $item_id = $this->_getParam('item_id', false);
    if (!$typeSelected)
      $this->view->typeSelected = $typeSelected = 'all';
    $action = Engine_Api::_()->getItem($resource_type, $resource_id);

    $table = Engine_Api::_()->getItemTable('core_like');
    $tableName = $table->info('name');


    $this->view->page = $page = $this->_getParam('page', 1);
    $select = $table->select()
      ->from($tableName, '*')
      ->setIntegrityCheck(false)
      ->where($tableName . '.resource_id =?', $item_id)
      ->where($tableName . '.resource_type =?', $resource_type);

    if ($typeSelected != 'all')
      $select->where('type =?', $typeSelected);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $users = array();
    $type = array();
    foreach ($paginator as $data) {
      $type[$data['poster_id']] = $data['type'];
      if ($data['poster_type'] == 'user') {
        $users[] = $data['poster_id'];
      }
    }
    $users = array_values(array_unique($users));
    $this->view->type = $type;
    if (!$is_ajax) {
      $AllTypesCount = Engine_Api::_()->comment()->likesGroup($action, 'subject');
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

    $this->view->users = Engine_Api::_()->getItemMulti('user', $users);

    if ($is_ajax) {
      echo $this->view->partial(
        '_reactionlikesuser.tpl',
        'comment',
        array('users' => $this->view->users, 'paginator' => $this->view->paginator, 'randonNumber' => $this->view->typeSelected, 'resource_id' => $this->view->resource_id, 'resource_type' => $this->view->resource_type, 'typeSelected' => $this->view->typeSelected, 'execute' => true, 'page' => $this->view->page, 'type' => $this->view->type, 'item_id' => $item_id)
      );
      die;

    }
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

      if (!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('epage')) {
        $select->from($table->info('name'));
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

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('epage') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('egroup') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ebusiness')) {
        $select->from($table->info('name'), array('user_id as item_id', new Zend_Db_Expr('"user" AS item_type')));
        $sqls = "";
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('epage')) {
          $pageTableName = Engine_Api::_()->getItemTable('epage_page')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('epage_page')->select()->from($pageTableName, array('page_id as item_id', new Zend_Db_Expr('"epage_page" AS item_type')))->where('search =?', 1)->where('draft =?', 1)->where('other_tag =?', 1);
          if (null !== ($text = $this->_getParam('query', ''))) {
            $sql2->where('`' . $pageTableName . '`.`title` LIKE "%' . $text . '%" || `' . $pageTableName . '`.`custom_url` LIKE "%' . str_replace('@', '', $text) . '%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION (' . $sql2 . ') ';
        }
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('egroup')) {
          $pageTableName = Engine_Api::_()->getItemTable('egroup_group')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('egroup_group')->select()->from($pageTableName, array('group_id as item_id', new Zend_Db_Expr('"egroup_group" AS item_type')))->where('search =?', 1)->where('draft =?', 1)->where('other_tag =?', 1);
          if (null !== ($text = $this->_getParam('query', ''))) {
            $sql2->where('`' . $pageTableName . '`.`title` LIKE "%' . $text . '%" || `' . $pageTableName . '`.`custom_url` LIKE "%' . str_replace('@', '', $text) . '%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION (' . $sql2 . ') ';
        }
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ebusiness')) {
          $pageTableName = Engine_Api::_()->getItemTable('businesses')->info('name');
          $sql2 = Engine_Api::_()->getItemTable('businesses')->select()->from($pageTableName, array('business_id as item_id', new Zend_Db_Expr('"businesses" AS item_type')))->where('search =?', 1)->where('draft =?', 1)->where('other_tag =?', 1);
          if (null !== ($text = $this->_getParam('query', ''))) {
            $sql2->where('`' . $pageTableName . '`.`title` LIKE "%' . $text . '%" || `' . $pageTableName . '`.`custom_url` LIKE "%' . str_replace('@', '', $text) . '%"');
            $sql2->limit($limit);
          }
          $sqls .= 'UNION (' . $sql2 . ') ';
        }
        $selectUnion = '(' . $select . ') ' . $sqls;
        $db = Engine_Db_Table::getDefaultAdapter();
        foreach ($db->fetchAll($selectUnion) as $friend) {
          $item = Engine_Api::_()->getItem($friend['item_type'], $friend['item_id']);
          if ($item->getType() == "user" && $item->mention != 'registered') {
            if (!$friend->authorization()->isAllowed($viewer, 'mention') && !$viewer->isAdmin())
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
            if (!$friend->authorization()->isAllowed($viewer, 'mention') && !$viewer->isAdmin())
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
