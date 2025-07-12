<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: socialshare.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_socialmenus', 'childMenuItemName' => 'core_admin_main_sharesettings')); ?>

<h2 class="page_heading"><?php echo $this->translate('Social Menus') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class='settings'>
  <?php echo $this->form->render($this) ?>
</div>
<script>
  en4.core.runonce.add(function() {
    showHide(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.socialshare.enable', 1); ?>);
  });
  
  function showHide(value) {
    if(value == 1) { 
      scriptJquery("#core_socialashare_allow-wrapper").show();
    } else {
      scriptJquery("#core_socialashare_allow-wrapper").hide();
    }
  }

  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_socialmenus').addClass('active');
</script>
