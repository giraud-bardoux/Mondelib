<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetranslator
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Translator.php 6590 2016-07-12 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitetranslator_Form_Admin_Translator extends Engine_Form {

    public function init() {
        $this
                
                ->setDescription('Here, you can translate any file from one language to any other language. You can also translate and overwrite any file (after any new changes) which has been already translated.')
                ->setAttrib('id', "translator_form")
                ->setAttrib('onsubmit', "showProgressBar(this)")
                ->setDisableTranslator(true)
                ;
        $this->loadDefaultDecorators();
	$this->getDecorator('Description')->setOption('escape', false);
        $translate = Zend_Registry::get('Zend_Translate');
        $source = Zend_Controller_Front::getInstance()->getRequest()->getParam('source');
        if (empty($source)) {
            $source = 'en';
        }

        $apiKey = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitetranslator.google.api.key');
        $url = 'https://translation.googleapis.com/language/translate/v2/languages?key=' . $apiKey . "&target=en";
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $responseDecoded = json_decode($response, true);
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);      //Here we fetch the HTTP response code
        curl_close($handle);

        $targetLanguage = array();
        foreach ($responseDecoded['data']['languages'] as $key => $value) {
            $targetLanguage[$value['language']] = $value['name'] . " [" . $value['language'] . "]";
        }

        $this->addElement('Select', 'source_language', array(
            'label' => 'Source Language',
            'description' => 'Select the source language from which you want to translate the below listed files.',
            'multiOptions' => engine_array_intersect_key($targetLanguage, $translate->getList()),
            'value' => $source,
            'required' => true,
            'onchange' => 'changeSoucreFiles(this.value)'
        ));

        $folder = APPLICATION_PATH . "/application/languages/$source" . DIRECTORY_SEPARATOR;
        $sourceFiles = glob($folder . '*.csv');
        $sourceFileOptions = engine_array_map(function($file) {
            return substr($file, strripos($file, '/') + 1);
        }, $sourceFiles);
        $sourceFileOptions = array_merge(array('all'), $sourceFileOptions);

        $this->addElement('MultiCheckbox', 'sitetranslator_csv_files', array(
            'description' => 'Select the files which you want to translate in another language.',
            'label' => 'Select Files',
            'required' => true,
            'multiOptions' => engine_array_combine($sourceFileOptions, $sourceFileOptions)
        ));


        $this->addElement('MultiCheckbox', 'target_language', array(
            'label' => 'Target Language',
            'required' => true,
            'description' => 'Select the language in which you want to translate above selected files.',
            'multiOptions' => array_merge(array('all' => 'all'), $targetLanguage),
        ));

        $this->addElement('Radio', 'overwrite', array(
            'label' => 'Overwrite the Translated Files',
            'description' => 'Do you want to overwrite the existing translated files?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1,
        ));
        $this->addElement('Button', 'submit', array(
            'label' => 'Generate',
            'type' => 'submit',
            //'onclick' => 'showProgressBar()',
            'ignore' => true
        ));
    }

}
