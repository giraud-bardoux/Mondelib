<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: twilio.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_otp', 'childMenuItemName' => 'core_admin_otp_integration')); ?>

<h2 class="page_heading"><?php echo $this->translate('OTP Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<p>
	<a href="<?php echo $this->url(array('action'=>'service-integration','module'=>'user','controller'=>'otp'),'admin_default',true); ?>" class="buttonlink icon_back"><?php echo $this->translate('Back to 3rd Party Services Integration'); ?></a>
</p>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script>
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_otp').addClass('active');
</script>
