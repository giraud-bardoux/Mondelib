<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 10099 2013-10-19 14:58:40Z ivan $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Plugin_Signup_Account extends Core_Plugin_FormSequence_Abstract
{
  protected $_name = 'account';
  protected $_formClass = 'User_Form_Signup_Account';
  protected $_script = array('signup/form/account.tpl', 'user');
  protected $_adminFormClass = 'User_Form_Admin_Signup_Account';
  protected $_adminScript = array('admin-signup/account.tpl', 'user');
  public $email = null;

  public function onView()
  {
    if (!empty($_SESSION['facebook_signup']) || !empty($_SESSION['twitter_signup']) || !empty($_SESSION['google_signup']) || !empty($_SESSION['telegram_signup']) || !empty($_SESSION['linkedin_signup'])) {

      // Attempt to preload information
      if (!empty($_SESSION['facebook_signup'])) {
        try {
          $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
          $facebook = $facebookTable->getApi();
          $settings = Engine_Api::_()->getDbtable('settings', 'core');
          if ($facebook && $settings->core_facebook_enable) {
            // Get email address
            $apiInfo = $facebook->api('/me?fields=name,gender,email,locale,first_name,last_name,birthday,picture');
            $user_id  = $apiInfo['id'];

            // General
            $form = $this->getForm();

            if (($emailEl = $form->getElement('email')) && !$emailEl->getValue()) {
              $emailEl->setValue($apiInfo['email']);
              $_SESSION['isValidCode'] = $apiInfo['email'];
            }
            if (($usernameEl = $form->getElement('username')) && !$usernameEl->getValue()) {
              $usernameEl->setValue(preg_replace('/[^A-Za-z]/', '', $apiInfo['name']));
            }
            
            if (($firstnameEl = $form->getElement('firstname')) && !$firstnameEl->getValue()) {
              $firstnameEl->setValue($apiInfo['first_name']);
            }
            if (($lastnameEl = $form->getElement('lastname')) && !$lastnameEl->getValue()) {
              $lastnameEl->setValue($apiInfo['last_name']);
            }
            if (($dobEl = $form->getElement('dob')) && !$dobEl->getValue()) {
              $dobEl->setValue(date("Y-m-d",strtotime($apiInfo['birthday'])));
            }
            if (($genderEl = $form->getElement('gender')) && !$genderEl->getValue()) {
              $genderEl->setValue($apiInfo['gender']);
            }

            // Locale
            $localeObject = new Zend_Locale($apiInfo['locale']);
            if (($localeEl = $form->getElement('locale')) && !$localeEl->getValue()) {
              $localeEl->setValue($localeObject->toString());
            }
            if (($languageEl = $form->getElement('language')) && !$languageEl->getValue()) {
              if (isset($languageEl->options[$localeObject->toString()])) {
                $languageEl->setValue($localeObject->toString());
              } else if (isset($languageEl->options[$localeObject->getLanguage()])) {
                $languageEl->setValue($localeObject->getLanguage());
              }
            }
            
            // Fetch image from Facebook
            $photo_url = "https://graph.facebook.com/" 
                     . $user_id 
                     . "/picture?type=large"
                     ;
          
            $this->_fetchImage($photo_url);
          }
        } catch (Exception $e) {
          // Silence?
        }
      }
      
      // Attempt to preload information
      if (!empty($_SESSION['twitter_signup'])) {
        try {
          $this->getForm()->populate(array(
            'username' => preg_replace('/[^A-Za-z]/', '', $_SESSION['signup_fields']['username']),
            'language' => $_SESSION['signup_fields']['lang'],
          ));
          
          //photo
          if(isset($_SESSION['signup_fields']['photo']) && !empty($_SESSION['signup_fields']['photo']))
            $this->_fetchImage($_SESSION['signup_fields']['photo']);
        } catch (Exception $e) {
          // Silence?
        }
      }
      
      if (!empty($_SESSION['google_signup'])) {
        try {
          $googleTable = Engine_Api::_()->getDbtable('google', 'user');
          if ($googleTable->isConnected()) {
          
            // General
            $form = $this->getForm();
            if (($emailEl = $form->getElement('email')) && !$emailEl->getValue()) {
              $emailEl->setValue($_SESSION['signup_fields']['email']);
              $_SESSION['isValidCode'] = $_SESSION['signup_fields']['email'];
            }

            if (!empty($_SESSION['signup_fields']['first_name']) && ($firstname = $form->getElement('firstname')) && !$firstname->getValue()) {
              $firstname->setValue($_SESSION['signup_fields']['first_name']);
            }
            
            if (!empty($_SESSION['signup_fields']['last_name']) && ($lastnameEl = $form->getElement('lastname')) && !$lastnameEl->getValue()) {
              $lastnameEl->setValue($_SESSION['signup_fields']['last_name']);
            }

            //photo
            if(isset($_SESSION['signup_fields']['photo']) && !empty($_SESSION['signup_fields']['photo']))
            $this->_fetchImage($_SESSION['signup_fields']['photo']);
          }
        } catch (Exception $e) {
            // Silence?
        }
      }

      if (!empty($_SESSION['telegram_signup'])) {
        try {
            
                // General
                $form = $this->getForm();
                if (($emailEl = $form->getElement('email')) && !$emailEl->getValue()) {
                  $emailEl->setValue($_SESSION['signup_fields']['email']);
                  $_SESSION['isValidCode'] = $_SESSION['signup_fields']['email'];
                }

                if (!empty($_SESSION['signup_fields']['first_name']) && ($firstname = $form->getElement('firstname')) && !$firstname->getValue()) {
                  $firstname->setValue($_SESSION['signup_fields']['first_name']);
                }
                if (!empty($_SESSION['signup_fields']['username']) && ($username = $form->getElement('username')) && !$username->getValue()) {
                  $username->setValue($_SESSION['signup_fields']['username']);
                }
                
                if (!empty($_SESSION['signup_fields']['last_name']) && ($lastnameEl = $form->getElement('lastname')) && !$lastnameEl->getValue()) {
                  $lastnameEl->setValue($_SESSION['signup_fields']['last_name']);
                }
    
                //photo
                if(isset($_SESSION['signup_fields']['photo']) && !empty($_SESSION['signup_fields']['photo']))
                $this->_fetchImage($_SESSION['signup_fields']['photo']);
            
        } catch (Exception $e) {
            // Silence?
        }
      }
      if (!empty($_SESSION['linkedin_signup'])) {
        try {
          $linkedinTable = Engine_Api::_()->getDbtable('linkedin', 'user');
          $linkedin = $linkedinTable->getApi();
          if ($linkedin && $linkedinTable->isConnected()) {
            // General
            $form = $this->getForm();
            if (($emailEl = $form->getElement('email')) && !$emailEl->getValue()) {
              $emailEl->setValue($_SESSION['signup_fields']['email']);
              $_SESSION['isValidCode'] = $_SESSION['signup_fields']['email'];
            }
            
            if (!empty($_SESSION['signup_fields']['first_name']) && ($firstname = $form->getElement('firstname')) && !$firstname->getValue()) {
              $firstname->setValue($_SESSION['signup_fields']['first_name']);
            }
            
            if (!empty($_SESSION['signup_fields']['last_name']) && ($lastnameEl = $form->getElement('lastname')) && !$lastnameEl->getValue()) {
              $lastnameEl->setValue($_SESSION['signup_fields']['last_name']);
            }
            
            if (($dobEl = $form->getElement('dob')) && !$dobEl->getValue() && !empty($_SESSION['signup_fields']['birthday'])) {
              $dobEl->setValue(date("Y-m-d", strtotime($_SESSION['signup_fields']['birthday'])));
            }
            
            //photo
            if(isset($_SESSION['signup_fields']['photo']) && !empty($_SESSION['signup_fields']['photo']))
            $this->_fetchImage($_SESSION['signup_fields']['photo']);
          }
        } catch (Exception $e) {
            // Silence?
        }
      }
    }

    if (isset($_SESSION['Payment_Plugin_Signup_Subscription'])) {
      try {
        $packageId = $_SESSION['Payment_Plugin_Signup_Subscription']['data']['package_id'];
        $package = Engine_Api::_()->getItem('payment_package', $packageId);
        if (empty($package)) {
          return;
        }

        $profileTypeIds = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
          ->getMappedProfileTypeIds($package->level_id);
        if (empty($profileTypeIds)) {
          return;
        }

        $form = $this->getForm();
        if (engine_count($profileTypeIds) == 1) {
          $form->removeElement('profile_type');
          // Hidden Profile Types
          $form->addElement('Hidden', 'profile_type', array(
            'value' => $profileTypeIds[0]['profile_type_id']
          ));
          return;
        }

        $profileTypes = Engine_Api::_()->getDbtable('options', 'authorization')->getAllProfileTypes();
        $profileTypeOptions = array('' => '');
        foreach ($profileTypes as $profileType) {
          $showOption  = false;
          foreach($profileTypeIds as $profileTypeId) {
            if ($profileType->option_id === $profileTypeId['profile_type_id']) {
              $showOption = true;
            }
          }
          if ($showOption) {
            $profileTypeOptions[$profileType->option_id] = $profileType->label;
          }
        }
        $form->getElement('profile_type')->setMultiOptions($profileTypeOptions);
      } catch (Exception $ex) {
          // Silence?
      }
    }
  }
  
  public function onSubmit(Zend_Controller_Request_Abstract $request) {
  
    // Form was not valid
    if(!$this->getForm()->isValid($request->getPost())) {
      
      $this->getSession()->active = true;
      $this->onSubmitNotIsValid();
      // $validateFields = Engine_Api::_()->core()->validateFormFields($this->getForm());
      // if(is_countable($validateFields) && engine_count($validateFields)){
      //   echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
      // }
      return false;
    } else if(!isset($_POST['submit_signup'])) {
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
      $translate = Zend_Registry::get('Zend_Translate');
      
      if($settings->getSetting('user.signup.enabletwostep', 0) == 1 || !empty($otpsms_signup_phonenumber)) {
      
        $valid = false;
        if(empty($_SESSION['isValidCode'])) {
          $valid = true;
        } else if(!empty($_SESSION['isValidCode']) && $_SESSION['isValidCode'] != $_POST['email']) {
          $valid = true;
        }
        
        if($valid) {
          if(!empty($otpsms_signup_phonenumber)) {
            $label = $translate->translate('Phone Number or email address');
            if(is_numeric($_POST['email']))
              $description = $translate->translate('Please verify your phone number.');
            else if($settings->getSetting('user.signup.enabletwostep', 0)) 
              $description = $translate->translate('Please verify your email address.');
            else if(empty($settings->getSetting('user.signup.enabletwostep', 0)))  {
              //echo json_encode(array('status' => true));die;
            }
          } else {
            $label = $translate->translate('Email Address');
            $description = $translate->translate('Please verify your email address.');
          }
          if(!empty($description)){
            $errors[] = array('isRequired' => true, 'label' => $label, 'errorMessage' => $description);
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
          }
        } else {
          //echo json_encode(array('status' => true));die;
        }
      } else {
        //echo json_encode(array('status' => true));die;
      }
    }
    if(!empty($_SESSION['facebook_signup'])) {
      $otpSession = new Zend_Session_Namespace('User_Plugin_Signup_Otp');
      $otpSession->active = false;
    }
    
    parent::onSubmit($request);
  }

  public function onProcess()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
    if ($emailadmin) {
      // the signup notification is emailed to the first SuperAdmin by default
      $users_table = Engine_Api::_()->getDbtable('users', 'user');
      $users_select = $users_table->select()
        ->where('level_id = ?', 1)
        ->where('enabled >= ?', 1);
      $super_admin = $users_table->fetchRow($users_select);
    }
    $data = $this->getSession()->data;
    
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

    //Mobile Number
    if ((!empty($_POST['country_code']) || !empty($data['countrycode'])) && is_numeric($data['email'])) {
      
      $data['phone_number'] = $data['email'];
      $domain = Engine_Api::_()->getApi('otp', 'core')->getDomain($_SERVER["HTTP_HOST"]);
      $email = NULL;
      $data['email'] = $email;
      if($_POST['country_code']) {
        $country_code = explode('_', $_POST['country_code']);
        $country_code = $country_code[0];
      } else if($data['countrycode']) {
        $country_code = explode('_', $data['countrycode']);
        $country_code = $country_code[0];
      }
      $data['country_code'] = $country_code; //$_POST['country_code'] ? $_POST['country_code'] : $data['countrycode'];
    }

    // Create user
    // Note: you must assign this to the registry before calling save or it
    // will not be available to the plugin in the hook
    $this->_registry->user = $user = Engine_Api::_()->getDbtable('users', 'user')->createRow();
    $user->setFromArray($data);
    $user->save();
    
    //Set Display Name
    $user->setDisplayName(array('first_name' => $user->firstname, 'last_name' => $user->lastname));
    $user->save();
    
    //Location Work
    Engine_Api::_()->getApi('location', 'core')->saveLocation($_SESSION['User_Plugin_Signup_Account']['data'], $user);
    
    //Username work
    // if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) && isset($user->email) && '' !== trim($user->email)) {
    //   $tmp = explode('@', $user->email);
    //   $username = $tmp[0] . rand();
    //   $user->username = $username;
    //   $user->save();
    // }
    
    //Referral code generated
    if(empty($user->referral_code)) {
      $user->referral_code = substr(md5(rand(0, 999) . $user->email), 10, 7);
      $user->save();
    }

    Engine_Api::_()->user()->setViewer($user);

    // Increment signup counter
    Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.creations');

    if ($user->verified && $user->enabled) {
      // Create activity for them
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'signup');
      // Set user as logged in if not have to verify email
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
    }
    
    // //Profile Type work
    // // Preload profile type field stuff
    // $profileTypeField = $this->getProfileTypeField();
    // if( $profileTypeField ) {
    //   $accountSession = new Zend_Session_Namespace('User_Plugin_Signup_Account');
    //   $profileTypeValue = @$accountSession->data['profile_type'];
    //   if( $profileTypeValue ) {
    //     $values = Engine_Api::_()->fields()->getFieldsValues($user);
    //     $valueRow = $values->createRow();
    //     $valueRow->field_id = $profileTypeField->field_id;
    //     $valueRow->item_id = $user->getIdentity();
    //     $valueRow->value = $profileTypeValue;
    //     $valueRow->save();
    //   }
    //   else{
    //     $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    //     if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
    //       $profileTypeField = $topStructure[0]->getChild();
    //       $options = $profileTypeField->getOptions();
    //       if( engine_count($options) == 1 ) {
    //         $values = Engine_Api::_()->fields()->getFieldsValues($user);
    //         $valueRow = $values->createRow();
    //         $valueRow->field_id = $profileTypeField->field_id;
    //         $valueRow->item_id = $user->getIdentity();
    //         $valueRow->value = $options[0]->option_id;
    //         $valueRow->save();
    //       }
    //     }
    //   }
    // }
    
    // //Photo Work
    // // Remove old key
    // unset($_SESSION['TemporaryProfileImg']);
    // unset($_SESSION['TemporaryProfileImgSquare']);

    // // Process
    // $dataPhoto = $this->getSession()->data;
    
    // $params = array(
    //   'parent_type' => 'user',
    //   'parent_id' => $user->user_id
    // );

    // if( !empty($this->getSession()->tmp_file_id) ) {
    //   // Save
    //   $storage = Engine_Api::_()->getItemTable('storage_file');

    //   // Update info
    //   $iMain = $storage->getFile($this->getSession()->tmp_file_id);
    //   $iMain->setFromArray($params);
    //   $iMain->save();

    //   $iSquare = $storage->getFile($this->getSession()->tmp_file_id, 'thumb.icon');
    //   $iSquare->setFromArray($params);
    //   $iSquare->save();
      
    //   // Update row
    //   $user->photo_id = $iMain->file_id;
    //   if(isset($user->import)) {
    //     $user->import = 1;
    //   }
    //   $user->save();

    //   $this->_resizeThumbnail($user);
    // }
    

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

