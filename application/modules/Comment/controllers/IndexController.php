<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: IndexController.php 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Comment_IndexController extends Core_Controller_Action_Standard
{

  function loadCommentAction()
  {
    $action_id = $this->_getParam("action_id");
    $isOnThisDayPage = $this->_getParam("isOnThisDayPage");
    $isPageSubject = $this->_getParam("isPageSubject");
    $onlyComment = $this->_getParam("onlyComment");
    $searchType = $this->_getParam("searchtype", $this->_getParam("searchType", 'newest'));
    $action = $this->view->action = Engine_Api::_()->getItem("activity_action", $action_id);
    $comments = $action->getComments('0', 0, $searchType);
    $data['comments'] = $comments;
    $data['isOnThisDayPage'] = $isOnThisDayPage;
    $data['isPageSubject'] = $isPageSubject;
    $data['onlyComment'] = $onlyComment;
    $data['type'] = $searchType;
    $data['action'] = $action;
    echo $this->view->partial(
      '_ajaxComment.tpl',
      'comment',
      $data
    );
    die;
  }

  public function contactAction()
  {
    $ownerId[] = $this->_getParam('owner_id', $this->_getParam('page_owner_id', 0));
    $this->view->form = $form = new Comment_Form_ContactOwner();
    $form->page_owner_id->setValue($this->_getParam('owner_id', $this->_getParam('page_owner_id')));
    // Not post/invalid
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = $form->getValues();
      $recipientsUsers = Engine_Api::_()->getItemMulti('user', array($values['page_owner_id']));
      $attachment = null;

      if ($this->_getParam('owner_id', 0) != $viewer->getIdentity()) {
        // Create conversation
        $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send(
          $viewer,
          $ownerId,
          $values['title'],
          $values['body'],
          $attachment
        );
      }

      // Send notifications
      foreach ($recipientsUsers as $user) {

        if ($user->getIdentity() == $viewer->getIdentity()) {
          continue;
        }

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
          $user,
          $viewer,
          $conversation,
          'message_new'
        );

      }

      // Increment messages counter
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

      // Commit
      $db->commit();
      echo json_encode(array('status' => 'true'));
      die;
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      throw $e;
    }
  }
  /**
   * Handles HTTP request to get an activity feed item's likes and returns a
   * Json as the response
   *
   * Use the default route and can be accessed from
   *  - /activity/index/viewlike
   *
   * @return void
   */
  public function viewlikeAction()
  {
    // Collect params
    $action_id = $this->_getParam('action_id');
    $viewer = Engine_Api::_()->user()->getViewer();

    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);

    // Redirect if not json context
    if (null === $this->_getParam('format', null)) {
      $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else if ('json' === $this->_getParam('format', null)) {
      $this->view->body = $this->view->activity($action, array('viewAllLikes' => true, 'noList' => $this->_getParam('nolist', false)));
    }
  }

  function voteupAction()
  {
    $itemguid = $this->_getParam('itemguid', 0);
    $userguid = $this->_getParam('userguid', 0);
    $type = $this->_getParam('type', 'upvote');

    $item = Engine_Api::_()->getItemByGuid($itemguid);
    $detailAction = $item;


    if ($userguid)
      $isPageSubject = Engine_Api::_()->getItemByGuid($userguid);
    else
      $isPageSubject = $this->view->viewer();
    $isVote = Engine_Api::_()->getDbTable('voteupdowns', 'comment')->isVote(array('resource_id' => $item->getIdentity(), 'resource_type' => $item->getType(), 'user_id' => $isPageSubject->getIdentity(), 'user_type' => $isPageSubject->getType()));
    $checkType = "";
    if ($isVote)
      $checkType = $isVote->type;

    
    if($checkType == $type){
      // remove vote
      if($type == "upvote"){
        $isVote->delete();
        if ($item->getType() == 'activity_action' && $detailAction) {
          $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
          $detailAction->save();
        } else {
          if ($item->getType() == 'core_comment') {
            $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
            $detailAction->save();
          } else if ($item->getType() == 'activity_comment') {
            $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
            $detailAction->save();
          }

        }
      }else{
        $isVote->delete();
        if ($item->getType() == 'activity_action' && $detailAction) {
          $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
          $detailAction->save();
        } else {
          if ($item->getType() == 'core_comment') {
            $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
            $detailAction->save();
          } else if ($item->getType() == 'activity_comment') {
            $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
            $detailAction->save();
          }
        }
      }
      echo $this->view->partial('_updownvote.tpl', 'comment', array('item' => $item, 'isPageSubject' => $isPageSubject));die;
    }

    if ($checkType != "upvote" && $type == "upvote") {
      //up vote
      $table = Engine_Api::_()->getDbTable('voteupdowns', 'comment');
      $vote = $table->createRow();
      $vote->type = "upvote";
      $vote->resource_type = $item->getType();
      $vote->resource_id = $item->getIdentity();
      $vote->user_type = $isPageSubject->getType();
      $vote->user_id = $isPageSubject->getIdentity();
      $vote->save();
      if ($item->getType() == 'activity_action' && $detailAction) {
        $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count + 1');
        $detailAction->save();
      } else {

        if ($item->getType() == 'core_comment') {
          $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count + 1');
          $detailAction->save();
        } else if ($item->getType() == 'activity_comment') {
          $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count + 1');
          $detailAction->save();
        }
      }
      if ($isVote) {
        $isVote->delete();
        if ($item->getType() == 'activity_action' && $detailAction) {
          $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
          $detailAction->save();
        } else {
          if ($item->getType() == 'core_comment') {
            $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
            $detailAction->save();
          } else if ($item->getType() == 'activity_comment') {
            $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
            $detailAction->save();
          }

        }
        //$item->vote_down_count = new Zend_Db_Expr('vote_down_count - 1');
      }
      $item->save();

    } else {
      //down vote
      $table = Engine_Api::_()->getDbTable('voteupdowns', 'comment');
      $vote = $table->createRow();
      $vote->type = "downvote";
      $vote->resource_type = $item->getType();
      $vote->resource_id = $item->getIdentity();
      $vote->user_type = $isPageSubject->getType();
      $vote->user_id = $isPageSubject->getIdentity();
      $vote->save();
      if ($item->getType() == 'activity_action' && $detailAction) {
        $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count + 1');
        $detailAction->save();
      } else {

        if ($item->getType() == 'core_comment') {

          $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count + 1');
          $detailAction->save();
        } else if ($item->getType() == 'activity_comment') {
          $detailAction->vote_down_count = new Zend_Db_Expr('vote_down_count + 1');
          $detailAction->save();
        }
      }
      //$item->vote_down_count = new Zend_Db_Expr('vote_down_count + 1');
      if ($isVote) {
        $isVote->delete();
        if ($item->getType() == 'activity_action' && $detailAction) {
          $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
          $detailAction->save();
        } else {
          if ($item->getType() == 'core_comment') {
            $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
            $detailAction->save();
          } else if ($item->getType() == 'activity_comment') {
            $detailAction->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
            $detailAction->save();
          }
        }
        //$item->vote_up_count = new Zend_Db_Expr('vote_up_count - 1');
      }
      $item->save();

    }
    echo $this->view->partial('_updownvote.tpl', 'comment', array('item' => $item, 'isPageSubject' => $isPageSubject));
    die;

  }
  /**
   * Handles HTTP request to like an activity feed item
   *
   * Use the default route and can be accessed from
   *  - /activity/index/like
   *   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function likeAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;
    // Collect params
    
    $guid = $this->_getParam('guid', 0);
    if ($guid) {
      $guid = Engine_Api::_()->getItemByGuid($guid);
      $guidUser = $guid->getOwner();
      if (!$guid)
        $guid = "";
    } else {
      $guid = "";
    }

    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $page_id = $this->_getParam('page_id');
    $sbjecttype = $this->_getParam('sbjecttype', false);
    $subjectid = $this->_getParam('subjectid', false);
    if ($subjectid) {
      $mainFolder = 'list-comment/';
      $fileName = '_subject';
    } else {
      $mainFolder = '';
      $fileName = '_activity';
    }
    
    // Start transaction
    // $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
    // $db->beginTransaction();
    try {
      if (!$sbjecttype)
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
      else
        $action = Engine_Api::_()->getItem($sbjecttype, $subjectid);
        
      // Action
      if (!$comment_id) {
        // Check authorization
        if ($action && !$sbjecttype && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')) {
          $this->view->error = ('This user is not allowed to like this item');
        }

        if (!$guid && !empty($action)) {
          $isLike = $action->likes()->getLike($viewer);
          if ($isLike) {
            $action->likes()->removeLike($viewer);
          }
          $like = $action->likes()->addLike($viewer);
        } else {
          $isLike = $action->likes()->getLike($guid);
          if ($isLike) {
            $action->likes()->removeLike($guid);
          }
          $like = $action->likes()->addLike($guid);
        }

        $like->type = $this->_getParam('type', 1);
        $like->save();

        $reactedType = $this->_getParam('type', 1);
        $reactionItem = Engine_Api::_()->getItem('comment_reaction', $reactedType);

        // Add notification for owner of activity (if user and not viewer)
        if (($action->getType() == "activity_action" && $action->subject_type == 'user') && ((($action->getType() != "activity_action" && $action->getOwner()->getIdentity() != $viewer->getIdentity()) || $action->subject_id != $viewer->getIdentity()) || ($guid && $guidUser && $guidUser->getIdentity() != $action->subject_id))) {
          
          if ($action->getType() == "activity_action")
            $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
          else
            $actionOwner = $action->getOwner();

          $senderObject = !empty($guid) ? $guidUser : $viewer;

          if ($reactedType == 1) {
            //Remove Previous Notification
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'liked', "subject_id =?" => $senderObject->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));

            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($actionOwner, $senderObject, $action, 'liked', array('label' => 'post'));
          } else {
            //Remove previous notification
            Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => 'activity_reacted', "subject_id =?" => $senderObject->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));

            //Send Reaction Notification
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($actionOwner, $senderObject, $action, 'activity_reacted', array('label' => 'post', 'reactionTitle' => $reactionItem->title, 'reaction_id' => $reactedType));
          }
        }
      }
      // Comment
      else {
        $comment = $action->comments()->getComment($comment_id);
        // Check authorization
        if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment')) {
          $this->view->error = ('This user is not allowed to like this item');
        }

        if (empty($guid)) {
          $isLike = $comment->likes()->getLike($viewer);
          if ($isLike) {
            $comment->like_count = new Zend_Db_Expr('like_count - 1');
            ;
            $comment->save();

            $comment->likes()->removeLike($viewer);
          }
          $like = $comment->likes()->addLike($viewer);
        } else {
          $isLike = $comment->likes()->getLike($guid);
          if ($isLike) {
            $comment->like_count = new Zend_Db_Expr('like_count - 1');
            ;
            $comment->save();

            $comment->likes()->removeLike($guid);

          }
          $like = $comment->likes()->addLike($guid);
        }

        $like->type = $this->_getParam('type', 1);
        $like->save();

        $reactedType = $this->_getParam('type', 1);
        $reactionItem = Engine_Api::_()->getItem('comment_reaction', $reactedType);
        
        // @todo make sure notifications work right
        if (($guid && $guidUser && $guidUser->getIdentity() != $comment->poster_id) || ($comment->poster_type == "user" && $comment->poster_id != $viewer->getIdentity())) {
          $ownerNoti = $comment->getPoster();

          if ($reactedType == 1) {
            //Remove Previous Notification
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($ownerNoti, !empty($guid) ? $guidUser : $viewer, $comment, 'liked', array('label' => 'comment'));
          } else {
            //Send Reaction Notification
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($ownerNoti, !empty($guid) ? $guidUser : $viewer, $comment, 'activity_reacted', array('label' => 'comment', 'reactionTitle' => $reactionItem->title, 'reaction_id' => $reactedType));
          }
        }
      }

      // Stats
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.likes');
      //$db->commit();
    } catch (Exception $e) {
      throw $e;
      //  $db->rollBack();
      $this->view->error = 'Error';
      //throw $e;
    }

    // Success
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('You now like this action.');

    if (!$comment_id) {
      $this->view->body = $this->view->partial(
        $mainFolder . $fileName . 'likereaction.tpl',
        'comment',
        array('action' => $action)
      );
    } else {
      $comment = isset($comment) ? $comment : array();

      if ($comment->parent_id) {
        //reply
        $this->view->body = $this->view->partial(
          $mainFolder . $fileName . 'commentreply.tpl',
          'comment',
          array('commentreply' => $comment, 'action' => $action, 'canComment' => 1, 'likeOptions' => true, 'isPageSubject' => $guid)
        );
      } else {
        //main comment
        $this->view->body = $this->view->partial(
          $mainFolder . $fileName . 'commentbodyoptions.tpl',
          'comment',
          array('comment' => $comment, 'actionBody' => $action, 'canComment' => 1, 'isPageSubject' => $guid)
        );
      }
    }
  }

  /**
   * Handles HTTP request to remove a like from an activity feed item
   *
   * Use the default route and can be accessed from
   *  - /activity/index/unlike
   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function unlikeAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;

    $guid = $this->_getParam('guid', 0);
    if ($guid) {
      $guid = Engine_Api::_()->getItemByGuid($guid);
      $guidUser = $guid->getOwner();
      if (!$guid)
        $guid = "";
    } else {
      $guid = "";
    }

    // Collect params
    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $page_id = $this->_getParam('page_id');
    $sbjecttype = $this->_getParam('sbjecttype', false);
    $subjectid = $this->_getParam('subjectid', false);
    if ($subjectid) {
      $mainFolder = 'list-comment/';
      $fileName = '_subject';
    } else {
      $mainFolder = '';
      $fileName = '_activity';
    }
    // Start transaction
    $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
    $db->beginTransaction();

    try {
      if (!$sbjecttype)
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
      else
        $action = Engine_Api::_()->getItem($sbjecttype, $subjectid);

      if (isset($comment_id)) {
        $comment = $action->comments()->getComment($comment_id);
      }
      // Action
      if (!$comment_id) {

        // Check authorization
        if (!$subjectid && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
          $this->view->error = ('This user is not allowed to unlike this item');

        //Remove reaction notification
        $reaction_array = array('liked', 'activity_reacted_love', 'activity_reacted_haha', 'activity_reacted_wow', 'activity_reacted_angry', 'activity_reacted_sad');
        foreach ($reaction_array as $reactionr) {
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $reactionr, "subject_id =?" => !empty($guidUser) ? $guidUser->getIdentity() : $viewer->getIdentity(), "object_type =? " => $action->getType(), "object_id = ?" => $action->getIdentity()));
        }
        if (!empty($guid))
          $isLike = $action->likes()->getLike($guid);
        else
          $isLike = $action->likes()->getLike($viewer);

        if (empty($guid))
          $action->likes()->removeLike($viewer);
        else
          $action->likes()->removeLike($guid);
      }

      // Comment
      else {

        // Check authorization
        if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment')) {
          $this->view->error = ('This user is not allowed to like this item');
        }

        if (!empty($guid))
          $isLike = $comment->likes()->getLike($guid);
        else
          $isLike = $comment->likes()->getLike($viewer);

        if (empty($guid))
          $comment->likes()->removeLike($viewer);
        else {
          $comment->likes()->removeLike($guid);
        }
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->error = 'error';
    }

    // Success
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('You no longer like this action.');

    // Redirect if not json context

    if (!$comment_id) {
      $this->view->body = $this->view->partial(
        $mainFolder . $fileName . 'likereaction.tpl',
        'comment',
        array('action' => $action, 'isPageSubject' => $guid)
      );
    } else {
      if ($comment->parent_id) {
        //reply
        $this->view->body = $this->view->partial(
          $mainFolder . $fileName . 'commentreply.tpl',
          'comment',
          array('commentreply' => $comment, 'action' => $action, 'canComment' => 1, 'likeOptions' => true, 'isPageSubject' => $guid)
        );
      } else {
        //main comment
        $this->view->body = $this->view->partial(
          $mainFolder . $fileName . 'commentbodyoptions.tpl',
          'comment',
          array('comment' => $comment, 'actionBody' => $action, 'canComment' => 1, 'isPageSubject' => $guid)
        );
      }

    }

  }

  /**
   * Handles HTTP request to get an activity feed item's comments and returns
   * a Json as the response
   *
   * Use the default route and can be accessed from
   *  - /activity/index/viewcomment
   *
   * @return void
   */
  public function viewcommentAction()
  {
    // Collect params
    $action_id = $this->_getParam('action_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    $viewcomment = true;
    if (!$this->_getParam('viewcomment', false)) {
      $viewcomment = false;
    }
    $this->view->body = $this->view->activity($action, array('noList' => $this->_getParam('nolist', true), 'page' => $this->_getParam('page'), 'onlyComment' => 1, 'viewcomment' => $viewcomment, 'type' => $this->_getParam('searchtype', '')), 'update');

    echo json_encode(array('status' => true, 'body' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;
  }

  public function viewcommentreplyAction()
  {
    // Collect params
    $comment_id = $this->_getParam('comment_id');
    $comment = Engine_Api::_()->getItem($this->_getParam('moduleN') . '_comment', $comment_id);
    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    $action_id = $this->_getParam('action_id');
    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    $this->view->body = $this->view->partial(
      '_activitycommentbody.tpl',
      'comment',
      array('comment' => $comment, 'action' => $action, 'page' => $page, 'viewmore' => true)
    );
    echo json_encode(array('status' => true, 'body' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;
  }
  public function viewcommentreplysubjectAction()
  {
    // Collect params
    $comment_id = $this->_getParam('comment_id');
    $comment = Engine_Api::_()->getItem('core_comment', $comment_id);
    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->getItem($this->_getParam('type'), $this->_getParam('action_id'));
    $this->view->body = $this->view->partial(
      'list-comment/_subjectcommentbody.tpl',
      'comment',
      array('comment' => $comment, 'subject' => $subject, 'page' => $page, 'viewmore' => true)
    );
    echo json_encode(array('status' => true, 'body' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;
  }
  /**
   * Handles HTTP POST request to comment on an activity feed item
   *
   * Use the default route and can be accessed from
   *  - /activity/index/comment
   *
   * @throws Engine_Exception If a user lacks authorization
   * @return void
   */
  public function commentAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;


    $guid = $this->_getParam('guid', 0);
    if ($guid) {
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if (!$guid)
        $guid = "";
    } else {
      $guid = "";
    }
    // Not post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }
    $subject_id = $this->_getParam('subject_id', false);
    $subject_type = $this->_getParam('subject_type', false);

    // Start transaction
    if (!$subject_id)
      $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
    else {
      $action = Engine_Api::_()->getItem($subject_type, $subject_id);
      $db = Engine_Api::_()->getItemtable($action->getType())->getAdapter();
    }
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $action_id = $this->view->action_id = $this->_getParam('action_id', $this->_getParam('action', null));
      if (!$subject_id) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
        $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
      } else {
        //$action = Engine_Api::_()->getItem($subject_type,$subject_id);
        $actionOwner = $action->getOwner();
      }
      if (!$action) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
      }

      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];

      $censor = new Engine_Filter_Censor();
      $body = $censor->filter($body);

      $gif_id = 0;
      $emoji_id = $_POST['emoji_id'];
      if (isset($_POST['gif_id']) ? $_POST['gif_id'] : 0)
        $gif_id = !empty($_POST['gif_id']) ? $_POST['gif_id'] : false;

      // Check authorization
      if (!$subject_id && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
        throw new Engine_Exception('This user is not allowed to comment on this item.');

      // Add the comment
      $comment = $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();


      $comment = Engine_Api::_()->getItem($typeC, $comment->comment_id);
      $file_id = trim(str_replace(',,', '', $_POST['file_id']), ',');
      if ($file_id && $file_id != '') {
        $counter = 1;
        $file_ids = explode(',', $file_id);
        $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'comment');
        foreach ($file_ids as $file_id) {
          if (!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if (strpos($file_id, '_album_photo')) {
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo', '', $file_id);
          } else {
            $file->type = 'video';
            $file->file_id = str_replace('_video', '', $file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();
          if ($counter == 1) {
            $comment->file_id = $file_id;
            $comment->save();
          }
          $counter++;
        }
      }

      if ($emoji_id) {
        $comment->emoji_id = $emoji_id;
        $comment->file_id = 0;
        $comment->save();
        $comment->body = '';
        $comment->save();
      }

      if ($guid) {
        $comment->poster_type = $guid->getType();
        $comment->poster_id = $guid->getIdentity();
        $comment->save();
        Engine_Hooks_Dispatcher::getInstance()->callEvent('onCommentCreateAfter', $comment);
      }

      //GIF Work
      if ($gif_id) {
        $comment->gif_id = 1;
        $comment->gif_url = $gif_id;
        $comment->save();
        $gifImageUrl = $gif_id;
        //$bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">', $gifImageUrl, $gifImageUrl);
        //$comment->body = $body . '<br />' .$bodyGif;
        $comment->body = $body;
        $comment->save();
      }

      //GIF Work
      //fetch link from comment
      $regex = '/(https|http)?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if (!empty($matches[0])) {
        $preview = $this->previewCommentLink($matches[0], $comment, $viewer);
        if ($preview) {
          $comment->preview = $preview;
          $comment->save();
        }
      }

      if($body && empty($gif_id)) {
        $comment->body = $body;
        $comment->save();
      }

      // Notifications
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      // Add notification for owner of activity (if user and not viewer)
      if ((!$subject_id && $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) || ($subject_id && !$viewer->isSelf($actionOwner))) {
        $notifyApi->addNotification(
          $actionOwner,
          !empty($guid) ? $guid : $viewer,
          $action,
          'commented',
          array(
            'label' => 'post'
          )
        );
      }

      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach ($action->comments()->getAllCommentsUsers() as $notifyUser) {
        if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
          $notifyApi->addNotification(
            $notifyUser,
            !empty($guid) ? $guid : $viewer,
            $action,
            'commented_commented',
            array(
              'label' => 'post'
            )
          );
        }
      }

      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach ($action->likes()->getAllLikesUsers() as $notifyUser) {
        if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
          $notifyApi->addNotification(
            $notifyUser,
            !empty($guid) ? $guid : $viewer,
            $action,
            'liked_commented',
            array(
              'label' => 'post'
            )
          );
        }
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['bodymention'], $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "comment" . '</a>';
      foreach ($result[2] as $value) {
        $user_id = str_replace('@_user_', '', $value);
        if (intval($user_id) > 0) {
          $item = Engine_Api::_()->getItem('user', $user_id);
          if (!$item || !$item->getIdentity())
            continue;
        } else {
          $itemArray = explode('_', $user_id);
          $resource_id_reply = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type_reply = implode('_', $itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply, $resource_id_reply);
          if (!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if (!$item || !$item->getIdentity())
            continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid : $viewer, $viewer, 'comment_tagged_people', array("commentLink" => $commentLink, 'resource_type' => $action->getType(), 'resource_id' => $action->getIdentity()));
      }
      //Tagging People by status box

      // Stats
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

      $db->commit();
    } catch (Exception $e) {
      throw $e;
      $db->rollBack();
      $this->view->error = Zend_Registry::get('Zend_Translate')->_($e);
    }

    // Assign message for json
    $this->view->status = true;
    $this->view->message = 'Comment posted';

    $method = 'update';
    $show_all_comments = $this->_getParam('show_all_comments', false);


    $comment = $this->getCommentById($comment);

    if (!$subject_id) {
      $commentStats = $this->view->partial('_activitylikereaction.tpl', 'comment', array('action' => $action));
      $this->view->body = $this->view->partial(
        '_activitycommentbody.tpl',
        'comment',
        array('comment' => $comment, 'action' => $action, 'isPageSubject' => $guid)
      );
    } else {
      $commentStats = $this->view->partial('list-comment/_subjectlikereaction.tpl', 'comment', array('action' => $action));
      $this->view->body = $this->view->partial(
        'list-comment/_subjectcommentbody.tpl',
        'comment',
        array('comment' => $comment, 'action' => $action, 'isPageSubject' => $guid)
      );

    }
    echo json_encode(array('status' => $this->view->status, 'content' => $this->view->body, 'error' => $this->view->error, 'commentStats' => $commentStats), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;

  }


  function getCommentById($comment)
  {
    if ($comment->getType() == 'activity_comment') {
      $activityCommentTable = Engine_Api::_()->getDbTable('comments', 'activity');
      $activityCommentTableName = $activityCommentTable->info('name');
      $select = $activityCommentTable->select()->from($activityCommentTableName, '*')->setIntegrityCheck(false)
        ->where($activityCommentTableName . '.comment_id =?', $comment->comment_id)
        ->limit(1);
      $comment = $activityCommentTable->fetchRow($select);
    } else if ($comment->getType() == 'core_comment') {
      $activityCommentTable = Engine_Api::_()->getDbTable('comments', 'core');
      $activityCommentTableName = $activityCommentTable->info('name');
      $select = $activityCommentTable->select()->from($activityCommentTableName, '*')->setIntegrityCheck(false)
        ->where($activityCommentTableName . '.comment_id =?', $comment->comment_id)
        ->limit(1);
      $comment = $activityCommentTable->fetchRow($select);
    }
    return $comment;
  }

  public function getCommentAction()
  {
    $action_id = $this->_getParam('action_id', 0);
    $dataGuid = $this->_getParam('dataGuid', 0);
    $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    $this->view->body = $this->view->activity($action, array('noList' => true, 'isOnThisDayPage' => false, 'viewAllLikes' => false, 'isPageSubject' => Engine_Api::_()->getItemByGuid($dataGuid)), 'update', false);
    echo json_encode(array('status' => true, 'body' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;
  }
  public function previewCommentLink($url, $comment, $viewer)
  {
    try {
      $contentLink = Engine_Api::_()->activity()->previewHTML($url);
      $image = @$contentLink['images'][0];
      if ($image && substr($image, 0, 2) == "//") {
        $image = str_replace('//', 'https://', $image);
      }
      if (!empty($contentLink['title']) && !empty($image)) {
        $title = $contentLink['title'];
        if (strpos($image, 'http') === false && strpos($image, 'https') === false) {
          $parseUrl = parse_url($url);
          $image = $parseUrl['scheme'] . '://' . $parseUrl['host'] . '/' . ltrim($image, '/');
        }
        $table = Engine_Api::_()->getDbtable('links', 'core');
        $link = $table->createRow();
        $data['uri'] = $url;
        $data['title'] = isset($title) ? $title : "";
        $data['parent_type'] = $comment->getType();
        $data['parent_id'] = $comment->getIdentity();
        $data['search'] = 0;
        $data['photo_id'] = 0;
        $link->setFromArray($data);
        $link->owner_type = $viewer->getType();
        $link->owner_id = $viewer->getIdentity();
        $link->save();
        $thumbnail = (string) @$image;
        // $thumbnail_parsed = @parse_url($thumbnail);
        $thumbnail_parsed = true;
        $content = $this->url_get_contents($thumbnail);
        if ($thumbnail && $content) {
          $tmp_path = APPLICATION_PATH . '/temporary/link';
          $tmp_file = $tmp_path . '/' . md5($thumbnail);
          if (!is_dir($tmp_path) && !mkdir($tmp_path, 0777, true)) {
            throw new Core_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
          }
          //var_dump(is_dir($tmp_file));die;
          // if( is_dir($tmp_path) ) {
          // $src_fh = fopen($thumbnail, 'r');
          // $tmp_fh = fopen($tmp_file, 'w');
          // stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
          // fclose($src_fh);
          // fclose($tmp_fh);
          // var_dump(($tmp_file));die;
          $file = file_put_contents($tmp_file, $content);
          if (($info = getimagesize($tmp_file)) && !empty($info[2])) {
            $ext = Engine_Image::image_type_to_extension($info[2]);
            $thumb_file = $tmp_path . '/thumb_' . md5($thumbnail) . '.' . $ext;
            $image = Engine_Image::factory();
            $image->open($tmp_file)
              ->autoRotate()
              ->resize(500, 500)
              ->write($thumb_file)
              ->destroy();
            $thumbFileRow = Engine_Api::_()->storage()->create(
              $thumb_file,
              array(
                'parent_type' => $link->getType(),
                'parent_id' => $link->getIdentity()
              )
            );
            $link->photo_id = $thumbFileRow->file_id;
            @unlink($thumb_file);
            @unlink($tmp_file);
            $link->save();
            return $link->getIdentity();
          }
          // }
          return $link->getIdentity();
        } else {
          return $link->getIdentity();
        }
      }
      return false;
    } catch (Exception $e) {
      return false;
    }
  }
  function url_get_contents($Url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }
  public function editCommentAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;


    // Not post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }
    $resource_id = $this->_getParam('resource_id', '');
    $resource_type = $this->_getParam('resource_type', '');
    $comment_id = $this->view->comment_id = $this->_getParam('comment_id', null);
    $module = $this->_getParam('modulecomment', '');
    if (!$resource_id)
      $comment = Engine_Api::_()->getItem($module . '_comment', $comment_id);
    else
      $comment = Engine_Api::_()->getItem('core_comment', $comment_id);

    //previous body
    $regex = '/https?\:\/\/[^\" ]+/i';
    $string = $comment->body;
    preg_match($regex, $string, $previousmatches);
    $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];

    $comment->body = $body;

    $execute = false;
    $file_id = trim(str_replace(',,', '', $_POST['file_id']), ',');
    if ($file_id && $file_id != '') {
      $counter = 1;
      $file_ids = explode(',', $file_id);
      $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'comment');
      $tableCommentFile->delete(array('comment_id =?' => $comment->comment_id));
      foreach ($file_ids as $file_id) {
        if (!$file_id)
          continue;
        $file = $tableCommentFile->createRow();
        if (strpos($file_id, '_album_photo')) {
          $file->type = 'album_photo';
          $file->file_id = str_replace('_album_photo', '', $file_id);
        } else {
          $file->type = 'video';
          $file->file_id = str_replace('_video', '', $file_id);
        }
        $file->comment_id = $comment->getIdentity();
        $file->save();
        if ($counter == 1) {
          $comment->file_id = $file_id;
          $comment->save();
        }
        $execute = true;
        $counter++;
      }
    }
    if (!$execute) {
      $comment->file_id = 0;
    }
    $emoji_id = $_POST['emoji_id'];
    if ($emoji_id) {
      $comment->emoji_id = $emoji_id;
      $comment->file_id = 0;
      $comment->save();
      $comment->body = '';
      $comment->save();
    } else {
      $comment->emoji_id = $emoji_id;
      $comment->save();
    }
    $comment->save();

    $gif_id = 0;
    if (isset($_POST['gif_id']) ? $_POST['gif_id'] : 0)
      $gif_id = !empty($_POST['gif_id']) ? $_POST['gif_id'] : false;

    //GIF Work
    if ($gif_id) {
      $comment->gif_id = 1;
      $comment->gif_url = $gif_id;
      $comment->save();
      //$gifImageUrl = $gif_id;
      //$bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">', $gifImageUrl, $gifImageUrl);
      //$comment->body = $body . '<br />' .$bodyGif;
      $comment->body = $body;
      $comment->save();
    } else {
      $comment->gif_id = 0;
      $comment->gif_url = NULL;
      $comment->save();
    }

    if($body && empty($gif_id)) {
      $comment->body = $body;
      $comment->save();
    }
    
    //fetch link from comment
    $regex = '/(https|http)?\:\/\/[^\" ]+/i';
    $string = $comment->body;
    preg_match($regex, $string, $matches);
    if (!empty($matches[0]) && $previousmatches != $matches) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $preview = $this->previewCommentLink($matches[0], $comment, $viewer);
      if ($preview) {
        $comment->preview = $preview;
        $comment->save();
      }
    } else if (empty($matches[0]) && $comment->preview) {
      $comment->preview = 0;
      $comment->save();
      $link = Engine_Api::_()->getItem('core_link', $comment->preview);
      $link->delete();
    }
    // $comment = $this->getCommentById($comment);
    //$showAllComments = $this->_getParam('show_all_comments', false);
    if (!$resource_id) {
      $this->view->body = $this->view->partial(
        '_activitycommentcontent.tpl',
        'comment',
        array('comment' => $comment, 'nolist' => true)
      );
    } else {
      $this->view->body = $this->view->partial(
        'list-comment/_subjectcommentcontent.tpl',
        'comment',
        array('comment' => $comment, 'nolist' => true)
      );
    }
    echo json_encode(array('status' => 1, 'content' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;

  }
  public function editReplyAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;

    // Not post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }
    $resource_id = $this->_getParam('resource_id', false);
    $resource_type = $this->_getParam('resource_type', false);
    $comment_id = $this->view->comment_id = $this->_getParam('comment_id', $this->_getParam('comment_id', null));
    if (!$resource_id) {
      $module = $this->_getParam('modulecomment', '');
      $comment = Engine_Api::_()->getItem($module . '_comment', $comment_id);
    } else
      $comment = Engine_Api::_()->getItem('core_comment', $comment_id);
    $regex = '/https?\:\/\/[^\" ]+/i';
    $string = $comment->body;
    preg_match($regex, $string, $matches);
    $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];

    $comment->body = $body;

    $file_id = $_POST['file_id'];

    // Add the comment

    $execute = false;
    $file_id = trim(str_replace(',,', '', $_POST['file_id']), ',');
    if ($file_id && $file_id != '') {
      $counter = 1;
      $file_ids = explode(',', $file_id);
      $tableCommentFile = Engine_Api::_()->getDbtable('commentfiles', 'comment');
      $tableCommentFile->delete(array('comment_id =?' => $comment->comment_id));
      foreach ($file_ids as $file_id) {
        if (!$file_id)
          continue;
        $file = $tableCommentFile->createRow();
        if (strpos($file_id, '_album_photo')) {
          $file->type = 'album_photo';
          $file->file_id = str_replace('_album_photo', '', $file_id);
        } else {
          $file->type = 'video';
          $file->file_id = str_replace('_video', '', $file_id);
        }
        $file->comment_id = $comment->getIdentity();
        $file->save();
        if ($counter == 1) {
          $comment->file_id = $file_id;
          $comment->save();
        }
        $execute = true;
        $counter++;
      }
    }
    if (!$execute) {
      $comment->file_id = 0;
    }
    $emoji_id = $_POST['emoji_id'];
    if ($emoji_id) {
      $comment->emoji_id = $emoji_id;
      $comment->file_id = 0;
      $comment->save();
      $comment->body = '';
      $comment->save();
    } else {
      $comment->emoji_id = $emoji_id;
      $comment->save();
    }

    $gif_id = 0;
    if (isset($_POST['gif_id']) ? $_POST['gif_id'] : 0)
      $gif_id = !empty($_POST['gif_id']) ? $_POST['gif_id'] : false;

    //GIF Work
    if ($gif_id) {
      $comment->gif_id = 1;
      $comment->gif_url = $gif_id;
      $comment->save();
      //$gifImageUrl = $gif_id;
      //$bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">', $gifImageUrl, $gifImageUrl);
      //$comment->body = $body . '<br />' .$bodyGif;
      $comment->body = $body;
      $comment->save();
    } else {
      $comment->gif_id = 0;
      $comment->gif_url = NULL;
      $comment->save();
    }

    if($body && empty($gif_id)) {
      $comment->body = $body;
      $comment->save();
    }

    $comment->save();
    //fetch link from comment
    $regex = '/(https|http)?\:\/\/[^\" ]+/i';
    $string = $comment->body;
    preg_match($regex, $string, $matches);
    if (!empty($matches[0]) && $previousmatches != $matches) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $preview = $this->previewCommentLink($matches[0], $comment, $viewer);
      if ($preview) {
        $comment->preview = $preview;
        $comment->save();
      }
    } else if (empty($matches[0]) && $comment->preview) {
      $comment->preview = 0;
      $comment->save();
      $link = Engine_Api::_()->getItem('core_link', $comment->preview);
      $link->delete();
    }
    $comment = $this->getCommentById($comment);
    if (!$resource_id)
      //$showAllComments = $this->_getParam('show_all_comments', false);
      $this->view->body = $this->view->partial(
        '_activitycommentreplycontent.tpl',
        'comment',
        array('commentreply' => $comment, 'nolist' => true)
      );
    else
      $this->view->body = $this->view->partial(
        'list-comment/_subjectcommentreplycontent.tpl',
        'comment',
        array('commentreply' => $comment, 'nolist' => true)
      );
    echo json_encode(array('status' => 1, 'content' => $this->view->body), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;

  }

  /**
   * Handles HTTP POST request to delete a comment or an activity feed item
   *
   * Use the default route and can be accessed from
   *  - /activity/index/delete
   *
   * @return void
   */
  function deleteAction()
  {
    if (!$this->_helper->requireUser()->isValid())
      return;

    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');


    // Identify if it's an action_id or comment_id being deleted
    $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
    $this->view->action_id = $action_id = (int) $this->_getParam('action_id', null);
    $type = $this->_getParam('type', false);
    if (!$type)
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
    else
      $action = Engine_Api::_()->getItem($type, $action_id);
    if (!$action) {
      // tell smoothbox to close
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
      $this->view->smoothboxClose = true;
      return $this->render('deletedItem');
    }

    // Send to view script if not POST
    if (!$this->getRequest()->isPost())
      return;


    // Both the author and the person being written about get to delete the action_id
    if (
      !$comment_id && (
        $activity_moderate ||
        ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
        ('user' == $action->object_type && $viewer->getIdentity() == $action->object_id))
    )   // commenter
    {
      // Delete action item and all comments/likes
      $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
      $db->beginTransaction();
      try {
        $action->deleteItem();
        $db->commit();

        // tell smoothbox to close
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
        $this->view->smoothboxClose = true;
        return $this->render('deletedItem');
      } catch (Exception $e) {
        $db->rollback();
        $this->view->status = false;
      }

    } elseif ($comment_id) {
      $comment = $action->comments()->getComment($comment_id);
      // allow delete if profile/entry owner
      $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
      $db->beginTransaction();
      if (
        $type || ($activity_moderate ||
          ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
          ('user' == $action->object_type && $viewer->getIdentity() == $action->object_id))
      ) {
        try {

          $action->comments()->removeComment($comment_id);

          $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');

          if ($comment->parent_id) {
            $parentCommentType = 'core_comment';

            if ($action->getType() == 'activity_action') {
              $commentType = $action->likes(true);
              if ($commentType->getType() == 'activity_action')
                $parentCommentType = 'activity_comment';
            }
            $parentCommentId = $comment->parent_id;
            $parentComment = Engine_Api::_()->getItem($parentCommentType, $parentCommentId);

            $parentComment->reply_count = new Zend_Db_Expr('reply_count - 1');
            $parentComment->save();
          }
          $this->view->commentCount = Engine_Api::_()->comment()->commentCount($action, 'subject');
          $this->view->action = $action;
          $db->commit();
          return $this->render('deletedComment');
        } catch (Exception $e) {
          $db->rollback();
          throw $e;
          $this->view->status = false;
        }
      } else {
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
        return $this->render('deletedComment');
      }

    } else {
      // neither the item owner, nor the item subject.  Denied!
      $this->_forward('requireauth', 'error', 'core');
    }

  }
  public function replyAction()
  {
    // Make sure user exists
    if (!$this->_helper->requireUser()->isValid())
      return;


    // Not post
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not a post');
      return;
    }
    $guid = $this->_getParam('guid', 0);
    if ($guid) {
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if (!$guid)
        $guid = "";
    } else {
      $guid = "";
    }


    // Start transaction
    $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $resource_type = $this->_getParam('resource_type', false);
      if (!$resource_type) {
        $action_id = $this->view->action_id = $this->_getParam('action_id', $this->_getParam('action', null));
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
        $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type . "_" . $action->subject_id);
      } else {
        $action = Engine_Api::_()->getItem($resource_type, $this->_getParam('resource_id'));
        $actionOwner = $action->getOwner();
      }
      if (!$action) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
      }

      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];

      // Check authorization
      if (!$resource_type && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
        throw new Engine_Exception('This user is not allowed to comment on this item.');

      // Add the comment
      $comment = $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();


      $comment = Engine_Api::_()->getItem($typeC, $comment->comment_id);
      $file_id = trim(str_replace(',,', '', $_POST['file_id']), ',');

      if ($file_id && $file_id != '') {
        $counter = 1;
        $file_ids = explode(',', $file_id);
        $tableCommentFile = Engine_Api::_()->getDbTable('commentfiles', 'comment');
        foreach ($file_ids as $file_id) {
          if (!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if (strpos($file_id, '_album_photo')) {
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo', '', $file_id);
          } else {
            $file->type = 'video';
            $file->file_id = str_replace('_video', '', $file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();

          if ($counter == 1) {


            $comment->file_id = $file_id;
            $comment->save();
          }
          $counter++;
        }
      }


      $emoji_id = $_POST['emoji_id'];
      if ($emoji_id) {
        $comment->emoji_id = $emoji_id;
        $comment->file_id = 0;
        $comment->save();
        $comment->body = '';
        $comment->save();
      }
      if ($guid) {
        $comment->poster_type = $guid->getType();
        $comment->poster_id = $guid->getIdentity();
        $comment->save();
      }
      $gif_id = !empty($_POST['gif_id']) ? $_POST['gif_id'] : false;
      if ($gif_id) {
        $comment->gif_id = 1;
        $comment->gif_url = $gif_id;
        $comment->save();
        //$gifImageUrl = $gif_id;
        //$bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">', $gifImageUrl, $gifImageUrl);
        //$comment->body = $body . '<br />' .$bodyGif;
        $comment->body = $body;
        $comment->save();
      }

      if($body && empty($gif_id)) {
        $comment->body = $body;
        $comment->save();
      }

      $parentCommentType = 'core_comment';

      if ($action->getType() == 'activity_action') {
        $commentType = $action->likes(true);
        if ($commentType->getType() == 'activity_action')
          $parentCommentType = 'activity_comment';
      }
      $parentCommentId = $this->_getParam('comment_id', false);

      $parentComment = Engine_Api::_()->getItem($parentCommentType, $parentCommentId);
      $parentComment->reply_count = new Zend_Db_Expr('reply_count + 1');
      $parentComment->save();
      $comment->parent_id = $parentCommentId;
      $comment->save();
      //fetch link from comment
      $regex = '/(https|http)?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if (!empty($matches[0])) {
        $preview = $this->previewCommentLink($matches[0], $comment, $viewer);
        if ($preview) {
          $comment->preview = $preview;
          $comment->save();
        }
      }

      // Notifications
      // Comment Reply notification to comment owner
      if ($parentComment->poster_type == 'user' && $parentComment->poster_id != $viewer->getIdentity()) {
        $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
        $user = Engine_Api::_()->getItem('user', $parentComment->poster_id);
        $notifyApi->addNotification($user, !empty($guid) ? $guid : $viewer, $action, 'comment_replycomment', array('label' => 'post'));
      } else {
        $type = $parentComment->poster_type;
        $id = $parentComment->poster_id;
        $commentItem = Engine_Api::_()->getItem($type, $id);
        if ($commentItem) {
          $commentUser = $commentItem->getOwner();
          if ($commentUser && $commentUser->getIdentity() != $viewer->getIdentity()) {
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            $notifyApi->addNotification($commentUser, !empty($guid) ? $guid : $viewer, $action, 'comment_replycomment', array('label' => 'post'));
            $viewer = $commentUser;
          }
        }
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['bodymention'], $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "reply" . '</a>';
      foreach ($result[2] as $value) {
        $user_id = str_replace('@_user_', '', $value);
        if (intval($user_id) > 0) {
          $item = Engine_Api::_()->getItem('user', $user_id);
          if (!$item || !$item->getIdentity())
            continue;
        } else {
          $itemArray = explode('_', $user_id);
          $resource_id_reply = $itemArray[count($itemArray) - 1];
          unset($itemArray[count($itemArray) - 1]);
          $resource_type_reply = implode('_', $itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply, $resource_id_reply);
          if (!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if (!$item || !$item->getIdentity())
            continue;
        }
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid : $viewer, $viewer, 'comment_taggedreply_people', array("commentLink" => $commentLink, 'resource_type' => $action->getType(), 'resource_id' => $action->getIdentity()));
      }
      //Tagging People by status box
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->error = Zend_Registry::get('Zend_Translate')->_($e);
      throw $e;
    }

    // Assign message for json
    $this->view->status = true;
    $this->view->message = 'Comment posted';
    $comment = $this->getCommentById($comment);
    $method = 'update';
    $show_all_comments = $this->_getParam('show_all_comments', false);
    if (!$resource_type)
      //$showAllComments = $this->_getParam('show_all_comments', false);
      $this->view->body = $this->view->partial(
        '_activitycommentreply.tpl',
        'comment',
        array('commentreply' => $comment, 'action' => $action, 'isPageSubject' => $guid, 'canComment' => true)
      );
    else
      $this->view->body = $this->view->partial(
        'list-comment/_subjectcommentreply.tpl',
        'comment',
        array('commentreply' => $comment, 'action' => $action, 'isPageSubject' => $guid, 'canComment' => true)
      );
    echo json_encode(array('status' => $this->view->status, 'content' => $this->view->body, 'error' => $this->view->error), JSON_HEX_QUOT | JSON_HEX_TAG);
    die;

  }
  public function getLikesAction()
  {
    $action_id = $this->_getParam('action_id');
    $comment_id = $this->_getParam('comment_id');

    if (
      !$action_id ||
      !$comment_id ||
      !($action = Engine_Api::_()->getItem('activity_action', $action_id)) ||
      !($comment = $action->comments()->getComment($comment_id))
    ) {
      $this->view->status = false;
      $this->view->body = '-';
      return;
    }

    $likes = $comment->likes()->getAllLikesUsers();
    $this->view->body = $this->view->translate(
      array(
        '%s likes this',
        '%s like this',
        engine_count($likes)
      ),
      strip_tags($this->view->fluentList($likes))
    );
    $this->view->status = true;
  }

  public function emojiAction()
  {
    $this->renderScript('_emoji.tpl');
  }

  //album photo upload function
  public function uploadFileAction()
  {
    if (!Engine_Api::_()->user()->getViewer()->getIdentity()) {
      $this->_redirect('login');
      return;
    }
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
      return;
    }
    if (empty($_FILES['Filedata'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    // Get album
    $viewer = Engine_Api::_()->user()->getViewer();
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum'))
      $module = 'sesalbum';
    else
      $module = 'album';

    $table = Engine_Api::_()->getDbtable('albums', $module);
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $type = $this->_getParam('type', 'wall');
      if (empty($type))
        $type = 'wall';
      $album = $table->getSpecialAlbum($viewer, $type);
      $photoTable = Engine_Api::_()->getDbtable('photos', $module);
      $photo = $photoTable->createRow();
      $photo->setFromArray(
        array(
          'owner_type' => 'user',
          'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
        )
      );
      $photo->save();
      $photo->setPhoto($_FILES['Filedata']);
      if ($type == 'message') {
        $photo->title = Zend_Registry::get('Zend_Translate')->_('Attached Image');
      }
      $photo->order = $photo->photo_id;
      $photo->album_id = $album->album_id;
      $photo->save();
      if (!$album->photo_id) {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }
      if ($type != 'message') {
        // Authorizations
        $auth = Engine_Api::_()->authorization()->context;
        $auth->setAllowed($photo, 'everyone', 'view', true);
        $auth->setAllowed($photo, 'everyone', 'comment', true);
      }
      $db->commit();
      $this->view->status = true;
      $this->view->photo_id = $photo->photo_id;
      $this->view->album_id = $album->album_id;
      $this->view->src = $this->view->url = $photo->getPhotoUrl('thumb.normalmain');
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected photos have been successfully saved.');
    } catch (Exception $e) {
      $db->rollBack();
      //throw $e;
      $this->view->status = false;
    }
    echo json_encode(array('src' => $this->view->src, 'photo_id' => $this->view->photo_id, 'status' => $this->view->status));
    die;
  }

  public function removepreviewAction()
  {
    $comment_id = $this->_getParam('comment_id', null);
    $type = $this->_getParam('type', null);
    if (empty($type))
      return;

    $comment = Engine_Api::_()->getItem($type, $comment_id);
    $comment->showpreview = 1;
    $comment->save();

    exit();
  }
}