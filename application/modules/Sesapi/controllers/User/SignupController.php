<?php

/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: SignupController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class User_SignupController extends Sesapi_Controller_Action_Standard {

	public function init() {
	}
	
	public function checkVersion($android, $ios) {
		if (_SESAPI_VERSION_ANDROID > 0 && _SESAPI_VERSION_ANDROID >= $android)
			return true;
		if (_SESAPI_VERSION_IOS > 0 && _SESAPI_VERSION_IOS >= $ios)
			return true;
		return false;
	}
	
	public function indexAction() {
	
    $settings = Engine_Api::_()->getApi('settings', 'core'); 
    
		// Get settings
		if (isset($_REQUEST['device_uuid']))
			$_SESSION['device_uuid'] = $_REQUEST['device_uuid'];

		// Get viewer
		$viewer = Engine_Api::_()->user()->getViewer();

		if ($this->_getParam('getForm') == "account") {

			$_SESSION['completedFormSesapi'] = array();

			//$form = new Sesapi_Form_Signup_Account();
			$form = new User_Form_Signup_Account();
      
      $isSocialLoginVerirfied = '';
			if (!empty($_SESSION['facebook_signup']) || !empty($_SESSION['google_signup']) || !empty($_SESSION['twitter_signup']) || !empty($_SESSION['linkedin_signup'])) {
				if (($emailEl = $form->getElement($form->getEmailElementFieldName())) && !$emailEl->getValue() && !empty($_SESSION['Email'])) {
					$emailEl->setValue($_SESSION['Email']);
				}
				
// 				if (($usernameEl = $form->getElement('username')) && !$usernameEl->getValue() && !empty($_SESSION['username'])) {
//           $usernameEl->setValue(preg_replace('/[^A-Za-z]/', '', $_SESSION['username']));
//         }
        
        if (($firstnameEl = $form->getElement('firstname')) && !$firstnameEl->getValue() && !empty($_SESSION['FirstName'])) {
          $firstnameEl->setValue($_SESSION['FirstName']);
        }
        
        if (($lastnameEl = $form->getElement('lastname')) && !$lastnameEl->getValue() && !empty($_SESSION['LastName'])) {
          $lastnameEl->setValue($_SESSION['LastName']);
        }
        $isSocialLoginVerirfied = $this->view->translate('Verified');
			}

			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('formTitle' => $this->view->translate($form->getTitle()), 'formDescription' => $this->view->translate($form->getDescription()),'enabletwostep' => $settings->getSetting('user.signup.enabletwostep', 0), 'password_des' => array($this->view->translate("Weak"), $this->view->translate("Strong")), "otp_text" => array('title' => "Validate OTP (One Time Password)", "resend_text" => "Resend", "expire_text" => "OTP Expired"), 'password_hint' => $this->view->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.'), 'is_sociallogin_verified' => $isSocialLoginVerirfied));
			
		} else if ($this->_getParam('validateAccountForm')) {
		
			$form = new User_Form_Signup_Account();
			$valid = true;
			if (!$form->isValid($this->getRequest()->getPost())) {
				$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
				if (is_countable($validateFields) && engine_count($validateFields)) {
					$valid = false;
					$this->validateFormFields($validateFields);
				}
			} else if(!isset($_POST['submit_signup'])) {

        $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
        $translate = Zend_Registry::get('Zend_Translate');
        
        if($settings->getSetting('user.signup.enabletwostep', 0) == 1 || !empty($otpsms_signup_phonenumber)) {
        
          $validCheck = false;
          if(empty($_SESSION['isValidCode'])) {
            $validCheck = true;
          } else if(!empty($_SESSION['isValidCode']) && $_SESSION['isValidCode'] != $_POST['email']) {
            $validCheck = true;
          }
          
          if($validCheck) {
            if(!empty($otpsms_signup_phonenumber)) {
              $label = $translate->translate('Phone Number or email address');
              if(is_numeric($_POST['email']))
                $description = $translate->translate('Please verify your phone number.');
              else if($settings->getSetting('user.signup.enabletwostep', 0)) 
                $description = $translate->translate('Please verify your email address.');
              else if(empty($settings->getSetting('user.signup.enabletwostep', 0)))  {
                //Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => ''));
              }
            } else {
              $label = $translate->translate('Email Address');
              $description = $translate->translate('Please verify your email address.');
            }
            
            if(!empty($description))
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $description, 'result' => array('status' => false, 'isRequired' => true, 'label' => $label, 'errorMessage' => $description)));
          }
        }
      }

			if ($valid) {
				$session = new Zend_Session_Namespace(get_class($form));
				$session->data = $form->getValues();
				$class = "";
				$error = '';
				
        foreach( Engine_Api::_()->getDbTable('signup', 'user')->fetchAll() as $row ) {
          if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Fields") !== false && !engine_in_array('Sesapi_Form_Signup_Fields',$_SESSION['completedFormSesapi'])){
            $class = "Sesapi_Form_Signup_Fields";
            $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Fields";
            break;
          } else if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Photo") !== false && !engine_in_array('Sesapi_Form_Signup_Photo',$_SESSION['completedFormSesapi'])) {
            $class = "Sesapi_Form_Signup_Photo";
            $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Photo";
            break;
          } 
//           else if($row->enable == 1 && strpos($row->class,"Sesinterest_Plugin_Signup_Interest") !== false && !engine_in_array('Sesinterest_Plugin_Signup_Interest',$_SESSION['completedFormSesapi']) && $this->checkVersion(2.7,3.7) && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesinterest')  && $this->checkVersion(2.7,3.7)) {
//             $class = "Sesinterest_Plugin_Signup_Interest";
//             $_SESSION['completedFormSesapi'][] = "Sesinterest_Plugin_Signup_Interest";
//             break;
//           }
        }
        
				if (!$class) {
					$this->signupUserSubmit();
				} else {
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $class));
				}
			}
		} else if($this->_getParam('getForm') == "fields") {
		
      $formArgs = $this->getTopLevelValues();

      $form = new Sesapi_Form_Signup_Fields($formArgs);
      
      if(!empty($_SESSION['facebook_signup']) || !empty($_SESSION['google_signup']) || empty($_SESSION['twitter_signup']) || empty($_SESSION['linkedin_signup'])){
        //populate fields, using Facebook data
        $fb_data = array();
        $fb_keys = array('first_name' => "FirstName", 'last_name' => "LastName");
        foreach( $fb_keys as $key => $value ) {
          if( isset($_SESSION[$value]) ){
            $fb_data[$key] = $_SESSION[$value];
          }
        }
        $struct = $form->getFieldStructure();
        foreach( $struct as $fskey => $map ){
          $field = $map->getChild();
          if( $field->isHeading() ) continue;
          if( isset($field->type) && array_key_exists($field->type, $fb_keys) ) {
            $el_key = $map->getKey();
            $el_val = $fb_data[$field->type];
            $el_obj = $form->getElement($el_key);
            if( $el_obj instanceof Zend_Form_Element &&
                !$el_obj->getValue() ) {
              $el_obj->setValue($el_val);
            }
          }
        }
      }
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields); 
    } else if($this->_getParam('validateFieldsForm')) {

      $formArgs = $this->getTopLevelValues();
      $form = new Sesapi_Form_Signup_Fields($formArgs);
      $values = $this->getRequest()->getPost();
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      foreach($formFields as $key => $value){
        if($value['type'] == "Date"){
          $date = $values[$value['name']];  
          $date = str_replace('/', '-', $date);
          if(!empty($date) && !is_null($date)){
            $name  = $value['name'];
            unset($values[$value['name']]);
            $values[$name]['month'] = date('m',strtotime($date));
            $values[$name]['year'] = date('Y',strtotime($date));
            $values[$name]['day'] = date('d',strtotime($date));
          }
        }  
      }
      
      if( !$form->isValid($values) ) {
        $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
        $this->validateFormFields($validateFields);
      } else {
        $session = new Zend_Session_Namespace(get_class($form));
        $session->data = $form->getProcessedValues();
        $class = "";
        $error = '';
        
        foreach( Engine_Api::_()->getDbTable('signup', 'user')->fetchAll() as $row ) {
          if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Fields") !== false && !engine_in_array('Sesapi_Form_Signup_Fields',$_SESSION['completedFormSesapi'])){
            $class = "Sesapi_Form_Signup_Fields";
            $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Fields";
            break;
          } else if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Photo") !== false && !engine_in_array('Sesapi_Form_Signup_Photo',$_SESSION['completedFormSesapi'])) {
            $class = "Sesapi_Form_Signup_Photo";
            $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Photo";
            break;
          } 
//           else if($row->enable == 1 && strpos($row->class,"Sesinterest_Plugin_Signup_Interest") !== false && !engine_in_array('Sesinterest_Plugin_Signup_Interest',$_SESSION['completedFormSesapi']) && $this->checkVersion(2.7,3.7) && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesinterest')  && $this->checkVersion(2.7,3.7)) {
//             $class = "Sesinterest_Plugin_Signup_Interest";
//             $_SESSION['completedFormSesapi'][] = "Sesinterest_Plugin_Signup_Interest";
//             break;
//           }
        }
        
        if(!$class){
          $this->signupUserSubmit();
        } else {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$class)); 
        }
      }
    } else if($this->_getParam('validatePhotoForm')){
    
      $session = new Zend_Session_Namespace("Sesapi_Form_Signup_Photo");

      if(!empty($_FILES['image']['name']) && $_FILES['image']['size'] > 0){
        $this->_resizeImages($_FILES,$session);
      } else if($this->_getParam('image'))
        $this->_fetchImage($this->_getParam('image'),$session);
        
      $class = "";
      $error = '';
      
      foreach( Engine_Api::_()->getDbTable('signup', 'user')->fetchAll() as $row ) {
        if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Fields") !== false && !engine_in_array('Sesapi_Form_Signup_Fields',$_SESSION['completedFormSesapi'])){
          $class = "Sesapi_Form_Signup_Fields";
          $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Fields";
          break;
        } else if($row->enable == 1 && strpos($row->class,"Plugin_Signup_Photo") !== false && !engine_in_array('Sesapi_Form_Signup_Photo',$_SESSION['completedFormSesapi'])) {
          $class = "Sesapi_Form_Signup_Photo";
          $_SESSION['completedFormSesapi'][] = "Sesapi_Form_Signup_Photo";
          break;
        } 
//         else if($row->enable == 1 && strpos($row->class,"Sesinterest_Plugin_Signup_Interest") !== false && !engine_in_array('Sesinterest_Plugin_Signup_Interest',$_SESSION['completedFormSesapi']) && $this->checkVersion(2.7,3.7) && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesinterest')  && $this->checkVersion(2.7,3.7)) {
//           $class = "Sesinterest_Plugin_Signup_Interest";
//           $_SESSION['completedFormSesapi'][] = "Sesinterest_Plugin_Signup_Interest";
//           break;
//         }
      }
      
      if(!$class){
        $this->signupUserSubmit();
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>$class)); 
      }
    }
	}

	public function signupUserSubmit() {
	
		$this->accountSubmit();
		
		$this->fieldSubmit();

		$session = new Zend_Session_Namespace("Sesapi_Form_Signup_Photo");
    
    if(!empty($session->tmp_file_id) ) {
      $this->photoSubmit();
    }
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesinterest')  && $this->checkVersion(2.7,3.7)){
      $this->interestsSubmit();
    }
    
		$this->signupUserSuccess();

		$user = Engine_Api::_()->user()->getViewer();
		
		//send new signup user
		$result["user_id"] = $user->user_id;
		$result["email"] = $user->email;
		$result["username"] = $user->username;
		$result["displayname"] = $user->getTitle();
		$result["photo_id"] = $user->photo_id;
		$result["status"] = $user->status;
		$result["password"] = $user->password;
		$result["status_date"] = $user->status_date;
		$result["salt"] = $user->salt;
		$result["locale"] = $user->locale;
		$result["language"] = $user->language;
		$result["timezone"] = $user->timezone;
		$result["search"] = $user->search;
		$result["level_id"] = $user->level_id;
		
		if(!empty($user->country_code) && !empty($user->phone_number)) { 
      $result["country_code"] = $user->country_code;
      $result["phone_number"] = $user->phone_number;
		}

		if (!empty($result['photo_id'])) {
			$photo = $this->getBaseUrl(false, $user->getPhotoUrl());
			$result['photo_url'] = $photo;
		} else
			$result['photo_url'] = $this->getBaseUrl() . '/application/modules/User/externals/images/nophoto_user_thumb_profile.png';

		//Auth token
		$token = Engine_Api::_()->getApi('oauth', 'sesapi')->generateOauthToken();
		$token->user_id = $result['user_id'];
		$token->save();
		
		//Register device token
		Engine_Api::_()->getDbTable('users', 'sesapi')->register(array('user_id' => $result['user_id'], 'device_uuid' => $_SESSION['device_uuid']));
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => $result, 'token' => $token->token));
		$_SESSION['completedFormSesapi'] = array();
	}
	
	public function signupUserSuccess() {
	
		$viewer = Engine_Api::_()->user()->getViewer();
		
		// Run post signup hook
		$event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserSignupAfter', $viewer);
		$responses = $event->getResponses();
		if ($responses) {
			foreach ($event->getResponses() as $response) {
				//silence
			}
		}

		// Handle subscriptions
		if (Engine_Api::_()->hasModuleBootstrap('payment')) {
			// Check for the user's plan
			$subscriptionsTable = Engine_Api::_()->getDbTable('subscriptions', 'payment');
			if (!$subscriptionsTable->check($viewer)) {

				// Handle default payment plan
				$defaultSubscription = null;
				try {
					$subscriptionsTable = Engine_Api::_()->getDbTable('subscriptions', 'payment');
					if ($subscriptionsTable) {
						$defaultSubscription = $subscriptionsTable->activateDefaultPlan($viewer);
						if ($defaultSubscription) {
							// Re-process enabled?
							$viewer->enabled = true;
							$viewer->save();
						}
					}
				} catch (Exception $e) {
					// Silence
				}

				if (!$defaultSubscription) {
					// Redirect to subscription page, log the user out, and set the user id
					// in the payment session
					$subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
					$subscriptionSession->user_id = $viewer->getIdentity();
					$user = $viewer->getIdentity();
					Engine_Api::_()->user()->setViewer(null);
					Engine_Api::_()->user()->getAuth()->getStorage()->clear();

					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'Sesapi_Form_Signup_Subscription'), $user);
				}
			}
		}

		// Handle email verification or pending approval
		if (!$viewer->enabled) {
			Engine_Api::_()->user()->setViewer(null);
			Engine_Api::_()->user()->getAuth()->getStorage()->clear();

			$confirmSession = new Zend_Session_Namespace('Signup_Confirm');
			$confirmSession->approved = $viewer->approved;
			$confirmSession->verified = $viewer->verified;
			$confirmSession->enabled = $viewer->enabled;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => 'require_confirmation'));
		}

		// Handle normal signup
		else {
			Engine_Api::_()->user()->getAuth()->getStorage()->write($viewer->getIdentity());
			Engine_Hooks_Dispatcher::getInstance()
				->callEvent('onUserEnable', array('user' => $viewer, 'shouldSendEmail' => false));
		}

		// Set lastlogin_date here to prevent issues with payment
		if ($viewer->getIdentity()) {
			$viewer->lastlogin_date = date("Y-m-d H:i:s");
			if ('cli' !== PHP_SAPI) {
				$ipObj = new Engine_IP();
				$viewer->lastlogin_ip = $ipObj->toBinary();
			}
			$viewer->save();
		}
		return true;
	}
	
  public function getProfileTypeField() {
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      return $topStructure[0]->getChild();
    }
    return null;
  }
	
	public function accountSubmit() {
	
		$session = new Zend_Session_Namespace("User_Form_Signup_Account");
		
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
    if ($emailadmin) {
      // the signup notification is emailed to the first SuperAdmin by default
      $users_table = Engine_Api::_()->getDbTable('users', 'user');
      $users_select = $users_table->select()
        ->where('level_id = ?', 1)
        ->where('enabled >= ?', 1);
      $super_admin = $users_table->fetchRow($users_select);
    }
    $data = $session->data;
    
    // Add email and code to invite session if available
    $inviteSession = new Zend_Session_Namespace('invite');
    if (isset($data['email'])) {
      $inviteSession->signup_email = $data['email'];
    }
    if (isset($data['code'])) {
      $inviteSession->signup_code = $data['code'];
    }

    if (!empty($data['language'])) {
      $data['locale'] = $data['language'];
    }
    $data['language'] = "en";
		
    //Mobile Number
    if ((!empty($_POST['country_code']) || !empty($data['countrycode'])) && is_numeric($data['email'])) {
      $data['phone_number'] = $data['email'];
      $domain = Engine_Api::_()->getApi('otp', 'core')->getDomain($_SERVER["HTTP_HOST"]);
      $email = NULL;
      $data['email'] = $email;
      $data['country_code'] = $_POST['country_code'] ? $_POST['country_code'] : $data['countrycode'];
    }

    // Create user
    // Note: you must assign this to the registry before calling save or it
    // will not be available to the plugin in the hook
    $user = Engine_Api::_()->getDbTable('users', 'user')->createRow();
    Zend_Registry::set('user', $user);
    $user->setFromArray($data);
    $user->save();
    
    //Set Display Name
    $user->setDisplayName(array('first_name' => $user->firstname, 'last_name' => $user->lastname));
    $user->save();
		
    //Username work
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) && isset($user->email) && '' !== trim($user->email)) {
      $tmp = explode('@', $user->email);
      $username = $tmp[0] . rand();
      $user->username = $username;
      $user->save();
    }
    
    //Referral code generated
    if(empty($user->referral_code)) {
      $user->referral_code = substr(md5(rand(0, 999) . $user->email), 10, 7);
      $user->save();
    }

    Engine_Api::_()->user()->setViewer($user);
    
    // Increment signup counter
    Engine_Api::_()->getDbTable('statistics', 'core')->increment('user.creations');

    if ($user->verified && $user->enabled) {
      // Create activity for them
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'signup');
      // Set user as logged in if not have to verify email
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
    }
    
