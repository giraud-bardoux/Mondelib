<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Avatar.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Edit_Avatar extends Engine_Form {

  public function init() {

    $this->setTitle('Add Avatar')
      ->setDescription('')
      ->setAttrib('name', 'user_avatar')
      ->setAttrib('class', 'global_form');

    $avatars = Engine_Api::_()->getDbtable('avatars', 'user')->getAvatars(array('enabled' => 1, 'fetchAll' => 1));
    $options = array();
    foreach ($avatars as $avatar) {
      $imageItem = Engine_Api::_()->getItem('user_avatar', $avatar->avatar_id);
      $photo = Engine_Api::_()->storage()->get($avatar->file_id, '');
      if($photo)
        $options[$avatar->file_id] = '<img src="'.$photo->getPhotoUrl().'" alt="" />';
    }

    $this->addElement('Radio', 'avatar', array(
      'label' => '',
      'multiOptions' => $options,
      'escape' => false,
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'javascript:ajaxsmoothboxclose();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}
