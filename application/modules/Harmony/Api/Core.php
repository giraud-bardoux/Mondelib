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

class Harmony_Api_Core extends Core_Api_Abstract {

  public function themeConstants($themeColor = array(), $fonts = array()) {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $constant = array(
  		"themewidget_radius" => "".$settings->getSetting('themewidget.radius', 10)."px",
    );

    $themeColorsArray = array();
    if(engine_count($themeColor) > 0) {
      foreach($themeColor as $key => $value) {
        $themeColorsArray["".$key.""] = "".$value."";
      }
    } else {
      $themecustom = Engine_Api::_()->getDbTable('customthemes', 'harmony')->getThemeKey(array('theme_id'=> $settings->getSetting('harmony.theme.color', 1)));
      foreach($themecustom as $value) {
        $themeColorsArray["".$value['column_key'].""] = "".$value['value']."";
      }
    } 
    $constant = array_merge($constant, $themeColorsArray);

    //Fonts
    if(engine_count($fonts) > 0) { 
      $fontsArray = array();
      foreach($fonts as $key => $value) {
        $fontsArray["".$key.""] = "".$value."";
      }
    } else {
      $fontsArray = array(
        "harmony_body_fontfamily" => "Default Font",
        "harmony_heading_fontfamily" => "Default Font",
        "harmony_mainmenu_fontfamily" => "Default Font",
        "harmony_tab_fontfamily" => "Default Font",
        "harmony_body_fontsize" => "0.85rem",
        "harmony_heading_fontsize" => "1.1rem",
        "harmony_mainmenu_fontsize" => "0.8rem",
        "harmony_tab_fontsize" => "0.875rem",
      );
    }

    $constant = array_merge($constant, $fontsArray);

    return $constant;
  }
}
