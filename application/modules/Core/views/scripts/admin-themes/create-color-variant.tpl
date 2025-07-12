<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: clone.tpl 9747 2017-02-01 02:08:08Z john $
 * @author     Steve
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_layout", 'parentMenuItemName' => 'core_admin_main_layout_themes', 'lastMenuItemName' => 'Color Variants')); ?>
<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>

<script type="text/javascript">
  var fetchColorVariant = function(variantName) {
    var url = en4.core.baseUrl+'admin/themes/create-color-variant/name/';
    window.location.href = url + variantName;
  }

  var showSubmit = function() {
    scriptJquery('#submitWrapper').css('display', 'block');
  }

</script>
