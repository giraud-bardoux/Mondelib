<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activitylikereaction.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>

<?php $action = $this->action;
$isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
?>
<?php
$likesGroup = Engine_Api::_()->comment()->likesGroup($action);
$commentCount = Engine_Api::_()->comment()->commentCount($action);
if ($commentCount || engine_count($likesGroup['data'])) {
  ?>
  <li class="comment_stats">
    <?php if (engine_count($likesGroup['data'])) { ?>
      <div class="comments_stats_likes">
        <span class="comments_likes_reactions">
          <?php foreach ($likesGroup['data'] as $type) { ?>
            <a title="<?php echo $this->translate('%s (%s)', $type['counts'], Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type['type'])) ?>"
              href="javascript:;" class="ajaxsmoothbox"
              data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(), 'resource_type' => $likesGroup['resource_type'], 'item_id' => $likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"><i
                style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type['type']); ?>);"></i></a>
          <?php } ?>
        </span>
        <a href="javascript:;" class="ajaxsmoothbox"
          data-url="<?php echo $this->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'id' => $action->getIdentity(), 'resource_type' => $likesGroup['resource_type'], 'item_id' => $likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>">
          <?php echo $this->FluentListUsers($action->likes()->getAllLikes(), $this->viewer(), '', $action->likes()->getLike($this->viewer())); ?></a>

      </div>
    <?php } ?>
    <?php if (!$this->isOnThisDayPage) { ?>
      <div class="comments_stats_comments comment_stats_<?php echo $action->getIdentity(); ?>">
        <?php if ($commentCount > 0) { ?>
          <a class="comment_btn_open select_action_<?php echo $action->getIdentity(); ?>"
            data-actionid="<?php echo $action->getIdentity(); ?>"
            href="javascript:void(0);"><?php echo $this->translate(array('%s comment', '%s comments', $commentCount), $this->locale()->toNumber($commentCount)) ?></a>
        <?php } ?>
      </div>
    <?php } ?>
  </li>
<?php } ?>
