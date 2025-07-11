<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Settings_Reply extends Engine_Form {

  public function init() {

    $ticket_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('ticket_id');
    $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);

    $this
      ->setAttrib('class', 'ticket_reply ignore_ajax_form')
      ->setAttrib('id', 'core_ticket_reply')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setMethod("POST")
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'support', "action" => "reply"), 'user_extended', true));
    
    $this->addElement('Textarea', 'description', array(
      'label' => " Add Reply",
      'allowEmpty' => false,
      'required' => true,
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Reply',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
  }
}
