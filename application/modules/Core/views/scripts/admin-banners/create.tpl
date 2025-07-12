<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: create.tpl 9747 2012-07-26 02:08:08Z john $
* @author     John
*/
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_layout", 'parentMenuItemName' => 'core_admin_main_layout_banners', 'lastMenuItemName' => 'Create New Banner')); ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_layout').parent().addClass('active');
  scriptJquery('.core_admin_main_layout_banners').addClass('active');
</script>
