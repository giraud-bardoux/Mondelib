<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetranslator
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Phrases.php 6590 2016-07-12 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitetranslator_Form_Admin_Phrases extends Engine_Form {

    public function init() {

         $this
                ->clearDecorators()
                ->addDecorator('FormElements')
                ->addDecorator('Form')
                ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
                ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
        ;

        $this
                ->setAttribs(array(
                    'id' => 'filter_form',
                    'class' => 'global_form_box',
                ))
                ->setMethod('GET')
                ->setDisableTranslator(true);
                
        $view = Zend_Registry::get('Zend_View');
        $translate = Zend_Registry::get('Zend_Translate');
        $source = Zend_Controller_Front::getInstance()->getRequest()->getParam('source_language');
        if (empty($source)) {
            $source = 'en';
        }

        $target = Zend_Controller_Front::getInstance()->getRequest()->getParam('target_language');
        if (empty($target)) {
            $target = 'en';
        }

        $languageList = $translate->getList();
    $localeObject = Zend_Registry::get('Locale');
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( !engine_in_array($defaultLanguage, $languageList) ) {
      if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }
    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach( $languageList as $key ) {
      $languageName = null;
      if( !empty($languages[$key]) ) {
        $languageName = $languages[$key];
      } else {
        $tmpLocale = new Zend_Locale($key);
        $region = $tmpLocale->getRegion();
        $language = $tmpLocale->getLanguage();
        if( !empty($languages[$language]) && !empty($territories[$region]) ) {
          $languageName = $languages[$language] . ' (' . $territories[$region] . ')';
        }
      }

      if( $languageName ) {
        $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
      } else {
        $localeMultiOptions[$key] = $view->translate('Unknown') . ' [' . $key . ']';
      }
    }
    $localeMultiOptions = array_merge(array($defaultLanguage => $defaultLanguage), $localeMultiOptions);

    $this->addElement('Select', 'source_language', array(
            'label' => 'Source Language',
            'description' => 'Select the source language to which you want to translate?',
            'multiOptions' => $localeMultiOptions,
            'value' => $source,
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => null, 'placement' => 'PREPEND')),
                array('HtmlTag', array('tag' => 'div')),
            ),
          //  'onchange' => 'changeLanguages(this.value,document.getElementById("target_language").value,document.getElementById("csv_files").value)'
        ));
        $this->addElement('Select', 'target_language', array(
            'label' => 'Target Language',
            'description' => 'Which language do you want to create a language pack for?',
            'multiOptions' => $localeMultiOptions,
            'value' => $target,
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => null, 'placement' => 'PREPEND')),
                array('HtmlTag', array('tag' => 'div')),
            ),
         //   'onchange' => 'changeLanguages(document.getElementById("source_language").value,this.value,document.getElementById("csv_files").value)'
        ));
        $folder = APPLICATION_PATH . "/application/languages/$source" . DIRECTORY_SEPARATOR;
        $sourceFiles = glob($folder . '*.csv');
        $sourceFileOptions = engine_array_map(function($file) {
            return substr($file, strripos($file, '/') + 1);
        }, $sourceFiles);
        $sourceFileOptions = array_merge(array('all'), $sourceFileOptions);
        $this->addElement('Select', 'csv_files', array(
            'label' => 'Files',
            'description' => 'Select the particular file to see phrase diffrences.',
            'multiOptions' => engine_array_combine($sourceFileOptions,$sourceFileOptions),
            'value' => 'all',
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => null, 'placement' => 'PREPEND')),
                array('HtmlTag', array('tag' => 'div')),
            ),
           // 'onchange' => 'changeLanguages(document.getElementById("source_language").value,document.getElementById("target_language").value,this.value)'
        ));
        
        $this->addElement('Button', 'submit', array(
            'label' => 'Filter',
            'type' => 'submit',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div')),
            ),
            'class' =>'sitetranslator_class',
            'ignore' => true
        ));
    }

}
