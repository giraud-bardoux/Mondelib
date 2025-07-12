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

class Core_Form_Admin_Support_Reply extends Engine_Form {

  public function init() {

    $ticket_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('ticket_id');
    $ticket = Engine_Api::_()->getItem('core_ticket', $ticket_id);

    $this
      ->setAttrib('class', 'ticket_reply')
        ->setAttrib('id', 'core_ticket_reply')
        ->setAttrib('enctype', 'multipart/form-data')
        ->setMethod("POST")
        ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'support', "action" => "reply"), 'admin_default', true));
    
    $this->addElement('Textarea', 'description', array(
      'label' => "Add reply",
      'allowEmpty' => false,
      'required' => true,
    ));
    
    if(!empty($ticket->resource_type) && !empty($ticket->resource_id)) {
      $this->addElement('Radio', 'approved', array(
        'label' => 'Take Action',
        'multiOptions' => array(
          '1' => 'Approve',
          '2' => 'Reject',
        ),
      ));
    }

    $this->addElement('Button', 'submit', array(
      'label' => 'Reply',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));
  }
}
