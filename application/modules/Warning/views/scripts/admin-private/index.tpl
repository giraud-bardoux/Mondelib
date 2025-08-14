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
		scriptJquery('input[type=radio][name=warning_privateenable]:checked').trigger('change');
  });

	AttachEventListerSE('change','input[type=radio][name=warning_privateenable]',function() {
		if(this.value == 1) {
			scriptJquery('#warning_private_pageactivate-wrapper').show();
			scriptJquery('#warning_privatepagephotoID-wrapper').show();
		} else {
			scriptJquery('#warning_private_pageactivate-wrapper').hide();
			scriptJquery('#warning_privatepagephotoID-wrapper').hide();
		}
	});
</script>
