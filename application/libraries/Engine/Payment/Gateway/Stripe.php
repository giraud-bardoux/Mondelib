<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Strip.php 10122 2013-12-11 17:29:07Z andres $
 */

include_once APPLICATION_PATH . "/application/libraries/Engine/Service/Stripe/init.php";
 
class Engine_Payment_Gateway_Stripe extends Engine_Payment_Gateway
{
  // Support
  protected $_supportedCurrencies = array(
    'USD'=>'USD',
    'AED'=>'AED',
    'AFN'=>'AFN',
    'ALL'=>'ALL',
    'AMD'=>'AMD',
    'ANG'=>'ANG',
    'AOA'=>'AOA',
    'ARS'=>'ARS',
    'AUD'=>'AUD',
    'AWG'=>'AWG',
    'AZN'=>'AZN',
    'BAM'=>'BAM',
    'BBD'=>'BBD',
    'BDT'=>'BDT',
    'BGN'=>'BGN',
    'BIF'=>'BIF',
    'BMD'=>'BMD',
    'BND'=>'BND',
    'BOB'=>'BOB',
    'BRL'=>'BRL',
    'BSD'=>'BSD',
    'BWP'=>'BWP',
    'BZD'=>'BZD',
    'CAD'=>'CAD',
    'CDF'=>'CDF',
    'CHF'=>'CHF',
    'CLP'=>'CLP',
    'CNY'=>'CNY',
    'COP'=>'COP',
    'CRC'=>'CRC',
    'CVE'=>'CVE',
    'CZK'=>'CZK',
    'DJF'=>'DJF',
    'DKK'=>'DKK',
    'DOP'=>'DOP',
    'DZD'=>'DZD',
    'EGP'=>'EGP',
    'ETB'=>'ETB',
    'EUR'=>'EUR',
    'FJD'=>'FJD',
    'FKP'=>'FKP',
    'GBP'=>'GBP',
    'GEL'=>'GEL',
    'GIP'=>'GIP',
    'GMD'=>'GMD',
    'GNF'=>'GNF',
    'GTQ'=>'GTQ',
    'GYD'=>'GYD',
    'HKD'=>'HKD',
    'HNL'=>'HNL',
    'HRK'=>'HRK',
    'HTG'=>'HTG',
    'HUF'=>'HUF',
    'IDR'=>'IDR',
    'ILS'=>'ILS',
    'INR'=>'INR',
    'ISK'=>'ISK',
    'JMD'=>'JMD',
    'JPY'=>'JPY',
    'KES'=>'KES',
    'KGS'=>'KGS',
    'KHR'=>'KHR',
    'KMF'=>'KMF',
    'KRW'=>'KRW',
    'KYD'=>'KYD',
    'KZT'=>'KZT',
    'LAK'=>'LAK',
    'LBP'=>'LBP',
    'LKR'=>'LKR',
    'LRD'=>'LRD',
    'LSL'=>'LSL',
    'MAD'=>'MAD',
    'MDL'=>'MDL',
    'MGA'=>'MGA',
    'MKD'=>'MKD',
    'MMK'=>'MMK',
    'MNT'=>'MNT',
    'MOP'=>'MOP',
    'MRO'=>'MRO',
    'MUR'=>'MUR',
    'MVR'=>'MVR',
    'MWK'=>'MWK',
    'MXN'=>'MXN',
    'MYR'=>'MYR',
    'MZN'=>'MZN',
    'NAD'=>'NAD',
    'NGN'=>'NGN',
    'NIO'=>'NIO',
    'NOK'=>'NOK',
    'NPR'=>'NPR',
    'NZD'=>'NZD',
    'PAB'=>'PAB',
    'PEN'=>'PEN',
    'PGK'=>'PGK',
    'PHP'=>'PHP',
    'PKR'=>'PKR',
    'PLN'=>'PLN',
    'PYG'=>'PYG',
    'QAR'=>'QAR',
    'RON'=>'RON',
    'RSD'=>'RSD',
    'RUB'=>'RUB',
    'RWF'=>'RWF',
    'SAR'=>'SAR',
    'SBD'=>'SBD',
    'SCR'=>'SCR',
    'SEK'=>'SEK',
    'SGD'=>'SGD',
    'SHP'=>'SHP',
    'SLL'=>'SLL',
    'SOS'=>'SOS',
    'SRD'=>'SRD',
    'STD'=>'STD',
    'SZL'=>'SZL',
    'THB'=>'THB',
    'TJS'=>'TJS',
    'TOP'=>'TOP',
    'TRY'=>'TRY',
    'TTD'=>'TTD',
    'TWD'=>'TWD',
    'TZS'=>'TZS',
    'UAH'=>'UAH',
    'UGX'=>'UGX',
    'UYU'=>'UYU',
    'UZS'=>'UZS',
    'VND'=>'VND',
    'VUV'=>'VUV',
    'WST'=>'WST',
    'XAF'=>'XAF',
    'XCD'=>'XCD',
    'XOF'=>'XOF',
    'XPF'=>'XPF',
    'YER'=>'YER',
    'ZAR'=>'ZAR',
    'ZMW'=>'ZMW'
  );
  
