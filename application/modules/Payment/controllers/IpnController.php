<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IpnController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_IpnController extends Core_Controller_Action_Standard {

  public function indexAction()
  {
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		
		$params = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
				$params[$keyval[0]] = urldecode($keyval[1]);
		}

    $params = array_merge($this->_getAllParams(), $params);
    $gatewayType = $params['action'];
    unset($params['module']);
    unset($params['controller']);
    unset($params['action']);
    unset($params['rewrite']);
    unset($params['gateway_id']);
    if( !empty($gatewayType) && 'index' !== $gatewayType ) {
      $params['gatewayType'] = $gatewayType;
    } else {
      $gatewayType = null;
    }

    // Log ipn
    $ipnLogFile = APPLICATION_PATH . '/temporary/log/payment-ipn.log';
    file_put_contents($ipnLogFile,
        date('c') . ': ' .
        print_r($params, true),
        FILE_APPEND);

    try {

      $gatewayId = 0;
      if(!empty($params['order_id']) && ($order = Engine_Api::_()->getItem('payment_order',$params['order_id']))) {
        $gatewayId = $order->gateway_id;
      }

      $gatewayId = (!empty($params['gateway_id']) ? $params['gateway_id'] : $gatewayId);

      // Get gateways
      $gatewayTable = Engine_Api::_()->getDbtable('gateways', 'payment');
      $gateways = $gatewayTable->fetchAll(array('enabled = ?' => 1));

      // Try to detect gateway
      $activeGateway = null;
      foreach( $gateways as $gateway ) {
        $gatewayPlugin = $gateway->getPlugin();

        // Action matches end of plugin
        if( $gatewayType &&
            substr(strtolower($gateway->plugin), - strlen($gatewayType)) == strtolower($gatewayType) ) {
          $activeGateway = $gateway;
        } else if( $gatewayId && $gatewayId == $gateway->gateway_id ) {
          $activeGateway = $gateway;
        } else if( method_exists($gatewayPlugin, 'detectIpn') &&
            $gatewayPlugin->detectIpn($params) ) {
          $activeGateway = $gateway;
        }
      }

    } catch( Exception $e ) {
      // Gateway detection failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Gateway detection failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    // Gateway could not be detected
    if( !$activeGateway ) {
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Active gateway could not be detected.',
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    // Validate ipn
    try {
      $gateway = $activeGateway;
      $gatewayPlugin = $gateway->getPlugin();
      
      $ipn = $gatewayPlugin->createIpn($params);
    } catch( Exception $e ) {
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN validation failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    
    // Process IPN
    try {
      $gatewayPlugin->onIpn($ipn);
    } catch( Exception $e ) {
      $gatewayPlugin->getGateway()->getLog()->log($e, Zend_Log::ERR);
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN processing failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    // Exit
    echo 'OK';
    exit(0);
  }
  
  public function stripeAction() {
  
    include_once APPLICATION_PATH . "/application/libraries/Engine/Service/Stripe/init.php";
  
    $params = $this->_getAllParams();
    
		$raw_post_data = file_get_contents('php://input');
    $action = $params['action'];
    
		$params = json_decode($raw_post_data,true);
		
    $params = array_merge($this->_getAllParams(), $params);

    $gatewayType = $params['action'];
    unset($params['module']);
    unset($params['controller']);
    unset($params['action']);
    unset($params['rewrite']);
    unset($params['gateway_id']);
    if( !empty($gatewayType) && 'stripe' !== $gatewayType ) {
      $params['gatewayType'] = $gatewayType;
    } else {
      $gatewayType = null;
    }

    // Log ipn
    $ipnLogFile = APPLICATION_PATH . '/temporary/log/stripe-ipn.log';
    file_put_contents($ipnLogFile,
        date('c') . ': ' .
        print_r($params, true),
        FILE_APPEND);

    try {
      //Get gateways
      $activeGateway = null;
      $metadata = $params['data']['object']['lines']['data'][0]['metadata'] ?? $params['data']['object']['metadata'];
      
      if(($order = Engine_Api::_()->getItem('payment_order',$metadata['order_id']))) {
        $gatewayId = $order->gateway_id;
        $gateway = Engine_Api::_()->getItem('payment_gateway', $gatewayId);
        
        $gatewayPlugin = $gateway->getPlugin();
        // Action matches end of plugin
        if( $gatewayType &&
            substr(strtolower($gateway->plugin), - strlen($gatewayType)) == strtolower($gatewayType) ) {
          $activeGateway = $gateway;
        } else if( $gatewayId && $gatewayId == $gateway->gateway_id ) {
          $activeGateway = $gateway;
        } else if( method_exists($gatewayPlugin, 'detectIpn') &&
            $gatewayPlugin->detectIpn($params) ) {
          $activeGateway = $gateway;
        }
      } else {
        echo 'Unknown Order';
        exit(1);
      }
    } catch( Exception $e ) {
      // Gateway detection failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Gateway detection failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    // Gateway could not be detected
    if(!$activeGateway ) {
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Active gateway could not be detected.',
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    
    // Replace this endpoint secret with your endpoint's unique secret
    // If you are testing with the CLI, find the secret by running 'stripe listen'
    // If you are using an endpoint defined with the API or dashboard, look in your webhook settings
    // at https://dashboard.stripe.com/webhooks
    $endpoint_secret = $gateway->config['endpoint_secret'];
    $secretKey = $gateway->config['secret'];
    \Stripe\Stripe::setApiKey($secretKey);
    try {
      $event = \Stripe\Event::constructFrom(
        json_decode($raw_post_data, true)
      );
    } catch(\UnexpectedValueException $e) {
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Webhook error while parsing basic request.',
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    if ($endpoint_secret) {
      // Only verify the event if there is an endpoint secret defined
      // Otherwise use the basic decoded event
      $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
      try {
        $event = \Stripe\Webhook::constructEvent(
          $raw_post_data, $sig_header, $endpoint_secret
        );
      } catch(\Stripe\Exception\SignatureVerificationException $e) {
        file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'Webhook error while validating signature.',
          FILE_APPEND);
          echo 'ERR';
          exit(1);
      }
    }
    
    // Validate ipn
    try {
      $gateway = $activeGateway;
      $gatewayPlugin = $gateway->getPlugin();
      //$ipn = $gatewayPlugin->createIpn($params);
    } catch( Exception $e ) {
      throw $e;
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN validation failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }

    // Process IPN
    try {
      $gatewayPlugin->onTransactionIpn($order,$params);
    } catch( Exception $e ) {
      throw $e;
      $gatewayPlugin->getGateway()->getLog()->log($e, Zend_Log::ERR);
      // IPN validation failed
      file_put_contents($ipnLogFile,
          date('c') . ': ' .
          'IPN processing failed: ' . $e->__toString(),
          FILE_APPEND);
      echo 'ERR';
      exit(1);
    }
    // Exit
    echo 'OK';
    exit(0);
  }
}
