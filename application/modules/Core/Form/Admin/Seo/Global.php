<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Seo.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_Global extends Authorization_Form_Admin_Level_Abstract {

  public function init() {
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    
    $this->setDescription("These settings affect all members in your community.");
    
    // init site title
    $this->addElement('Text', 'core_general_site_title', array(
      'label' => 'Site Title',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITETITLE_DESCRIPTION',
      'value' => $settings->getSetting('core.general.site.title', 'My Community'),
    ));
    $this->core_general_site_title->getDecorator('Description')->setOption('placement', 'append');

    // init site description
    $this->addElement('Textarea', 'core_general_site_description', array(
      'label' => 'Site Description',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITEDESCRIPTION_DESCRIPTION',
      'value' => $settings->getSetting('core.general.site.description'),
    ));
    $this->core_general_site_description->getDecorator('Description')->setOption('placement', 'append');

    // init site keywords
    $this->addElement('Textarea', 'core_general_site_keywords', array(
      'label' => 'Site Keywords',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_SITEKEYWORDS_DESCRIPTION',
      'value' => $settings->getSetting('core.general.site.keywords'),
    ));
    $this->core_general_site_keywords->getDecorator('Description')->setOption('placement', 'append');
    
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    if(engine_count($files) > 1) {
      
      $description = $this->getTranslator()->translate('Choose from below the Meta Image which will be used when content shared from your website to other social networking services does not have any image. [Note: You can add a new photo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager</a>. Leave the field blank if you do not want to show image.]');
      $description = vsprintf($description, array($fileLink));
      
      $this->addElement('Select', 'core_general_nonmeta_photo', array(
        'label' => 'Meta Image',
        'description' => $description,
        'multiOptions' => $files,
        'allowEmpty' => true,
        'required' => false,
        'escape' => false,
        'value' => $settings->getSetting('core.general.nonmeta.photo', ''),
      ));
      $this->core_general_nonmeta_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'core_general_nonmeta_photo', array(
        'label' => 'Meta Image',
        'description' => $description,
      ));
      $this->core_general_nonmeta_photo->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
