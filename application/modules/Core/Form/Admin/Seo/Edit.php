<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_Edit extends Engine_Form {

  public function init() {

    $page_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('page_id', 0);
    $corePageItem = Engine_Api::_()->getItem('core_page', $page_id);

    $moreinfo = $this->getTranslator()->translate($corePageItem->title);

    $title = $this->getTranslator()->translate('Edit Meta Tags For %1$s ');
    $title = vsprintf($title, array($moreinfo));

    $description = $this->getTranslator()->translate('Here, you can edit the meta tags information for the %1$s.');
    $description = vsprintf($description, array($moreinfo));
    

    $this->setTitle($title);

    $this->setDescription($description);

    $this->addElement('Text', "title", array(
      'label' => 'Title',
      'description' => "Enter Title for this page. This title will show up in title tag.",
      'allowEmpty' => false,
      'required' => true,
    ));

    $this->addElement('Textarea', "description", array(
      'label' => 'Description',
      'description' => "Enter meta Description for this page. This description will show up description tag of this page.",
      'maxlength' => '300',
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '300')),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    $this->addElement('Textarea', "keywords", array(
      'label' => 'Keywords',
      'description' => "Enter meta keywords for this page. You can add multiple tags separated by comma.",
      'maxlength' => '300',
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '300')),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    $this->addElement('Textarea', "meta_tags", array(
      'label' => 'Additional Meta Tags',
      'description' => 'Enter more meta tags that you want to add for this page. Example : <meta name="yourwebsite" content="your website name">',
    ));

    $this->addElement('Select', 'roboto_tags', array(
      'label' => 'Robot Tag',
      'description' => 'INDEX – a command for the search engine crawler to index that webpage and FOLLOW – a command for the search engine crawler to follow the links in that webpage.',
      'multiOptions' => array(
        '1' => 'Index, Follow',
        '2' => 'Index, Nofollow',
        '3' => 'Noindex, Follow',
        '4' => 'Noindex, Nofollow',
      ),
    ));
    
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    if(engine_count($files) > 1) {
      $description = $this->getTranslator()->translate('Choose from below the Meta Image for this page. This image will show up when this page from website is shared on Search Engine. [Note: You can add a new photo from the "File & Media Manager" section from here: <a href="%1$s" target="_blank">File & Media Manager.</a> Leave the field blank if you do not want to show image.]');
      $description = vsprintf($description, array($fileLink));
        
      $this->addElement('Select', 'meta_image', array(
        'label' => 'Meta Image',
        'description' => $description,
        'escape' => false,
        'multiOptions' => $files,
      ));
      $this->meta_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'meta_image', array(
        'label' => 'Meta Image',
        'description' => $description,
      ));
      $this->meta_image->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $this->addElement('Button', 'execute', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));
    
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'prependText' => ' or ',
        'ignore' => true,
        'link' => true,
        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'managemetakeywords')),
        'decorators' => array('ViewHelper'),
    ));
  }
}
