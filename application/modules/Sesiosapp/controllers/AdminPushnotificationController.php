<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminPushnotificationController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesiosapp_AdminPushnotificationController extends Core_Controller_Action_Admin {
 public function manageAction() {
   $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main', array(), 'sesiosapp_admin_main_pushnoti');
   
   $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main_pushnoti', array(), 'sesiosapp_admin_main_managepushnoti');
    
    $this->view->noti = Engine_Api::_()->getDbTable('pushnotifications','sesiosapp')->getNotifications();
 }
 
 public function resendAction(){
   $id = $this->_getParam('id','');
   $item = Engine_Api::_()->getItem('sesiosapp_pushnotifications',$id);
    $this->view->form = $form = new Sesiosapp_Form_Admin_Notification();  
    $form->populate($item->toArray());  
      if ($this->getRequest()->isPost() && $form->isValid($_POST)) {
        $values = $form->getValues();
        $title = addslashes($values['title']);
        $body = addslashes($values['description']);
        
        $params = '';
        if($values['criteria'] == 'all'){
          $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('platform'=>1));
        }else if($values['criteria'] == 'memberlevel'){
          $params = $level = $values['memberlevel'];
           $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('level'=>$level,'platform'=>1));
        }else if($values['criteria'] == 'network'){
           $params = $network = $values['network'];
            $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('network'=>$network,'platform'=>1));
        }else if($values['criteria'] == 'user'){
            $params = $user_ids = $values['to'];
            $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('user_ids'=>$user_ids,'platform'=>1));
        }
        
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->query("INSERT INTO `engine4_sesiosapp_pushnotifications`(`title`, `description`, `criteria`, `param`, `sent`, `creation_date`) VALUES ('".$title."','".$body."','".$values['criteria']."','".$params."','1','".date('Y-m-d H:i:s')."')");
        //send notifications
        $data = array('title'=>strip_tags($title),'description'=>$body);
        $result = Engine_Api::_()->getApi('pushnoti','sesapi')->iOS($data,$tokens,array());
        $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('Push Notification Sent Successfully.')
        ));
      }     
  }
 
 public function createAction(){
    $this->view->form = $form = new Sesiosapp_Form_Admin_Notification();    
      if ($this->getRequest()->isPost() && $form->isValid($_POST)) {
        $values = $form->getValues();
        $title = addslashes($values['title']);
        $body = addslashes($values['description']);
        
        $params = '';
        if($values['criteria'] == 'all'){
          $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('platform'=>1));
        }else if($values['criteria'] == 'memberlevel'){
          $params = $level = $values['memberlevel'];
           $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('level'=>$level,'platform'=>1));
        }else if($values['criteria'] == 'network'){
           $params = $network = $values['network'];
            $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('network'=>$network,'platform'=>1));
        }else if($values['criteria'] == 'user'){
            $params = $user_ids = $values['to'];
            $tokens = Engine_Api::_()->getDbTable('users','sesapi')->getTokens(array('user_ids'=>$user_ids,'platform'=>1));
        }
        
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->query("INSERT INTO `engine4_sesiosapp_pushnotifications`(`title`, `description`, `criteria`, `param`, `sent`, `creation_date`) VALUES ('".$title."','".$body."','".$values['criteria']."','".$params."','1','".date('Y-m-d H:i:s')."')");
        //send notifications
        $data = array('title'=>strip_tags($title),'description'=>$body);
        $result = Engine_Api::_()->getApi('pushnoti','sesapi')->iOS($data,$tokens,array());
        $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('Push Notification Sent Successfully.')
        ));
      }     
  }
  public function deleteAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $id = $this->_getParam('id',false);
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Sesapi_Form_Delete();
    $form->setTitle('Delete Notification?');
    $form->setDescription('Are you sure that you want to delete this notification? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    $item = Engine_Api::_()->getItem('sesiosapp_pushnotifications',$id);
    if (!$item) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Notification doesn't exists to delete");
      return;
    }
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    $db = $item->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $item->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Notification has been deleted.');
    return $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh'=> 10,
                'messages' => Array($this->view->message)
    ));  
  }
  function settingsAction(){ 
  $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main', array(), 'sesiosapp_admin_main_pushnoti');
   
   $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sesiosapp_admin_main_pushnoti', array(), 'sesiosapp_admin_main_pushnotisettings');
            
            
    // Build the different notification types
    $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
    $notificationTypes = Engine_Api::_()->getDbtable('notificationTypes', 'sesiosapp')->getNotificationTypes();
    $notificationSettings = Engine_Api::_()->getDbtable('notificationTypes', 'sesiosapp')->getDefaultNotifications();
    
    $notificationTypesAssoc = array();
    $notificationSettingsAssoc = array();
    foreach( $notificationTypes as $type ) {
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

    ksort($notificationTypesAssoc);

    $notificationTypesAssoc = array_filter(array_merge(array(
      'general' => array(),
      'misc' => array(),
    ), $notificationTypesAssoc));

    // Make form
    $this->view->form = $form = new Engine_Form(array(
      'title' => 'Push Notification Settings',
      'description' => 'Select the actions for which you want to send Push Notifications in your app?',
    ));

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
      return;
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
    Engine_Api::_()->getDbtable('notificationTypes', 'sesiosapp')
        ->setDefaultNotifications($values);

    $form->addNotice('Your changes have been saved.');  
  }
}
