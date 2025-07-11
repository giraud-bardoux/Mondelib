<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: UserDataImport.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Form_Admin_UserDataImport extends Engine_Form {

  public function init() {
  
    $this->setTitle('Import User Data')
        ->setDescription('Are you sure you want to import first names, last names, gender, and birthdates of members on your site? Please do not close this popup.')
        ->setAttrib('class', 'global_form_popup')
        ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
        ->setMethod('POST');

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Import',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}
