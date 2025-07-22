<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitetranslator
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Global.php 6590 2016-07-12 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitetranslator_Form_Admin_Global extends Engine_Form {

    protected $_variables;

    protected function setVariables($variables) {
        $this->_variables = $variables;
    } 

    public function init() {

        $this
                //->setTitle('Global Settings')
                ->setDescription('These settings affect all members in your community.')
                ->setDisableTranslator(true);

        $settings = Engine_Api::_()->getApi('settings', 'core');
        $view = Zend_Registry::get('Zend_View');
        $faqLink = '<a href="' . $view->baseUrl() . '/admin/sitetranslator/translator/support" title="Go to Support"  target="_blank">click here</a> ';
        $this->addElement('Text', 'sitetranslator_google_api_key', array(
            'label' => 'Google Translator API Key',
            'description' => 'Please enter your Google Translator API Key.  To know how to generate and configure this, please ' . $faqLink,
            'value' => $settings->getSetting('sitetranslator.google.api.key', ''),
        ));
        $this->sitetranslator_google_api_key->addDecorator('Description', array('placement' => 'PREPEND', 'class' => 'description', 'escape' => false));
        
        $this->addElement('Text', 'sitetranslator_google_api_character_limit', array(
            'label' => 'Character Limit',
            'required' => true,
            'description' => 'Please enter Google API character translation limit for per 100 second. You can enter the maximum character limit you have in your Google account. Entering more characters here will speed up your translation process.
                                [Note: Minimum character limit should be 100000.]',
            'value' => $settings->getSetting('sitetranslator.google.api.character.limit', 100000),
        ));

        $this->sitetranslator_google_api_character_limit->getDecorator('Description')->setOption('escape', false);
        $this->addElement('Radio', 'sitetranslator_content_translator_widget_enable', array(
            'label' => 'Content Translation',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            //'onchange' => 'hideShowTooltip(this.value)',
            'description' => 'Do you want to enable content translation on your website?',
            'value' => $settings->getSetting('sitetranslator.content.translator.widget.enable', 0),
        ));
        $this->sitetranslator_content_translator_widget_enable->addDecorator('Description', array('placement' => 'PREPEND', 'class' => 'description', 'escape' => false));
        $faqLink =  '<a href="'.$view->baseUrl().'/admin/sitetranslator/settings/faqs/showFAQ/faq_11" title="Go to FAQ"  target="_blank"> click here </a> ';
        $this->addElement('Textarea', 'sitetranslator_variables', array(
            'label' => 'Special Variables',
            'required' => true,
            'description' => 'Please enter the special variables using comma (,) as separator. Special variables are the strings which you want to ignore during translation process, for example: your site’s name, email signature etc.
            [Note: Please don’t remove already added special variables. In case you have lost the list of special variables then please'.$faqLink.'.]',
            'value' => join(",",array_keys($this->_variables)),
        ));
        $this->sitetranslator_variables->getDecorator('Description')->setOption('escape', false);

        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true
        ));
    }

}
