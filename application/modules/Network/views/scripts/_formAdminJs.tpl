<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _formAdminJs.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 * @author     John
 */
?>

<script type="text/javascript">

  en4.core.runonce.add(function() {
    // Attach assignment stuff
    scriptJquery('.form-elements input[name=assignment]').on('change', function(event) {
      var ele = scriptJquery(this);
      if( !ele.prop("checked")) return;
      if( ele.val() == '1' ) {
        scriptJquery('#field_id-wrapper').css('display', '');
      } else {
        scriptJquery('#field_id-wrapper').css('display', 'none');
        scriptJquery('.network_field_container').css('display', 'none');
        scriptJquery('#field_id').val('');
      }
    }).trigger('change');

    // Attach field switching stuff
    scriptJquery('#field_id').on('change', function(event) {
      var field_id = scriptJquery(this).val();
      var field_el = scriptJquery('#field_pattern_' + field_id + '-wrapper');
      if( !field_el ) return;
      scriptJquery('.network_field_container').css('display', 'none');
      field_el.css('display', '');
    }).trigger('change');
  });


<?php /*
  var lastDiv = <?php echo ( $this->form->field_id->getValue() ? $this->form->field_id->getValue() : 'null' ) ?>;
  
  var updateshown = function()
  {
    if( lastDiv != null)
    {
      var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
      var display_element = document.getElementById(pattern_name).style.display='none';
    }
    lastDiv = document.getElementById('admin-form').field_id.value;
    var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
    document.getElementById(pattern_name).style.display = 'block';
  }


  var updateassign = function()
  {
    form = document.getElementById('admin-form');
    var assignment_list = form.elements['assignment'];
    var pattern_name = "field_pattern_" + lastDiv + "_group-wrapper"; 
    var pattern_display_element = document.getElementById(pattern_name).style.display;
    var field_id_element = document.getElementById('field_id-wrapper');
   if (assignment_list[1].checked)
    { 
        document.getElementById(pattern_name).style.display = 'block';
  field_id_element.style.display = 'block';
    } 
    else 
    {
      document.getElementById(pattern_name).style.display = 'none';      
  field_id_element.style.display = 'none';

    }
  }

  window.onload = function()
  {
    updateshown();
    updateassign();
  }
  */ ?>

</script>
