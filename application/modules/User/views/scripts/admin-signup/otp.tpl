<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: otp.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="settings">
  <?php echo $this->form->render($this); ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_signup').addClass('active');
</script>
