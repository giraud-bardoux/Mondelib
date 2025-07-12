<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Verification.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Payment_Form_Admin_Verification_Create extends Engine_Form {

  public function init() {
    
    parent::init();
    
    $package_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('package_id', 0);
    
    $description = $this->getTranslator()->translate('This page offers User Verification Settings, allowing you to customize user verification on your website. Additionally, you can choose a verified icon image to display alongside the names of verified users on your site.<br /><br />These settings are applied individually to different member levels. Start by selecting the member level you wish to modify, and then adjust the settings for that level below.<br /><br />Note: You can also manually adjust the verification status of specific users by editing them in the \'<a href="%1$s" target="_blank">Manage Member</a>\' section.<br /><br />You can approve or reject payments made via Bank, Cash or Cheque from <a href="%2$s" target="_blank">Trasactions</a> section.');
    
    $moreinfo = $this->getTranslator()->translate('<br />More Info: <a href="%3$s" target="_blank"> KB Article</a>');
        
    $description = vsprintf($description.$moreinfo, array('admin/user/manage', 'admin/payment','https://community.socialengine.com/blogs/597/141/manage-verification',));

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    
    $this->setDescription($description);
   
    // Element: level_id
    $multiOptions = array();
    foreach( Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level ) {
      if( $level->type == 'public') {
        continue;
      }
      $multiOptions[$level->getIdentity()] = $level->getTitle();
    }
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'description' => 'Choose the Member Level to which you want to assign the below settings.',
			'multiOptions' => $multiOptions,
			'onchange' => 'fetchLevelSettings(this.value);',
			//'disabled' => !empty($package_id) ? 'disabled' : '',
    ));

    //New File System Code
    $covers = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png', 'webp')));
    foreach( $files as $file ) {
      $covers[$file->storage_path] = $file->name;
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $fileLink = $view->baseUrl() . '/admin/files/';
    
    if(engine_count($covers) > 1) {
      $description = $this->getTranslator()->translate('Choose the image to be shown alongside verified users on your site. This image will show in both user panel and admin panel of your site. [Note: You can add a new image from the "<a href="%1$s" target="_blank">File & Media Manager</a>" section. If you leave the field blank then nothing will show.]');
      $description = vsprintf($description, array($fileLink));
      
      $this->addElement('Select', 'verified_icon', array(
        'label' => 'Choose Verified Icon',
        'description' => $description,
        'multiOptions' => $covers,
      ));
    } else {
      $description = $this->getTranslator()->translate('There are currently no images in the <a href="%1$s" target="_blank"> File & Media Manager </a> section of your site. Please begin by uploading an image to get started.');
      $description = vsprintf($description, array($fileLink));
      $description = "<div class='tip'><span>" . $description . "</span></div>";
      $this->addElement('Dummy', 'verified_icon', array(
        'label' => 'Choose Verified Icon',
        'description' => $description,
      ));
    }
    
    $this->verified_icon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Text', 'verified_tiptext', array(
      'label' => 'Tip Text on Badge Icon',
      'description' => "Enter the text which you want to show when users mouseover on verification badge for this member level.",
      'required' => true,
      'allowEmpty' => false,
      'maxlength' => '64',
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '64'))
      ),
      'value' => 'Verified'
    ));
    
    $this->addElement('Radio', 'verified', array(
      'label' => 'Allow User Verification',
      'description' => 'Do you want to enable user verification for members of this level? Below you can choose how members can be verified.',
      'multiOptions' => array(
          1 => 'Yes, auto verify users.',
          2 => 'Yes, allow users to request verification. If you choose this, then users will be able to request for verification from their Settings page.',
          4 => 'Yes, verify users on Payment basis. If you choose this, then you can configure payment settings below. Users will be able to manage verification on their Settings page.',
          0 => 'No, do not allow user verification.'
      ),
      'onchange' => "verifiedCheck(this.value)",
      'value' => 0,
    ));
    

    if( Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0 ) {
      $this->addElement('Dummy', 'gateway_error', array(
        'content' => "<div class='tip'><span>".$view->translate('There are currently no enabled payment gateways. You must %1$sadd one%2$s before this page is available.', '<a href="admin/payment/gateway">', '</a>') . "</span></div>",
      ));
    }

    // Element: recurrence
    $this->addElement('Duration', 'recurrence', array(
      'label' => 'Billing Cycle',
      'description' => 'How often should members in this be billed?',
      'required' => true,
      'allowEmpty' => false,
      //'validators' => array(
        //array('Int', true),
        //array('GreaterThan', true, array(0)),
      //),
      'value' => array(1, 'month'),
    ));
    //unset($this->getElement('recurrence')->options['day']);
    //$this->getElement('recurrence')->options['forever'] = 'One-time';
    
    $this->addElement('Text', 'price', array(
      'label' => 'Price for Verification Subscription',
      'description' => 'Enter the amount to charge the members of this level on your site. This will be charged once for one-time plans, and each billing cycle for recurring plans. (Number must be greater than zero).',
      'validators' => array(
          array('Float', true),
          new Engine_Validate_AtLeast(1),
      ),
      'value' => 1.00,
    ));
    
    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Create Plan',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'index', 'package_id' => null)),
      'decorators' => array('ViewHelper'),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      )
    ));
	}
}
