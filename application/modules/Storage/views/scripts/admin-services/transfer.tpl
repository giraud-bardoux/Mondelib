

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_settings_storage', 'lastMenuItemName' => 'Transfer Files')); ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_storage').addClass('active');
</script>