  protected $_supportedLanguages = array(
    'es', 'en', 'de', 'fr', 'nl', 'pt', 'zh', 'it', 'ja', 'pl',
  );
  protected $_supportedRegions = array(
    'AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM',
    'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ',
    'BM', 'BT', 'BO', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI',
    'KH', 'CM', 'CA', 'CV', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO',
    'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CY', 'CZ', 'DK', 'DJ',
    'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'FK', 'FO', 'FJ',
    'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR',
    'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA',
    'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT',
    'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA',
    'LV', 'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW',
    'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD',
    'MC', 'MN', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'AN', 'NC',
    'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS',
    'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO',
    'RU', 'RW', 'SH', 'KN', 'LC', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN',
    'CS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'ES', 'LK',
    'SD', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL',
    'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE',
    'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH',
    'YE', 'ZM',
  );
  protected $_supportedBillingCycles = array(
    /* 'Day', */ 'Week', /* 'SemiMonth',*/ 'Month', 'Year',
  );
  // Translation
  protected $_transactionMap = array(
    Engine_Payment_Transaction::REGION => 'LOCALECODE',
    Engine_Payment_Transaction::RETURN_URL => 'RETURNURL',
    Engine_Payment_Transaction::CANCEL_URL => 'CANCELURL',
    // Deprecated?
    Engine_Payment_Transaction::IPN_URL => 'NOTIFYURL',
    Engine_Payment_Transaction::VENDOR_ORDER_ID => 'INVNUM',
    Engine_Payment_Transaction::CURRENCY => 'CURRENCYCODE',
    Engine_Payment_Transaction::REGION => 'LOCALECODE',
  );
  public function  __construct(array $options = null)
  {
    parent::__construct($options);
    if( null === $this->getGatewayMethod() ) {
      $this->setGatewayMethod('POST');
    }
  }
  /**
   * Get the service API
   *
   * @return Engine_Service_PayPal
   */
  public function getService()
  {
    if( null === $this->_service ) {
      $this->_service = new Engine_Service_PayPal(array_merge(
        $this->getConfig(),
        array(
          'testMode' => $this->getTestMode(),
          'log' => ( true ? $this->getLog() : null ),
        )
      ));
    }
    return $this->_service;
  }

  public function getGatewayUrl()
  {
  }
  
  
  // IPN
  public function processIpn(Engine_Payment_Ipn $ipn)
  {
    // Validate ----------------------------------------------------------------
    // Get raw data
    $rawData = $ipn->getRawData();
    // Log raw data
    //if( 'development' === APPLICATION_ENV ) {
      $this->_log(print_r($rawData, true), Zend_Log::DEBUG);
    //}
    // Check a couple things in advance
    if( !empty($rawData['test_ipn']) && !$this->getTestMode() ) {
      throw new Engine_Payment_Gateway_Exception('Test IPN sent in non-test mode');
    }
    // @todo check the email address of the account?
    // Build url and post data
    $parsedUrl = parse_url($this->getGatewayUrl());
    $rawData = array_merge(array(
      'cmd' => '_notify-validate',
    ), $rawData);
    foreach ($rawData as $key => $value) {
      $rawData[$key] = stripslashes($value);
    }
    $postString = http_build_query($rawData, '', '&');

    if( empty($parsedUrl['host']) ) {
      $this->_throw(sprintf('Invalid host in gateway url: %s', $this->getGatewayUrl()));
      return false;
    }
    if( empty($parsedUrl['path']) ) {
      $parsedUrl['path'] = '/';
    }
    // Open socket
    $fp = fsockopen('ssl://' . $parsedUrl['host'], 443, $errNum, $errStr, 30);
    if( !$fp ) {
      $this->_throw(sprintf('Unable to open socket: [%d] %s', $errNum, $errStr));
    }
    stream_set_blocking($fp, true);
    fputs($fp, "POST {$parsedUrl['path']} HTTP/1.1\r\n");
    fputs($fp, "Host: {$parsedUrl['host']}\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: " . strlen($postString) . "\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $postString . "\r\n\r\n");
    $response = '';
    while( !feof($fp) ) {
      $response .= fgets($fp, 1024);
    }
    fclose($fp);
    if( !stripos($response, 'VERIFIED') ) {
      $this->_log($response);
      $this->_throw(sprintf('IPN Validation Failed: %s %s', $parsedUrl['host'], $parsedUrl['path']));
      return false;
    }
    // Success!
    $this->_log('IPN Validation Succeeded');
    // Process -----------------------------------------------------------------
    $rawData = $ipn->getRawData();
    $data = $rawData;
    return $data;
  }

  // Transaction
  public function processTransaction(Engine_Payment_Transaction $transaction)
  {
    $data = array();
    $rawData = $transaction->getRawData();
    // Driver-specific params
    if( isset($rawData['driverSpecificParams']) ) {
      if( isset($rawData['driverSpecificParams'][$this->getDriver()]) ) {
        $data = array_merge($data, $rawData['driverSpecificParams'][$this->getDriver()]);
      }
      unset($rawData['driverSpecificParams']);
    }
    // Add default region?
    if( empty($rawData['region']) && ($region = $this->getRegion()) ) {
      $rawData['region'] = $region;
    }
    // Add default currency
    if( empty($rawData['currency']) && ($currency = $this->getCurrency()) ) {
      $rawData['currency'] = $currency;
    }
    // Process abtract translation map
    $tmp = array();
    $data = array_merge($data, $this->_translateTransactionData($rawData, $tmp));
    $rawData = $tmp;
    // Call setExpressCheckout
    $token = $this->getService()->setExpressCheckout($data);
    $data = array();
    $data['cmd'] = '_express-checkout';
    $data['token'] = $token;
    return $data;
  }
  
  function setConfig($params) {
    if(isset($params['secret']))
    \Stripe\Stripe::setApiKey($params['secret']);
  }
  
  function test() {
    try {
      $stripePlan = \Stripe\Plan::retrieve("test");
    } catch(Exception $e) {
      if( strpos($e->getMessage(), 'Invalid API Key provided:') !== false) {
        throw new Engine_Payment_Gateway_Exception(sprintf('Gateway login ' .
            'failed. Please double-check ' .
            'your connection information. ' .
            '%1$s', $e->getMessage()));
      }
    }
  }
}
