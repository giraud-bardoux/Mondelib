<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Create.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Form_Admin_Settings_Create extends Engine_Form {
  public function init() {
    $this->setTitle('Create New Filter')
            ->setDescription('Choose a module for which you want to create a filter in the feeds.');
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', 0);
      $integrateothermoduleArray = array();
			//get all enabled modules
      $coreTable = Engine_Api::_()->getDbTable('modules', 'core');
      $select = $coreTable->select()
              ->from($coreTable->info('name'), array('name', 'title'))
              ->where('enabled =?', 1)
              ->where('type =?', 'extra');
      $resultsArray = $select->query()->fetchAll();
      if (!empty($resultsArray)) {
        foreach ($resultsArray as $result) {
          $integrateothermoduleArray[$result['name']] = $result['title'];
        }
      }
    if (!empty($integrateothermoduleArray)) {
      $this->addElement('Select', 'filtertype', array(
          'label' => 'Choose Module',
          'description' => 'Below, you can choose the plugin to be integrated.',
          'allowEmpty' => false,
          'onchange'=>'setModuleName(this.options[this.selectedIndex].text);',
          'multiOptions' => $integrateothermoduleArray,
      ));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_("Here are no module enabled to configure.") . "</span></div>";
      $this->addElement('Dummy', 'filtertype', array(
          'description' => $description,
      ));
      $this->filtertype->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
    
    $this->addElement('hidden','module',array('order'=>998));
      
    $this->addElement('Text', 'title', array(
      'label' => 'Filter Title',
      'allowEmpty' => false,
      'required' => true,
      'description' => 'Enter the title for the filter.',
    ));     
      
    $this->addElement('Text', 'icon', array(
      'label' => 'Icon / Icon Class',
      'style' => 'width: 500px',
      'description'=>'Enter the Icon for the Filter'
    ));

    $this->addElement('Checkbox', 'active', array(
      'label' => 'Yes, enable this filter.',
      'description' => '',
      'value' => 1,
    ));   
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'link' => true,
      'prependText' => ' or ',
      'onclick' => 'javascript:parent.Smoothbox.close()',
      'decorators' => array(
          'ViewHelper',
      ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
