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
<div class='tabs'>
  <ul class="navigation">
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'comingsoon', 'action' => 'index'), $this->translate('Settings')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'visitors', 'action' => 'index'), $this->translate('Manage Visitors')) ?>
    </li>
  </ul>
</div>
<div class='clear'>
  <div class='settings warning_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php 
$start = time();
$start_date = date('m/d/Y',$start);
?>
<script type="text/javascript">
  scriptJquery(``).insertBefore(scriptJquery('#start_time-date').attr("type","text").attr("autocomplete","off").attr("placeholder","Select a Date").datepicker({
    minDate: '<?php echo $start_date;  ?>',
    timepicker: true,
  }));
  
  en4.core.runonce.add(function() {
		scriptJquery('input[type=radio][name=warning_comingsoonenable]:checked').trigger('change');
	});
	
	AttachEventListerSE('change','input[type=radio][name=warning_comingsoonenable]',function(){
		if(this.value == 1) {
			scriptJquery('#start_time-wrapper').show();
			scriptJquery('#warning_comingsooncontactenable-wrapper').show();
			scriptJquery('#warning_comingsoonlogo-wrapper').show();
			scriptJquery('#warning_comingsoonenablesocial-wrapper').show();
			scriptJquery('#warning_comingsoon_pageactivate-wrapper').show();
			scriptJquery('#warning_comingsoonphotoID-wrapper').show();
		} else {
			scriptJquery('#start_time-wrapper').hide();
			scriptJquery('#warning_comingsooncontactenable-wrapper').hide();
			scriptJquery('#warning_comingsoonlogo-wrapper').hide();
			scriptJquery('#warning_comingsoonenablesocial-wrapper').hide();
			scriptJquery('#warning_comingsoon_pageactivate-wrapper').hide();
			scriptJquery('#warning_comingsoonphotoID-wrapper').hide();
		}
	});
</script>
