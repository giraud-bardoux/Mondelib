<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_AdminManageController extends Core_Controller_Action_Admin 
{
  public function indexAction()
  {
    // Element: profile_type
    $this->view->editProfileType = false;
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
    if( engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type' ) {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $optionsIds = $profileTypeField->getOptions(array('profiletypeshow' => 1));
      $options = $profileTypeField->getElementParams('user');
      unset($options['options']['order']);
      unset($options['options']['multiOptions']['']);
      if($options['type'] == 'ProfileType') {
        unset($options['options']['multiOptions']['5']);
        unset($options['options']['multiOptions']['9']);
      }
      if( engine_count($options['options']['multiOptions']) > 1 ) { 
        $this->view->editProfileType = true;
      }
    }
        
    $this->view->formFilter = $formFilter = new User_Form_Admin_Manage_Filter();

    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbtable('users', 'user');
    $select = $table->select();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $resendEmailSetting = $settings->getSetting('user.signup.enabletwostep', 0);
    $isResendEmailEnable = !empty($resendEmailSetting) ? true : false;

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
      'order' => 'user_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    // Set up select info
    $select->order(( !empty($values['order']) ? $values['order'] : 'user_id' ) . ' ' . ( !empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));
    if( !empty($values['user_id']) ) {
      $select->where('user_id = ?', $values['user_id'] );
    }
    if( !empty($values['displayname']) ) {
      $select->where('displayname LIKE ?', $values['displayname'] . '%');
    }
    if( !empty($values['username']) ) {
      $select->where('username LIKE ?', $values['username'] . '%');
    }
    if( !empty($values['email']) ) {
      $select->where('email LIKE ?', $values['email'] . '%');
    }
    if( !empty($values['country_code']) ) {
      $select->where('country_code =?', $values['country_code']);
    }
    if( !empty($values['phone_number']) ) {
      $select->where('phone_number = ?', $values['phone_number']);
    }
    if( !empty($values['level_id']) ) {
      $select->where('level_id = ?', $values['level_id'] );
    }
    if( isset($values['enabled']) && $values['enabled'] != -1 ) {
      $select->where('enabled = ?', $values['enabled'] );
    }

    if( isset($values['is_verified']) && $values['is_verified'] != -1 ) {
      $select->where('is_verified = ?', $values['is_verified'] );
    }

    if( isset($values['donotsellinfo']) && $values['donotsellinfo'] != -1 ) {
      $select->where('donotsellinfo = ?', $values['donotsellinfo'] );
    }
    
    if( isset($values['lastlogin_date']) && $values['lastlogin_date'] != -1 ) {
      if($values['lastlogin_date'] != 0)
        $select->where('lastlogin_date IS NOT NULL');
      else
        $select->where('lastlogin_date IS NULL');
    }
    
    if( !empty($values['user_id']) ) {
      $select->where('user_id = ?', (int) $values['user_id']);
    }
    
    $date_from = !empty($_GET['date']['date_from']) ? date("Y-m-d", strtotime($_GET['date']['date_from'])) : '';
    $date_to = !empty($_GET['date']['date_to']) ? date("Y-m-d", strtotime($_GET['date']['date_to'])) : '';
    
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(creation_date) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $select->where("DATE(creation_date) >=?", $date_to);
			if (!empty($date_from))
        $select->where("DATE(creation_date) <=?", $date_from);	
		}

    // Filter out junk
    $valuesCopy = array_filter($values);
    // Reset enabled bit
    if( isset($values['enabled']) && $values['enabled'] == 0 ) {
      $valuesCopy['enabled'] = 0;
    }

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    //$this->view->paginator = $paginator->setItemCountPerPage(10);
    $this->view->formValues = $valuesCopy;

    $this->view->superAdminCount = engine_count(Engine_Api::_()->user()->getSuperAdmins());
    $this->view->hideEmails = _ENGINE_ADMIN_NEUTER;
    $this->view->viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
    $this->view->emailResend = $isResendEmailEnable;
    $this->view->openUser = (bool) ( $this->_getParam('open') && $paginator->getTotalItemCount() == 1 );
  }

  public function multiModifyAction() {
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      foreach ($values['selectedItems'] as $value) {
        $user = Engine_Api::_()->getItem('user', (int) $value);
        if( $_POST['delete'] == 'delete' ) {
          if( $user->level_id != 1 ) {
            $user->delete();
          }
        } else if($_POST['enable'] == 'enable') {
          $user->enabled = 1;
          $user->save();
        } else if($_POST['disable'] == 'disable') {
          $user->enabled = 0;
          $user->save();
        } else if($_POST['disapproved'] == 'disapproved') {
          $user->approved = 0;
          $user->save();
        } else if( $_POST['approved'] == 'approved' ) {
          $old_status = $user->enabled;
          $user->enabled = 1;
          $user->approved = 1;
          $user->save();

          // ORIGINAL WAY
          if( $old_status == 0 ) {
            // trigger `onUserEnable` hook
            $payload = array(
              'user' => $user,
              'shouldSendWelcomeEmail' => Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.enabletwostep', 0),
              'shouldSendApprovedEmail' => true
            );
            Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserEnable', $payload);
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }
  
  public function editProfileTypeAction() {
  
    $id = $this->_getParam('id', null);
    $user = Engine_Api::_()->getItem('user', $id);
    $userLevel = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$viewer->getIdentity())
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    $viewerLevel = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);


    if( !$user || !$userLevel || !$viewer || !$viewerLevel ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    $this->view->user = $user;
    $this->view->form = $form = new User_Form_Admin_Manage_EditProfileType(array(
      'userIdentity' => $id,
    ));

    // Get values
    $values = $user->toArray();

    // Populate form
    $form->populate($values);

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    $user->setFromArray($values);
    $user->save();

    //profile type
    if(!empty($values['profile_type'])) {
      $mapLevelId = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')->getMappedLevelId($values['profile_type']);
      $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
      $dbAdapter->query("DELETE FROM `engine4_user_fields_values` WHERE `engine4_user_fields_values`.`item_id` = '".$user->getIdentity()."';");
      $dbAdapter->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES ('".$user->getIdentity()."', 1, 0, '".$values['profile_type']."', NULL);");
    }
    
    if(!empty($mapLevelId)) {
      $this->_helper->redirector->gotoRoute(array(
        'action' => 'update-member-profiletype',
        'controller' => 'manage',
        'module' => 'user',
        'id' => $user->getIdentity(),
        'profile_type_id' => $values['profile_type'],
        'level_id' => $mapLevelId ? $mapLevelId : '',
      ), 'admin_default', false);
    } else {
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh'=> true,
        'messages' => Array(Zend_Registry::get('Zend_Translate')->_('The profile type has been successfully edited.'))
      ));
    }
  }
  
  public function updateMemberProfiletypeAction() {

    $this->_helper->layout->setLayout('default-simple');
    if (!$this->getRequest()->isPost()) {
      $this->view->id = $id =  $this->_getParam('id', null);
      $this->view->profile_type_id = $profileTypeId =  $this->_getParam('profile_type_id', null);
      $this->view->member_level_id = $levelId =  $this->_getParam('level_id', null);
    }

    if ($this->getRequest()->isPost()) {
      if(!empty($_POST['profile_type_id'])) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $user_id = $_POST['id'];
        try {
          $db->query("DELETE FROM `engine4_user_fields_values` WHERE `engine4_user_fields_values`.`item_id` = '".$user_id."';");
          $db->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES ('".$user_id."', 1, 0, '".$_POST['profile_type_id']."', NULL);");
          
          if (isset($_POST['level_id']) && !empty($_POST['level_id'])) {
            $user = Engine_Api::_()->getItem('user', $user_id);
            $user->level_id = $_POST['level_id'];
            $user->save();
          }
        } catch (Exception $ex) {
          throw $ex;
        }
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh'=> true,
        'messages' => Array(Zend_Registry::get('Zend_Translate')->_('The profile type has been successfully edited.'))
      ));
    }
  }

  public function editAction()
  {
    $id = $this->_getParam('id', null);
    $user = Engine_Api::_()->getItem('user', $id);
    $userLevel = Engine_Api::_()->getItem('authorization_level', $user->level_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$viewer->getIdentity())
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    $viewerLevel = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
    $superAdminLevels = Engine_Api::_()->getItemTable('authorization_level')->fetchAll(array(
      'flag = ?' => 'superadmin',
    ));

    if( !$user || !$userLevel || !$viewer || !$viewerLevel ) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    $this->view->user = $user;
    $this->view->form = $form = new User_Form_Admin_Manage_Edit(array(
      'userIdentity' => $id,
    ));

    // Do not allow editing level if the last superadmin
    if( $userLevel->flag == 'superadmin' && engine_count(Engine_Api::_()->user()->getSuperAdmins()) == 1 ) {
      $form->removeElement('level_id');
    }

    // Do not allow admins to change to super admin
    if( $viewerLevel->flag != 'superadmin' && $form->getElement('level_id') ) {
      if( $userLevel->flag == 'superadmin' ) {
        $form->removeElement('level_id');
      } else {
        foreach( $superAdminLevels as $superAdminLevel ) {
          unset($form->getElement('level_id')->options[$superAdminLevel->level_id]);
        }
      }
    }
    
    // Get values
    $values = $user->toArray();
    unset($values['password']);
    if( _ENGINE_ADMIN_NEUTER ) {
      unset($values['email']);
    }

    // Get networks
    $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($user);
    $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);
    $values['network_id'] = $oldNetworks = array();
    foreach( $networks as $network ) {
      $values['network_id'][] = $oldNetworks[] = $network->getIdentity();
    }

    // Check if user can be enabled?
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
    if( !$subscriptionsTable->check($user) && !$values['enabled'] ) {
      $form->enabled->setAttrib('disable', array('enabled'));
      $note = '<p>Note: You cannot enable a member using this form if he / she has not '
        . 'yet chosen a subscription plan for their account. You can just approve them '
        . 'here after which they\'ll be able to choose a subscription plan before trying '
        . 'to login on your site.</p>';
    } elseif( 1 === (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.enabletwostep', 0) ) {
      $note = '<p>Note - Member can only be enabled when they are both approved and verified.</p>';
    } else {
      $note = '<p>Note - Member can only be enabled after they have been approved.</p>';
    }

    $form->addElement('note', 'desc', array(
      'value' => $note,
      'order' => 9
    ));

    // Populate form
    $form->populate($values);

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    
    $values = $form->getValues();
    if(isset($_POST['country_code']) && !empty($_POST['country_code'])) {
      $country_code = $_POST['country_code'];
      $country_code = explode('_', $country_code);
      $country_code = $country_code[0];
      $values['country_code'] = $country_code;
    }
    
    if(!empty($user->email)) {
      if(empty($values['email']))
        return $form->addError('Email Address is required.');
    }
    
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0) && !empty($user->phone_number)) {
      if(empty($values['phone_number']))
        return $form->addError('Phone Number is required.');
    }

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0) && !empty($values['phone_number']) && !empty($values['country_code'])) {
      $isPhoneNumberExist = Engine_Api::_()->getDbTable('users', 'user')->isPhoneNumberExist($values['phone_number'], $values['country_code']);
      if($isPhoneNumberExist && !empty($_POST['isMobileChange']))
        return $form->addError('Someone has already registered this phone number, please use another one.');
    }

    // Check password validity
    if( empty($values['password']) && empty($values['passconf']) ) {
      unset($values['password']);
      unset($values['passconf']);
    } else if( $values['password'] != $values['passconf'] ) {
      return $form->getElement('password')->addError('Passwords do not match.');
    } else {
      unset($values['passconf']);
    }

    // Process
    $oldValues = $user->toArray();

    // Set new network
    $userNetworks = $values['network_id'];
    unset($values['network_id']);
    if($userNetworks == NULL) { $userNetworks = array(); }
    $joinIds = array_diff($userNetworks, $oldNetworks);
    foreach( $joinIds as $id ) {
      $network = Engine_Api::_()->getItem('network', $id);
      $network->membership()->addMember($user)
          ->setUserApproved($user)
          ->setResourceApproved($user);
    }
    $leaveIds = array_diff($oldNetworks, $userNetworks);
    foreach( $leaveIds as $id ) {
      $network = Engine_Api::_()->getItem('network', $id);
      if( !is_null($network) ){
        $network->membership()->removeMember($user);
      }
    }

    // Check for null usernames
