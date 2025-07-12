<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_invites', 'childMenuItemName' => 'invite_admin_settings')); ?>
<div class="manage_invite_heading_top">
  <h2 class="page_heading"><?php echo $this->translate('Manage Invites') ?></h2>

  <?php $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
  $levelsvalue = unserialize($levels); ?>
  <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1) && engine_in_array($this->viewer()->level_id, $levelsvalue)) { ?>
    <a class="admin_link_btn" href="invite"><?php echo $this->translate("Invite New User"); ?></a>
  <?php } ?>
</div>

<?php if (engine_count($this->navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class='clear'>
  <div class='settings core_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    enableSignup('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting("invite.enable", 1); ?>');
  });

  function enableSignup(value) {
    if (value == 1) {
      scriptJquery("#invite_signupenable-wrapper").show();
      scriptJquery("#invite_socialmediaoptions-wrapper").show();
      scriptJquery('#invite_allowlevels-wrapper').show();

      scriptJquery("#invite_facebook-wrapper").show();
      scriptJquery("#invite_facebookclientid-wrapper").show();
      scriptJquery("#invite_facebookclientsecret-wrapper").show();

    } else {
      scriptJquery("#invite_signupenable-wrapper").hide();
      scriptJquery("#invite_socialmediaoptions-wrapper").hide();
      scriptJquery('#invite_allowlevels-wrapper').hide();

      scriptJquery("#invite_facebook-wrapper").hide();
      scriptJquery("#invite_facebookclientid-wrapper").hide();
      scriptJquery("#invite_facebookclientsecret-wrapper").hide();
    }
  }
</script>