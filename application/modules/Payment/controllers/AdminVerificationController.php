<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminPackageController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_AdminVerificationController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_verification', array(), 'core_admin_main_settings_verification');
    
    // Test curl support
    if( !function_exists('curl_version') ||
        !($info = curl_version()) ) {
      $this->view->error = $this->view->translate('The PHP extension cURL ' .
          'does not appear to be installed, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    // Test curl ssl support
    else if( !($info['features'] & CURL_VERSION_SSL) ||
        !engine_in_array('https', $info['protocols']) ) {
      $this->view->error = $this->view->translate('The installed version of ' .
          'the cURL PHP extension does not support HTTPS, which is required ' .
          'for interaction with payment gateways. Please contact your ' .
          'hosting provider.');
    }
    // Check for enabled payment gateways
    else if( Engine_Api::_()->getDbtable('gateways', 'payment')->getEnabledGatewayCount() <= 0 ) {
      $this->view->error = $this->view->translate('There are currently no ' .
          'enabled payment gateways. You must %1$sadd one%2$s before this ' .
          'page is available.', '<a href="' .
          $this->view->escape($this->view->url(array('controller' => 'gateway'))) .
          '">', '</a>');
    }

    // Make form
    //$this->view->formFilter = $formFilter = new Payment_Form_Admin_Package_Filter();

    // Process form
//     if( $formFilter->isValid($this->_getAllParams()) ) {
//       if( null === $this->_getParam('enabled') ) {
//         $formFilter->populate(array('enabled' => 1));
//       }
//       $filterValues = $formFilter->getValues();
//     } else {
//       $filterValues = array(
//         'enabled' => 1,
//       );
//       $formFilter->populate(array('enabled' => 1));
//     }
    
    if( empty($filterValues['order']) ) {
      $filterValues['order'] = 'order';
    }
    if( empty($filterValues['direction']) ) {
      $filterValues['direction'] = 'ASC';
    }
    $this->view->filterValues = $filterValues;
    $this->view->order = $filterValues['order'];
    $this->view->direction = $filterValues['direction'];

    // Initialize select
    $table = Engine_Api::_()->getDbtable('verificationpackages', 'payment');
    $select = $table->select();

    // Add filter values
    if( !empty($filterValues['query']) ) {
      $select->where('title LIKE ?', $filterValues['package_id'] . '%');
    }
    
    if( !empty($filterValues['level_id']) ) {
      $select->where('level_id = ?', $filterValues['level_id']);
    }
    
    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  public function createAction() {
  
		// Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_verification', array(), 'core_admin_main_settings_verification');
		
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Verification_Create();
    $locale = $this->view->locale()->getLocaleDefault();

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();
    
    if($values['verified'] == 4) {
      if(engine_in_array($values['recurrence'], array(1, 3, 6))) {
        $values['recurrence_type'] = 'month';
      } else if($values['recurrence'] == 12) {
        $values['recurrence'] = 1;
        $values['recurrence_type'] = 'year';
      }
    } else if($values['verified'] != 4) {
      $values['recurrence'] = 0;
      $values['recurrence_type'] = 'forever';
    }
    $values['duration'] = 0;
    $values['duration_type'] = 'forever';


    $packageTable = Engine_Api::_()->getDbtable('verificationpackages', 'payment');
    $db = $packageTable->getAdapter();
    $db->beginTransaction();

    try {
      $values['price'] = Zend_Locale_Format::getNumber($values['price'], array('locale' => $locale));

      // Create package
      $package = $packageTable->createRow();
      $package->setFromArray($values);
      $package->save();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    
    if($values['verified'] == 1 && $values['verified'] != $valuesForm['verified']) {
      $_SESSION['popup'] = true;
      return $this->_helper->redirector->gotoRoute();
    } else {
      // Redirect
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }

  public function editAction() {

		// Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_verification', array(), 'core_admin_main_settings_verification');
		
    // Make form
    $this->view->form = $form = new Payment_Form_Admin_Verification_Create();
		
    // Get package
    $level_id = $this->_getParam('level_id', null);
    $getPackage = Engine_Api::_()->getDbTable('verificationpackages', 'payment')->getPackage(array('level_id' => $level_id));
    $verificationpackage_id = $getPackage->getIdentity();
    if( null === ($packageIdentity = $verificationpackage_id) ||
        !($package = Engine_Api::_()->getDbTable('verificationpackages', 'payment')->find($verificationpackage_id)->current()) ) {
      throw new Engine_Exception('No package found');
    }

    // Populate form
    $this->view->package = $package;

    // Get supported billing cycles
    $gateways = array();
    $supportedBillingCycles = array();
    $partiallySupportedBillingCycles = array();
    $fullySupportedBillingCycles = null;
    $gatewaysTable = Engine_Api::_()->getDbtable('gateways', 'payment');
    foreach( $gatewaysTable->fetchAll(/*array('enabled = ?' => 1)*/) as $gateway ) {
      $gateways[$gateway->gateway_id] = $gateway;
      $supportedBillingCycles[$gateway->gateway_id] = $gateway->getGateway()->getSupportedBillingCycles();
      $partiallySupportedBillingCycles = array_merge($partiallySupportedBillingCycles, $supportedBillingCycles[$gateway->gateway_id]);
      if( null === $fullySupportedBillingCycles ) {
        $fullySupportedBillingCycles = $supportedBillingCycles[$gateway->gateway_id];
      } else {
        $fullySupportedBillingCycles = array_intersect($fullySupportedBillingCycles, $supportedBillingCycles[$gateway->gateway_id]);
      }
    }
    $partiallySupportedBillingCycles = array_diff($partiallySupportedBillingCycles, $fullySupportedBillingCycles);

    $multiOptions = /* array(
      'Fully Supported' =>*/ array_combine(array_map('strtolower', $fullySupportedBillingCycles), $fullySupportedBillingCycles)/*,
      'Partially Supported' => array_combine(array_map('strtolower', $partiallySupportedBillingCycles), $partiallySupportedBillingCycles),
    )*/;
    $form->getElement('recurrence')
      ->setMultiOptions($multiOptions)
      //->setDescription('-')
      ;
    $form->getElement('recurrence')->options/*['Fully Supported']*/['forever'] = 'One-time';

    $values = $package->toArray();

    // if($values['recurrence'] == 1 && $values['recurrence_type'] == 'year') {
    //   $values['recurrence'] = 12;
    // } else if(!empty($values['price']) && $values['recurrence'] == 0 && $values['recurrence_type'] == 'forever') {
    //   $values['recurrence'] = 13;
    // }

    $otherValues = array(
      'recurrence' => array($package->recurrence, $package->recurrence_type),
    );

    $form->populate($values);

    $values['level_id'] = $level_id;
    // Hack em up
    $form->populate($otherValues);

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $values = $form->getValues();
    unset($values['level_id']);
    
    if($values['verified'] == 4) {
      $tmp = $values['recurrence'];
      unset($values['recurrence']);
      if( empty($tmp) || !is_array($tmp) ) {
        $tmp = array(null, null);
      }
      $values['recurrence'] = (int) $tmp[0];
      $values['recurrence_type'] = $tmp[1];
      // if(engine_in_array($values['recurrence'], array(1, 3, 6))) {
      //   $values['recurrence_type'] = 'month';
      // } else if($values['recurrence'] == 12) {
      //   $values['recurrence'] = 1;
      //   $values['recurrence_type'] = 'year';
      // } else if($values['recurrence'] == 13) {
      //   $values['recurrence'] = 0;
      //   $values['recurrence_type'] = 'forever';
      // }
    } else if($values['verified'] != 4) {
      $values['recurrence'] = 0;
      $values['recurrence_type'] = 'forever';
    }
    $values['duration'] = 0;
    $values['duration_type'] = 'forever';
    
    $packageTable = Engine_Api::_()->getDbtable('verificationpackages', 'payment');
    $db = $packageTable->getAdapter();
    $db->beginTransaction();
    try {
      // Update package
      $package->setFromArray($values);
      $package->save();

      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    if($values['verified'] == 1 && $values['verified'] != $valuesForm['verified']) {
      $_SESSION['popup'] = true;
      return $this->_helper->redirector->gotoRoute();
    } else {
      // Redirect
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }
  
  public function autoverifyExistingMembersAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    if (!$this->getRequest()->isPost()) {
      $this->view->member_level_id = $levelId =  $this->_getParam('level_id', null);
    }
    
    if ($this->getRequest()->isPost()) {
      $level_id = $this->_getParam('level_id');
      if(!empty($level_id)) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
          $db->query("UPDATE `engine4_users` SET `is_verified` = '1' WHERE `engine4_users`.`level_id` = '".$level_id."';");
        } catch (Exception $ex) {
          throw $ex;
        }
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh'=> true,
        'messages' => Array(Zend_Registry::get('Zend_Translate')->_("Existing users have been successfully verified."))
      ));
    }
  }
}
