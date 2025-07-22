<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('item' => $this->event, 'modulename' => 'event')); ?>

<?php
  $start = time();
  $end = time();
  $oldTz = date_default_timezone_get();
  date_default_timezone_set($this->viewer()->timezone);
  $start_date = date('m/d/Y',strtotime($this->event->starttime));
  $end_date = date('m/d/Y',strtotime($this->event->endtime));
  date_default_timezone_set($oldTz);
?>
<script type="text/javascript">
  var modulename = 'event';
  var category_id = '<?php echo $this->category_id; ?>';
  var subcat_id = '<?php echo $this->subcat_id; ?>';
  var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

  en4.core.runonce.add(function() {
    if(category_id && category_id != 0) {
      showSubCategory(category_id, subcat_id);
    } else {
      if(scriptJquery('#category_id').val()) {
        showSubCategory(scriptJquery('#category_id').val());
      } else {
        if(document.getElementById('subcat_id-wrapper'))
          document.getElementById('subcat_id-wrapper').style.display = "none";
      }
    }

    if(subsubcat_id) {
      if(subcat_id && subcat_id != 0) {
        showSubSubCategory(subcat_id, subsubcat_id);
      } else {
        if(document.getElementById('subsubcat_id-wrapper'))
          document.getElementById('subsubcat_id-wrapper').style.display = "none";
      }
    } else if(subcat_id) {
      showSubSubCategory(subcat_id);
    }
    else {
      if(document.getElementById('subsubcat_id-wrapper'))
        document.getElementById('subsubcat_id-wrapper').style.display = "none";
    }
  });
</script>
<?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->subject())); ?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <div class="global_form_wrap">
      <?php echo $this->form->render($this) ?>
    </div>
  </div>
</div>

<script type="text/javascript">
  var sesselectedDate = '<?php echo $start_date;  ?>'; 
  scriptJquery('#starttime-date').attr("type","text").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: '<?php echo $start_date;  ?>',
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
   }).on('change', function(ev){
    sesselectedDate = scriptJquery('#starttime-date').val();
    scriptJquery('#endtime-date').datepicker('option', 'minDate', scriptJquery('#starttime-date').val());  
  });
  
  scriptJquery('#endtime-date').attr("type","text").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: sesselectedDate,
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
  });
  
  en4.core.runonce.add(function() {
    isOnline('<?php echo $this->event->is_online; ?>');
  });
  
  function isOnline(value) {
    if(value == 1) {
      scriptJquery('#website-wrapper').show();
      scriptJquery('#location-wrapper').hide();
      scriptJquery('#location').val('');
    } else {
      scriptJquery('#website-wrapper').hide();
      scriptJquery('#location-wrapper').show();
      scriptJquery('#website').val('');
    }
  }
</script>
