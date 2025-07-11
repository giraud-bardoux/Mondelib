<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Form_Subscription_TransactionFilter extends Engine_Form {

  public function init() {
  
    $this->clearDecorators()
        ->addDecorator('FormElements')
        ->addDecorator('Form')
        ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
        ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'));

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    // Element: query
    $this->addElement('Text', 'order_id', array(
      'label' => 'Order ID',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

		$subform = new Engine_Form(array(
			'description' => 'Order Date',
			'elementsBelongTo'=> 'date',
			'decorators' => array(
				'FormElements',
				array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
				array('HtmlTag', array('tag' => 'div', 'id' =>'integer-wrapper'))
			)
		));
		$subform->addElement('Text', 'date_from', array('placeholder'=>'from'));
    $subform->addElement('Text', 'date_to', array('placeholder'=>'to'));
		$this->addSubForm($subform, 'date');
    
    // Element: query
    $this->addElement('Text', 'amount', array(
      'label' => 'Amount',
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: gateway_id
    $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    $multiOptions = array('' => '');
    foreach( $gatewaysTable->fetchAll() as $gateway ) {
      if(!$gateway->enabled) continue;
      $multiOptions[$gateway->gateway_id] = $gateway->title;
    }
    $multiOptions[3000] = "Wallet";
    $this->addElement('Select', 'gateway_id', array(
      'label' => 'Gateway',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: type
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $multiOptions = (array) $transactionsTable->select()
      ->from($transactionsTable->info('name'), 'type')
      ->distinct(true)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
    if (!empty($multiOptions)) {
      $multiOptions = array_combine(
        array_values($multiOptions),
        array_map('ucwords', array_values($multiOptions))
      );
      // array_combine() will return false if the array is empty
      if (false === $multiOptions) {
        $multiOptions = array();
      }
    }
    $multiOptions = array_merge(array('' => ''), $multiOptions);
    $this->addElement('Select', 'type', array(
      'label' => 'Type',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: state
    $transactionsTable = Engine_Api::_()->getDbtable('transactions', 'payment');
    $multiOptions = (array) $transactionsTable->select()
      ->from($transactionsTable->info('name'), 'state')
      ->distinct(true)
      ->query()
      ->fetchAll(Zend_Db::FETCH_COLUMN)
      ;
    if (!empty($multiOptions)) {
      $multiOptions = array_combine(
        array_values($multiOptions),
        array_map('ucfirst', array_values($multiOptions))
      );
      // array_combine() will return false if the array is empty
      if (false === $multiOptions) {
        $multiOptions = array();
      }
    }
    $multiOptions = array_merge(array('' => ''), $multiOptions);
    $this->addElement('Select', 'state', array(
      'label' => 'Status',
      'multiOptions' => $multiOptions,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => null, 'placement' => 'PREPEND')),
        array('HtmlTag', array('tag' => 'div')),
      ),
    ));

    // Element: order
    $this->addElement('Hidden', 'order', array(
      'order' => 10004,
    ));

    // Element: direction
    $this->addElement('Hidden', 'direction', array(
      'order' => 10005,
    ));

    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Search',
      'type' => 'submit',
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
        array('HtmlTag2', array('tag' => 'div')),
      ),
    ));
  }
}
