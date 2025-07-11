<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PayPal.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Gateway_PayPal extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();

    $this->setTitle('Payment Gateway: PayPal');
    
    $description = $this->getTranslator()->translate('PAYMENT_FORM_ADMIN_GATEWAY_PAYPAL_DESCRIPTION');
    $description = vsprintf($description, array(
      'https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-api-signature',
      'https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-ipn-notify',
      'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'payment',
          'controller' => 'ipn',
          'action' => 'PayPal'
        ), 'default', true),
    ));
    $this->setDescription($description);

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);


    // Elements
    $this->addElement('Text', 'username', array(
      'label' => 'API Username',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));

    $this->addElement('Text', 'password', array(
      'label' => 'API Password',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));

    $this->addElement('Text', 'signature', array(
      'label' => 'API Signature',
      //'description' => 'You only need to fill in either Signature or ' .
      //    'Certificate, depending on what type of API account you create.',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';

    $fileOptions = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $fileOptions[$file->storage_path] = $file->name;
    }
    if (engine_count($fileOptions) > 1) {
      $description = $this->getTranslator()->translate('Choose an icon to show with this gateway. This icon will show in both user panel and admin panel of your site. [Note: You can add a new icon from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section. If you leave the field blank then nothing will show.]');
      $description = vsprintf($description, array($fileLink));

      $this->addElement('Select', 'icon', array(
        'label' => "Icon",
        'description' => $description,
        'multiOptions' => $fileOptions,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    } else {
      $description = $this->getTranslator()->translate('There are currently no icons in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an icon to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'icon', array(
        'label' => "Icon",
        'description' => $description,
      ));
      $this->icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    /*
    $this->addElement('Textarea', 'certificate', array(
      'label' => 'API Certificate',
      'description' => 'You only need to fill in either Signature or ' .
          'Certificate, depending on what type of API account you create.',
    ));
     * 
     */
  }

  public function isValid($values)
  {
    $enabled = (bool) $values['enabled'];
    if( $enabled && ( empty($values['username']) || empty($values['password']) || empty($values['signature'])) ) {
      $this->addError('Please enter the correct API details before enabling this gateway.');
      return false;
    }
    return parent::isValid($values);
  }
}
