<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: managemetakeywords.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_seo_managemetakeywords')); ?>

<h2 class="page_heading"><?php echo $this->translate('SEO Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<p class="mb-2"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'core', 'controller' => 'seo', 'action' => 'managemetakeywords'), $this->translate("Back to Meta Tags Settings"), array('class' => 'icon_back buttonlink')) ?></p>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_seo').addClass('active');
</script>
