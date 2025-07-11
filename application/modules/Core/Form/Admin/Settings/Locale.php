<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Locale.php 9812 2012-11-01 02:14:01Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Locale extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Locale Settings')
      ->setDescription('CORE_FORM_ADMIN_SETTINGS_LOCALE_DESCRIPTION');

    // Element: timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Default Timezone',
      'multiOptions' => Engine_Api::_()->core()->timeZone(),
    ));

    // Init default locale
    $localeObject = Zend_Registry::get('Locale');

    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach( array_keys(Zend_Locale::getLocaleList()) as $key ) {
      $languageName = null;
      if( !empty($languages[$key]) ) {
        $languageName = $languages[$key];
      } else {
        $tmpLocale = new Zend_Locale($key);
        $region = $tmpLocale->getRegion();
        $language = $tmpLocale->getLanguage();
        if( !empty($languages[$language]) && !empty($territories[$region]) ) {
          $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
        }
      }

      if( $languageName ) {
        $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
      }
    }
    $localeMultiOptions = array_merge(array('auto'=>'[Automatic]'), $localeMultiOptions);
    
    $this->addElement('Select', 'locale', array(
      'label' => 'Default Locale',
      'multiOptions' => $localeMultiOptions,
      'value' => 'auto',
      'disableTranslator'=> true
    ));
    
    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
