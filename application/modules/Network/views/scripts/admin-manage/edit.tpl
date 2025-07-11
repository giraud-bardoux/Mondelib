<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_networks', 'lastMenuItemName' => 'Create Network')); ?>
<?php echo $this->partial('_formAdminJs.tpl', array('form' => $this->form)) ?>

<div class="settings network_form">
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_networks').addClass('active');
</script>
