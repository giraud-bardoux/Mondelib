<?php ?>
<div class="user_confirm_popup">
	<p><?php echo $this->translate("Are you sure you want to remove your profile photo?"); ?></p>
  <div class="user_confirm_popup_btns">
  	<button onclick="javascript:window.ajaxsmoothboxclose();"><?php echo $this->translate("Cancel"); ?></button>
   	<button onclick="javascript:window.removeProfilePhoto();window.ajaxsmoothboxclose();"><?php echo $this->translate("Confirm"); ?></button>
  </div>
</div>
