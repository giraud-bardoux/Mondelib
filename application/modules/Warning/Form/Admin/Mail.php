<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Mail.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Warning_Form_Admin_Mail extends Engine_Form {

  public function init() {
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    $this->setTitle('Send Email');

    $settings = $settings->core_mail;

    if( !@$settings['queueing'] ) {
      $this->addElement('Radio', 'queueing', array(
        'label' => 'Utilize Mail Queue',
        'description' => 'Mail queueing permits the emails to be sent out over time, preventing your mail server from being overloaded by outgoing emails.  It is recommended you utilize mail queueing for large email blasts to help prevent negative performance impacts on your site.',
        'multiOptions' => array(
          1 => 'Utilize Mail Queue (recommended)',
          0 => 'Send all emails immediately (only recommended for less than 100 recipients).',
        ),
        'value' => 1,
      ));
    }

    $this->addElement('Text', 'from_address', array(
      'label' => 'From:',
      'value' => (!empty($settings['from']) ? $settings['from'] : 'noreply@' . $_SERVER['HTTP_HOST']),
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        'EmailAddress',
      )
    ));
    $this->from_address->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);
    
    $this->addElement('Text', 'from_name', array(
      'label' => 'From (name):',
      'required' => true,
      'allowEmpty' => false,
      'value' => (!empty($settings['name']) ? $settings['name'] : 'Site Administrator'),
    ));

    $this->addElement('Text', 'subject', array(
      'label' => 'Subject:',
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('Textarea', 'body', array(
      'label' => 'Body',
      'required' => true,
      'allowEmpty' => false,
      'description' => '(HTML or Plain Text)',
    ));
    $this->body->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));

    $this->addElement('Textarea', 'body_text', array(
      'label' => 'Body (text)',
    ));

    $this->addDisplayGroup(array('body_text'), 'advanced', array(
      'decorators' => array(
        'FormElements',
        array('Fieldset', array('style' => 'display:none;')),
      ),
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Emails',
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
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }
}
