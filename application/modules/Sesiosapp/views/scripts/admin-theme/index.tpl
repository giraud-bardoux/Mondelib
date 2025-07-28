<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core');
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js'); ?>

<script>
hashSign = '#';
isColorFieldRequired = false;
</script>
<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<h2>
  <?php echo $this->translate("Native iOS Mobile App") ?>
</h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class='clear'>
  <div class='settings sesiosapp_themes_form' style="position:relative;">
    <?php echo $this->form->render($this); ?>
    <div class="sesiosapp_loading_cont_overlay" style="display:none"></div>
  </div>
</div>
<script>
  en4.core.runonce.add(function(){
    changeThemeColor(scriptJquery("input[name='theme_color']:checked").val());  
  })
  AttachEventListerSE('click','.seschangeThemeName',function(e){
    e.preventDefault();
     var id = scriptJquery('#custom_theme_color').val();
     var href = scriptJquery(this).attr('href')+'/customtheme_id/'+id;
     Smoothbox.open(href);
      parent.Smoothbox.close;
      return false;
  });
  AttachEventListerSE('click','#delete_custom_themes',function(e){
    e.preventDefault();
     var id = scriptJquery('#custom_theme_color').val();
     var href = scriptJquery(this).attr('href')+'/customtheme_id/'+id;
     Smoothbox.open(href);
      parent.Smoothbox.close;
      return false;
  })
  
  function changeCustomThemeColor(value) {
      changeThemeColor(scriptJquery("input[name='theme_color']:checked").val());
      if(scriptJquery("input[name='theme_color']:checked").val() == 5)
        scriptJquery('.sesiosapp_loading_cont_overlay').show();
      var URL = en4.core.staticBaseUrl+'sesiosapp/admin-theme/getcustomthemecolors/';
      (scriptJquery.ajax({
          method: 'post',
          'url': URL ,
          'data': {
            format: 'html',
            customtheme_id: value,
          },
          success : function(responseHTML) {
          var customthevalyearray = scriptJquery.parseJSON(responseHTML);
          
          for(i=0;i<customthevalyearray.length;i++){
            var splitValue = customthevalyearray[i].split('||');
            scriptJquery('#'+splitValue[0]).val(splitValue[1]);
            if(scriptJquery('#'+splitValue[0]).hasClass('SEcolor')){
              if(splitValue[1] == ""){
                splitValue[1] = "#FFFFFF";  
              }
             try{
              document.getElementById(splitValue[0]).color.fromString('#'+splitValue[1]);
             }catch(err) {
               document.getElementById(splitValue[0]).value = "#FFFFFF";
             }
            }
          }
          //generate string
          scriptJquery('.sesiosapp_loading_cont_overlay').hide();
          /*var index = 0;
          var string = "";
          scriptJquery('.global_form input').each(function(){
            if(index > 6)
            {
              var value = scriptJquery(this).val();
              string = string + "'"+value+"',";
              //console.log(scriptJquery(this).val(),index);
            }
            index++;
          });
          console.log(string);*/
        }
      })).send();
  }
	function changeThemeColor(value) {
    var customthemeValue = scriptJquery('#custom_theme_color').val();
    if(customthemeValue > 6){
      scriptJquery('#edit_custom_themes').show();
      scriptJquery('#delete_custom_themes').show();  
    }else{
      scriptJquery('#edit_custom_themes').hide();
      scriptJquery('#delete_custom_themes').hide();    
    }
     if(value != 5){
      scriptJquery('.sesiosapp_bundle').prev().hide();
      scriptJquery('.sesiosapp_bundle').hide();
      scriptJquery('#custom_theme_color-wrapper, .sesiosapp_styling_buttons').hide();
      scriptJquery('#submit').css('display','none');
     }else{
      scriptJquery('.sesiosapp_bundle').prev().show();
      scriptJquery('.sesiosapp_bundle').show();
      scriptJquery('#custom_theme_color-wrapper, .sesiosapp_styling_buttons').show();
      scriptJquery('#submit').css('display','inline-block');  
     }    
  }
  
</script>
