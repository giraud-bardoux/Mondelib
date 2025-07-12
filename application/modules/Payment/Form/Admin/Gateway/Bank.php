<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bank.php 9747 2019-12-07 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Gateway_Bank extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();
    $this->setTitle('Payment Gateway: Bank');
    $this->setDescription('PAYMENT_FORM_ADMIN_GATEWAY_BANK_DESCRIPTION');
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

  $checkInfo = "
  Account Name:
Account Number:
Bank Name:
Branch Address of Bank:
IFSC Code:
  ";


    // Elements
    $this->addElement('Textarea', 'account_details', array(
      'label' => 'Account Details',
      'required' => true,
      'allowEmpty' => false,
      'value'=>$checkInfo,
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    $this->addElement('Select', 'receipt', array(
      'label' => 'Make Receipt Upload Mandatory',
      'description'=> 'Do you want to make receipt upload field mandatory when users go for payment via bank?',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' =>array('1'=>'Yes','0'=>'No'),
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
  }
}
