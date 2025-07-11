<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Update.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Form_Admin_ProfileTypeLevelMap_Update extends Engine_Form
{
  public function init() {
    $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('id', null);
    $profileTypeId = Zend_Controller_Front::getInstance()->getRequest()->getParam('profileTypeId', null);
    $option_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('option_id', null);

    $getProfileTypeData = 0;
    if($profileTypeId)
      $getProfileTypeData = Engine_Api::_()->user()->getProfileTypeData(array('option_id' => $profileTypeId));

    $title = "Map Profile Type with Member Level";
    $this
      ->setAttrib('id', 'admin-form')
      ->setMethod('POST')
      ->setAction($_SERVER['REQUEST_URI'])
      ->setTitle($title)
      ->setDescription('Here you can select your profile type to map with respective member levels.');

    if (!empty($id)) {
      $profileTypeId = Engine_Api::_()->getItem('mapProfileTypeLevel', $id)->profile_type_id;
    }

    if (!empty($profileTypeId)) {
      $profileTypeOptions[$profileTypeId] = Engine_Api::_()->getItem('option', $profileTypeId)->label;

      $this->addElement('Select', 'profile_type_id_temp', array(
        'label' => 'Profile Type',
        'disabled' => 'disabled',
        'multiOptions' => $profileTypeOptions,
      ));
      $this->addElement('hidden', 'profile_type_id', array(
        'value' => $profileTypeId,
        'order' => 103
      ));

    } else {
      $profileTypeOptions = Engine_Api::_()->getDbtable('options', 'authorization')->getProfileTypesOptions();

      $this->addElement('Select', 'profile_type_id', array(
        'label' => 'Profile Type',
        'multiOptions' => $profileTypeOptions,
      ));
    }
    
    // Element: level_id
    $viewer = Engine_Api::_()->user()->getViewer();
    $multiOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if(!$viewer->isSuperAdmin() && $level->flag == 'superadmin') {
          continue;
      }
      //Admin Member Profile Type

//       if($getProfileTypeData == 1 && ($level->type != 'admin' || $level->flag == 'superadmin')) {
//         continue;
//       }

      $multiOptions[$level->getIdentity()] = $level->getTitle();
    }

    if($getProfileTypeData != 1) {
      unset($multiOptions[1]);
      unset($multiOptions[2]);
    }
    
    if($getProfileTypeData == 1) {
      $this->addElement('Select', 'member_level_id', array(
        'label' => 'Member Level',
        'multiOptions' => $multiOptions,
        'disabled' => 'disabled',
      ));
    } else {
      $this->addElement('Select', 'member_level_id', array(
        'label' => 'Member Level',
        'multiOptions' => $multiOptions,
      ));
    }
    
    if($getProfileTypeData != 1) {
      $this->addElement('Button', 'submit', array(
        'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
      ));

      $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'ignore' => true,
        'onClick' => 'parent.Smoothbox.close();',
        'decorators' => array(
          'ViewHelper'
        )
      ));
      $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    } else if($getProfileTypeData == 1) {
      $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        //'prependText' => ' or ',
        'ignore' => true,
        'onClick' => 'parent.Smoothbox.close();',
        'decorators' => array(
          'ViewHelper'
        )
      ));
      $this->addDisplayGroup(array('cancel'), 'buttons');
    }
  }
}
