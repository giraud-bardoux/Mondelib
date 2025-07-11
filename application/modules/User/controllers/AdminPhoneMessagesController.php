<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminPhoneMessagesController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_AdminPhoneMessagesController extends Core_Controller_Action_Admin
{

  public function indexAction()
  {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_manage', array(), 'user_admin_phone_messages');

    $table = Engine_Api::_()->getDbTable('phonemessages', 'user');
    $tableName = $table->info('name');

    $usertable = Engine_Api::_()->getDbTable('users', 'user');
    $usertableName = $usertable->info('name');

    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');

    if (engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getElementParams('user');
      $this->view->profile_type = $options['options']['multiOptions'];
    }

    $this->view->formFilter = $form = new User_Form_Admin_Manage_FilterMessages();

    $page = $this->_getParam('page', 1);

    // Process form
    $values = array();
    $form->populate($this->_getAllParams());
    $values = $form->getValues();
    
    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    $this->view->assign($values);

    $select = $table->select()
                    ->from($tableName)
                    ->setIntegrityCheck(false)
                    ->joinLeft($usertableName, $usertableName . '.user_id = ' . $tableName . '.user_id', null)
                    ->order('creation_date DESC');

    if (!empty($values['username'])) {
      $select->where('displayname =?', $_GET['username']);
    }

    if (!empty($values['message'])) {
      $select->where("message LIKE ?", trim($_GET['message']) . '%');
    }

    if (!empty($values['interval'])) {
      switch ($values['interval']) {
        case 'today':
          $select->where("CAST($tableName.creation_date AS DATE)=?", date('Y-m-d'));
          break;
        case 'sevendays':
          $select->where("$tableName.creation_date >= DATE(NOW()) - INTERVAL 7 DAY");
          break;
        case 'specific':
          if (!empty($values['starttime'])) {
            $select->where("CAST($tableName.creation_date AS DATE) >=?", trim($values['starttime']));
          }
          if (!empty($values['endtime'])) {
            $select->where("CAST($tableName.creation_date AS DATE) <=?", trim($values['endtime']));
          }
          break;
      }
    }

    if (!empty($values['type'])) {
      if ($values['type'] == "memberlevel") {
        if (!empty($values['memberlevel'])) {
          $case = "CASE when parent_type = 'memberlevel' AND type = " . $values['memberlevel'] . " THEN true ELSE false END";
          $select->where($case);
        } else {
          $select->where('parent_type =?', 'memberlevel');
        }
      } else {
        if (!empty($values['profiletype'])) {
          $case = "CASE when parent_type = 'profiletype' AND type = " . $values['profiletype'] . " THEN true ELSE false END";
          $select->where($case);
        } else {
          $select->where('parent_type =?', 'profiletype');
        }
      }
    }

    
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator = $paginator->setCurrentPagenumber($page);
  }

  function sendMessageAction() {
  
    $this->view->formFilter = $form = new User_Form_Admin_Manage_SendMessages();
    
    $this->_helper->layout->setLayout('admin-simple');
    
    if (!$this->getRequest()->isPost())
      return;
      
    if (!$form->isValid($this->_getAllParams()))
      return;

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $value = $form->getValues();
      try {
      
        $table = Engine_Api::_()->getDbTable('phonemessages', 'user');

        $row = $table->createRow();
        $values['parent_type'] = $value['type'];
        $values['message'] = $value['message'];
        if ($values['parent_type'] != 'profiletype') {
          $values['type'] = $value['memberlevel'];
          $values['specific'] = $value['sendto'];
          //$values['specific'] = 0;
          if ($values['specific'] == "specific") {
            $values['specific'] = 1;
            $values['user_id'] = $value['user_id'];
            if (!empty($values["user_id"])) {
              $user = Engine_Api::_()->getItem('user', $values['user_id']);
              $values['type'] = $user->level_id;
              if ($user->phone_number)
                Engine_Api::_()->getApi('otp', 'core')->sendMessageCode("+" . $user->country_code . $user->phone_number, $values['message'], '', '', '', $direct = false);
            }
          } else {
            $tableName = Engine_Api::_()->getDbTable('users', 'user');
            $select = $tableName->select();
            if (!empty($_POST['memberlevel'])) {
              $select->where("level_id=?", $_POST['memberlevel']);
            }
            $users = $tableName->fetchAll($select);
            foreach ($users as $user) {
              if ($user->phone_number)
                Engine_Api::_()->getApi('otp', 'core')->sendMessageCode("+" . $user->country_code . $user->phone_number, $values['message'], '', '', '', $direct = false);
            }
          }
        } else {
          $values['type'] = $value['profiletype'];
          if (empty($values['type'])) {
            $tableName = Engine_Api::_()->getDbTable('users', 'user');
            $select = $tableName->select();
            $users = $tableName->fetchAll($select);
            foreach ($users as $user) {
              if ($user->phone_number)
                Engine_Api::_()->getApi('otp', 'core')->sendMessageCode("+" . $user->country_code . $user->phone_number, $values['message'], '', '', '', $direct = false);
            }
          } else {
            //fetch user of a specific profile id.
            $db = Engine_Db_Table::getDefaultAdapter();
            $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('user');
            if (engine_count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
              $profiletype = $topStructure[0]->getChild();
              $options = $profiletype->getOptions();
              $value = $options[0]['field_id'];
            }
            $users = $db->select()
              ->from('engine4_user_fields_values')
              ->where('value = ?', $values['type'])
              ->where('field_id = ?', $value)
              ->query()
              ->fetchAll();
            if (!empty($users)) {
              foreach ($users as $obj) {
                $user = Engine_Api::_()->getItem('user', $obj['item_id']);
                if ($user->phone_number)
                  Engine_Api::_()->getApi('otp', 'core')->sendMessageCode("+" . $user->country_code . $user->phone_number, $values['message'], '', '', '', $direct = false);
              }
            }
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
