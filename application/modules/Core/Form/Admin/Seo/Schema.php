<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Schema.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_Schema extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Schema Markup')
        ->setDescription('You can enter information for schema markup that you want to show for rich content of your website in Search Engine Result Pages (SERP).');

    $this->addElement('Radio', "coreseo_schema_type", array(
      'label' => 'Schema Type',
      'description' => "Select schema type.",
      'multiOptions' => array(
        '1' => 'Website',
        '3' => "Custom",
      ),
      'allowEmpty' => false,
      'required' => true,
      'onchange' => 'hideside(this.value)',
      'value' => $settings->getSetting('coreseo_schema_type', 1),
    ));

    $this->addElement('Text', "coreseo_sitetitle", array(
      'label' => 'Site Title',
      'description' => "Enter Site Title.",
      'allowEmpty' => false,
      'required' => true,
      'value' => $settings->getSetting('coreseo.sitetitle', Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title')),
    ));

    $this->addElement('Text', "coreseo_alternatetitle", array(
      'label' => 'Website Alternate Title',
      'description' => "Enter Website Alternate Title.",
      'value' => $settings->getSetting('coreseo.alternatetitle', ''),
    ));

    $this->addElement('Text', "coreseo_facebook", array(
      'label' => 'Facebook URL',
      'description' => "Enter URL of the Facebook for your website.",
      'value' => $settings->getSetting('coreseo.facebook', ''),
    ));

    $this->addElement('Text', "coreseo_twitter", array(
      'label' => 'X URL',
      'description' => "Enter URL of the X for your website.",
      'value' => $settings->getSetting('coreseo.twitter', ''),
    ));

    $this->addElement('Text', "coreseo_linkedin", array(
      'label' => 'LinkedIn URL',
      'description' => "Enter URL of the LinkedIn for your website.",
      'value' => $settings->getSetting('coreseo.linkedin', ''),
    ));

    $this->addElement('Text', "coreseo_instagram", array(
      'label' => 'Instagram URL',
      'description' => "Enter URL of the Instagram for your website.",
      'value' => $settings->getSetting('coreseo.instagram', ''),
    ));

    $this->addElement('Text', "coreseo_youtube", array(
      'label' => 'YouTube URL',
      'description' => "Enter URL of the YouTube for your website.",
      'value' => $settings->getSetting('coreseo.youtube', ''),
    ));

    $this->addElement('Textarea', "coreseo_othermediaurl", array(
      'label' => 'Other SocialMedia URL',
      'description' => "Enter URL of other social media for your website.",
      'value' => $settings->getSetting('coreseo.othermediaurl', ''),
    ));

    $this->addElement('Textarea', "coreseo_customschema", array(
      'label' => 'Custom Schema Markup',
      'description' => "Enter the Custom Schema Markup you want to enter for your website in json-ld format. [Note: You need not to include script tags, you can just add the json code.]",
      'value' => $settings->getSetting('coreseo.customschema', ''),
    ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
