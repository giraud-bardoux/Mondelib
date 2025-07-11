<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Location.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Location extends Core_Api_Abstract {

  public function saveLocation($postData, $item, $type = 'create') {
  
    if (isset($postData['lat']) && isset($postData['lng']) && $postData['lat'] != '' && $postData['lng'] != '' && !empty($postData['location'])) {
      
      //if($type == 'edit' && $item) {
        $locationData = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => $item->getType(), 'resource_id' => $item->getIdentity()));
        if($locationData) {
          $locationData->delete();
        }
      //}
      
      $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
      $dbGetInsert->query('INSERT INTO engine4_core_locations (resource_id, venue, lat, lng ,city,state,zip,country,address,address2, resource_type) VALUES ("' . $item->getIdentity() . '","' . $postData['location'] . '", "' . $postData['lat'] . '","' . $postData['lng'] . '","' . $postData['city'] . '","' . $postData['state'] . '","' . $postData['zip'] . '","' . $postData['country'] . '","' . @$postData['address'] . '","' . @$postData['address2'] . '", "'.$item->getType().'")	ON DUPLICATE KEY UPDATE	lat = "' . $postData['lat'] . '" , lng = "' . $postData['lng'] . '",city = "' . $postData['city'] . '", state = "' . $postData['state'] . '", country = "' . $postData['country'] . '", zip = "' . $postData['zip'] . '", address = "' . @$postData['address'] . '", address2 = "' . @$postData['address2'] . '"');
    }
  }

  function getUserLocationBasedCookieData() {
  
    $location = $lat = $lng = $location_countryshortname = '';
    if (isset($_COOKIE['location_data']) && isset($_COOKIE['location_lat']) && isset($_COOKIE['location_lng'])) {
      $location = $_COOKIE['location_data'];
      $lat = $_COOKIE['location_lat'];
      $lng = $_COOKIE['location_lng'];
      $location_countryshortname = $_COOKIE['location_countryshortname'];

      if($location == 'undefined' && $lat == 'undefined' && $lng == 'undefined') {
        $location = $lat = $lng = $location_countryshortname = '';
      }
    }
    return array('location' => $location, 'lat' => $lat, 'lng' => $lng, 'location_countryshortname' => $location_countryshortname);
  }
}
