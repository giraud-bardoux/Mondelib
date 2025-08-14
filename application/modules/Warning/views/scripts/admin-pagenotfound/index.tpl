<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Warning/views/scripts/_adminHeader.tpl';?>

<div class='clear'>
  <div class='settings warning_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="text/javascript">
	en4.core.runonce.add(function() {
    showHide('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('warning.pagenotfoundenable', 1); ?>');
  });
	
	function showHide(value) {
		if(value == 1) {
			scriptJquery('#warning_pagenotfound_pageactivate-wrapper').show();
			scriptJquery('#warning_pagenotfoundphotoID-wrapper').show();
		} else {
			scriptJquery('#warning_pagenotfound_pageactivate-wrapper').hide();
			scriptJquery('#warning_pagenotfoundphotoID-wrapper').hide();
		}
	}
</script>
