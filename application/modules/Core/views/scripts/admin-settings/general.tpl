<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: general.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_general')); ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script>
	en4.core.runonce.add(function() {
    loginLogs("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.enableloginlogs', '1'); ?>");
    hideShowMaintenance("<?php echo $this->maintenanceMode; ?>");
  });

	function loginLogs(value) {
		if(value == 1) {
			scriptJquery('#logincrondays-wrapper').show();
		} else {
			scriptJquery('#logincrondays-wrapper').hide();
		}
	}
	
	function hideShowMaintenance(value) {
		if(value == 1) {
			scriptJquery('#maintenance_code-wrapper').show();
		} else {
			scriptJquery('#maintenance_code-wrapper').hide();
		}
	}
</script>
