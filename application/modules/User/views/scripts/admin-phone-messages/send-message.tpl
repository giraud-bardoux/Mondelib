<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: send-message.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class='global_form_popup'> 
  <?php echo $this->formFilter->render($this) ?> 
</div>

<script type="application/javascript">

  var contentAutocomplete;
  var maxRecipients = 1;
  
  function removeFromToValue(id, elmentValue, element) {
    id = `${id}` 
    var toValues = $(elmentValue).value;
    var toValueArray = toValues.split(",");
    var toValueIndex = "";
    var checkMulti = id.search(/,/);
    // check if we are removing multiple recipients
    if (checkMulti != - 1) {
      var recipientsArray = id.split(",");
      for (var i = 0; i < recipientsArray.length; i++){
        removeToValue(recipientsArray[i], toValueArray, elmentValue);
      }
    } else {
      removeToValue(id, toValueArray, elmentValue);
    }

    // hide the wrapper for element if it is empty
    if ($(elmentValue).value == ""){
      $(elmentValue + '-wrapper').setStyle('height', '0');
      $(elmentValue + '-wrapper').setStyle('display', 'none');
    }
    $(element).disabled = false;
  }

  function removeToValue(id, toValueArray, elmentValue) {
    for (var i = 0; i < toValueArray.length; i++){
      if (toValueArray[i] == id) toValueIndex = i;
    }
    toValueArray.splice(toValueIndex, 1);
    $(elmentValue).value = toValueArray.join();
  }

  en4.core.runonce.add(function() {
    AutocompleterRequestJSON('user', "<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'getusers', 'type' => 'phone'), 'default', true) ?>", function(selecteditem) {
      document.getElementById('user_id').value = selecteditem.id;
    }, ['memberlevel'])
  });

  AttachEventListerSE('change','#type',function(e){
    var value = scriptJquery(this).val();
    if(value == 'memberlevel'){
      scriptJquery('#sendto-wrapper').show();
      scriptJquery('#user-wrapper').hide();
      scriptJquery('#profiletype-wrapper').hide();
      scriptJquery('#memberlevel-wrapper').show();
      scriptJquery('#sendto').trigger('change');
    }else if(value == "profiletype"){
      scriptJquery('#memberlevel-wrapper').hide();
      scriptJquery('#sendto-wrapper').hide();
      scriptJquery('#user-wrapper').hide();
      scriptJquery('#profiletype-wrapper').show();
    }
  });
  
  scriptJquery('#type').trigger('change');
  AttachEventListerSE('change','#sendto',function(e){
    var value = scriptJquery(this).val();
    if(value == 'specific'){
      scriptJquery('#user-wrapper').show();
    } else {
      scriptJquery('#user-wrapper').hide();
    }
  });
</script>
