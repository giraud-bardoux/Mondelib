<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: AdminSettingsController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_AdminSettingsController extends Core_Controller_Action_Admin {
    function helpAction(){
        $this->_forward('support', null, null, array());
    }
	public function supportAction() {

       $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesapi_admin_main', array());

    }
  public function indexAction() {
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesapi_admin_main', array(), 'sesapi_admin_main_settings');
    $this->view->form = $form = new Sesapi_Form_Admin_Global();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      include_once APPLICATION_PATH . "/application/modules/Sesapi/controllers/License.php";
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesapi.pluginactivated')) {
        unset($values['sesapi_update_enable']);
        foreach ($values as $key => $value) {
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if($error)
          $this->_helper->redirector->gotoRoute(array());
      }
    }
  }
  function manualAction(){
      $this->_helper->layout->setLayout('admin-simple');
  }
  function automaticAction(){
    
    
    $this->_helper->layout->setLayout('admin-simple');
    $exists = Engine_Api::_()->sesapi()->checkCodeexists();
    if(!$exists){
        $path = APPLICATION_PATH.DIRECTORY_SEPARATOR.'index.php';
        if(file_exists($path)){
          $checkString = "restApi";
          $content = file_get_contents($path);
          $replaceString = 
              'if(isset($_GET[\'restApi\']) && !empty($_GET[\'restApi\']) && $_GET[\'restApi\'] == \'Sesapi\'){ 
  define(\'_ENGINE_R_TARG\', \'sesapi.php\'); 
  define(\'_SESAPI_R_TARG\', \'sesapi.php\');
  if(!empty($_GET[\'sesapi_platform\']))
    define(\'_SESAPI_PLATFORM_SERVICE\', $_GET[\'sesapi_platform\']);
  else
    define(\'_SESAPI_PLATFORM_SERVICE\',0);
  if(!empty($_GET[\'sesapi_version\'])){
    if(_SESAPI_PLATFORM_SERVICE == 1)
      define(\'_SESAPI_VERSION_IOS\',$_GET[\'sesapi_version\']);
    else if(_SESAPI_PLATFORM_SERVICE == 2)
      define(\'_SESAPI_VERSION_ANDROID\',$_GET[\'sesapi_version\']);
  }else{
      define(\'_SESAPI_VERSION_ANDROID\',0);
      define(\'_SESAPI_VERSION_IOS\',0);
  }
  if(empty($_FILES[\'image\']))
      $_FILES[\'image\'] = array();
  elseif(empty($_FILES[\'video\']))
      $_FILES[\'video\'] = array();
}else
    define(\'_ENGINE_R_TARG\', \'index.php\');
if(!empty($_GET[\'sesapiPaymentModel\']))
  $_SESSION[\'sesapiPaymentModel\']  = true;';
          $finalContent = str_replace('define(\'_ENGINE_R_TARG\', \'index.php\');',$replaceString,$content);
          chmod($path, 0777);
          $user_model_codewrite = fopen($path, 'w+');
          fwrite($user_model_codewrite, $finalContent);
          fclose($user_model_codewrite);
        }
    }      
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 2000,
      'parentRefresh' => 2000,
      'messages' => array('Code upgraded in file Successfully.')
    ));
  }
  // for default installation
  function setDashboardIcons($file, $cat_id, $resize = false) {
    $fileName = $file;
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sesapi_menu',
        'parent_id' => $cat_id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $name,
    );

    // Save
    $filesTable = Engine_Api::_()->getDbTable('files', 'storage');
    if ($resize) {
      // Resize image (main)
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_poster.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(800, 800)
              ->write($mainPath)
              ->destroy();

      // Resize image (normal) make same image for activity feed so it open in pop up with out jump effect.
      $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_thumb.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(500, 500)
              ->write($normalPath)
              ->destroy();
    } else {
      $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_poster.' . $extension;
      copy($file, $mainPath);
    }
    if ($resize) {
      // normal main  image resize
      $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_icon.' . $extension;
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(100, 100)
              ->write($normalMainPath)
              ->destroy();
    } else {
      $normalMainPath = $path . DIRECTORY_SEPARATOR . $base . '_icon.' . $extension;
      copy($file, $normalMainPath);
    }
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      if ($resize) {
        $iIconNormal = $filesTable->createFile($normalPath, $params);
        $iMain->bridge($iIconNormal, 'thumb.thumb');
      }
      $iNormalMain = $filesTable->createFile($normalMainPath, $params);
      $iMain->bridge($iNormalMain, 'thumb.icon');
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      if ($resize) {
        @unlink($normalPath);
      }
      @unlink($normalMainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new User_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    if ($resize) {
      @unlink($normalPath);
    }
    @unlink($normalMainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
  function documentationAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesapi_admin_main', array(), 'sesapi_admin_main_documentation');
  }
  
  
  function uploadMenuAction(){
    $menus = array('search'=>1,'messages'=>3,'notifications'=>4,'friends_req'=>5,'members'=>7,'album'=>8,'videos'=>9,'blog'=>10,'music'=>11,'settings'=>13,'contactus'=>15,'privacy'=>16,'termofservice'=>17,'signout'=>18,'videochanel'=>19,'videoplaylist'=>20,'musicplaylist'=>21,'musicsongs'=>22);
    $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesapi' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "menu" . DIRECTORY_SEPARATOR;
    foreach($menus as $key=>$value){
      $menu = Engine_Api::_()->getItem('sesapi_menu',$value);
      if($menu){
        if (is_file($PathFile . $key.'.png')){
            $menu->setPhoto($PathFile . $key.'.png');
        }  
      }  
    }
   
   
  }
  
}
