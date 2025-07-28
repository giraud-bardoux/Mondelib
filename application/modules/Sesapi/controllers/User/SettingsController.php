<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: SettingsController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class User_SettingsController extends Sesapi_Controller_Action_Standard
{
  protected $_user;

  public function initfn()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;
    
    if((!Engine_Api::_()->user()->getViewer()->getIdentity())){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'unauthorize_access', 'result' => array()));  
    }
    if( null === $id )
    {
      $subject = Engine_Api::_()->user()->getViewer();
      Engine_Api::_()->sesapi()->setSubject(Engine_Api::_()->getItem('user',Engine_Api::_()->user()->getViewer()->getIdentity()));
    } else {
      $subject = Engine_Api::_()->getItem('user', $id);
      Engine_Api::_()->sesapi()->setSubject($subject);
    }
  }

  public function generalAction() {
  
    $this->initfn();
    
    // Config vars
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $userSettings = Engine_Api::_()->getDbTable('settings', 'user');
    $this->view->user = $user = Engine_Api::_()->sesapi()->getSubject();
    $this->view->form = $form = new User_Form_Settings_General(array(
        'item' => $user
    ));
    
    $phone_number = !empty($user->phone_number) ? $user->phone_number : '';

    // Set up profile type options
    /*
    $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
    if( isset($aliasedFields['profile_type']) )
    {
      $options = $aliasedFields['profile_type']->getElementParams($user);
      unset($options['options']['order']);
      $form->accountType->setOptions($options['options']);
    }
    else
    { */
    $form->removeElement('accountType');
    /* } */
    
    // Removed disabled features
    if( $form->getElement('username') && (empty(Engine_Api::_()->authorization()->getPermission($user, 'user', 'username')) || empty(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1))) ) {
        $form->removeElement('username');
    }
    
    if($form->facebook)
      $form->removeElement('facebook');
    if($form->facebook_id)
      $form->removeElement('facebook_id');  
    if($form->twitter)
      $form->removeElement('twitter'); 
    if($form->twitter_id)
      $form->removeElement('twitter_id');  
    if($form->token)
      $form->removeElement('token');
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $form->populate($user->toArray());     
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('country_code' => $user->country_code,'enabletwostep' => $settings->getSetting('user.signup.enabletwostep', 0)));
    }
    
    // Check if post and populate
    if( !$this->getRequest()->isPost() ) {
      if(!empty($_SESSION['isValidCode'])) 
        unset($_SESSION['isValidCode']);

      $form->populate($user->toArray());
      
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => Zend_Registry::get('Zend_Translate')->_('Invalid method')));
    }

    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);
    }
    
    if(empty($_SESSION['isValidCode']) && !empty($phone_number) && $phone_number != $_POST['phone_number']) {
      $phoneNumberEl = $form->getElement('phone_number');
      $phoneNumberEl->addError('Please verify your phone number.');
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);  
    } else if(empty($_SESSION['isValidCode']) && $phone_number != $_POST['phone_number']){
      $phoneNumberEl = $form->getElement('phone_number');
      $phoneNumberEl->addError('Please verify your phone number.');
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);  
    }

    // -- Process --

    $values = $form->getValues();
    $values = array_merge($values, $_POST);
    $error = false;
    
    // Check email against banned list if necessary
    if( ($emailEl = $form->getElement('email')) &&
        isset($values['email']) &&
        $values['email'] != $user->email ) {
      $bannedEmailsTable = Engine_Api::_()->getDbTable('BannedEmails', 'core');
      if( $bannedEmailsTable->isEmailBanned($values['email']) ) {
        $emailEl->addError('This email address is not available, please use another one.');
        $error = true;
      }
    }
    
    if(!empty($values['phone_number']) && !empty($values['country_code'])) {
      $isPhoneNumberExist = Engine_Api::_()->getDbTable('users', 'user')->isPhoneNumberExist($values['phone_number'], $values['country_code']);
      if(!empty($isPhoneNumberExist) && $values['phone_number'] == $user->phone_number && !empty($_SESSION['isValidCode'])) {
        $phoneNumberEl = $form->getElement('phone_number');
        $error = true;
        $phoneNumberEl->addError('This phone number is already exists. Please use another one.');
      }
    }
    
    // Check username against banned list if necessary
    if( ($usernameEl = $form->getElement('username')) &&
      isset($values['username']) &&
      $values['username'] != $user->username ) {
      $bannedUsernamesTable = Engine_Api::_()->getDbTable('BannedUsernames', 'core');
      if( $bannedUsernamesTable->isUsernameBanned($values['username']) ) {
        $error = true;
        $usernameEl->addError('This profile address is not available, please use another one.');
      }
    }

    if($error){
       $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
       $this->validateFormFields($validateFields);  
    }
    
    // Set values for user object
    $user->setFromArray($values);
    unset($_SESSION['isValidCode']);
    
    $user->save();
    
    // Send success message
    $message = $this->view->translate('Settings were successfully saved.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$message)); 
  }

  public function privacyAction()
  {    
    $this->initfn();
    
    $user = Engine_Api::_()->sesapi()->getSubject();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $auth = Engine_Api::_()->authorization()->context;
    
    $this->view->form = $form = new User_Form_Settings_Privacy(array(
      'item' => $user,
    ));

    // Init blocked
    $this->view->blockedUsers = array();

    if(Engine_Api::_()->authorization()->getPermission($user, 'user', 'block')) {
      foreach ($user->getBlockedUsers() as $blocked_user_id) {
        $this->view->blockedUsers[] = Engine_Api::_()->user()->getUser($blocked_user_id);
      }
    } else {
      $form->removeElement('blockList');
    }
    
    // Hides options from the form if there are less then one option.
    if( engine_count($form->privacy->options) <= 1 ) {
        $form->removeElement('privacy');
    }
    if( engine_count($form->comment->options) <= 1 ) {
        $form->removeElement('comment');
    }
    if( engine_count($form->mention->options) <= 1 ) {
        $form->removeElement('mention');
    }

    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poke') && engine_count($form->pokeAction->options) <= 1 ) {
      $form->removeElement('pokeAction');
    }
    
    // Populate form
    $form->populate($user->toArray());
    if(empty($user->birthday_format) && $form->getElement("birthday_format")){
      $form->populate(array('birthday_format'=>'monthdayyear'));
    }
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $newFormFieldsArray = array();
      foreach($formFields as $fields){ //echo "<pre>";var_dump($fields);die;
        foreach($fields as $key => $field){  
          if($key == "value" && $fields['name'] == "publishTypes"){ 
            if(engine_count($fields['value'])){
              $val = $fields['value'];
              unset($fields["value"]);
              $fields["value"] = (array_flip($val));     
              sort( $fields["value"]);           
            }
          }
        }
        $newFormFieldsArray[] = $fields;
      }
      $this->generateFormFields($newFormFieldsArray);
    }
    
    if(!empty($_POST["publishTypes"])){
      $publishTypes = array_filter($_POST["publishTypes"],function($a){  return  ($a != 0); });
      $_POST["publishTypes"] = array_keys($publishTypes);
    }
    
    // Check if valid
    if( !$form->isValid($_POST) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);
    }

    $form->save();
    $values = $form->getValues();
   
    if($values["search"] == 1)
      $values["search"] = 0;
    else
      $values["search"] = 1;
    
    $user->setFromArray($values)->save();

    $message = $this->view->translate('Your changes have been saved.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$message));
  }
  
  public function requirePasswordAction(){
      return $this->_forward('password', null, null, array('format' => 'html','require_password'=>1));
  }
  
  public function passwordAction()
  {
    $this->initfn();
    
    $user = Engine_Api::_()->sesapi()->getSubject();

    $this->view->form = $form = new User_Form_Settings_Password();
    $form->removeElement('passwordroutine');
    $form->removeElement('showhidenewpassword');
    $form->removeElement('showhideconfirmpassword');
    $form->removeElement('showhidepassword');

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('password_des' => array($this->view->translate("Weak"), $this->view->translate("Strong")), 'password_hint' => $this->view->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.')));
    }

    // Check if valid
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);
    }


    // Check conf
    if( $form->getValue('passwordConfirm') !== $form->getValue('password') ) {
      $form->getElement('passwordConfirm')->addError($this->view->translate('Passwords did not match'));
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);
    }
    
    // Process form
    $userTable = Engine_Api::_()->getItemTable('user');
    $db = $userTable->getAdapter();

    // Check old password
    $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');
      $valid = false;
      $matchOldPassword = false;
      if(strlen($user->password) > 32){
          $select = $userTable->select()
              ->from($userTable, 'password')
              ->where('user_id = ?', $user->getIdentity())
              ->limit(1)
          ;
          $result = $select
              ->query()
              ->fetchColumn()
          ;
          if($result) {
              $valid = password_verify($form->getValue('oldPassword'),$result);
              if($valid){
                  if(password_verify($form->getValue('password'),$result)) {
                      $matchOldPassword = true;
                  }
              }
          }
      }else{
          $password = new Zend_Db_Expr(sprintf('MD5(CONCAT(%s, %s, salt))', $db->quote($salt), $db->quote($form->getValue('oldPassword'))));
          $select = $userTable->select()
              ->from($userTable, new Zend_Db_Expr('TRUE'))
              ->where('user_id = ?', $user->getIdentity())
              ->where('password = ?', $password)
              ->limit(1)
          ;
          $valid = $select
              ->query()
              ->fetchColumn()
          ;
      }
      if($matchOldPassword){
          $form->getElement('password')->addError(Zend_Registry::get('Zend_Translate')->_('It seems that you have used an old password. Choose a new password, to protect your account.'));
          $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
          $this->validateFormFields($validateFields);
      }else if( !$valid ) {
          $form->getElement('oldPassword')->addError(Zend_Registry::get('Zend_Translate')->_('Old password did not match'));
          $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
          $this->validateFormFields($validateFields);
      }

    
    // Save
    $db->beginTransaction();

    try {
      $user->setFromArray($form->getValues());
      $user->last_password_reset = date('Y-m-d H:i:s');
      $user->setFromArray($form->getValues());
      $user->save();
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }

    $message = $this->view->translate('Settings were successfully saved.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$message));
  }

  public function networkAction()
  {
    $this->initfn();
    $viewer = Engine_Api::_()->sesapi()->getSubject();
    
    if( isset($_POST['join_id']) ) {
      $network = Engine_Api::_()->getItem('network', $_POST['join_id']);
      if( null === $network ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Network not found"),'result'=>array()));
      } else if( $network->assignment != 0 ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Network not found"),'result'=>array()));
      } else {
        $network->membership()->addMember($viewer)
          ->setUserApproved($viewer)
          ->setResourceApproved($viewer);

        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>"success"));
      }
    } else if( isset($_REQUEST['leave_id']) ) {
      $network = Engine_Api::_()->getItem('network', $_REQUEST['leave_id']);
      if( null === $network ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Network not found"),'result'=>array()));
      } else if( $network->assignment != 0 ) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Network not found"),'result'=>array()));
      } else {
        $network->membership()->removeMember($viewer);
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>"success"));
      }
    }
    // Get networks to suggest
    $allNetwork = array();
    $table = Engine_Api::_()->getItemTable('network');
    $select = $table->select()
      ->where('assignment = ?', 0)
      ->order('title ASC');

    $data = array();
    $availableNetworkCounter = 0;
    $selectedNetworkCounter = 0;
    //echo $select;die;
    foreach( $table->fetchAll($select) as $network )
    {
      if( !$network->membership()->isMember($viewer) )
      {
        $allNetwork["networkAvailable"][$availableNetworkCounter] = $network->toArray();
        $availableNetworkCounter++;
      }
    }
    $select = Engine_Api::_()->getDbTable('membership', 'network')->getMembershipsOfSelect($viewer)
            ->order('engine4_network_networks.title ASC');
    foreach( Engine_Api::_()->getDbTable('networks', 'network')->fetchAll($select) as $network )
    {
      $allNetwork["networkSelected"][$selectedNetworkCounter] = $network->toArray();
        $selectedNetworkCounter++;
    }
   
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$allNetwork));
  }

  public function notificationsAction()
  {
    $this->initfn();
    
    $user = Engine_Api::_()->sesapi()->getSubject();
    
    // Build the different notification types
    $modules = Engine_Api::_()->getDbTable('modules', 'core')->getModulesAssoc();
    $notificationTypes = Engine_Api::_()->getDbTable('notificationTypes', 'activity')->getNotificationTypes();
    $notificationSettings = Engine_Api::_()->getDbTable('notificationSettings', 'activity')->getEnabledNotifications($user);

    $notificationTypesAssoc = array();
    $notificationSettingsAssoc = array();
    foreach( $notificationTypes as $type ) {
      if( engine_in_array($type->module, array('core', 'activity', 'fields', 'authorization', 'messages', 'user')) ) {
        $elementName = 'general';
        $category = 'General';
      } else if( isset($modules[$type->module]) ) {
        $elementName = preg_replace('/[^a-zA-Z0-9]+/', '-', $type->module);
        $category = $modules[$type->module]->title;
      } else {
        $elementName = 'misc';
        $category = 'Misc';
      }

      $notificationTypesAssoc[$elementName]['category'] = $category;
      $notificationTypesAssoc[$elementName]['types'][$type->type] = 'ACTIVITY_TYPE_' . strtoupper($type->type);

      if( engine_in_array($type->type, $notificationSettings) ) {
        $notificationSettingsAssoc[$elementName][] = $type->type;
      }
    }

    ksort($notificationTypesAssoc);

    $notificationTypesAssoc = array_filter(array_merge(array(
      'general' => array(),
      'misc' => array(),
    ), $notificationTypesAssoc));

    // Make form
    $this->view->form = $form = new Engine_Form();

    foreach( $notificationTypesAssoc as $elementName => $info ) {
      $form->addElement('MultiCheckbox', $elementName, array(
        'label' => $info['category'],
        'multiOptions' => $info['types'],
        'value' => (array) @$notificationSettingsAssoc[$elementName],
      ));
    }

    $form->addElement('Button', 'execute', array(
      'label' => 'Save Changes',
      'type' => 'submit',
    ));

   // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    foreach($_POST as $key=>$value){
      if(is_array($_POST[$key])){
        $values = array_filter($_POST[$key],function($a){  return  ($a != 0); });
        $_POST[$key] = array_keys($values);  
       } 
    }
    // Check if valid
    if( !$form->isValid($_POST) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $this->validateFormFields($validateFields);
    }


    // Process
    $values = array();
    foreach( $form->getValues() as $key => $value ) {
      if( !is_array($value) ) continue;
      
      foreach( $value as $skey => $svalue ) {
        if( !isset($notificationTypesAssoc[$key]['types'][$svalue]) ) {
          continue;
        }
        $values[] = $svalue;
      }
    }
    
    // Set notification setting
    Engine_Api::_()->getDbTable('notificationSettings', 'activity')
        ->setEnabledNotifications($user, $values);
    $message = $this->view->translate('Your changes have been saved.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"",'result'=>$message));
  }

  public function deleteAction() {
    
    $this->initfn();
    
    $user = Engine_Api::_()->sesapi()->getSubject();
    if( !$this->_helper->requireAuth()->setAuthParams($user, null, 'delete')->isValid() ) 
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'unauthorize_access', 'result' => array()));

    $this->view->isLastSuperAdmin   = false;
    if( 1 === engine_count(Engine_Api::_()->user()->getSuperAdmins()) && 1 === $user->level_id ) {
      $this->view->isLastSuperAdmin = true;
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'unauthorize_access', 'result' => array())); 
    }
  
    // Process
    $db = Engine_Api::_()->getDbTable('users', 'user')->getAdapter();
    $db->beginTransaction();

    try {
      $user->delete();
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array())); 
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('delete'=>array('0'=>array('success'=>1))))); 
  }
}
