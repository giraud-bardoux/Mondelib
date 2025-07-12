<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: clone.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_layout", 'parentMenuItemName' => 'core_admin_main_layout_themes', 'lastMenuItemName' => 'Theme Manager')); ?>

<div class="settings">
<?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_layout').parent().addClass('active');
  scriptJquery('.core_admin_main_layout_themes').addClass('active');
</script>
