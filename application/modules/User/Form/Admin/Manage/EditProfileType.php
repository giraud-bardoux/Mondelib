<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Admin_Manage_EditProfileType extends Engine_Form {

  protected $_userIdentity;

  public function setUserIdentity($userIdentity)
  {
    $this->_userIdentity = (int) $userIdentity;
    return $this;
  }

  public function init() {
  
    $this->setAttrib('id', 'admin_members_edit')
        ->setTitle('Edit Profile Type')
        ->setAction($_SERVER['REQUEST_URI']);

    //profile type
    $user = Engine_Api::_()->getItem('user', $this->_userIdentity);

    $profileTypeValue = Engine_Api::_()->user()->getProfileFieldValue(array('user_id' => $user->getIdentity(), 'field_id' => 1));
    
    // Element: profile_type
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
      $options = $profileTypeField->getElementParams('user');

      unset($options['options']['order']);
      unset($options['options']['multiOptions']['']);
//             if($options['type'] == 'ProfileType') {
//               unset($options['options']['multiOptions']['5']);
//               unset($options['options']['multiOptions']['9']);
//             }

      $options = $profileTypeField->getElementParams('user');
      unset($options['options']['order']);
      unset($options['options']['multiOptions']['0']);
      if($profileTypeValue)
      unset($options['options']['multiOptions'][$profileTypeValue]);
      
      if(engine_count($options['options']['multiOptions']) > 1) {
        $this->addElement('Select', 'profile_type', array_merge($options['options'], array(
          'label' => '',
          'description' => 'Are you sure you want to change Profile Type of this user? Once you change the profile type, the data of this member\'s current profile type will be lost and will not be recovered once deleted.',
          'required' => true,
          'allowEmpty' => false,
          'tabindex' => $tabIndex++,
        )));
      }
    }

    // Element: token
    $this->addElement('Hash', 'token');

    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
    $button_group->addDecorator('DivDivDivWrapper');
  }
}
