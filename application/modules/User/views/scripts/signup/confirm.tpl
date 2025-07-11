<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: confirm.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<div class="generic_layout_container layout_main">
  <div class="generic_layout_container layout_middle">
    <div class="generic_layout_container layout_core_content text-center">
      <div class="auth_page_icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="800px" height="800px" viewBox="-8 0 512 512"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm80 168c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm-160 0c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm194.8 170.2C334.3 380.4 292.5 400 248 400s-86.3-19.6-114.8-53.8c-13.6-16.3 11-36.7 24.6-20.5 22.4 26.9 55.2 42.2 90.2 42.2s67.8-15.4 90.2-42.2c13.4-16.2 38.1 4.2 24.6 20.5z"/></svg>
      </div>
      <h3 class="mb-2">
        <?php echo $this->translate("Thanks for joining!") ?>
      </h3>
      <p class="text-center mb-3">
        <?php
        if( !$this->verified || !$this->approved ) {
          echo $this->translate("Welcome! Once we have approved your account, you will be able to sign in.");
        }
        ?>
      </p>
        <?php if(!empty($_GET['restApi'])) { ?>
          <a class="btn btn-primary" href="<?php echo $this->url(array(), 'default', true) ?>/finish/state/active"><?php echo $this->translate("Ok, thanks!") ?></a>
        <?php } else { ?>
          <a class="btn btn-primary" href="<?php echo $this->url(array(), 'default', true) ?>"><?php echo $this->translate("Ok, thanks!") ?></a>
        <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery("body").addClass('authpage')
  });
</script>
