<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2017-01-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_iframely')); ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
//<![CDATA[
  function updateFields() {
    var checkedValue = scriptJquery('input[name=host]:checked').val();
    scriptJquery('#secretIframelyKey-wrapper').hide();
    scriptJquery('#baseUrl-wrapper').hide();
    if('<?php echo Engine_Iframely::IFRAMELY_HOST ?>' == checkedValue ) {
      scriptJquery('#secretIframelyKey-wrapper').show();
    }
    if ('<?php echo Engine_Iframely::OWN_HOST ?>' == checkedValue ) {
      scriptJquery('#baseUrl-wrapper').show();
    }
  }
  updateFields();
//]]>
</script>
