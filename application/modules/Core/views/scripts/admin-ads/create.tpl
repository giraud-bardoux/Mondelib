<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_ads', 'childMenuItemName' => 'core_admin_main_ads_create')); ?>

<?php
  $start = time();
  $end = time();
  $oldTz = date_default_timezone_get();
  date_default_timezone_set($this->viewer()->timezone);
  $start_date = date('m/d/Y',$start);
  $end_date = date('m/d/Y',strtotime('+1 Days' ,$end));
  date_default_timezone_set($oldTz);
?>
<script type="text/javascript">
en4.core.runonce.add(function(){
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
    endtime_element.style.display = "flex";
    return;
  }
}
en4.core.runonce.add(updateTextFields);
</script>
<h2 class="page_heading">
  <?php echo $this->translate("Ads") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class='create_ad settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_ads').addClass('active');
</script>
