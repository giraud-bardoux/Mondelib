<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Filter.php 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Warning_Form_Admin_Comingsoon_Filter extends Engine_Form {

  public function init() {
    
    $action = Zend_Controller_Front::getInstance()->getRequest()->getParam('action', null);

    $this->clearDecorators()
        ->addDecorator('FormElements')
        ->addDecorator('Form')
        ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
        ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');
      
    $this->addElement('Text', 'visitor_id', array(
      'placeholder' => 'ID',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));
      
    $this->addElement('Text', 'name', array(
      'placeholder' => 'Name',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));
    
    $this->addElement('Text', 'email', array(
      'placeholder' => 'Email',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));
    
    $this->addElement('Text', 'body', array(
      'placeholder' => 'Message',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));

    $this->addElement('Hidden', 'id', array(
      'order' => 10003,
    ));

    $this->addElement('Button', 'search', array(
        'label' => 'Search',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
          'ViewHelper',
          array('HtmlTag', array('tag' => 'div'))
        ),
    ));

    //Set default action without URL-specified params
    $params = array();
    foreach (array_keys($this->getValues()) as $key) {
      $params[$key] = null;
    }
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble($params));
  }
}
