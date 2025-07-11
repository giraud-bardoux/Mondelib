<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AuthController.php 10149 2014-03-26 19:59:07Z lucas $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
 
require(realpath(dirname(__FILE__) . '/..') . DIRECTORY_SEPARATOR . 'Api' . DIRECTORY_SEPARATOR . 'Twitter'.DIRECTORY_SEPARATOR.'autoload.php');
use TwitterSE\TwitterOAuth\TwitterOAuth;

class User_AuthController extends Core_Controller_Action_Standard
{
    function timeDiff($seconds){
        // extract hours
        $hours = floor($seconds / (60 * 60));
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
        // return the final array
        $string = "";
        if($hours > 0)
            $string .= $hours.($hours != 1 ? " hours " : " hour ");
        if($minutes > 0)
            $string .= $minutes.($minutes != 1 ? " minutes " : " minute ");
        if($seconds > 0)
            $string .= $seconds.($seconds != 1 ? " seconds " : " second ");
        return trim($string," ");
    }
  
    public function loginAction(){
			// START OTP CODE VERIFICATION WORK
		  if($this->_getParam('code','') && !empty($_POST['code'])) {
				$email = $this->_getParam('email','');
				$country_code = $this->_getParam('country_code','');
				$code = $this->_getParam('code','');
				$user_id = $this->_getParam('user_id','');
				$test_user_id = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.user.id', 0);
				if(!empty($test_user_id) && $test_user_id == $user_id) {
					$otpsms_test_code = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.code', '');
					if($otpsms_test_code && $otpsms_test_code == $code) {
					$otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
					$otpverification->code = $otpsms_test_code;
					} else {
					echo json_encode(array('error'=>1,'error_message'=>array(array('errorMessage'=>$this->view->translate("The OTP you entered is invalid. Please enter the correct OTP."))  )));die;
					}
				} else {
					//fetch from table
					$codes = Engine_Api::_()->getDbTable('codes','user');
					$select = $codes->select()->where('email =?',$email)->where('code =?',$code)->where('type =?','login');
					$codeData = $codes->fetchRow($select);
					if( !$codeData ) {
					echo json_encode(array('error'=>1,'error_message'=>array(array('errorMessage'=>$this->view->translate("The OTP you entered is invalid. Please enter the correct OTP."))  )));die;
					}

					$otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
					$otpverification->code = $code;
					$expire = 300;
					$time = time() - $expire;
					if( strtotime($codeData->modified_date) < $time ) {
					echo json_encode(array('error'=>1,'error_message'=>array(array('errorMessage'=>$this->view->translate("The OTP code you entered has expired. Please click on'RESEND' button to get new OTP code.")))));die;
					}
				}
				
			}
			//END OTP CODE VERIFICATION WORK
			$settings = Engine_Api::_()->getApi('settings', 'core');
			$enableloginlogs = $settings->getSetting('core.general.enableloginlogs', 0);
			$email = '';
			$otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
	
			if(!empty($otpverification) && !empty($otpverification->email)){ 
				$email = $otpverification->email;
				$password = $otpverification->password;
				$return_url = isset($_SESSION['return_url']) ? $_SESSION['return_url'] : '';
				//$remember = $otpverification->remember;
				$code = $otpverification->code;
				
				$user_id = $otpverification->user_id;
				if(!$user_id){
				$user_id = $_SESSION["otpsms_loggedin_user_id"];
				}
				
				//$otpverification->unsetAll();
				if(!$code)
				$this->_helper->redirector->gotoRoute(array(), 'default', true);
				
				$test_user_id = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.user.id', 0);
				if(!empty($test_user_id) && $test_user_id == $user_id) {
				$otpsms_test_code = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.code', '');
				if($otpsms_test_code && $otpsms_test_code != $code) {
					$this->_helper->redirector->gotoRoute(array(), 'default', true);
				}
				} else {
				$codes = Engine_Api::_()->getDbTable('codes','user');
				$select = $codes->select()->where('email =?',$email)->where('code =?',$code)->where('type =?','login');
				$codeData = $codes->fetchRow($select);   
				if(!$codeData) {
					$this->_helper->redirector->gotoRoute(array(), 'default', true);
				}
				}
				$user = Engine_Api::_()->getItem('user',$user_id);
				$email = $user->email;
			}

			// Already logged in
			if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
				$this->view->status = false;
				$this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already signed in.');
				if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
						$this->_helper->redirector->gotoRoute(array(), 'default', true);
				}
				return;
			}

			// Make form
			$this->view->form = $form = new User_Form_Login();
			if(empty($otpverification->email)) {
				$form->setAction($this->view->url(array('return_url' => null), 'user_login'));
				$form->populate(array(
					//'return_url' => $this->_getParam('return_url'),
					'return_url' => !empty($_SESSION['return_url']) ? $_SESSION['return_url'] : '',
				));

				// Render
				$disableContent = $this->_getParam('disableContent', 0);
				if( !$disableContent ) {
						$this->_helper->content
								->setEnabled()
						;
				}

				// Not a post
				if( !$this->getRequest()->isPost() ) {
						$this->view->status = false;
						$this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
						return;
				}

				// Form not valid
				if( !$form->isValid($this->getRequest()->getPost()) ) {
						$this->view->status = false;
						$this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
						return;
				}

				// Check login creds
				extract($form->getValues()); // $email, $password, $remember
				
				$user_table = Engine_Api::_()->getDbtable('users', 'user');
				
				//login with username
				$emailField = 'email';
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1)) {
					if (strpos($email, '@') == false) { 
						$emailField = 'username';
					}
				}
				
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0) && is_numeric($email)) {
					$emailField = 'phone_number';
				} 

				$user_select = $user_table->select()
						->where("`$emailField` = ?", $email);          // If post exists
						
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0) && is_numeric($email) && !empty($_POST['country_code'])) {
		$country_code = explode('_', $_POST['country_code']);
					$user_select->where('country_code =?', $country_code[0]);
				}
				$user = $user_table->fetchRow($user_select);
			}
        
			// Get ip address
			$db = Engine_Db_Table::getDefaultAdapter();
			$ipObj = new Engine_IP();
			$ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

			// Check if user exists
			if( empty($user) ) {
					$this->view->status = false;
					if(is_numeric($email)) {
						$this->view->error = $error = Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email or phone number and password, and try again.');
					} else {
						$this->view->error = $error = Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email and password, and try again.');
					}
					$form->addError(Zend_Registry::get('Zend_Translate')->_($error));
					
					if(!empty($enableloginlogs)) {
						// Register login
						Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
								'email' => $email,
								'ip' => $ipExpr,
								'timestamp' => new Zend_Db_Expr('NOW()'),
								'state' => 'no-member',
						)); 
					}
					return;
			}

			$lockAccount = ($settings
					->getSetting('core.spam.lockaccount', 0));
			$lockAttempts = ($settings
					->getSetting('core.spam.lockattempts', 3));
			$lockDuration = ($settings
					->getSetting('core.spam.lockduration', 120));

			if($lockAccount && $user->login_attempt_count && $user->login_attempt_count >= $lockAttempts){
					if(strtotime($user->last_login_attempt) + $lockDuration > time()){
							$this->view->status = false;
							$timeDiff = $this->timeDiff(strtotime($user->last_login_attempt) + $lockDuration - time());
							$this->view->error = $this->view->translate('You have reached maximum login attempts. Please try after %s.',$timeDiff);
							$form->addError($this->view->translate('You have reached maximum login attempts. Please try after %s.',$timeDiff));
							$user->login_attempt_count = $user->login_attempt_count + 1;
							$user->save();
							return;
					}else{
							$user->last_login_attempt = NULL;
							$user->login_attempt_count = 0;
							$user->save();
					}
			}
        
			if(empty($otpverification->email)) {
				$isValidPassword = Engine_Api::_()->user()->checkCredential($user->getIdentity(), $password,$user);

				if (!$isValidPassword) {
						if($lockAccount){
								$user->last_login_attempt = date('Y-m-d H:i:s');
								$user->login_attempt_count = $user->login_attempt_count + 1;
								$user->save();
						}
						$this->view->status = false;
						
						if(is_numeric($email)) {
							$this->view->error = $error = Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email or phone number and password, and try again.');
						} else {
							$this->view->error = $error = Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email and password, and try again.');
						}
						$form->addError(Zend_Registry::get('Zend_Translate')->_($error));
						
						if(!empty($enableloginlogs)) {
							// Register bad password login
							Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
									'user_id' => $user->getIdentity(),
									'email' => $email,
									'ip' => $ipExpr,
									'timestamp' => new Zend_Db_Expr('NOW()'),
									'state' => 'bad-password',
							));
						}
						return;
				}
			}

			// Check if user is verified and enabled
			if( !$user->enabled ) {
					if( !$user->verified ) {
							$this->view->status = false;

							$token = Engine_Api::_()->user()->getVerifyToken($user->getIdentity());
							$resend_url = $this->_helper->url->url(array('action' => 'resend', 'token'=> $token), 'user_signup', true);
							$translate = Zend_Registry::get('Zend_Translate');
							$error = $translate->translate('This account still requires either email verification or admin approval.');
							$error .= ' ';
							$error .= sprintf($translate->translate('Click <a href="%s">here</a> to resend the email.'), $resend_url);
							$form->getDecorator('errors')->setOption('escape', false);
							$form->addError($error);
							
							if(!empty($enableloginlogs)) {
								// Register login
								Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
										'user_id' => $user->getIdentity(),
										'email' => $email,
										'ip' => $ipExpr,
										'timestamp' => new Zend_Db_Expr('NOW()'),
										'state' => 'disabled',
								));
							}
							return;
					} else if( !$user->approved ) {
							$this->view->status = false;

							$translate = Zend_Registry::get('Zend_Translate');
							$error = $translate->translate('This account still requires admin approval.');
							$form->getDecorator('errors')->setOption('escape', false);
							$form->addError($error);

							if(!empty($enableloginlogs)) {
								// Register login
								Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
										'user_id' => $user->getIdentity(),
										'email' => $email,
										'ip' => $ipExpr,
										'timestamp' => new Zend_Db_Expr('NOW()'),
										'state' => 'disabled',
								));
							}

							return;
					}
					// Should be handled by hooks or payment
					//return;
			}
      
			//OTP CODE HERE
			if(empty($otpverification->email) && is_numeric($_POST['email']) && $settings->getSetting('otpsms.login.options',0) == 2 && !empty($user->phone_number) && !empty($user->enable_verification) ) {
			 
				// Register login
				Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
					'user_id' => $user->getIdentity(),
					'email' => $email,
					'ip' => $ipExpr,
					'timestamp' => new Zend_Db_Expr('NOW()'),
					'state' => 'OtpVerificationSend',
				));

				$otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
				
				//validate opt limit set by admin
				$codes = Engine_Api::_()->getDbTable('codes','user');
				$response = $codes->generateCode($user, $email, "login");
				if(!empty($response['error'])){
					$form->addError($response['message']);
					$otpverification->step = 1;
					$_POST['email'] = null;
					$_POST['password'] = null;
					return;
				}

				$otpverification->user_id = $user->getIdentity();
				$otpverification->step = 2;
				$otpverification->email = $email;
				$otpverification->password = $password;
				$otpverification->country_code = $user->country_code;
				//$otpverification->return_url = $this->_getParam('return_url');
				//$otpverification->remember = $this->_getParam('remember',0);
				$_SESSION["otpsms_loggedin_user_id"] = $user->getIdentity();
				$code = $response['code'];
				$_POST['email'] = null;
				$_POST['password'] = null;
				
				//send code to mobile
				$phone_number = "+".$user->country_code . $user->phone_number;
				if(!is_numeric($email))
					Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_otp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
				else if(is_numeric($email))
					Engine_Api::_()->getApi('otp', 'core')->sendMessage($phone_number, $code, 'login');
				//redirect to outh login page
				return $this->_helper->redirector->gotoRoute(array('action' => 'verify'), 'user_verify', true);
			}

			// Handle subscriptions
			if( Engine_Api::_()->hasModuleBootstrap('payment') ) {
					// Check for the user's plan
					$subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
					if( !$subscriptionsTable->check($user) ) {
						if(!empty($enableloginlogs)) {
							// Register login
							Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
									'user_id' => $user->getIdentity(),
									'email' => $email,
									'ip' => $ipExpr,
									'timestamp' => new Zend_Db_Expr('NOW()'),
									'state' => 'unpaid',
							));
						}
						// Redirect to subscription page
						$subscriptionSession = new Zend_Session_Namespace('Payment_Subscription');
						$subscriptionSession->unsetAll();
						$subscriptionSession->user_id = $user->getIdentity();
						
						Engine_Api::_()->user()->setViewer();
						Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
						
						return $this->_helper->redirector->gotoRoute(array('module' => 'payment',
								'controller' => 'subscription', 'action' => 'index'), 'default', true);
					}
			}

			// Run pre login hook
			$event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginBefore', $user);
			foreach( (array) $event->getResponses() as $response ) {
					if( is_array($response) ) {
							if( !empty($response['error']) && !empty($response['message']) ) {
									$form->addError($response['message']);
							} else if( !empty($response['redirect']) ) {
									$this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
							} else {
									continue;
							}

							if(!empty($enableloginlogs)) {
								// Register login
								Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
										'user_id' => $user->getIdentity(),
										'email' => $email,
										'ip' => $ipExpr,
										'timestamp' => new Zend_Db_Expr('NOW()'),
										'state' => 'third-party',
								));
							}

							// Return
							return;
					}
			}

			// Version 3 Import compatibility
			if( empty($user->password) ) {
				$compat = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.compatibility.password');
				$migration = null;
				try {
						$migration = Engine_Db_Table::getDefaultAdapter()->select()
								->from('engine4_user_migration')
								->where('user_id = ?', $user->getIdentity())
								->limit(1)
								->query()
								->fetch();
				} catch( Exception $e ) {
						$migration = null;
						$compat = null;
				}
				if( !$migration ) {
						$compat = null;
				}

				if( $compat == 'import-version-3' ) {

						// Version 3 authentication
						$cryptedPassword = self::_version3PasswordCrypt($migration['user_password_method'], $migration['user_code'], $password);
						if( $cryptedPassword === $migration['user_password'] ) {
								// Regenerate the user password using the given password
								$user->salt = (string) rand(1000000, 9999999);
								$user->password = $password;
								$user->save();
								Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
								// @todo should we delete the old migration row?
						} else {
								$this->view->status = false;
								$this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid credentials');
								$form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid credentials supplied'));
								return;
						}
						// End Version 3 authentication

				} else {
						$form->addError('There appears to be a problem logging in. Please reset your password with the Forgot Password link.');

						if(!empty($enableloginlogs)) {
							// Register login
							Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
									'user_id' => $user->getIdentity(),
									'email' => $email,
									'ip' => $ipExpr,
									'timestamp' => new Zend_Db_Expr('NOW()'),
									'state' => 'v3-migration',
							));
						}
						return;
				  }
			}

			// Normal authentication
			else {
				if(!empty($otpverification->email)) {
					if(!empty($otpverification) && !empty($otpverification->email)) {
						if($codeData)
						$codeData->delete();
						$otpverification->unsetAll();
						unset($_SESSION["otpsms_loggedin_user_id"]);
					}
					Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
				} else {
					$authResult = Engine_Api::_()->user()->authenticate($email, $password,$user);
					$authCode = $authResult->getCode();
					Engine_Api::_()->user()->setViewer();

					if( $authCode != Zend_Auth_Result::SUCCESS  ) {
							$this->view->status = false;
							$this->view->error = Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email and password, and try again.');
							$form->addError(Zend_Registry::get('Zend_Translate')->_('The credentials you have supplied are invalid. Please check your email and password, and try again.'));

							if(!empty($enableloginlogs)) {
								// Register login
								Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
										'user_id' => $user->getIdentity(),
										'email' => $email,
										'ip' => $ipExpr,
										'timestamp' => new Zend_Db_Expr('NOW()'),
										'state' => 'bad-password',
								));
							}

							return;
					}
				}
			}

			// -- Success! --
			// Register login
			$loginTable = Engine_Api::_()->getDbtable('logins', 'user');
			$loginTable->insert(array(
					'user_id' => $user->getIdentity(),
					'email' => $email,
					'ip' => $ipExpr,
					'timestamp' => new Zend_Db_Expr('NOW()'),
					'state' => 'success',
					'active' => true,
			));
			$_SESSION['login_id'] = $login_id = $loginTable->getAdapter()->lastInsertId();
        
			//Switch profile
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.recent.login', 1) && !empty($password)) {
				if (!isset($_COOKIE['user_login_users'])) {
					$cookie_value = Zend_Json::encode(array("userid_" . $user->getIdentity() => $user->getIdentity() . '_' . base64_encode($password)));
					setcookie('user_login_users', $cookie_value, time() + 86400, '/');
				} else {
					$user_login_users = Zend_Json::decode($_COOKIE['user_login_users']);
					$cookie_value_merge = array_merge(array("userid_" . $user->getIdentity() => $user->getIdentity() . '_' . base64_encode($password)), $user_login_users);
					$cookie_value = Zend_Json::encode(array_unique($cookie_value_merge));
					setcookie('user_login_users', $cookie_value, time() + 86400, '/');
				}
			}

			// Remember
			if( @$remember ) {
					$lifetime = 1209600; // Two weeks
					Zend_Session::getSaveHandler()->setLifetime($lifetime, true);
					Zend_Session::rememberMe($lifetime);
			}

			// Increment sign-in count
			Engine_Api::_()->getDbtable('statistics', 'core')
					->increment('user.logins');

			// Test activity @todo remove
			$viewer = Engine_Api::_()->user()->getViewer();
			if( $viewer->getIdentity() ) {
					$viewer->lastlogin_date = date("Y-m-d H:i:s");
					if( 'cli' !== PHP_SAPI ) {
							$viewer->lastlogin_ip = $ipExpr;
					}
					$viewer->save();
          Engine_Api::_()->getDbtable('actions', 'activity')
                ->addActivity($viewer, $viewer, 'login');
			}

			// Assign sid to view for json context
			$this->view->status = true;
			$this->view->message = Zend_Registry::get('Zend_Translate')->_('Login successful');
			$this->view->sid = Zend_Session::getId();
			$this->view->sname = Zend_Session::getOptions('name');

			// Run post login hook
			$event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLoginAfter', $viewer);

			// Do redirection only if normal context
			if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
					// Redirect by form
					$uri = $form->getValue('return_url');
					if( $uri ) {
						if(strlen($uri) > 0) {
							unset($_SESSION['return_url']);
              $url = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'].$uri;
							echo json_encode(array('status' => true, 'redirectFullURL' => $url));die;
							//return $this->_redirect($url, array('prependBase' => false));
						}
					}

					// Redirect by session
					$session = new Zend_Session_Namespace('Redirect');
					if( isset($session->uri) ) {
							$uri  = $session->uri;
							$opts = $session->options;
							$session->unsetAll();
							return $this->_redirect($uri, $opts);
					} else if( isset($session->route) ) {
							$session->unsetAll();
							return $this->_helper->redirector->gotoRoute($session->params, $session->route, $session->reset);
					}

					// Redirect by hook
					foreach( (array) $event->getResponses() as $response ) {
							if( is_array($response) ) {
									if( !empty($response['error']) && !empty($response['message']) ) {
											return $form->addError($response['message']);
									} else if( !empty($response['redirect']) ) {
											return $this->_helper->redirector->gotoUrl($response['redirect'], array('prependBase' => false));
									}
							}
					}

					// Redirect to edit profile if user has no profile type
					$aliasedFields = $viewer->fields()->getFieldsObjectsByAlias();
					$profileType = isset($aliasedFields['profile_type']) ?
							(is_object($aliasedFields['profile_type']->getValue($user)) ?
									$aliasedFields['profile_type']->getValue($viewer)->value : null
							) : null;

