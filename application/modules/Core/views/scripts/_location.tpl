<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _location.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php
$item = $this->item;
$modulename = $this->modulename;

if(Engine_Api::_()->getApi('settings', 'core')->getSetting($modulename.'.enable.location', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mapApiKey', '')) { ?>
  <?php
    if($item) {
      $locationData = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => $item->getType(), 'resource_id' => $item->getIdentity()));
    }
  ?>
  <script type="text/javascript">
    //function initGoogleMap() {
    en4.core.runonce.add(function() {
      if(isGoogleKeyEnabled){
      var input = document.getElementById('location');
      if(typeof input != 'undefined') {
        var autocomplete = new google.maps.places.Autocomplete(input);
        
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
          var place = autocomplete.getPlace();
          if (!place.geometry) {
            return;
          }
          
          document.getElementById('lng').value = place.geometry.location.lng();
          document.getElementById('lat').value = place.geometry.location.lat();
          
          var lat = scriptJquery('#lat').val();
          var lng = scriptJquery('#lng').val();
          
          if(lat && lng) {
            var geocoder = new google.maps.Geocoder(); 
            geocoder.geocode({'latLng': new google.maps.LatLng(lat, lng)}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK && results.length) {
                if (results[0]) {
                  for(var i=0; i<results[0].address_components.length; i++){
                    if(results[0].address_components[i].types[0] == 'postal_code') {
                      if(results[0].address_components[i].types[0] == 'postal_code') {
                        var postalCode = results[0].address_components[i].long_name;
                      }
                    }
                  }
                }
                if (results[1]) {
                  var indice=0;
                  for (var j=0; j<results.length; j++){
                    if (results[j].types[0]=='locality'){
                      indice=j;
                      break;
                    }
                  }
                  var city = state = country = "";
                  if(typeof results[indice] != "undefines"){
                    for (var i=0; i<results[indice].address_components.length; i++){
                      if (results[indice].address_components[i].types[0] == "locality") {
                        //this is the object you are looking for
                        city = results[indice].address_components[i].long_name;
                      }
                      if (results[indice].address_components[i].types[0] == "administrative_area_level_1") {
                        //this is the object you are looking for
                        state = results[indice].address_components[i].long_name;
                      }
                      if (results[indice].address_components[i].types[0] == "country") {
                        //this is the object you are looking for
                        country = results[indice].address_components[i].long_name;
                      }
                    }
                  }

                  if(city != "")
                    scriptJquery('#city').val(city);
                  else
                    scriptJquery('#city').val('');

                  if(state != "")
                    scriptJquery('#state').val(state);
                  else
                    scriptJquery('#state').val('');

                  if(country != "")
                    scriptJquery('#country').val(country);
                  else
                    scriptJquery('#country').val('');

                  if(postalCode != "")
                    scriptJquery('#zip').val(postalCode);
                  else
                    scriptJquery('#zip').val('');
                }
              }
            });
          }
        });
      }
      }

      <?php if(!empty($locationData)) { ?>
        <?php if($locationData->lat) { ?>
          scriptJquery('#lat').val('<?php echo $locationData->lat; ?>');
        <?php } ?>
        <?php if($locationData->lng) { ?>
          scriptJquery('#lng').val('<?php echo $locationData->lng; ?>');
        <?php } ?>
        <?php if($locationData->city) { ?>
          scriptJquery('#city').val('<?php echo $locationData->city; ?>');
        <?php } ?>
        <?php if($locationData->state) { ?>
          scriptJquery('#state').val('<?php echo $locationData->state; ?>');
        <?php } ?>
        <?php if($locationData->country) { ?>
          scriptJquery('#country').val('<?php echo $locationData->country; ?>');
        <?php } ?>
        <?php if($locationData->zip) { ?>
          scriptJquery('#zip').val('<?php echo $locationData->zip; ?>');
        <?php } ?>
      <?php } ?>
    });
  </script>
<?php } ?>
