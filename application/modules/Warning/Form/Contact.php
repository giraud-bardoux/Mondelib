<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Contact.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_Form_Contact extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Contact Us')
      ->setDescription('_CORE_CONTACT_DESCRIPTION')
      ->setAction($_SERVER['REQUEST_URI']);
    
    $this->addElement('Text', 'name', array(
      'label' => 'Name',
      'required' => true,
      'notEmpty' => true,
    ));
    
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'required' => true,
      'notEmpty' => true,
      'validators' => array(
        'EmailAddress'
      )
    ));
    $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    
    $this->addElement('Textarea', 'body', array(
      'label' => 'Message',
      'required' => true,
      'notEmpty' => true,
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Send Message',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array(
        'ViewHelper',
      ),
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