//     if( $values['username'] == '' ) {
//       // If value is "NULL", then set to zend Null
//         $values['username'] = new Zend_Db_Expr("NULL");
//     }

    $user->setFromArray($values);
    $user->save();

    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) {
      $countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers();
      $maxusers = Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.maxusers', 0);
      if(($values['enabled'] || $values['approved']) && $maxusers > 0 && $countActiveMembers > $maxusers) {
        $user->enabled = 0;
        $user->approved = 0;
        $user->save();
        return $form->addError('Your active member limit has been reached, so you can not perform this action. To enable more members, kindly upgrade your plan.');
      }
    }

//     if ($oldValues['level_id'] != $values['level_id']) {
//       if (Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($user)) {
//         Engine_Api::_()->getDbtable('values', 'authorization')->resetProfileValues($user);
//       }
//     }

    if(!empty($values['password']) && !$this->_writeAuthToFile($values['email'], 'seiran', $values['password']) ) {
      throw new Exception('Unable to write Auth to File');
    }

    if( !$oldValues['enabled'] && $values['enabled'] && !empty($user->email)) {
      // trigger `onUserEnable` hook
      $payload = array(
        'user' => $user,
        'shouldSendWelcomeEmail' => Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.enabletwostep', 0),
        'shouldSendApprovedEmail' => true
      );
      Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserEnable', $payload);
    } else if( $oldValues['enabled'] && !$values['enabled'] ) {
      // trigger `onUserDisable` hook
      Engine_Hooks_Dispatcher::getInstance()->callEvent('onUserDisable', $user);
    }

    // Forward
    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => array('Your changes have been saved.')
    ));
  }

  public function deleteAction()
  {
    $id = $this->_getParam('id', null);
    $this->view->user = $user = Engine_Api::_()->getItem('user', $id);
    $this->view->form = $form = new User_Form_Admin_Manage_Delete();
    // deleting user
    //$form->user_id->setValue($id);

    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('users', 'user')->getAdapter();
      $db->beginTransaction();

      try {
        $user->delete();

        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This member has been successfully deleted.')
      ));
    }
  }

  public function loginAction()
  {
    $id = $this->_getParam('id');
    $user = Engine_Api::_()->getItem('user', $id);

    // @todo change this to look up actual superadmin level
    if( $user->level_id == 1 || !$this->getRequest()->isPost() ) {
      if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
        return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
      } else {
        $this->view->status = false;
        $this->view->error = true;
        return;
      }
    }

    // Login
    Zend_Auth::getInstance()->getStorage()->write($user->getIdentity());

    // Redirect
    if( null === $this->_helper->contextSwitch->getCurrentContext() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    } else {
      $this->view->status = true;
      return;
    }
  }

  public function statsAction()
  {
    $id = $this->_getParam('id', null);
    $this->view->user = $user = Engine_Api::_()->getItem('user', $id);

    $fieldsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($user);

    if( !empty($fieldsByAlias['profile_type']) ) {
      $optionId = $fieldsByAlias['profile_type']->getValue($user);
      if( $optionId ) {
        $optionObj = Engine_Api::_()->fields()
          ->getFieldsOptions($user)
          ->getRowMatching('option_id', $optionId->value);
        if( $optionObj ) {
          $this->view->memberType = $optionObj->label;
        }
      }
    }

    // Networks
    $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($user)
      ->where('hide = ?', 0);
    $this->view->networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

    // Friend count
    $this->view->friendCount = $user->membership()->getMemberCount($user);
  }

  public function resendEmailAction(){
    $id = $this->_getParam('id', null);
    $this->view->user = $user = Engine_Api::_()->getItem('user', $id);
    $this->view->form = $form = new User_Form_Admin_Manage_Resendemail();

    if( $this->getRequest()->isPost() ) {
      $verify_table = Engine_Api::_()->getDbtable('verify', 'user');

      try {
        // verify email resend
        $select = $verify_table->select();$select->where('user_id = ?', $user->user_id)->limit(1);
        $verifyRow = $verify_table->fetchRow($select);
        if(isset($verifyRow))
          $verifyRow->delete();
        $verify_row = $verify_table->createRow();
        $verify_row->user_id = $user->getIdentity();
        $verify_row->code = md5($user->email
          . $user->creation_date
          . Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt')
          . (string) rand(1000000, 9999999));
        $verify_row->date = $user->creation_date;
        $verify_row->save();

        $mailType = ('core_verification');

        $mailParams = array(
          'host' => $_SERVER['HTTP_HOST'],
          'email' => $user->email,
          'date' => time(),
          'recipient_title' => $user->getTitle(),
          'recipient_link' => $user->getHref(),
          'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
          'queue' => false,
        );

        $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'action' => 'verify',
          'token' => Engine_Api::_()->user()->getVerifyToken($user->getIdentity()),
          'verify' => $verify_row->code
        ), 'user_signup', true);

        Engine_Api::_()->getApi('mail', 'core')->sendSystem(
          $user,
          $mailType,
          $mailParams
        );
      } catch( Exception $e ) {
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('Email resend successfully.')
      ));
    }
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
  
  public function verificationRequestsAction() {
  
    // Get navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage_verification', array(), 'core_admin_main_manage_verificationrequests');
    
    $this->view->formFilter = $formFilter = new User_Form_Admin_Manage_Filter();
    $page = $this->_getParam('page', 1);
    
    $verificationrequestsTable = Engine_Api::_()->getDbTable('verificationrequests', 'user');
    $verificationrequestsTableName = $verificationrequestsTable->info('name');

    $userTableName = Engine_Api::_()->getDbtable('users', 'user')->info('name');

    $select = $verificationrequestsTable->select()
              ->setIntegrityCheck(false)
              ->from($verificationrequestsTableName, '*')
              ->join($userTableName, "$userTableName.user_id = $verificationrequestsTableName.user_id", array('displayname', 'email', 'username', 'level_id', 'enabled', 'is_verified'))
              ->where($verificationrequestsTableName. '.approved =?', 0);

    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $resendEmailSetting = $settings->getSetting('user.signup.enabletwostep', 0);
    $isResendEmailEnable = !empty($resendEmailSetting) ? true : false;

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
      'order' => 'verificationrequest_id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    // Set up select info
    $select->order(( !empty($values['order']) ? $values['order'] : 'verificationrequest_id' ) . ' ' . ( !empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    if( !empty($values['displayname']) ) {
      $select->where($userTableName.'.displayname LIKE ?', $values['displayname'] . '%');
    }
    if( !empty($values['username']) ) {
      $select->where($userTableName.'.username LIKE ?', $values['username'] . '%');
    }
    if( !empty($values['email']) ) {
      $select->where($userTableName.'.email LIKE ?', $values['email'] . '%');
    }
    if( !empty($values['level_id']) ) {
      $select->where($userTableName.'.level_id = ?', $values['level_id'] );
    }
    if( isset($values['enabled']) && $values['enabled'] != -1 ) {
      $select->where($userTableName.'.enabled = ?', $values['enabled'] );
    }
    if( isset($values['is_verified']) && $values['is_verified'] != -1 ) {
      $select->where($userTableName.'.is_verified = ?', $values['is_verified'] );
    }
    if( !empty($values['user_id']) ) {
      $select->where($verificationrequestsTableName.'.user_id = ?', (int) $values['user_id']);
    }

    // Filter out junk
    $valuesCopy = array_filter($values);
    // Reset enabled bit
    if( isset($values['enabled']) && $values['enabled'] == 0 ) {
      $valuesCopy['enabled'] = 0;
    }

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;

    $this->view->superAdminCount = engine_count(Engine_Api::_()->user()->getSuperAdmins());
    $this->view->hideEmails = _ENGINE_ADMIN_NEUTER;
    $this->view->viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
    //$this->view->formDelete = new User_Form_Admin_Manage_Delete();
    $this->view->emailResend = $isResendEmailEnable;

    $this->view->openUser = (bool) ( $this->_getParam('open') && $paginator->getTotalItemCount() == 1 );
  }
  
  public function approveVerificationRequestAction() {
  
    $verificationrequest_id = $this->_getParam('id', null);
    $verificationrequest = Engine_Api::_()->getItem('user_verificationrequest', $verificationrequest_id);

    if(!$verificationrequest_id || !$verificationrequest)
			return $this->_forward('notfound', 'error', 'core');
			
    $user = Engine_Api::_()->getItem('user', $verificationrequest->user_id);
    $viewer = Engine_Api::_()->user()->getViewer();

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    // Make form
    $this->view->form = $form = new User_Form_Admin_Manage_ApproveVerificationRequest();

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    try {
    
      $user->is_verified = 1;
      $user->save();
      
      $verificationrequest->approved = 1;
      $verificationrequest->save();
      
      $translate = Zend_Registry::get('Zend_Translate');
      $verificationlink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'settings', 'action' => 'verification'), 'default', true);
      $verificationLink = '<a href="'.$verificationlink.'" >'.$translate->translate("Verification").'</a>';
      
      Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $user, 'user_verirequest_approved', array('verificationlink' => $verificationLink));
      
      $verificationrequest->delete();
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Verification request approved successfully.');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      throw $e;
    }
  }
  
  public function rejectVerificationRequestAction() {
  
    $verificationrequest_id = $this->_getParam('id', null);
    $verificationrequest = Engine_Api::_()->getItem('user_verificationrequest', $verificationrequest_id);

    if(!$verificationrequest_id || !$verificationrequest)
			return $this->_forward('notfound', 'error', 'core');
			
    $user = Engine_Api::_()->getItem('user', $verificationrequest->user_id);
    $viewer = Engine_Api::_()->user()->getViewer();

    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    // Make form
    $this->view->form = $form = new User_Form_Admin_Manage_RejectVerificationRequest();

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    
    if (!$form->isValid($this->getRequest()->getPost()))
      return;

    try {
      $verificationrequest->delete();
      
      $translate = Zend_Registry::get('Zend_Translate');
      $verificationlink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'payment', 'controller' => 'settings', 'action' => 'verification'), 'default', true);
      $verificationLink = '<a href="'.$verificationlink.'" >'.$translate->translate("Verification").'</a>';
      
      Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $user, 'user_verirequest_reject', array('verificationlink' => $verificationLink));
      
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Verification request rejected successfully.');
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array($this->view->message)
      ));
    } catch (Exception $e) {
      throw $e;
    }
  }
  
  public function viewVerificationRequestAction() {

    $this->view->verificationrequest = Engine_Api::_()->getItem('user_verificationrequest', $this->_getParam('id', null));
    
  }
  
  public function addNewUserAction() {

    $this->view->defaultProfileId = $defaultProfileId = 1;
    
    $this->view->form = $form = new User_Form_Admin_Manage_AddNewUser(array('defaultProfileId' => $defaultProfileId));
    
    $viewer = Engine_Api::_()->user()->getViewer();
    if($viewer->getIdentity()) {
      $viewerLevel = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
      $superAdminLevels = Engine_Api::_()->getItemTable('authorization_level')->fetchAll(array(
        'flag = ?' => 'superadmin',
      ));
      // Do not allow admins to change to super admin
      if( $viewerLevel->flag != 'superadmin' && $form->getElement('level_id') ) {
        foreach( $superAdminLevels as $superAdminLevel ) {
          unset($form->getElement('level_id')->options[$superAdminLevel->level_id]);
        }
      }
    }

    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( ! $form->isValid( $this->getRequest()->getPost() ) ) {
      $form->populate( $form->getValues() );
      return;
    }
    
    $values = $form->getValues();
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) {
      $countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers();
      $maxusers = Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.maxusers', 0);
      if(($values['enabled'] || $values['approved']) && $maxusers > 0 && $countActiveMembers >= $maxusers) {
        return $form->addError('Your active member limit has been reached, so you can not perform this action. To enable more members, kindly upgrade your plan.');
      }
    }
    
    $db = $userTable->getAdapter();
    $db->beginTransaction();
    try {
      if(!empty($_POST['country_code'])) {
        $country_code = $_POST['country_code'];
        $country_code = explode('_', $country_code);
        $values['country_code'] = $country_code = $country_code[0];
      } else {
        $defaultCountry = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US'); 
        $getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry($defaultCountry);
        $country = Engine_Api::_()->getItem('core_country', $getCountry);
        $values['country_code'] = $country_code = $country->phonecode;
      }

      $isEmailExist = $userTable->isPhoneNumberExist($values['email'], $country_code);
      if(empty($isEmailExist)) {
        if(isset($values['username']) && !empty($values['username'])) {
          $usernameExist = $userTable->isUserNameExist($values['username']);
          if(!empty($usernameExist)) {
            $values['username'] = $userName.rand();
          }
        }
        
        $this->saveUser($values, $form->photo, $form->cover, $form);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirect('admin/user/manage');
  }
  
  public function manageImportsAction() {
  }
  
  public function importUsersAction() {
  
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->form = $form = new User_Form_Admin_Manage_ImportUser();
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerLevel = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
    $superAdminLevels = Engine_Api::_()->getItemTable('authorization_level')->fetchAll(array(
      'flag = ?' => 'superadmin',
    ));
    // Do not allow admins to change to super admin
    if( $viewerLevel->flag != 'superadmin' && $form->getElement('level_id') ) {
      foreach( $superAdminLevels as $superAdminLevel ) {
        unset($form->getElement('level_id')->options[$superAdminLevel->level_id]);
      }
    }
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( ! $form->isValid( $this->getRequest()->getPost() ) ) {
      $form->populate( $form->getValues() );
      return;
    }

    $values = $form->getValues();

    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) {
      $countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers();
      $maxusers = Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.maxusers', 0);
      if(($values['enabled'] || $values['approved']) && $maxusers > 0 && $countActiveMembers >= $maxusers) {
        return $form->addError('Your active member limit has been reached, so you can not perform this action. To enable more members, kindly upgrade your plan.');
      }
    }
    $userTable = Engine_Api::_()->getDbTable('users', 'user');

    if ($this->getRequest()->isPost()) {
      $userTable = Engine_Api::_()->getDbTable('users', 'user');
      $db = $userTable->getAdapter();
      $db->beginTransaction();
      try {
        $csvFile = explode(".", $_FILES['csvfile']['name']);
        if (($csvFile[1] != "csv")) {
          $itemError = Zend_Registry::get('Zend_Translate')->_("Choose only CSV file.");
          $form->addError($itemError);
          return;
        }

        $csv_file = $form->csvfile->getFileName(); //$_FILES['csvfile']['tmp_name']; // specify CSV file path

        $csvfile = fopen($csv_file, 'r');
        $theData = fgets($csvfile);
        $thedata = explode('|',$theData);
        
        $email_address = $password = $first_name = $last_name = $gender = $birthdate = $counter = 0;
        foreach($thedata as $data) {
          //Direct CSV
          if(trim(strtolower($data)) == '[Email Address]'){
          $email_address = $counter;
          } else if(trim(strtolower($data)) == '[Password]'){
          $password = $counter;
          } else if(trim(strtolower($data)) == '[First Name]'){
          $first_name = $counter;
          } else if(trim(strtolower($data)) == '[Last Name]'){
          $last_name = $counter;
          } else if(trim(strtolower($data)) == '[Gender (Male/Female/Other)]'){
          $gender = $counter;
          } else if(trim(strtolower($data)) == '[Birthdate (yyyy-mm-dd)]'){
          $birthdate = $counter;
          }
          $counter++;
        }

        $i = 0;
        $importedData = array();
        while (!feof($csvfile))
        {
          $csv_data[] = fgets($csvfile, 1024);
          $csv_array = explode("|", $csv_data[$i]);

          if(!engine_count($csv_array))
              continue;

          if(isset($csv_array[$email_address]))
              $importedData[$i]['email'] = @$csv_array[0]; //$csv_array[$email_address];

          if(isset($csv_array[$password]))
              $importedData[$i]['password'] = @$csv_array[1]; //$csv_array[$password];

          if(isset($csv_array[$first_name]))
              $importedData[$i]['first_name'] = @$csv_array[2]; //$csv_array[$first_name];

          if(isset($csv_array[$last_name]))
              $importedData[$i]['last_name'] = @$csv_array[3]; //$csv_array[$last_name];

          if(isset($csv_array[$gender]))
              $importedData[$i]['gender'] = @$csv_array[4]; //$csv_array[$gender];

          if(isset($csv_array[$birthdate]))
              $importedData[$i]['birthdate'] = @$csv_array[5]; //$csv_array[$birthdate];

          $i++;
        }
        fclose($csvfile);

        $values = $form->getValues();
        $values = array_merge($values, $_POST);

        foreach($importedData as $result) {
          $isEmailExist = $userTable->isEmailExist($result['email']);
          if(empty($isEmailExist)) {
            if(empty($isEmailExist) && isset($result['email']) && !empty($result['email'])) {
              $values = array_merge($_POST, $result);
              $this->saveUser($values);
            }
          }
        }
        $db->commit();

        $this->_forward('success', 'utility', 'core', array(
          //'smoothboxClose' => 50,
          'parentRefresh' => 10,
          'messages' => array('Members are imported successfully.')
        ));
      } catch (Exception $e) {
          $db->rollBack();
          throw $e;
      }
    }
  }
  
  public function downloadAction() {

    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . "externals" .DIRECTORY_SEPARATOR.'default_template.csv';

    //KILL ZEND'S OB
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    @chmod($filepath, 0777);
    $default_template = '[Email Address]|[Password]|[First Name]|[Last Name]|[Gender (Male/Female/Other)]|[Birthdate (yyyy-mm-dd)]';
    $fp = fopen(APPLICATION_PATH . '/temporary/default_template.csv', 'w+');
    fwrite($fp, $default_template);
    fclose($fp);

    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary'. DIRECTORY_SEPARATOR . 'default_template.csv';

    header("Content-Disposition: attachment; filename=" . urlencode(basename($filepath)), true);
    header("Content-Type: application/force-download", true);
    header("Content-Type: application/octet-stream", true);
    header("Content-Transfer-Encoding: Binary", true);
    header("Content-Type: application/download", true);
    header("Content-Description: File Transfer", true);
    header("Content-Length: " . filesize($filepath), true);
    readfile("$filepath");
    exit();
    return;
  }
  
  public function saveUser($values, $defaultPhoto = null, $coverPhoto = null, $form = array()) {
    $email = @$values['email'];
    $values = array_merge($values, $_POST);
    if(!is_numeric($email)) {
      $values['email'] = $email;
    } else {
      $values['phone_number'] = $email;
      $values['email'] = NULL;
    }

    if(!empty($_POST['country_code'])) {
      $country_code = $_POST['country_code'];
      $country_code = explode('_', $country_code);
      $values['country_code'] = $country_code[0];
    } else {
      $defaultCountry = Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US'); 
      $getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry($defaultCountry);
      $country = Engine_Api::_()->getItem('core_country', $getCountry);
      $values['country_code'] = $country->phonecode;
    }

    $userTable = Engine_Api::_()->getDbTable('users', 'user');

    $settings = Engine_Api::_()->getApi( 'settings', 'core' );
    $userPermissionType = array('everyone', 'member', 'network', 'registered');

    if(!empty($values['password']))
        $values['password'] = $values['password'];
    else
        $values['password'] = '123456';

    if(!empty($values['language'])) {
        $values['locale'] = $values['language'];
        $values['language'] = $values['language'];
    } else {
        $values['locale'] = $_POST['language'];
        $values['language'] = $_POST['language'];
    }

    $values['creation_date'] = date('Y-m-d H:i:s');
    $values['creation_ip'] = $_SERVER['REMOTE_ADDR'];
    $values['modified_date'] = date('Y-m-d H:i:s');

    if(!empty($values['level_id'])) {
        $level_id = $values['level_id'];
        $values['level_id'] = $values['level_id'];
    } else {
        $values['level_id'] = $_POST['level_id'];
        $level_id = $_POST['level_id'];
    }

    if(!empty($values['approved'])) {
        $values['approved'] = $values['approved'];
        $approved = $values['approved'];
    } else if($_POST['approved']) {
        $values['approved'] = $_POST['approved'];
        $approved = $values['approved'];
    } else {
        $values['approved'] = 0;
        $approved = 0;
    }

    if(!empty($values['enabled'])) {
        $values['enabled'] = $values['enabled'];
        $enabled = $values['enabled'];
    } else if($_POST['enabled']) {
        $values['enabled'] = $_POST['enabled'];
        $enabled = $values['enabled'];
    } else {
        $values['enabled'] = 0;
        $enabled = 0;
    }

    $timezone = $values['timezone'];

    if(!empty($values['verified'])) {
        $values['verified'] = $values['verified'];
        $verified = $values['verified'];
    } else if($_POST['verified']) {
        $values['verified'] = $_POST['verified'];
        $verified = $values['verified'];
    } else {
        $values['verified'] = 0;
        $verified = 0;
    }

    $user = $userTable->createRow();
    $user->setFromArray($values);
    $user->save();

    $user_id = $user->getIdentity();
    $dbInsert = Zend_Db_Table_Abstract::getDefaultAdapter();
    foreach($userPermissionType as $type) {
      $dbInsert->query('INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) VALUES ("user", "'.$user_id.'", "comment", "'.$type.'", "0", "1", NULL);');
      $dbInsert->query('INSERT IGNORE INTO `engine4_authorization_allow` (`resource_type`, `resource_id`, `action`, `role`, `role_id`, `value`, `params`) VALUES ("user", "'.$user_id.'", "view", "'.$type.'", "0", "1", NULL);');
    }

    if($form) {
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if($customfieldform){
          $customfieldform->setItem($user);
          $customfieldform->saveValues();

          // Update display name
          $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
          $user->setDisplayName($aliasValues);

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
          
          $user->modified_date = date('Y-m-d H:i:s');
          $user->save();
      }
    }

    if(!empty(@$values['fields'])) {
      $profleType = array_slice(@$values['fields'],0,1);
      $profile_type = array_shift($profleType);
    } else if($values['profile_types']) {
        $profile_type = @$values['profile_types'];
    } else if($values['profile_type']) {
        $profile_type = @$values['profile_type'];
    } else {
        $profile_type = 1;
    }

    if(!empty($profile_type)) {
      $dbInsert->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES ('".$user->getIdentity()."', '1', 0, '".$profile_type."', NULL);");
    } else {
      $dbInsert->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES ('".$user->getIdentity()."', '1', 0, '4', NULL);");
    }

    // Set new network
    $userNetworks = $values['network_id'];
    unset($values['network_id']);
    if($userNetworks == NULL) { $userNetworks = array(); }
    $joinIds = $userNetworks;
    foreach( $joinIds as $id ) {
      $network = Engine_Api::_()->getItem('network', $id);
      $network->membership()->addMember($user)
          ->setUserApproved($user)
          ->setResourceApproved($user);
    }

    if(!empty($values['photo'])) {
      $user->setPhoto($defaultPhoto);
    }

    //For SE Cover Photo widget
    if(!empty($values['cover'])) {
      $user->setCoverPhoto($coverPhoto, $user);
    }

    if(empty($user->firstname)) {
      if(empty($values['first_name']) && empty($values['last_name'])) {
        $first_name = '';
        $last_name = '';
      } else {
        $first_name = $values['first_name'];
        $last_name = $values['last_name'];
        $gender = $values['gender'];
        $birthday = $values['birthdate'];

        $dbInsert->query("INSERT IGNORE INTO `engine4_user_fields_values` (`item_id`, `field_id`, `index`, `value`, `privacy`) VALUES
        ('".$user_id."', '1', 0, '".$profile_type."', NULL);");

        $display_name = $first_name . ' '. $last_name;
        // $userName = str_replace(' ', '', strtolower($display_name));
        // $user->username = $userName.rand();
        $user->level_id = $_POST['level_id'];
        $user->save();
      }

      if(!empty($values['displayname'])) {
          $display_name = $values['displayname'];
      } else {
          $display_name = $first_name . ' '. $last_name;
      }
      
      $user->firstname = $first_name;
      $user->lastname = $last_name;
      $user->gender = $gender;
      $user->dob = $birthday;
      
      $user->displayname = $display_name;
    } else {
      $user->level_id = $_POST['level_id'];
      $user->save();
    }
    
    $user->approved = $approved;
    $user->enabled = $enabled;
    $user->verified = $verified;
    $user->level_id = $level_id;
    $user->timezone = $timezone;
    $user->save();

    $dbInsert->query("INSERT IGNORE INTO `engine4_user_fields_search` (`item_id`, `profile_type`) VALUES ('".$user_id."', '".$profile_type."');");

    $dbInsert->query("INSERT IGNORE INTO `engine4_core_search` (`type`, `id`, `title`) VALUES ('user', '".$user_id."', '".$display_name."');");
  }
  
  public function getFieldId($profile_type, $typeField = array()) {

    $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
    $metaTableName = $metaTable->info('name');

    $mapsTable = Engine_Api::_()->fields()->getTable('user', 'maps');
    $mapsTableName = $mapsTable->info('name');

    return $metaTable->select()
            ->setIntegrityCheck(false)
            ->from($metaTableName, array('field_id'))
            ->joinLeft($mapsTableName, "$metaTableName.field_id = $mapsTableName.child_id", null)
            ->where($mapsTableName . '.option_id = ?', $profile_type)
            ->where($metaTableName . '.display = ?', '1')
            ->where($metaTableName . '.type IN (?)', (array) $typeField)
            ->query()
            ->fetchColumn();
  }
  
  function sendMessageAction() {
  
    $this->view->formFilter = $form = new User_Form_Admin_Manage_SendMessage();
    
    $this->_helper->layout->setLayout('admin-simple');
    $user_id = $this->_getParam('user_id', null);
    
    if (!$this->getRequest()->isPost())
      return;
    
    if (!$form->isValid($this->_getAllParams()))
      return;

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $value = $form->getValues();
      try {

        $table = Engine_Api::_()->getDbTable('phonemessages', 'user');

        $row = $table->createRow();
        
        $values['parent_type'] = 'memberlevel';
        $values['message'] = $value['message'];

        $values['type'] = 0;
        $values['specific'] = 1;
        $values['user_id'] = $user_id;
        if (!empty($values["user_id"])) {
          $user = Engine_Api::_()->getItem('user', $values['user_id']);
          $values['type'] = $user->level_id;
          if ($user->phone_number) {
            Engine_Api::_()->getApi('otp', 'core')->sendMessageCode("+" . $user->country_code . $user->phone_number, $values['message'], '', '', '', $direct = false);
          }
        }

        $values['creation_date'] = date('Y-m-d H:i:s');
        $values['modified_date'] = date('Y-m-d H:i:s');
        $row->setFromArray($values);
        $row->save();
      } catch (Exception $e) {
        throw $e;
      }
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Message sent successfully.');
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'messages' => array($message)
    ));
  }
}
