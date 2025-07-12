<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Styling.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Settings_Styling extends Engine_Form {

  public function init() {
    
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    $this->setTitle('Manage Color Schemes')
        ->setDescription("Here, you can manage the color schemes of your website.");
        
    $this->addElement('Dummy', 'color_tip', array(
      'content' => "<div class='tip'><span>".$this->getTranslator()->translate('Once you switch color schemes or make any changes to the new color schemes you added, please change the mode of your website from Production to Development. This has to be done everytime, and you can switch to production instantly or as soon you are done configuring the color scheme of your website.')."</span></div>",
    ));

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $api = Engine_Api::_()->core();

    $contrast_mode = $settings->getSetting('contrast.mode', 'dark_mode');
    $theme_color = $settings->getSetting('harmony.theme.color', 1);

    $this->addElement('Radio', 'contrast_mode', array(
      'label' => 'Contrast Mode?',
      'description' => 'Choose the Contrast mode for the accessibility widget on your website. You can choose Dark Mode or Light Mode as per the default theme on your website.',
      'multiOptions' => array(
        'light_mode' => 'Light Mode',
        'dark_mode' => 'Dark Mode'
      ),
      'value'=>$contrast_mode
    ));

    $customThemes = Engine_Api::_()->getDbTable('customthemes', 'harmony')->getCustomThemes(array('all' => 1));
    foreach($customThemes as $customTheme) {
      if(engine_in_array($customTheme['theme_id'], array(1,2,3))) {
        $themeOptions[$customTheme['theme_id']] = '<img src="./application/modules/Harmony/externals/images/color-scheme/'.$customTheme['theme_id'].'.png" alt="" />';
      } else {
        $themeOptions[$customTheme['theme_id']] = '<img src="./application/modules/Harmony/externals/images/color-scheme/custom.png" alt="" /> <span class="custom_theme_name">'. $customTheme->name.'</span>';
      }
    }

    $this->addElement('Radio', 'theme_color', array(
      'label' => 'Color Schemes',
      'multiOptions' => $themeOptions,
      'onclick' => 'changeThemeColor(this.value, "")',
      'escape' => false,
      'value' => $theme_color,
    ));

    $this->addElement('dummy', 'custom_themes', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/Harmony/views/scripts/custom_themes.tpl',
        'class' => 'form element',
      )))
    ));

    $themecustom = Engine_Api::_()->getDbTable('customthemes', 'harmony')->getThemeKey(array('theme_id'=>$theme_color, 'default' => 0));
    foreach($themecustom as $value) {
      ${$value['column_key']} = $value['value'];
    }

    $this->addElement('Dummy', 'header_settings', array(
        'label' => 'Header Styling Settings',
    ));
    $this->addElement('Text', "harmony_header_background_color", array(
        'label' => 'Header Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_header_background_color,
    ));

    $this->addElement('Text', "harmony_mainmenu_background_color", array(
        'label' => 'Main Menu Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_mainmenu_background_color,
    ));

    $this->addElement('Text', "harmony_mainmenu_links_color", array(
        'label' => 'Main Menu Link Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_mainmenu_links_color,
    ));

    $this->addElement('Text', "harmony_mainmenu_links_hover_color", array(
        'label' => 'Main Menu Link Hover Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_mainmenu_links_hover_color,
    ));
    $this->addElement('Text', "harmony_minimenu_search_background_color", array(
      'label' => 'Header Search Background Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_minimenu_search_background_color,
    ));
     $this->addElement('Text', "harmony_minimenu_search_font_color", array(
      'label' => 'Header Search Font Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_minimenu_search_font_color,
    ));   
    $this->addDisplayGroup(array('harmony_header_background_color', 'harmony_mainmenu_background_color', 'harmony_mainmenu_links_color', 'harmony_mainmenu_links_hover_color', 'harmony_minimenu_search_background_color','harmony_minimenu_search_font_color'), 'header_settings_group', array('disableLoadDefaultDecorators' => true));
    $header_settings_group = $this->getDisplayGroup('header_settings_group');
    $header_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'header_settings_group'))));

    $this->addElement('Dummy', 'footer_settings', array(
        'label' => 'Footer Styling Settings',
    ));
    $this->addElement('Text', "harmony_footer_background_color", array(
        'label' => 'Footer Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_footer_background_color,
    ));

    $this->addElement('Text', "harmony_footer_font_color", array(
        'label' => 'Footer Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_footer_font_color,
    ));

    $this->addElement('Text', "harmony_footer_links_color", array(
        'label' => 'Footer Link Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_footer_links_color,
    ));

    $this->addElement('Text', "harmony_footer_border_color", array(
        'label' => 'Footer Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_footer_border_color,
    ));
    $this->addDisplayGroup(array('harmony_footer_background_color', 'harmony_footer_font_color', 'harmony_footer_links_color', 'harmony_footer_border_color'), 'footer_settings_group', array('disableLoadDefaultDecorators' => true));
    $footer_settings_group = $this->getDisplayGroup('footer_settings_group');
    $footer_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'footer_settings_group'))));

    $this->addElement('Dummy', 'body_settings', array(
        'label' => 'Body Styling Settings',
    ));
    $this->addElement('Text', "harmony_theme_color", array(
        'label' => 'Theme Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_theme_color,
    ));

    $this->addElement('Text', "harmony_body_background_color", array(
        'label' => 'Body Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_body_background_color,
    ));

    $this->addElement('Text', "harmony_font_color", array(
        'label' => 'Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_font_color,
    ));

    $this->addElement('Text', "harmony_font_color_light", array(
        'label' => 'Font Light Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_font_color_light,
    ));

    $this->addElement('Text', "harmony_links_color", array(
      'label' => 'Link Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_links_color,
    ));

    $this->addElement('Text', "harmony_links_hover_color", array(
        'label' => 'Link Hover Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_links_hover_color,
    ));

    $this->addElement('Text', "harmony_headline_color", array(
        'label' => 'Headline Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_headline_color,
    ));

    $this->addElement('Text', "harmony_border_color", array(
        'label' => 'Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_border_color,
    ));
    $this->addElement('Text', "harmony_box_background_color", array(
        'label' => 'Box Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_box_background_color,
    ));
    $this->addElement('Text', "harmony_box_background_color_alt", array(
        'label' => 'Box Background Alt Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_box_background_color_alt,
    ));
    $this->addElement('Text', "harmony_input_background_color", array(
        'label' => 'Input Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_input_background_color,
    ));
    $this->addElement('Text', "harmony_input_font_color", array(
        'label' => 'Input Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_input_font_color,
    ));
    $this->addElement('Text', "harmony_input_border_color", array(
        'label' => 'Input Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_input_border_color,
    ));
   $this->addElement('Text', "harmony_button_background_color", array(
        'label' => 'Button Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_button_background_color,
    ));
    $this->addElement('Text', "harmony_button_background_color_hover", array(
        'label' => 'Button Background Hovor Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_button_background_color_hover,
    ));
    $this->addElement('Text', "harmony_button_font_color", array(
        'label' => 'Button Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_button_font_color,
    ));
    $this->addElement('Text', "harmony_button_border_color", array(
        'label' => 'Button Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $harmony_button_border_color,
    ));
    $this->addElement('Text', "harmony_button_font_color_hover", array(
      'label' => 'Button Font Hover Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_button_font_color_hover,
    ));
    $this->addElement('Text', "harmony_button_border_color_hover", array(
      'label' => 'Button Border Hover Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_button_border_color_hover,
    ));
    $this->addElement('Text', "harmony_secondary_button_background_color", array(
      'label' => 'Secondary Button Background Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_background_color,
    ));
    $this->addElement('Text', "harmony_secondary_button_background_color_hover", array(
      'label' => 'Secondary Button Background Hover Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_background_color_hover,
    ));
    $this->addElement('Text', "harmony_secondary_button_font_color", array(
      'label' => 'Secondary Button Font Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_font_color,
    ));
    $this->addElement('Text', "harmony_secondary_button_font_color_hover", array(
      'label' => 'Secondary Button Font Hover Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_font_color_hover,
    ));
    $this->addElement('Text', "harmony_secondary_button_border_color", array(
      'label' => 'Secondary Button Border Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_border_color,
    ));
    $this->addElement('Text', "harmony_secondary_button_border_color_hover", array(
      'label' => 'Secondary Button Border Hover Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_secondary_button_border_color_hover,
    ));
    $this->addElement('Text', "harmony_comments_background_color", array(
      'label' => 'Comments Background Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $harmony_comments_background_color,
    ));

    $this->addDisplayGroup(array('harmony_theme_color','harmony_body_background_color', 'harmony_font_color', 'harmony_font_color_light', 'harmony_links_color', 'harmony_links_hover_color','harmony_headline_color', 'harmony_border_color', 'harmony_box_background_color','harmony_box_background_color_alt', 'harmony_input_background_color', 'harmony_input_font_color', 'harmony_input_border_color', 'harmony_button_background_color','harmony_button_background_color_hover','harmony_button_font_color','harmony_button_font_color_hover','harmony_button_border_color','harmony_button_border_color_hover','harmony_secondary_button_background_color','harmony_secondary_button_background_color_hover','harmony_secondary_button_font_color','harmony_secondary_button_font_color','harmony_secondary_button_font_color_hover','harmony_secondary_button_border_color','harmony_secondary_button_border_color_hover','harmony_dashboard_list_background_color_hover', 'harmony_dashboard_list_border_color', 'harmony_dashboard_font_color', 'harmony_dashboard_link_color', 'harmony_comments_background_color'), 'body_settings_group', array('disableLoadDefaultDecorators' => true));
    $body_settings_group = $this->getDisplayGroup('body_settings_group');
    $body_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'body_settings_group'))));

    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
//     $this->addElement('Button', 'submit', array(
//         'label' => 'Save as Draft',
//         'type' => 'submit',
//         'ignore' => true,
//         'decorators' => array('ViewHelper')
//     ));
//     $this->addDisplayGroup(array('save', 'submit'), 'buttons');
  }
}
