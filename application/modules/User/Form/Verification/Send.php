<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Send.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Verification_Send extends Engine_Form {

  public function init() {
  
    $this->setTitle('Request Verification');
    $this->setDescription("Are you sure you want to request for verification of your profile? Also, include a message to support your verification request.");
    $this->setAttrib('id', 'user_form_sendverificationrequest');
    
    $this->addElement('Textarea', 'message', array(
      'label' => 'Message (optional):',
      'maxlength' => 255,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '255'))
      ),
      'allowEmpty' => true,
      'required' => false,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Yes',
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
