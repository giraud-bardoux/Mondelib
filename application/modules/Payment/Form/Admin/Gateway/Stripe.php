<?php

class Payment_Form_Admin_Gateway_Stripe extends Engine_Form {

  public function init() {
  
    $this->setTitle('Payment Gateway: Stripe');

    $description = $this->getTranslator()->translate('PAYMENT_FORM_ADMIN_GATEWAY_STRIPE_DESCRIPTION');
    $description = vsprintf($description, array(
      'https://dashboard.stripe.com/register',
      'https://dashboard.stripe.com/account/webhooks',
      'https://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'payment',
          'controller' => 'ipn',
          'action' => 'stripe'
        ), 'default', true),
    ));
    $this->setDescription($description);

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->addElement('Text', "publish", array(
      'label' => 'Publishable key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    
    $this->addElement('Text', "secret", array(
      'label' => 'Secret key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    
    $this->addElement('Text', "endpoint_secret", array(
      'label' => 'Webhook Signing Secret',
      'required' => true,
      'allowEmpty' => false,
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
    
    $this->addElement('Radio', "enabled", array(
      'label' => 'Enable?',
      'multiOptions' => array('1' => 'Yes', '0' => 'No'),
    ));

    // Element: test_mode
    $this->addElement('Radio', 'test_mode', array(
      'label' => 'Enable Test Mode?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'order' => 10000,
        'ignore' => true
    ));
  }
  
  public function isValid($values)
  {
    $enabled = (bool) $values['enabled'];
    if( $enabled && ( empty($values['publish']) || empty($values['secret'])) ) {
      $this->addError('Please enter the correct details before enabling this gateway.');
      return false;
    }
    return parent::isValid($values);
  }
}
