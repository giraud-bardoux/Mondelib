<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Countries.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Countries extends Engine_Db_Table {
  
  protected $_rowClass = 'Core_Model_Country';
  
  public function getCountry($countryCode) {
  
    return $this->select()
              ->from($this->info('name'), 'country_id')
              ->where('phonecode =?', $countryCode)
			  ->orWhere('iso2 =?', $countryCode)
              ->query()
              ->fetchColumn();
  }
  
  function getCountries() {
    $select = $this->select()
                  ->where('enabled =?',1)
                  ->order('name ASC');
    return $this->fetchAll($select);
  }
  
  public function getAllCountryHtml($params = array()) {
  
    if(isset($params['country_code']) && !empty($params['country_code']))
      $defaultCountry = $params['country_code'];
    else 
     $defaultCountry = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US');

    $country_code = '<div id="'.$params['id'].'" style="display:none;"> <div id="country_code_element" style="display:none;"> <select name="country_code" id="country_code" style="display:none;">';
    foreach ($this->getCountries() as $country) {
      
      if(!empty($country->icon)) {
        $path = Engine_Api::_()->core()->getFileUrl($country->icon);
        if(!empty($path)) { 
          $image = json_encode(array('image' => $path));
        }
      } else {
        $image = '';
      }

      $country_code .= '<option ' . ($country->iso2 == $defaultCountry || $country->phonecode == $defaultCountry ? "selected = 'selected'" : "") . " value='" . $country->phonecode . "_".$country->country_id."'  data-data='".$image."' >" . $country->name.' (+'.str_replace('+', '', $country->phonecode).')' . '</option>';

    }
    $country_code .= '</select></div></div>';
    return $country_code;
  }
}
