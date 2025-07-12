<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Manage_ImportUser extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Bulk Import Members Using CSV File');
	
	 $this->setDescription('Below settings will apply to all the members imported via uploaded csv file.');

    $this->addElement('File', 'csvfile', array(
        'label' => 'Choose the .csv file to upload members in bulk on your site',
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->csvfile->addValidator('Extension', false, 'csv');

    // Element: timezone
    $this->addElement('Select', 'timezone', array(
      'label' => 'Timezone',
      'value' => $settings->getSetting('core.locale.timezone'),
      'multiOptions' => Engine_Api::_()->core()->timeZone(),
    ));
    $this->timezone->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    // Languages
    $languageNameList = Engine_Api::_()->getApi('languages', 'core')->getLanguages();

    if(engine_count($languageNameList)>1){
      $this->addElement('Select', 'language', array(
        'label' => 'Language',
        'multiOptions' => $languageNameList,
        
      ));
      $this->language->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    }
    else{
      $this->addElement('Hidden', 'language', array(
        'value' => key($languageNameList),
        'order' => 1002
      ));
    }
    
    // Element: profile_type
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();

      $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
      
      $options = $profileTypeField->getElementParams('user');

      unset($options['options']['order']);
      unset($options['options']['multiOptions']['']);
      if($options['type'] == 'ProfileType') {
        unset($options['options']['multiOptions']['5']);
        unset($options['options']['multiOptions']['9']);
      }
      if( engine_count($options['options']['multiOptions']) > 1 ) { 
        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        $this->addElement('Select', 'profile_types', array_merge($options['options'], array(
              'required' => true,
              'allowEmpty' => false,
              'tabindex' => $tabIndex++,
            )));
      } else if( engine_count($options['options']['multiOptions']) == 1 ) {
        $this->addElement('Hidden', 'profile_types', array(
          'value' => $optionsIds[0]->option_id,
          'order' => 1001
        ));
      }
    }

    //Element member level
    $levelMultiOptions = array();
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
    foreach( $levels as $row ) {
      $levelMultiOptions[$row->level_id] = $row->getTitle();
    }
    $defaultLevelId = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel()->level_id;
    $this->addElement('Select', 'level_id',array(
        'label'  => 'Select Member Level',
        'required'  => true,
        'multiOptions'  => $levelMultiOptions,
        'value' => $defaultLevelId,
    ));

    // Init level
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
      $networkMultiOptions = array(); //0 => ' ');
      $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
      if(engine_count($networks) > 0) {
        foreach( $networks as $row ) {
          $networkMultiOptions[$row->network_id] = $row->getTitle();
        }
        $this->addElement('Multiselect', 'network_id', array(
          'label' => 'Networks',
          'multiOptions' => $networkMultiOptions,
        ));
      }
    }

    $this->addElement('Checkbox', 'approved', array(
        'label' => 'Approved?',
        'validators' => array(
            'notEmpty',
            array('GreaterThan', false, array(0)),
        ),
        'tabindex' => $tabIndex++,
    ));

    $this->addElement('Checkbox', 'verified', array(
        'label' => 'Is Email Verified?',
        'validators' => array(
            'notEmpty',
            array('GreaterThan', false, array(0)),
        ),
        'tabindex' => $tabIndex++,
    ));

    $this->addElement('Checkbox', 'enabled', array(
        'label' => 'Enabled?',
        'validators' => array(
            'notEmpty',
            array('GreaterThan', false, array(0)),
        ),
        'tabindex' => $tabIndex++,
    ));
    
    $this->addElement('Checkbox', 'is_verified', array(
      'label' => 'Verified?',
      'validators' => array(
          'notEmpty',
          array('GreaterThan', false, array(0)),
      ),
      'tabindex' => $tabIndex++,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Import',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'link' => true,
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }
}