//     //Profile Type work
//     // Preload profile type field stuff
//     $profileTypeField = $this->getProfileTypeField();
//     
//     if(!empty($data['profile_type'])) {
//       $values = Engine_Api::_()->fields()->getFieldsValues($user);
//       $valueRow = $values->createRow();
//       $valueRow->field_id = 1;
//       $valueRow->item_id = $user->getIdentity();
//       $valueRow->value = $data['profile_type']; //$options[0]->option_id;
//       $valueRow->save();
//     }

//     if( $profileTypeField ) {
//       $accountSession = new Zend_Session_Namespace('User_Plugin_Signup_Account');
//       $profileTypeValue = @$accountSession->data['profile_type'];
//       if( $profileTypeValue ) {
//         $values = Engine_Api::_()->fields()->getFieldsValues($user);
//         $valueRow = $values->createRow();
//         $valueRow->field_id = $profileTypeField->field_id;
//         $valueRow->item_id = $user->getIdentity();
//         $valueRow->value = $profileTypeValue;
//         $valueRow->save();
//       }
//       else{
//         $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
//         if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
//           $profileTypeField = $topStructure[0]->getChild();
//            
//           $options = $profileTypeField->getOptions();
//           if( engine_count($options) == 1 ) {
//             $values = Engine_Api::_()->fields()->getFieldsValues($user);
//             $valueRow = $values->createRow();
//             $valueRow->field_id = $profileTypeField->field_id;
//             $valueRow->item_id = $user->getIdentity();
//             $valueRow->value = $data['profile_type']; //$options[0]->option_id;
//             $valueRow->save();
//           }
//         }
//       }
//     }
    
