<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Fonts.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Form_Admin_Settings_Fonts extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Manage Fonts')
        ->setDescription('Here, you can configure the font settings for this theme on your website. You can also choose to enable the Google Fonts.');
            
    $googleFontArray = Engine_Api::_()->core()->getGoogleFonts('fontfamily');

    $this->addElement('Select', 'harmony_googlefonts', array(
      'label' => 'Choose Fonts',
      'description' => 'Choose from below the Fonts which you want to enable in this theme.',
      'multiOptions' => array(
        '0' => 'Web Safe Font Combinations',
        '1' => 'Google Fonts',
      ),
      'onchange' => "usegooglefont(this.value)",
      'value' => $settings->getSetting('harmony.googlefonts', 0),
    ));
    
    $font_array = array(
      'Default Font' => 'Default Font',
      'Georgia, serif' => 'Georgia, serif',
      '"Palatino Linotype", "Book Antiqua", Palatino, serif' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
      '"Times New Roman", Times, serif' => '"Times New Roman", Times, serif',
      'Arial, Helvetica, sans-serif' => 'Arial, Helvetica, sans-serif',
      '"Arial Black", Gadget, sans-serif' => '"Arial Black", Gadget, sans-serif',
      '"Comic Sans MS", cursive, sans-serif' => '"Comic Sans MS", cursive, sans-serif',
      'Impact, Charcoal, sans-serif' => 'Impact, Charcoal, sans-serif',
      '"Lucida Sans Unicode", "Lucida Grande", sans-serif' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
      'Tahoma, Geneva, sans-serif' => 'Tahoma, Geneva, sans-serif',
      '"Trebuchet MS", Helvetica, sans-serif' => '"Trebuchet MS", Helvetica, sans-serif',
      'Verdana, Geneva, sans-serif' => 'Verdana, Geneva, sans-serif',
      '"Courier New", Courier, monospace' => '"Courier New", Courier, monospace',
      '"Lucida Console", Monaco, monospace' => '"Lucida Console", Monaco, monospace',
    );
    
    //Body Settings
    $this->addElement('Select', 'harmony_body_fontfamily', array(
      'label' => 'Body - Font Family',
      'description' => "Choose font family for the text under Body Styling.",
      'multiOptions' => $font_array,
      'value' => $settings->getSetting('harmony_body_fontfamily'),
    ));
    $this->getElement('harmony_body_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    $this->addElement('Text', 'harmony_body_fontsize', array(
      'label' => 'Body - Font Size',
      'description' => 'Enter the font size for the text under Body Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_body_fontsize', '.85rem'),
    ));
    $this->getElement('harmony_body_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_body_fontfamily', 'harmony_body_fontsize'), 'harmony_bodygrp', array('disableLoadDefaultDecorators' => true));
    $harmony_bodygrp = $this->getDisplayGroup('harmony_bodygrp');
    $harmony_bodygrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_bodygrp'))));

    //Google Font work
    $this->addElement('Select', 'harmony_googlebody_fontfamily', array(
      'label' => 'Body - Font Family',
      'description' => "Choose font family for the text under Body Styling.",
      'multiOptions' => $googleFontArray,
      'value' => $settings->getSetting('harmony_googlebody_fontfamily'),
    ));
    $this->getElement('harmony_googlebody_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addElement('Text', 'harmony_googlebody_fontsize', array(
      'label' => 'Body - Font Size',
      'description' => 'Enter the font size for the text under Body Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_googlebody_fontsize','.85rem'),
    ));
    $this->getElement('harmony_googlebody_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_googlebody_fontfamily', 'harmony_googlebody_fontsize'), 'harmony_googlebodygrp', array('disableLoadDefaultDecorators' => true));
    $harmony_googlebodygrp = $this->getDisplayGroup('harmony_googlebodygrp');
    $harmony_googlebodygrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_googlebodygrp'))));
    

    //Heading Settings
    $this->addElement('Select', 'harmony_heading_fontfamily', array(
      'label' => 'Heading - Font Family',
      'description' => "Choose font family for the text under Heading Styling.",
      'multiOptions' => $font_array,
      'value' => $settings->getSetting('harmony_heading_fontfamily'),
    ));
    $this->getElement('harmony_heading_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    $this->addElement('Text', 'harmony_heading_fontsize', array(
      'label' => 'Heading - Font Size',
      'description' => 'Enter the font size for the text under Heading Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_heading_fontsize', '1.1rem'),
    ));
    $this->getElement('harmony_heading_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_heading_fontfamily', 'harmony_heading_fontsize'), 'harmony_headinggrp', array('disableLoadDefaultDecorators' => true));
    $harmony_headinggrp = $this->getDisplayGroup('harmony_headinggrp');
    $harmony_headinggrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_headinggrp'))));
    
    
    //Google Font work
    $this->addElement('Select', 'harmony_googleheading_fontfamily', array(
      'label' => 'Heading - Font Family',
      'description' => "Choose font family for the text under Heading Styling.",
      'multiOptions' => $googleFontArray,
      'value' => $settings->getSetting('harmony_googleheading_fontfamily'),
    ));
    $this->getElement('harmony_googleheading_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addElement('Text', 'harmony_googleheading_fontsize', array(
      'label' => 'Heading - Font Size',
      'description' => 'Enter the font size for the text under Heading Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_googleheading_fontsize', '1.1rem'),
    ));
    $this->getElement('harmony_googleheading_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_googleheading_fontfamily', 'harmony_googleheading_fontsize'), 'harmony_googleheadinggrp', array('disableLoadDefaultDecorators' => true));
    $harmony_googleheadinggrp = $this->getDisplayGroup('harmony_googleheadinggrp');
    $harmony_googleheadinggrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_googleheadinggrp'))));

    //Main Menu Settings
    $this->addElement('Select', 'harmony_mainmenu_fontfamily', array(
      'label' => 'Main Menu - Font Family',
      'description' => "Choose font family for the text under Main Menu Styling.",
      'multiOptions' => $font_array,
      'value' => $settings->getSetting('harmony_mainmenu_fontfamily'),
    ));
    $this->getElement('harmony_mainmenu_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
            
    $this->addElement('Text', 'harmony_mainmenu_fontsize', array(
      'label' => 'Main Menu - Font Size',
      'description' => 'Enter the font size for the text under Main Menu Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_mainmenu_fontsize', '.8rem'),
    ));
    $this->getElement('harmony_mainmenu_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_mainmenu_fontfamily', 'harmony_mainmenu_fontsize'), 'harmony_mainmenugrp', array('disableLoadDefaultDecorators' => true));
    $harmony_mainmenugrp = $this->getDisplayGroup('harmony_mainmenugrp');
    $harmony_mainmenugrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_mainmenugrp'))));
    
    //Google Font work
    $this->addElement('Select', 'harmony_googlemainmenu_fontfamily', array(
      'label' => 'Main Menu - Font Family',
      'description' => "Choose font family for the text under Main Menu Styling.",
      'multiOptions' => $googleFontArray,
      'value' => $settings->getSetting('harmony_googlemainmenu_fontfamily'),
    ));
    $this->getElement('harmony_googlemainmenu_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addElement('Text', 'harmony_googlemainmenu_fontsize', array(
      'label' => 'Main Menu - Font Size',
      'description' => 'Enter the font size for the text under Main Menu Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_googlemainmenu_fontsize', '.8rem'),
    ));
    $this->getElement('harmony_googlemainmenu_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_googlemainmenu_fontfamily', 'harmony_googlemainmenu_fontsize'), 'harmony_googlemainmenugrp', array('disableLoadDefaultDecorators' => true));
    $harmony_googlemainmenugrp = $this->getDisplayGroup('harmony_googlemainmenugrp');
    $harmony_googlemainmenugrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_googlemainmenugrp'))));
    

    //Tab Settings
    $this->addElement('Select', 'harmony_tab_fontfamily', array(
      'label' => 'Tab - Font Family',
      'description' => "Choose font family for the text under Tab Styling.",
      'multiOptions' => $font_array,
      'value' => $settings->getSetting('harmony_tab_fontfamily'),
    ));
    $this->getElement('harmony_tab_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    $this->addElement('Text', 'harmony_tab_fontsize', array(
      'label' => 'Tab - Font Size',
      'description' => 'Enter the font size for the text under Tab Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_tab_fontsize', '1em'),
    ));
    $this->getElement('harmony_tab_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_tab_fontfamily', 'harmony_tab_fontsize'), 'harmony_tabgrp', array('disableLoadDefaultDecorators' => true));
    $harmony_tabgrp = $this->getDisplayGroup('harmony_tabgrp');
    $harmony_tabgrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_tabgrp'))));
    
    
    //Google Font work
    $this->addElement('Select', 'harmony_googletab_fontfamily', array(
      'label' => 'Tab - Font Family',
      'description' => "Choose font family for the text under Tab Styling.",
      'multiOptions' => $googleFontArray,
      'value' => $settings->getSetting('harmony_googletab_fontfamily'),
    ));
    $this->getElement('harmony_googletab_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addElement('Text', 'harmony_googletab_fontsize', array(
      'label' => 'Tab - Font Size',
      'description' => 'Enter the font size for the text under Tab Styling.',
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('harmony_tab_fontsize', '1em'),
    ));
    $this->getElement('harmony_googletab_fontsize')->getDecorator('Description')->setOption('escape',false); 
    
    $this->addDisplayGroup(array('harmony_googletab_fontfamily', 'harmony_googletab_fontsize'), 'harmony_googletabgrp', array('disableLoadDefaultDecorators' => true));
    $harmony_googletabgrp = $this->getDisplayGroup('harmony_googletabgrp');
    $harmony_googletabgrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'harmony_googletabgrp'))));

    
    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
