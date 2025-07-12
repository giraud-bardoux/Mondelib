<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: CurrencyFilter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Form_Admin_Settings_CurrencyFilter extends Engine_Form {

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

    $this->addElement('Text', 'currency_id', array(
      'placeholder' => 'ID',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));
    
    $this->addElement('Text', 'title', array(
      'placeholder' => 'Currency Name',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));
    
    $this->addElement('Text', 'code', array(
      'placeholder' => 'Currency Code',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div'))
      ),
    ));

    $this->addElement('Select', 'enabled', array(
      'required' => true,
      'multiOptions' => array(
        '-1' => 'Status',
        '1' => 'Enable',
        '0' => 'Disable',
      ),
      'decorators' => array(
          'ViewHelper',
          array('Label', array('tag' => null, 'placement' => 'PREPEND')),
          array('HtmlTag', array('tag' => 'div'))
      ),
      'value' => '-1',
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
