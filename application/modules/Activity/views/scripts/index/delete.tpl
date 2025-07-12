<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: delete.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<div class='activity_delete_popup'>
  <?php if (empty($this->comment_id)): ?>
    <?php $id = 'activity_adv_delete'; ?>
  <?php else: ?>
    <?php $id = 'activity_adv_comment_delete'; ?>
  <?php endif; ?>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>" id="<?php echo $id; ?>">
    <input class="hidden_actn" type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>" />
    <div class="activity_delete_popup_head">
      <?php if (!empty($this->comment_id)): ?>
        <?php echo $this->translate("Delete Comment?") ?>
      <?php else: ?>
        <?php echo $this->translate("Delete Activity Item?") ?>
      <?php endif; ?>
    </div>
    <div class="activity_delete_popup_cont">
      <?php if (!empty($this->comment_id)): ?>
        <?php echo $this->translate("Are you sure that you want to delete this comment? This action cannot be undone.") ?>
      <?php else: ?>
        <?php echo $this->translate("Are you sure that you want to delete this activity item and all of its comments? This action cannot be undone. <br />You can also consider editing this post, if you just want to make some changes.") ?>
      <?php endif; ?>
    </div>
    <div class="activity_delete_popup_btm d-flex gap-2">
      <?php if (!empty($this->comment_id)): ?>
        <input type="hidden" name="comment_id" value="<?php echo (int) $this->comment_id ?>" class="hidden_cmnt" />
      <?php endif; ?>
      <?php if (!empty($this->comment_id)): ?>
        <button type='submit' class="btn btn-danger"><?php echo $this->translate("Delete") ?></button>
        <a href="javascript:void(0);" class="btn btn-link" onclick="ajaxsmoothboxclose();return false;"><?php echo $this->translate("cancel") ?></a>
      <?php else: ?>
        <div class="flex-fill">
          <button type='submit' class="btn btn-alt" onClick="ajaxsmoothboxclose();return false;"><?php echo $this->translate("Cancel") ?></button>
        </div>
        <div class="d-flex gap-2">
          <?php if (Engine_Api::_()->getItem('activity_action', $this->action_id)->canEdit()) { ?>
            <button type='submit' class="btn btn-primary edit_feed_edit"><?php echo $this->translate("Edit Feed") ?></button>
          <?php } ?>
          <button type='submit' class="btn btn-danger"><?php echo $this->translate("Delete Feed") ?></button>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php die; ?>