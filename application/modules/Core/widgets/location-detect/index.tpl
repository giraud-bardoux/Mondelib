<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="core_header_link">
  <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'index', 'action' => 'detect-location'), 'default', true); ?>" class="ajaxsmoothbox location_data_core"><i class="fas fa-map-marker-alt"></i></a>
  <span>
    <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'index', 'action' => 'detect-location'), 'default', true); ?>" class="ajaxsmoothbox location_data_core" <?php if(!empty($this->cookiedata['location'])){ ?> style="display:block" <?php }else{ ?> style="display:none"  <?php } ?> id="location_data_f" href="javascript:;"><?php echo $this->cookiedata['location']; ?></a>
    <a class="ajaxsmoothbox location_data_core" href="<?php echo $this->url(array('module' => 'core', 'controller' => 'index', 'action' => 'detect-location'), 'default', true); ?>" id="location_data_e" <?php if(empty($this->cookiedata['location'])){ ?> style="display:block" <?php }else{ ?> style="display:none"  <?php } ?>><?php echo $this->translate('Select Your Location'); ?></a>
  </span>
</div>
<div id="core_location_popup_html" style="display:none;">
  <input type="text" id="core_cookievalue" autocomplete="off" placeholder="<?php echo $this->translate('Location'); ?>" value=""/>
  <input type="hidden" id="core_cookielat" value="" />
  <input type="hidden" id="core_cookielng"  value=""/>
  <button id="saveLocationDataValue" onclick=""><?php echo $this->translate("Save"); ?></button>
</div>

<script type="application/javascript">
<?php if(empty($this->cookiedata['location'])) { ?>
  en4.core.runonce.add(function() {
    getLocation();
  });	

  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition);
    }
  }
  
  function showPosition(position) {
    var latMap = position.coords.latitude;
    var lngMap = position.coords.longitude;
    codeLatLng(latMap,lngMap);
  }
  
  function codeLatLng(lat, lng) {
    var latlng = new google.maps.LatLng(lat, lng);
		var 	geocoder = new google.maps.Geocoder();
		var mylocation;
    geocoder
    .geocode(
    {
      'latLng' : latlng
    },
    function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (results[1]) {
          var arrAddress = results;
          //iterate through address_component array
          scriptJquery
            .each(
              arrAddress,
              function(i, address_component) {
              if(i == 0){
                mylocation = address_component.formatted_address;
              }
                
            });
        } 
        if(mylocation) {
          scriptJquery('#core_cookievalue').val(mylocation);
          scriptJquery('#core_cookielat').val(lat);
          scriptJquery('#core_cookielng').val(lng);
          scriptJquery('#saveLocationDataValue').trigger('click');
        }
      } 
    });
	}
<?php } ?>

  scriptJquery('#saveLocationDataValue').click(function(e){

    //set location data in cookie
    var location = scriptJquery('#core_cookievalue').val();
    var lat = scriptJquery('#core_cookielat').val();
    var lng = scriptJquery('#core_cookielng').val();
    //scriptJquery('#core_cookie_value').css('border','');
    //scriptJquery("#core_remove_location").prop('checked', false); 
    if(lat && lng && location){
      setCookieCustom('location_data',location,30);
      //set lat in cookie
      setCookieCustom('location_lat',lat,30);
      //set lng in cookie		
      setCookieCustom('location_lng',lng,30);
      scriptJquery('#location_data_f').show();
      //window.proxyLocation.reload();
      scriptJquery('#location_data_e').hide();
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
  
  function setCookieCustom(cname, cvalue, exdays) {
      var d = new Date();
      d.setTime(d.getTime() + (exdays*24*60*60*1000));
      var expires = "expires="+d.toGMTString();
      document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/"; 
  } 

  en4.core.runonce.add(function() {
    scriptJquery(".layout_core_location_detect").appendTo(scriptJquery("#core_menu_mini_menu"));
  });
</script>
