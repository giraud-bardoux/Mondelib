<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminGatewayController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_AdminGatewayController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_payment', array(), 'core_admin_main_payment_gateways');
    // Test curl support
    if( !function_exists('curl_version') ||
        !($info = curl_version()) ) {
      $this->view->error = $this->view->translate('The PHP extension cURL ' .
          'does not appear to be installed, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    else if( !($info['features'] & CURL_VERSION_SSL) ||
        !engine_in_array('https', $info['protocols']) ) {
      $this->view->error = $this->view->translate('The installed version of ' .
          'the cURL PHP extension does not support HTTPS, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }

    // Make paginator
    $select = Engine_Api::_()->getDbtable('gateways', 'payment')->select()
        ->where('`plugin` != ?', 'Payment_Plugin_Gateway_Testing');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function editAction()
  {
		$this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_payment', array(), 'core_admin_main_payment_gateways');
		
    // Get gateway
    $gateway = Engine_Api::_()->getDbtable('gateways', 'payment')
      ->find($this->_getParam('gateway_id'))
      ->current();

    // Make form
    $this->view->form = $form = $gateway->getPlugin()->getAdminGatewayForm();
    
    if(engine_in_array($gateway->plugin, array('Payment_Plugin_Gateway_Free', 'Payment_Plugin_Gateway_Bank', 'Payment_Plugin_Gateway_Cash', 'Payment_Plugin_Gateway_Cheque'))) {
      $form->removeElement('test_mode');
    }
    
    if ( _ENGINE_ADMIN_NEUTER ) {
        return;
    }
    // Populate form
    $form->populate($gateway->toArray());
    if( is_array($gateway->config) ) {
      $form->populate($gateway->config);
      if(!engine_in_array($gateway->plugin, array('Payment_Plugin_Gateway_Free', 'Payment_Plugin_Gateway_Bank', 'Payment_Plugin_Gateway_Cash', 'Payment_Plugin_Gateway_Cheque'))) {
        $form->test_mode->setValue($gateway->test_mode);
      }
    }

    // get active curruncies
    
    $gatewayTable = Engine_Api::_()->getDbtable('currencies', 'payment');
    $select = $gatewayTable->select()->where('gateways LIKE ?','%"'.$this->_getParam('gateway_id').'"%');
    $gatewaysResult = $gatewayTable->fetchAll($select);
    $values = array();
    foreach($gatewaysResult as $currency) {
      $values[$currency->currency_id] = $currency->currency_id;
    }
    $form->addElement("Multiselect",'curruncies',array(
      'Label' => 'Choose Currencies',
      'Description' => 'Choose the currencies you want to enable for this payment gateway. If you do not select any, then this gateway will not be available to users.',
      'value'=>$values
    ));

  

    $currencies = Engine_Api::_()->getDbtable('currencies', 'payment')->getCurrencies(array('enabled' => 1));
    $currenciesArray = array();
    foreach($currencies as $currency) {
      $currenciesArray[$currency->currency_id] = $currency->title;
    }
    $form->getElement('curruncies')->setMultiOptions($currenciesArray);

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();
    
    $enabled = (bool) $values['enabled'];
    $testMode = isset($values['test_mode']) ? $values['test_mode'] : 0;
    unset($values['enabled']);
    $currencies = $_POST["curruncies"] ?? array();
    $currenciesArray = Engine_Api::_()->getDbtable('currencies', 'payment')->getCurrencies();
    foreach($currenciesArray as $currency) {
      $gateways = json_decode($currency->gateways,true);
      if(is_null($gateways)) $gateways = array();
      if(!in_array($currency["currency_id"],$currencies)){
        if(array_search( $this->_getParam('gateway_id'), $gateways ) !== false)
        unset( $gateways[array_search( $this->_getParam('gateway_id'), $gateways )] );
      }else{
        if(!in_array($this->_getParam('gateway_id'),$gateways)){
          $gateways[] = (string)$this->_getParam('gateway_id');
        }
      }
      $currency->gateways = json_encode($gateways);
      $currency->save();
    }

    // Validate gateway config
    if( $enabled ) {
      $gatewayObject = $gateway->getGateway();

      try {
        $gatewayObject->setConfig($values);
        $response = $gatewayObject->test();
      } catch( Exception $e ) {
        $enabled = false;
        $form->populate(array('enabled' => false));
        $form->addError(sprintf('Gateway login failed. Please double check ' .
            'your connection information. The gateway has been disabled. ' .
            'The message was: [%2$d] %1$s', $e->getMessage(), $e->getCode()));
      }


      

      // Process
      $message = null;
      try {
        $config = $gateway->getPlugin()->processAdminGatewayForm($values);
      } catch( Exception $e ) {
        $message = $e->getMessage();
        $config = null;
      }
    } else {
      $form->addError('Gateway is currently disabled.');
      $config = $gateway->config;
      $config['username'] = $values['username'];
      $config['password'] = $values['password'];
      if( isset($values['signature']) ) {
        $config['signature'] = $values['signature'];
      }
    }

    if( null !== $config ) {
      $gateway->setFromArray(array(
        'enabled' => $enabled,
        'test_mode' => $testMode,
        'config' => $config,
      ));
      $gateway->save();

      $form->addNotice('Changes saved.');
    } else {
      $form->addError($message);
    }

    // Try to update/create all product if enabled
    $gatewayPlugin = $gateway->getGateway();
    if( $gateway->enabled &&
        method_exists($gatewayPlugin, 'createProduct') &&
        method_exists($gatewayPlugin, 'editProduct') &&
        method_exists($gatewayPlugin, 'detailVendorProduct') ) {
      $packageTable = Engine_Api::_()->getDbtable('packages', 'payment');
      try {
        foreach( $packageTable->fetchAll() as $package ) {
          if( $package->isFree() ) {
            continue;
          }
          // Check billing cycle support
          if( !$package->isOneTime() ) {
            $sbc = $gateway->getGateway()->getSupportedBillingCycles();
            if( !engine_in_array($package->recurrence_type, array_map('strtolower', $sbc)) ) {
              continue;
            }
          }
          // If it throws an exception, or returns empty, assume it doesn't exist?
          try {
            $info = $gatewayPlugin->detailVendorProduct($package->getGatewayIdentity());
          } catch( Exception $e ) {
            $info = false;
          }
          // Create
          if( !$info ) {
            $gatewayPlugin->createProduct($package->getGatewayParams());
          }
        }
        $form->addNotice('All plans have been checked successfully for products in this gateway.');
      } catch( Exception $e ) {
        $form->addError('We were not able to ensure all packages have a product in this gateway.');
        $form->addError($e->getMessage());
      }
    }
  }

  public function deleteAction()
  {
    $this->view->form = $form = new Payment_Form_Admin_Gateway_Delete();
  }
}
