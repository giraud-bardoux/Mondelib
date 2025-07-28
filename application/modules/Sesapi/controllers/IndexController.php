<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Sesapi_IndexController extends Sesapi_Controller_Action_Standard
{

  function addshortcutAction(){
      $id = Engine_Api::_()->sesapi()->addShortCut($this->_getAllParams());
      if($id){
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $data['shortcut_id'] = $id;
        $data['message'] = $view->translate("Remove From Shortcuts");
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $data));
      }
  }
  function removeshortcutAction(){
    $return = Engine_Api::_()->sesapi()->removeShortCut($this->_getAllParams());
    if($return){
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $data['message'] = $view->translate("Add to Shortcuts");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $data));
    }
}

  function circuitChatAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid User'));

    }

    // Get permission setting
    $permission = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'messages', 'create');
    if( Authorization_Api_Core::LEVEL_DISALLOW === $permission )
    {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'Invalid User'));
    } 


    $userId = !empty($_POST["uid"]) ? $_POST["uid"] : $viewer->getIdentity();

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_url', '').'/api/user/login-uid',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "uid":'.$userId.'
      } ',
      CURLOPT_HTTPHEADER => array(
        'Client-ID: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_client_id', ''),
        'Client-Secret: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_client_secret', ''),
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $responseJSON = json_decode($response,true);
    $responseJSON["type"] = "circuitChat";


    // update token
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_url', '').'/api/user/push-notification',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "token":'.$_POST["token"].'
      } ',
      CURLOPT_HTTPHEADER => array(
        'Client-ID: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_client_id', ''),
        'Client-Secret: '.Engine_Api::_()->getApi('settings', 'core')->getSetting('echat_client_secret', ''),
        'Content-Type: application/json'
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $responseJSON));
  }

  public function suggestAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');


        if($this->_getParam('allUser') || $this->_getParam('message',false)){
            $select = $table->select()->where('enabled = ?','1');
        }else {
            $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
        }


      if( $this->_getParam('includeSelf', true) ) {
        $data[] = array(
          'type' => 'user',
          'id' => $viewer->getIdentity(),
          'guid' => $viewer->getGuid(),
          'label' => $viewer->getTitle() . ' (you)',
          'photo' => $this->view->itemPhoto($viewer, 'thumb.icon'),
          'url' => $viewer->getHref(),
        );
      }

      if( 0 < ($limit = (int) $this->_getParam('limit', 10)) ) {
        $select->limit($limit);
      }

      if( null !== ($text = $this->_getParam('search', $this->_getParam('value'))) ) {
        $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
      }
      $ids = array();
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
        $data[] = array(
          'type'  => 'user',
          'id'    => $friend->getIdentity(),
          'guid'  => $friend->getGuid(),
          'label' => $friend->getTitle(),
          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
          'url'   => $friend->getHref(),
        );
        $ids[] = $friend->getIdentity();
        $friend_data[$friend->getIdentity()] = $friend->getTitle();
      }
    }

    if( $this->_getParam('sendNow', true) ) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
  
  public  function updatePushTokenAction(){
      $device_id = $this->_getParam('device_id');
      $resource_id = $this->_getParam('user_id',$this->view->viewer()->getIdentity());
      Engine_Api::_()->getDbTable('users','sesapi')->register(array('user_id'=>$resource_id,'device_uuid'=>$device_id));
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => 1));
  }
  public function indexAction()
  {
    $this->view->someVar = 'someVal';
  }
  function privacyAction(){
    $str = $this->view->translate('_CORE_PRIVACY_STATEMENT');
    if ($str == strip_tags($str)) {
      // there is no HTML tags in the text
      $message = nl2br($str);
    } else {
      $message = $str;
    }
    $title =  $this->view->translate('Privacy Statement');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('privacy'=>array('description'=>$message,'title'=>$title))));
  }
  function termsAction(){
    $title =  $this->view->translate('Terms of Service');
    $str = $this->view->translate('_CORE_TERMS_OF_SERVICE');
    if ($str == strip_tags($str)) {
      // there is no HTML tags in the text
      $message = nl2br($str);
    } else {
      $message = $str;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('terms'=>array('description'=>$message,'title'=>$title))));
  }
  

  public function supportedCurrenciesAction() {	

    //Currency work	
    $currencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));	
    if(engine_count($currencies) > 1) {	

      $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();	
      $currentData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);	

      $defaultCurrency = Engine_Api::_()->payment()->getCurrentCurrency();	
      $currenciesArray = array();	

      $counter = 0;	
      foreach ($currencies as $currency) {
//         if($currentCurrency == $currency->code)
//           continue;	
        
        $currenciesArray[$counter]['key'] = $currency->code;
        $currenciesArray[$counter]['title'] = $currency->title . ' - ' . $currency->code;	

        if(isset($currency->icon) && !empty($currency->icon)) {	
          $path = Engine_Api::_()->core()->getFileUrl($currency->icon);	
          if($path) {	
            $currenciesArray[$counter]['image_url'] = $path;	
          }	
        } else {	
          $currenciesArray[$counter]['image_url'] = '';	
        }
        $counter++;
      }	
      $result['enabled_currencies'] = $currenciesArray;	
    }

    $result['default_currency']  = $defaultCurrency;	
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "", 'result' => $result));	
  } 	

  public function changeCurrencyAction() {	

    $viewer = Engine_Api::_()->user()->getViewer();	
    $_SESSION['current_currencyId'] = $this->_getParam('currency',"USD");	
    $settings = Engine_Api::_()->getApi('settings', 'core');	
    if($viewer->getIdentity() && $settings->hasSetting("sesmultiplecurrency_user".$viewer->getIdentity())){	
      $settings->removeSetting("sesmultiplecurrency_user".$viewer->getIdentity());	
    }	

    if($viewer->getIdentity()){	
      $settings->setSetting("sesmultiplecurrency_user".$viewer->getIdentity(),$_SESSION['current_currencyId']);	
    }	

    setcookie('current_currencyId', $_SESSION['current_currencyId'], time() + (86400*365), '/');	

    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => "",'result'=>array('default_currency' => $_SESSION['current_currencyId'])));	
  }
  
  function appDefaultDataAction(){
      //update session
      $token = !empty($_REQUEST['auth_token']) ? $_REQUEST['auth_token'] : "";
      $table = Engine_Api::_()->getDbTable('aouthtokens', 'sesapi');
      if($token){
          $token = $table->check($token);
          if($token){
            $token->sessions++;
            $token->save();
          }
      }
      $result = array();
      $settings = Engine_Api::_()->getApi('settings', 'core');
      if(_SESAPI_PLATFORM_SERVICE == 1){
          $result['isEnableSkipLogin'] = $settings->getSetting('sesiosapp.guest.enable', 1) ? true : false;
      }else{
          $result['isEnableSkipLogin'] = $settings->getSetting('sesandroidapp.guest.enable', 1) ? true : false;
      }
        $result['is_core_activity'] = false;
      
      //check core plugins
      $coreModules = array();
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('tickvideo')){
        $coreModules["tickvideo"] = true;
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1){
        $coreModules["seslocation"] = true;
      }
      //blog,classified,event,forum,group,music,album,poll,video,activity
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('blog')){
        $coreModules["blog"] = "blog";
      }
      
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('classified')){
        $coreModules["classified"] = "classified";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('event')){
        $coreModules["event"] = "event";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('forum')){
        $coreModules["forum"] = "forum";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('group')){
        $coreModules["group"] = "group";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('music')){
        $coreModules["music"] = "music";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album')){
        $coreModules["album"] = "album";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('poll')){
        $coreModules["poll"] = "poll";
      }
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')){
        $coreModules["video"] = "video";
      }
      
        $result['sesfeedgif_giphyapi'] = $settings->getSetting('activity.giphyapi', '');
      $result['core_modules_enabled'] = $coreModules;
      if(count($coreModules) == 0){
        $result['core_modules_enabled'] = (object) array();
      }
      $result["isAlbumEnable"] = false;
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum')){
        $result["isAlbumEnable"] = true;
      }
      $result["isVideoEnable"] = false;
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesvideo') || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video')){
        $result["isVideoEnable"] = true;
      }
      
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.followenable', '1')) {
        $result["isfollowEnable"] = true;
      } else {
        $result["isfollowEnable"] = false;
      }
      
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2)) {
        $result["isfriendsEnable"] = true;
      } else {
        $result["isfriendsEnable"] = false;
      }

     if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')){
        $recArray = array();
        $reactions = Engine_Api::_()->getDbTable('reactions','comment')->getPaginator();
        $counterReaction = 0;
        foreach($reactions as $reac){
          if(!$reac->enabled)
            continue;
          $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
          $result['reaction'][$counterReaction]  = $icon['main'];
          $counterReaction++;
        }
      }
      if(_SESAPI_PLATFORM_SERVICE == 1){
        $result['loginBackgroundImage'] =  $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesiosapp_login_background_image', 'application/modules/Sesiosapp/externals/images/login.jpeg')));
        $result['forgotPasswordBackgroundImage'] =   $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesiosapp_forgot_background_image', 'application/modules/Sesiosapp/externals/images/forgot.jpeg')));
        $result['rateusBackgroundImage'] =  $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesiosapp_rateus_background_image', 'application/modules/Sesiosapp/externals/images/rateus.jpg')));
        $result['dahsboardmenuBackgroundImage'] = $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesiosapp_dashboardmenu_background_image', 'application/modules/Sesiosapp/externals/images/dashboardmenu.jpg')));

        $result['loadingImage'] = $settings->getSetting('sesiosapp_loadingimage', '32');
        $result['titleHeaderType'] = (string) $settings->getSetting('sesiosapp_show_titleheader', '1');
        $result['memberImageShapeIsRound'] = $settings->getSetting('sesiosapp_memberImageShapeIsRound', '0') ? true : false;
        $result['isNavigationTransparent'] = $settings->getSetting('sesiosapp_isNavigationTransparent', '0') ? true : false;
        $result['siteTitle'] = $settings->getSetting('sesiosapp_sitetitle', '') ;
        $result['enableLoggedinUserphoto'] = $settings->getSetting('sesiosapp_display_loggedinuserphoto', 1) ? true : false;
        $result['enableTabbedMenu'] = $settings->getSetting('sesiosapp_enable_tabbedmenu', 1) ? true : false;
        $result['limitForIphone'] = (string)$settings->getSetting('sesiosapp_limitForIphone', 10);
        $result['limitForIpad'] = (string)$settings->getSetting('sesiosapp_limitForIpad', 10);
        $result['descriptionTrucationLimitFeed'] = (int)$settings->getSetting('sesiosapp_feedtruncationlimit', 200);
        $result['enableTabbarTitle'] = $settings->getSetting('sesiosapp_showtabbartitle', 1) ? true : false;
        $result['enableHeaderFixedFeed'] = $settings->getSetting('sesiosapp.headerfixed', 1) ? true : false;
        $result['shareTextForFeed'] = $settings->getSetting('sesiosapp_shareontext', 'SocialEngine');
        $result['appstoreUrl'] = str_replace(array('https://','http://'),'',$settings->getSetting('sesiosapp_appurl', ''));
        $result['googleapikey'] = $settings->getSetting('sesiosapp_googleapikey', '');

        //default app styling
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $getActivatedTheme = $settings->getSetting('sesiosapptheme.color',1);
        $customActivatedTheme = $settings->getSetting('sesiosappcustom.theme.color',1);
        $isCustom = 0;
        $theme_id = $getActivatedTheme;
        if($getActivatedTheme == 5){
          $isCustom = 1;
          $theme_id = $customActivatedTheme;
        }
        $sesiosapptheme = Engine_Api::_()->getDbTable('customthemes','sesiosapp')->getThemeKey(array('theme_id'=>$theme_id,'is_custom'=>$isCustom));
        $themeStyling = array();
        $counterTheme = 0;
        foreach($sesiosapptheme as $res){
          if(!$res['value'])
            continue;
          $themeStyling[$counterTheme]['key'] = str_replace('sesiosapp_','',$res['column_key']);
          if(strpos($res['column_key'],'fontSize') !== false || strpos($res['column_key'],'buttonRadius') !== false || strpos($res['column_key'],'buttonBorderWidth') !== false){
            $themeStyling[$counterTheme]['value'] = (int)$res['value'];
          }else{
            $themeStyling[$counterTheme]['value'] = $res['value'];
          }
          $counterTheme++;
        }
        $result['theme_styling'] = $themeStyling;
        $moduleName = "sesiosapp";
      }else if(_SESAPI_PLATFORM_SERVICE == 2){
        $result['loadingImage'] = $settings->getSetting('sesandroid_loadingimage', '3');
        $result['loginBackgroundImage'] = $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesandroidapp_login_background_image', 'application/modules/Sesandroidapp/externals/images/login.jpeg')));
        $result['forgotPasswordBackgroundImage'] =  $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesandroidapp_forgot_background_image', 'application/modules/Sesandroidapp/externals/images/forgot.jpeg')));
        $result['rateusBackgroundImage'] =  $this->getBaseUrl(true,Engine_Api::_()->core()->getFileUrl($settings->getSetting('sesandroidapp_rateus_background_image', 'application/modules/Sesandroidapp/externals/images/rateus.jpg')));

        $result['titleHeaderType'] = $settings->getSetting('sesandroidapp_show_titleheader', '');
        /* Admin - Setting is not working. #756 */
        $result['memberImageShapeIsRound'] = $settings->getSetting('sesandroidapp_memberImageShapeIsRound', '0') ? true : false;
        $result['isNavigationTransparent'] = $settings->getSetting('sesandroidapp_isNavigationTransparent', '0') ? true : false;
        $result['siteTitle'] = $settings->getSetting('sesandroidapp_sitetitle', '') ;
        $result['enableLoggedinUserphoto'] = $settings->getSetting('sesandroidapp_display_loggedinuserphoto', 1) ? true : false;
        $result['limitForPhone'] = (string)$settings->getSetting('sesandroidapp_limitForphone', 10);
        $result['limitForTablet'] = (string)$settings->getSetting('sesandroidapp_limitForTablet', 10);
        $result['descriptionTrucationLimitFeed'] = (int)$settings->getSetting('sesandroidapp_feedtruncationlimit', 200);
        $result['enableTabbarTitle'] = $settings->getSetting('sesandroidapp_showtabbartitle', 1) ? true : false;
        $result['enableHeaderFixedFeed'] = $settings->getSetting('sesandroidapp.headerfixed', 1) ? true : false;
        $result['shareTextForFeed'] = $settings->getSetting('sesandroidapp_shareontext', 'SocialEngine');

        //default app styling
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $getActivatedTheme = $settings->getSetting('sesandroidapptheme.color',1);
        $customActivatedTheme = $settings->getSetting('sesandroidappcustom.theme.color',1);
        $isCustom = 0;
        $theme_id = $getActivatedTheme;
        if($getActivatedTheme == 5){
          $isCustom = 1;
          $theme_id = $customActivatedTheme;
        }
        $sesandroidapptheme = Engine_Api::_()->getDbTable('customthemes','sesandroidapp')->getThemeKey(array('theme_id'=>$theme_id,'is_custom'=>$isCustom));
        $themeStyling = array();
        $counterTheme = 0;
        foreach($sesandroidapptheme as $res){
          if(!$res['value'])
            continue;
          $themeStyling[$counterTheme]['key'] = str_replace('sesandroidapp_','',$res['column_key']);
          if(strpos($res['column_key'],'fontSize') !== false || strpos($res['column_key'],'buttonRadius') !== false || strpos($res['column_key'],'buttonBorderWidth') !== false){
            $themeStyling[$counterTheme]['value'] = (int)$res['value'];
          }else{
            $themeStyling[$counterTheme]['value'] = $res['value'];
          }
          $counterTheme++;
        }
        $result['theme_styling'] = $themeStyling;
        $moduleName = "sesandroidapp";
      }
      $user = Engine_Api::_()->user()->getViewer();
      if($user->getIdentity()){
          $result['user']["user_id"] = $user->user_id;
          $result['user']["email"] = $user->email;
          $result['user']["username"] = $user->username;
          $result['user']["displayname"] = $user->getTitle();
          $result['user']["photo_id"] = $user->photo_id;
          $result['user']["status"] = $user->status;
          $result['user']["password"] = $user->password;
          $result['user']["status_date"] = $user->status_date;
          $result['user']["salt"] = $user->salt;
          $result['user']["locale"] = $user->locale;
          $result['user']["language"] = $user->language;
          $result['user']["timezone"] = $user->timezone;
          $result['user']["search"] = $user->search;
          $result['user']["level_id"] = $user->level_id;
          $result['user']['photo_url']= $this->userImage($this->view->viewer(),'thumb.profile');

      }
      //default slideshow
      $enableVideo = 0;
      if(_SESAPI_VERSION_ANDROID >= 1.2){
          $enableVideo = 1;
      }
      if(_SESAPI_VERSION_IOS >= 1.2 && _SESAPI_VERSION_IOS < 1.5){
          $enableVideo = 1;
      }
      $result['disable_welcome_screen'] = $settings->getSetting($moduleName.'.disable.welcome',0);
      $paginator = Engine_Api::_()->getDbTable('slides', $moduleName)->getSlides(true,array('fetchAll'=>true,'enableVideo'=>$enableVideo));

      if(_SESAPI_VERSION_IOS >= 1.5 || _SESAPI_VERSION_ANDROID >= 2.4){
        $isVideo = false;
        if($settings->getSetting($moduleName.'.video.slide',0)){
          $result['video_url'] = $this->getBaseUrl(true,$settings->getSetting($moduleName.'.video.slide',0));
          $result['video_slideshow'] = true;
        }
        if(is_countable($paginator) && engine_count($paginator)){
          $slideshows = array();
          $counter = 0;
          foreach($paginator as $item){
            $photoUrl = $item->getFilePath();
            if(!$photoUrl && !$isVideo)
              continue;
            if($photoUrl)
              $slideshows[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            $slideshows[$counter]['title'] = $item->title;
            $slideshows[$counter]['description'] = $item->description;
            $counter++;
          }
          if(is_countable($slideshows) && engine_count($slideshows)){
            $result['slideshow'] = $slideshows;
          }
        }

          //graphic
          $graphics = array();
          $counter = 0;
          $paginator = Engine_Api::_()->getDbTable('graphics', $moduleName)->getGraphics(true,array('fetchAll'=>true));
          foreach($paginator as $item){
            if($item->file_id){
              $photoUrl = $item->getFilePath();
              if($photoUrl)
                $graphics[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            }
            $graphics[$counter]['title'] = $item->title;
            $graphics[$counter]['description'] = $item->description;
            $graphics[$counter]['title_color'] = '#'.$item->title_color;
            $graphics[$counter]['description_color'] = '#'.$item->description_color;
            $graphics[$counter]['background_color'] = '#'.$item->background_color;
            $counter++;
          }
          if(engine_count($graphics)){
            $result['graphics'] = $graphics;
          }
      }else{
        if(is_countable($paginator) && engine_count($paginator)){
          $slideshows = array();
          $counter = 0;
          $isVideo = false;
          foreach($paginator as $item){
            if($item->video_id){
              $videoUrl = $item->getFilePath('video_id');
              if(!$videoUrl)
                continue;
              $slideshows[$counter]['videourl'] = $this->getBaseUrl(false,$videoUrl);
              $isVideo = true;
            }
            $photoUrl = $item->getFilePath();
            if(!$photoUrl && !$isVideo)
              continue;
            if($photoUrl)
              $slideshows[$counter]['image'] = $this->getBaseUrl(false,$photoUrl);
            $slideshows[$counter]['title'] = $item->title;
            $slideshows[$counter]['description'] = $item->description;
            $counter++;
          }
          if(is_countable($slideshows) && engine_count($slideshows)){
            $result['slideshow'] = $slideshows;
            if($isVideo){
              $result['video_slideshow'] = true;
            }
          }
        }
      }
      $result['dateFormat'] = Engine_Api::_()->core()->dateFormatCalendar();
      $result['is_story_enabled'] = false;
      if(_SESAPI_PLATFORM_SERVICE == 2) {
        $result['is_story_enabled'] = $isStoryEnabled =  Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('eandroidstories') ? true : false;
        if ($isStoryEnabled) {
          $result['story_video_limit'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesstories.videouplimit', 10);
          $result['sesstories_storyviewtime'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesstories.storyviewtime', 5);
        }
      

      } else if(_SESAPI_PLATFORM_SERVICE == 1) {
       
        $result['is_story_enabled'] = $isStoryEnabled =  Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('eiosstories') ? true : false;
        if ($isStoryEnabled) {
          $result['story_video_limit'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('eiosstories.videouplimit', 10);
          $result['sesstories_storyviewtime'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('eiosstories.storyviewtime', 5);
        }
      }
      
      //Currency work	
      $currencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));	
      if(engine_count($currencies) > 1) {	
        $result['is_sesmultiplecurrency_enabled'] = 1;	

        $currentCurrency = Engine_Api::_()->payment()->getCurrentCurrency();	
        $currentData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);	

        $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();	

        if(!empty($_SESSION['current_currencyId'])) {	
          $result["default_currency"] = $_SESSION['current_currencyId'];	
        } else if($user->getIdentity()) {	
          $result['default_currency'] = $settings->getSetting("sesmultiplecurrency_user".$user->getIdentity(),$currentCurrency);	
        } else {	
          $result['default_currency'] = $currentCurrency;	
        }	
        $_SESSION['current_currencyId'] = $result['default_currency'];	
      }
  
//       $result['is_sesmultiplecurrency_enabled'] =  (bool)(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmultiplecurrency') && $settings->getSetting('sesmultiplecurrency.pluginactivated'));
//       if( $result['is_sesmultiplecurrency_enabled']){
//           if(!empty($_SESSION['sesmultiplecurrency_currencyId'])){
//               $result["default_currency"] = $_SESSION['sesmultiplecurrency_currencyId'];
//           }else if($user->getIdentity())
//             $result['default_currency'] = $settings->getSetting("sesmultiplecurrency_user".$user->getIdentity(),Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency());
//           else
//               $result['default_currency'] = Engine_Api::_()->payment()->getCurrentCurrency();
//           $_SESSION['sesmultiplecurrency_currencyId'] = $result['default_currency'];
//       }

    // for live stream enable.
    $result['is_livestream_enabled'] = false;
    if ((_SESAPI_VERSION_IOS >= 1.9 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID >= 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
      $result['agora_app_id_live_streaming'] = Engine_Api::_()->getApi('settings', 'core')->getSetting("elivestreaming_agoraappid",'');
      if((_SESAPI_VERSION_IOS >= 1.9 && _SESAPI_PLATFORM_SERVICE == 1))
        $result['is_livestream_enabled'] = (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('eioslivestreaming')) ? true : false;
      if((_SESAPI_VERSION_ANDROID >= 3.1 && _SESAPI_PLATFORM_SERVICE == 2))
        $result['is_livestream_enabled'] = (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('eandlivestreaming')) ? true : false;
      $result['linux_base_url'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('elivestreaming.linux.base.url',"");
    }

      //ses demo user
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesdemouser')){
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $headingText = $this->view->translate($settings->getSetting('sesdemouser.headingText', "Site Tour with Test Users"));
        $innerText = $this->view->translate($settings->getSetting('sesdemouser.innerText', 'Choose a test user to login and take a site tour.'));
        $limit = $settings->getSetting('sesdemouser.limit',6);
        $defaultimage = $settings->getSetting('sesdemouser.defaultimage', '');
        $results = Engine_Api::_()->getDbTable('demousers', 'sesdemouser')->getDemoUsers(array('widgettype' => 'widget', 'limit' => $limit));
        if($defaultimage){
          $defaultimage = Engine_Api::_()->core()->getFileUrl($defaultimage);
        }else{
          $defaultimage = _ENGINE_SITE_URL.'/application/modules/Sesdemouser/externals/images/nophoto_user_thumb_icon.png';
        }
        if (engine_count($results) > 0){
          $demoUsers = array();
          $counterDemo = 0;
          foreach($results as $res){
             $user = Engine_Api::_()->getItem('user', (int) $res->user_id);
             $demoUsers[$counterDemo]['image_url'] = $this->userImage($user->getIdentity());
             $demoUsers[$counterDemo]['user_id'] = $user->getIdentity();
             $counterDemo++;
          }
          $result['demoUser']['users'] = $demoUsers;
          $result['demoUser']['defaultimage'] = $defaultimage;
          $result['demoUser']['headingText'] = $headingText;
          $result['demoUser']['innerText'] = $innerText;
        }
      }
      
      //Login as Username
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.allowloginusername', 0)) { 
        $email = Zend_Registry::get('Zend_Translate')->_('Email Address or Username');
      } else {
        $email = Zend_Registry::get('Zend_Translate')->_('Email Address');
      }
      $result['email_field_label'] = $email;
      
      //Country for otp work
      $otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);
      if(!empty($otpsms_signup_phonenumber)) {
        $countriesArray = array();
        $countriesCounter = 0;
        $countries =  Engine_Api::_()->getDbTable('countries', 'core')->getCountries();
        foreach ($countries as $country) {
          if(!empty($country->icon)) {
            $path = Engine_Api::_()->core()->getFileUrl($country->icon);
            if(!empty($path)) { 
              $image = $path;
            }
          } else {
            $image = '';
          }

          $countriesArray[$countriesCounter]['key'] = $country->phonecode;
          $countriesArray[$countriesCounter]['name'] = $country->name .' (+'.$country->phonecode.')';
          $countriesArray[$countriesCounter]['image'] = $image ? $image : '' ;
          $countriesCounter++;
          
          $getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US'));
          if(!empty($getCountry)) {
            $country = Engine_Api::_()->getItem('core_country', $getCountry);
          }

          $result['default_country'] = $country->phonecode;
          $result['countries'] = $countriesArray;
        }
      }
      //Country for otp work
      
      //Location work
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
        $cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
        $result['locationData'] = $cookiedata;
      }
      
      //Common Text
      $textCounter = 0;
      $result['localize'] = array("otpFormText" => array('title' => $this->view->translate("Two Step Authentication"), 'description' => $this->view->translate("Please enter the One Time Password (OTP) to complete the verification process."), "button_text" => $this->view->translate("Verify"), "resend_text" => $this->view->translate("Resend"), "expire_text" => $this->view->translate("OTP Expired")));
      
      //if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sessociallogin')){
          //$result['socialLogin'] = $this->socialLogin();
      //}
      
      //Social Login
      if(($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret) || ($settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret)  || ($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) || ($settings->core_linkedin_enable == 'login' && $settings->core_linkedin_access && $settings->core_linkedin_secret)) {
      
      $result['isSocialEnable'] = true;
      if($settings->core_google_enable == 'login' && $settings->core_google_clientid && $settings->core_google_clientsecret) {
        $result['loginWithGmail'] = true;
      }
      if($settings->core_facebook_enable == 'login' && $settings->core_facebook_appid && $settings->core_facebook_secret) {
        $result['loginWithFacebook'] = true;
      }
      if($settings->core_twitter_enable == 'login' && $settings->core_twitter_key && $settings->core_twitter_secret) {
        $result['loginWithTwitter'] = true;
      }
      
      }
      
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }

  public function socialLogin(){
       $settings = Engine_Api::_()->getApi('settings', 'core');
       $returnUrl = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) .Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
       $facebookHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'facebook'), 'default', true);
       $twitterHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'twitter'), 'default', true);
       $linkdinHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'linkedin'), 'default', true);
       $likedinTable = Engine_Api::_()->getDbTable('linkedin', 'sessociallogin');
       $linkedinApi = $likedinTable->getApi();
       $instagramHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'instagram'), 'default', true);
       $instagramTable = Engine_Api::_()->getDbTable('instagram', 'sessociallogin');
      $instagram = $instagramTable->getApi('auth');
       $googleHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'google'), 'default', true);
       $googleTable = Engine_Api::_()->getDbTable('google', 'sessociallogin');
      $google = $googleTable->getApi();
       $pinterestHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'pinterest'), 'default', true);
       $pinterestTable = Engine_Api::_()->getDbTable('pinterest', 'sessociallogin');
       $yahooHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'yahoo'), 'default', true);
       $yahooTable = Engine_Api::_()->getDbTable('yahoo', 'sessociallogin');
       $hotmailHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'hotmail'), 'default', true);
       $hotmailTable = Engine_Api::_()->getDbTable('hotmail', 'sessociallogin');
        $flickrHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'flickr'), 'default', true);
       $flickrTable = Engine_Api::_()->getDbTable('flickr', 'sessociallogin');
       $vkHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'vk'), 'default', true);
       $vkTable = Engine_Api::_()->getDbTable('vk', 'sessociallogin');
       $counter = 0;
       $arrayData = array();
       $returnUrl = "&restApi=Sesapi";
       if(Engine_Api::_()->getDbTable('facebook', 'sessociallogin')->getApi()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Facebook');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$facebookHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'facebook';
          $counter++;
       }
       if('none' != $settings->getSetting('core_twitter_enable', 'none')
    && $settings->core_twitter_secret){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Twitter');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$twitterHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'twitter';
          $counter++;
       }
       if($linkedinApi && $likedinTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Linkedin');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$linkdinHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'linkedin';
          $counter++;
       }
       if($instagramTable->isConnected() && $instagram){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Instagram');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$instagramHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'instagram';
          $counter++;
       }
       if($googleTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Google Plus');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$googleHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'googleplus';
          $counter++;
       }
       if($pinterestTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Pinterest');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$pinterestHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'pinterest';
          $counter++;
       }
       if($yahooTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Yahoo');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$yahooHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'yahoo';
          $counter++;
       }
       if($hotmailTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Hot Mail');
          $arrayData[$counter]['href'] = $this->getBaseUrl(false,$hotmailHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'hotmail';
          $counter++;
       }
       if($flickrTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Flickr');
          $arrayData[$counter]['href'] =  $this->getBaseUrl(false,$flickrHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'flickr';
          $counter++;
       }
       if($vkTable->isConnected()){
          $arrayData[$counter]['title'] = $this->view->translate('Log in with Vkontakte');
          $arrayData[$counter]['href'] =  $this->getBaseUrl(false,$vkHref).'?return_url='.$returnUrl;
          $arrayData[$counter]['name'] = 'vkontakte';
          $counter++;
       }
       return $arrayData;
  }

  //get album categories ajax based.
  public function subcategoryAction() {
    $type = $this->_getParam('type',0);
    $category_id = $this->_getParam('category_id', null);
    $module = $this->_getParam('moduleName','');
     $data = array();
    if ($category_id) {
			$subcategory = Engine_Api::_()->getDbTable('categories', $module)->getModuleSubcategory(array('category_id'=>$category_id,'column_name'=>'*','param'=>$type));
      $count_subcat = engine_count($subcategory->toarray());
      if ($count_subcat > 0)
      $data[""] = "";
      if ($subcategory && $count_subcat) {
        foreach ($subcategory as $category) {
          $data[$category->getIdentity()] = Zend_Registry::get('Zend_Translate')->_($category["category_name"]);
        }
      }
    }
    $result["subcategory"] = $data;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
	// get album subsubcategory ajax based
  public function subsubcategoryAction() {
    $type = $this->_getParam('type',0);
    $category_id = $this->_getParam('subcategory_id', null);
    $module = $this->_getParam('moduleName','');
    $data = array();
    if ($category_id) {
      $subcategory = Engine_Api::_()->getDbTable('categories', $module)->getModuleSubsubcategory(array('category_id'=>$category_id,'column_name'=>'*','param'=>$type));
      $count_subcat = engine_count($subcategory->toarray());
      if ($count_subcat > 0)
      $data[""] = "";
      if ($subcategory && $count_subcat) {
        foreach ($subcategory as $category) {
          $data[$category->getIdentity()] = Zend_Registry::get('Zend_Translate')->_($category["category_name"]);
        }
      }
    }
    $result["subsubcategory"] = $data;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $result));
  }
  public function likeAction(){
    $resource_id = $this->_getParam('resource_id',0);
    $resource_type = $this->_getParam('resource_type',0);
    $type = $this->_getParam('reaction_type',0);
    $notificationType = $actionType = "liked";// $resource_type.'_like';
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    try{
    //make item
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);

      if (!$item) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
      }
    }catch(Exception $e){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $itemTable = Engine_Api::_()->getItemTable($resource_type,$resource_id);
    $tableLike = Engine_Api::_()->getDbTable('likes', 'core');
    $tableMainLike = $tableLike->info('name');

    $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', $resource_type)
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $resource_id);
    $result = $tableLike->fetchRow($select);

    if (isset($result) && !empty($result) && $type == 0) {
      //delete
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $result->delete();
        
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }

      $item->save();
      $subject = $item;
     // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Unliked Successfully"));

    } else {
      if(!$type)
        $type = 1;
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
       if(!$result){
        $like = $tableLike->createRow();
        $like->poster_id = $viewer_id;
        $like->resource_type = $resource_type;
        $like->resource_id = $resource_id;
        $like->type = $type;
        $like->poster_type = 'user';
        $like->save();
        $item->like_count = $item->like_count + 1;
        $item->save();
       }else{
        $like = $result;
        $like->type = $type;
        $like->save();
        $notActivity = true;
       }
       
        //Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
         Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      //Send notification and activity feed work.
      $subject = $item;
      $owner = $subject->getOwner();
	     if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && $actionType && $notificationType && empty($notActivity)) {
	       $activityTable = Engine_Api::_()->getDbTable('actions', 'activity');
	       Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	       Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
	       $result = $activityTable->fetchRow(array('type =?' => $actionType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

	       if (!$result) {
	        $action = $activityTable->addActivity($viewer, $subject, $actionType);
	        if ($action)
	          $activityTable->attachActivity($action, $subject);
	       }
	     }
     // Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Liked Successfully"));
    }
       //if($subject->getType() == "album_photo"){
        $itemTable = Engine_Api::_()->getItemTable($subject->getType(),$subject->getIdentity());
        $tableLike = Engine_Api::_()->getDbTable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $select = $tableLike->select()
              ->from($tableMainLike)
              ->where('resource_type = ?', $subject->getType())
              ->where('poster_id = ?', $viewer_id)
              ->where('poster_type = ?', 'user')
              ->where('resource_id = ?', $subject->getIdentity());
        $resultData = $tableLike->fetchRow($select);
        $response = array();
        if($resultData){
            $response['reaction_type'] = $resultData->type;
        }
        $response['reactionUserData'] = $this->view->FluentListUsers($subject->likes()->getAllLikesUsers(),'',$subject->likes()->getLike($this->view->viewer()),$this->view->viewer());

        $table = Engine_Api::_()->getDbTable('likes','core');
        $select = $table->select()->from($table->info('name'),array('type'=>'type', 'total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$subject->getIdentity());
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')) {
          $recTable = Engine_Api::_()->getDbTable('reactions','comment')->info('name');
          $select->group('type')->setIntegrityCheck(false);
          $select->where('resource_type =?',$subject->getType());
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$table->info("name").'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
        }
        $resultData =  $table->fetchAll($select);

            $response['is_like'] = Engine_Api::_()->sesapi()->contentLike($subject);
            $reactionData = array();
            $reactionCounter = 0;
            if(engine_count($resultData) && Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')){
              foreach($resultData as $type){
                $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['total'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
                $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
                $reactionCounter++;
              }
              $response['reactionData'] = $reactionData;
            }
     // }
     // if(isset($result)){
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $response));
      //}
  }
  protected function _likes($resource_id,$resource_type){
      if($resource_type == "activity_action"){
          $action = Engine_Api::_()->getItem($resource_type,$resource_id);
          if($action){
          $likesGroup = Engine_Api::_()->comment()->likesGroup($action);
          return $likesGroup['data'];
        }
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      if ($resource_type != "activity_action")
          $table = Engine_Api::_()->getDbTable('likes','core');
      else
          $table = Engine_Api::_()->getDbTable('likes','activity');
      $recTable = Engine_Api::_()->getDbTable('reactions','comment')->info('name');
      $select = $table->select()->from($table->info('name'),array('type'=>'type','total'=>new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?',$resource_id)->group('type')->setIntegrityCheck(false);
      if ($resource_type != "activity_action") {
          $select->where('resource_type =?', $resource_type);
          
          $select->setIntegrityCheck(false);
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$table->info("name").'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
      }else{
          $select->where('resource_type =?', $resource_type);
          $select->setIntegrityCheck(false);
          $select->joinLeft($recTable,$recTable.'.reaction_id ='.$table->info("name").'.type',array('file_id'))->where('enabled =?',1)->order('total DESC');
      }
      return $table->fetchAll($select);
  }
	public function checkVersion($android,$ios){
		if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
				return  true;
		if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
				return true;
		return false;
	}
	public function commentsAction(){
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->getItem($resource_type,$resource_id);
    $sesAdv = true;
    
    if($sesAdv){
      $page = $this->_getParam('page',false);
      $comments = array();
      //likes content
      if($page == 1){
          $likes = $this->_likes($resource_id,$resource_type);
          if(engine_count($likes) > 0){
            $counter = 0;
            $total = 0;
            foreach($likes as $reac){
              $comments["comments"]['likes'][$counter]['reaction_id']  = $reac['type'];
              $comments["comments"]['likes'][$counter]['total']  = $reac['total'];
              $total = $total + $reac['total'];
              $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac['file_id'],'','');
              $comments["comments"]['likes'][$counter]['image']  = $icon['main'];
              $counter++;
            }
            $comments["comments"]["like_stats"]['total_likes'] = $total;
            $comments["comments"]["like_stats"]['likes_fluent_list'] = $this->view->FluentListUsers($subject->likes()->getAllLikes(),'',$subject->likes()->getLike($viewer),$viewer);
          }
      }
     }
      $extraParams = $commentsContent = array();
      //get Comments
      $reverseOrder = false;
      $canComment = $subject->getType() == "activity_action" || $subject->getType() == "activity_action" ? true : $subject->authorization()->isAllowed($viewer, 'comment');
      $canDelete = $subject->authorization()->isAllowed($viewer, 'delete');
      $tableComment = Engine_Api::_()->getDbTable('comments','core');
      $tableCommentName = $tableComment->info('name');
      $commentSelect = $subject->comments()->getCommentSelect();
      if($sesAdv){
        if(strpos($commentSelect,'`engine4_activity_comments`') === FALSE){
          $commentsTableName = Engine_Api::_()->getDbTable('comments', 'core')->info('name');
          $commentSelect->setIntegrityCheck(false);
          $commentSelect->where($commentsTableName.'.parent_id =?',0);

        }else{
          $commentsTableName = Engine_Api::_()->getDbTable('comments', 'activity')->info('name');
          $commentSelect->setIntegrityCheck(false);
          $commentSelect->where($commentsTableName.'.parent_id =?',0);
        }
      }
      $commentSelect->reset('order');
      $commentSelect->order('comment_id DESC');
      $paginato = Zend_Paginator::factory($commentSelect);
      $paginato->setCurrentPageNumber($page);
      $paginato->setItemCountPerPage($this->_getParam('limit',5));
      $commentsContent = false;
      $commentsContent = $this->commentsContent($paginato,$subject,true);
      if(engine_count($commentsContent))
      $comments["comment_data"] = $commentsContent;
      $albumenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesalbum');
      $videoenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video') || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesvideo');
      if($sesAdv){
        $comments['reply_comment'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('comment.enablenestedcomments', 1) ? true : false;
      } else {
        $comments['reply_comment'] = false;
      }
        if(_SESAPI_VERSION_IOS && _SESAPI_VERSION_IOS <= 2.2){
            $comments['reply_comment'] = false;
        }
      $comments['can_comment'] = $canComment ? true : false;
      if($sesAdv){
        $attachments = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.enableattachement', '');
        $comments['attachment_options'] = $attachments;
      }
      $comments['can_delete'] = $canDelete ? true : false;
      $comments['enable']['album'] = $albumenable ? 1 : 0;
      $comments['enable']['video'] = $videoenable ? 1 : 0;
      
      $commentSettings = (Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.enableattachement', ''));
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.giphyapi', '') && engine_in_array('gif', $commentSettings)) {
        $comments['enable']['is_gif'] = 1;
      } else {
        $comments['enable']['is_gif'] = 0;
      }

      $comments['enable']['stickers'] = engine_in_array('stickers', $commentSettings);
      $comments['enable']['emojis'] = engine_in_array('emotions', $commentSettings);
      
      $extraParams['pagging']['total_page'] = $paginato->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginato->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginato->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
     Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $comments),$extraParams));
  }
  public function deleteAction(){
    if( !$this->_helper->requireUser()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));
    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbTable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
    // Identify if it's an action_id or comment_id being deleted
    $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
    $this->view->action_id  = $action_id  = (int) $this->_getParam('resource_id', null);
    $resources_type = $this->_getParam('resource_type',false);
    if( $resources_type && $action_id ) {
      $item = Engine_Api::_()->getItem($resources_type, $action_id);
      if( $item instanceof Core_Model_Item_Abstract &&
          (method_exists($item, 'comments') || method_exists($item, 'likes')) ) {
          if( !Engine_Api::_()->core()->hasSubject() ) {
              Engine_Api::_()->core()->setSubject($item);
          }
          //$this->_helper->requireAuth()->setAuthParams($item, $viewer, 'comment');
      }
    }
    if (!$item){
      // tell smoothbox to close
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot delete this item because it has been removed.');
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
    }
    // Send to view script if not POST
    //if (!$this->getRequest()->isPost())
      //Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->message, 'result' => ""));
    if ($comment_id){
        $comment = $item->comments()->getComment($comment_id);
        // allow delete if profile/entry owner
        $db = Engine_Api::_()->getDbTable('comments', 'activity')->getAdapter();
        $db->beginTransaction();
          try {
              $item->comments()->removeComment($comment_id);
              $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
              $db->commit();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => $this->view->message));
          } catch (Exception $e) {
            $db->rollback();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => ""));
          }
    } else {
      // neither the item owner, nor the item subject.  Denied!
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"permission_error", 'result' => ""));
    }  
  }
  public function viewcommentreplyAction()
  {
    // Collect params
    $comment_id = $this->_getParam('comment_id');
    if($this->_getParam('resource_type') == "activity_action"){
        //$comment = Engine_Api::_()->getItem($this->_getParam('resource_type'),$comment_id);
        $action_id = $this->_getParam('activity_id',$this->_getParam('resource_id'));
      $action    = Engine_Api::_()->getDbTable('actions', 'activity')->getActionById($action_id);
    }else{
        //$comment = Engine_Api::_()->getItem($this->_getParam('resource_type'),$comment_id);
      $action_id = $this->_getParam('resource_id');
      $action    = Engine_Api::_()->getItem($this->_getParam('resource_type'),$action_id);
    }
    $page = $this->_getParam('page');
    $viewer    = Engine_Api::_()->user()->getViewer();
    $replies['replies'] = $this->getReplies($action,$comment_id,$page);
    //$replies['comment'] = $comment->toArray();
    $viewMoreData = $this->getReplies($action,$comment_id,$page,true);
    if (engine_count($viewMoreData)){
        $replies['viewMoreReplyData'] = $viewMoreData;
        $replies['viewMoreReplyData']['comment_id'] = $comment_id;
        $replies['viewMoreReplyData']['action_id'] = $action->getIdentity();
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $replies));
  }
  protected function getReplies($subject,$comment_id,$page = "zero",$isPagging = false){
    $commentSelect = $subject->comments()->getCommentSelect();
    if(strpos($commentSelect,'`engine4_activity_comments`') === FALSE){
          $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'core');
          $coreCommentTableName = $coreCommentTable->info('name');
          $select = $coreCommentTable->select()
              ->from($coreCommentTable,'*')
              ->setIntegrityCheck(false);
          $select->where('parent_id =?', $comment_id);
		}else{
          $coreCommentTable = Engine_Api::_()->getDbTable('comments', 'activity');
          $coreCommentTableName = $coreCommentTable->info('name');
          $select = $coreCommentTable->select()
              ->from($coreCommentTable,'*')
              ->setIntegrityCheck(false);
          $select->where('parent_id =?', $comment_id);
		}
    if($page == 'zero'){
       $commentCount = engine_count($select->query()->fetchAll());
       $page = ceil($commentCount/5);
    }
    $select->reset('order');
    $viewMoreReplyData = array();
    $select->order('comment_id DESC');
    $comments = Zend_Paginator::factory($select);
    $comments->setCurrentPageNumber($page);
    $comments->setItemCountPerPage($this->_getParam('limit_data',1));
    if($isPagging && $comments->getCurrentPageNumber() > 1 ):
      if($comment instanceof Activity_Model_Comment){
        $module = 'activity';
      }else{
        $module="core";
      }
     $viewMoreReplyData['module'] = $module;
     $viewMoreReplyData['page'] = $comments->getCurrentPageNumber() - 1;
    endif;
    if($isPagging){
      return $viewMoreReplyData;
    }
    return $this->commentsContent($comments,$subject,false,$viewMoreReplyData);
  }
  protected function commentsContent($comments,$subject,$isComment = false,$viewMoreReplyData = array()){
		$guid = $this->_getParam('guid');
    $array = array();
    $counter = 0;
    
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($comments as $comment){
      $array[$counter] = $comment->toArray();
      $array[$counter]["is_like"] = Engine_Api::_()->sesapi()->contentLike($comment);
      $replies = array();
      if($isComment && $this->checkVersion(3.0,3.3)){
        //if(0){
        //get comment replies
       if($sesAdv)
        $replies  = $this->getReplies($subject,$comment["comment_id"]);
       if(@engine_count($replies)){
         $array[$counter]["replies"] = $replies;
			 }
        $viewMoreData = array();
         if($sesAdv)
          $viewMoreData = $this->getReplies($subject,$comment["comment_id"],'zero',true);
         if (@engine_count($viewMoreData)){
            $array[$counter]['viewMoreReplyData'] = $viewMoreData;
            $array[$counter]['viewMoreReplyData']['comment_id'] = $comment->getIdentity();
            $array[$counter]['viewMoreReplyData']['action_id'] = $subject->getIdentity();
         }
					$likeResult = array();
					$likesGroup = array();
					if($sesAdv)
            $likesGroup = Engine_Api::_()->comment()->commentLikesGroup($comment,false);
					$reactionData = array();
					$reactionCounter = 0;
					if(@engine_count($likesGroup['data'])){
						foreach($likesGroup['data'] as $type){

							$reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)',$type['counts'],Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type['type']));
							$reactionData[$reactionCounter]['url'] = Engine_Api::_()->sesapi()->getBaseUrl(false).$this->view->url(array('module' => 'activity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $comment->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true);
							$reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type['type']));
							$reactionCounter++;
						}
					}
        if($sesAdv) {
					$array[$counter]['reactionUserData'] = $this->view->FluentListUsers($comment->likes()->getAllLikes(),'',$comment->likes()->getLike($this->view->viewer()),$this->view->viewer());;
					if(engine_count($reactionData))
					$array[$counter]['reactionData'] = $reactionData;
        }
      if($likeRow = $comment->likes()->getLike(!empty($guid) ? Engine_Api::_()->getItemByGuid($guid) : Engine_Api::_()->user()->getViewer()) ){
        $type = '';
        $imageLike = '';
        $text = 'Unlike';
        if($likeRow->getType() == 'activity_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        } else if($likeRow->getType() == 'core_like' && $sesAdv) {
          $type = $likeRow->type;
          $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false,Engine_Api::_()->getDbTable("reactions",'comment')->likeImage($type));
          $text = Engine_Api::_()->getDbTable("reactions",'comment')->likeWord($type);
        }
        $likeResult['is_like'] = true;
        $like = true;
      }else{
        $likeResult['is_like'] = false;
        $like = false;
        $type = '';
        $imageLike = '';
        $text = 'Like';
      }

        if(empty($like)) {
            $array[$counter]["like"]["name"] = "like";
        }else {
            $array[$counter]["like"]["name"] = "unlike";
        }
        $array[$counter]["like"]["type"] = $type;
        $array[$counter]["like"]["image"] = $imageLike;
        $array[$counter]["like"]["title"] = $text ? $this->view->translate($text):'';
			}
      //get hashtags from body
      $array[$counter]['hashTags'] = Engine_Api::_()->sesapi()->gethashtags($comment->body);
      //get mention from body
      $array[$counter]['mention'] = Engine_Api::_()->sesapi()->getMentionTags($comment->body);
    if($sesAdv) {
      

      if($comment->file_id){
          $getFilesForComment = Engine_Api::_()->getDbTable('commentfiles','comment')->getFiles(array('comment_id'=>$comment->comment_id));
          $attachmentCounter = 0;
        foreach($getFilesForComment as $fileid){
          if($fileid->type == 'album_photo'){
            try{
              $photo = Engine_Api::_()->getItem('album_photo',$fileid->file_id);
              if($photo){
                $attachPhoto  = Engine_Api::_()->sesapi()->getPhotoUrls($photo,'','');
                if(engine_count($attachPhoto)){
                  $array[$counter]['attachphotovideo'][$attachmentCounter]["images"] = $attachPhoto;
                  $array[$counter]["attachphotovideo"][$attachmentCounter]["id"] = $photo->getIdentity();
                  $array[$counter]['attachphotovideo'][$attachmentCounter]["type"] = "album_photo";
                }else{
                  continue;
                }
              } else {
                continue;
              }
            }catch(Exception $e){
              continue;
          }
          }else{
            try{
             $video = Engine_Api::_()->getItem('video',$fileid->file_id);
             if($video){
               $videoAttach =  Engine_Api::_()->sesapi()->getPhotoUrls($video,'','');
               if(engine_count($videoAttach)){
                $array[$counter]['attachphotovideo'][$attachmentCounter]["images"] = $videoAttach;
                $array[$counter]["attachphotovideo"][$attachmentCounter]["id"] = $video->getIdentity();
                $array[$counter]['attachphotovideo'][$attachmentCounter]["type"] = $video->getType();
                  
                if ($video->type == 3) {
                  if (!empty($video->file_id)) {
                      $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
                      if($storage_file){
                        $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'] = $this->getBaseUrl(false,$storage_file->map());
                        $array[$counter]['attachphotovideo'][$attachmentCounter]['video_extension'] = $storage_file->extension;
                      }
                  }
                }else{
                  $embedded = $video->getRichContent(true,array(),'',true);
                  preg_match('/src="([^"]+)"/', $embedded, $match);
                  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
                    $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'] = str_replace('//','https://',$match[1]);
                  }else{
                    $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'] = $match[1];
                  }
                  if(!empty($array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'])){
                      $dataIframeURL = $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'];
                      if(strpos($dataIframeURL,'youtube') !== false ){
                          if(strpos($dataIframeURL,'?') !== false ){
                            $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'] = $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL']."&feature=oembed";
                          }else{
                            $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL'] = $array[$counter]['attachphotovideo'][$attachmentCounter]['iframeURL']."?feature=oembed";
                          }
                      }
                  }
                }
               }else
                continue;
              } else {
                continue;
              }
            }catch(Exception $e){

            }
          }
          $attachmentCounter++;
        }
      }else if($comment->emoji_id){
        $emoji =  Engine_Api::_()->getItem('comment_emotionfile',$comment->emoji_id);
        if($emoji){
           $photo = Engine_Api::_()->sesapi()->getPhotoUrls($emoji->photo_id,'','');
           $array[$counter]['emoji_image'] = $photo["main"];
        }
      }
			
      if($comment->preview && !$comment->showpreview){
        $link = Engine_Api::_()->getItem('core_link',$comment->preview);
        $array[$counter]['link']['images']  = Engine_Api::_()->sesapi()->getPhotoUrls($link,'','');
        $array[$counter]['link']['href'] = $this->getBaseUrl(false,$link->getHref());
        $array[$counter]['link']['title'] = $link->title;
        $parseUrl = parse_url($link->uri);
        $desc =  str_replace(array('www.','demo.'),array('',''),$parseUrl['host']);
        $array[$counter]['link']['description'] = $desc;
      }
    }
      //user
      if($comment->poster_type == "user"){
        $user = Engine_Api::_()->getItem('user',$comment->poster_id);
        $array[$counter]['user_image'] = $this->userImage($user->getIdentity(),"thumb.profile");
        $user_id = $user->getIdentity();
      }else{
        $user = Engine_Api::_()->getItem($comment->poster_type,$comment->poster_id);
        $array[$counter]['user_image'] = $this->getBaseUrl(true,$user->getPhotoUrl('thumb.profile'));
        $user_id = $user->getParent()->getIdentity();
      }
        $array[$counter]['user_href'] = $this->getBaseUrl(true,$user->getHref());
        $array[$counter]['user_title'] = $user->getTitle($_GET["sesapi_platform"] != 3);
      
      //GIF work
      if($comment && isset($comment->gif_url) && $comment->gif_url) {
        $array[$counter]['gif_url'] = $comment->gif_url;
        $array[$counter]['gif_id'] = 1;
      }
      
      $type = $comment->getType();
      if ($comment->poster_id == $viewer->getIdentity() || $viewer->isAdmin()){
				$array[$counter]["can_delete"] = true;
				$optionCounter = 0;
				if($comment->body){
					$array[$counter]['options'][$optionCounter]['name']= 'edit';
					$array[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Edit');
					$optionCounter++;
				}
				$array[$counter]['options'][$optionCounter]['name']= 'delete';
				$array[$counter]['options'][$optionCounter]['value'] = $this->view->translate('Delete');

     }else{
				$array[$counter]["can_delete"] = false;
     }
      $counter++;
    }
    return $array;
  }
   public function favouriteAction(){
    $resource_id = $this->_getParam('resource_id',0);
    $resource_type = $this->_getParam('resource_type',0);
    $notificationType = $resource_type.'_favourite';
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));

    try{
    //make item
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if (!$item) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
    }
    }catch(Exception $e){
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->getItemfav($resource_type, $resource_id);

    $favItem = Engine_Api::_()->getItemtable($resource_type, $resource_id);
    if (!empty($Fav)) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      $item->favourite_count = $item->favourite_count - 1;
      $item->save();
      if(@$notificationType) {
	      Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
	      Engine_Api::_()->getDbTable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
	      Engine_Api::_()->getDbTable('actions', 'activity')->detachFromActivity($item);
      }
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Unfavourite Successfully"));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', $item->getModuleName())->createRow();
        if($resource_type == "sespage_album" || $resource_type == "sespage_photo" || $resource_type == "sesgroup_album" || $resource_type == "sesgroup_photo" || $resource_type == "sesbusiness_album" || $resource_type == "sesbusiness_photo"){
          $fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        }else{
          $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        }
        $fav->resource_type = $resource_type;
        $fav->resource_id = $resource_id;
        $fav->save();
        $item->favourite_count = $item->favourite_count + 1;
        $item->save();
        // Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      if(@$notificationType && $resource_type != "sesmusic_artist") {
	      $subject = $item;
	      $owner = $subject->getOwner();
	      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
	        $activityTable = Engine_Api::_()->getDbTable('actions', 'activity');
	        Engine_Api::_()->getDbTable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
	        $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
	        if (!$result) {
	          $action = $activityTable->addActivity($viewer, $subject, $notificationType);
	          if ($action)
	            $activityTable->attachActivity($action, $subject);
	        }
	      }
      }
       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => "Item Favourite Successfully"));
    }
  }
  public function commentLikeAction(){
    $viewer = $this->view->viewer();
    if($viewer->getIdentity() == 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array()));
    $resource_id = $this->_getParam('resource_id',"");
    $resource_type = $this->_getParam('resource_type',"");
    if(!$resource_id || !$resource_type)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    $item = Engine_Api::_()->getItem($resource_type,$resource_id);
    if(!$item)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
    //check view privacy
    if (!$this->_helper->requireAuth()->setAuthParams($item, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $canComment = $item->authorization()->isAllowed($viewer, 'comment');
    $canDelete = $item->authorization()->isAllowed($viewer, 'edit');
    $commentLikeStats = array();
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('comment')){
      $recArray = array();
      $reactions = Engine_Api::_()->getDbTable('reactions','comment')->getPaginator();
      $counter = 0;
      foreach($reactions as $reac){
        if(!$reac->enabled)
          continue;
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['reaction_id']  = $reac['reaction_id'];
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['title']  = $this->view->translate($reac['title']);
        $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id,'','');
        $commentLikeStats["stats"]['reaction_plugin'][$counter]['image']  = $icon['main'];
        $counter++;
      }
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();
      if($viewer_id){
          $itemTable = Engine_Api::_()->getItemTable($resource_type,$resource_id);
          $tableLike = Engine_Api::_()->getDbTable('likes', 'core');
          $tableMainLike = $tableLike->info('name');

          $select = $tableLike->select()
              ->from($tableMainLike)
              ->where('resource_type = ?', $resource_type)
              ->where('poster_id = ?', $viewer_id)
              ->where('poster_type = ?', 'user')
              ->where('resource_id = ?', $resource_id);
          $select->setIntegrityCheck(false);

          $result = $tableLike->fetchRow($select);
        if($result){
            $commentLikeStats['stats']['reaction_type'] = $result->type;
        }
      }
      $commentLikeStats['stats']['comment_Count'] = (int) Engine_Api::_()->comment()->commentCount($item,'subject');
    }
    $type = $resource_type; //"user_id";
    $id = "user_id";
    if($resource_type == "sespage_album"){
      $type = "sespage_album";
      $id = "owner_id";
    }else if($resource_type == "sespage_photo"){
      $type = "sespage_photo";
      $id = "owner_id";
    }else if($resource_type == "sesgroup_album"){
      $type = "sesgroup_album";
      $id = "owner_id";
    }else if($resource_type == "sesgroup_photo"){
      $type = "sesgroup_photo";
      $id = "owner_id";
    }else if($resource_type == "sesbusiness_album"){
      $type = "sesbusiness_album";
      $id = "owner_id";
    }else if($resource_type == "sesbusiness_photo"){
      $type = "sesbusiness_photo";
      $id = "owner_id";
    }
   
    $commentLikeStats['stats']['is_like'] = Engine_Api::_()->sesapi()->contentLike($item); 
    $commentLikeStats['stats']['like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
    if(isset($item->favourite_count)){
      $commentLikeStats['stats']['is_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($item,'favourites',$item->getModuleName(),$type,$id);
      $commentLikeStats['stats']['favourite_count'] = (int) Engine_Api::_()->sesapi()->getContentFavouriteCount($item,'favourites',$item->getModuleName(),$type,$id);
    }
    $commentLikeStats['stats']['can_comment'] = $canComment ? true : false;
    $commentLikeStats['stats']['can_delete'] = $canDelete ? true : false;
    $commentLikeStats['stats']['loggedin'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $commentLikeStats));
  }


  public function createAction()
  {
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: POST, GET');
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
    ini_set("memory_limit","240M");
    $guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if(!$guid)
        $guid = "";
    }else{
        $guid = "";
    }
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));
    
    // Not post
    $subject_id = $this->_getParam('resource_id',false);
    $subject_type = $this->_getParam('resource_type',false);
    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
        $sesAdv = true;
    }else{
        if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
            $sesAdv = false;
        }
    }
    if($sesAdv)
	    $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    else
      $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    // Start transaction
    if(!$subject_id)
      $db = $actionTable->getAdapter();
    else{
      $action = Engine_Api::_()->getItem($subject_type,$subject_id);
      $db = Engine_Api::_()->getItemtable($action->getType())->getAdapter();
    }
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $action_id = $this->view->action_id = $this->_getParam('activity_id', $this->_getParam('action_id', null));
      if(!$subject_id){
       $action = $actionTable->getActionById($action_id);
       $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
      }else{
        //$action = Engine_Api::_()->getItem($subject_type,$subject_id);
        $actionOwner = $action->getOwner();
      }
      if (!$action) {
        $this->view->status = false;
        $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $this->_getParam('body',$_POST['body']);
      //Emojis Work
      // if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
      //   $bodyEmojis = explode(' ', $body);
      //   foreach($bodyEmojis as $bodyEmoji) {
      //     $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
      //     $body = str_replace($bodyEmoji,$emojisCode,$body);
      //   }
      // }
      //Emojis Work End
      $emoji_id = isset($_POST['emoji_id']) ? $_POST['emoji_id'] : "";
      // Check authorization
      if (!$subject_id && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment'))
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('This user is not allowed to comment on this item.'), 'result' => array()));
      $photoupload  = array();

      // If we're here, we're done
      if(!empty($_FILES["attachmentImage"]) && engine_count($_FILES["attachmentImage"]) > 0 && $sesAdv){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
           $counter = 0;
           foreach($_FILES['attachmentImage']['name'] as $key=>$image){
              $uploadimage = array();
              if ($_FILES['attachmentImage']['name'][$key] == "")
               continue;
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$key];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$key];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$key];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$key];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$key];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $photoupload[$counter] = $photo->getIdentity().'_album_photo';
            $counter++;
          }
          }catch(Exception $e){
            $this->view->error =  $e->getMessage();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
          }
      }
      if(isset($_POST['video']) && !empty($_POST['video']) && $sesAdv){
        $counter = 0;
        $uploadData = array();
         ksort($_POST['video']);
        foreach($_POST['video'] as $video){
          if($video == "photo"){
             if(!empty($photoupload[$counter]))
              $uploadData[] = $photoupload[$counter];
             $counter++;
          }else{
             $uploadData[] = $video;
          }
        }
      }
      if($sesAdv) {
        if(isset($uploadData) && !empty($uploadData) ){
          $uploadData = array_filter($uploadData, 'strlen');
          $_POST['file_id'] = implode(',',$uploadData);
        }else if(engine_count($photoupload)){
          $uploadData = array_filter($uploadData, 'strlen');
          $_POST['file_id'] = implode(',',$photoupload);
        }
      }
      // Add the comment
      if(!$body)
        $body = "";
      // $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
      // $bodyEmojis = explode(' ', $body);
      // foreach($bodyEmojis as $bodyEmoji) {
      //   $emojisCode = Engine_Api::_()->sesapi()->encode($bodyEmoji);
      //   $body = str_replace($bodyEmoji,$emojisCode,$body);
      // }
      $comment =  $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();
      
      $comment = Engine_Api::_()->getItem($typeC,$comment->comment_id);
      if(isset($_POST['file_id'])){
        $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
      } else {
        $file_id = "";
      }
      if(!empty($file_id) && $file_id && $file_id != ''){
        $counter = 1;
        $file_ids = explode(',',$file_id);
        $tableCommentFile = Engine_Api::_()->getDbTable('commentfiles', 'comment');
        foreach($file_ids as $file_id){
          if(!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if(strpos($file_id,'_album_photo')){
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo','',$file_id);
          }else{
            $file->type = 'video';
            $file->file_id = str_replace('_video','',$file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();
          if($counter == 1){
            $comment->file_id = $file_id;
            $comment->save();
          }
          $counter++;
        }
      }
      if(!empty($emoji_id) && $emoji_id){
        $comment->emoji_id = $emoji_id;
        $comment->file_id = 0;
        $comment->body = '';
        $comment->save();
      }
      
      //GIF Work
      if(isset($_POST['image_id']) && $_POST['image_id']) {
        $comment->gif_id = 1;
        $comment->gif_url = $_POST['image_id'];
        $gifImageUrl = $_POST['image_id'];
        $bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">' , $gifImageUrl , $gifImageUrl);
        $comment->body = $bodyGif;
        $comment->save();
      }
      
      //sespage comment
      if($guid){
        $comment->poster_type = $guid->getType();
        $comment->poster_id = $guid->getIdentity();
        $comment->save();
        Engine_Hooks_Dispatcher::getInstance()->callEvent('onCommentCreateAfter', $comment);
      }
      //fetch link from comment
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if(!empty($matches[0])){
        $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
        if($preview){
          $comment->preview = $preview;
          $comment->save();
        }
      }
      // Notifications
      if($sesAdv)
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      else 
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
      // Add notification for owner of activity (if user and not viewer)
      if( (!$subject_id && $action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) || ($subject_id && !$viewer->isSelf($actionOwner)) )
      {
        $notifyApi->addNotification($actionOwner, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'commented', array(
          'label' => 'post'
        ));
      }
      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->comments()->getAllCommentsUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'commented_commented', array(
            'label' => 'post'
          ));
        }
      }
      // Add a notification for all users that commented or like except the viewer and poster
      // @todo we should probably limit this
      foreach( $action->likes()->getAllLikesUsers() as $notifyUser )
      {
        if( $notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity() )
        {
          $notifyApi->addNotification($notifyUser, !empty($guid) ? $guid->getOwner() : $viewer, $action, 'liked_commented', array(
            'label' => 'post'
          ));
        }
      }
      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/', $_POST['body'], $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "comment" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
        if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id_reply = $itemArray[engine_count($itemArray) - 1];
          unset($itemArray[engine_count($itemArray) - 1]);
          $resource_type_reply = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply,$resource_id_reply);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid : $viewer, $viewer, 'comment_tagged_people', array("commentLink" => $commentLink,'resource_type'=>$subject_type,'resource_id'=>$subject_id));
      }
      //Tagging People by status box
      // Stats
      Engine_Api::_()->getDbTable('statistics', 'core')->increment('core.comments');

      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_($e);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $commentContent = $this->commentsContent(array($comment),$action);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));
  }
  public function replyAction()
  {
    ini_set("memory_limit","240M");
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));


    // Not post
    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
    }

		$guid = $this->_getParam('guid',0);
    if($guid){
      $guid = Engine_Api::_()->getItemByGuid($guid);
      if(!$guid)
        $guid = "";
    }else{
        $guid = "";
    }

    // Start transaction
    $db = Engine_Api::_()->getDbTable('actions', 'activity')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $resource_type = $this->_getParam('resource_type',false);
      if(!$resource_type){
        $action_id = $this->view->action_id = $this->_getParam('resource_id', $this->_getParam('action', null));
        $action = Engine_Api::_()->getDbTable('actions', 'activity')->getActionById($action_id);
        $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
      }else{
        $action = Engine_Api::_()->getItem($resource_type,$this->_getParam('resource_id'));
        $actionOwner = $action->getOwner();
      }

      if (!$action) {
        $this->view->status = false;
        $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
      }
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $_POST['body'];
      $body = Engine_Api::_()->sesapi()->encode($body);
      // Check authorization
      if (!$resource_type && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')){
        $this->view->error = 'This user is not allowed to comment on this item.';
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }

      $photoupload  = array();
      // If we're here, we're done
      if(!empty($_FILES["attachmentImage"]) && engine_count($_FILES["attachmentImage"]) > 0){
           // Get album
          $viewer = Engine_Api::_()->user()->getViewer();
          $table = Engine_Api::_()->getItemTable('album');
          $type = 'wall';
          $album = $table->getSpecialAlbum($viewer, $type);
          $photoTable = Engine_Api::_()->getItemTable('photo');
          $auth = Engine_Api::_()->authorization()->context;
          try{
           $counter = 0;
           foreach($_FILES['attachmentImage']['name'] as $key=>$image){
              $uploadimage = array();
              if ($_FILES['attachmentImage']['name'][$key] == "")
               continue;
              $uploadimage["name"] = $_FILES['attachmentImage']['name'][$key];
              $uploadimage["type"] = $_FILES['attachmentImage']['type'][$key];
              $uploadimage["tmp_name"] = $_FILES['attachmentImage']['tmp_name'][$key];
              $uploadimage["error"] = $_FILES['attachmentImage']['error'][$key];
              $uploadimage["size"] = $_FILES['attachmentImage']['size'][$key];
              $photo = $photoTable->createRow();
              $photo->setFromArray(array(
                  'owner_type' => 'user',
                  'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
              ));
              $photo->save();
              $photo->setPhoto($uploadimage);
              $photo->order = $photo->photo_id;
              $photo->album_id = $album->album_id;
              $photo->save();
              if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
              }
              // Authorizations
              $auth->setAllowed($photo, 'everyone', 'view', true);
              $auth->setAllowed($photo, 'everyone', 'comment', true);
              $photoupload[$counter] = $photo->getIdentity().'_album_photo';
            $counter++;
          }
          }catch(Exception $e){

            $this->view->error =  $e->getMessage();
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error));
          }
      }

      if(engine_count($_POST['video'])){
        $counter = 0;
        $uploadData = array();
         ksort($_POST['video']);
        foreach($_POST['video'] as $video){
          if($video == "photo"){
             if(!empty($photoupload[$counter]))
              $uploadData[] = $photoupload[$counter];
             $counter++;
          }else{
             $uploadData[] = $video;
          }
        }
      }
      if(engine_count($uploadData)){
        $uploadData = array_filter($uploadData, 'strlen');
        $_POST['file_id'] = implode(',',$uploadData);
      }else if(engine_count($photoupload)){
        $uploadData = array_filter($uploadData, 'strlen');
        $_POST['file_id'] = implode(',',$photoupload);
      }

      // Add the comment
      if(!$body)
        $body = "";
      $comment =  $action->comments()->addComment($viewer, $body);
      $typeC = $comment->getType();

			

      $comment = Engine_Api::_()->getItem($typeC,$comment->comment_id);
       $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
      if($file_id && $file_id != ''){
        $counter = 1;
        $file_ids = explode(',',$file_id);
        $tableCommentFile = Engine_Api::_()->getDbTable('commentfiles', 'comment');
        foreach($file_ids as $file_id){
          if(!$file_id)
            continue;
          $file = $tableCommentFile->createRow();
          if(strpos($file_id,'_album_photo')){
            $file->type = 'album_photo';
            $file->file_id = str_replace('_album_photo','',$file_id);
          }else{
            $file->type = 'video';
            $file->file_id = str_replace('_video','',$file_id);
          }
          $file->comment_id = $comment->getIdentity();
          $file->save();
          if($counter == 1){
						
            $comment->file_id = $file_id;
            $comment->save();
          }
          $counter++;
        }
      }
       
      $emoji_id = $_POST['emoji_id'];
      if($emoji_id){
          $comment->emoji_id = $emoji_id;
          $comment->file_id = 0;
          $comment->body = '';
          $comment->save();
      }

      //GIF Work
      if(isset($_POST['image_id']) && $_POST['image_id']) {
        $comment->gif_id = 1;
        $comment->gif_url = $_POST['image_id'];
        $comment->save();
        $gifImageUrl = $_POST['image_id'];
        $bodyGif = sprintf('<img src="%s" class="giphy_image" alt="%s">' , $gifImageUrl , $gifImageUrl);
        $comment->body = $bodyGif;
        $comment->save();
      }

			  //sespage comment
      if($guid){
        if(isset($comment->poster_type)){
          $comment->poster_type = $guid->getType();
          $comment->poster_id = $guid->getIdentity();
          $comment->save();
        }
      }

			$gif_id = $_POST['gif_id'];
      if($gif_id){
          $comment->gif_id = $gif_id;
          $comment->file_id = 0;
          $comment->save();
        $comment->body = '';
        $comment->save();
        $image = Engine_Api::_()->getItem('sesfeedgif_image', $gif_id);
        $image->user_count++;
        $image->save();
      }


      $parentCommentType = 'core_comment';
      if($action->getType() == 'activity_action' || $action->getType() == 'activity_action'){
        $commentType = $action->likes(true);
        if($commentType->getType() == 'activity_action' || $action->getType() == 'activity_action')
          $parentCommentType = 'activity_comment';
      }

       $parentCommentId = $this->_getParam('comment_id',false);
      

      $parentComment = Engine_Api::_()->getItem($parentCommentType,$parentCommentId);
      $parentComment->reply_count = new Zend_Db_Expr('reply_count + 1');
      $parentComment->save();
      $comment->parent_id = $parentCommentId;
      $comment->save();
      //fetch link from comment
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $matches);
      if(!empty($matches[0])){
        $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
        if($preview){
          $comment->preview = $preview;
          $comment->save();
        }
      }

			// Notifications
      // Comment Reply notification to comment owner
      if($parentComment->poster_type == 'user' && $parentComment->poster_id != $viewer->getIdentity()) {
        $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
        $user = Engine_Api::_()->getItem('user', $parentComment->poster_id);
        $notifyApi->addNotification($user, !empty($guid) ? $guid : $viewer, $action, 'comment_replycomment', array('label' => 'post'));
      }else{
        $type = $parentComment->poster_type;
        $id = $parentComment->poster_id;
        $commentItem = Engine_Api::_()->getItem($type,$id);
        if($commentItem){
          $commentUser = $commentItem->getOwner();
          if($commentUser && $commentUser->getIdentity() != $viewer->getIdentity()){
            $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
            $notifyApi->addNotification($commentUser, !empty($guid) ? $guid : $viewer, $action, 'comment_replycomment', array('label' => 'post'));
            $viewer = $commentUser;
          }
        }
      }

      //Tagging People by status box
      preg_match_all('/(^|\s)(@\w+)/',$body, $result);
      $commentLink = '<a href="' . $comment->getHref() . '">' . "reply" . '</a>';
      foreach($result[2] as $value) {
        $user_id = str_replace('@_user_','',$value);
       if(intval($user_id)>0){
          $item = Engine_Api::_()->getItem('user',$user_id);
          if(!$item || !$item->getIdentity())
           continue;
        }else{
          $itemArray = explode('_',$user_id);
          $resource_id_reply = $itemArray[engine_count($itemArray) - 1];
          unset($itemArray[engine_count($itemArray) - 1]);
          $resource_type_reply = implode('_',$itemArray);
          $item = Engine_Api::_()->getItem($resource_type_reply,$resource_id_reply);
          if(!$item || !$item->getIdentity())
            continue;
          $item = $item->getOwner();
          if(!$item || !$item->getIdentity())
           continue;
        }
        
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($item, !empty($guid) ? $guid : $viewer, $viewer, 'comment_taggedreply_people', array("commentLink" => $commentLink,'resource_type'=>$action->getType(),'resource_id'=>$action->getIdentity()));
      }
      //Tagging People by status box
      $db->commit();

    }
    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->error  = Zend_Registry::get('Zend_Translate')->_($e);
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    // Assign message for json
    $this->view->status = true;
    $this->view->message = 'Comment posted';

     $commentContent = $this->commentsContent(array($comment),$action);

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));

  }

	 public function editAction()
  {
    // Make sure user exists
    if( !$this->_helper->requireUser()->isValid() )
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Invalid Request"), 'result' => array()));

    // Not post
     if( !$this->getRequest()->isPost() )
      {
       $this->view->status = false;
      $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->error, 'result' => array()));
      }
      $resource_id = $this->_getParam('resource_id','');
      $resource_type = $this->_getParam('resource_type','');
      $comment_id = $this->view->comment_id = $this->_getParam('comment_id', null);
      $module = $this->_getParam('modulecomment','');
      $item = Engine_Api::_()->getItem('activity_action',$resource_id);
      $type = Engine_Api::_()->getDbTable('actionTypes',"activity")->getActionType($item->type);
      if($item->comments()->getSender()->getType() != 'activity_action'){
        $comment = Engine_Api::_()->getItem('core_comment',$comment_id);
      }else if($resource_type == 'activity_action' || $resource_type == 'activity_action')
        $comment = Engine_Api::_()->getItem('activity_comment',$comment_id);
      else
        $comment = Engine_Api::_()->getItem('core_comment',$comment_id);

      //previous body
      $regex = '/https?\:\/\/[^\" ]+/i';
      $string = $comment->body;
      preg_match($regex, $string, $previousmatches);
      $body = !empty($_POST['bodymention']) ? $_POST['bodymention'] : $this->_getParam('body',$_POST['body']);


      //Feeling Emojis Work
      if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Api::_()->sesemoji()->EncodeEmoji($bodyEmoji);
          $body = str_replace($bodyEmoji,$emojisCode,$body);
        }
      }
      //Feeling Emojis Work End
      $comment->body = $body;
      $comment->save();
      $sesAdv = false;
      if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('activity')) {
          $sesAdv = true;
      }else{
          if ((_SESAPI_VERSION_IOS > 1.7 && _SESAPI_PLATFORM_SERVICE == 1) || (_SESAPI_VERSION_ANDROID > 3.1 && _SESAPI_PLATFORM_SERVICE == 2)) {
              $sesAdv = false;
          }
      }
      if($sesAdv) {
        

        $execute = false;
        $file_id = trim(str_replace(',,','',$_POST['file_id']),',');
        if($file_id && $file_id != ''){
          $counter = 1;
          $file_ids = explode(',',$file_id);
          $tableCommentFile = Engine_Api::_()->getDbTable('commentfiles', 'comment');
          $tableCommentFile->delete(array('comment_id =?'=>$comment->comment_id));
          foreach($file_ids as $file_id){
            if(!$file_id)
              continue;
            $file = $tableCommentFile->createRow();
            if(strpos($file_id,'_album_photo')){
              $file->type = 'album_photo';
              $file->file_id = str_replace('_album_photo','',$file_id);
            }else{
              $file->type = 'video';
              $file->file_id = str_replace('_video','',$file_id);
            }
            $file->comment_id = $comment->getIdentity();
            $file->save();
            if($counter == 1){
              $comment->file_id = $file_id;
              $comment->save();
            }
            $execute = true;
            $counter++;
          }
        }
        if(!$execute)
        {
          $comment->file_id = 0;
        }
        $emoji_id = $_POST['emoji_id'];
        if($emoji_id){
          $comment->emoji_id = $emoji_id;
          $comment->file_id = 0;
          $comment->body = '';
          $comment->save();
        }
        $comment->save();
        //fetch link from comment
        $regex = '/https?\:\/\/[^\" ]+/i';
        $string = $comment->body;
        preg_match($regex, $string, $matches);

        if(!empty($matches[0]) && $previousmatches != $matches){
          $viewer = Engine_Api::_()->user()->getViewer();
          $preview = $this->previewCommentLink($matches[0],$comment,$viewer);
          if($preview){
            $comment->preview = $preview;
            $comment->save();
          }
        }else if(empty($matches[0]) && $comment->preview){
            $comment->preview = 0;
            $comment->save();
            $link = Engine_Api::_()->getItem('core_link',$comment->preview);
            $link->delete();
        }
      }
    $commentContent = $this->commentsContent(array($comment),$action);
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('comment_data'=>$commentContent[0])));
  }
   public function previewCommentLink($url,$comment,$viewer){

         $contentLink = Engine_Api::_()->comment()->getMetaTags($url);
         if(!empty($contentLink['title']) && !empty($contentLink['image'])){
            $image = $contentLink['image'];
            $title = $contentLink['title'];
            if(strpos($contentLink['image'],'http') === false){
              $parseUrl = parse_url($url);
              $image = $parseUrl['scheme'].'://'.$parseUrl['host'].'/'.ltrim($contentLink['image'],'/');
            }
         }
          $table = Engine_Api::_()->getDbTable('links', 'core');
          $link = $table->createRow();
          $data['uri'] = $url;
          $data['title'] = $title;
          $data['parent_type']  = $comment->getType();
          $data['parent_id']  = $comment->getIdentity();
          $data['search']  = 0;
          $data['photo_id']  = 0;
          $link->setFromArray($data);
          $link->owner_type = $viewer->getType();
          $link->owner_id = $viewer->getIdentity();
          $thumbnail = (string) @$image;
          $thumbnail_parsed = @parse_url($thumbnail);
          if( $thumbnail && $thumbnail_parsed ){
            $tmp_path = APPLICATION_PATH . '/temporary/link';
            $tmp_file = $tmp_path . '/' . md5($thumbnail);
              if( is_dir($tmp_path) ) {
                $src_fh = fopen($thumbnail, 'r');
                $tmp_fh = fopen($tmp_file, 'w');
                stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                fclose($src_fh);
                fclose($tmp_fh);
                if( ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
                  $ext = Engine_Image::image_type_to_extension($info[2]);
                  $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;
                  $image = Engine_Image::factory();
                  $image->open($tmp_file)
                    ->autoRotate()
                    ->resize(500, 500)
                    ->write($thumb_file)
                    ->destroy();
                  $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                    'parent_type' => $link->getType(),
                    'parent_id' => $link->getIdentity()
                  ));
                  $link->photo_id = $thumbFileRow->file_id;
                  @unlink($thumb_file);
                  @unlink($tmp_file);
                  $link->save();
                  return $link->getIdentity();
                }
              }
          }
        return false;
   }
   function removeLocationAction(){
    $location = $this->_getParam('location_data');
    $lat = $this->_getParam('location_lat');
    $lng = $this->_getParam('location_lng');
    $_SESSION["location_data"] = "";
    setcookie('location_data', $location, time() - (30 * 24*60*60*1000), "/");
    setcookie('location_lat', $lat, time() - (30 * 24*60*60*1000), "/");
    setcookie('location_lng', $lng, time() - (30 * 24*60*60*1000), "/");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success'=>1)));

  }

  function setLocationAction(){
    if(!empty($_GET['resposneSend'])){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('success'=>1)));
    }
    $location = $this->_getParam('location_data');
    $lat = $this->_getParam('location_lat');
    $lng = $this->_getParam('location_lng');

    $_SESSION["location_data"] = $location;
    $_SESSION["location_lat"] = $lat;
    $_SESSION["location_lng"] = $lng;

    setcookie('location_datatetete', $location, time() + (86400 * 365), "/");
    setcookie('location_data', $location, time() + (86400 * 365), "/");
    setcookie('location_lat', $lat, time() + (86400 * 365), "/");
    setcookie('location_lng', $lng, time() + (86400 * 365), "/");
    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    header("Location:".$actual_link.'&resposneSend=1');
  }
  
  public function getLocationAction() {
    $result['locationData'] = array();
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1) {
      $cookiedata = Engine_Api::_()->getApi('location', 'core')->getUserLocationBasedCookieData();
      $result['locationData'] = $cookiedata;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result));
  }
}
