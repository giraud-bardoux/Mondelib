<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Selectedmenus.php 2022-01-14 02:08:08Z john $
 * @author     John
 */

class Core_Form_Admin_Seo_Selectedmenus extends Engine_Form {

  public function init() {

    $content_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id', 0);
    $content = Engine_Api::_()->getItem('core_sitemap', $content_id);

    $menustable = Engine_Api::_()->getDbTable('menus', 'core');
    $select = $menustable->select();
    $allmenus = $menustable->fetchAll($select);

    $finalArray = array();
    foreach($allmenus as $allmenu) {
        $finalArray[$allmenu->id] = $allmenu->title;
    }

    $coreseo_select_menus = Engine_Api::_()->getApi('settings','core')->getSetting('coreseo_select_menus','');

    $this->setTitle("Select Menu for Sitemap")
    ->setAttrib('class', 'global_form_popup');
    $this->setDescription("Below, you can select menu that you want to add into sitemap.");

    $this->addElement('MultiCheckbox', 'coreseo_select_menus', array(
      //'label' => 'Select Menus',
      //'description' => 'You can select menus.',
      //'allowEmpty' => false,
      'multiOptions' => $finalArray,
      'value' => json_decode($coreseo_select_menus),
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
