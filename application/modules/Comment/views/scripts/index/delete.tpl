<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: delete.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
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
        <?php echo $this->translate("Delete Feed?") ?>
      <?php endif; ?>
    </div>
    <div class="activity_delete_popup_cont">
      <?php if (!empty($this->comment_id)): ?>
        <?php echo $this->translate("Are you sure that you want to delete this comment? This action cannot be undone.") ?>
      <?php else: ?>
        <?php echo $this->translate("This feed will be deleted and you won't be able to find it anymore. You can also edit this feed, if you just want to change something.") ?>
      <?php endif; ?>
    </div>
    <div class="activity_delete_popup_btm clearfix">

      <?php if (!empty($this->comment_id)): ?>
        <input type="hidden" name="comment_id" value="<?php echo (int) $this->comment_id ?>" class="hidden_cmnt" />
      <?php endif; ?>
      <?php if (!empty($this->comment_id)): ?>
        <button type='submit'><?php echo $this->translate("Delete") ?></button>
        <?php echo $this->translate(" or ") ?>
        <a href="javascript:void(0);" onclick="parent.Smoothbox.close();"><?php echo $this->translate("Cancel") ?></a>
      <?php else: ?>
        <div class="floatL">
          <button type='submit'
            onClick="ajaxsmoothboxclose();return false;"><?php echo $this->translate("Cancel") ?></button>
        </div>
        <div class="floatR">
          <button type='submit' class="edit_feed_edit"><?php echo $this->translate("Edit Feed") ?></button>
          <button type='submit'><?php echo $this->translate("Delete Feed") ?></button>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>