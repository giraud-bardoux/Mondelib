<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Core.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Api_Core extends Core_Api_Abstract {
  public function themeConstants(){
    return array('sesiosapp_fontSizeNormal','sesiosapp_fontSizeMedium','sesiosapp_fontSizeLarge','sesiosapp_fontSizeVeryLarge','sesiosapp_fontSizeNormal_ipad','sesiosapp_fontSizeMedium_ipad','sesiosapp_fontSizeLarge_ipad','sesiosapp_fontSizeVeryLarge_ipad','sesiosapp_navigationColor','sesiosapp_navigationTitleColor','sesiosapp_appBackgroundColor','sesiosapp_appforgroundcolor','sesiosapp_tableViewSeparatorColor','sesiosapp_appFontColor','sesiosapp_activityFeedLinkColor','sesiosapp_appSepratorColor','sesiosapp_noDataLabelTextColor','sesiosapp_navigationDisabledColor','sesiosapp_navigationActiveColor','sesiosapp_statsTextColor','sesiosapp_titleLightColor','sesiosapp_starColor','sesiosapp_placeholdercolor','sesiosapp_menuGradientColor1','sesiosapp_menuGradientColor2','sesiosapp_menuGradientColor3','sesiosapp_menuGradientColor4','sesiosapp_menuGradientColor5','sesiosapp_buttonBackgroundColor','sesiosapp_buttonTitleColor','sesiosapp_buttonRadius','sesiosapp_buttonBorderWidth','sesiosapp_buttonBorderColor','sesiosapp_searchBarTextColor','sesiosapp_searchBarPlaceHolderColor','sesiosapp_searchBarIconColor','sesiosapp_contentProfilePageTabTitleColor','sesiosapp_contentProfilePageTabActiveColor','sesiosapp_contentProfilePageTabBackgroundColor','sesiosapp_menuButtonBackgroundColor','sesiosapp_menuButtonTitleColor','sesiosapp_menuButtonActiveTitleColor','sesiosapp_contentScreenTitleBackgroundColor','sesiosapp_contentScreenTitleColor','sesiosapp_contentScreenActiveColor','sesiosapp_outsideNavigationTitleColor','sesiosapp_outsidePlaceHolderColor','sesiosapp_outsideTitleColor','sesiosapp_outsideButtonTitleColor','sesiosapp_outsideButtonBackgroundColor');  
  }
  public function themeOneConstants(){
    return array('10','12','14','16','12','14','16','18','#157EC2','#FFFFFF','#EBF0F3','#FFFFFF','#BCC2C1','#000000','#000000','#BCC2C1','#90949C','#999999','#FFFFFF','#90949C','#90949C','#FFC107','#FFFFFF','#D7C0AC','#9ABEB1','#19989E','#0F6A75','#085361','#157EC2','#FFFFFF','5','','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#555555','#157EC2','#F5F5F5','#FFFFFF','#555555','#157EC2','#FFFFFF','#555555','#157EC2','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#157EC2');
  }
  public function themeTwoConstants(){
    return array('10','12','14','16','12','14','16','18','#ED54A4','#FFFFFF','#F5F5F5','#FFFFFF','#F5F5F5','#243238','#243238','#F5F5F5','#ED54A4','#4682B4','#4F93CC','#000000','#707070','#ED54A4','#707070','#0FB8AD','#0FB8AD','#1FC8DB','#2CB5E8','#2CB5E8','#4682B4','#FFFFFF','','','#4682B4','#FFFFFF','#FFFFFF','#FFFFFF','#000000','#ED54A4','#FFFFFF','#FFFFFF','#000000','#ED54A4','#FFFFFF','#000000','#ED54A4','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#ED54A4'); 
  }
  public function themeThreeConstants(){
    return array('10','12','14','16','12','14','16','18','#252627','#FFFFFF','#070707','#252627','#3A3C3D','#F5F5F5','#FFFFFF','#3A3C3D','#FFFFFF','#FFFFFF','#FFFFFF','#C5C5C5','#C5C5C5','#FFAD08','#FFFFFF','#EB3349','#EB3349','#F45C43','#F45C43','#F45C43','#B63A6B','#FFFFFF','5','','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#B63A6B','#252627','#252627','#FFFFFF','#B63A6B','#252627','#FFFFFF','#B63A6B','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#B63A6B'); 
  }
  public function themeFourConstants(){
    return array('10','12','14','16','12','14','16','18','#4266B2','#FFFFFF','#E9EBEE','#FFFFFF','#E9EBEE','#000000','#000000','#E9EBEE','#4266B2','#B2BED2','#4266B2','#000000','#90949C','#4266B2','#90949C','#D7C0AC','#9ABEB1','#19989E','#0F6A75','#085361','#E9EBEE','#4266B2','5','1','#4266B2','#FFFFFF','#90949C','#FFFFFF','#000000','#4266B2','#F5F5F5','#FFFFFF','#000000','#4266B2','#FFFFFF','#000000','#4266B2','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#4266B2'); 
  }
  public function themeFiveConstants(){
    return array('10','12','14','16','12','14','16','18','#E40046','#FFFFFF','#F7F7F7','#FFFFFF','#F1F1F1','#000000','#E40046','#F1F1F1','#E40046','#CD003F','#FFFFFF','#000000','#666666','#CD003F','#909090','#7B4397','#7B4397','#DC2430','#DC2430','#DC2430','#E40046','#FFFFFF','3','1','#E40046','#FFFFFF','#FFFFFF','#FFFFFF','#000000','#E40046','#FFFFFF','#FFFFFF','#000000','#E40046','#FFFFFF','#000000','#E40046','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#E40046'); 
  }
  public function themeSixConstants(){
    return array('10','12','14','16','12','14','16','18','#FF1D23','#FFFFFF','#111418','#222428','#36383D','#FFFFFF','#FF1D23','#36383D','#FFFFFF','#FF5252','#FFFFFF','#DDDDDD','#DDDDDD','#FF1D23','#718080','#7B4397','#7B4397','#DC2430','#DC2430','#DC2430','#FF1D23','#FFFFFF','3','1','#FF1D23','#FFFFFF','#FFFFFF','#FFFFFF','#FFFFFF','#FF1D23','#222428','#111418','#FFFFFF','#FF1D23','#111418','#FFFFFF','#FF1D23','#FFFFFF','#2B2B2B','#000000','#FFFFFF','#FF1D23');
  }
  public function getValue($key = "",$results = array()){    
    if($key){
       foreach($results as $result){
          if(($result['column_key'] == $key))
          {
            return $result['value'];  
          }  
       }
    }
    return "";
  }
  public function getThemeKeyValue($key = "",$result = array(),$defaultValue = ""){ 
      $value = $this->getValue($key,$result);
      if($key && !empty($value)){
        return $value;  
      }else if($defaultValue){
          return $defaultValue;
      }else{
        //return own default value  
      }
  }
}