//       case 2:
//       case 3:
//         if(empty($_SESSION['facebook_signup'])) {
//           // verify email before enabling account
//           $verify_table = Engine_Api::_()->getDbtable('verify', 'user');
//           $verify_row = $verify_table->createRow();
//           $verify_row->user_id = $user->getIdentity();
//           $verify_row->code = md5($user->email
//             . $user->creation_date
//             . $settings->getSetting('core.secret', 'staticSalt')
//             . (string) rand(1000000, 9999999));
//           $verify_row->date = $user->creation_date;
//           $verify_row->save();
// 
//           $mailType = 'core_verification';
// 
//           $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
//             'action' => 'verify',
//             'token' => Engine_Api::_()->user()->getVerifyToken($user->getIdentity()),
//             'verify' => $verify_row->code
//             ), 'user_signup', true);
// 
//           if ($emailadmin) {
//             $mailAdminType = 'notify_admin_user_signup';
// 
//             $mailAdminParams = array(
//               'host' => $_SERVER['HTTP_HOST'],
//               'email' => $super_admin->email,
//               'date' => date("F j, Y, g:i a"),
//               'recipient_title' => $super_admin->displayname,
//               'object_title' => $user->getTitle(),
//               'object_link' => $user->getHref(),
//             );
//           }
//         }
//         break;

      default:
        // do nothing
        break;
    }

    if (!empty($mailType)) {
      $this->_registry->mailParams = $mailParams;
      $this->_registry->mailType = $mailType;
      // Moved from User_Plugin_Signup_Fields
      // Engine_Api::_()->getApi('mail', 'core')->sendSystem(
      //   $user,
      //   $mailType,
      //   $mailParams
      // );
    }

    if (!empty($mailAdminType)) {
      $this->_registry->mailAdminParams = $mailAdminParams;
      $this->_registry->mailAdminType = $mailAdminType;
      // // Moved to User_Plugin_Signup_Fields
      // $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
      // $super_adminEmail = $settings->getSetting('user.signup.adminemailaddress', null);
      // if (empty($emailadmin)) {
      //   $super_adminEmail = $mailAdminParams['email'];
      // } elseif(!empty($emailadmin) && empty($super_adminEmail)) {
			// 	$super_adminEmail = $mailAdminParams['email'];
      // }
      // $mailAdminParams['email'] = $super_adminEmail;
      // Engine_Api::_()->getApi('mail', 'core')->sendSystem($super_adminEmail, $mailAdminType, $mailAdminParams);
    }
    
    unset($_SESSION['isValidCode']);

    // Attempt to connect facebook
    if (!empty($_SESSION['facebook_signup'])) {
      try {
        $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
        $facebook = $facebookTable->getApi();
        if ($facebook && $settings->core_facebook_enable) {
          $facebookTable->insert(array(
            'user_id' => $user->getIdentity(),
            'facebook_uid' => $facebook->getUser(),
            'access_token' => $facebook->getAccessToken(),
            //'code' => $code,
            'expires' => 0, // @todo make sure this is correct
          ));
        }
      } catch (Exception $e) {
        // Silence
        if ('development' == APPLICATION_ENV) {
          echo $e;
        }
      }
      unset($_SESSION['facebook_signup']);
    }

    // Attempt to connect twitter
    if (!empty($_SESSION['twitter_signup'])) {
      try {
        $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
        $twitterTable->insert(array(
          'user_id' => $user->getIdentity(),
          'twitter_uid' => $_SESSION['twitter_uid'],
          'twitter_token' => $_SESSION['twitter_token'],
          'twitter_secret' => $_SESSION['twitter_secret'],
        ));
      } catch (Exception $e) {
        // Silence?
        if ('development' == APPLICATION_ENV) {
            echo $e;
        }
      }
      unset($_SESSION['twitter_signup']);
    }
    
    // Attempt to connect google
    if (!empty($_SESSION['google_signup'])) {
      try {
        $googleTable = Engine_Api::_()->getDbtable('google', 'user');
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
            echo $e;
        }
      }
      unset($_SESSION['google_signup']);
    }

    // Attempt to connect telegram
    if (!empty($_SESSION['telegram_signup'])) {
      try {
        $telegramTable = Engine_Api::_()->getDbtable('telegram', 'user');
        
          $telegramTable->insert(array(
            'user_id' => $user->getIdentity(),
            'telegram_uid' => $_SESSION['telegram_uid'],
            'access_token' => $_SESSION['telegram_token'].'_'.$_SESSION['telegram_uid']
          ));
        
      } catch (Exception $e) {
        // Silence
        if ('development' == APPLICATION_ENV) {
          echo $e;
        }
      }
      unset($_SESSION['telegram_signup']);
      }
  
    
    // Attempt to connect linkedin
    if (!empty($_SESSION['linkedin_signup'])) {
      try {
        $linkedinTable = Engine_Api::_()->getDbtable('linkedin', 'user');
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
            echo $e;
        }
      }
      unset($_SESSION['linkedin_signup']);
    }

  }

  // public function getProfileTypeField() {
  //   $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
  //   if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
  //     return $topStructure[0]->getChild();
  //   }
  //   return null;
  // }
  
  protected function _fetchImage($photo_url)
  {
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $photo_url);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
     curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
     $data = curl_exec($ch);
     curl_close($ch);
     
     $tmpfile = APPLICATION_PATH_TMP . DS . md5($photo_url) . '.jpg';
     @file_put_contents( $tmpfile, $data );
     $this->_resizeImages($tmpfile);
  }
  
  protected function _resizeImages($file)
  {
    $name = basename($file);
    $path = dirname($file);

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
    if( empty($this->getSession()->tmp_file_id) ) {
      // Save
      $iMain = $storage->createTemporaryFile($iMainPath);
      $iSquare = $storage->createTemporaryFile($iSquarePath);

      $iMain->bridge($iSquare, 'thumb.icon');

      $this->getSession()->tmp_file_id = $iMain->file_id;
    } else {
      // Overwrite
      $iMain = $storage->getFile($this->getSession()->tmp_file_id);
      $iMain->store($iMainPath);

      $iSquare = $storage->getFile($this->getSession()->tmp_file_id, 'thumb.icon');
      $iSquare->store($iSquarePath);
    }

    // Save path to session?
    $_SESSION['TemporaryProfileImg'] = $iMain->map();
    $_SESSION['TemporaryProfileImgSquare'] = $iSquare->map();
    
    // Remove temp files
    @unlink($path . '/p_' . $name);
    @unlink($path . '/m_' . $name);
    @unlink($path . '/s_' . $name);
  }

  protected function _resizeThumbnail($user)
  {
    $storage = Engine_Api::_()->storage();

    $iProfile = $storage->get($user->photo_id);
    $iSquare = $storage->get($user->photo_id, 'thumb.icon');

    // Read into tmp file
    $pName = $iProfile->getStorageService()->temporary($iProfile);
    $iName = dirname($pName) . '/nis_' . basename($pName);

    if( !empty($this->getSession()->coordinates) ) {
      list($x, $y, $w, $h) = explode(':', $this->getSession()->coordinates);

      $image = Engine_Image::factory();
      $image->open($pName)
          ->autoRotate()
          ->resample((int) $x, (int) $y, (int) $w, (int) $h, 48, 48)
          ->write($iName)
          ->destroy();
      
			$iSquare->store($iName);
			@unlink($iName);
    } else {
      $image = Engine_Image::factory();
      $image->open($pName);
      $size = min($image->height, $image->width);
      $x = ($image->width - $size) / 2;
      $y = ($image->height - $size) / 2;
      $w = $h = $size;
    }
    $image = Engine_Image::factory();
    $image->open($pName)
        ->autoRotate()
        ->resample((int) $x, (int) $y, (int) $w, (int) $h, 400, 400)
        ->write($pName)
        ->destroy();

    $iProfile->store($pName);
    @unlink($pName);
  }
  
  public function onAdminProcess($form)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $values = $form->getValues();
    if($values['enablesigupfields'])
        $values['enablesigupfields'] = json_encode($values['enablesigupfields']);
    $settings->user_signup = $values;
    if ($values['inviteonly'] == 1) {
      $step_table = Engine_Api::_()->getDbtable('signup', 'user');
      $step_row = $step_table->fetchRow($step_table->select()->where('class = ?', 'User_Plugin_Signup_Invite'));
      $step_row->enable = 0;
    }

    $form->addNotice('Your changes have been saved.');
  }
}
