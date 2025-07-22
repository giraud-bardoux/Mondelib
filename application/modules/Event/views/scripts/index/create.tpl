<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'event')); ?>

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
<?php if( $this->parent_type == 'group' ) { ?>
  <h2>
    <?php echo $this->group->__toString() ?>
    <?php echo '&#187; '.$this->translate('Events');?>
  </h2>
<?php } ?>
<div class="global_form_wrap">
  <?php echo $this->form->render() ?>
</div>
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
  var sesselectedDate = '<?php echo $start_date;  ?>'; 
  scriptJquery('#starttime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: '<?php echo $start_date;  ?>',
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
   }).on('change', function(ev){
      sesselectedDate = scriptJquery('#starttime-date').val();
      scriptJquery('#endtime-date').datepicker('option', 'minDate', scriptJquery('#starttime-date').val());
  });
  
  scriptJquery('#endtime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: sesselectedDate,
    changeMonth: true,
    changeYear: true,
    yearRange: "+0:+100",
  });

  en4.core.runonce.add(function() {
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
      isOnline(0);
    <?php } else { ?>
      isOnline(1);
    <?php } ?>
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
  
  scriptJquery('.core_main_event').parent().addClass('active');
</script>
