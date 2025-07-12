<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: location.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_location')); ?>
<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php $enablelocation = ($this->postParams['enableglocation'] == 1 && empty($this->postParams['core_mapApiKey'])) ? $this->postParams['enablelocation'] : Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', '0'); ?>
<script type="text/javascript">
  scriptJquery( window ).load(function() {
    enablelocation('<?php echo $enablelocation ;?>');
  });
  
  function enablelocation(value) {
    if(value == 1) {
			document.getElementById('core_mapApiKey-wrapper').style.display = 'flex';
			document.getElementById('core_search_type-wrapper').style.display = 'flex';
		} else {
			document.getElementById('core_mapApiKey-wrapper').style.display = 'none';
			document.getElementById('core_search_type-wrapper').style.display = 'none';
    }
  }
</script>
