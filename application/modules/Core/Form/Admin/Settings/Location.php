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

class Core_Form_Admin_Settings_Location extends Engine_Form {

  public function init() {

    $this->setTitle('Location Settings')
          ->setDescription('These settings affect all members in your community.');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Select', "enableglocation", array(
      'label' => 'Allow Location',
      'description' => 'Do you want enable location on your website?',
      'allowEmpty' => true,
      'required' => false,
      'multiOptions'=> array(
        1 => 'Yes, allow google location',
        2 => 'Yes, allow location',
        0 => 'No'
      ),
      'onchange' => 'enablelocation(this.value);',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0),
    ));

    $guidlines = $this->getTranslator()->translate('Enable below APIs:
    1. Google Maps JavaScript API
    2. Google Maps Embed API
    3. Google Static Maps API
    4. Google Places API Web Service
    5. Google Maps Directions API
    6. Google Maps Geolocation API');

    $description = $this->getTranslator()->translate('Enter the Google map API key for displaying Google map on your website.');
    $clickhere = $this->getTranslator()->translate('<a href="%1$s" target="_blank"> Click Here</a> to generate the API key.');
    $description = vsprintf($description.$clickhere, array('https://console.developers.google.com/project'));
    
    $this->addElement('Text', "core_mapApiKey", array(
      'label' => 'Google Map API Key',
      'description' => $description . '<a href="javascript:;" class="core_form_help_icon" title="' . $guidlines . '"><img src="application/modules/Core/externals/images/admin/question.png" alt="Question" /></a>',
      'allowEmpty' => true,
      'required' => false,
      'value' => $settings->getSetting('core.mapApiKey', ''),
    ));
    $this->core_mapApiKey->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Select', 'core_search_type', array(
      'label' => 'Proximity Search Unit (Search via Google API)',
      'description' => 'Choose the unit for proximity search of location on your website. (Note: This setting will only work when you have enabled location via Google APIs. If you have disabled Google APIs, then you will not able to search based on their proximity.)',
      'multiOptions' => array(
          1 => 'Miles',
          0 => 'Kilometers'
      ),
      'value' => $settings->getSetting('core.search.type', 1),
    ));
    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
