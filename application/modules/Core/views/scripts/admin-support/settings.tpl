<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: settings.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage_tickets", 'childMenuItemName' => 'core_admin_manage_settings')); ?>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">
  scriptJquery( window ).load(function() {
    enablesupport('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.enablesupport', 1) ;?>');
  });
  
  function enablesupport(value) {
    if(value == 1) {
			document.getElementById('core_supportcreate-wrapper').style.display = 'flex';
		} else {
			document.getElementById('core_supportcreate-wrapper').style.display = 'none';
    }
  }
</script>
