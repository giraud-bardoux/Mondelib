<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: create.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<script type="text/javascript">
  var isEndDateRequired = '<?php echo $this->enableenddate; ?>';
  var MAX_UPLOAD_SIZE_NAME =  '<?php echo $this->upload_max_size ?>'; 
  var MAX_UPLOAD_SIZE_BYTES =  <?php echo $this->max_file_upload_in_bytes ?>; 
  
  function validFileSize(file) {
    var fileElement = document.getElementById("file");
    var size = fileElement.files[0].size;
    if (size > MAX_UPLOAD_SIZE_BYTES)
    {
      fileElement.value = "";
      alert("File size must under "+MAX_UPLOAD_SIZE_NAME);
      return;
    }
  }

</script>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<style>
  .calendar_output_span{display:none}
  #date-hour, #date-minute, #date-ampm{display:none;}
  #starttime-hour, #starttime-minute, #starttime-ampm{display:none;}
  #endtime-hour, #endtime-minute, #endtime-ampm{display:none;}
</style>
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
  
  function enableenddatse(value){
    if(value == 0){
      scriptJquery("#endtime-wrapper").hide();
    }else{
      scriptJquery("#endtime-wrapper").show();
    }
  }
  en4.core.runonce.add(function() {
    enableenddatse(scriptJquery('input[name="enableenddate"]:checked').val());
  });

  var sesselectedDate = '<?php echo $start_date;  ?>'; 
  scriptJquery('#starttime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: '<?php echo $start_date;  ?>',
   }).on('change', function(ev){
      sesselectedDate = scriptJquery('#starttime-date').val();
      scriptJquery('#endtime-date').datepicker('option', 'minDate', scriptJquery('#starttime-date').val());
  });
  
  scriptJquery('#endtime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('Select a Date'); ?>").datepicker({
    minDate: sesselectedDate,
  });

  scriptJquery("#endtime-hour").val("1");
  scriptJquery("#endtime-minute").val("0"); 
  scriptJquery("#endtime-ampm").val("AM");
  scriptJquery("#starttime-hour").val("1");
  scriptJquery("#starttime-minute").val("0");
  scriptJquery("#starttime-ampm").val("AM");
    
</script>
