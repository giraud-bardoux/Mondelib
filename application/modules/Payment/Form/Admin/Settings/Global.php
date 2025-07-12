<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Settings_Global extends Engine_Form
{
  public function init()
  {

    $description = $this->getTranslator()->translate(
          'These settings affect all members in your community. <br>');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      $moreinfo = $this->getTranslator()->translate(
          'More Info: <a href="%1$s" target="_blank"> KB Article</a>');
    } else {
      $moreinfo = $this->getTranslator()->translate(
          '');
    }

    $description = vsprintf($description.$moreinfo, array(
      'https://community.socialengine.com/blogs/597/75/billing-settings',
    ));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this
      ->setTitle('Global Settings')
      ->setDescription($description);

    // Element: currency
    $this->addElement('Select', 'currency', array(
      'label' => 'Default Currency',
      'value' => 'USD',
      'description' => "Choose the default currency to be enabled on your site. Note: All the currencies enabled from the Manage Currencies page will be shown in this dropdown.",
      'value' => $settings->getSetting('payment.currency'),
    ));
    
    $this->addElement('Select', 'autoupdate', array(
      'label' => 'Automatically Update Currency Exchange Rates',
      'multiOptions' => array('1'=>'Yes','0'=>'No'),
      'value' => $settings->getSetting("payment.autoupdate",0),
      'onchange' => "autoUpdateCurrency(this.value);",
    ));

    //currency api key
    $url = '<a href="https://free.currencyconverterapi.com/free-api-key" target="_blank">Click here</a>';
    $description = sprintf('Enter the currency converter API key. %s to create the API key.',$url);
    $this->addElement('Text', "currencyapikey", array(
      'label' => 'Enter Currency Converter API Key',
      'description' => $description,
      'allowEmpty' => true,
      'required' => false,
      'value' => $settings->getSetting('payment.currencyapikey'),
    ));
    $this->getElement('currencyapikey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addElement('Select', 'enablewallet', array(
      'label' => 'Enable Wallet',
      'description' => "Do you want enable wallet feature for payments on your website?",
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No'
      ),
      'value' => $settings->getSetting("payment.enablewallet",1),
    ));

    $this->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
    ));
  }
}
