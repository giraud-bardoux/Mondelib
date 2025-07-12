<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: detect-location.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
 
?>
<div class="core_location_popup">
  <div class="core_location_popup_header">
    <?php echo $this->translate("Your Current Location"); ?>
  </div>
  <div class="core_location_popup_content">
    <div class="core_location_popup_field">  
      <input type="text" id="location_data" autocomplete="off" placeholder="<?php echo $this->translate('Location'); ?>" value="<?php echo !empty($this->cookiedata['location']) ? $this->cookiedata['location'] : '' ; ?>"/>
    </div>
    <div class="core_location_popup_field" id="core_remove_location_ctn">
      <input type="checkbox" id="core_remove_location" /><label for="core_remove_location"><?php echo $this->translate("Remove Selected Location"); ?></label>
    </div>
    <input type="hidden" id="location_lat" value="<?php echo !empty($this->cookiedata['lat']) ? $this->cookiedata['lat'] : '' ; ?>" />
    <input type="hidden" id="location_lng"  value="<?php echo !empty($this->cookiedata['lng']) ? $this->cookiedata['lng'] : '' ; ?>"/>
  </div>
  <div class="core_location_popup_footer">
    <button id="saveLocationData" onclick=""><?php echo $this->translate("Save"); ?></button>
    <button id="cancelLocationData" onclick="javascript:ajaxsmoothboxclose();" class="secondary_button" name="cancel"><?php echo $this->translate("Cancel"); ?></button>
  </div>
</div>

<script>
  en4.core.runonce.add(function() {
    coreCookieChangedLocation();
    var htmlF = scriptJquery('#location_data_f').html();
    if(!htmlF){
      scriptJquery('#core_remove_location_ctn').hide();
    }else{
      scriptJquery('#core_remove_location_ctn').show();
    }
//   scriptJquery('.location_data_core').click(function(){
//       scriptJquery('#core_location_popup').show();
// 
//   });
    scriptJquery('#saveLocationData').click(function(e){
      //get remove location value
      var removeLocation = scriptJquery('#core_remove_location').is(':checked');
      if(removeLocation){
        scriptJquery('#location_data').val('');
        scriptJquery('#location_lat').val('');
        scriptJquery('#location_lng').val('');
      }
      //set location data in cookie
      var location = scriptJquery('#location_data').val();
      var lat = scriptJquery('#location_lat').val();
      var lng = scriptJquery('#location_lng').val();
      scriptJquery('#location_data').css('border','');
      scriptJquery("#core_remove_location").prop('checked', false); 
      if(lat && lng && location){
        setCookie('location_data',location,30);
        //set lat in cookie
        setCookie('location_lat',lat,30);
        //set lng in cookie		
        setCookie('location_lng',lng,30);
        scriptJquery('#location_data_f').show();
        loadAjaxContentApp(window.proxyLocation.href);
        scriptJquery('#location_data_e').hide();
        scriptJquery('#core_location_popup').hide();
      }else{
        setCookie('location_data',location,30,'Thu, 01 Jan 1970 00:00:01 GMT');
        //set lat in cookie
        setCookie('location_lat',lat,30,'Thu, 01 Jan 1970 00:00:01 GMT');
        //set lng in cookie		
        setCookie('location_lng',lng,30,'Thu, 01 Jan 1970 00:00:01 GMT');
        scriptJquery('#location_data_f').hide();
        loadAjaxContentApp(window.proxyLocation.href);
        scriptJquery('#location_data_e').show();
        scriptJquery('#core_location_popup').hide();
      }
      if(scriptJquery('#locationCoreList').length){
        scriptJquery('#locationCoreList').val(location);	
        scriptJquery('#latCoreList').val(lat);	
        scriptJquery('#lngCoreList').val(lng);	
      }
      scriptJquery('#location_data_f').html(location);
      if(!scriptJquery('#locationCoreList').length)
        return false;
    });
    scriptJquery('#cancelLocationData').click(function(){
        scriptJquery('#core_location_popup').hide();
        var htmlF = scriptJquery('#location_data_f').html();
        if(!htmlF){
          scriptJquery('#location_data_e').show();
          scriptJquery('#location_data_f').hide();
        }else{
          scriptJquery('#location_data_e').hide();
          scriptJquery('#location_data_f').show();
        }
        scriptJquery('#location_data').val(htmlF);
          scriptJquery("#core_remove_location").prop('checked', false);
    });
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toGMTString();
        document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/"; 
    } 
  });
  function coreCookieChangedLocation() {
    var input =document.getElementById('location_data');
    if(!isGoogleKeyEnabled) return;
    if(typeof input != 'undefined') {
      var autocomplete = new google.maps.places.Autocomplete(input);
      google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
          return;
        }
        document.getElementById('location_lng').value = place.geometry.location.lng();
        document.getElementById('location_lat').value = place.geometry.location.lat();
        var address = '';
        if (place.address_components) {
          address = [
            (place.address_components[0] && place.address_components[0].short_name || ''),
            (place.address_components[1] && place.address_components[1].short_name || ''),
            (place.address_components[2] && place.address_components[2].short_name || '')
          ].join(' ');
        }
      }); 
    }
  }
</script>
