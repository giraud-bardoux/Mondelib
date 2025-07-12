<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ManageController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminManagePackagesController extends Core_Controller_Action_Admin {

  public function init() {
  
    $packageManager = new Engine_Package_Manager(array(
        'basePath' => APPLICATION_PATH,
    ));
    Zend_Registry::set('Engine_Package_Manager', $packageManager);
    $packageManager = Zend_Registry::get('Engine_Package_Manager');
    $packageManager->setDb(Engine_Db_Table::getDefaultAdapter());

    $package = array();
    foreach ($packageManager->listInstalledPackages(array(), false) as $installedPackage) {
      $key = explode('-', $installedPackage->getKey());
      $package[$key[1]] = $installedPackage->getKey();
    }
    $this->view->packages = $package;
  }

  public function indexAction() {
    $table = Engine_Api::_()->getDbTable('modules', 'core');
    $this->view->name = $name = $this->_getParam('name', null);
    $this->view->results = $this->allModulesData(array('name' => $name));
    $this->view->allEnabledModules = $this->allModulesData(array('enabled' => 1));
    $this->view->allDisabledModules = $this->allModulesData(array('enabled' => 0));
  }
  
  public function enabledAction() {
    
    $table = Engine_Api::_()->getDbTable('modules', 'core');
    $this->view->name = $name = $this->_getParam('name', null);
    $this->view->allModules = $this->allModulesData();
    $this->view->results = $this->allModulesData(array('enabled' => 1, 'name' => $name));
    $this->view->allEnabledModules = $this->allModulesData(array('enabled' => 1));
    $this->view->allDisabledModules = $this->allModulesData(array('enabled' => 0));
  }
  
  public function disabledAction() {
    $table = Engine_Api::_()->getDbTable('modules', 'core');
    $this->view->name = $name = $this->_getParam('name', null);
    $this->view->allModules = $this->allModulesData();
    $this->view->results = $this->allModulesData(array('enabled' => 0, 'name' => $name));
    $this->view->allEnabledModules = $this->allModulesData(array('enabled' => 1));
    $this->view->allDisabledModules = $this->allModulesData(array('enabled' => 0));
  }
  
  public function allModulesData($params = array()) {
    $viewer = Engine_Api::_()->user()->getViewer();
    // Get items
    $table = Engine_Api::_()->getDbtable('menuItems', 'core');
    $select = $table->select()
                  ->where('menu = ?', 'core_admin_main_plugins')
                  ->order('label');

    if(!$viewer->isSuperAdmin()) {
      $select->where('name <> ?', 'core_admin_main_plugins_acppro');
    }

    $getMenus = $table->fetchAll($select);
    $menus = array();
    foreach($getMenus as $getMenu) {
      $menus[] = $getMenu->module;
    }
    
    $table = Engine_Api::_()->getDbTable('modules', 'core');
    $select = $table->select()
                  ->from($table->info('name'))
                  ->where('type = ?', 'extra')
                  ->order('title ASC');
    if(engine_count($menus) > 0) { 
      $select->where('name IN (?)', $menus);
    }
    if(isset($params['name']) && !empty($params['name']))
      $select->where("title LIKE ?", $params['name'].'%');
    if(isset($params['enabled']) && $params['enabled'] == 1) {
      $select->where('enabled = ?', 1);
    }
    if(isset($params['enabled']) && $params['enabled'] == 0) {
      $select->where('enabled = ?', 0);
    }
    return $table->fetchAll($select);
  }


  public function disableAction() {
    
    $packageManager = new Engine_Package_Manager(array(
        'basePath' => APPLICATION_PATH,
    ));
    Zend_Registry::set('Engine_Package_Manager', $packageManager);
    $packageManager = Zend_Registry::get('Engine_Package_Manager');
    $packageManager->setDb(Engine_Db_Table::getDefaultAdapter());
    
    $this->view->form = $form = new Core_Form_Admin_Confirm(array(
        'title' => 'Disable Package?',
        'description' => 'Are you sure you want to disable this package?',
        'submitLabel' => 'Disable Package',
        'cancelHref' => $this->view->url(array('action' => 'index')),
        'useToken' => true,
    ));

    if (!$this->getRequest()->isPost()) {
        return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
        return;
    }

    // Do the disable
    $packageName = $this->_getParam('package');
    
    $package = null;
    foreach ($packageManager->listInstalledPackages(array(), false) as $installedPackage) {
        if ($installedPackage->getKey() == $packageName) {
            $package = $installedPackage;
        }
    }

    // Enable/disable
    if ($package->hasAction('disable')) {
        $operation = new Engine_Package_Manager_Operation_Disable($packageManager, $package);
        $ret = $packageManager->execute($operation, 'disable');
    }
    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => array('Plugin disable successfully.')
    ));
  }
  

  public function enableAction() {
    
    $packageManager = new Engine_Package_Manager(array(
        'basePath' => APPLICATION_PATH,
    ));
    Zend_Registry::set('Engine_Package_Manager', $packageManager);
    $packageManager = Zend_Registry::get('Engine_Package_Manager');
    $packageManager->setDb(Engine_Db_Table::getDefaultAdapter());
    
    $this->view->form = $form = new Core_Form_Admin_Confirm(array(
        'title' => 'Enable Package?',
        'description' => 'Are you sure you want to enable this package?',
        'submitLabel' => 'Enable Package',
        'cancelHref' => $this->view->url(array('action' => 'index')),
        'useToken' => true,
    ));

    if (!$this->getRequest()->isPost()) {
        return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
        return;
    }

    // Do the enable
    $packageName = $this->_getParam('package');
    $package = null;
    
    foreach ($packageManager->listInstalledPackages(array(), false) as $installedPackage) {
      if ($installedPackage->getKey() == $packageName) {
        $package = $installedPackage;
      }
    }

    // Enable/disable
    if ($package->hasAction('enable')) {
        $operation = new Engine_Package_Manager_Operation_Enable($packageManager, $package);
        $ret = $packageManager->execute($operation, 'enable');

        // Try to flush the scaffold cache
        try {
            Engine_Package_Utilities::fsRmdirRecursive(APPLICATION_PATH . '/temporary/scaffold', false, array('index.html'));
        } catch (Exception $e) {
        }

        // Try to increment the site counter
        try {
            $db = Zend_Registry::get('Zend_Db');
            $db->update('engine4_core_settings', array(
                'value' => new Zend_Db_Expr('value + 1'),
            ), array(
                'name = ?' => 'core.site.counter',
            ));
        } catch (Exception $e) {
            // Silence
        }
    }
    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => array('Plugin enabled successfully.')
    ));
  }
}
