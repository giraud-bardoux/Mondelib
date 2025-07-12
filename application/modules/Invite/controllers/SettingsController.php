<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SettingsController.php 10123 2013-12-11 17:29:35Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Invite_SettingsController extends Core_Controller_Action_User
{
  public function init()
  {
    // Can specifiy custom id
    $id = $this->_getParam('id', null);
    $subject = null;
    if( null === $id ) {
      $subject = Engine_Api::_()->user()->getViewer();
      Engine_Api::_()->core()->setSubject($subject);
    } else {
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
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));
  }
  
  
  public function manageInvitesAction()
  {
    $user = Engine_Api::_()->core()->getSubject('user');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    if($settings->getSetting('invite.referralforsingup', 1)) {
      if (empty($user->referral_code)) {
        $referralCode = substr(md5(rand(0, 999) . $user->email), 10, 7);
        $user->referral_code = $referralCode;
        $user->save();
      }
      $this->view->referral_code = $user->referral_code;
      $this->view->referral = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'signup'), 'default', true) . '?referral_code=' . $user->referral_code;
    }

    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1)) {
      return $this->_forward('requireauth', 'error', 'core');
    } else {
      if($user->getIdentity()) {
        $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
        $levelsvalue = unserialize($levels);
        if(!engine_in_array($user->level_id, $levelsvalue))
          return $this->_forward('requireauth', 'error', 'core');
      }
    }

    // Make form
    $this->view->formFilter = $formFilter = new Invite_Form_Invite_Filter();

    // Process form
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $filterValues = $formFilter->getValues();
    } else {
      $filterValues = array();
    }
    $this->view->filterValues = $filterValues;

    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $tableName = $inviteTable->info('name');

    $tableUserName = Engine_Api::_()->getDbTable('users', 'user')->info('name');

    $select = $inviteTable->select()
              ->setIntegrityCheck(false)
              ->from($tableName);
    
    if( !empty($filterValues['country_code']) || !empty($filterValues['phone_number'])) {
      $select->join($tableUserName, "$tableName.new_user_id = $tableUserName.user_id", null);
    } else {
      $select->join($tableUserName, "$tableName.user_id = $tableUserName.user_id", null);
    }

    $select->where($tableName.'.user_id =?', $user->getIdentity())
          ->order($tableName.'.id DESC');

    if( !empty($filterValues['recipient']) ) {
      $select->where('recipient LIKE ?', $filterValues['recipient'] . '%');
    }

    if( !empty($filterValues['country_code']) ) {
      $select->where($tableUserName.'.country_code =?', $filterValues['country_code']);
    }

    if( !empty($filterValues['phone_number']) ) {
      $select->where($tableUserName.'.phone_number = ?', $filterValues['phone_number']);
    }

    if( !empty($filterValues['code']) ) {
      $select->where($tableName.'.code = ?', $filterValues['code']);
    }

    if( !empty($filterValues['id']) ) {
      $select->where($tableName.'.id = ?', (int) $filterValues['id']);
    }

    if( isset($filterValues['import_method']) && $filterValues['import_method'] != -1 ) {
      $select->where('import_method LIKE ?', $filterValues['import_method'] . '%');
    }

    $date_from = !empty($filterValues['date']['date_from']) ? date("Y-m-d", strtotime($filterValues['date']['date_from'])) : '';
    $date_to = !empty($filterValues['date']['date_to']) ? date("Y-m-d", strtotime($filterValues['date']['date_to'])) : '';
		if(!empty($date_to) && !empty($date_from)) {
			$select->where("DATE(timestamp) BETWEEN '".$date_from."' AND '".$date_to."'");
    } else {
			if (!empty($date_to))
        $select->where("DATE(timestamp) >=?", $date_to);
			if (!empty($date_from))
        $select->where("DATE(timestamp) <=?", $date_from);	
		}

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    $paginator->setItemCountPerPage(10);
  }
  
  public function deleteAction() {
  
    $id = $this->_getParam('invite_id', null);
    $invite = Engine_Api::_()->getItem('invite', (int) $id);
    $this->view->form = $form = new Invite_Form_Invite_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('invites', 'invite')->getAdapter();
      $db->beginTransaction();
      try {
        $invite->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This invite has been successfully deleted.')
      ));
    }
  }
}
