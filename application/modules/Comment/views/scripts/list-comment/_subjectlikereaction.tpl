<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _subjectlikereaction.tpl 2024-10-29 00:00:00Z 
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
$subject = !empty($this->subject) ? $this->subject : $this->action;
$likesGroup = Engine_Api::_()->comment()->likesGroup($subject, 'subject');
$commentCount = Engine_Api::_()->comment()->commentCount($subject, 'subject');
if ($commentCount || engine_count($likesGroup['data'])) {
  ?>
  <li class="comment_stats">
    <?php if (engine_count($likesGroup['data'])) { ?>
      <div class="comments_stats_likes">
        <span class="comments_likes_reactions">
          <?php foreach ($likesGroup['data'] as $type) { ?>
            <a title="<?php echo $this->translate('%s (%s)', $type['counts'], Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type['type'])) ?>"
              href="javascript:;" class="ajaxsmoothbox"
              data-url="<?php echo $this->url(array('module' => 'comment', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $subject->getIdentity(), 'resource_type' => $likesGroup['resource_type'], 'item_id' => $likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"><i
                style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type['type']); ?>);"></i></a>
          <?php } ?>
        </span>
        <a href="javascript:;" class="ajaxsmoothbox"
          data-url="<?php echo $this->url(array('module' => 'comment', 'controller' => 'ajax', 'action' => 'likes', 'id' => $subject->getIdentity(), 'resource_type' => $likesGroup['resource_type'], 'item_id' => $likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>">
          <?php echo $this->FluentListUsers($subject->likes()->getAllLikesUsers(), $this->viewer(), '', $subject->likes()->getLike($this->viewer())); ?></a>
      </div>
    <?php } ?>
    <div class="comments_stats_comments  comment_stats_<?php echo $subject->getIdentity(); ?>"">
    <?php if ($commentCount > 0) { ?>
      <a class=" comment_btn_open select_action_<?php echo $subject->getIdentity(); ?>" data-subjectid="<?php echo $subject->getIdentity(); ?>" data-subjecttype="<?php echo $subject->getType(); ?>" href="javascript:void(0);">
        <?php echo $this->translate(array('%s comment', '%s comments', $commentCount), $this->locale()->toNumber($commentCount)) ?></a>
        &nbsp;&middot;&nbsp;
        <div class="comment_pulldown_wrapper  dropdown" data-actionid="<?php echo $subject->getIdentity(); ?>">
          <a href="javascript:void(0);" class="search_advcomment_txt dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span><?php echo $this->translate('Newest'); ?></span></a>
          <ul class="dropdown-menu search_adv_comment dropdown-menu-end">
            <li><a href="javascript:;" data-subjectype="<?php echo $subject->getType(); ?>" data-type="newest" class="dropdown-item subject search_adv_comment_a active"><?php echo $this->translate("Newest"); ?></a>
            </li>
            <li><a href="javascript:;" data-subjectype="<?php echo $subject->getType(); ?>" data-type="oldest" class="dropdown-item subject search_adv_comment_a"><?php echo $this->translate("Oldest"); ?></a>
            </li>
          </ul>

        </div>
      </div>
    <?php } ?>
  </li>
<?php } ?>