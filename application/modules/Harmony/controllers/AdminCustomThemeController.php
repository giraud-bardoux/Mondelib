<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminCustomThemeController.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_AdminCustomThemeController extends Core_Controller_Action_Admin {

  public function indexAction() {
    
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('harmony_admin_main', array(), 'harmony_admin_main_customcss');
    
    //Start Make extra file for harmony theme custom css and harmony constant
    $this->makeCustomFile($form);
    //Start Make extra file for harmony constant
    
    $writeable = array();
    $writeable['harmony'] = false;
    try {
      foreach(array('theme.css', 'harmony-custom.css') as $file) {
        if(!file_exists(APPLICATION_PATH . "/application/themes/harmony/$file")) {
          throw new Core_Model_Exception('Missing file');
        } else {
          $this->checkWriteable(APPLICATION_PATH . "/application/themes/harmony/$file");
        }
      }
      $writeable['harmony'] = true;
    } catch(Exception $e) {
      $this->view->errorMessage = $e->getMessage();
    }
    
    $this->view->writeable = $writeable;
    $this->view->activeFileContents = file_get_contents(APPLICATION_PATH . '/application/themes/harmony/harmony-custom.css');
  }

  public function saveAction() {
  
    $theme_id = 'harmony';
    $file = $this->_getParam('file');
    $body = $this->_getParam('body');

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Bad method");
      return;
    }

    if( !$theme_id || !$file || !$body ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Bad params");
      return;
    }

    // Get theme
    $themeTable = Engine_Api::_()->getDbtable('themes', 'core');
    $themeSelect = $themeTable->select()->where('name = ?', $theme_id)->limit(1);
    $theme = $themeTable->fetchRow($themeSelect);

    if( !$theme ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Missing theme");
      return;
    }

    //Check file
    $basePath = APPLICATION_PATH . '/application/themes/harmony';
    $manifestData = include $basePath . '/manifest.php';
    if( empty($manifestData['files']) || !engine_in_array($file, $manifestData['files']) ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Not in theme files");
      return;
    }
    $fullFilePath = $basePath . '/' . $file;
    try {
      $this->checkWriteable($fullFilePath);
    } catch( Exception $e ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Not writeable");
      return;
    }

    // Now lets write the custom file
    if( !file_put_contents($fullFilePath, $body) ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Could not save contents');
      return;
    }
    
    // clear scaffold cache
    Core_Model_DbTable_Themes::clearScaffoldCache();
    $this->view->status = true;
  }
 
  public function checkWriteable($path)
  {
    if( !file_exists($path) ) {
      throw new Core_Model_Exception('Path doesn\'t exist');
    }
    if( !is_writeable($path) ) {
      throw new Core_Model_Exception('Path is not writeable');
    }
    if( !is_dir($path) ) {
      if( !($fh = fopen($path, 'ab')) ) {
        throw new Core_Model_Exception('File could not be opened');
      }
      fclose($fh);
    }
  }
  
  public function makeCustomFile($form) {

    //Start Make extra file for harmony theme custom css
    $themeDirName = APPLICATION_PATH . '/application/themes/harmony';
    @chmod($themeDirName, 0777);
    if (!is_readable($themeDirName)) {
      $itemError = Zend_Registry::get('Zend_Translate')->_("You have not read permission on below file path. So, please give chmod 777 recursive permission to continue this process. Path Name: %s", $themeDirName);
      $form->addError($itemError);
      return;
    }
    $fileName = $themeDirName . '/harmony-custom.css';
    $fileexists = @file_exists($fileName);
    if (empty($fileexists)) {
      @chmod($themeDirName, 0777);
      if (!is_writable($themeDirName)) {
        $itemError = Zend_Registry::get('Zend_Translate')->_("You have not writable permission on below file path. So, please give chmod 777 recursive permission to continue this process. <br /> Path Name: $themeDirName");
        $form->addError($itemError);
        return;
      }
      $fh = @fopen($fileName, 'w');
      @fwrite($fh, '/* ADD YOUR CUSTOM CSS HERE */');
      @chmod($fileName, 0777);
      @fclose($fh);
      @chmod($fileName, 0777);
      @chmod($fileName, 0777);
    }
    //Start Make extra file for harmony theme custom css
  }
}
