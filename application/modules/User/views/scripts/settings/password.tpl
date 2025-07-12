<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: password.tpl 9869 2013-02-12 22:37:42Z shaun $
 * @author     Steve
 */
?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
      <div class="user_setting_global_form">
        <?php if(!empty($_SESSION['requirepassword'] )){ ?>
          <div class="require_password">
            <?php echo $this->content()->renderWidget('core.menu-logo',array('disableLink'=>true)); ?>
            <?php echo $this->form->render($this) ?>
          </div>
        <?php }else{ ?>
        <?php echo $this->form->render($this) ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#password-element').append('<div id="passwordroutine" class="password_checker"><div id="passwordroutine_length"></div><div class="d-flex justify-content-between align-content-center"><div id="passwordroutine_text" class="font_small"><?php echo $this->translate("Enter your password.")?></div><div id="password-hint"><i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $this->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.'); ?>" data-bs-original-title="" title=""></i></div></div></div>');

  });
</script>
