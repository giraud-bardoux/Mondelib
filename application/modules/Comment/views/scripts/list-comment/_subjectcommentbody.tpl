<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _subjectcommentbody.tpl 2024-10-29 00:00:00Z 
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
  $actionBody = !empty($this->subject) ? $this->subject : $this->action;
  if (!$actionBody)
    return;
  $page = !empty($this->page) ? $this->page : 'zero';
  $viewmore = !empty($this->viewmore) ? $this->viewmore : false;
  $canComment = ($actionBody->authorization()->isAllowed($this->viewer(), 'comment'));
  $poster = $this->item($comment->poster_type, $comment->poster_id);
  $canDelete = ($actionBody->authorization()->isAllowed($this->viewer(), 'edit') || $poster->isSelf($this->viewer()));
  $islanguageTranslate = 1;
  $languageTranslate = 'en';
?>
<?php if (!$viewmore) { ?>
  <li id="comment-<?php echo $comment->comment_id ?>" class="comment_cnt_li">
    <div class="comments_author_photo">
      <?php echo $this->htmlLink(
        $this->item($comment->poster_type, $comment->poster_id)->getHref(),
        $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $actionBody->getOwner()->getTitle())
      ) ?>
    </div>
    <div class="comments_info">
      <div class="comment_comments_options">
        <a href="javascript:void(0);" class="comment_cmt_hideshow comment_comments_options_icon"
          onclick="showhidecommentsreply('<?php echo $comment->comment_id ?>', '<?php echo $actionBody->getIdentity(); ?>')"><i
            id="hideshow_<?php echo $comment->comment_id ?>_<?php echo $actionBody->getIdentity(); ?>"
            class="far fa-minus-square"></i></a>
        <?php if ($canDelete): ?>
          <div class="dropdown comment_pulldown_wrapper">
            <a href="javascript:void(0);" class="comment_comments_options_icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></a>
            <ul class="dropdown-menu dropdown-menu-end comment_pulldown_cont">
              <?php if ($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity') || (($this->subject() && method_exists($this->subject(), 'canDeleteComment') && $this->subject()->canDeleteComment($this->subject())))) { ?>
                <li>
                  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'comment', 'controller' => 'index', 'action' => 'delete', 'type' => $actionBody->getType(), 'action_id' => $actionBody->getIdentity(), 'comment_id' => $comment->comment_id), $this->translate('Delete'), array('class' => 'ajaxPrevent dropdown-item icon_delete commentsmoothbox comment_delete')) ?>
                </li>
              <?php } ?>
              <?php //if (empty($comment->emoji_id) && empty($comment->gif_id)) { ?>
                <?php if ($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity') || ($this->subject() && method_exists($this->subject(), 'canEditComment') && $this->subject()->canEditComment($this->subject()))) { ?>
                  <li>
                    <?php echo $this->htmlLink(('javascript:;'), $this->translate('Edit'), array('class' => 'dropdown-item icon_edit activity_comment_edit')) ?>
                  </li>
                <?php } ?>
              <?php //} ?>
              <?php if ($this->viewer()->getIdentity() != $comment->poster_id) { ?>
                <li>
                  <?php echo $this->htmlLink(array("module" => "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $comment->getGuid()), '<span>' . $this->translate("Report") . '</span>', array('onclick' => "openSmoothBoxInUrl(this.href);return false;", "class" => "ajaxPrevent dropdown-item icon_report")); ?>
                </li>
              <?php } ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <div class="comments_content">
        <span class='comments_author  core_tooltip'
          data-src="<?php echo $this->item($comment->poster_type, $comment->poster_id)->getGuid(); ?>">
          <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle()); ?>
        </span>
        <?php $content = $comment->body; ?>
        <?php
        echo $this->partial(
          'list-comment/_subjectcommentcontent.tpl',
          'comment',
          array('comment' => $comment, 'isPageSubject' => $this->subject)
        );
        ?>
      </div>
      <?php
      echo $this->partial(
        'list-comment/_subjectcommentbodyoptions.tpl',
        'comment',
        array('comment' => $comment, 'actionBody' => $actionBody, 'canComment' => $canComment, 'isPageSubject' => $this->subject)
      );
      ?>

      <div class="comments_reply comment_replies clearfix"
        id="comments_reply_reply_<?php echo $comment->comment_id; ?>_<?php echo $actionBody->getIdentity(); ?>"
        style="display:block;">
        <ul class="comments_reply_cnt">
        <?php } ?>
        <?php $commentReply = Engine_Api::_()->comment()->getReply($comment->comment_id, $actionBody, $page); ?>
        <?php if ($commentReply->getCurrentPageNumber() > 1): ?>
          <li class="comment_reply_view_more">
            <div class="comments_viewall">
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View more replies'), array(
                'onclick' => 'commentactivitycommentreply("' . $actionBody->getIdentity() . '","' . $comment->getIdentity() . '", "' . ($commentReply->getCurrentPageNumber() - 1) . '",this,"","' . $actionBody->getType() . '")'
              )
              ) ?>
            </div>
          </li>
        <?php endif; ?>
        <?php foreach ($commentReply as $commentreply) { ?>
          <?php
          echo $this->partial(
            'list-comment/_subjectcommentreply.tpl',
            'comment',
            array('commentreply' => $commentreply, 'action' => $actionBody, 'canComment' => $canComment, 'isPageSubject' => $this->subject)
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
                <?php
                echo $this->itemPhoto($this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity()), 'thumb.icon', $this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity())->getTitle());
                ?>
              </div>
              <?php echo $this->partial('_commentAttachments.tpl', 'comment', array('comment' => $comment, 'item' => $actionBody, 'type' => 'commentsubject')); ?>
            </form>
          </div>
        <?php } ?>
      </div>
    </div>
  </li>
<?php } ?>