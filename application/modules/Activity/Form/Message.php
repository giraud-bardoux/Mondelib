<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Message.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_Form_Message extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Compose Message');
    $this->setDescription('')
       ->setAttrib('id', 'activityact_messages_compose');

    // init title
    $this->addElement('Text', 'title', array(
      'label' => 'Subject',
      'order' => 1,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_HtmlSpecialChars(),
      ),
    ));
   
    // init body - plain text
    $this->addElement('Textarea', 'body', array(
      'label' => 'Message',
      'order' => 2,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      ),
    ));
    // init title
    $this->addElement('Text', 'attachment_content_div', array(
      'label' => '',
      'order' => 3,
      'required' => false,
      'allowEmpty' => true,
    ));
   
    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Send Message',
      'order' => 4,
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
