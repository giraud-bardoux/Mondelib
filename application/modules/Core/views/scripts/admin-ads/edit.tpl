<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_ads', 'childMenuItemName' => 'adcampaign_admin_main_edit')); ?>
<?php
  $start = time();
  $end = time();
  $oldTz = date_default_timezone_get();
  date_default_timezone_set($this->viewer()->timezone);
  $start_date = date('m/d/Y',strtotime($this->campaign->start_time));
  $end_date = date('m/d/Y',strtotime($this->campaign->end_time));
  date_default_timezone_set($oldTz);
?>
<script type="text/javascript">
en4.core.runonce.add(function() {
  var sesselectedDate = '<?php echo $start_date;  ?>'; 
  scriptJquery(`<button type="button" class="event_calendar"></button>`).insertBefore(scriptJquery('#start_time-date').attr("type","text").attr("autocomplete","off").datepicker({
    minDate: '<?php echo $start_date;  ?>',
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
   }).on('change', function(ev){
    sesselectedDate = scriptJquery('#starttime-date').val();
    scriptJquery('#end_time-date').datepicker('option', 'minDate', scriptJquery('#start_time-date').val());  
  }));
  
  scriptJquery(`<button type="button" class="event_calendar"></button>`).insertBefore(scriptJquery('#end_time-date').attr("type","text").attr("autocomplete","off").datepicker({
    minDate: sesselectedDate,
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
  }));
});


var updateTextFields = function(endsettings)
{
  var endtime_element = document.getElementById("end_time-wrapper");
  endtime_element.style.display = "none";

  if (endsettings.value == 0)
  {
    endtime_element.style.display = "none";
    return;
  }

  if (endsettings.value == 1)
  {
    endtime_element.style.display = "block";
    return;
  }
}

<?php if($this->campaign->end_settings == 0):?>
  en4.core.runonce.add(updateTextFields);
<?php endif;?>
</script>
<h2 class="page_heading"><?php echo $this->translate("Editing Ad Campaign: ") ?><?php echo $this->campaign->name;?></h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_ads').addClass('active');
</script>
