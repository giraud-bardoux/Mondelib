<?php

/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Styling.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesandroidapp_Form_Admin_Styling extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $sesandroidappApi = Engine_Api::_()->sesandroidapp();
    $this->setTitle('Manage Color Schemes')
      ->setDescription('Here, you can manage the color schemes of your website.');

    $getActivatedTheme = $settings->getSetting('sesandroidapptheme.color', 1);
    $customActivatedTheme = $settings->getSetting('sesandroidappcustom.theme.color', 1);

    $customtheme_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('customtheme_id', 0);
    if ($customtheme_id) {
      $customtheme_value = $customtheme_id;
    } else if ($getActivatedTheme == 5) {
      $customtheme_value = $customActivatedTheme;
    } else {
      $customtheme_value = $getActivatedTheme;
    }

    if ($getActivatedTheme != 5) {
      $customActivatedTheme = $getActivatedTheme;
    }
    $sesandroidapptheme = Engine_Api::_()->getDbTable('customthemes', 'sesandroidapp')->getThemeKey(array('theme_id' => $customActivatedTheme, 'is_custom' => 1));
    if (engine_count($sesandroidapptheme))
      $sesandroidapptheme = $sesandroidapptheme->toArray();
    else
      $sesandroidapptheme = array();
    $this->addElement('Radio', 'theme_color', array(
      'label' => 'Color Schemes',
      'multiOptions' => array(
        1 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/1.png" alt="" />',
        2 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/2.png" alt="" />',
        3 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/3.png" alt="" />',
        4 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/4.png" alt="" />',
        6 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/5.png" alt="" />',
        7 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/6.png" alt="" />',
        5 => '<img src="./application/modules/Sesandroidapp/externals/images/color-scheme/custom.png" alt="" />',
      ),
      'required' => true,
      'allowEmpty' => false,
      'onchange' => 'changeThemeColor(this.value, "")',
      'escape' => false,
      'value' => $getActivatedTheme,
    ));

    $sestheme = array();

    $getCustomThemes = Engine_Api::_()->getDbTable('themes', 'sesandroidapp')->getTheme();
    foreach ($getCustomThemes as $getCustomTheme) {
      $sestheme[$getCustomTheme['theme_id']] = $getCustomTheme['name'];
    }

    $this->addElement('Select', 'custom_theme_color', array(
      'label' => 'Custom Theme Color',
      'multiOptions' => $sestheme,
      'required' => true,
      'allowEmpty' => false,
      'onChange' => 'changeCustomThemeColor(this.value)',
      'escape' => false,
      'value' => $customtheme_value,
    ));

    $this->addElement('dummy', 'custom_themes', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/Sesandroidapp/views/scripts/custom_themes.tpl',
        'class' => 'form element',
        'customtheme_id' => $customtheme_id,
        'activatedTheme' => $customActivatedTheme,
      )))
    ));

    $sesandroidapp_navigationColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_navigationColor', $sesandroidapptheme);
    $sesandroidapp_navigationTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_navigationTitleColor', $sesandroidapptheme);
    $sesandroidapp_appBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_appBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_appforgroundcolor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_appforgroundcolor', $sesandroidapptheme);
    $sesandroidapp_tableViewSeparatorColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_tableViewSeparatorColor', $sesandroidapptheme);
    $sesandroidapp_appFontColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_appFontColor', $sesandroidapptheme);
    $sesandroidapp_activityFeedLinkColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_activityFeedLinkColor', $sesandroidapptheme);
    $sesandroidapp_appSepratorColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_appSepratorColor', $sesandroidapptheme);
    $sesandroidapp_noDataLabelTextColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_noDataLabelTextColor', $sesandroidapptheme);
    $sesandroidapp_navigationDisabledColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_navigationDisabledColor', $sesandroidapptheme);
    $sesandroidapp_navigationActiveColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_navigationActiveColor', $sesandroidapptheme);
    $sesandroidapp_navigationDisabledColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_navigationDisabledColor', $sesandroidapptheme);
    $sesandroidapp_titleLightColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_titleLightColor', $sesandroidapptheme);
    $sesandroidapp_starColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_starColor', $sesandroidapptheme);
    $sesandroidapp_placeholdercolor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_placeholdercolor', $sesandroidapptheme);

    $sesandroidapp_buttonBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_buttonBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_buttonTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_buttonTitleColor', $sesandroidapptheme);
    $sesandroidapp_buttonRadius = $sesandroidappApi->getThemeKeyValue('sesandroidapp_buttonRadius', $sesandroidapptheme);
    $sesandroidapp_buttonBorderWidth = $sesandroidappApi->getThemeKeyValue('sesandroidapp_buttonBorderWidth', $sesandroidapptheme, '0');
    $sesandroidapp_buttonBorderColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_buttonBorderColor', $sesandroidapptheme, '');
    $sesandroidapp_searchBarTextColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_searchBarTextColor', $sesandroidapptheme);
    $sesandroidapp_searchBarPlaceHolderColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_searchBarPlaceHolderColor', $sesandroidapptheme);
    $sesandroidapp_searchBarIconColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_searchBarIconColor', $sesandroidapptheme);
    $sesandroidapp_contentProfilePageTabTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentProfilePageTabTitleColor', $sesandroidapptheme);
    $sesandroidapp_contentProfilePageTabActiveColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentProfilePageTabActiveColor', $sesandroidapptheme);
    $sesandroidapp_contentProfilePageTabBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentProfilePageTabBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_menuButtonTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuButtonTitleColor', $sesandroidapptheme);
    $sesandroidapp_menuButtonBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuButtonBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_menuButtonActiveTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuButtonActiveTitleColor', $sesandroidapptheme);
    $sesandroidapp_contentScreenTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentScreenTitleColor', $sesandroidapptheme);
    $sesandroidapp_contentScreenTitleBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentScreenTitleBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_contentScreenActiveColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_contentScreenActiveColor', $sesandroidapptheme);
    $sesandroidapp_outsidePlaceHolderColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_outsidePlaceHolderColor', $sesandroidapptheme);
    $sesandroidapp_outsideTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_outsideTitleColor', $sesandroidapptheme);
    $sesandroidapp_outsideButtonTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_outsideButtonTitleColor', $sesandroidapptheme);
    $sesandroidapp_outsideButtonBackgroundColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_outsideButtonBackgroundColor', $sesandroidapptheme);
    $sesandroidapp_outsideNavigationTitleColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_outsideNavigationTitleColor', $sesandroidapptheme);
    $sesandroidapp_statsTextColor = $sesandroidappApi->getThemeKeyValue('sesandroidapp_statsTextColor', $sesandroidapptheme);

    $sesandroidapp_fontSizeNormal = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeNormal', $sesandroidapptheme, 10);
    $sesandroidapp_fontSizeMedium = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeMedium', $sesandroidapptheme, 12);
    $sesandroidapp_fontSizeLarge = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeLarge', $sesandroidapptheme, 14);
    $sesandroidapp_fontSizeVeryLarge = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeVeryLarge', $sesandroidapptheme, 16);

    $sesandroidapp_fontSizeNormal_ipad = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeNormal_ipad', $sesandroidapptheme, 12);
    $sesandroidapp_fontSizeMedium_ipad = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeMedium_ipad', $sesandroidapptheme, 14);
    $sesandroidapp_fontSizeLarge_ipad = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeLarge_ipad', $sesandroidapptheme, 16);
    $sesandroidapp_fontSizeVeryLarge_ipad = $sesandroidappApi->getThemeKeyValue('sesandroidapp_fontSizeVeryLarge_ipad', $sesandroidapptheme, 18);

    $sesandroidapp_menuGradientColor1 = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuGradientColor1', $sesandroidapptheme, '#d7c0ac');
    $sesandroidapp_menuGradientColor2 = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuGradientColor2', $sesandroidapptheme, '#9abeb1');
    $sesandroidapp_menuGradientColor3 = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuGradientColor3', $sesandroidapptheme, '#19989e');
    $sesandroidapp_menuGradientColor4 = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuGradientColor4', $sesandroidapptheme, '#0f6a75');
    $sesandroidapp_menuGradientColor5 = $sesandroidappApi->getThemeKeyValue('sesandroidapp_menuGradientColor5', $sesandroidapptheme, '#085361');

    $allowEmpty = true;
    $required = false;

    //Start Footer Styling
    $this->addElement('Dummy', 'fontsize_settings', array(
      'label' => 'App Font Size Settings for Phone',
    ));
    $this->addElement('Text', "sesandroidapp_fontSizeNormal", array(
      'label' => 'Font Size Normal',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeNormal,
    ));

    $this->addElement('Text', "sesandroidapp_fontSizeMedium", array(
      'label' => 'Font Size Medium',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeMedium,
    ));

    $this->addElement('Text', "sesandroidapp_fontSizeLarge", array(
      'label' => 'Font Size Large',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeLarge,
    ));
    $this->addElement('Text', "sesandroidapp_fontSizeVeryLarge", array(
      'label' => 'Font Size Extra Large',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeVeryLarge,
    ));
    $this->addDisplayGroup(array('sesandroidapp_fontSizeNormal', 'sesandroidapp_fontSizeMedium', 'sesandroidapp_fontSizeLarge', 'sesandroidapp_fontSizeVeryLarge'), 'iphonefont_settings_group', array('disableLoadDefaultDecorators' => true));
    $iphonefont_settings_group = $this->getDisplayGroup('iphonefont_settings_group');
    $iphonefont_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'iphonefont_settings_group'))));
    //End Footer Styling  

    //Start Footer Styling
    $this->addElement('Dummy', 'ipadfontsize_settings', array(
      'label' => 'App Font Size Settings for Tablet',
    ));
    $this->addElement('Text', "sesandroidapp_fontSizeNormal_ipad", array(
      'label' => 'Font Size Normal',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeNormal_ipad,
    ));

    $this->addElement('Text', "sesandroidapp_fontSizeMedium_ipad", array(
      'label' => 'Font Size Medium',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeMedium_ipad,
    ));

    $this->addElement('Text', "sesandroidapp_fontSizeLarge_ipad", array(
      'label' => 'Font Size Large',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeLarge_ipad,
    ));
    $this->addElement('Text', "sesandroidapp_fontSizeVeryLarge_ipad", array(
      'label' => 'Font Size Extra Large',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_fontSizeVeryLarge_ipad,
    ));
    $this->addDisplayGroup(array('sesandroidapp_fontSizeNormal_ipad', 'sesandroidapp_fontSizeMedium_ipad', 'sesandroidapp_fontSizeLarge_ipad', 'sesandroidapp_fontSizeVeryLarge_ipad'), 'ipadfont_settings_group', array('disableLoadDefaultDecorators' => true));
    $ipadfont_settings_group = $this->getDisplayGroup('ipadfont_settings_group');
    $ipadfont_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'ipadfont_settings_group'))));
    //End Footer Styling 

    //Start Header Styling
    $this->addElement('Dummy', 'header_settings', array(
      'label' => 'App General Setting:',
    ));

    $this->addElement('Text', "sesandroidapp_navigationColor", array(
      'label' => 'Header Navigation Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_navigationColor,
    ));

    $this->addElement('Text', "sesandroidapp_navigationTitleColor", array(
      'label' => 'Header Navigation Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_navigationTitleColor,
    ));
    $this->addElement('Text', "sesandroidapp_navigationActiveColor", array(
      'label' => 'Header navigation icon active color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_navigationActiveColor,
    ));
    $this->addElement('Text', "sesandroidapp_navigationDisabledColor", array(
      'label' => 'Header navigation icon disabled color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_navigationDisabledColor,
    ));
    $this->addElement('Text', "sesandroidapp_appBackgroundColor", array(
      'label' => 'Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_appBackgroundColor,
    ));

    $this->addElement('Text', "sesandroidapp_appforgroundcolor", array(
      'label' => 'Forground Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_appforgroundcolor,
    ));

    $this->addElement('Text', "sesandroidapp_tableViewSeparatorColor", array(
      'label' => 'Table View Seprator Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_tableViewSeparatorColor,
    ));

    $this->addElement('Text', "sesandroidapp_appFontColor", array(
      'label' => 'Font Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_appFontColor,
    ));

    $this->addElement('Text', "sesandroidapp_activityFeedLinkColor", array(
      'label' => 'Activity Feed Link Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_activityFeedLinkColor,
    ));

    $this->addElement('Text', "sesandroidapp_appSepratorColor", array(
      'label' => 'Seprator Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_appSepratorColor,
    ));
    $this->addElement('Text', "sesandroidapp_noDataLabelTextColor", array(
      'label' => 'No Data Text Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_noDataLabelTextColor,
    ));



    $this->addElement('Text', "sesandroidapp_statsTextColor", array(
      'label' => 'Stats Text Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_statsTextColor,
    ));

    $this->addElement('Text', "sesandroidapp_titleLightColor", array(
      'label' => 'Font Light Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_titleLightColor,
    ));

    //Top Panel Color
    $this->addElement('Text', "sesandroidapp_starColor", array(
      'label' => 'Rating Star Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_starColor,
    ));
    $this->addElement('Text', "sesandroidapp_placeholdercolor", array(
      'label' => 'Text Place Holder Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_placeholdercolor,
    ));
    //Top Panel Color


    $this->addDisplayGroup(array('sesandroidapp_navigationColor', 'sesandroidapp_navigationTitleColor', 'sesandroidapp_appBackgroundColor', 'sesandroidapp_appforgroundcolor', 'sesandroidapp_tableViewSeparatorColor', 'sesandroidapp_appFontColor', 'sesandroidapp_activityFeedLinkColor', 'sesandroidapp_appSepratorColor', 'sesandroidapp_noDataLabelTextColor', 'sesandroidapp_navigationDisabledColor', 'sesandroidapp_navigationActiveColor',  'sesandroidapp_statsTextColor', 'sesandroidapp_titleLightColor', 'sesandroidapp_starColor', 'sesandroidapp_placeholdercolor'), 'header_settings_group', array('disableLoadDefaultDecorators' => true));
    $header_settings_group = $this->getDisplayGroup('header_settings_group');
    $header_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'header_settings_group'))));
    //End Header Styling


    //Start Footer Styling
    $this->addElement('Dummy', 'footer_settings', array(
      'label' => 'App button settings',
    ));
    $this->addElement('Text', "sesandroidapp_buttonBackgroundColor", array(
      'label' => 'Button Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_buttonBackgroundColor,
    ));
    $this->addElement('Text', "sesandroidapp_buttonRadius", array(
      'label' => 'Button Radius',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_buttonRadius,
    ));
    $this->addElement('Text', "sesandroidapp_buttonBackgroundColor", array(
      'label' => 'Button Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_buttonBackgroundColor,
    ));
    $this->addElement('Text', "sesandroidapp_buttonTitleColor", array(
      'label' => 'Button Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_buttonTitleColor,
    ));

    $this->addElement('Text', "sesandroidapp_buttonBorderWidth", array(
      'label' => 'Button Border Width',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'value' => $sesandroidapp_buttonBorderWidth,
    ));
    $this->addElement('Text', "sesandroidapp_buttonBorderColor", array(
      'label' => 'Button Border Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_buttonBorderColor,
    ));
    $this->addDisplayGroup(array('sesandroidapp_buttonBackgroundColor', 'sesandroidapp_buttonTitleColor', 'sesandroidapp_buttonRadius', 'sesandroidapp_buttonBorderWidth', 'sesandroidapp_buttonBorderColor'), 'button_settings_group', array('disableLoadDefaultDecorators' => true));
    $button_settings_group = $this->getDisplayGroup('button_settings_group');
    $button_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'button_settings_group'))));
    //End Footer Styling
    //Start Body Styling
    $this->addElement('Dummy', 'searchbar_settings', array(
      'label' => 'App search bar settings',
    ));
    $this->addElement('Text', "sesandroidapp_searchBarTextColor", array(
      'label' => 'Search Bar Text Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_searchBarTextColor,
    ));


    $this->addElement('Text', "sesandroidapp_searchBarPlaceHolderColor", array(
      'label' => 'Search Bar Placeholder Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_searchBarPlaceHolderColor,
    ));

    $this->addElement('Text', "sesandroidapp_searchBarIconColor", array(
      'label' => 'Search Bar Search Icon Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_searchBarIconColor,
    ));


    $this->addDisplayGroup(array('sesandroidapp_searchBarTextColor', 'sesandroidapp_searchBarPlaceHolderColor', 'sesandroidapp_searchBarIconColor'), 'searchbar_settings_group', array('disableLoadDefaultDecorators' => true));
    $searchbar_settings_group = $this->getDisplayGroup('searchbar_settings_group');
    $searchbar_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'searchbar_settings_group'))));
    //End Body Styling


    //Start Body Styling
    $this->addElement('Dummy', 'content_settings', array(
      'label' => 'App content Profile Tabs Settings',
    ));
    $this->addElement('Text', "sesandroidapp_contentProfilePageTabTitleColor", array(
      'label' => 'Content Profile Tabs Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentProfilePageTabTitleColor,
    ));


    $this->addElement('Text', "sesandroidapp_contentProfilePageTabActiveColor", array(
      'label' => 'Content Profile Tabs Active Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentProfilePageTabActiveColor,
    ));

    $this->addElement('Text', "sesandroidapp_contentProfilePageTabBackgroundColor", array(
      'label' => 'Content Profile Tabs Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentProfilePageTabBackgroundColor,
    ));


    $this->addDisplayGroup(array('sesandroidapp_contentProfilePageTabTitleColor', 'sesandroidapp_contentProfilePageTabActiveColor', 'sesandroidapp_contentProfilePageTabBackgroundColor'), 'contentprofiletabs_settings_group', array('disableLoadDefaultDecorators' => true));
    $contentprofiletabs_settings_group = $this->getDisplayGroup('contentprofiletabs_settings_group');
    $contentprofiletabs_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'contentprofiletabs_settings_group'))));


    //Start Body Styling
    $this->addElement('Dummy', 'menu_settings', array(
      'label' => 'App Plugin Menu Settings',
    ));
    $this->addElement('Text', "sesandroidapp_menuButtonTitleColor", array(
      'label' => 'Plugin Menu Button Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_menuButtonTitleColor,
    ));


    $this->addElement('Text', "sesandroidapp_menuButtonBackgroundColor", array(
      'label' => 'Plugin Menu Button Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_menuButtonBackgroundColor,
    ));

    $this->addElement('Text', "sesandroidapp_menuButtonActiveTitleColor", array(
      'label' => 'Plugin Menu Button Active Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_menuButtonActiveTitleColor,
    ));


    $this->addDisplayGroup(array('sesandroidapp_menuButtonBackgroundColor', 'sesandroidapp_menuButtonTitleColor', 'sesandroidapp_menuButtonActiveTitleColor'), 'plguinmenu_settings_group', array('disableLoadDefaultDecorators' => true));
    $plguinmenu_settings_group = $this->getDisplayGroup('plguinmenu_settings_group');
    $plguinmenu_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'plguinmenu_settings_group'))));

    //Start Body Styling
    $this->addElement('Dummy', 'tabs_settings', array(
      'label' => 'App My content tabs settings',
    ));
    $this->addElement('Text', "sesandroidapp_contentScreenTitleColor", array(
      'label' => 'Switch Tab on My Pages Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentScreenTitleColor,
    ));


    $this->addElement('Text', "sesandroidapp_contentScreenTitleBackgroundColor", array(
      'label' => 'Switch Tab on My Pages Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentScreenTitleBackgroundColor,
    ));

    $this->addElement('Text', "sesandroidapp_contentScreenActiveColor", array(
      'label' => 'Switch Tab on My Pages Title Active Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_contentScreenActiveColor,
    ));


    $this->addDisplayGroup(array('sesandroidapp_contentScreenTitleBackgroundColor', 'sesandroidapp_contentScreenTitleColor', 'sesandroidapp_contentScreenActiveColor'), 'switchtab_settings_group', array('disableLoadDefaultDecorators' => true));
    $switchtab_settings_group = $this->getDisplayGroup('switchtab_settings_group');
    $switchtab_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'switchtab_settings_group'))));


    //Start Body Styling
    $this->addElement('Dummy', 'welcome_settings', array(
      'label' => 'App Login/Welcome/Forgot Password screen settings',
    ));
    $this->addElement('Text', "sesandroidapp_outsidePlaceHolderColor", array(
      'label' => 'Place Holder Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_outsidePlaceHolderColor,
    ));


    $this->addElement('Text', "sesandroidapp_outsideTitleColor", array(
      'label' => 'Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_outsideTitleColor,
    ));

    $this->addElement('Text', "sesandroidapp_outsideButtonTitleColor", array(
      'label' => 'Button Text Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_outsideButtonTitleColor,
    ));

    $this->addElement('Text', "sesandroidapp_outsideButtonBackgroundColor", array(
      'label' => 'Button Background Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_outsideButtonBackgroundColor,
    ));
    $this->addElement('Text', "sesandroidapp_outsideNavigationTitleColor", array(
      'label' => 'Navigation Title Color',
      'allowEmpty' => $allowEmpty,
      'required' => $required,
      'class' => 'SEcolor',
      'value' => $sesandroidapp_outsideNavigationTitleColor,
    ));


    $this->addDisplayGroup(array('sesandroidapp_outsideNavigationTitleColor', 'sesandroidapp_outsidePlaceHolderColor', 'sesandroidapp_outsideTitleColor', 'sesandroidapp_outsideButtonTitleColor', 'sesandroidapp_outsideButtonBackgroundColor'), 'welcome_settings_group', array('disableLoadDefaultDecorators' => true));
    $welcome_settings_group = $this->getDisplayGroup('welcome_settings_group');
    $welcome_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'class' => 'sesandroidapp_bundle', 'id' => 'welcome_settings_group'))));



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