//             if (empty($profileType)) {
//                 return $this->_helper->redirector->gotoRoute(array(
//                     'action' => 'profile',
//                     'controller' => 'edit',
//                 ), 'user_extended', false);
//             }

				//Redirection
				$url = "";
				$afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
				if($afterLogin == 4) {
					$url= $this->view->url(array('action' => 'home'), 'user_general',true);
				} else if($afterLogin == 3) {
					$url= $this->view->url(array('id' => $viewer->getIdentity()), 'user_profile',true);
				} else if($afterLogin == 2) { 
					$url= $this->view->url(array('controller' => 'edit','action' => 'profile'), 'user_extended',true);
				} else if($afterLogin == 1) {
					$url= Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', '');
				}
				echo json_encode(array('status' => true, 'redirectFullURL' => $url));die;
			}
    }

    public function logoutAction()
    {
        // Check if already logged out
        $viewer = Engine_Api::_()->user()->getViewer();
        if( !$viewer->getIdentity() ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('You are already logged out.');
            if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
                $this->_helper->redirector->gotoRoute(array(), 'default', true);
            }
            return;
        }

        // Run logout hook
        $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutBefore', $viewer);

        // Update online status
        $onlineTable = Engine_Api::_()->getDbtable('online', 'user')
            ->delete(array(
                'user_id = ?' => $viewer->getIdentity(),
            ));

        // Logout
        Engine_Api::_()->user()->getAuth()->clearIdentity();

        if( !empty($_SESSION['login_id']) ) {
            Engine_Api::_()->getDbtable('logins', 'user')->update(array(
                'active' => false,
            ), array(
                'login_id = ?' => $_SESSION['login_id'],
            ));
            unset($_SESSION['login_id']);
        }


        // Run logout hook
        $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserLogoutAfter', $viewer);

        $doRedirect = true;

        // Clear twitter/facebook session info

        // facebook api
        $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
        $facebook = $facebookTable->getApi();
        $settings = Engine_Api::_()->getDbtable('settings', 'core');
        if( $facebook && 'none' != $settings->core_facebook_enable ) {
            /*
            $logoutUrl = $facebook->getLogoutUrl(array(
              'next' => 'http://' . $_SERVER['HTTP_HOST'] . $this->view->url(array(), 'default', true),
            ));
            */
            if( method_exists($facebook, 'getAccessToken') &&
                ($access_token = $facebook->getAccessToken()) ) {
                $doRedirect = false; // javascript will run to log them out of fb
                $this->view->appId = $facebook->getAppId();
                $access_array = explode("|", $access_token);
                if ( ($session_key = $access_array[1]) ) {
                    $this->view->fbSession = $session_key;
                }
            }
            try {
                $facebook->clearAllPersistentData();
            } catch( Exception $e ) {
                // Silence
            }
        }

        unset($_SESSION['facebook_lock']);
        unset($_SESSION['facebook_uid']);

        // twitter api
        /*
        $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
        $twitter = $twitterTable->getApi();
        $twitterOauth = $twitterTable->getOauth();
        if( $twitter && $twitterOauth ) {
          try {
            $result = $accountInfo = $twitter->account->end_session();
          } catch( Exception $e ) {
            // Silence
            echo $e;die();
          }
        }
        */
        unset($_SESSION['twitter_lock']);
        unset($_SESSION['twitter_token']);
        unset($_SESSION['twitter_secret']);
        unset($_SESSION['twitter_token2']);
        unset($_SESSION['twitter_secret2']);

        // Response
        $this->view->status = true;
        $this->view->message =  Zend_Registry::get('Zend_Translate')->_('You are now logged out.');
        if( $doRedirect && null === $this->_helper->contextSwitch->getCurrentContext() ) {
          if(!isset($_SESSION['popupuserid'])) {
            //Redirection
            $afterLogout = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.logout', 3);
            if($afterLogout == 3) {
              return $this->_helper->redirector->gotoRoute(array(), 'default', true);
            } else if($afterLogout == 2) { 
              return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
            } else if($afterLogout == 1) {
              header('Location: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('core.logouturl', ''));
            }
          } else if(isset($_SESSION['popupuserid']) && !empty($_SESSION['popupuserid'])) {
            unset($_SESSION['popupuserid']);
            return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
          }
        }
    }

    public function forgotAction()
    {
        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // no logged in users
        if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
            return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
        }

        // Make form
        $this->view->form = $form = new User_Form_Auth_Forgot();

        // Check request
        if( !$this->getRequest()->isPost() ) {
            return;
        }
        
        if( !$form->isValid($this->getRequest()->getPost())) {
          $validateFields = Engine_Api::_()->core()->validateFormFields($form);
          if(is_countable($validateFields) && engine_count($validateFields)){
            echo json_encode(array('status' => false, 'error_message' => $validateFields));die;
          }
        }
        
        $email = $_POST['email'];
        
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
        
        if(!empty($email)) {
          if(is_numeric($email) && !empty($otpsms_signup_phonenumber)) {
            $fieldname = 'phone_number';
            $label = 'Phone Number';
            $userNotExistError = $this->view->translate("A user account with this phone number is not found.");
            $country_code = $_POST['country_code'];
            $country_code = explode('_', $country_code);
            $user = Engine_Api::_()->getDbtable('users', 'user')->fetchRow(array('country_code = ?' => $country_code[0], 'phone_number = ?' => $email));
          } else {
            $fieldname = 'email';
            $label = $this->view->translate('Email Address');
            $userNotExistError = $this->view->translate("A user account with this email address is not found.");
            $user = Engine_Api::_()->getDbtable('users', 'user')->fetchRow(array('email = ?' => $email));
          }
          // Check for existing user
          
          if( !$user || !$user->getIdentity() ) {
            $errors[] = array('isRequired' => true, 'label' => $label, 'errorMessage' => $userNotExistError);
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
          } else if( !$user->enabled ) {
            $errors[] = array('isRequired' => true, 'label' => $label, 'errorMessage' => "That user account has not yet been verified or disabled by an admin.");
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
          } else if(empty($_SESSION['isValidCode'])) {
            echo json_encode(array('status' => true));die;
          }
        }
        
        $valid = true;
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid = false;
        }
        
        if(!$valid){
          if(is_numeric($email)){
            $valid = true;
          }
        }
        
        if(!$valid) {
          $errors[] = array('errorMessage' => "Email Address / Phone number is not valid, Please provide a valid Email or phone number.");
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        // Check for existing user
        $user = Engine_Api::_()->getDbtable('users', 'user')
          ->fetchRow(array('email = ?' => $form->getValue('email')));
        if( !$user || !$user->getIdentity() ) {
          $user = Engine_Api::_()->getDbtable('users', 'user')
          ->fetchRow(array('phone_number = ?' => $form->getValue('email')));
          if( !$user || !$user->getIdentity() ) {
            $errors[] = array('errorMessage' => 'A user account with this email address is not found.');
            echo json_encode(array('status' => false, 'error_message' => $errors));die;
          }
        }

        // Check to make sure they're enabled
        if( !$user->enabled ) {
          $errors[] = array('errorMessage' => 'That user account has not yet been verified or disabled by an admin.');
          echo json_encode(array('status' => false, 'error_message' => $errors));die;
        }

        // Ok now we can do the fun stuff
        $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
        $db = $forgotTable->getAdapter();
        $db->beginTransaction();

        try
        {
            // Delete any existing reset password codes
            $forgotTable->delete(array(
                'user_id = ?' => $user->getIdentity(),
            ));
            
            // Create a new reset password code
            $code = base_convert(md5($user->salt . $user->email . $user->user_id . uniqid(time(), true)), 16, 36);
            $forgotTable->insert(array(
                'user_id' => $user->getIdentity(),
                'code' => $code,
                'creation_date' => date('Y-m-d H:i:s'),
            ));
            if(!empty($user->email)) {
              // Send user an email
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'core_lostpassword', array(
                  'host' => $_SERVER['HTTP_HOST'],
                  'email' => $user->email,
                  'date' => time(),
                  'recipient_title' => $user->getTitle(),
                  'recipient_link' => $user->getHref(),
                  'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
                  'object_link' => $this->_helper->url->url(array('action' => 'reset', 'code' => $code, 'uid' => $user->getIdentity())),
                  'queue' => false,
              ));
            }
            unset($_SESSION['isValidCode']);
            // Show success
            $this->view->user = $user;
            $this->view->code = $code;
            $this->view->sent = true;

            $db->commit();
        }

        catch( Exception $e )
        {
            $db->rollBack();
            throw $e;
        }
    }

    public function resetAction()
    {
        // no logged in users
        if( Engine_Api::_()->user()->getViewer()->getIdentity() ) {
            return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
        }

        // Check for empty params
        $user_id = $this->_getParam('uid');
        $code = $this->_getParam('code');

        if( empty($user_id) || empty($code) ) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Check user
        $user = Engine_Api::_()->getItem('user', $user_id);
        if( !$user || !$user->getIdentity() ) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Check code
        $forgotTable = Engine_Api::_()->getDbtable('forgot', 'user');
        $forgotSelect = $forgotTable->select()
            ->where('user_id = ?', $user->getIdentity())
            ->where('code = ?', $code);

        $forgotRow = $forgotTable->fetchRow($forgotSelect);
        if( !$forgotRow || (int) $forgotRow->user_id !== (int) $user->getIdentity() ) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Code expired
        // Note: Let's set the current timeout for 1 hours for now
        $min_creation_date = time() - (3600 * 1);
        if( strtotime($forgotRow->creation_date) < $min_creation_date ) { // @todo The strtotime might not work exactly right
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Make form
        $this->view->form = $form = new User_Form_Auth_Reset();
        $form->setAction($this->_helper->url->url(array()));

        // Check request
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        // Check data
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        // Process
        $values = $form->getValues();

        // Check same password
        if( $values['password'] !== $values['passconf'] ) {
            $form->addError('The passwords you entered did not match.');
            return;
        }

        // Db
        $db = $user->getTable()->getAdapter();
        $db->beginTransaction();

        try
        {
            // Delete the lost password code now
            $forgotTable->delete(array(
                'user_id = ?' => $user->getIdentity(),
            ));

            // This gets handled by the post-update hook
            $user->password = $values['password'];
            $user->save();
            if($form->resetalldevice->getValue()){ 
                Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($user->user_id);
            }
            $db->commit();

            $this->view->reset = true;
            //return $this->_helper->redirector->gotoRoute(array(), 'user_login', true);
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }
    }

    public function facebookAction()
    {
        // Clear
        if( null !== $this->_getParam('clear') ) {
            unset($_SESSION['facebook_lock']);
            unset($_SESSION['facebook_uid']);
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
        $facebook = $facebookTable->getApi();
        $settings = Engine_Api::_()->getDbtable('settings', 'core');

        $db = Engine_Db_Table::getDefaultAdapter();
        $ipObj = new Engine_IP();
        $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

        // Enabled?
        if( !$facebook || 'none' == $settings->core_facebook_enable ) {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        // Already connected
        if( $facebook->getUser() ) {
            $code = $facebook->getPersistentData('code');
            // Get email address
            $apiInfo = $facebook->api('/me?fields=name,gender,email,locale');

            // Attempt to login
            if( !$viewer->getIdentity() ) {
                $facebook_uid = $facebook->getUser();
                if( $facebook_uid ) {
                    $user_id = $facebookTable->select()
                        ->from($facebookTable, 'user_id')
                        ->where('facebook_uid = ?', $facebook_uid)
                        ->query()
                        ->fetchColumn();
                }
                if(empty(@$user_id) && !empty($apiInfo) && !empty($apiInfo['email']) && isset($apiInfo['email'])) {
                  $userTable = Engine_Api::_()->getDbTable('users', 'user');
                  $user_id = $userTable->select()
                          ->from($userTable, 'user_id')
                          ->where('email = ?', $apiInfo['email'])
                          ->query()
                          ->fetchColumn();
                }
                if( $user_id &&
                    $viewer = Engine_Api::_()->getItem('user', $user_id) ) {
                    Zend_Auth::getInstance()->getStorage()->write($user_id);

                    // Register login
                    $viewer->lastlogin_date = date("Y-m-d H:i:s");

                    if( 'cli' !== PHP_SAPI ) {
                        $viewer->lastlogin_ip = $ipExpr;

                        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                            'user_id' => $user_id,
                            'ip' => $ipExpr,
                            'timestamp' => new Zend_Db_Expr('NOW()'),
                            'state' => 'success',
                        ));
                    }

                    $viewer->save();
                } else if( $facebook_uid ) {
                    // They do not have an account
                    $_SESSION['facebook_signup'] = true;
                    return $this->_helper->redirector->gotoRoute(array(
                        //'action' => 'facebook',
                    ), 'user_signup', true);
                }
            } else {
                // Check for facebook user
                $facebookInfo = $facebookTable->select()
                    ->from($facebookTable)
                    ->where('facebook_uid = ?', $facebook->getUser())
                    ->limit(1)
                    ->query()
                    ->fetch();

                if (!empty($facebookInfo) && $facebookInfo['user_id'] != $viewer->getIdentity()) {
                    // Redirect to referer page
                    $url = $_SESSION['redirectURL'];
                    $parsedUrl = parse_url($url);
                    $separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
                    $url .= $separator . 'already_integrated_fb_account=1';
                    $facebook->clearAllPersistentData();
                    return $this->_redirect($url, array('prependBase' => false));
                }
                // Attempt to connect account
                $info = $facebookTable->select()
                    ->from($facebookTable)
                    ->where('user_id = ?', $viewer->getIdentity())
                    ->limit(1)
                    ->query()
                    ->fetch();
                if( empty($info) ) {
                    $facebookTable->insert(array(
                        'user_id' => $viewer->getIdentity(),
                        'facebook_uid' => $facebook->getUser(),
                        'access_token' => $facebook->getAccessToken(),
                        'code' => $code,
                        'expires' => 0,
                    ));
                } else {
                    // Save info to db
                    $facebookTable->update(array(
                        'facebook_uid' => $facebook->getUser(),
                        'access_token' => $facebook->getAccessToken(),
                        'code' => $code,
                        'expires' => 0,
                    ), array(
                        'user_id = ?' => $viewer->getIdentity(),
                    ));
                }
            }
            //Redirection
            $afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
            if($afterLogin == 4) {
              return $this->_helper->_redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
            } else if($afterLogin == 3) {
              return $this->_helper->redirector->gotoRoute(array('id' => $viewer->getIdentity()), 'user_profile', true);
            } else if($afterLogin == 2) { 
              return $this->_helper->redirector->gotoRoute(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
            } else if($afterLogin == 1) {
              header('Location: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', ''));
            }
            // Redirect to referer page
//             $url = $_SESSION['redirectURL'];
//             return $this->_redirect($url, array('prependBase' => false));
        }

        // Not connected
        else {
            // Okay
            if( !empty($_GET['code']) ) {
                // This doesn't seem to be necessary anymore, it's probably
                // being handled in the api initialization
                return $this->_helper->redirector->gotoRoute(array(), 'default', true);
            }

            // Error
            else if( !empty($_GET['error']) ) {
                // @todo maybe display a message?
                return $this->_helper->redirector->gotoRoute(array(), 'default', true);
            }

            // Redirect to auth page
            else {
                $url = $facebook->getLoginUrl(array(
                    'redirect_uri' => (_ENGINE_SSL ? 'https://' : 'http://')
                        . $_SERVER['HTTP_HOST'] . $this->view->url(),
                    'scope' => join(',', array(
                        'email',
                    )),
                ));
                return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
            }
        }
    }
    
    public function twitterAction() {

      $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
      
      $viewer = Engine_Api::_()->user()->getViewer();
      $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
      $db = Engine_Db_Table::getDefaultAdapter();
      
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));

      //Add your app consumer key between single quotes
      define('CONSUMER_KEY', $settings['key']);
      //Add your app consumer secret key between single quotes
      define('CONSUMER_SECRET', $settings['secret']);

      $callback = ((_ENGINE_SSL ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'user/auth/twitter';
      //Your app callback URL i.e. 
      define('OAUTH_CALLBACK', $callback); 
      
      if(isset($_SESSION['oauth_token']) && isset($_GET['oauth_token'])) {
      
        $oauth_token = $_SESSION['oauth_token'];unset($_SESSION['oauth_token']);
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        
        //necessary to get access token other wise u will not have permision to get user info
        $params=array("oauth_verifier" => $_GET['oauth_verifier'], "oauth_token" => $_GET['oauth_token']);
        $access_token = $connection->oauth("oauth/access_token", $params);
        
        //now again create new instance using updated return oauth_token and oauth_token_secret because old one expired if u dont u this u will also get token expired error
        $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET, $access_token['oauth_token'],$access_token['oauth_token_secret']);
        
        $twitter_token = $access_token['oauth_token'];
        $twitter_secret = $access_token['oauth_token_secret'];
        $accountInfo = $connection->get("account/verify_credentials");
        
        if( $viewer->getIdentity() ) {
            $info = $twitterTable->select()
                ->from($twitterTable)
                ->where('user_id = ?', $viewer->getIdentity())
                ->query()
                ->fetch();
            if( !empty($info) ) {
                $twitterTable->update(array(
                    'twitter_uid' => $accountInfo->id,
                    'twitter_token' => $twitter_token,
                    'twitter_secret' => $twitter_secret,
                ), array(
                    'user_id = ?' => $viewer->getIdentity(),
                ));
            } else {
                $twitterTable->insert(array(
                    'user_id' => $viewer->getIdentity(),
                    'twitter_uid' => $accountInfo->id,
                    'twitter_token' => $twitter_token,
                    'twitter_secret' => $twitter_secret,
                ));
            }
            // Redirect
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } else { // Otherwise try to login?
            $info = $twitterTable->select()
                ->from($twitterTable)
                ->where('twitter_uid = ?', $accountInfo->id)
                ->query()
                ->fetch();
            if(empty($info) || empty($info['user_id'])) {
                // They do not have an account
                $_SESSION['twitter_signup'] = true;
                $name = explode(" ", $accountInfo->name);
                $fieldArray['id'] = $accountInfo->id;
                $fieldArray['photo'] = $accountInfo->profile_image_url;
                $fieldArray['first_name'] = @$name[0];
                $fieldArray['last_name'] =  @$name[1]; 
                $fieldArray['username'] =  $accountInfo->screen_name;
                $fieldArray['lang'] =  $accountInfo->lang;

                $_SESSION['twitter_token'] = $twitter_token;
                $_SESSION['twitter_secret'] = $twitter_secret;

                $_SESSION['twitter_uid'] = $accountInfo->id;
                $_SESSION['signup_fields'] = $fieldArray;
                return $this->_helper->redirector->gotoRoute(array(//'action' => 'twitter',
                ), 'user_signup', true);
            } else {
                Zend_Auth::getInstance()->getStorage()->write($info['user_id']);
                // Register login
                $viewer = Engine_Api::_()->getItem('user', $info['user_id']);
                $viewer->lastlogin_date = date("Y-m-d H:i:s");
                if( 'cli' !== PHP_SAPI ) {
                    $viewer->lastlogin_ip = $ipExpr;
                    Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                        'user_id' => $info['user_id'],
                        'ip' => $ipExpr,
                        'timestamp' => new Zend_Db_Expr('NOW()'),
                        'state' => 'success',
                        'source' => 'twitter',
                    ));
                }
                $viewer->save();
                
                //Redirection
                $afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
                if($afterLogin == 4) {
                  return $this->_helper->_redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
                } else if($afterLogin == 3) {
                  return $this->_helper->redirector->gotoRoute(array('id' => $viewer->getIdentity()), 'user_profile', true);
                } else if($afterLogin == 2) { 
                  return $this->_helper->redirector->gotoRoute(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
                } else if($afterLogin == 1) {
                  header('Location: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', ''));
                }
                // Redirect to referer page
//                 $url = $_SESSION['redirectURL'];
//                 return $this->_redirect($url, array('prependBase' => false));
            }
        }
      } else{
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        $temporary_credentials = $connection->oauth('oauth/request_token', array("oauth_callback" =>$callback));
        $_SESSION['oauth_token']=$temporary_credentials['oauth_token'];   
        $_SESSION['oauth_token_secret']=$temporary_credentials['oauth_token_secret'];
        $url = $connection->url("oauth/authenticate", array("oauth_token" => $temporary_credentials['oauth_token']));
        // REDIRECTING TO THE URL
        header('Location: ' . $url); 
      }
    }
    
    public function googleAction() {
      // Clear
      unset($_SESSION['google_lock']);
      unset($_SESSION['signup_fields']);
      unset($_SESSION['google_signup']);
      if (isset($_GET['return_url']))
        $_SESSION['redirectURL'] = $_GET['return_url'];
      $viewer = Engine_Api::_()->user()->getViewer();
      $FieldArray = array();

      $table = Engine_Api::_()->getDbTable('google', 'user');
      $db = Engine_Db_Table::getDefaultAdapter();
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
      $this->view->error = true;
      $this->view->success = false;
      $api_key = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientid', '');
      $api_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.google.clientsecret', '');
      $siteURL = ((_ENGINE_SSL ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . $this->view->baseUrl().'/' . 'user/auth/google';
      // Already connected

      $queryString = $_SERVER['REQUEST_URI'];
      parse_str(explode("?",$queryString)[1], $get_array);

      if (!empty($get_array['code'])) {
        $code = $get_array['code'];
        $clientId = $api_key;
        $clientSecret = $api_secret;
        $referer = $siteURL;

        $postBody = 'code=' . urlencode($code)
                . '&grant_type=authorization_code'
                . '&redirect_uri=' . urlencode($referer)
                . '&client_id=' . urlencode($clientId)
                . '&client_secret=' . urlencode($clientSecret);

        $curl = curl_init();
        curl_setopt_array($curl, array(CURLOPT_CUSTOMREQUEST => 'POST'
          , CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token'
          , CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'
              , 'Content-Length: ' . strlen($postBody)
              , 'User-Agent: YourApp/0.1 +http://yoursite.com/yourapp'
          )
          , CURLOPT_POSTFIELDS => $postBody
          , CURLOPT_REFERER => $referer
          , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
          , CURLOPT_TIMEOUT => 12 // max seconds to wait
          , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
          , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
          , CURLOPT_SSL_VERIFYPEER => 0 // do verify the SSL of CURLOPT_URL, this is for security
          , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
        ));
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $response = json_decode($response, true);

        if (empty($response['access_token'])) {
          $this->view->error = true;
          return;
        }
        $accessToken = $response['access_token'];
        $refreshToken = $response['refresh_token'];

        // get user info
        $q = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken;
        $json = $this->url_get_contents($q);
        $userInfoArray = json_decode($json, true);
        if (!empty($userInfoArray['id'])) {
          $googleid = $userInfoArray['id'];
          $FieldArray['id'] = $googleid;
          $FieldArray['photo'] = $userInfoArray['picture'];
          $FieldArray['email'] = $userInfoArray['email'];
          $FieldArray['first_name'] = $userInfoArray['given_name'];
          $FieldArray['last_name'] = $userInfoArray['family_name'];
        } else {
          $this->view->error = true;
          return;
        }

        // Attempt to login
        if (!$viewer->getIdentity()) {
          if ($googleid) {
            $user_id = $table->select()
                    ->from($table, 'user_id')
                    ->where('google_uid = ?', $googleid)
                    ->query()
                    ->fetchColumn();
          } 
          
          if(empty(@$user_id) && !empty($userInfoArray['email']) && isset($userInfoArray['email'])) {
            $userTable = Engine_Api::_()->getDbTable('users', 'user');
            $user_id = $userTable->select()
                    ->from($userTable, 'user_id')
                    ->where('email = ?', $userInfoArray['email'])
                    ->query()
                    ->fetchColumn();
          }
          
          $viewer = Engine_Api::_()->getItem('user', $user_id);
          if ($user_id && $viewer->getIdentity()) {
            Zend_Auth::getInstance()->getStorage()->write($user_id);
            // Register login
            $viewer->lastlogin_date = date("Y-m-d H:i:s");
            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;
              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $user_id,
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'success',
                  'source' => 'google',
              ));
            }
            $viewer->save();
          } else if ($googleid) {
            if (!empty($user_id))
              Engine_Api::_()->getDbtable('google', 'user')->delete(array('user_id =?' => $user_id));
            // They do not have an account
            $_SESSION['google_signup'] = true;
            $_SESSION['access_token'] = $accessToken;
            $_SESSION['refresh_token'] = $refreshToken;
            $_SESSION['google_uid'] = $googleid;
            $_SESSION['signup_fields'] = $FieldArray;

            return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
          }
        }
        
        //Redirection
        $afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
        if($afterLogin == 4) {
          return $this->_helper->_redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
        } else if($afterLogin == 3) {
          return $this->_helper->redirector->gotoRoute(array('id' => $viewer->getIdentity()), 'user_profile', true);
        } else if($afterLogin == 2) { 
          return $this->_helper->redirector->gotoRoute(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
        } else if($afterLogin == 1) {
          header('Location: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', ''));
        }
      }
      // Not connected
      else {
        // Okay
        if (!empty($get_array['code']))
          $this->view->error = true;
        // Error
        else if (!empty($get_array['code']))
          $this->view->error = true;
        // Redirect to auth page
        else {
          $url = "https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile&response_type=code&access_type=offline&redirect_uri=" . $siteURL . "&approval_prompt=force&client_id=" . $api_key;
          return $this->_helper->redirector->gotoUrl($url, array('prependBase' => false));
        }
      }
    }


    function checkTelegramAuthorization($auth_data,$token) {
    
      $check_hash = $auth_data['hash'];
      unset($auth_data['hash']);
      $data_check_arr = [];
      foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
      }
      sort($data_check_arr);
      $data_check_string = implode("\n", $data_check_arr);
      $secret_key = hash('sha256', $token, true);
      $hash = hash_hmac('sha256', $data_check_string, $secret_key);
      if (strcmp($hash, $check_hash) !== 0) {
        return array("error"=>1,"message"=>'Data is NOT from Telegram');
      }
      if ((time() - $auth_data['auth_date']) > 86400) {
       return array("error"=>1,"message"=>'Data is outdated');
      }
      return $auth_data;
    }
  
    public function telegramAction() {
      // Clear
      
      unset($_SESSION['signup_fields']);
      unset($_SESSION['telegram_signup']);
      if (isset($_GET['return_url']))
        $_SESSION['redirectURL'] = $_GET['return_url'];
      $viewer = Engine_Api::_()->user()->getViewer();
      
      $settings = Engine_Api::_()->getDbtable('settings', 'core');
  
      $db = Engine_Db_Table::getDefaultAdapter();
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
      $telegramKeys = Engine_Api::_()->user()->telegramEnable();
      // Enabled?
      if (!$telegramKeys) {
        echo json_encode(array("status"=>0,'message'=>"Api credentials are wrong."));die;
      }
  
      //validate data
      $valid = $this->checkTelegramAuthorization($_POST,$telegramKeys['token']);
      if($valid['error'] == 1){
        echo json_encode(array("status"=>0,'message'=>$valid['message']));die;
      }
  
      $data = $valid;
      // Already connected
      if (!empty($_POST)) {
        
        $telegramTable = Engine_Api::_()->getDbTable("telegram","user");
        // Attempt to login
        if (!$viewer->getIdentity()) {
          $user_id = $telegramTable->select()
                  ->from($telegramTable, 'user_id')
                  ->where('telegram_uid = ?', $data['id'])
                  ->query()
                  ->fetchColumn();
          
          $viewer = Engine_Api::_()->getItem('user', $user_id);

          if ($user_id) {
            $translate = Zend_Registry::get('Zend_Translate');
            // Check if user is verified and enabled
            if( !$viewer->enabled ) {
              if( !$viewer->verified ) {
                $error = $translate->translate('This account still requires either email verification.');
                // Register login
                Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $viewer->getIdentity(),
                  'email' => !empty($viewer->email) ? $viewer->email : "",
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'disabled',
                ));
                
                echo json_encode(array("status" => 0,'message'=>$error));die;
              } else if(!$viewer->approved) {
                $error = $translate->translate('This account still requires admin approval.');
                // Register login
                Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $viewer->getIdentity(),
                  'email' => !empty($viewer->email) ? $viewer->email : "",
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'disabled',
                ));
                
                echo json_encode(array("status" => 0,'message'=>$error));die;
              }
            }

            Zend_Auth::getInstance()->getStorage()->write($user_id);
  
            // Register login
            $viewer->lastlogin_date = date("Y-m-d H:i:s");
  
            if ('cli' !== PHP_SAPI) {
              $viewer->lastlogin_ip = $ipExpr;
  
              Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                  'user_id' => $user_id,
                  'ip' => $ipExpr,
                  'timestamp' => new Zend_Db_Expr('NOW()'),
                  'state' => 'success',
                  // 'source' => 'facebook',
              ));
            }
  
            $viewer->save();

            $afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
            if($afterLogin == 4) {
              $url =  $this->view->url(array('action' => 'home'), 'user_general', true);
            } else if($afterLogin == 3) {
              $url =  $this->view->url(array('id' => $viewer->getIdentity()), 'user_profile', true);
            } else if($afterLogin == 2) { 
              $url =  $this->view->url(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
            } else if($afterLogin == 1) {
              $url = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', '');
            }

            echo json_encode(array("status"=>2,'url'=>$url));die;
          } else if ($data['id']) {
            if (!empty($user_id))
              Engine_Api::_()->getDbtable('telegram', 'user')->delete(array('user_id =?' => $user_id));
            // They do not have an account
            $_SESSION['telegram_signup'] = true;
            $FieldArray['id'] = $data['id'];
            $FieldArray['first_name'] = $data["first_name"];
            $FieldArray['last_name'] = $data["last_name"];
            $FieldArray['username'] = $data["username"];
            $FieldArray['email'] = "";
            $FieldArray['photo'] = $data['photo_url'];
            $_SESSION['signup_fields'] = $FieldArray;
            $_SESSION['telegram_uid'] = $data['id'];
            $_SESSION['telegram_token'] = $data['hash'];
            
            echo json_encode(array("status"=>1));die;
            
          }
        }
        // Redirect to referer page
        echo json_encode(array("status"=>0,'message'=>"Api credentials are wrong."));die;
      }
    }

    public function linkedinAction() {
    
      $this->view->error = true;
      $this->view->success = false;
      
      if (isset($_GET['return_url']))
        $_SESSION['redirectURL'] = $_GET['return_url'];
        
      if (null !== $this->_getParam('clear') && empty($_GET['oauth_verifier'])) {
        unset($_SESSION['linkedin_lock']);
        unset($_SESSION['linkedin_uid']);
        unset($_SESSION['linkedin_secret']);
        unset($_SESSION['linkedin_token']);
        unset($_SESSION['oauth_token_secret']);
        unset($_SESSION['linkedin_token']);
        unset($_SESSION['linkedin_access']);
        unset($_SESSION['signup_fields']);
        unset($_SESSION['linkedin_signup']);
      }
      
      if ($this->_getParam('denied')) {
        $this->view->error = 'Access Denied!';
        return;
      }
      
      // Setup
      $viewer = Engine_Api::_()->user()->getViewer();
      $db = Engine_Db_Table::getDefaultAdapter();
      $ipObj = new Engine_IP();
      $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
      $FieldArray = array();
      $likedinTable = Engine_Api::_()->getDbtable('linkedin', 'user');
      
      $likedin = $likedinTable->getApi();
      $access = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.access', '');
      $secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.linkedin.secret', '');

      // Check
      if (empty($likedin)) {
        $this->error = true;
      }
      try {
        if(!empty($_GET["error_description"])){
          die($_GET["error_description"]);
        }
        if (empty($_GET['code'])) {
          $likedin->setCallbackUrl((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url());
          $likedin->setTokenAccess(NULL);
          header('Location: https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id='.$access.'&redirect_uri='.urlencode((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url()).'&state=fooobar&scope=profile%20email%20openid');
            exit();
        } else if (!empty($_GET['code'])) {

          $result = json_decode($this->url_get_contents("https://www.linkedin.com//oauth/v2/accessToken?grant_type=authorization_code&code=".$_GET['code']."&redirect_uri=".urlencode((_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->view->url())."&client_id=".$access."&client_secret=".$secret),true);
          if (!empty($result['access_token'])) {
            $_SESSION['linkedin_token'] = $token = $result['access_token'];
            $_SESSION['linkedin_secret'] = $secret = $result['access_token'];
            $_SESSION['linkedin_access'] = $result;


            $url = "https://api.linkedin.com/v2/userinfo";
            $curl = curl_init();
            curl_setopt_array( $curl, 
            array( CURLOPT_CUSTOMREQUEST => 'GET'
                  , CURLOPT_URL => $url
                  , CURLOPT_HTTPHEADER => array(  'Authorization: Bearer '.$result['access_token'] )
                  , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
            ) );
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
            curl_close($curl);

            $basicProfile = json_decode($response,true);
            
            if ($basicProfile) {
              $image = "";
              if(!empty($basicProfile["picture"]))
                  $image = $basicProfile["picture"];
              $FieldArray['id'] = $basicProfile['sub'];
              $FieldArray['photo'] = $image;
              $FieldArray['email'] = $basicProfile["email"];
              $FieldArray['first_name'] = $basicProfile['given_name'];
              $FieldArray['last_name'] = $basicProfile['family_name'];
              $infoId = $basicProfile['sub'];

              if (!$infoId)
                  return;
              $_SESSION['linkedin_lock'] = true;
              $_SESSION['linkedin_uid'] = $infoId;
              $_SESSION['signup_fields'] = $FieldArray;
            }
          }
          
          // Attempt to login
          if (!$viewer->getIdentity()) {

            if ($infoId) {
              $user_id = $likedinTable->select()
                      ->from($likedinTable, 'user_id')
                      ->where('linkedin_uid = ?', $infoId)
                      ->query()
                      ->fetchColumn();
            } 
            
            if(empty(@$user_id) && !empty($basicProfile["email"]) && isset($basicProfile["email"])) {
              $userTable = Engine_Api::_()->getDbTable('users', 'user');
              $user_id = $userTable->select()
                      ->from($userTable, 'user_id')
                      ->where('email = ?', $basicProfile["email"])
                      ->query()
                      ->fetchColumn();
            }
            $viewer = Engine_Api::_()->getItem('user', $user_id);
            if ($user_id && $viewer->getIdentity()) {
              Zend_Auth::getInstance()->getStorage()->write($user_id);
              // Register login
              $viewer->lastlogin_date = date("Y-m-d H:i:s");
              if ('cli' !== PHP_SAPI) {
                $viewer->lastlogin_ip = $ipExpr;

                Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
                    'user_id' => $user_id,
                    'ip' => $ipExpr,
                    'timestamp' => new Zend_Db_Expr('NOW()'),
                    'state' => 'success',
                    // 'source' => 'linkedin',
                ));
              }
              $viewer->save();
            } else if ($infoId) {
              if (!empty($user_id))
                Engine_Api::_()->getDbtable('linkedin', 'user')->delete(array('user_id =?' => $user_id));
              // They do not have an account
              $_SESSION['linkedin_signup'] = true;
              
              return $this->_helper->redirector->gotoRoute(array(), 'user_signup', true);
            }
          }
          //Redirection
          $afterLogin = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4);
          if($afterLogin == 4) {
            return $this->_helper->_redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
          } else if($afterLogin == 3) {
            return $this->_helper->redirector->gotoRoute(array('id' => $viewer->getIdentity()), 'user_profile', true);
          } else if($afterLogin == 2) { 
            return $this->_helper->redirector->gotoRoute(array('controller' => 'edit','action' => 'profile'), 'user_extended', true);
          } else if($afterLogin == 1) {
            header('Location: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('core.loginurl', ''));
          }
        }
      } catch (Exception $e) {
        throw $e;
        $this->view->error = true;
      }
    }
    
    function url_get_contents ($Url) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $Url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      $output = curl_exec($ch);
      curl_close($ch);
      return $output;
    }

    static protected function _version3PasswordCrypt($method, $salt, $password)
    {
        // For new methods
        if( $method > 0 ) {
            if( !empty($salt) ) {
                list($salt1, $salt2) = str_split($salt, ceil(strlen($salt) / 2));
                $salty_password = $salt1.$password.$salt2;
            } else {
                $salty_password = $password;
            }
        }

        // Hash it
        switch( $method ) {
            // crypt()
            default:
            case 0:
                $user_password_crypt = crypt($password, '$1$'.str_pad(substr($salt, 0, 8), 8, '0', STR_PAD_LEFT).'$');
                break;

            // md5()
            case 1:
                $user_password_crypt = md5($salty_password);
                break;

            // sha1()
            case 2:
                $user_password_crypt = sha1($salty_password);
                break;

            // crc32()
            case 3:
                $user_password_crypt = sprintf("%u", crc32($salty_password));
                break;
        }

        return $user_password_crypt;
    }
    
  function loginOtpAction() {

    $email = $this->_getParam('emailField');
    $password = $this->_getParam('password');
    $country_code = $this->_getParam('country_code');
    $country_code = explode('_', $country_code);
    $country_code = $country_code[0];
    $type = $this->_getParam('type');
    
    $valid = true;
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $valid = false;
    }
    
    if(!$valid){
      if(is_numeric($email)){
        $valid = true; 
      }
    }
    
    if(!$valid) {
      echo json_encode(array("error" => 1,'message'=>"Email Address / Phone number is not valid, Please provide a valid Email or phone number."));die;  
      return;
    }
    
    // Get ip address
    $db = Engine_Db_Table::getDefaultAdapter();
    $ipObj = new Engine_IP();
    $ipExpr = new Zend_Db_Expr($db->quoteInto('UNHEX(?)', bin2hex($ipObj->toBinary())));
    
    // Check for existing user
    $user = Engine_Api::_()->getDbtable('users', 'user')->fetchRow(array('email = ?' => $email));
    if( !$user || !$user->getIdentity() ) {
      $user = Engine_Api::_()->getDbTable('users', 'user')->fetchRow(array('phone_number = ?' => $email, 'country_code = ?' => $country_code));
      if( !$user || !$user->getIdentity() ) {
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'no-member',
        ));
        if(is_numeric($email)) {
          echo json_encode(array("error" => 1,'message'=>Zend_Registry::get('Zend_Translate')->_('No record of a member with this phone number is found.')));die;
        } else {
          echo json_encode(array("error" => 1,'message'=>Zend_Registry::get('Zend_Translate')->_('No record of a member with this email address is found.')));die;
        }
      }
    }
    
    $translate = Zend_Registry::get('Zend_Translate');
    // Check if user is verified and enabled
    if( !$user->enabled ) {
      if( !$user->verified ) {
        $this->view->status = false;
        $translate = Zend_Registry::get('Zend_Translate');
        $error = $translate->translate('This account still requires either email verification.');
        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
        ));
        
        echo json_encode(array("error" => 1,'message'=>$error));die;
      } else if(!$user->approved) {
        $this->view->status = false;
        $error = $translate->translate('This account still requires admin approval.');

        // Register login
        Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
          'user_id' => $user->getIdentity(),
          'email' => $email,
          'ip' => $ipExpr,
          'timestamp' => new Zend_Db_Expr('NOW()'),
          'state' => 'disabled',
        ));
        
        echo json_encode(array("error" => 1,'message'=>$error));die;
      }  
    }
    
    if(is_numeric($email) && empty($user->phone_number) ) {
      $error = $translate->translate('No Phone Number is registered with your account please enter password to login.');
      echo json_encode(array("error" => 1,'message'=>$error));die;
    }
    
    // Register login
    Engine_Api::_()->getDbtable('logins', 'user')->insert(array(
      'user_id' => $user->getIdentity(),
      'email' => $email,
      'ip' => $ipExpr,
      'timestamp' => new Zend_Db_Expr('NOW()'),
      'state' => 'OtpVerificationSend',
    ));
    
    $otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
    $otpverification->unsetAll();
    $otpverification->user_id = $user->getIdentity();
    $otpverification->email = $email;
    $otpverification->password = $password;
    //$otpverification->return_url = $this->_getParam('return_url');
    //$otpverification->remember = $this->_getParam('remember',0);
    $_SESSION["otpsms_loggedin_user_id"] = $user->getIdentity();
    
    //validate opt limit set by admin
    $test_user_id = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.user.id', 0);
    if($test_user_id && $test_user_id == $user->getIdentity()) {
      $otpsms_test_code = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.test.code', '');
      if($otpsms_test_code) {
        $code = $otpsms_test_code;
      }
    } else {
      $codes = Engine_Api::_()->getDbTable('codes','user');
      $response = $codes->generateCode($user, $email, $type);
      if(!empty($response['error'])){
        echo json_encode(array('error'=>1,'message'=>$response['message']));die;  
      }
    }
    $code = $response['code'];
    
    if(empty($test_user_id)) {
      if(is_numeric($email)) {
        //send code to mobile
        $phone_number = "+".$country_code . $email;
        Engine_Api::_()->getApi('otp', 'core')->sendMessage($phone_number, $code, $type);
      } else {
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_otp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
      }
    }
    
    $formOTP = new User_Form_Otpsms();
    $formOTP->setAttrib('class','global_form');
    //$formOTP->setAttrib('id', 'otpsms_login_verify');
    $formOTP->setAction($this->view->url(array('module'=>'user','action'=>'login','controller'=>'auth'),'default',true));
    $formOTP->email_data->setValue($user->getIdentity());
    $formOTP->email->setValue($email);
    $formOTP->country_code->setValue($country_code);
    
    //send for to reponse
    if(is_numeric($email)) {
      echo json_encode(array('form'=>$formOTP->render($this->view),'error'=>0, 'timerdata' => Engine_Api::_()->getApi('otp', 'core')->getOtpExpire()));die;
    } else {
      echo json_encode(array('form'=>$formOTP->render($this->view),'error'=>0));die;
    }
  }
 
  function resendLoginCodeAction() {
  
    $type = $this->_getParam('type');
    $email = $this->_getParam('email');
    $country_code = $this->_getParam('country_code');
    
    $user = Engine_Api::_()->getItem('user',$this->_getParam('user_id',0));
    //validate opt limit set by admin
    $codes = Engine_Api::_()->getDbTable('codes','user');
    $response = $codes->generateCode($user, $email, $type);
    if(!empty($response['error'])){
      echo json_encode(array('error'=>1,'message'=>$response['message']));die;  
    }
    $code = $response['code'];
    //send code to mobile
    $phone_number = "+".$country_code . $email;
    Engine_Api::_()->getApi('otp', 'core')->sendMessage($phone_number, $code, $type);
    //send for to reponse
    $form = new User_Form_Otpsms();
    $description = $form->getDescription();
    echo json_encode(array('error'=>0,'description'=>$description, 'timerdata' => Engine_Api::_()->getApi('otp', 'core')->getOtpExpire()));die;
  }
  
  function verifyAction() {

    //verify OTP
    $this->view->form = $form = new User_Form_Otpsms();
    
    $otpverification = new Zend_Session_Namespace('Otp_Login_Verification');
    $this->view->user_id = $otpverification->user_id;
    
    if(!empty($this->view->user_id))
      $user = Engine_Api::_()->getItem('user', $this->view->user_id);
      
    $this->view->email = $otpverification->email;
    $this->view->country_code = $otpverification->country_code;

    $form->setAction($this->view->url(array('module'=>'user','controller'=>'auth','action'=>'login'),'default',true));
	
    if( !$this->getRequest()->isPost() ) {
			$form->populate($_SESSION['Otp_Login_Verification']);
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No action taken');
      return;
    }
    
    if( !$form->isValid($_SESSION['Otp_Login_Verification']) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }
  }

  public function removerecentloginAction() {
    $user_id = $this->_getParam('user_id', null);
    $redirectURL = $this->_getParam('redirectURL', null);
    if(empty($redirectURL)) {
      $redirectURL = $this->view->url(array(), 'default', true);
    }
    
    if(empty($user_id))
        return;
    $recent_login = Zend_Json::decode($_COOKIE['user_login_users']);
    if(engine_count($recent_login) > 0) {
      unset($recent_login['userid_'.$user_id]);
      $cookie_value = Zend_Json::encode($recent_login);
      setcookie('user_login_users', $cookie_value, time() + 86400, '/');
      
      echo json_encode(array('status' => true, 'message' => $this->view->translate('You have successfully removed account.'), 'redirect_url' => $redirectURL));die;
    } else {
      setcookie('user_login_users', '', time() + 86400, '/');
      
      echo json_encode(array('status' => false));die;
    }
  }
  
  public function poploginAction() {
  
    $id = $this->_getParam('user_id');
    $type = $this->_getParam('type', false);
    if($type) {
      $_SESSION['popupuserid'] = $id;
      $this->_redirect('logout');
    }
    $cookies = Zend_Json::decode($_COOKIE['user_login_users']);
    if(isset($cookies['userid_'.$id]) && !empty($cookies['userid_'.$id])) {
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $this->view->user = Engine_Api::_()->getItem('user', $id);
      $this->view->user_id = $id;
    } else {
      $this->_redirect('login');
      exit();
    }
  }
  
  public function quickloginAction() {
  
    $id = $this->_getParam('user_id');
    $password = $this->_getParam('password', null);
    $popup = $this->_getParam('popup', false);
    
    if(empty($password))
        return;
    $user = Engine_Api::_()->getItem('user', $id);
    
    if(!empty($popup)) {
      $password = $password;
    } else {
      $password = base64_decode($password);
    }
    
    if(Engine_Api::_()->user()->checkCredential($id, $password, $user) && $user->getIdentity() == $id) {

      // @todo change this to look up actual superadmin level
      if (!$this->getRequest()->isPost()) {
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
          echo json_encode(array('status' => false, 'redirect_url' => $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null))));die;
          return;
        } else {
          echo json_encode(array('status' => false, 'message' => $this->view->translate('Your password is not correct.')));die;
          return;
        }
      }

      // Login
      Zend_Auth::getInstance()->getStorage()->write($user->getIdentity());

      // Redirect
      if (null === $this->_helper->contextSwitch->getCurrentContext()) {
        echo json_encode(array('status' => false, 'redirect_url' => $this->_helper->redirector->gotoRoute(array("action" => 'home'), 'user_general', true)));die;
        return;
      } else {
        echo json_encode(array('status' => true, 'redirect_url' => $this->view->url(array('action' => 'home'), 'user_general', true)));die;
        return;
      }
    }
    echo json_encode(array('status' => false, 'message' => $this->view->translate('Your password is not correct.')));die;
    return;
  }
}
