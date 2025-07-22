<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetranslator
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AddCustomPhrase.php 6590 2016-07-12 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitetranslator_Form_Admin_AddCustomPhrase extends Engine_Form {

    public function init() {

        $this
                ->setTitle('Add Custom Phrase')
                ->setDescription('Here, you can translate custom phrases from one language to another. You can check the translation of the phrase and can edit it as per your requirement. [Note: English is the preferred source language for translation as it contains all the phrases.]')
                ->setAttrib('onsubmit','isTranslated(event)')
                ->setDisableTranslator(true);
        $translate = Zend_Registry::get('Zend_Translate');
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
            $targetLanguage[$value['language']] = $value['name'] . "[" . $value['language'] . "]";
        }
        $languages = engine_array_intersect_key($targetLanguage, $translate->getList());
        $this->addElement('Select', 'source_language', array(
            'label' => 'Source Language',
            'description' => 'Select the source language for the phrase which you want to translate.',
            'multiOptions' => $languages,
            'value' => 'en',
            'onchange' =>'(this.value == $("target_language").value) ? $("translate_button").style.display="none" : $("translate_button").style.display="block"; '
        ));
        $this->addElement('Textarea', 'sitetranslator_phrase_key', array(
            'label' => 'Phrase',
            'required' => true,
            'description' => 'Enter the phrase which you want to translate.',
        ));
        $this->sitetranslator_phrase_key->addDecorator('Description', array('placement' => 'PREPEND', 'class' => 'description', 'escape' => false));
        $this->addElement('Select', 'target_language', array(
            'label' => 'Target Language',
            'description' => 'Select the language in which you want to translate above phrase.',
            'multiOptions' => $languages,
            'onchange' =>'$("sitetranslator_phrase_value").value=""; (this.value == $("source_language").value) ? $("translate_button").style.display="none" : $("translate_button").style.display="block";'
        ));

        $this->addElement('Textarea', 'sitetranslator_phrase_value', array(
            'label' => 'Translated Phrase',
            'required' => true,
            'onKeyUp'=>'!this.value ? $("submit").style = "background-color:#7a7a7a;" : $("submit").style = "background-color:#619dbe;"',
            'description' => 'Below is the translated version of above entered phrase. You can edit the translated phrase as per your requirement.',
        ));
        $this->sitetranslator_phrase_value->addDecorator('Description', array('placement' => 'PREPEND', 'class' => 'description', 'escape' => false));
        
        $this->addElement('Button', 'submit', array(
            'label' => 'Add Phrase',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
