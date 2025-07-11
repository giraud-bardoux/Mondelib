<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: approve-feeds.tpl 2024-10-28 00:00:00Z 
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
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>" id="<?php echo $id; ?>">
    <input class="hidden_actn" type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>" />
    <div class="activity_delete_popup_head">
      <?php echo $this->translate("Approve Activity Item?") ?>
    </div>
    <div class="activity_delete_popup_cont">
      <?php echo $this->translate("Are you sure want to approve this feed? This action cannot be undone.") ?>
    </div>
    <div class="activity_delete_popup_btm clearfix">
      <input type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>" />
      <button type='button' data-url="<?php echo (int) $this->action_id; ?>"
        class="activity_approve_btn"><?php echo $this->translate("Approve") ?></button>
      <?php echo $this->translate(" or ") ?>
      <a href="javascript:void(0);" onclick="ajaxsmoothboxclose();"><?php echo $this->translate("cancel") ?></a>
    </div>
  </form>
</div>