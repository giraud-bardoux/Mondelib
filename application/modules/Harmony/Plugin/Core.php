<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Core.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Plugin_Core extends Zend_Controller_Plugin_Abstract {

	public function onRenderLayoutDefault(){

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $changelanding = $settings->getSetting('harmony.changelanding', 0);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(!empty($changelanding) && $module == 'core' && $controller == 'index' && $action == 'index') {
      $script = '
        en4.core.runonce.add(function() {
          scriptJquery ("body").addClass("harmony_landingpage");
        });';
      $view->headScript()->appendScript($script);
		}
		
    //Theme mode active

    $script = "var isThemeModeActive = true;";
    $view->headScript()->appendScript($script);

    //Google Font Work
    $usegoogleFont = $settings->getSetting('harmony.googlefonts', 0);
    if(!empty($usegoogleFont)) {
      $string = 'https://fonts.googleapis.com/css?family=';

      $bodyFontFamily = $settings->getSetting('harmony.googlebody.fontfamily'); 
      $string .= str_replace('"', '', $bodyFontFamily);
      $string .= ':'.$settings->getSetting('harmony.googlebody.fontfamilyvariants');

      $headingFontFamily = $settings->getSetting('harmony.googleheading.fontfamily');
      $string .= '|'.str_replace('"', '', $headingFontFamily);
      $string .= ':'.$settings->getSetting('harmony.googleheading.fontfamilyvariants');
      
      $mainmenuFontFamily = $settings->getSetting('harmony.googlemainmenu.fontfamily');
      $string .= '|'.str_replace('"', '', $mainmenuFontFamily);
      $string .= ':'.$settings->getSetting('harmony.googlemainmenu.fontfamilyvariants');

      $tabFontFamily = $settings->getSetting('harmony.googletab.fontfamily');
      $string .= '|'.str_replace('"', '', $tabFontFamily);
      $string .= ':'.$settings->getSetting('harmony.googletab.fontfamilyvariants');

      $view->headLink()->appendStylesheet($string);
    }
	}
  public function onRenderLayoutDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event, 'simple');
  }
}
