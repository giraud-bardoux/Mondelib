<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Dispatcher.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
class Sesapi_Controller_Action_Helper_Dispatcher extends Zend_Controller_Action_Helper_Abstract {
  public function postDispatch()
  {
    if(Engine_Api::_()->core()->hasSubject() && !empty($_GET["getDeepLinkingParams"])){
      $request = new Zend_Controller_Request_Http((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
      $frontController = Zend_Controller_Front::getInstance();		
      $router = $frontController->getRouter();
      $routeName = $router->route($request);
      $getParams = $routeName->getParams();
      unset($getParams["getDeepLinkingParams"]);
      unset($getParams["rewrite"]);
      $subject = Engine_Api::_()->core()->getSubject();

      $contentTypeItem = Engine_Api::_()->getItemTable($subject->getType());
			//get current content type item id
      $primaryId = current($contentTypeItem->info("primary"));

      $getParams["resource_type"] = $subject->getType();
      $getParams[$primaryId] = $subject->getIdentity();
      
      if(!empty($subject->custom_url))
        $getParams["custom_url"] = $subject->custom_url;
      
      if(!empty($subject->album_id))
        $getParams["album_id"] = $subject->album_id;

      if(!empty($subject->owner_id))
        $getParams["owner_id"] = $subject->owner_id;
      
      if(!empty($subject->user_id))
        $getParams["user_id"] = $subject->user_id;

        echo json_encode($getParams);die;
    }
  }
  public function preDispatch()
  {
     
    if(!empty($_SESSION['sesapi'])){
      $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      unset($_SESSION['sesapi']); 
      header("Location:".$actual_link.'&restApi=Sesapi');
      exit();
    }
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmultiplecurrency') && !empty($_GET['restApi']) && $_GET['restApi'] == "Sesapi"){
      $viewer = Engine_Api::_()->user()->getViewer();
      $settings = Engine_Api::_()->getApi('settings', 'core');
      //if(!isset($_COOKIE['sesmultiplecurrency_currencyId'])) {
        if(!empty($_COOKIE['sesmultiplecurrency_currencyId'])){
              $currency = $settings->getSetting("sesmultiplecurrency_user".$viewer->getIdentity(),$_COOKIE['sesmultiplecurrency_currencyId']);
        }else if(!empty($_SESSION['sesmultiplecurrency_currencyId'])){
              $currency = $_SESSION['sesmultiplecurrency_currencyId'];
        } else {
          $currency = "";
        }
        $currency = @!empty($currency) ? $currency : $settings->getSetting("sesmultiplecurrency_user".$viewer->getIdentity(),Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency());
        $currency = $settings->getSetting('sesmultiplecurrency.'.$currency.'active','0') ? $currency : $settings->getSetting("sesmultiplecurrency_user".$viewer->getIdentity(),Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency());
        $_SESSION['sesmultiplecurrency_currencyId'] = $currency;
      //}
      
      $_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'] = 1;
    }
    //location based search
    if(!empty($_SESSION['location_data'])){
      $_COOKIE["location_data"] = $_SESSION['location_data'];
      $_COOKIE["location_lat"] = $_SESSION['location_lat'];
      $_COOKIE["location_lng"] = $_SESSION['location_lng'];
    }
    
    if(!empty($_GET['sesapi_platform'])){
      $settingEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesapi.headerfooter.enable', '1');
    }else
      $settingEnable = 0;
    if(($settingEnable || !empty($_SESSION['removeSiteHeaderFooter']) || !empty($_GET["fromApp"])) && strpos($_SERVER['REQUEST_URI'],'admin') === FALSE ){
      $_SESSION['removeSiteHeaderFooter'] = true;
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl . 'application/modules/Sesapi/externals/styles/style.css');
    }
    //set language
    $locale = null;
    $language = (!empty($_REQUEST['language'])) ? $_REQUEST['language'] : "";
    if($language){
      if( !empty($language) ) {
        try {
          $language = Zend_Locale::findLocale($language);
        } catch( Exception $e ) {
          $language = null;
        }
      }
  
      if(  $language && !$locale ) $locale = $language;
      if( !$language &&  $locale ) $language = $locale;
      
      if( $language && $locale ) {
        // Set as cookie
        //remove language cookie to set again
        if(isset($_COOKIE['en4_language']))
          setcookie('en4_language', $language, time() - (86400*365), '/');
        setcookie('en4_language', $language, time() + (86400*365), '/');
        //remove locale cookie to set again
        if(isset($_COOKIE['en4_locale']))
          setcookie('en4_locale', $language, time() + (86400*365), '/');
        setcookie('en4_locale',   $locale,   time() + (86400*365), '/');
      }
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id_loggedin = $viewer->getIdentity();
    if(!empty($_REQUEST['auth_token'])){
      $user_id = Engine_Api::_()->getApi('oauth','sesapi')->validateToken($_REQUEST['auth_token']);
      if($user_id){ 
        $user = Engine_Api::_()->getItem('user',$user_id);
        if($user->getIdentity()){
          Zend_Auth::getInstance()->getStorage()->write($user_id);
          Engine_Api::_()->user()->setViewer();  
          // fixed issue for local change from general settings page
          // if(!empty($locale) && !empty($language)){
          //   $setLocale = new Zend_Locale($locale);
          //   $user->locale = $locale;
          //   $user->language = $language;
          //   $user->save();
          // }
          if(empty($_REQUEST["restApi"]) && empty($user_id_loggedin)){
            header("Location:".$_SERVER["REQUEST_URI"]);
            exit();
          }
        }else{
          Engine_Api::_()->user()->getAuth()->clearIdentity();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'account_deleted','result'=>array()));  
        }
      }
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(!empty($_SESSION['subscriptionStepsEnable']) && strpos($_SERVER['REQUEST_URI'],'sesapi/subscription') === false){
       $_SERVER['REQUEST_URI'] = $view->url(array('module'=>'sesapi','controller'=>'subscription','action'=>"finish",'state'=>"failure"),'default',true);
       unset($_SESSION['subscriptionStepsEnable']);
    }
  }
}