<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _subjectcommentbodyoptions.tpl 2024-10-29 00:00:00Z 
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
$canComment = $this->canComment;
$comment = $this->comment;
$isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
$actionBody = $this->actionBody;

$islanguageTranslate = 1;
$languageTranslate = 'en';

?>
<?php if ($comment->likes()->getLikeCount() > 0): ?>
  <?php $likesGroup = Engine_Api::_()->comment()->commentLikesGroup($comment, false);
  $counts = 0;
  if (engine_count($likesGroup['data'])) {
    ?>
    <span class="comments_likes_total">
      <span class="comments_likes_reactions">
        <?php foreach ($likesGroup['data'] as $type) {
          $counts = $type['counts'] + $counts;
          ?>
          <a title="<?php echo $this->translate('%s (%s)', $type['counts'], Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type['type'])) ?>"
            href="javascript:;" class="ajaxsmoothbox"
            data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $comment->getIdentity(), 'id' => $actionBody->getIdentity(), 'resource_type' => $actionBody->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><i
              style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type['type']); ?>);"></i></a>
        <?php } ?>
      </span>
      <a href="javascript:;" class="ajaxsmoothbox"
        data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $comment->getIdentity(), 'id' => $actionBody->getIdentity(), 'resource_type' => $actionBody->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><?php echo $counts; ?></a>
    </span>
  <?php } ?>
<?php endif ?>
<ul class="comments_date"
  id="comments_reply_<?php echo $comment->comment_id; ?>_<?php echo $actionBody->getIdentity(); ?>"
  style="display:block;">

  <?php if ($canComment): ?>
    <template
      class="owner-info"><?php echo $this->getUserInfo($this->item($comment->poster_type, $comment->poster_id)); ?></template>

    <?php $isLiked = $comment->likes()->isLike($isPageSubject); ?>
    <?php if ($this->viewer()->getIdentity() && $this->canComment):
      if ($likeRow = $comment->likes()->getLike($isPageSubject)) {

        $like = true;
        if ($likeRow->type)
          $type = $likeRow->type;
        else
          $type = 1;

        $imageLike = Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type);
        $text = Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type);
      } else {
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'CORELIKE';
      }
      ?>
      <li class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions comment_hoverbox_wrapper">
        <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
        <?php if (engine_count($getReactions) > 0): ?>
          <div class="comment_hoverbox">
            <?php foreach ($getReactions as $getReaction): ?>
              <span>
                <span data-text="<?php echo $this->translate($getReaction->title); ?>"
                  data-actionid="<?php echo $actionBody->getIdentity(); ?>"
                  data-commentid="<?php echo $comment->getIdentity(); ?>" data-type="<?php echo $getReaction->reaction_id; ?>"
                  data-subjectid="<?php echo $isPageSubject->getIdentity(); ?>"
                  data-sbjecttype="<?php echo $isPageSubject->getType(); ?>"
                  data-guid="<?php echo $isPageSubject->getGuid(); ?>"
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
            id="comment_like_action_<?php echo $comment->getIdentity(); ?>" <?php $_SESSION["corefromLightbox"] = '';
        } else { ?>
            id="comment_like_actionrec_<?php echo $comment->getIdentity(); ?>" <?php } ?>
          data-like="<?php echo $this->translate('CORELIKEC') ?>"
          data-subjectid="<?php echo $isPageSubject->getIdentity(); ?>"
          data-sbjecttype="<?php echo $isPageSubject->getType(); ?>"
          data-unlike="<?php echo $this->translate('COREUNLIKEC') ?>"
          data-actionid="<?php echo $actionBody->getIdentity(); ?>"
          data-commentid="<?php echo $comment->getIdentity(); ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>"
          data-type="1" class="commentcomment<?php echo $like ? 'unlike _reaction' : 'like'; ?>">
          <span><?php echo $this->translate($text); ?></span>
        </a>
      </li>
    <?php endif; ?>
    <li class="sep">&middot;</li>
  <?php endif ?>
  <?php  if($this->viewer()->getIdentity()){ ?> 
  <li class="comments_reply">
    <?php echo $this->htmlLink($this->viewer()->getIdentity() ? 'javascript:;' : $this->baseUrl().'/login', $this->translate('COREREPLY'), array('class' => $this->viewer()->getIdentity() ? 'commentreply' : "")) ?>
  </li>
  <?php }else if(!$this->viewer()->getIdentity()){ ?> 
    <li class="comments_reply">
    <?php echo $this->htmlLink($this->url(array(), 'user_login', true), $this->translate('COREREPLY'), array('class' => '')) ?>
  </li>
  <?php } ?>
  <li class="sep">&middot;</li>

  <?php if ($islanguageTranslate) { ?>
    <!-- <li class="comments_reply_translate"> <a href="javascript:void(0);" class="comments_translate_link floatR"
        onClick="socialSharingPopUp('https://translate.google.com/#auto/<?php //echo $languageTranslate; ?>/<?php //echo urlencode(strip_tags($comment->body)); ?>','Google');return false;"><?php //echo $this->translate("Translate"); ?></a>
    </li>
    <li class="sep">&middot;</li> -->
  <?php } ?>
  <li class="comments_timestamp">
    <?php echo $this->timestamp($comment->creation_date); ?>
  </li>

</ul>