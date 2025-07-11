<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_payment', array(), 'core_admin_main_payment_settings');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Settings_Global();
    
    $currencies = Engine_Api::_()->getDbtable('currencies', 'payment')->getCurrencies(array('enabled' => 1));
    $currenciesArray = array();
    foreach($currencies as $currency) {
      $currenciesArray[$currency->code] = $currency->title;
    }
    $form->getElement('currency')->setMultiOptions($currenciesArray);

    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
          'currencyapikey' => '******',
      ));
    }

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    $values = $form->getValues();
    
    if($settings->getSetting('payment.currency') != $values['currency']) {
      $getCurrency = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($values['currency']);
      $getCurrency->change_rate = 1;
      $getCurrency->enabled = 1;
      $getCurrency->save();
    }
    
    // Save settings
    Engine_Api::_()->getApi('settings', 'core')->payment = $values;
    
    $form->addNotice('Your changes have been saved.');
  }
  
	public function currencyAction() {
	
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_payment', array(), 'core_admin_main_payment_currency');

    $this->view->formFilter = $formFilter = new Payment_Form_Admin_Settings_CurrencyFilter();
    
    // Process form
    $values = array();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values['selectedItems'] as $value) {
        $currencyItem = Engine_Api::_()->getItem('payment_currency', $value);
        if($_POST['enable'] == 'enable') {
          $currencyItem->enabled = 1;
          $currencyItem->save();
        } else if($_POST['disable'] == 'disable' && $currencyItem->code != Engine_Api::_()->payment()->defaultCurrency()) {
          $currencyItem->enabled = 0;
          $currencyItem->save();
        }
      }
    }

    $table = Engine_Api::_()->getDbTable('currencies', 'payment');
    $tableName = $table->info('name');
    
    $select = $table->select()
            ->from($tableName);

    // Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'currency_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);
    
    if(!empty($_GET['currency_id']))
      $select->where($tableName . ".currency_id = ?", $_GET['currency_id']);

    if (!empty($_GET['title']))
      $select->where($tableName . ".title LIKE ?", $_GET['title'] . '%');

    if(!empty($_GET['code']))
      $select->where($tableName . ".code = ?", $_GET['code']);
      
    if(isset($_GET['enabled']) && $_GET['enabled'] != -1)
      $select->where($tableName.'.enabled = ?', $_GET['enabled'] );
      
    $select->order($tableName.'.order ASC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(200);
    $paginator->setCurrentPageNumber( $this->_getParam('page', 1) );
    $this->view->formValues = $valuesCopy;
  }
  
  public function enableAction() {

    $id = $this->_getParam('id');
    $currency = Engine_Api::_()->getItem('payment_currency', $id);
    if (!empty($id) && !_ENGINE_ADMIN_NEUTER) {
      $currency->enabled = !$currency->enabled;
      $currency->save();
    }
    $this->_redirect($_SERVER['HTTP_REFERER']);
  }
  
  public function createCurrencyAction() {
  
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new Payment_Form_Admin_Settings_EditCurrency();
    
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      
      $values = $form->getValues();
      
      if(!empty($values['code'])) {
        $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($values['code']);
        if($currencyData) {
          $form->addError($this->view->translate("This currency code already exists."));
          return;
        }
      }
      
      $currenciesTable = Engine_Api::_()->getDbtable('currencies', 'payment');
      $db = $currenciesTable->getAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();
        
        if(isset($values['gateways']) && !empty($values['gateways'])) {
          $values['gateways'] = json_encode($values['gateways']);
        }
        
        $currency = $currenciesTable->createRow();
        $currency->setFromArray($values);
        $currency->save();
        $db->commit();
        $this->_forward('success', 'utility', 'core', array(
          //'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully add new currency.'))
        ));
        
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }

    }
  }
  
  public function editCurrencyAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    
    $id = $this->_getParam('id');
    $currency = Engine_Api::_()->getItem('payment_currency', $id);
    
    $this->view->form = $form = new Payment_Form_Admin_Settings_EditCurrency();
    $form->populate($currency->toArray());
    if(!empty($currency->gateways)) {
      $gateways = json_decode($currency->gateways);
      foreach($gateways as $gateway) {
        $gateway = Engine_Api::_()->getItem('payment_gateway', $gateway);
        if($gateway->enabled)
        $form->gateways->setValue($gateways);
      }
    }
    
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
    
      $values = $form->getValues();
      unset($values['code']);
      if($currency->code == Engine_Api::_()->payment()->defaultCurrency()) {
        unset($values['change_rate']);
        unset($values['enabled']);
      }

      if(isset($values['gateways']) && !empty($values['gateways'])) {
        $values['gateways'] = json_encode($values['gateways']);
      }

      $currency->setFromArray($values);
      $currency->save();

      $this->_forward('success', 'utility', 'core', array(
        //'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully edit currency.'))
      ));

    }
  }

  public function orderAction() {
    $table = Engine_Api::_()->getDbTable('currencies', 'payment');
    $results = $table->fetchAll($table->select());
    $orders = $this->getRequest()->getParam('order');
    foreach ($results as $result) {
      $key = array_search ('order_'.$result->getIdentity(), $orders);
      $result->order = $key+1;
      $result->save();
    }
    return;
  }
  
  public function updateCurrencyAction() {
		ini_set('max_execution_time', 0);
		Engine_Api::_()->payment()->updateCurrencyValues();
		$this->_redirect($_SERVER['HTTP_REFERER']);
	}
}
