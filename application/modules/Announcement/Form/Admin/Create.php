<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 9837 2012-11-29 01:12:35Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Announcement_Form_Admin_Create extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Post New Announcement')
      ->setDescription('Please compose your new announcement below.')
      ->setAttrib('id', 'announcements_create');     
    
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
      // Prepare Network options
      $networkOptions = array();
      foreach( Engine_Api::_()->getDbtable('networks', 'network')->fetchAll() as $network) {
        $networkOptions[$network->network_id] = $network->getTitle();
      }   
      if(engine_count($networkOptions) > 0) {
      // Select Networks
      $this->addElement('multiselect', 'networks', array(
        'label' => 'Networks',
        'multiOptions' => $networkOptions,
        'description' => 'Which Neworks do you want to see this Announcement?',
        'required' => false,
        'allowEmpty' => true,
      ));
      }

      $this->addElement('Radio','memberlevel_condition',array(
          'label' => 'Choose AND/OR Parameter',
          'description' => 'Choose "AND" or "OR" parameter for the display of announcement with below setting. (Choosing "AND" will require both parameters to match, while choosing "OR" will only require one parameter to match.)',
          'multiOptions' => array('AND'=>'AND','OR'=>'OR'),
          'value' => 'AND'
      ));
    }

    // Prepare Member levels
    $levelOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      $levelOptions[$level->level_id] = $level->getTitle();
    }
    // Select Member Levels
    $this->addElement('multiselect', 'member_levels', array(
      'label' => 'Member Levels',
      'multiOptions' => $levelOptions,
      'description' => 'Which Member Levels do you want to see this Announcement?',
      'required' => false,
      'allowEmpty' => true,
      'value' => array(4),  // Default level
    ));



    // Prepare Profile Types
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      if( engine_count($options) > 1 ) {

          $this->addElement('Radio','profiletype_condition',array(
              'label' => 'Choose AND/OR Parameter',
              'description' => 'Choose "AND" or "OR" parameter for the display of announcement with below setting. (Choosing "AND" will require both parameters to match, while choosing "OR" will only require one parameter to match.)',
              'multiOptions' => array('AND'=>'AND','OR'=>'OR'),
              'value' => 'AND'
          ));

        $options = $profileTypeField->getElementParams('user');
        unset($options['options']['order']);
        unset($options['options']['multiOptions']['0']);
        unset($options['options']['multiOptions']['5']);
        unset($options['options']['multiOptions']['9']);
        // Select Profile Types
        $this->addElement('multiselect', 'profile_types', array_merge($options['options'], array(
              'required' => false,
              'description' => 'Which Profile Types do you want to see this Announcement?',
              'allowEmpty' => true
            )));
      } else if( engine_count($options) == 1 ) {
        // Empty and Hidden Profile Types

      }
    }   
    
    // Add title
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'required' => true,
      'allowEmpty' => false,
    ));

    $localeObject = Zend_Registry::get('Locale');
    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    $languageList = Zend_Registry::get('Zend_Translate')->getList();
    
    foreach ($languageList as $key => $language) {
			if(!in_array($key, array('auto', 'en')))
				continue;
      
      $uploadUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'index', 'action' => 'upload-photo'), 'default', true);
      
      $this->addElement('TinyMce', 'body', array(
				'label' => 'Body',
				'required' => true,
				'editorOptions' => array(
					//'html' => true,
					'uploadUrl' => $uploadUrl,
				),
				'allowEmpty' => false,
      ));
    }
    
    foreach ($languageList as $key => $language) {
      if(in_array($key, array('auto', 'en')))
				continue;
      $key = explode('_', $key);
      $key = $key[0];
      if ($language == 'en')
        $coulmnName = 'body';
      else
        $coulmnName = $language . '_body';

      if (engine_count($languageList) == '1')
        $label = 'Body';
      else
        $label = 'Body [' . $languages[$key] . ']';
      $this->addElement('TinyMce', $coulmnName, array(
				'label' => $label,
				'editorOptions' => array(
					'html' => true,
				),
      ));
    }

        // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Post Announcement',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'announcement', 'controller' => 'manage', 'action' => 'index'), 'admin_default', true),
      'prependText' => Zend_Registry::get('Zend_Translate')->_(' or '),
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
