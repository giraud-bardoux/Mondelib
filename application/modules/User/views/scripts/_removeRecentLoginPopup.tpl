<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _removeRecentLoginPopup.tpl 9979 2013-03-19 22:07:33Z john $
 * @author     John
 */
?>
<div id="remove_pop_wrap">
  <div class="modal fade" id="recent_login_remove" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="recent_login_removeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" id="send_recentremove_form">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="recent_login_removeLabel"><?php echo $this->translate("Remove account"); ?></h1>
        </div>
        <div class="modal-body">
          <p><?php echo $this->translate("Are you sure that you want to remove this account?"); ?></p>
        </div>
        <div class="modal-footer">
          <form action="" method="post" id="remove_recentlogin_form" enctype="multipart/form-data">
            <input type="hidden" name="removeUserId" id="removeUserId" />
            <input type="hidden" name="redirectURL" id="redirectURL" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
            <button type="button" class="btn btn-link" data-bs-dismiss="modal" onclick="closeRemoveUser();"><?php echo $this->translate("Cancel"); ?></button>
            <button type="button" class="btn btn-primary" id="remove_account"><?php echo $this->translate('Remove Account'); ?></button>
          </form>
        </div>
        <div class="core_loading_cont_overlay" id="core_loading_cont_overlay" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
en4.core.runonce.add(function() {
  scriptJquery(scriptJquery('#remove_pop_wrap').html()).appendTo('#append-script-data');
  scriptJquery('#remove_pop_wrap').remove()
});
</script>
