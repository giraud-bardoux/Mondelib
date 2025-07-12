<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activitycommentreply.tpl 2024-10-29 00:00:00Z 
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
$commentreply = $this->commentreply;
$isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
$action = $this->action;
$canComment = ($action->getTypeInfo()->commentable &&
  $this->viewer()->getIdentity() &&
  Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')
);
$languageTranslate = 'en';
?>
<?php if (empty($this->likeOptions)) { ?>
  <li id="comment-<?php echo $commentreply->comment_id; ?>">
    <template
      class="owner-info"><?php echo $this->getUserInfo($this->item($commentreply->poster_type, $commentreply->poster_id)); ?></template>
    <div class="comments_author_photo">
      <?php echo $this->htmlLink(
        $this->item($commentreply->poster_type, $commentreply->poster_id)->getHref(),
        $this->itemPhoto($this->item($commentreply->poster_type, $commentreply->poster_id), 'thumb.icon', $action->getSubject()->getTitle())
      ) ?>
    </div>
    <div class="comments_reply_info comments_info">
      <div class="comment_comments_options">
        <a href="javascript:void(0);" class="comment_cmt_hideshow comment_comments_options_icon"
          onclick="showhidecommentsreply('<?php echo $commentreply->comment_id ?>', '<?php echo $action->getIdentity(); ?>')"><i
            id="hideshow_<?php echo $commentreply->comment_id ?>_<?php echo $action->getIdentity(); ?>"
            class="far fa-minus-square"></i></a>
        <?php if ($this->viewer()->getIdentity() && ( ($this->viewer()->getIdentity() == $commentreply->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')) || $this->viewer()->getIdentity() != $comment->poster_id)): ?>
          <div class="comment_pulldown_wrapper ">
            <a href="javascript:void(0);" class="comment_comments_options_icon" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></a>
            <ul class="dropdown-menu dropdown-menu-end comment_pulldown_cont">
              <?php if (($this->viewer()->getIdentity() == $commentreply->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')) || (($this->subject() && method_exists($this->subject(), 'canDeleteComment') && $this->subject()->canDeleteComment($this->subject())))) { ?>
                <li>
                  <?php echo $this->htmlLink(
                    array(
                      'route' => 'default',
                      'module' => 'activity',
                      'controller' => 'index',
                      'action' => 'delete',
                      'action_id' => $action->action_id,
                      'comment_id' => $commentreply->comment_id,
                    ), $this->translate('Delete'), array('class' => 'ajaxPrevent dropdown-item icon_delete commentsmoothbox comment_delete')) ?>
                </li>
                <?php //if (empty($commentreply->gif_id) && empty($commentreply->emoji_id)) { ?>
                  <?php if ((($this->subject() && method_exists($this->subject(), 'canEditComment') && $this->subject()->canEditComment($this->subject()))) || ($this->viewer()->getIdentity() == $commentreply->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity'))) { ?>
                    <li>
                      <?php echo $this->htmlLink(('javascript:;'), $this->translate('Edit'), array('class' => 'dropdown-item icon_edit comment_reply_edit')) ?>
                    </li>
                  <?php } ?>
                <?php //} ?>
              <?php } ?>
              <?php if ($this->viewer()->getIdentity() != $commentreply->poster_id) { ?>
                <li>
                  <?php echo $this->htmlLink(array("module" => "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $commentreply->getGuid()), '<span>' . $this->translate("Report") . '</span>', array('onclick' => "openSmoothBoxInUrl(this.href);return false;", "class" => "ajaxPrevent dropdown-item icon_report comment_report")); ?>
                </li>
              <?php } ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <div class="comments_content">
        <span class='comments_reply_author comments_author core_tooltip'
          data-src="<?php echo $this->item($commentreply->poster_type, $commentreply->poster_id)->getGuid(); ?>">
          <?php echo $this->htmlLink($this->item($commentreply->poster_type, $commentreply->poster_id)->getHref(), $this->item($commentreply->poster_type, $commentreply->poster_id)->getTitle()); ?>
        </span>
        <?php $content = $commentreply->body; ?>
        <?php
        echo $this->partial(
          '_activitycommentreplycontent.tpl',
          'comment',
          array('commentreply' => $commentreply, 'isPageSubject' => $isPageSubject)
        );
        ?>
      <?php } ?>
    </div>
    <?php if ($commentreply->likes()->getLikeCount() > 0): ?>
      <?php $likesGroup = Engine_Api::_()->comment()->commentLikesGroup($commentreply, false);
      if (engine_count($likesGroup['data'])) {
        ?>
        <span class="comments_likes_total">
          <span class="comments_likes_reactions">
            <?php foreach ($likesGroup['data'] as $type) { ?>
              <a title="<?php echo $this->translate('%s (%s)', $type['counts'], Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type['type'])) ?>"
                href="javascript:;" class="ajaxsmoothbox"
                data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $commentreply->getIdentity(), 'id' => $action->getIdentity(), 'resource_type' => $action->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><i
                  style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type['type']); ?>);"></i></a>
            <?php } ?>
          </span>
          <a href="javascript:;" class="ajaxsmoothbox"
            data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $commentreply->getIdentity(), 'id' => $action->getIdentity(), 'resource_type' => $action->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><?php echo $commentreply->likes()->getLikeCount(); ?></a>
        </span>
      <?php } ?>
    <?php endif ?>
    <ul class="comments_reply_date comments_date"
      id="comments_reply_<?php echo $commentreply->comment_id; ?>_<?php echo $action->getIdentity(); ?>"
      style="display:block;">

      <?php if ($canComment): ?>
        <template
          class="owner-info"><?php echo $this->getUserInfo($this->item($commentreply->poster_type, $commentreply->poster_id)); ?></template>
        <?php $isLiked = $commentreply->likes()->isLike($isPageSubject); ?>
        <?php if ($this->viewer()->getIdentity() && $this->canComment):
          if ($likeRow = $commentreply->likes()->getLike($isPageSubject)) {

            $like = true;
            if ($likeRow->type)
              $type = $likeRow->type;
            else
              $type = 1;
            ;
            $imageLike = Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type);
            $text = Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type);
          } else {
            $like = false;
            $type = '';
            $imageLike = '';
            $text = 'CORELIKE';
          }
          ?>
          <li
            class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions comment_hoverbox_wrapper">
            <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
            <?php if (engine_count($getReactions) > 0): ?>
              <div class="comment_hoverbox">
                <?php foreach ($getReactions as $getReaction): ?>
                  <span>
                    <span data-text="<?php echo $this->translate($getReaction->title); ?>"
                      data-actionid="<?php echo $action->getIdentity(); ?>"
                      data-commentid="<?php echo $commentreply->getIdentity(); ?>"
                      data-type="<?php echo $getReaction->reaction_id; ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>"
                      class="commentcommentlike reaction_btn comment_hoverbox_btn">
                      <div class="reaction comment_hoverbox_btn_icon"> <i class="react"
                          style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($getReaction->reaction_id); ?>)"></i>
                      </div>
                    </span>
                    <div class="text">
                      <div><?php echo $this->translate($getReaction->title); ?></div>
                    </div>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <a href="javascript:void(0);" <?php if (!empty($_SESSION["corefromLightbox"])) { ?>
                id="comment_like_action_<?php echo $commentreply->getIdentity(); ?>" <?php $_SESSION["corefromLightbox"] = '';
            } else { ?> id="comment_like_actionrec_<?php echo $commentreply->getIdentity(); ?>" <?php } ?>
              data-like="<?php echo $this->translate('CORELIKEC') ?>"
              data-unlike="<?php echo $this->translate('COREUNLIKEC') ?>"
              data-actionid="<?php echo $action->getIdentity(); ?>"
              data-commentid="<?php echo $commentreply->getIdentity(); ?>"
              data-guid="<?php echo $isPageSubject->getGuid(); ?>" data-type="1"
              class="commentcomment<?php echo $like ? 'unlike _reaction' : 'like'; ?>">
              <span><?php echo $this->translate($text); ?></span>
            </a>
          </li>
        <?php endif; ?>
        <li class="sep">&middot;</li>
      <?php endif ?>

      <li class="comments_reply_btn">
      <?php echo $this->htmlLink($this->viewer()->getIdentity() ? 'javascript:;' : $this->baseUrl().'/login', $this->translate('COREREPLY'), array('class' => $this->viewer()->getIdentity() ? 'commentreplyreply' : "")) ?>
      </li>
      <li class="sep">&middot;</li>

      <?php if ($this->viewer()->getIdentity() && $commentreply->poster_id != $this->viewer()->getIdentity() && $action->type == "post_self_buysell"): ?>
        <li class="comments_reply">
          <?php echo $this->htmlLink($this->url(array('owner_id' => $commentreply->poster_id, 'action' => 'contact', 'controller' => 'index', 'module' => 'comment'), 'default', true), $this->translate('COREMESSAGE'), array('class' => 'ajaxsmoothbox')) ?>
        </li>
        <li class="sep">&middot;</li>
      <?php endif; ?>
      <?php if (isset($comment->body) && strlen(preg_replace("/(\\\u[0-9a-f]{4})+?|\s+/", "", strip_tags($content)))) { ?>
        <!-- <li class="comments_reply_translate"> <a href="javascript:void(0);"
            onClick="socialSharingPopUp('https://translate.google.com/#auto/<?php //echo $languageTranslate; ?>/<?php //echo urlencode(strip_tags($comment->body)); ?>','Google');return false;"><?php //echo $this->translate("Translate"); ?></a>
        </li>
        <li class="sep">&middot;</li> -->
      <?php } ?>
      <?php if (
        $this->viewer()->getIdentity() &&
        (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
          ($this->viewer()->getIdentity() == $commentreply->poster_id) ||
          Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity'))
      ): ?>
        <?php if (!empty($comment->preview) && empty($comment->showpreview)) { ?>
          <li id="remove_preview_<?php echo $commentreply->comment_id ?>">
            <a href="javascript:void(0);"
              onclick="removePreview('<?php echo $comment->getIdentity(); ?>','<?php echo $commentreply->comment_id; ?>', '<?php echo $commentreply->getType(); ?>')">
              <?php echo $this->translate("Remove Preview"); ?>
            </a>
          </li>
          <li id="remove_previewli_<?php echo $commentreply->comment_id ?>" class="sep">&middot;</li>
        <?php }endif; ?>

      <li class="comments_reply_timestamp">
        <?php echo $this->timestamp($commentreply->creation_date); ?>
      </li>
    </ul>
    <?php if (empty($this->likeOptions)) { ?>
    </div>
  </li>
<?php } ?>