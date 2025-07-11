<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SettingsController.php 10003 2013-03-26 22:48:26Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_SettingsController extends Core_Controller_Action_User
{
    protected $_user;

    public function init()
    {
        // Can specifiy custom id
        $id = $this->_getParam('id', null);
        $subject = null;

        if( null === $id )
        {
            if(!Engine_Api::_()->core()->hasSubject($subject)) {
                $subject = Engine_Api::_()->user()->getViewer();
                Engine_Api::_()->core()->setSubject($subject);
            }
        }
        else
        {
            $subject = Engine_Api::_()->getItem('user', $id);
            Engine_Api::_()->core()->setSubject($subject);
        }

        // Set up require's
        $this->_helper->requireUser();
        $this->_helper->requireSubject();
        $this->_helper->requireAuth()->setAuthParams(
            $subject,
            null,
            'edit'
        );

        // Set up navigation
        // $this->view->navigation = $navigation = Engine_Api::_()
        //   ->getApi('menus', 'core')
        //   ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));

        $contextSwitch = $this->_helper->contextSwitch;
        $contextSwitch
            //->addActionContext('reject', 'json')
            ->initContext();
        
        $param = $this->_getParam('param', 0);
        
//         if(empty($_SESSION['requirepassword'] ) && empty($param)) {
//             // Render
//             $this->_helper->content
//                 // ->setNoRender()
//                 ->setEnabled();
//         }
    }
    
    public function editEmailAction() {
    
      if (!$this->_helper->requireUser()->isValid()) return;

      // In smoothbox
      $this->_helper->layout->setLayout('default-simple');
      
      $user = Engine_Api::_()->core()->getSubject();
      
      $emailverify = Engine_Api::_()->authorization()->getPermission($user,'user', 'emailverify');
      
      $this->view->form = $form = new User_Form_Settings_EditEmail(array('item' => $user));
      
      // Not post/invalid
      if (!$this->getRequest()->isPost()) {
        return;
      }
      
      $this->view->emailError = 0;
      if (!$form->isValid($this->getRequest()->getPost())) {
        $this->view->emailErrors = 1;
        return;
      }

      if(isset($_POST['submit_code']) && !empty($emailverify)) {
        //2 step verfication check
        $email = $_POST['email'];
        $codeTable = Engine_Api::_()->getDbTable('codes', 'user');
        $isEmailExist = $codeTable->isEmailExist($email);
        if($isEmailExist) {
          $isEmailExist->delete();
        }
        $code = rand(100000, 999999);
        $row = $codeTable->createRow();
        $row->email = $email;
        $row->code = $code;
        $row->creation_date = date('Y-m-d H:i:s');
        $row->modified_date = date('Y-m-d H:i:s');
        $row->save();
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_changeemailotp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
        
        $form->removeElement('submit_code');
        $form->removeElement('cancel');
//         $form->addElement('Text', "code", array(
//             'label' => 'Enter Verification Code',
//             'description' => '',
//             'allowEmpty' => false,
//             'required' => true,
//         ));
        // Buttons
        $form->addElement('Button', 'submit', array(
          'label' => 'Submit',
          'type' => 'submit',
          'ignore' => true,
          'order' => 999,
          'decorators' => array('ViewHelper')
        ));

        $form->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'link' => true,
          'order' => '1000',
          'prependText' => ' or ',
          'href' => '',
          'onclick' => 'parent.Smoothbox.close();',
          'decorators' => array(
            'ViewHelper'
          )
        ));
        $form->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $form->getDisplayGroup('buttons');
        return;
      }
      
      if(isset($_POST['submit']) && !empty($emailverify)) {
        //2 step verfication check
        $inputcode = $_POST['code'];
        $email = $_POST['email'];
        $code_id = Engine_Api::_()->getDbtable('codes', 'user')->isExist($inputcode, $email);
        if(empty($code_id)) {
          $form->addError("The verification code you entered is invalid. Please enter the correct verification code.");
          
          $form->removeElement('submit_code');
          $form->removeElement('cancel');
//           $form->addElement('Text', "code", array(
//               'label' => 'Enter Verification Code',
//               'description' => '',
//               'allowEmpty' => false,
//               'required' => true,
//           ));

          // Buttons
          $form->addElement('Button', 'submit', array(
            'label' => 'Submit',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
          ));

          $form->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'href' => '',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
              'ViewHelper'
            )
          ));
          $form->addDisplayGroup(array('submit', 'cancel'), 'buttons');
          $button_group = $form->getDisplayGroup('buttons');
          return;
        } else {
          $code = Engine_Api::_()->getItem('user_code', $code_id);
          $code->delete();
        }
      }

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $user->email = $_POST['email'];
        $user->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 2000,
        'parentRefresh' => 1000,
        'messages' => array("Your email has been edited successfully!")
      ));
    }

    public function generalAction()
    {
        // Config vars
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $userSettings = Engine_Api::_()->getDbtable('settings', 'user');
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
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

        //Set names of those elements that need to be removed and are also dependent on POST
        $removeElements = array();
        // Facebook
        if( 'none' != $settings->getSetting('core.facebook.enable', 'none') ) {
            $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
            $facebook = $facebookTable->getApi();
            if( $facebook && $facebook->getUser() ) {
                $removeElements['facebook'] = 'facebook';
                $form->getElement('facebook_id')->setAttrib('checked', true);
            } else {
                $removeElements['facebook_id'] = 'facebook_id';
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('facebook');
            @$form->removeElement('facebook_id');
        }

        if ( engine_in_array('facebook_id', $removeElements) && $this->_getParam('already_integrated_fb_account') ) {
            $form->facebook->addError('Facebook account you\'re trying to connect is already connected to another account.');
        }
        // X
        if( 'none' != $settings->getSetting('core.twitter.enable', 'none') ) {
            $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
            $twitter = $twitterTable->getApi();
            if( $twitter && $twitterTable->isConnected() ) {
                $form->removeElement('twitter');
                $form->getElement('twitter_id')->setAttrib('checked', true);
            } else {
                $form->removeElement('twitter_id');
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('twitter');
            @$form->removeElement('twitter_id');
        }


        // Check if post and populate
        if( !$this->getRequest()->isPost() ) {
            // user location lat/lng
            $location = Engine_Api::_()->getDbTable('locations', 'core')->getLocationData(array('resource_type' => 'user', "resource_id" => $user->getIdentity()));
            if($location){
              $form->populate($location->toArray());
            }
            if(!empty($_SESSION['isValidCode'])) 
              unset($_SESSION['isValidCode']);
            foreach($removeElements as $elementName) {
                $form->removeElement($elementName);
            }
            $form->populate($user->toArray());

            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
            return;
        }

        // Check if valid
        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        
		if(empty($_SESSION['isValidCode'])){
			if(!empty($phone_number) || !empty($_POST['phone_number'])){
			  $country_code = explode('_', $_POST['country_code']);
			}
			if(empty($_SESSION['isValidCode']) && !empty($phone_number) && $user->country_code.$phone_number != $country_code[0].$_POST['phone_number']) {
			  $phoneNumberEl = $form->getElement('phone_number');
			  //return $phoneNumberEl->addError('Please verify your phone number.');
			  $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('Please verify your phone number.'));
			  echo json_encode(array('status' => false, 'error_message' => $errors));die;
			} else if(empty($_SESSION['isValidCode']) && !empty($_POST['phone_number']) && $user->country_code.$phone_number != $country_code[0].$_POST['phone_number']){
			  $phoneNumberEl = $form->getElement('phone_number');
			  $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('Please verify your phone number.'));
			  echo json_encode(array('status' => false, 'error_message' => $errors));die;
			  
			  //return $phoneNumberEl->addError('Please verify your phone number.');
			}
		}
        
        // -- Process --

        $values = $form->getValues();
        $values = array_merge($values, $_POST);
        // Check email against banned list if necessary
        if( ($emailEl = $form->getElement('email')) &&
            isset($values['email']) &&
            $values['email'] != $user->email ) {
            $bannedEmailsTable = Engine_Api::_()->getDbtable('BannedEmails', 'core');
            if( $bannedEmailsTable->isEmailBanned($values['email']) ) {
              $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('This email address is not available, please use another one.'));
              echo json_encode(array('status' => false, 'error_message' => $errors));die;
              //return $emailEl->addError('This email address is not available, please use another one.');
            }
        }

        if(!empty($values['phone_number']) && !empty($values['country_code'])) {
          $isPhoneNumberExist = Engine_Api::_()->getDbtable('users', 'user')->isPhoneNumberExist($values['phone_number'], $values['country_code'], $user->getIdentity());
          if(!empty($isPhoneNumberExist) && $values['phone_number'] == $user->phone_number && !empty($_SESSION['isValidCode'])) {
            $phoneNumberEl = $form->getElement('phone_number');
            $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('This phone number is already exists. Please use another one.'));
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
              
            //return $phoneNumberEl->addError('This phone number is already exists. Please use another one.');
          }
        }

        // Check username against banned list if necessary
        if( ($usernameEl = $form->getElement('username')) &&
            isset($values['username']) &&
            $values['username'] != $user->username ) {
            $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
            if( $bannedUsernamesTable->isUsernameBanned($values['username']) ) {
              $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('This username is not available, please use another one.'));
              echo json_encode(array('status' => false, 'error_message' => $errors));die;
              //return $usernameEl->addError('This username is not available, please use another one.');
            }
        }

        if(!empty($values['country_code']) && !empty($values['phone_number'])) {
            $country_code = $values['country_code'];
            $country_code = explode('_', $country_code);
            $values['country_code'] = $country_code[0];
        }
        // Set values for user object
        $user->setFromArray($values);

        // If username is changed
        //$aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
        //$user->setDisplayName($aliasValues);

        $user->save();
        
        unset($_SESSION['isValidCode']);

        // Update account type
        /*
        $accountType = $form->getValue('accountType');
        if( isset($aliasedFields['profile_type']) )
        {
          $valueRow = $aliasedFields['profile_type']->getValue($user);
          if( null === $valueRow ) {
            $valueRow = Engine_Api::_()->fields()->getTable('user', 'values')->createRow();
            $valueRow->field_id = $aliasedFields['profile_type']->field_id;
            $valueRow->item_id = $user->getIdentity();
          }
          $valueRow->value = $accountType;
          $valueRow->save();
        }
         *
         */

        // Update facebook settings
        if( isset($facebook) && $form->getElement('facebook_id') ) {
            if( $facebook->getUser() ) {
                if( empty($values['facebook_id']) ) {
                    // Remove integration
                    $facebookTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    $facebook->clearAllPersistentData();
                    unset($removeElements['facebook']);
                    $removeElements['facebook_id'] = 'facebook_id';
                }
            }
        }

        // Update twitter settings
        if( isset($twitter) && $form->getElement('twitter_id') ) {
            if( $twitterTable->isConnected() ) {
                if( empty($values['twitter_id']) ) {
                    // Remove integration
                    $twitterTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    unset($_SESSION['twitter_token2']);
                    unset($_SESSION['twitter_secret2']);
                    unset($_SESSION['twitter_token']);
                    unset($_SESSION['twitter_secret']);
                }
            }
        }

        //Location Work
        Engine_Api::_()->getApi('location', 'core')->saveLocation($_POST, $user);


        foreach($removeElements as $elementName) {
            $form->removeElement($elementName);
        }
        // Send success message
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Settings saved.');
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.')));die;
    }

    public function privacyAction()
    {

        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $auth = Engine_Api::_()->authorization()->context;

        $this->view->form = $form = new User_Form_Settings_Privacy(array(
            'item' => $user,
        ));

        // Init blocked
        $this->view->blockedUsers = array();

        if( Engine_Api::_()->authorization()->getPermission($user, 'user', 'block') ) {
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
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }

        $form->save();
        $values = $form->getValues();
        $values['view_privacy'] =  $values['privacy'];
        $user->setFromArray($values)
            ->save();

        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')));die;
    }
    
    public function requirePasswordAction(){
        return $this->_forward('password', null, null, array('format' => 'html','require_password'=>1));
    }
    
    public function passwordAction()
    {
        $user = Engine_Api::_()->core()->getSubject();
        $this->view->form = $form = new User_Form_Settings_Password();
        $form->populate($user->toArray());
        if( !$this->getRequest()->isPost() ){
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        // Check conf
        if( $form->getValue('passwordConfirm') !== $form->getValue('password') ) {
            $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('Passwords did not match'));
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
                
            //$form->getElement('passwordConfirm')->addError(Zend_Registry::get('Zend_Translate')->_('Passwords did not match'));
            //return;
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
            $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('It seems that you have used an old password. Choose a new password, to protect your account.'));
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
            
            //$form->getElement('password')->addError(Zend_Registry::get('Zend_Translate')->_('It seems that you have used an old password. Choose a new password, to protect your account.'));
            //return;
        }else if( !$valid ) {
            $errors[] = array('errorMessage' => Zend_Registry::get('Zend_Translate')->_('Old password did not match'));
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
            
            //$form->getElement('oldPassword')->addError(Zend_Registry::get('Zend_Translate')->_('Old password did not match'));
            //return;
        }
        // Save
        $db->beginTransaction();
        try {
            if(!empty($form->getValue('password')) && !$this->_writeAuthToFile($user->email, 'seiran', $form->getValue('password')) ) {
              throw new Exception('Unable to write Auth to File');
            }
            $user->setFromArray($form->getValues());
            $user->last_password_reset = date('Y-m-d H:i:s');
            $user->save();
            if($form->resetalldevice->getValue()){ 
                Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($user->user_id);
            }
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            //throw $e;
            $errors[] = array('errorMessage' => $e->getMessage());
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }
        
        if(!empty($_SESSION['requirepassword']))
            $this->_helper->redirector->gotoRoute(array());
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
        
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.')));die;
    }

    public function networkAction()
    {
        if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1))
          return $this->_forward('notfound', 'error', 'core');
          
        $viewer = Engine_Api::_()->user()->getViewer();

        $this->view->available_networks = Network_Model_Network::getUserNetworks($viewer);

        $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)
            ->order('engine4_network_networks.title ASC');
        $this->view->networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

        // Get networks to suggest
        $network_suggestions = array();
        $table = Engine_Api::_()->getItemTable('network');
        $select = $table->select()
            ->where('assignment = ?', 0)
            ->order('title ASC');

        if( null !== ($text = $this->_getParam('text', $this->_getParam('text'))))
        {
            $select->where('`'.$table->info('name').'`.`title` LIKE ?', '%'. $text .'%');
        }

        $data = array();
        foreach( $table->fetchAll($select) as $network )
        {
            if( !$network->membership()->isMember($viewer) )
            {
                $network_suggestions[] = $network;
            }
        }
        $this->view->network_suggestions = $network_suggestions;


        $this->view->form = $form = new User_Form_Settings_Network();

        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        // Process
        $viewer = Engine_Api::_()->user()->getViewer();

        if( $form->getValue('join_id') ) {
            $network = Engine_Api::_()->getItem('network', $form->getValue('join_id'));
            if( null === $network ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else if( $network->assignment != 0 ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else {
                $network->membership()->addMember($viewer)
                    ->setUserApproved($viewer)
                    ->setResourceApproved($viewer);
            }
        } else if( $form->getValue('leave_id') ) {
            $network = Engine_Api::_()->getItem('network', $form->getValue('leave_id'));
            if( null === $network ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else if( $network->assignment != 0 ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else {
                $network->membership()->removeMember($viewer);
            }
        }

        $this->_helper->redirector->gotoRoute(array());
    }

    public function notificationsAction()
    {

        $user = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();
        // Build the different notification types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $notificationTypes = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();
        $notificationSettings = Engine_Api::_()->getDbtable('notificationSettings', 'activity')->getEnabledNotifications($user);

        $notificationTypesAssoc = array();
        $notificationSettingsAssoc = array();
        foreach( $notificationTypes as $type ) {
            if(engine_in_array($type->type, array('invite_notify_admin',"abuse_report", "payment_manual_subscribe")) && !$viewer->isAdmin()){
                continue;
            }
            if(!empty($type->default)) {
              
              //if any notification is only for admin then not show to other member
              if(!$viewer->isAdmin() && $type->is_admin) continue;

							if( isset($modules[$type->module]) ) {
								$category = 'ACTIVITY_CATEGORY_TYPE_' . strtoupper($type->module);
								$translateCategory = Zend_Registry::get('Zend_Translate')->_($category);
								if( $translateCategory === $category ) {
										$elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', $type->module);
										$category = $modules[$type->module]->title;
								} else {
										$elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($translateCategory));
								}
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
        }

        ksort($notificationTypesAssoc);

        $notificationTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $notificationTypesAssoc));

        // Make form
        $this->view->form = $form = new Engine_Form(array(
            'title' => 'Notification Settings',
            'description' => 'Which of the these do you want to receive notification alerts about?',
        ));
        $form->setAttrib('class', 'global_form form_submit_ajax');

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

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
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
        Engine_Api::_()->getDbtable('notificationSettings', 'activity')
            ->setEnabledNotifications($user, $values);

        $form->addNotice('Your changes have been saved.');
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')));die;
    }


    public function emailsAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $viewer = Engine_Api::_()->user()->getViewer();
        // Build the different email types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $emailTypes = Engine_Api::_()->getDbTable('mailTemplates', 'core')->getEmailTypes();
        $emailSettings = Engine_Api::_()->getDbtable('emailSettings', 'user')->getEnabledEmails($user);
        
        $emailTypesAssoc = array();
        $emailSettingsAssoc = array();
        foreach( $emailTypes as $type ) {
            if($type->type == "abuse_report" && !$viewer->isAdmin()){
                continue;
            }
            if(!empty($type->default)) {
              //if any email template is only for admin then not show to other member
              if(!$viewer->isAdmin() && $type->is_admin) continue;
              
							if( isset($modules[$type->module]) ) {
									$category = 'ACTIVITY_CATEGORY_TYPE_' . strtoupper($type->module);
									$translateCategory = Zend_Registry::get('Zend_Translate')->_($category);
									if( $translateCategory === $category ) {
											$elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', $type->module);
											$category = $modules[$type->module]->title;
									} else {
											$elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($translateCategory));
									}
							} else {
									$elementName = 'misc';
									$category = 'Misc';
							}

							$emailTypesAssoc[$elementName]['category'] = $category;
							$emailTypesAssoc[$elementName]['types'][$type->type] = '_EMAIL_' . strtoupper($type->type) . '_TITLE';

							if( engine_in_array($type->type, $emailSettings) ) {
									$emailSettingsAssoc[$elementName][] = $type->type;
							}
						}
        }

        ksort($emailTypesAssoc);

        $emailTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $emailTypesAssoc));

        // Make form
        $this->view->form = $form = new Engine_Form(array(
            'title' => 'Email Settings',
            'description' => 'Which of the these do you want to receive email alerts about?',
        ));
        $form->setAttrib('class', 'global_form form_submit_ajax');

        // Disable all Email
        $form->addElement('Checkbox', 'disable_email', array(
            'label' => 'Disable all site emails?',
            'value' => !empty($user->disable_email) ? $user->disable_email : 0,
            'onclick' => "disableEmail(this);",
        ));
        // Disable all admin Email
        $form->addElement('Checkbox', 'disable_adminemail', array(
            'label' => 'Disable all admin emails',
            'value' => !empty($user->disable_adminemail) ? $user->disable_adminemail : 0,
        ));
        foreach( $emailTypesAssoc as $elementName => $info ) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array) @$emailSettingsAssoc[$elementName],
                'class' => 'email_settings'
            ));
        }

        $form->addElement('Button', 'execute', array(
            'label' => 'Save Changes',
            'type' => 'submit',
        ));

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }

        // Process
        $values = array();
        foreach( $form->getValues() as $key => $value ) {
            if( !is_array($value) ) continue;

            foreach( $value as $skey => $svalue ) {
                if( !isset($emailTypesAssoc[$key]['types'][$svalue]) ) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        // Disable all email
        $user->disable_email = !empty($form->getElement('disable_email')->getValue()) ? $form->getElement('disable_email')->getValue() : '0';
        $user->disable_adminemail = !empty($form->getElement('disable_adminemail')->getValue()) ? $form->getElement('disable_adminemail')->getValue() : '0';
        $user->save();

        if(empty($user->disable_email)) {
            // Set email setting
            Engine_Api::_()->getDbtable('emailSettings', 'user')->setEnabledEmails($user, $values);
        }

        if(!empty($user->disable_email)) {
            foreach( $emailTypesAssoc as $elementName => $info ) {
                $form->addElement('MultiCheckbox', $elementName, array(
                    'label' => $info['category'],
                    'multiOptions' => $info['types'],
                    'value' => (array) @$emailSettingsAssoc[$elementName],
                    'class' => 'email_settings'
                ));
            }
        }
        $form->addNotice('Your changes have been saved.');
        echo json_encode(array('status' => true, 'redirectURL' => '', 'success_message' => Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')));die;
    }

    public function deleteAction()
    {

        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        if( !$this->_helper->requireAuth()->setAuthParams($user, null, 'delete')->isValid() ) return;

        $this->view->isSuperAdmin = false;
        if( 1 === $user->level_id ) {
            $this->view->isSuperAdmin = true;
            return;
        }

        // Form
        $this->view->form = $form = new User_Form_Settings_Delete();

        if( !$this->getRequest()->isPost()){
          return; 
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
          return;
        }
        
        // Process
        $db = Engine_Api::_()->getDbtable('users', 'user')->getAdapter();
        $db->beginTransaction();

        try {
            $user->delete();
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }

        // Unset viewer, remove auth, clear session
        Engine_Api::_()->user()->setViewer(null);
        Zend_Auth::getInstance()->getStorage()->clear();
        Zend_Session::destroy();

        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    
    protected function _writeAuthToFile($user, $realm, $password) {

      // Try using normal fs op
      if( $this->_htpasswd(APPLICATION_PATH . '/install/config/auth.php', $user, $realm, $password) ) {
        return true;
      }

      // Try using ftp
      if( !empty($this->_session->ftp) && !empty($this->_session->ftp['target']) ) {
        try {
          $ftp = Engine_Package_Utilities::ftpFactory($this->_session->ftp);
          $rfile = $this->_session->ftp['target'] . 'install/config/auth.php';
          $tmpfile = tempnam('/tmp', md5(time() . rand(0, 1000000)));
          //chmod($tmpfile, 0777);
          $ret = $ftp->get($rfile, $tmpfile, true);
          if( $ftp->isError($ret) ) {
            throw new Engine_Exception($ret->getMessage());
          }
          if( !$this->_htpasswd($tmpfile, $user, $realm, $password) ) {
            throw new Engine_Exception('Unable to write to tmpfile');
          }
          $ret = $ftp->put($tmpfile, $rfile, true);
          if( $ftp->isError($ret) ) {
            // Try to chmod + write + unchmod
            $ret2 = $ftp->chmod($rfile, '0777');
            if( $ftp->isError($ret2) ) {
              throw new Engine_Exception($ret2->getMessage());
            }
            $ret2 = $ftp->put($tmpfile, $rfile, true);
            if( $ftp->isError($ret2) ) {
              throw new Engine_Exception($ret2->getMessage());
            }
            $ret2 = $ftp->chmod($rfile, '0755');
            if( $ftp->isError($ret2) ) {
              throw new Engine_Exception($ret2->getMessage());
            }
          }

        } catch( Exception $e ) {
          throw $e;
        }
      }

      throw new Engine_Exception('Unable to write to auth file');
    }

    protected function _htpasswd($file, $user, $realm, $password)
    {
      $newLine = $user . ':' . $realm . ':' . md5($user . ':' . $realm . ':' . $password);

      // Read file
      $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if( !$lines ) {
        return false;
      }

      // Search for existing
      $found = false;
      $userRealm = $user . ':' . $realm;
      foreach( $lines as $index => $line ) {
        if( $line == $newLine ) {
          // Same password
          return true;
        } else if( substr($line, 0, strlen($userRealm)) == $userRealm ) {
          // Different password
          if( !$found ) {
            $lines[$index] = $newLine;
            $found = true;
          } else {
            unset($lines[$index]); // Prevent multiple user-realm combos
          }
        }
      }

      if( !$found ) {
        $lines[] = $newLine;
      }

      if( !@file_put_contents($file, join("\n", $lines)) ) {
        return false;
      }

      return true;
    }
}
