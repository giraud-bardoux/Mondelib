<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSignupController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_AdminOtpController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_settings');
    
    $this->view->form = $form = new User_Form_Admin_Otp_Global();

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.user.id', 0)){
      $form->otpsms_test_user_id->setValue(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.user.id', 0));
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())){
      $values = $form->getValues();

      if(!empty($values['otpsms_test_user_id']) && !empty($values['otpsms_test_mobilenumber']) && empty($values['otpsms_test_code'])) {
        $form->addError($this->view->translate("Please enter the 6 digit OTP."));
        return;
      }
      if(!empty($values['otpsms_test_user_id']) && !empty($values['otpsms_test_mobilenumber']) && !empty($values['otpsms_test_code']) && strlen($values['otpsms_test_code']) < 6) {
        $form->addError($this->view->translate("OTP is less than 6 digit."));
        return;
      }
      if(empty($values['otpsms_test_mobilenumber']) && empty($values['otpsms_test_code'])) {
        $values['otpsms_test_user_id'] = 0;
      }

      foreach ($values as $key => $value){
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }
  
  public function serviceIntegrationAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_integration');
    $this->view->enabledService = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.integration','');
  }

  function enableServiceAction() {
    $type = $this->_getParam('type');
    if($type) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', $type);
    }
    $this->_redirect('admin/user/otp/service-integration');
  }

  public function amazonAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_integration');
    
    $this->view->form = $form = new User_Form_Admin_Otp_Amazon();
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    if( !empty($settings->getSetting('otpsms_amazon')) )
      $form->populate($settings->getSetting('otpsms_amazon'));
     
    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
        'clientId' => '******',
        'clientSecret' => '******',
      ));
    }

    if( !$this->getRequest()->isPost())
      return;

    if( !$form->isValid($this->getRequest()->getPost()))
      return;
      
    $values = $form->getValues();
    if( empty($values['clientId']) || empty($values['clientSecret']) ) {
      $values['clientId'] = '';
      $values['clientSecret'] = '';
    }
    
    if( Engine_Api::_()->getApi('settings', 'core')->otpsms_amazon )
      Engine_Api::_()->getApi('settings', 'core')->removeSetting('otpsms_amazon');

    Engine_Api::_()->getApi('settings', 'core')->otpsms_amazon = $values;
    
    $service = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.integration');
    
    if( !empty($values['enabled']) &&  !empty($values['clientSecret']) && !empty($values['clientId'])) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', 'amazon');
    } else if( $service != 'twilio' ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', '');
    }
    
    $form->addNotice('Your changes have been saved.');
    $form->populate($values);
  }
  
  public function twilioAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_integration');

    $this->view->form = $form = new User_Form_Admin_Otp_Twilio();
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    if( !empty($settings->getSetting('otpsms_twilio')) )
      $form->populate($settings->getSetting('otpsms_twilio'));

    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
        'clientId' => '******',
        'clientSecret' => '******',
        'phoneNumber' => '******',
      ));
    }

    if( !$this->getRequest()->isPost() )
      return;

    if( !$form->isValid($this->getRequest()->getPost()) )
      return;
    
    $values = $form->getValues();
    if( empty($values['clientId']) || empty($values['clientSecret']) || empty($values['phoneNumber'])) {
      $values['clientId'] = '';
      $values['clientSecret'] = '';
      $values['phoneNumber'] = '';
    }
    
    if( Engine_Api::_()->getApi('settings', 'core')->otpsms_twilio )
    Engine_Api::_()->getApi('settings', 'core')->removeSetting('otpsms_twilio');
    
    Engine_Api::_()->getApi('settings', 'core')->otpsms_twilio = $values;
    
    $service = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.integration');
    
    if( !empty($values['enabled']) &&  !empty($values['clientSecret']) && !empty($values['clientId']) && !empty($values['phoneNumber'])) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', 'twilio');
    } else if( $service != 'amazon' ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', '');
    }
    
    $form->addNotice('Your changes have been saved.');
    $form->populate($values);
  }
  
  public function message91Action(){

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_integration');

    $this->view->form = $form = new User_Form_Admin_Otp_Message91();
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    if( !empty($settings->getSetting('otpsms_message91')) )
        $form->populate($settings->getSetting('otpsms_message91'));

    if( _ENGINE_ADMIN_NEUTER ) {
      $form->populate(array(
        'clientId' => '******',
        'clientSecret' => '******',
        'senderId' => '******',
      ));
    }  

    if( !$this->getRequest()->isPost() )
        return;

    if( !$form->isValid($this->getRequest()->getPost()) )
        return;

    $values = $form->getValues();
    if( empty($values['clientId']) || empty($values['clientSecret']) || empty($values['senderId'])) {
      $values['clientId'] = '';
      $values['clientSecret'] = '';
      $values['senderId'] = '';
    }

    if( Engine_Api::_()->getApi('settings', 'core')->otpsms_message91 )
        Engine_Api::_()->getApi('settings', 'core')->removeSetting('otpsms_message91');
        
    Engine_Api::_()->getApi('settings', 'core')->otpsms_message91 = $values;
    $service = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.integration');
    if( !empty($values['enabled']) &&  !empty($values['clientSecret']) && !empty($values['clientId']) && !empty($values['senderId'])) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', 'message91');
    } else if( $service != 'amazon' ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.integration', '');
    }
    
    $form->addNotice('Your changes have been saved.');
    $form->populate($values);
  }
  
	public function manageCountriesAction() {
	
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_otp', array(), 'core_admin_otp_countries');

    $this->view->formFilter = $formFilter = new User_Form_Admin_Otp_CountryFilter();
    
    // Process form
    $values = array();
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values['selectedItems'] as $value) {
        $countryItem = Engine_Api::_()->getItem('core_country', $value);
        if($_POST['enable'] == 'enable') {
          $countryItem->enabled = 1;
          $countryItem->save();
        } else if($_POST['disable'] == 'disable' && $countryItem->iso2 != Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) {
          $countryItem->enabled = 0;
          $countryItem->save();
        }
      }
    }

    $table = Engine_Api::_()->getDbTable('countries', 'core');
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
      'order' => 'country_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);
    
    if(!empty($_GET['country_id']))
      $select->where($tableName . ".country_id = ?", $_GET['country_id']);

    if (!empty($_GET['name']))
      $select->where($tableName . ".name LIKE ?", $_GET['name'] . '%');

    if(!empty($_GET['phonecode']))
      $select->where($tableName . ".phonecode = ?", $_GET['phonecode']);
      
    if(isset($_GET['enabled']) && $_GET['enabled'] != -1)
      $select->where($tableName.'.enabled = ?', $_GET['enabled'] );
      
    $select->order($tableName.'.order ASC');

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(300);
    $paginator->setCurrentPageNumber( $this->_getParam('page', 1) );
    $this->view->formValues = $valuesCopy;
  }
  
  public function enableAction() {

    $id = $this->_getParam('id');
    $item = Engine_Api::_()->getItem('core_country', $id);
    if (!empty($id) && !_ENGINE_ADMIN_NEUTER) {
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect($_SERVER['HTTP_REFERER']);
  }

  public function editCountryAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    
    $id = $this->_getParam('id');
    $country = Engine_Api::_()->getItem('core_country', $id);
    
    $this->view->form = $form = new User_Form_Admin_Otp_EditCountry();
    $form->populate($country->toArray());
    if($country->iso2 == Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) {
      $form->default->setValue(1);
    }
    
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
    
      $values = $form->getValues();
      unset($values['phonecode']);
      
      if($country->iso2 == Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries', 'US')) {
        unset($values['enabled']);
        unset($values['default']);
      }
      
      $country->setFromArray($values);
      $country->save();
      
      if(!empty($values['default'])) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting('otpsms.default.countries', $country->iso2);
      }

      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully edited the country.'))
      ));

    }
  }
}
