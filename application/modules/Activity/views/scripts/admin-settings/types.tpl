<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: types.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_settings_activity', 'childMenuItemName' => 'core_admin_settings_activitytypes')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<script type="text/javascript">
  var url = en4.core.baseUrl+'admin/activity/settings/types/plugin/';
  var fetchActivitySettings = function(type, plugin) {
    window.location.href = url + plugin + '/type/' + type;
  }

  var fetchActivityTypes = function(plugin) {
    window.location.href = url + plugin;
  }
</script>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_activity').addClass('active');
</script>
