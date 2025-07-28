<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Styling.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_Form_Admin_Styling extends Engine_Form {
  public function init() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $sesiosappApi = Engine_Api::_()->sesiosapp();
    $this->setTitle('Manage Color Schemes')
            ->setDescription('Here, you can manage the color schemes of your website.');
    
    $getActivatedTheme = $settings->getSetting('sesiosapptheme.color',1);
    $customActivatedTheme = $settings->getSetting('sesiosappcustom.theme.color',1);
    
    $customtheme_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('customtheme_id', 0);
    if($customtheme_id) {
      $customtheme_value = $customtheme_id;
    } else if($getActivatedTheme == 5){
      $customtheme_value = $customActivatedTheme;
    }else{
      $customtheme_value = $getActivatedTheme;  
    }
    
    if($getActivatedTheme != 5){
      $customActivatedTheme = $getActivatedTheme;  
    }
    $sesiosapptheme = Engine_Api::_()->getDbTable('customthemes','sesiosapp')->getThemeKey(array('theme_id'=>$customActivatedTheme,'is_custom'=>1));
    if(engine_count($sesiosapptheme))
      $sesiosapptheme = $sesiosapptheme->toArray();
    else
      $sesiosapptheme = array();
    $this->addElement('Radio', 'theme_color', array(
        'label' => 'Color Schemes',
        'multiOptions' => array(
            1 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/1.png" alt="" />',
            2 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/2.png" alt="" />',
            3 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/3.png" alt="" />',
            4 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/4.png" alt="" />',
						6 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/5.png" alt="" />',
						7 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/6.png" alt="" />',
						5 => '<img src="./application/modules/Sesiosapp/externals/images/color-scheme/custom.png" alt="" />',
        ),
        'required'=>true,
        'allowEmpty'=>false,
        'onchange' => 'changeThemeColor(this.value, "")',
        'escape' => false,
        'value' => $getActivatedTheme,
    ));
        
    $sestheme = array();
    
    $getCustomThemes = Engine_Api::_()->getDbTable('themes', 'sesiosapp')->getTheme();
    foreach($getCustomThemes as $getCustomTheme){
      $sestheme[$getCustomTheme['theme_id']] = $getCustomTheme['name'];
    }

    $this->addElement('Select', 'custom_theme_color', array(
        'label' => 'Custom Theme Color',
        'multiOptions' => $sestheme,
        'required'=>true,
        'allowEmpty'=>false,
        'onChange' => 'changeCustomThemeColor(this.value)',
        'escape' => false,
        'value' => $customtheme_value,
    ));

    $this->addElement('dummy', 'custom_themes', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/Sesiosapp/views/scripts/custom_themes.tpl',
        'class' => 'form element',
        'customtheme_id' => $customtheme_id,
        'activatedTheme' => $customActivatedTheme,
      )))
    ));
      
	    $sesiosapp_navigationColor = $sesiosappApi->getThemeKeyValue('sesiosapp_navigationColor',$sesiosapptheme);
			$sesiosapp_navigationTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_navigationTitleColor',$sesiosapptheme);
			$sesiosapp_appBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_appBackgroundColor',$sesiosapptheme);
			$sesiosapp_appforgroundcolor = $sesiosappApi->getThemeKeyValue('sesiosapp_appforgroundcolor',$sesiosapptheme);
			$sesiosapp_tableViewSeparatorColor = $sesiosappApi->getThemeKeyValue('sesiosapp_tableViewSeparatorColor',$sesiosapptheme);
			$sesiosapp_appFontColor = $sesiosappApi->getThemeKeyValue('sesiosapp_appFontColor',$sesiosapptheme);
			$sesiosapp_activityFeedLinkColor = $sesiosappApi->getThemeKeyValue('sesiosapp_activityFeedLinkColor',$sesiosapptheme);
			$sesiosapp_appSepratorColor = $sesiosappApi->getThemeKeyValue('sesiosapp_appSepratorColor',$sesiosapptheme);
			$sesiosapp_noDataLabelTextColor = $sesiosappApi->getThemeKeyValue('sesiosapp_noDataLabelTextColor',$sesiosapptheme);
			$sesiosapp_navigationDisabledColor = $sesiosappApi->getThemeKeyValue('sesiosapp_navigationDisabledColor',$sesiosapptheme);
			$sesiosapp_navigationActiveColor = $sesiosappApi->getThemeKeyValue('sesiosapp_navigationActiveColor',$sesiosapptheme);
			$sesiosapp_navigationDisabledColor = $sesiosappApi->getThemeKeyValue('sesiosapp_navigationDisabledColor',$sesiosapptheme);
			$sesiosapp_titleLightColor = $sesiosappApi->getThemeKeyValue('sesiosapp_titleLightColor',$sesiosapptheme);
			$sesiosapp_starColor = $sesiosappApi->getThemeKeyValue('sesiosapp_starColor',$sesiosapptheme);
			$sesiosapp_placeholdercolor = $sesiosappApi->getThemeKeyValue('sesiosapp_placeholdercolor',$sesiosapptheme);
			
			$sesiosapp_buttonBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_buttonBackgroundColor',$sesiosapptheme);
			$sesiosapp_buttonTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_buttonTitleColor',$sesiosapptheme);
			$sesiosapp_buttonRadius = $sesiosappApi->getThemeKeyValue('sesiosapp_buttonRadius',$sesiosapptheme);
			$sesiosapp_buttonBorderWidth = $sesiosappApi->getThemeKeyValue('sesiosapp_buttonBorderWidth',$sesiosapptheme,'0');
			$sesiosapp_buttonBorderColor = $sesiosappApi->getThemeKeyValue('sesiosapp_buttonBorderColor',$sesiosapptheme,'');
			$sesiosapp_searchBarTextColor = $sesiosappApi->getThemeKeyValue('sesiosapp_searchBarTextColor',$sesiosapptheme);
			$sesiosapp_searchBarPlaceHolderColor = $sesiosappApi->getThemeKeyValue('sesiosapp_searchBarPlaceHolderColor',$sesiosapptheme);
			$sesiosapp_searchBarIconColor = $sesiosappApi->getThemeKeyValue('sesiosapp_searchBarIconColor',$sesiosapptheme);
			$sesiosapp_contentProfilePageTabTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentProfilePageTabTitleColor',$sesiosapptheme);
			$sesiosapp_contentProfilePageTabActiveColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentProfilePageTabActiveColor',$sesiosapptheme);
			$sesiosapp_contentProfilePageTabBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentProfilePageTabBackgroundColor',$sesiosapptheme);
			$sesiosapp_menuButtonTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_menuButtonTitleColor',$sesiosapptheme);
			$sesiosapp_menuButtonBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_menuButtonBackgroundColor',$sesiosapptheme);
			$sesiosapp_menuButtonActiveTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_menuButtonActiveTitleColor',$sesiosapptheme);
			$sesiosapp_contentScreenTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentScreenTitleColor',$sesiosapptheme);
			$sesiosapp_contentScreenTitleBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentScreenTitleBackgroundColor',$sesiosapptheme);
			$sesiosapp_contentScreenActiveColor = $sesiosappApi->getThemeKeyValue('sesiosapp_contentScreenActiveColor',$sesiosapptheme);
			$sesiosapp_outsidePlaceHolderColor = $sesiosappApi->getThemeKeyValue('sesiosapp_outsidePlaceHolderColor',$sesiosapptheme);
			$sesiosapp_outsideTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_outsideTitleColor',$sesiosapptheme);
			$sesiosapp_outsideButtonTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_outsideButtonTitleColor',$sesiosapptheme);
			$sesiosapp_outsideButtonBackgroundColor = $sesiosappApi->getThemeKeyValue('sesiosapp_outsideButtonBackgroundColor',$sesiosapptheme);
			$sesiosapp_outsideNavigationTitleColor = $sesiosappApi->getThemeKeyValue('sesiosapp_outsideNavigationTitleColor',$sesiosapptheme);
			$sesiosapp_statsTextColor = $sesiosappApi->getThemeKeyValue('sesiosapp_statsTextColor',$sesiosapptheme);
      
      $sesiosapp_fontSizeNormal = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeNormal',$sesiosapptheme,10);
      $sesiosapp_fontSizeMedium = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeMedium',$sesiosapptheme,12);
      $sesiosapp_fontSizeLarge = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeLarge',$sesiosapptheme,14);
      $sesiosapp_fontSizeVeryLarge = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeVeryLarge',$sesiosapptheme,16);
      
      $sesiosapp_fontSizeNormal_ipad = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeNormal_ipad',$sesiosapptheme,12);
      $sesiosapp_fontSizeMedium_ipad = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeMedium_ipad',$sesiosapptheme,14);
      $sesiosapp_fontSizeLarge_ipad = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeLarge_ipad',$sesiosapptheme,16);
      $sesiosapp_fontSizeVeryLarge_ipad = $sesiosappApi->getThemeKeyValue('sesiosapp_fontSizeVeryLarge_ipad',$sesiosapptheme,18);
      
      $sesiosapp_menuGradientColor1 = $sesiosappApi->getThemeKeyValue('sesiosapp_menuGradientColor1',$sesiosapptheme,'#d7c0ac');
      $sesiosapp_menuGradientColor2 = $sesiosappApi->getThemeKeyValue('sesiosapp_menuGradientColor2',$sesiosapptheme,'#9abeb1');
      $sesiosapp_menuGradientColor3 = $sesiosappApi->getThemeKeyValue('sesiosapp_menuGradientColor3',$sesiosapptheme,'#19989e');
      $sesiosapp_menuGradientColor4 = $sesiosappApi->getThemeKeyValue('sesiosapp_menuGradientColor4',$sesiosapptheme,'#0f6a75');
      $sesiosapp_menuGradientColor5 = $sesiosappApi->getThemeKeyValue('sesiosapp_menuGradientColor5',$sesiosapptheme,'#085361');
      
      $allowEmpty = true;
      $required = false;
    
    //Start Footer Styling
    $this->addElement('Dummy', 'fontsize_settings', array(
        'label' => 'App Font Size Settings for iPhone',
    ));
    $this->addElement('Text', "sesiosapp_fontSizeNormal", array(
        'label' => 'Font Size Normal',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeNormal,
    ));

    $this->addElement('Text', "sesiosapp_fontSizeMedium", array(
        'label' => 'Font Size Medium',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeMedium,
    ));

    $this->addElement('Text', "sesiosapp_fontSizeLarge", array(
        'label' => 'Font Size Large',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeLarge,
    ));
    $this->addElement('Text', "sesiosapp_fontSizeVeryLarge", array(
        'label' => 'Font Size Extra Large',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeVeryLarge,
    ));
    $this->addDisplayGroup(array('sesiosapp_fontSizeNormal', 'sesiosapp_fontSizeMedium', 'sesiosapp_fontSizeLarge', 'sesiosapp_fontSizeVeryLarge'), 'iphonefont_settings_group', array('disableLoadDefaultDecorators' => true));
    $iphonefont_settings_group = $this->getDisplayGroup('iphonefont_settings_group');
    $iphonefont_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'iphonefont_settings_group'))));
    //End Footer Styling  
    
    //Start Footer Styling
    $this->addElement('Dummy', 'ipadfontsize_settings', array(
        'label' => 'App Font Size Settings for iPad',
    ));
    $this->addElement('Text', "sesiosapp_fontSizeNormal_ipad", array(
        'label' => 'Font Size Normal',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeNormal_ipad,
    ));

    $this->addElement('Text', "sesiosapp_fontSizeMedium_ipad", array(
        'label' => 'Font Size Medium',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeMedium_ipad,
    ));

    $this->addElement('Text', "sesiosapp_fontSizeLarge_ipad", array(
        'label' => 'Font Size Large',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeLarge_ipad,
    ));
    $this->addElement('Text', "sesiosapp_fontSizeVeryLarge_ipad", array(
        'label' => 'Font Size Extra Large',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_fontSizeVeryLarge_ipad,
    ));
    $this->addDisplayGroup(array('sesiosapp_fontSizeNormal_ipad', 'sesiosapp_fontSizeMedium_ipad', 'sesiosapp_fontSizeLarge_ipad', 'sesiosapp_fontSizeVeryLarge_ipad'), 'ipadfont_settings_group', array('disableLoadDefaultDecorators' => true));
    $ipadfont_settings_group = $this->getDisplayGroup('ipadfont_settings_group');
    $ipadfont_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'ipadfont_settings_group'))));
    //End Footer Styling 
    
    //Start Header Styling
    $this->addElement('Dummy', 'header_settings', array(
        'label' => 'App General Setting:',
    ));
    
    $this->addElement('Text', "sesiosapp_navigationColor", array(
        'label' => 'Header Navigation Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_navigationColor,
    ));
		
    $this->addElement('Text', "sesiosapp_navigationTitleColor", array(
        'label' => 'Header Navigation Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_navigationTitleColor,
    ));
    $this->addElement('Text', "sesiosapp_navigationActiveColor", array(
        'label' => 'Header navigation icon active color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_navigationActiveColor,
    ));
    $this->addElement('Text', "sesiosapp_navigationDisabledColor", array(
        'label' => 'Header navigation icon disabled color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_navigationDisabledColor,
    ));
    $this->addElement('Text', "sesiosapp_appBackgroundColor", array(
        'label' => 'Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_appBackgroundColor,
    ));
		
    $this->addElement('Text', "sesiosapp_appforgroundcolor", array(
        'label' => 'Forground Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_appforgroundcolor,
    ));

    $this->addElement('Text', "sesiosapp_tableViewSeparatorColor", array(
        'label' => 'Table View Seprator Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_tableViewSeparatorColor,
    ));

    $this->addElement('Text', "sesiosapp_appFontColor", array(
        'label' => 'Font Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_appFontColor,
    ));

    $this->addElement('Text', "sesiosapp_activityFeedLinkColor", array(
        'label' => 'Activity Feed Link Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_activityFeedLinkColor,
    ));
		
    $this->addElement('Text', "sesiosapp_appSepratorColor", array(
        'label' => 'Seprator Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_appSepratorColor,
    ));
    $this->addElement('Text', "sesiosapp_noDataLabelTextColor", array(
        'label' => 'No Data Text Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_noDataLabelTextColor,
    ));
		
    

    $this->addElement('Text', "sesiosapp_statsTextColor", array(
        'label' => 'Stats Text Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_statsTextColor,
    ));

    $this->addElement('Text', "sesiosapp_titleLightColor", array(
        'label' => 'Font Light Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_titleLightColor,
    ));
    
    //Top Panel Color
    $this->addElement('Text', "sesiosapp_starColor", array(
        'label' => 'Rating Star Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_starColor,
    ));
    $this->addElement('Text', "sesiosapp_placeholdercolor", array(
        'label' => 'Text Place Holder Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_placeholdercolor,
    ));
    //Top Panel Color
    

    $this->addDisplayGroup(array('sesiosapp_navigationColor', 'sesiosapp_navigationTitleColor', 'sesiosapp_appBackgroundColor', 'sesiosapp_appforgroundcolor', 'sesiosapp_tableViewSeparatorColor', 'sesiosapp_appFontColor', 'sesiosapp_activityFeedLinkColor', 'sesiosapp_appSepratorColor', 'sesiosapp_noDataLabelTextColor', 'sesiosapp_navigationDisabledColor', 'sesiosapp_navigationActiveColor',  'sesiosapp_statsTextColor', 'sesiosapp_titleLightColor','sesiosapp_starColor', 'sesiosapp_placeholdercolor'), 'header_settings_group', array('disableLoadDefaultDecorators' => true));
    $header_settings_group = $this->getDisplayGroup('header_settings_group');
    $header_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'header_settings_group'))));
    //End Header Styling
    
    //Start Footer Styling
    $this->addElement('Dummy', 'gradient_settings', array(
        'label' => 'App Gradient Color Settings',
    ));
    $this->addElement('Text', "sesiosapp_menuGradientColor1", array(
        'label' => 'Gradient 1',
        'allowEmpty' => $allowEmpty,
        'class'=>'SEcolor',
        'required' => $required,
        'value' => $sesiosapp_menuGradientColor1,
    ));

    $this->addElement('Text', "sesiosapp_menuGradientColor2", array(
        'label' => 'Gradient 2',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class'=>'SEcolor',
        'value' => $sesiosapp_menuGradientColor2,
    ));

    $this->addElement('Text', "sesiosapp_menuGradientColor3", array(
        'label' => 'Gradient 3',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class'=>'SEcolor',
        'value' => $sesiosapp_menuGradientColor3,
    ));
    $this->addElement('Text', "sesiosapp_menuGradientColor4", array(
        'label' => 'Gradient 4',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class'=>'SEcolor',
        'value' => $sesiosapp_menuGradientColor4,
    ));
     $this->addElement('Text', "sesiosapp_menuGradientColor5", array(
        'label' => 'Gradient 5',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class'=>'SEcolor',
        'value' => $sesiosapp_menuGradientColor5,
    ));
    $this->addDisplayGroup(array('sesiosapp_menuGradientColor1', 'sesiosapp_menuGradientColor2', 'sesiosapp_menuGradientColor3', 'sesiosapp_menuGradientColor4','sesiosapp_menuGradientColor5'), 'gradientmenu_settings_group', array('disableLoadDefaultDecorators' => true));
    $gradientmenu_settings_group = $this->getDisplayGroup('gradientmenu_settings_group');
    $gradientmenu_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'gradientmenu_settings_group'))));
    //End Footer Styling 
    
    
    //Start Footer Styling
    $this->addElement('Dummy', 'footer_settings', array(
        'label' => 'App button settings',
    ));
    $this->addElement('Text', "sesiosapp_buttonBackgroundColor", array(
        'label' => 'Button Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_buttonBackgroundColor,
    ));
    $this->addElement('Text', "sesiosapp_buttonRadius", array(
        'label' => 'Button Radius',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_buttonRadius,
    ));
    $this->addElement('Text', "sesiosapp_buttonBackgroundColor", array(
        'label' => 'Button Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_buttonBackgroundColor,
    ));
    $this->addElement('Text', "sesiosapp_buttonTitleColor", array(
        'label' => 'Button Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_buttonTitleColor,
    ));

    $this->addElement('Text', "sesiosapp_buttonBorderWidth", array(
        'label' => 'Button Border Width',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'value' => $sesiosapp_buttonBorderWidth,
    ));
    $this->addElement('Text', "sesiosapp_buttonBorderColor", array(
        'label' => 'Button Border Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_buttonBorderColor,
    ));
    $this->addDisplayGroup(array('sesiosapp_buttonBackgroundColor', 'sesiosapp_buttonTitleColor', 'sesiosapp_buttonRadius', 'sesiosapp_buttonBorderWidth', 'sesiosapp_buttonBorderColor'), 'button_settings_group', array('disableLoadDefaultDecorators' => true));
    $button_settings_group = $this->getDisplayGroup('button_settings_group');
    $button_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'button_settings_group'))));
    //End Footer Styling
    //Start Body Styling
    $this->addElement('Dummy', 'searchbar_settings', array(
        'label' => 'App search bar settings',
    ));
    $this->addElement('Text', "sesiosapp_searchBarTextColor", array(
        'label' => 'Search Bar Text Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_searchBarTextColor,
    ));
    
    
    $this->addElement('Text', "sesiosapp_searchBarPlaceHolderColor", array(
        'label' => 'Search Bar Placeholder Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_searchBarPlaceHolderColor,
    ));

    $this->addElement('Text', "sesiosapp_searchBarIconColor", array(
        'label' => 'Search Bar Search Icon Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_searchBarIconColor,
    ));

   
    $this->addDisplayGroup(array('sesiosapp_searchBarTextColor','sesiosapp_searchBarPlaceHolderColor', 'sesiosapp_searchBarIconColor'), 'searchbar_settings_group', array('disableLoadDefaultDecorators' => true));
    $searchbar_settings_group = $this->getDisplayGroup('searchbar_settings_group');
    $searchbar_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'searchbar_settings_group'))));
    //End Body Styling
    
    
     //Start Body Styling
    $this->addElement('Dummy', 'content_settings', array(
        'label' => 'App content Profile Tabs Settings',
    ));
    $this->addElement('Text', "sesiosapp_contentProfilePageTabTitleColor", array(
        'label' => 'Content Profile Tabs Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentProfilePageTabTitleColor,
    ));
    
    
    $this->addElement('Text', "sesiosapp_contentProfilePageTabActiveColor", array(
        'label' => 'Content Profile Tabs Active Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentProfilePageTabActiveColor,
    ));

    $this->addElement('Text', "sesiosapp_contentProfilePageTabBackgroundColor", array(
        'label' => 'Content Profile Tabs Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentProfilePageTabBackgroundColor,
    ));

   
    $this->addDisplayGroup(array('sesiosapp_contentProfilePageTabTitleColor','sesiosapp_contentProfilePageTabActiveColor', 'sesiosapp_contentProfilePageTabBackgroundColor'), 'contentprofiletabs_settings_group', array('disableLoadDefaultDecorators' => true));
    $contentprofiletabs_settings_group = $this->getDisplayGroup('contentprofiletabs_settings_group');
    $contentprofiletabs_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'contentprofiletabs_settings_group'))));
    
    
     //Start Body Styling
    $this->addElement('Dummy', 'menu_settings', array(
        'label' => 'App Plugin Menu Settings',
    ));
    $this->addElement('Text', "sesiosapp_menuButtonTitleColor", array(
        'label' => 'Plugin Menu Button Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_menuButtonTitleColor,
    ));
    
    
    $this->addElement('Text', "sesiosapp_menuButtonBackgroundColor", array(
        'label' => 'Plugin Menu Button Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_menuButtonBackgroundColor,
    ));

    $this->addElement('Text', "sesiosapp_menuButtonActiveTitleColor", array(
        'label' => 'Plugin Menu Button Active Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_menuButtonActiveTitleColor,
    ));

   
    $this->addDisplayGroup(array('sesiosapp_menuButtonBackgroundColor','sesiosapp_menuButtonTitleColor', 'sesiosapp_menuButtonActiveTitleColor'), 'plguinmenu_settings_group', array('disableLoadDefaultDecorators' => true));
    $plguinmenu_settings_group = $this->getDisplayGroup('plguinmenu_settings_group');
    $plguinmenu_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'plguinmenu_settings_group'))));
    
     //Start Body Styling
    $this->addElement('Dummy', 'tabs_settings', array(
        'label' => 'App My content tabs settings',
    ));
    $this->addElement('Text', "sesiosapp_contentScreenTitleColor", array(
        'label' => 'Switch Tab on My Pages Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentScreenTitleColor,
    ));
    
    
    $this->addElement('Text', "sesiosapp_contentScreenTitleBackgroundColor", array(
        'label' => 'Switch Tab on My Pages Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentScreenTitleBackgroundColor,
    ));

    $this->addElement('Text', "sesiosapp_contentScreenActiveColor", array(
        'label' => 'Switch Tab on My Pages Title Active Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_contentScreenActiveColor,
    ));

   
    $this->addDisplayGroup(array('sesiosapp_contentScreenTitleBackgroundColor','sesiosapp_contentScreenTitleColor', 'sesiosapp_contentScreenActiveColor'), 'switchtab_settings_group', array('disableLoadDefaultDecorators' => true));
    $switchtab_settings_group = $this->getDisplayGroup('switchtab_settings_group');
    $switchtab_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'switchtab_settings_group'))));
    
    
     //Start Body Styling
    $this->addElement('Dummy', 'welcome_settings', array(
        'label' => 'App Login/Welcome/Forgot Password screen settings',
    ));
    $this->addElement('Text', "sesiosapp_outsidePlaceHolderColor", array(
        'label' => 'Place Holder Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_outsidePlaceHolderColor,
    ));
    
    
    $this->addElement('Text', "sesiosapp_outsideTitleColor", array(
        'label' => 'Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_outsideTitleColor,
    ));

    $this->addElement('Text', "sesiosapp_outsideButtonTitleColor", array(
        'label' => 'Button Text Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_outsideButtonTitleColor,
    ));
    
     $this->addElement('Text', "sesiosapp_outsideButtonBackgroundColor", array(
        'label' => 'Button Background Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_outsideButtonBackgroundColor,
    ));
     $this->addElement('Text', "sesiosapp_outsideNavigationTitleColor", array(
        'label' => 'Navigation Title Color',
        'allowEmpty' => $allowEmpty,
        'required' => $required,
        'class' => 'SEcolor',
        'value' => $sesiosapp_outsideNavigationTitleColor,
    ));
     
   
    $this->addDisplayGroup(array('sesiosapp_outsideNavigationTitleColor','sesiosapp_outsidePlaceHolderColor','sesiosapp_outsideTitleColor', 'sesiosapp_outsideButtonTitleColor','sesiosapp_outsideButtonBackgroundColor'), 'welcome_settings_group', array('disableLoadDefaultDecorators' => true));
    $welcome_settings_group = $this->getDisplayGroup('welcome_settings_group');
    $welcome_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div','class'=>'sesiosapp_bundle', 'id' => 'welcome_settings_group'))));
    
    
    
    //Add submit button
    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Save as Draft',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
    $this->addDisplayGroup(array('save', 'submit'), 'buttons');
  }

}
