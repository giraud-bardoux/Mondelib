<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: EditSettings.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_EditSettings extends Engine_Form {

  public function init() {

    $sitemap_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('sitemap_id', 0);
    $content = Engine_Api::_()->getItem('core_sitemap', $sitemap_id);

    $moreinfo = $this->getTranslator()->translate($content->title);
    
    $title = $this->getTranslator()->translate('Edit Setting For %1$s ');
    $title = vsprintf($title, array($moreinfo));

    $description = $this->getTranslator()->translate('Here, you can edit the setting for the %1$s.');
    $description = vsprintf($description, array($moreinfo));

    $this->setTitle($title)->setAttrib('class', 'global_form_popup');
    $this->setDescription($description);

    $this->addElement('Select', 'frequency', array(
      'label' => 'Frequency',
      'description' => 'Choose the frequency of this content.',
      'allowEmpty' => false,
      'multiOptions' => array(
        'always' => 'Always' ,
        'hourly' => 'Hourly' ,
        'daily' => 'Daily' ,
        'weekly' => 'Weekly' ,
        'monthly' => 'Monthly' ,
        'yearly' => 'Yearly' ,
        'never' => 'Never' ,
      ),
    ));


    $this->addElement('Select', 'priority', array(
      'label' => 'Priority',
      'description' => 'Choose the priority of this content.',
      'allowEmpty' => false,
      'multiOptions' => array(
        '0.1' => '0.1',
        '0.2' => '0.2',
        '0.3' => '0.3',
        '0.4' => '0.4',
        '0.5' => '0.5',
        '0.6' => '0.6',
        '0.7' => '0.7',
        '0.8' => '0.8',
        '0.9' => '0.9',
        '1.0' => '1.0'
      ),
    ));

    $this->addElement('Text', "limit", array(
        'label' => 'Enter Limit',
        'description' => "Enter limit for this content that you want to add in sitemap. [0 for no limit]",
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Checkbox', 'enabled', array(
        'label' => 'Do you want to enable this content sitemap?',
        'description' => 'Enable',
        'value' => 1,
    ));

    $this->addElement('Button', 'execute', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => "javascript:parent.Smoothbox.close();",
        'href' => "javascript:void(0);",
        'decorators' => array(
            'ViewHelper',
        ),
    ));
  }
}
