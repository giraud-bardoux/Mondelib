<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activitycommentbody.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php
$comment = $this->comment;
$actionBody = $this->action;
if (!$actionBody)
  return;
$page = !empty($this->page) ? $this->page : 'zero';
$isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
$viewmore = !empty($this->viewmore) ? $this->viewmore : false;
$canComment = ($actionBody->getTypeInfo()->commentable &&
  $this->viewer()->getIdentity() &&
  Engine_Api::_()->authorization()->isAllowed($actionBody->getCommentableItem(), null, 'comment')
);
?>
<?php if (!$viewmore) { ?>
  <li id="comment-<?php echo $comment->comment_id ?>" class="comment_cnt_li">
    <template
      class="owner-info"><?php echo $this->getUserInfo($this->item($comment->poster_type, $comment->poster_id)); ?></template>
    <div class="comments_author_photo">
      <?php echo $this->htmlLink(
        $this->item($comment->poster_type, $comment->poster_id)->getHref(),
        $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $actionBody->getSubject()->getTitle())
      ) ?>
    </div>
    <div class="comments_info">
      <div class="comment_comments_options">
        <a href="javascript:void(0);" class="comment_cmt_hideshow comment_comments_options_icon" onclick="showhidecommentsreply('<?php echo $comment->comment_id ?>', '<?php echo $actionBody->getIdentity(); ?>')"><i id="hideshow_<?php echo $comment->comment_id ?>_<?php echo $actionBody->getIdentity(); ?>" class="far fa-minus-square"></i></a>
        <?php if ($this->viewer()->getIdentity() && ( ($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')) || $this->viewer()->getIdentity() != $comment->poster_id)): ?>
          <div class="comment_pulldown_wrapper  dropdown">
            <a href="javascript:void(0);" class="comment_comments_options_icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></a>
            <ul class="dropdown-menu dropdown-menu-end comment_pulldown_cont">
              <?php if (($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')) || (($this->subject() && method_exists($this->subject(), 'canDeleteComment') && $this->subject()->canDeleteComment($this->subject())))) { ?>
                <li>
                  <?php echo $this->htmlLink(
                    array(
                      'route' => 'default',
                      'module' => 'activity',
                      'controller' => 'index',
                      'action' => 'delete',
                      'action_id' => $actionBody->action_id,
                      'comment_id' => $comment->comment_id,
                    ),
                    $this->translate('Delete'),
                    array('class' => 'ajaxPrevent dropdown-item icon_delete commentsmoothbox comment_delete')
                  ) ?>
                </li>
              <?php } ?>
              <?php //if (empty($comment->gif_id) && empty($comment->emoji_id)) { ?>
                <?php if ((($this->subject() && method_exists($this->subject(), 'canEditComment') && $this->subject()->canEditComment($this->subject()))) || ($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity'))) { ?>
                  <li>
                    <?php echo $this->htmlLink(('javascript:;'), $this->translate('Edit'), array('class' => 'dropdown-item icon_edit activity_comment_edit')) ?>
                  </li>
                <?php } ?>
              <?php //} ?>
              <?php if ($this->viewer()->getIdentity() != $comment->poster_id) { ?>
                <li>
                  <?php echo $this->htmlLink(array("module" => "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $comment->getGuid()), '<span>' . $this->translate("Report") . '</span>', array('onclick' => "openSmoothBoxInUrl(this.href);return false;", "class" => "ajaxPrevent dropdown-item icon_report comment_report")); ?>
                </li>
              <?php } ?>
            </ul>

          </div>
        <?php endif; ?>
      </div>
      <div class="comments_content">
        <span class='comments_author core_tooltip'
          data-src="<?php echo $this->item($comment->poster_type, $comment->poster_id)->getGuid(); ?>">
          <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle()); ?>
        </span>
        <?php $content = $comment->body; ?>
        <?php
        echo $this->partial(
          '_activitycommentcontent.tpl',
          'comment',
          array('comment' => $comment, 'isPageSubject' => $isPageSubject)
        );
        ?>
      </div>

      <?php
      echo $this->partial(
        '_activitycommentbodyoptions.tpl',
        'comment',
        array('comment' => $comment, 'actionBody' => $actionBody, 'canComment' => $canComment, 'isPageSubject' => $isPageSubject)
      );
      ?>
      <div class="comments_reply comment_replies clearfix"
        id="comments_reply_reply_<?php echo $comment->comment_id; ?>_<?php echo $actionBody->getIdentity(); ?>"
        style="display:block;">
        <ul class="comments_reply_cnt">
        <?php } ?>
        <?php $commentReply = $actionBody->getReply($comment->comment_id, '', $page); ?>
        <?php if ($commentReply->getCurrentPageNumber() > 1): ?>
          <li class="comment_reply_view_more">
            <div class="comments_viewall">
              <?php if ($comment instanceof Activity_Model_Comment) {
                $module = 'activity';
              } else {
                $module = "core";
              } ?>
              <?php echo $this->htmlLink(
                'javascript:void(0);',
                $this->translate('View more replies'),
                array(
                  'onclick' => 'commentactivitycommentreply("' . $actionBody->getIdentity() . '","' . $comment->getIdentity() . '", "' . ($commentReply->getCurrentPageNumber() - 1) . '",this,"' . $module . '")'
                )
              ) ?>
            </div>
          </li>
        <?php endif; ?>
        <?php foreach ($commentReply as $commentreply) { ?>
          <?php
          echo $this->partial(
            '_activitycommentreply.tpl',
            'comment',
            array('commentreply' => $commentreply, 'action' => $actionBody, 'canComment' => $canComment, 'isPageSubject' => $this->isPageSubject)
          );
        }
        ?>
        <?php if (!$viewmore) { ?>
        </ul>
        <?php if (Engine_Api::_()->user()->getViewer()->getIdentity() != 0) { ?>
          <div class="comment_reply_form" style="display:none;">
            <template
              class="owner-info"><?php echo $this->getUserInfo($this->item($comment->poster_type, $comment->poster_id)); ?></template>
            <form class="activity-comment-form-reply advcomment_form" method="post" style="display:none;">
              <div class="comment_user_img comments_author_photo">
                <?php echo $this->itemPhoto($isPageSubject, 'thumb.icon', $isPageSubject->getTitle()); ?>
              </div>
              <?php echo $this->partial('_commentAttachments.tpl', 'comment', array('comment' => $comment, 'item' => $actionBody, 'type' => 'activitycommentbody')); ?>
            </form>
          </div>
        <?php } ?>
      </div>
    </div>
  </li>
<?php } ?>
