<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _composetargetpost.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php 
  $settings = Engine_Api::_()->getApi('settings', 'core');
?>
<script type="text/javascript">
  
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.Activitytargetpost({
      status:'<?php echo @$status ?>',
      lang : {
        'Choose Preferred Audience' : '<?php echo $this->translate("Choose Preferred Audience"); ?>'
      }
    }));
  });

  function saveTargetPostValues(){
    if(scriptJquery('#ajaxsmoothbox_main').length){
      var editElem = '_edit';
      var editElemIn = '-edit';
    }
    else{
      var editElem = '';
      var editElemIn = '';
    }
    scriptJquery('#compose-targetpost'+editElemIn+'-form-input').prop('checked', true);
    scriptJquery('.composer_targetpost'+editElem+'_toggle').addClass('composer_targetpost'+editElem+'_toggle_active');  
  }
var openTargetPostPopup = function(){
  if(!scriptJquery('#location_send').length)
  scriptJquery('.composer_targetpost_toggle').append('<input type="hidden" id="country_name"  name="targetpost[country_name]" value=""><input type="hidden" id="city_name"  name="targetpost[city_name]" value=""><input type="hidden" id="location_send"  name="targetpost[location_send]" value=""><input type="hidden" id="location_city" name="targetpost[location_city]" value=""><input type="hidden" id="location_country"  name="targetpost[location_country]"value=""><input type="hidden" id="gender_send" name="targetpost[gender_send]" value=""><input type="hidden" id="age_min_send" name="targetpost[age_min_send]" value=""><input type="hidden" id="age_max_send" name="targetpost[age_max_send]" value=""><input type="hidden" id="targetpostlat" name="targetpost[targetpostlat]" value=""><input type="hidden" id="targetpostlng" name="targetpost[targetpostlng]" value=""><input type="hidden" id="targetpostlatcity" name="targetpost[targetpostlatcity]" value=""><input type="hidden" id="targetpostlngcity" name="targetpost[targetpostlngcity]" value="">');
  
	<?php 
  	$optionHTML = '';
  	for($i=14;$i<99;$i++){ 
  			$optionHTML = $optionHTML.'<option value="'.$i.'">'.$i.'</option>';		
  	} 
  ?>

	var htmlOptions = '<?php echo $optionHTML; ?>';
	msg = "<div class='activity_target_popup  clearfix'><div class='activity_target_post_popup_header'><?php echo $this->translate('Choose Preferred Audience'); ?></div><div class='activity_target_post_popup_cont'><p><?php echo $this->translate('Choose preferred audience for your post.'); ?></p>";
  var memberenable = '<?php echo $settings->getSetting('enableglocation', 0); ?>';
	
  if(memberenable == 1) {
    msg += "<div class='activity_target_popup_field clearfix'><div class='activity_target_popup_field_label'><?php echo $this->translate('Location');?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='Choose to share your post with members in World or specific location.'></i></div><div class='activity_target_popup_field_element'><span><input type='radio' checked='checked' class='selected_coun_val' name='country_type_sel' value='world'> <?php echo $this->translate('World');?></span><span><input type='radio' name='country_type_sel' class='selected_coun_val' value='country'> <?php echo $this->translate('Country');?></span><span><input class='selected_coun_val' type='radio' name='country_type_sel' value='city'> <?php echo $this->translate('By City');?></span><div class='activity_target_popup_field_input'><input type='text' name='country_sel' id='country_sel' placeholder='Select Country' style='display:none;'><input type='text' name='city_sel' id='city_sel' placeholder='Select City' style='display:none;'><p class='activity_target_popup_error' style='display:none;' id='location_error_sel'><?php echo $this->translate('Please select value.');?></p></div></div></div>";
  }
  
  msg += "<div class='activity_target_popup_field clearfix'>"+"<div class='activity_target_popup_field_label'><?php echo $this->translate('Gender');?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='<?php echo ("Choose to share your post with &quot;All&quot; or specific gender.");?>'></i></div><div class='activity_target_popup_field_element'><span><input type='radio' checked='checked'  name='gender_type_sel' value='all'> <?php echo $this->translate('All');?></span><span><input type='radio' name='gender_type_sel'  value='male'><?php echo $this->translate('Men');?></span><span><input type='radio' name='gender_type_sel' value='women'> <?php echo $this->translate('Women');?></span></div></div>";

  msg += "<div class='activity_target_popup_field'><div class='activity_target_popup_field_label'><?php echo $this->translate('Age');?> <i class='activity_tooltip fa fa-info-circle font_color_light' title='<?php echo $this->translate("Choose minimum and maximum age of the members with whom you want to share your post.");?>'></i></div><div class='activity_target_popup_field_element activity_target_age_main'><span><select name='age_sel_min' id='age_sel_min'><option value='13'>13</option>"+htmlOptions+"</select><span class='separator'>-</span><select name='age_sel_max' id='age_sel_max'>"+htmlOptions+"<option value='99'>99+</option></select><p class='activity_target_popup_error' style='display:none;' id='age_error_sel'><?php echo $this->translate('Age max field is greater than Age min field.');?></p></div></div>";

  msg += "</div><div class='activity_target_post_popup_btm'><button href=\"javascript:void(0);\" class='savevaluessel notclose'><?php echo $this->translate("Save"); ?></button><button href=\"javascript:void(0);\" class='removevaluessel notclose' style='display:none;'><?php echo $this->translate("Remove"); ?></button><button href=\"javascript:void(0);\" onclick=\"javascript:parent.Smoothbox.close()\" class='secondary notclose'><?php echo $this->translate("Close"); ?></button></div></div>";
	Smoothbox.open(msg);
	//change values
	var location_send = scriptJquery('#location_send');
	var location_city = scriptJquery('#location_city');
	var location_country = scriptJquery('#location_country');
	var gender_send = scriptJquery('#gender_send');
	var age_min_send = scriptJquery('#age_min_send');
	var age_max_send = scriptJquery('#age_max_send');
	if(location_send.val()  == 'country'){
		scriptJquery('#country_sel').show();
		scriptJquery('#city_sel').hide();
	}else if(location_send.val() == 'city'){
		scriptJquery('#country_sel').hide();
		scriptJquery('#city_sel').show();
	}else{
		scriptJquery('#country_sel').hide();
		scriptJquery('#city_sel').hide();
	}
	scriptJquery('input:radio[name="country_type_sel"][value="'+location_send.val()+'"]').attr('checked',true);
	scriptJquery('#country_sel').val(location_country.val());
	scriptJquery('#city_sel').val(location_city.val());
	scriptJquery('input:radio[name="gender_type_sel"][value="'+gender_send.val()+'"]').attr('checked',true);
	scriptJquery('#age_sel_min').val(age_min_send.val());
	scriptJquery('#age_sel_max').val(age_max_send.val());
  if(scriptJquery('#compose-targetpost-form-input').is(':checked'))
    scriptJquery('.removevaluessel').show();
	scriptJquery('#TB_ajaxContent').addClass('activity_target_post_popup_wrapper ');
	activitytooltip();
  if(memberenable)
    makeGoogleMapSelect();
}
function makeGoogleMapSelect(){
  if(scriptJquery('#ajaxsmoothbox_main').length)
    var editElem = '_edit';
  else
    var editElem = '';
  var input = document.getElementById('country_sel'+editElem);
  if(!isGoogleKeyEnabled) return;
  if(typeof input != 'undefined') {
    autocompleteCountry = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocompleteCountry, 'place_changed', function () {
      var place = autocompleteCountry.getPlace();
      if (!place.geometry)
        return;
      scriptJquery('#targetpostlat'+editElem).val(place.geometry.location.lat());
      scriptJquery('#targetpostlng'+editElem).val(place.geometry.location.lng());
      var geocoder = new google.maps.Geocoder(); 
      var country = '';
      geocoder.geocode({'latLng': new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng())}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK && results.length) {
        if (results[0]) {
          for(var i=0; i<results[0].address_components.length; i++)
          {
            if(results[0].address_components[i].types[0] == 'postal_code') {
            	var postalCode = results[0].address_components[i].long_name;
            }
          }
        }
        if (results[1]) {
          var indice=0;
          for (var j=0; j<results.length; j++)
          {
              if (results[j].types[0]=='locality')
              {
                  indice=j;
                  break;
              }
          }
          if(typeof results[j] != 'undefined'){
            for (var i=0; i<results[j].address_components.length; i++)
            {
            if (results[j].address_components[i].types[0] == "country") {
              //this is the object you are looking for
              country = results[j].address_components[i].long_name;
             }
            }
          }
        }
        scriptJquery('#location_error_sel'+editElem).hide();
        if(!country){
          scriptJquery('#location_error_sel'+editElem).show().html('Country name not fetch for given location. Write Country name manually in text box and save.');
          scriptJquery('#country_name'+editElem).val('');
          return;
        }
        scriptJquery('#country_name'+editElem).val(country);
      } 
     });	
    });
  }
  var input = document.getElementById('city_sel'+editElem);
  if(typeof input != 'undefined') {
    var opts = {
      types: ['(cities)']
    };
    autocompleteCity = new google.maps.places.Autocomplete(input,opts);
    google.maps.event.addListener(autocompleteCity, 'place_changed', function () {
      var place = autocompleteCity.getPlace();
      if (!place.geometry)
        return;
      scriptJquery('#targetpostlatcity'+editElem).val(place.geometry.location.lat());
      scriptJquery('#targetpostlngcity'+editElem).val(place.geometry.location.lng());
      var geocoder = new google.maps.Geocoder(); 
      var city = '';
      geocoder.geocode({'latLng': new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng())}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK && results.length) {
          if (results[0]) {
            for(var i=0; i<results[0].address_components.length; i++)
            {
              if(results[0].address_components[i].types[0] == 'postal_code') {
              	var postalCode = results[0].address_components[i].long_name;
              }
            }
          }
          if (results[1]) {
            var indice=0;
            for (var j=0; j<results.length; j++)
            {
                if (results[j].types[0]=='locality')
                {
                    indice=j;
                    break;
                }
            }
            if(typeof results[j] != 'undefined'){
              for (var i=0; i<results[j].address_components.length; i++)
              {
                if (results[j].address_components[i].types[0] == "locality") {
                    //this is the object you are looking for
                    city = results[j].address_components[i].long_name;
                }
              }
            }
          }
          scriptJquery('#location_error_sel'+editElem).hide();
          if(!city){
            scriptJquery('#location_error_sel'+editElem).show().html('City name not fetch for given location.Write City name manually in text box and save.');
          }
          scriptJquery('#city_name'+editElem).val(city);
        } 
      });	
    });
  }
}
AttachEventListerSE('click','.savevaluessel',function(e){
  if(scriptJquery('#ajaxsmoothbox_main').length)
    var editElem = '_edit';
  else
    var editElem = '';
	var agemin = scriptJquery('#age_sel_min'+editElem).val();
	var agemax = scriptJquery('#age_sel_max'+editElem).val();
	var error = false;
  if(!agemin && !agemax)
   scriptJquery('#age_error_sel'+editElem).hide();	
	else if(agemin >= agemax){
			scriptJquery('#age_error_sel'+editElem).show();
			error = true
	}
 if(scriptJquery('input[name=country_type_sel'+editElem+']:checked').val() == 'city'){
    if(scriptJquery('#city_sel'+editElem).val() == ''){
      scriptJquery('#location_error_sel'+editElem).show().html('Please select city name.');;
      error = true
    }else{
      scriptJquery('#location_error_sel'+editElem).hide();
    }
	}else if(scriptJquery('input[name=country_type_sel'+editElem+']:checked').val() == 'country'){
		if(scriptJquery('#country_sel'+editElem).val() == ''){
				scriptJquery('#location_error_sel'+editElem).show().html('Please select country name.');;
				error = true
			}else{
				scriptJquery('#location_error_sel'+editElem).hide();
			}
	}else{
			scriptJquery('#location_error_sel'+editElem).hide();	
	}
	if(error){
		return false;
	}
	//change values
	var location_send = scriptJquery('#location_send'+editElem);
	var location_city = scriptJquery('#location_city'+editElem);
	var location_country = scriptJquery('#location_country'+editElem);
	var gender_send = scriptJquery('#gender_send'+editElem);
	var age_min_send = scriptJquery('#age_min_send'+editElem);
	var age_max_send = scriptJquery('#age_max_send'+editElem);
	location_send.val(scriptJquery('input[name=country_type_sel'+editElem+']:checked').val());
	location_city.val(scriptJquery('#city_sel'+editElem).val());
	location_country.val(scriptJquery('#country_sel'+editElem).val());
	gender_send.val(scriptJquery('input[name=gender_type_sel'+editElem+']:checked').val());
	age_min_send.val(scriptJquery('#age_sel_min'+editElem).val());
	age_max_send.val(scriptJquery('#age_sel_max'+editElem).val());
  if(!scriptJquery('#country_name'+editElem).val())
    scriptJquery('#country_name'+editElem).val(scriptJquery('#country_sel'+editElem).val());
	scriptJquery('#TB_ajaxContent').find('input').removeAttr('checked'); 
	scriptJquery('#TB_ajaxContent').find('input').val('');
  
  saveTargetPostValues();
	parent.Smoothbox.close();			
});
function removeTargetPostValues(){
  if(scriptJquery('#ajaxsmoothbox_main').length){
    var editElem = '_edit';
    var editElemEdit = '-edit';
  }else{
    var editElem = '';
    var editElemEdit = '';
  }
  if(document.getElementById('compose-targetpost'+editElemEdit+'-form-input'))
    scriptJquery('#compose-targetpost'+editElemEdit+'-form-input').prop('checked', false);
  scriptJquery('.composer_targetpost'+editElem+'_toggle').removeClass('composer_targetpost'+editElem+'_toggle_active'); 
  //change values
	var location_send = scriptJquery('#location_send'+editElem);
	var location_city = scriptJquery('#location_city'+editElem);
	var location_country = scriptJquery('#location_country'+editElem);
	var gender_send = scriptJquery('#gender_send'+editElem);
	var age_min_send = scriptJquery('#age_min_send'+editElem);
	var age_max_send = scriptJquery('#age_max_send'+editElem);
	location_send.val('');
	location_city.val('');
	location_country.val('');
	gender_send.val('');
	age_min_send.val('');
	age_max_send.val('');
	scriptJquery('#TB_ajaxContent').find('input').removeAttr('checked'); 
	scriptJquery('#TB_ajaxContent').find('input').val('');
  parent.Smoothbox.close();
  
}
AttachEventListerSE('click','.removevaluessel',function(e){
  removeTargetPostValues();  
});
AttachEventListerSE('click','.selected_coun_val',function(e){
  if(scriptJquery('#ajaxsmoothbox_main').length)
    var editElem = '_edit';
  else
    var editElem = '';
	var id = scriptJquery(this).val();
	if(id  == 'country'){
		scriptJquery('#country_sel'+editElem).show();
		scriptJquery('#city_sel'+editElem).hide();
	}else if(id == 'city'){
		scriptJquery('#country_sel'+editElem).hide();
		scriptJquery('#city_sel'+editElem).show();
	}else{
		scriptJquery('#country_sel'+editElem).hide();
		scriptJquery('#city_sel'+editElem).hide();
	}
})
</script>