//     //Photo Work
//     // Remove old key
//     unset($_SESSION['TemporaryProfileImg']);
//     unset($_SESSION['TemporaryProfileImgSquare']);
// 
//     // Process
//     $dataPhoto = $session->data;
//     
//     $params = array(
//       'parent_type' => 'user',
//       'parent_id' => $user->user_id
//     );
// 
//     if( !empty($session->tmp_file_id) ) {
//       // Save
//       $storage = Engine_Api::_()->getItemTable('storage_file');
// 
//       // Update info
//       $iMain = $storage->getFile($session->tmp_file_id);
//       $iMain->setFromArray($params);
//       $iMain->save();
// 
//       $iSquare = $storage->getFile($session->tmp_file_id, 'thumb.icon');
//       $iSquare->setFromArray($params);
//       $iSquare->save();
//       
//       // Update row
//       $user->photo_id = $iMain->file_id;
//       if(isset($user->import)) {
//         $user->import = 1;
//       }
//       $user->save();
// 
//       $this->_resizeThumbnail($user);
//     }

    $mailType = null;
    $mailParams = array(
      'host' => $_SERVER['HTTP_HOST'],
      'email' => $user->email,
      'date' => time(),
      'recipient_title' => $user->getTitle(),
      'recipient_link' => $user->getHref(),
      'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
      'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
    );

    // Mail stuff
    switch ($settings->getSetting('user.signup.enabletwostep', 0)) {
      case 0:
        if ($emailadmin) {
          $mailAdminType = 'notify_admin_user_signup';
          $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
          $date = new DateTime("now", new DateTimeZone($siteTimezone));
          $mailAdminParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $super_admin->email,
            'date' => $date->format('F j, Y, g:i a'),
            'recipient_title' => $super_admin->displayname,
            'object_title' => $user->displayname,
            'object_link' => $user->getHref(),
          );
        }
        break;
      case 1:
        // send welcome email
        $mailType = 'core_welcome';
        if ($emailadmin) {
          $mailAdminType = 'notify_admin_user_signup';
          $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
          $date = new DateTime("now", new DateTimeZone($siteTimezone));
          $mailAdminParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $super_admin->email,
            'date' => $date->format('F j, Y, g:i a'),
            'recipient_title' => $super_admin->displayname,
            'object_title' => $user->getTitle(),
            'object_link' => $user->getHref(),
          );
        }
      break;
      default:
        // do nothing
        break;
    }
    
    if (!empty($mailType) && !empty($user->email) && !empty($user->approved) && !empty($user->verified) && !empty($user->enabled)) {
      Zend_Registry::set('mailParams', $mailParams);
			Zend_Registry::set('mailType', $mailType);

      // Moved from User_Plugin_Signup_Fields
//       Engine_Api::_()->getApi('mail', 'core')->sendSystem(
//         $user,
//         $mailType,
//         $mailParams
//       );
    }
    
    if (!empty($mailAdminType) && !empty($user->email)) {
      Zend_Registry::set('mailAdminParams', $mailParams);
			Zend_Registry::set('mailAdminType', $mailType);
			
      // Moved to User_Plugin_Signup_Fields
      $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
      $super_adminEmail = $settings->getSetting('user.signup.adminemailaddress', null);
      if (empty($emailadmin)) {
        $super_adminEmail = $mailAdminParams['email'];
      } elseif(!empty($emailadmin) && empty($super_adminEmail)) {
				$super_adminEmail = $mailAdminParams['email'];
      }
      $mailAdminParams['email'] = $super_adminEmail;
      //Engine_Api::_()->getApi('mail', 'core')->sendSystem($super_adminEmail, $mailAdminType, $mailAdminParams);
    }
    
    unset($_SESSION['isValidCode']);

    
		// Attempt to connect facebook
		if (!empty($_SESSION['facebook_signup'])) {
			try {
				$facebookTable = Engine_Api::_()->getDbTable('facebook', 'user');
				$settings = Engine_Api::_()->getDbTable('settings', 'core');
				if ($settings->core_facebook_enable) {
					$facebookTable->insert(
						array(
							'user_id' => $user->getIdentity(),
							'facebook_uid' => $_SESSION["facebook_uid"],
							'access_token' => $_SESSION["fbToken"],
							//'code' => $code,
							'expires' => 0, // @todo make sure this is correct
						)
					);
				}
			} catch (Exception $e) {
				// Silence
				if ('development' == APPLICATION_ENV) {
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
				}
			}
			unset($_SESSION['facebook_signup']);
		}

		// Attempt to connect twitter
		if (!empty($_SESSION['twitter_signup'])) {
			try {
				$twitterTable = Engine_Api::_()->getDbTable('twitter', 'user');

				$settings = Engine_Api::_()->getDbTable('settings', 'core');
				$twitterTable->insert(
					array(
						'user_id' => $user->getIdentity(),
						'twitter_uid' => $_SESSION['twitter_uid'],
						'twitter_token' => ($_SESSION['twitter_token'] ?? ''),
						'twitter_secret' => $_SESSION['twitter_secret'],
					)
				);

			} catch (Exception $e) {
				// Silence?
				if ('development' == APPLICATION_ENV) {
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
				}
			}
			unset($_SESSION['twitter_signup']);
		}
		
    // Attempt to connect google
    if (!empty($_SESSION['google_signup'])) {
      try {
        $googleTable = Engine_Api::_()->getDbTable('google', 'user');
        if ($googleTable->isConnected()) {
          $googleTable->insert(array(
            'user_id' => $user->getIdentity(),
            'google_uid' => $_SESSION['google_uid'],
            'access_token' => $_SESSION['access_token'],
            'code' => $_SESSION['refresh_token'],
            'expires' => 0,
          ));
        }
      } catch (Exception $e) {
				// Silence
				if ('development' == APPLICATION_ENV) {
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
				}
			}
      unset($_SESSION['google_signup']);
    }

    // Attempt to connect linkedin
    if (!empty($_SESSION['linkedin_signup'])) {
      try {
        $linkedinTable = Engine_Api::_()->getDbTable('linkedin', 'user');
        if ($linkedinTable->isConnected()) {
          $linkedinTable->insert(array(
            'user_id' => $user->getIdentity(),
            'linkedin_uid' => $_SESSION['linkedin_uid'],
            'access_token' => $_SESSION['linkedin_token'],
            'code' => $_SESSION['linkedin_secret'],
            'expires' => 0,
          ));
        }
      } catch (Exception $e) {
        // Silence
				if ('development' == APPLICATION_ENV) {
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
				}
      }
      unset($_SESSION['linkedin_signup']);
    }
    
		return true;
	}
	
  public function fieldSubmit() {
  
    $session =   new Zend_Session_Namespace("Sesapi_Form_Signup_Fields");
    
    // In this case, the step was placed before the account step.
    // Register a hook to this method for onUserCreateAfter
   
    $user = Zend_Registry::get('user');
    
     // Preload profile type field stuff
    $profileTypeField = $this->getProfileTypeField();
    if( $profileTypeField ) {
      $accountSession = new Zend_Session_Namespace('User_Form_Signup_Account');
      $profileTypeValue = @$accountSession->data['profile_type'];
      if( $profileTypeValue ) {
        $values = Engine_Api::_()->fields()->getFieldsValues($user);
        $valueRow = $values->createRow();
        $valueRow->field_id = $profileTypeField->field_id;
        $valueRow->item_id = $user->getIdentity();
        $valueRow->value = $profileTypeValue;
        $valueRow->save();
      } else {
        $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
        if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
          $profileTypeField = $topStructure[0]->getChild();
          $options = $profileTypeField->getOptions();
          if( engine_count($options) == 1 ) {
            $values = Engine_Api::_()->fields()->getFieldsValues($user);
            $valueRow = $values->createRow();
            $valueRow->field_id = $profileTypeField->field_id;
            $valueRow->item_id = $user->getIdentity();
            $valueRow->value = $options[0]->option_id;
            $valueRow->save();
          }
        }
      }
    }
    
    $formArgs = $this->getTopLevelValues();
    $form = new Sesapi_Form_Signup_Fields($formArgs);
    
    // Save them values
    $form = $form->setItem($user);
     
    $form->setProcessedValues($session->data);
    $form->saveValues();

    $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
    $user->setDisplayName($aliasValues);
    $user->save();
    
    //Save values in users table
    if( is_array($aliasValues) )
    {
      // Has only first
      if( !empty($aliasValues['first_name']) )
      {
        $user->firstname = $aliasValues['first_name'];
        $user->save();
      }
      // Has only last
      if( !empty($aliasValues['last_name']) )
      {
        $user->lastname = $aliasValues['last_name'];
        $user->save();
      } 
      //has only birthdate
      if( !empty($aliasValues['gender']) )
      {
        $gender = Engine_Api::_()->user()->getOptionIdValue(array('option_id' => $aliasValues['gender']));
        $user->gender = strtolower($gender);
        $user->save();
      }
      //has only birthdate
      if( !empty($aliasValues['birthdate']) )
      {
        $user->dob = $aliasValues['birthdate'];
        $user->save();
      }
    }
    
    // Send Welcome E-mail
    if( Zend_Registry::isRegistered('mailType') ) {
      $mailType   =  Zend_Registry::get('mailType')  ;
      $mailParams = Zend_Registry::get('mailParams')  ;
      Engine_Api::_()->getApi('mail', 'core')->sendSystem(
        $user,
        $mailType,
        $mailParams
      );
    }
    
    // Send Notify Admin E-mail
    if( isset($this->_registry->mailAdminType) && $this->_registry->mailAdminType ) {
      $mailAdminType   = $this->_registry->mailAdminType;
      $mailAdminParams = $this->_registry->mailAdminParams;
      
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
      $super_adminEmail = $settings->getSetting('user.signup.adminemailaddress', null);
      if (empty($emailadmin)) {
        $super_adminEmail = $mailAdminParams['email'];
      } elseif(!empty($emailadmin) && empty($super_adminEmail)) {
				$super_adminEmail = $mailAdminParams['email'];
      }
      $mailAdminParams['email'] = $super_adminEmail;
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($super_adminEmail, $mailAdminType, $mailAdminParams);
    }
    
    return true;
  }
  
  public function photoSubmit() {
  
    $user = Zend_Registry::get('user');;

    // Remove old key
    unset($_SESSION['TemporaryProfileImg']);
    unset($_SESSION['TemporaryProfileImgSquare']);
    $session = new Zend_Session_Namespace("Sesapi_Form_Signup_Photo");
    // Process
    $data = $session->data;
    
    $params = array(
      'parent_type' => 'user',
      'parent_id' => $user->user_id
    );
    
    if(!empty($session->tmp_file_id) ) {
      // Save
      $storage = Engine_Api::_()->getItemTable('storage_file');

      // Update info
      $iMain = $storage->getFile($session->tmp_file_id);
      $iMain->setFromArray($params);
      $iMain->save();

      $iSquare = $storage->getFile($session->tmp_file_id, 'thumb.icon');
      $iSquare->setFromArray($params);
      $iSquare->save();
      
      // Update row
      $user->photo_id = $iMain->file_id;
      $user->save();      
    }
    return true;  
  }
  
  public function interestsSubmit() {
  
    $user = Engine_Api::_()->user()->getViewer();
    $interestSession = new Zend_Session_Namespace('Sesinterest_Verification');
    $values['user_id'] = $user->getIdentity();
    $interestTable = Engine_Api::_()->getDbTable('interests', 'sesinterest');
    $table = Engine_Api::_()->getDbTable('userinterests', 'sesinterest');
    if(!empty($interestSession->interests)) {
      foreach($interestSession->interests as $interest) {
        $getColumnName = $interestTable->getColumnName(array('column_name' => 'interest_name', 'interest_id' => $interest));
        $values['interest_name'] = $getColumnName;
        $values['interest_id'] = $interest;

        $row = $table->createRow();
        $row->setFromArray($values);
        $row->save();
      }
    }
    
    if(!empty($interestSession->custom_interests)) {
      $custom_interests = explode(',',$interestSession->custom_interests);
      foreach($custom_interests as $custom_interest) {
        if(empty($custom_interest)) continue;
        $interest_id = $interestTable->getColumnName(array('column_name' => 'interest_id', 'interest_name' => $custom_interest));
        if(empty($interest_id)) {
          $values['interest_name'] = $custom_interest;
          $values['approved'] = '0';
          $values['created_by'] = '0';
          $values['user_id'] = $user->getIdentity();
          $row = $interestTable->createRow();
          $row->setFromArray($values);
          $row->save();
          //Entry in Userinterest table
          $valuesUser['interest_name'] = $custom_interest;
          $valuesUser['interest_id'] = $row->getIdentity();
          $valuesUser['user_id'] = $user->getIdentity();
          $rowUser = $table->createRow();
          $rowUser->setFromArray($valuesUser);
          $rowUser->save();
        }
      }
    }
  }
  
  protected function _resizeImages($file,$session,$imageOuterUpload = false) {

    if(!$imageOuterUpload){
      $name = basename($file['image']['name']);
      $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
      // $file_path_info = explode("/", $path);
      // $fileDirectoryPath = "";
      // if (!is_dir($path)) {
      //    foreach ($file_path_info as $key => $value) {
      //        if (!empty($fileDirectoryPath)) {
      //            $fileDirectoryPath .=     $value .'/';
      //        } else {
      //            $fileDirectoryPath .= $value.'/' ;
      //        }
      //        if (!is_dir($fileDirectoryPath)) {
      //            mkdir($fileDirectoryPath,0777);
      //            chmod($fileDirectoryPath, 0777);
      //        }
      //    }
      // }
      $file = $file['image']['tmp_name'];
    } else {
      $name = basename($file);
      $path = dirname($file);
    }
    
    // Resize image (main)
    $iMainPath = $path . '/m_' . $name;
    $image = Engine_Image::factory();
    $image->open($file)
        ->autoRotate()
        ->resize(720, 720)
        ->write($iMainPath)
        ->destroy();

    // Resize image (icon.square)
    $iSquarePath = $path . '/s_' . $name;
    $image = Engine_Image::factory();
    $image->open($file)
        ->autoRotate();
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 48, 48)
        ->write($iSquarePath)
        ->destroy();
    
    // Cloud compatibility, put into storage system as temporary files
    $storage = Engine_Api::_()->getItemTable('storage_file');

    // Save/load from session
		$iMain = $storage->createTemporaryFile($iMainPath);
		$iSquare = $storage->createTemporaryFile($iSquarePath);
		
		$iMain->bridge($iSquare, 'thumb.icon');
		
		$session->tmp_file_id = $iMain->file_id;

    // Save path to session?
    $_SESSION['TemporaryProfileImg'] = $iMain->map();
    $_SESSION['TemporaryProfileImgSquare'] = $iSquare->map();
    
    // Remove temp files
    @unlink($path . '/m_' . $name);
    @unlink($path . '/s_' . $name);
    return $session->tmp_file_id;
  }
  
  protected function _fetchImage($photo_url,$session) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $photo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    $data = curl_exec($ch);
    curl_close($ch);
    $tmpfile = APPLICATION_PATH_TMP . DS . md5($photo_url) . '.jpg';
    @file_put_contents( $tmpfile, $data );
    $this->_resizeImages($tmpfile,$session,true);
  }

  public function getTopLevelValues() {
  
    $formArgs = array();
    // Preload profile type field stuff
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField =  $topStructure[0]->getChild();
    } else
      $profileTypeField = null;
    
    if( $profileTypeField ) {
      $accountSession = new Zend_Session_Namespace('User_Form_Signup_Account');
      $profileTypeValue = @$accountSession->data['profile_type'];
      if( $profileTypeValue ) {
        $formArgs = array(
          'topLevelId' => $profileTypeField->field_id,
          'topLevelValue' => $profileTypeValue,
        );
      }
      else{
        $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
        if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
          $profileTypeField = $topStructure[0]->getChild();
          $options = $profileTypeField->getOptions();
          if( engine_count($options) == 1 ) {
            $formArgs = array(
              'topLevelId' => $profileTypeField->field_id,
              'topLevelValue' => $options[0]->option_id,
            );
          }
        }
      }
    }
    return $formArgs;
  }

	public function verifyAction() {
	
		$verify = $this->_getParam('verify');
		$email = $this->_getParam('email');
		$settings = Engine_Api::_()->getApi('settings', 'core');

		// No code or email
		if (!$verify || !$email) {
			$this->view->status = false;
			$this->view->error = $this->view->translate('The email or verification code was not valid.');
			return;
		}

		// Get verify user
		$userTable = Engine_Api::_()->getDbTable('users', 'user');
		$user = $userTable->fetchRow($userTable->select()->where('email = ?', $email));

		if (!$user || !$user->getIdentity()) {
			$this->view->status = false;
			$this->view->error = $this->view->translate('The email does not match an existing user.');
			return;
		}

		// If the user is already verified, just redirect
		if ($user->verified) {
			$this->view->status = true;
			return;
		}

		// Get verify row
		$verifyTable = Engine_Api::_()->getDbTable('verify', 'user');
		$verifyRow = $verifyTable->fetchRow($verifyTable->select()->where('user_id = ?', $user->getIdentity()));

		if (!$verifyRow || $verifyRow->code != $verify) {
			$this->view->status = false;
			$this->view->error = $this->view->translate('There is no verification info for that user.');
			return;
		}

		// Process
		$db = $verifyTable->getAdapter();
		$db->beginTransaction();

		try {

			$verifyRow->delete();
			$user->verified = 1;
			$user->save();

			if ($user->enabled) {
				Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserEnable', array('user' => $user, 'shouldSendEmail' => false));
			}

			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}

		$this->view->status = true;
	}
	
	public function takenAction()
	{
		$username = $this->_getParam('username');
		$email = $this->_getParam('email');

		// Sent both or neither username/email
		if ((bool) $username == (bool) $email) {
			$this->view->status = false;
			$this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param count');
			return;
		}

		// Username must be alnum
		if ($username) {
			$validator = new Zend_Validate_Alnum();
			if (!$validator->isValid($username)) {
				$this->view->status = false;
				$this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param value');
				//$this->view->errors = $validator->getErrors();
				return;
			}

			$table = Engine_Api::_()->getItemTable('user');
			$row = $table->fetchRow($table->select()->where('username = ?', $username)->limit(1));

			$this->view->status = true;
			$this->view->taken = ($row !== null);
			return;
		}

		if ($email) {
			$validator = new Zend_Validate_EmailAddress();
			$validator->getHostnameValidator()->setValidateTld(false);
			if (!$validator->isValid($email)) {
				$this->view->status = false;
				$this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid param value');
				//$this->view->errors = $validator->getErrors();
				return;
			}

			$table = Engine_Api::_()->getItemTable('user');
			$row = $table->fetchRow($table->select()->where('email = ?', $email)->limit(1));

			$this->view->status = true;
			$this->view->taken = ($row !== null);
			return;
		}
	}

	public function confirmAction()
	{
		$confirmSession = new Zend_Session_Namespace('Signup_Confirm');
		$this->view->approved = $this->_getParam('approved', $confirmSession->approved);
		$this->view->verified = $this->_getParam('verified', $confirmSession->verified);
		$this->view->enabled = $this->_getParam('verified', $confirmSession->enabled);
	}
}
