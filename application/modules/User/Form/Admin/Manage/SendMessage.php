<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Otpsms
 * @package    Otpsms
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Messages.php  2018-11-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class User_Form_Admin_Manage_SendMessage extends Engine_Form {

  public function init() {
    
    $this->setTitle('Send Message')
    ->setDescription('Here, you can send message to this member which has signed up via phone number on your website. The message will be sent as SMS on the mobile phone.')
    ->setAttribs(array(
      'id' => '',
      'class' => '',
    ))
    ->setMethod('POST');

    $this->addElement('Textarea','message',array(
      'label'=>'Message',
      'allowEmpty'=>false,
      'required'=>'true',
      'maxlength' => 100,
    ));
   
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Message',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'Cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onClick' => 'javascript:parent.Smoothbox.close();',
      'decorators' => array(
          'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
