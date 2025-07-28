<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminThemeController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */


class Sesandroidapp_AdminThemeController extends Core_Controller_Action_Admin {
  public function indexAction() {
    //create default theme and custom theme
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesandroidapp_admin_main', array(), 'sesandroidapp_admin_main_styling');    
    $this->view->customtheme_id = $this->_getParam('customtheme_id', null);
    $this->view->form = $form = new Sesandroidapp_Form_Admin_Styling();
    $isDefaultTheme = isset($_POST['theme_color']) && $_POST['theme_color'] == 5 ? true : (engine_count($_POST) == 0 ? true : false);
    if (($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) || !$isDefaultTheme ) {
      $values = $form->getValues();
      $db = Engine_Db_Table::getDefaultAdapter();
      $settingsTable = Engine_Api::_()->getDbTable('settings', 'core');
      $settingsTableName = $settingsTable->info('name');
      $is_custom = $_POST['theme_color'] == 5 ? 1 : 0;
      $theme_id =  $_POST['theme_color'] != $_POST['theme_color'] ? 1 : $_POST['custom_theme_color'];
      
      if(empty($_POST['submit'])){
        if($_POST['theme_color']){
            Engine_Api::_()->getApi('settings', 'core')->setSetting('sesandroidapptheme_color',$_POST['theme_color']);
        }
        if($_POST['custom_theme_color']){
            Engine_Api::_()->getApi('settings', 'core')->setSetting('sesandroidappcustom_theme_color',$_POST['custom_theme_color']);
        }  
      }
      unset($values['theme_color']);
      unset($values['custom_theme_color']);
      unset($values['save']);
      unset($values['submit']);
      if($_POST['theme_color'] == 5){
        foreach ($values as $key => $value) {        
          $db->query("INSERT INTO `engine4_sesandroidapp_customthemes` (`value`, `column_key`,`is_custom`,`theme_id`) VALUES ('".$value."','".$key."','".$is_custom."','".$theme_id."') ON DUPLICATE KEY UPDATE `value`='".$value."';
  ");     
        }
      }
      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }
  //Get Custom theme color values
  public function getcustomthemecolorsAction() {
    $customtheme_id = $this->_getParam('customtheme_id', 1);
    if(empty($customtheme_id))
      return;
    $themecustom = Engine_Api::_()->getDbTable('customthemes','sesandroidapp')->getThemeKey(array('theme_id'=>$customtheme_id,'is_custom'=>1));
    $customthecolorArray = array();
    foreach($themecustom as $value) {
      $customthecolorArray[] = $value['column_key'].'||'.$value['value'];
    }
    echo json_encode($customthecolorArray);die;
  }  
  public function addCustomThemeAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $customtheme_id = $this->_getParam('customtheme_id', 0);
    $this->view->form = $form = new Sesandroidapp_Form_Admin_CustomTheme();
    if ($customtheme_id) {
      $form->setTitle("Edit Custom Theme Name");
      $form->submit->setLabel('Save Changes');
      $customtheme = Engine_Api::_()->getItem('sesandroidapp_themes', $customtheme_id);
      $form->populate($customtheme->toArray());
    }
    if ($this->getRequest()->isPost()) {
      if (!$form->isValid($this->getRequest()->getPost()))
        return;
      $db = Engine_Api::_()->getDbtable('themes', 'sesandroidapp')->getAdapter();
      $db->beginTransaction();
      try {
        $table = Engine_Api::_()->getDbtable('themes', 'sesandroidapp');
        $values = $form->getValues();
        if(!$customtheme_id)
          $customtheme = $table->createRow();
        $customtheme->setFromArray($values);
        $customtheme->save();
        $theme_id = $customtheme->theme_id;
        if(!empty($values['customthemeid'])) {
          $dbInsert = Engine_Db_Table::getDefaultAdapter();
           $db->query("INSERT IGNORE INTO engine4_sesandroidapp_customthemes (column_key,is_custom,theme_id,value) SELECT column_key,1,'".$theme_id."',value FROM engine4_sesandroidapp_customthemes WHERE theme_id = '".$customtheme_id."'");
        }
        $db->commit();
        return $this->_forward('success', 'utility', 'core', array(
          'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesandroidapp', 'controller' => 'theme', 'action' => 'index', 'customtheme_id' => $customtheme->theme_id),'admin_default',true),
          'messages' => array('New Custom theme created successfully.')
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }
  public function deleteCustomThemeAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->customtheme_id = $customtheme_id = $this->_getParam('customtheme_id', 0);
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $getActivatedTheme = $settings->getSetting('sesandroidapptheme.color',1);
    $customActivatedTheme = $settings->getSetting('sesandroidappcustom.theme.color',1);
    if($getActivatedTheme == 5){
        if($customActivatedTheme == $customtheme_id){
          // activated theme
            $this->renderScript('admin-theme/activated-custom-theme.tpl');
            return;
        }
    }    
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $theme = Engine_Api::_()->getItem('sesandroidapp_themes', $customtheme_id);
        $dbQuery = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbQuery->query("DELETE FROM engine4_sesandroidapp_customthemes WHERE theme_id = ".$theme->theme_id);
         $theme->delete();
        $db->commit();
        $this->_forward('success', 'utility', 'core', array(
            'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesandroidapp', 'controller' => 'theme', 'action' => 'index'),'admin_default',true),
            'messages' => array('You have successfully delete custom theme.')
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    } else {
      // Output
      $this->renderScript('admin-theme/delete-custom-theme.tpl');
    }
  }
}
