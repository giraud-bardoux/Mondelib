<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Delete.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */

class User_Form_Edit_ProfileType extends Engine_Form {

  public function init() {
  
    $this->setTitle("Edit Profile Type")
        ->setDescription('Choose a new Profile Type for your profile. Once you choose change your profile type, the data of your current profile type will be lost.')
        ->setAttrib('class', 'global_form_popup');
    
    $user = Engine_Api::_()->user()->getViewer();
    $profileTypeValue = Engine_Api::_()->user()->getProfileFieldValue(array('user_id' => $user->getIdentity(), 'field_id' => 1));
    
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

      $options = $profileTypeField->getElementParams('user');
      unset($options['options']['order']);
      unset($options['options']['multiOptions']['0']);
      if($profileTypeValue)
      unset($options['options']['multiOptions'][$profileTypeValue]);
      $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
        'required' => true,
        'allowEmpty' => false,
        'tabindex' => $tabIndex++,
      )));
    }

    $this->addElement('Button', 'submit', array(
      'label' => 'Edit',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'onClick' => 'parent.Smoothbox.close();',
      'decorators' => array('ViewHelper'),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
