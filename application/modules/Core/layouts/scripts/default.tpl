<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: default.tpl 10227 2014-05-16 22:43:27Z andres $
 * @author     John
 */
?>
<?php
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $http_https = _ENGINE_SSL ? 'https://' : 'http://';
    $counter = (int) $this->layout()->counter;
    $staticBaseUrl = $this->layout()->staticBaseUrl;
    $headIncludes = $this->layout()->headIncludes;
    
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->headTitle()
        ->setSeparator(' - ');
    //Page Data
    $pageName = $request->getModuleName() . '_' . $request->getActionName() . '_' . $request->getControllerName();
    $pageInfo = Engine_Api::_()->getDbtable('pages', 'core')->getPageInfo(array('name' => $pageName));
    if(!empty($pageInfo))
    $page_id = $pageInfo->page_id;
    
    $pageTitleKey = 'pagetitle-' . $request->getModuleName() . '-' . $request->getActionName() . '-' . $request->getControllerName();
    $pageTitle = $this->translate($pageTitleKey);
    
    if ($pageTitle && $pageTitle != $pageTitleKey) {
      $this->headTitle($pageTitle, Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
    }
    
    $this->headTitle($this->translate($this->layout()->siteinfo['title']));
    
    $this->headMeta()
        ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
        ->appendHttpEquiv('Content-Language', $this->locale()->getLocale()->__toString());

    // Make description and keywords
    $description = $this->layout()->siteinfo['description'];
    $keywords = $this->layout()->siteinfo['keywords'];

    if ($this->subject() && $this->subject()->getIdentity()) {
      $this->headTitle(strip_tags($this->subject()->getTitle()), Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);

      $description = strip_tags($this->subject()->getDescription()) . ' ' . $description;
      // Remove the white space from left and right side
      $keywords = trim($keywords);
      if (!empty($keywords) && (strrpos($keywords, ',') !== (strlen($keywords) - 1))) {
          $keywords .= ',';
      }
      $keywords .= $this->subject()->getKeywords(',');
    }

    $keywords = trim($keywords, ',');
    
    $pageUrl = $http_https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $themeFontSize = !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] ? $_SESSION['font_theme'] : "";
    $bodyClass = $htmlClass = "";
    if (!$this->viewer()->getIdentity()){
        $bodyClass .= "guest-user";
    }
    
    $contrast_mode = $settings->getSetting('contrast.mode', 'dark_mode');
    $themeModeColor = !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] ? $_SESSION['mode_theme'] : "";
    if($contrast_mode == 'dark_mode' && $themeModeColor == 'dark_mode') {
      $htmlClass .= " ".$themeModeColor;
    } else if($contrast_mode == 'light_mode' && $themeModeColor == 'light_mode') {
      $htmlClass .= " ".$themeModeColor;
    }
    
    if (isset($this->layout()->siteinfo['identity'])) {
        $identity = $this->layout()->siteinfo['identity'];
    } else {
        $identity = $request->getModuleName() . '-' .
            $request->getControllerName() . '-' .
            $request->getActionName();
    }
?>
<?php $locale = $this->locale()->getLocale()->__toString(); $orientation = ($this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr'); ?>
<?php $headerContent = $this->content('header'); ?>
<?php $footerContent = $this->content('footer'); ?>
<?php

 if(!empty($_GET['getContentOnly']) && !empty($_SERVER['HTTP_REFERER'])) {
    echo $this->hooks('onRenderLayoutDefault', $this);

    // Process
    foreach ($this->headScript()->getContainer() as $dat) {
        if (!empty($dat->attributes['src'])) {
            $dat->attributes['src'] = "remove";
        }
    }
    $metaTags =  '<div id="script-default-data" style="display:none">
    <div id="script-page-url">'.$_SERVER["REQUEST_URI"].'</div>
    <div id="script-page-id">global_page_'.$identity.'</div>
    <div id="header-orientation">'.$orientation.'</div>
    <div id="header-locale">'.$locale.'</div>
    <div id="script-page-class">'.$bodyClass.'</div>
    <div id="script-page-title">'.strip_tags($this->headTitle()->toString()).'</div>
    </div>';
    $metaTags .= $this->headScript()->toString();

    if ($this->subject()){
       $metaTags .='<script type="application/javascript">en4.core.subject = {
            type : "'.$this->subject()->getType().'",
            id : "'.$this->subject()->getIdentity().'",
            guid : "'.$this->subject()->getGuid().'"
        };</script>';
    }else{
        $metaTags .= '<script type="application/javascript">en4.core.subject = {type:"",id:0,guid:""}</script>';
    }
    if ($this->viewer()->getIdentity()){
       $metaTags .='<script type="application/javascript">en4.user.viewer = {
            type : "'.$this->viewer()->getType().'",
            id : "'.$this->viewer()->getIdentity().'",
            guid : "'.$this->viewer()->getGuid().'"
        };</script>';
    }else{
        $metaTags .= '<script type="application/javascript">en4.user.viewer = {}</script>';
    }

    if(!empty($_GET['getFullContent'])){ 
        $fullContent = '<div id="global_header">
            '.$headerContent.'
        </div>
        <div id="global_wrapper">
            <div id="global_content">
                
                <span id="show-sidebar"><span><i class="fa fa-angle-down"></i></span></span>
                '.$this->layout()->content.'
            </div>
        </div>
        <div id="global_footer">
            '.$footerContent.'
        </div><div id="append-script-data"></div>';

        echo $metaTags.$fullContent;die;
    }

    echo '<div id="global_content"><span id="show-sidebar"><span><i class="fa fa-angle-down"></i></span></span>'.$metaTags.$this->layout()->content.'</div>';die;
 } 
 
 if (APPLICATION_ENV == 'development') {
    Engine_Api::_()->core()->generateJsCss();
 }

?>
<?php echo $this->doctype()->__toString() ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>" dir="<?php echo $orientation ?>" class="<?php echo $htmlClass; ?>" <?php if ($themeFontSize): ?> style="font-size: <?php echo $themeFontSize; ?>"<?php endif; ?>>
<head>
    <base href="<?php echo rtrim($this->serverUrl($this->baseUrl()), '/'). '/' ?>" />


    <?php // ALLOW HOOKS INTO META?>
    <?php echo $this->hooks('onRenderLayoutDefault', $this) ?>


    <?php // TITLE/META?>
    <?php
    

    $this->headMeta()->appendName('description', trim($description));
    $this->headMeta()->appendName('keywords', trim($keywords));
    $this->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');
    
    if(!empty($page_id)) {
      //Roboto tag
      if($pageInfo->roboto_tags == 1) {
          $view->headMeta()->setProperty('robots', 'index, follow');
      } elseif($pageInfo->roboto_tags == 2) {
          $view->headMeta()->setProperty('robots', 'index, nofollow');
      } elseif($pageInfo->roboto_tags == 3) {
          $view->headMeta()->setProperty('robots', 'noindex, follow');
      } elseif($pageInfo->roboto_tags == 4) {
          $view->headMeta()->setProperty('robots', 'noindex,nofollow');
      }

      //Add custom tags
      if(!empty($pageInfo->meta_tags)) {
        $view->layout()->headIncludes = $pageInfo->meta_tags;
      }

      //Add Image
      if (!empty($pageInfo->meta_image)) {
        $image = Engine_Api::_()->core()->getFileUrl($pageInfo->meta_image);
        //$view->doctype('XHTML1_RDFA');
        $view->headMeta()->setProperty('og:image', $image);
        $view->headMeta()->setProperty('twitter:image',$image);
      }
    }
    
    //OG Meta Tags for facebook
    $this->headMeta()->setProperty('og:locale', $view->locale()->getLocale()->__toString());
    $this->headMeta()->setProperty('og:type', "website");
    $this->headMeta()->setProperty('og:url', $pageUrl);
    $this->headMeta()->setProperty('og:title', strip_tags($this->headTitle()->toString()));
    $this->headMeta()->setProperty('og:description', trim($description));
    
    //OG Meta Tags for twitter
    $this->headMeta()->setProperty('twitter:card', 'summary_large_image');
    $this->headMeta()->setProperty('twitter:url', $pageUrl);
    $this->headMeta()->setProperty('twitter:title', strip_tags($this->headTitle()->toString()));
    $this->headMeta()->setProperty('twitter:description', trim($description));
    if(!empty($page_id) && !empty($pageInfo->meta_image)) {
      $view->headMeta()->setProperty('twitter:image',$image);
    }

    //Adding open graph meta tag for video thumbnail
    if ($this->subject() && $this->subject()->getPhotoUrl()) {
      $this->headMeta()->setProperty('og:image', $this->absoluteUrl($this->subject()->getPhotoUrl()));
      
      //OG Meta Tags for twitter
      $this->headMeta()->setProperty('twitter:image', $this->absoluteUrl($this->subject()->getPhotoUrl()));
    }
    
    //Hreflang is an HTML <link> or <link> tag attribute that tells search engines the relationship between pages in different languages on your website. Google uses the attribute to serve the correct regional or language URLs in its search results based on the searcher's country and language preferences.
    $languages = Engine_Api::_()->getApi('languages', 'core')->getLanguages();
    if(engine_count($languages) > 1) {
      foreach($languages as $key => $language) {
        $view->headLink(array('rel' => "alternate", 'hreflang' => $key, 'href' => $view->absoluteUrl($view->url().'?locale='.$key)),'PREPEND');
      }
    }

    //A canonical URL lets you tell search engines that certain similar URLs are actually the same. Sometimes you have products or content that can be found on multiple URLs — or even multiple websites, but by using canonical URLs (HTML link tags with the attribute rel=canonical), you can have these on your site without harming your rankings.
    $view->headLink(array('rel' => 'canonical', 'href' => $view->absoluteUrl($view->url())),'PREPEND');

    //OpenSearch is a collection of simple formats for the sharing of search results. The OpenSearch description document format can be used to describe a search engine so that it can be used by search client applications.
    if(file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'osdd.xml')) {
      $view->headLink(array('rel' => 'search', 'href' => 'osdd.xml', 'type' => 'application/opensearchdescription+xml'),'PREPEND');
    }
    
    //Schema Markup
    $schema_type = $settings->getSetting('coreseo.schema.type', 1);
    if($schema_type == 1) {
    
      $socialmediaURL = array($settings->getSetting('coreseo.facebook', ''), $settings->getSetting('coreseo.twitter', ''), $settings->getSetting('coreseo.linkedin', ''), $settings->getSetting('coreseo.instagram', ''), $settings->getSetting('coreseo.youtube', ''));
      
      $othermediaurl = $settings->getSetting('coreseo.othermediaurl', '');
      $othermediaurl = explode(',', $othermediaurl);
      $socialmediaURL = array_merge($socialmediaURL, $othermediaurl);
      $socialmediaURL = array_filter(array_map('trim', $socialmediaURL));

      $scheme_array = array(
          '@context' => 'http://schema.org',
          '@type' => 'Website',
          "name" => $settings->getSetting('coreseo.sitetitle', Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title')),
          "alternateName" => $settings->getSetting('coreseo.alternatetitle', ''),
          "url" => $view->absoluteUrl($view->url()),
          "sameAs" => $socialmediaURL, //URL of a reference Web page that unambiguously indicates the item's identity. E.g. the URL of the item's Wikipedia page, Wikidata entry, or official website. Ex: https://schema.org/sameAs
      );
      $schememarkup_array = array_filter($scheme_array);
      $schema_markup = json_encode($schememarkup_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
      $schema_markup = $settings->getSetting('coreseo.customschema', '');
    }
    

    // Get body identity
   
    ?>

    <?php $controllerName = $request->getControllerName();?>
    <?php $actionName = $request->getActionName(); ?>
    
    <?php echo $this->headTitle()->toString()."\n" ?>
    <?php echo $this->headMeta()->toString()."\n" ?>

    <link href="<?php echo $staticBaseUrl . 'externals/bootstrap/css/bootstrap.css?c='.$counter; ?>" media="screen" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/styles/styles.css?c='.$counter; ?>">


    <?php // LINK/STYLES?>
    <?php $favicon = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon',false); ?>
    <?php
    $this->headLink(array(
        'rel' => 'shortcut icon',
        'href' => ($favicon ? Engine_Api::_()->core()->getFileUrl($favicon) : $staticBaseUrl . ( isset($this->layout()->favicon) ? $this->layout()->favicon : 'favicon.ico')),
        'type' => 'image/x-icon'),
        'PREPEND');
    $themes = array();
    if (!empty($this->layout()->themes)) {
        $themes = $this->layout()->themes;
    } else {
        $themes = array('default');
    }

    $contrast_mode = $settings->getSetting('contrast.mode', 'dark_mode');
    foreach ($themes as $theme) {
        $themePath = include APPLICATION_PATH.'/application/themes/'.$theme.'/manifest.php';
              
        foreach ($themePath['includefiles'] as $themefilePath) {
          if (APPLICATION_ENV != 'development') {
              $this->headLink()->prependStylesheet($staticBaseUrl . 'application/themes/' . $theme . '/'.$themefilePath);
          } else {
              $this->headLink()->prependStylesheet(rtrim($this->baseUrl(), '/'). '/application/themes/' . $theme . '/'.$themefilePath);
          }
        }
      }
    // Process
    foreach ($this->headLink()->getContainer() as $dat) {
        if (!empty($dat->href)) {
            if (false === strpos($dat->href, '?')) {
                $dat->href .= '?c=' . $counter;
            } else {
                $dat->href .= '&c=' . $counter;
            }
        }
    }

    $currentTheme = APPLICATION_PATH . '/application/themes/' . $themes[0] . '/default.tpl';
    $currentThemeHeader = APPLICATION_PATH . '/application/themes/' . $themes[0] . '/head.tpl';
    ?>

    <?php echo $this->headLink()->toString()."\n" ?>
    <?php echo $this->headStyle()->toString()."\n" ?>

    <?php // TRANSLATE?>
    <?php $this->headScript()->prependScript($this->headTranslate()->toString()) ?>
    
    <?php
      $loginSignupPage = true;
      $flagLoginSignup = true;
    ?>
    
    <?php if($loginSignupPage) { ?>
    <?php // SCRIPTS?>
    <script type="text/javascript">if (window.location.hash == '#_=_')window.location.hash = '';</script>
    <script type="text/javascript">
        <?php echo $this->headScript()->captureStart(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>

        //Date.setServerOffset('<?php echo date('D, j M Y G:i:s O', time()) ?>');

        en4.orientation = '<?php echo $orientation ?>';
        en4.core.environment = '<?php echo APPLICATION_ENV ?>';
        en4.core.language.setLocale('<?php echo $this->locale()->getLocale()->__toString() ?>');
        en4.core.setBaseUrl('<?php echo $this->url(array(), 'default', true) ?>');
        en4.core.staticBaseUrl = '<?php echo $this->escape($staticBaseUrl) ?>';
        en4.core.loader = scriptJquery.crtEle('img', {src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});

        <?php if ($this->subject()): ?>
        en4.core.subject = {
            type : '<?php echo $this->subject()->getType(); ?>',
            id : <?php echo $this->subject()->getIdentity(); ?>,
            guid : '<?php echo $this->subject()->getGuid(); ?>'
        };
        <?php endif; ?>
        <?php if ($this->viewer()->getIdentity()): ?>
        en4.user.viewer = {
            type : '<?php echo $this->viewer()->getType(); ?>',
            id : <?php echo $this->viewer()->getIdentity(); ?>,
            guid : '<?php echo $this->viewer()->getGuid(); ?>'
        };
        <?php endif; ?>
        if( <?php echo(Engine_Api::_()->getDbtable('settings', 'core')->core_dloader_enabled ? 'true' : 'false') ?> ) {
            en4.core.runonce.add(function() {
                en4.core.dloader.attach();
            });
        }

        <?php echo $this->headScript()->captureEnd(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>
        
        var post_max_size = '<?php echo Engine_Api::_()->core()->convertPHPSizeToBytes(ini_get('upload_max_filesize')); ?>';
        var currentPageUrl = '<?php echo $_SERVER["REQUEST_URI"]; ?>';
        var max_photo_upload_limit = 50;
        var photo_upload_text = "<?php echo $this->string()->escapeJavascript($this->translate('Max upload of %s allowed.', 50)); ?>";
        var dateFormatCalendar = "<?php echo Engine_Api::_()->core()->dateFormatCalendar(); ?>";
    </script>
    <?php if(!empty($schema_markup) && isset($schema_markup)) { ?>
      <script type="application/ld+json">
        <?php echo $schema_markup; ?>
      </script>
    <?php } ?>
    <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/jQuery/jquery-ui.css?c='.$counter; ?>" />
    <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'application/modules/Core/externals/styles/nprogress.css?c='.$counter ?>" />

    <?php 
        $counterCssKey = Engine_Api::_()->getApi('settings','core')->getSetting("core.styles.counter",0); 
        if(!empty($counterCssKey)){ 
            for($i = 1; $i <= $counterCssKey; $i++){ ?>
                <link rel="stylesheet" href="<?php echo $staticBaseUrl . "externals/styles/styles_$i.css?c=".$counter ?>" />
        <?php }
        }
    ?>

    <?php 
    //Load google map
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mapApiKey', '')) { ?>
      <script type="text/javascript" src="<?php echo 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mapApiKey', '').'&language='.$_COOKIE['en4_language'] ?>"></script>
    <?php } ?>

    <?php
      //Load Google Recaptcha
      $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
      $recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;
      if($recaptchaVersionSettings == 0  && $spamSettings['recaptchaprivatev3'] && $spamSettings['recaptchapublicv3']) { ?>
      <script src="<?php echo 'https://www.google.com/recaptcha/api.js?render='.$spamSettings['recaptchapublicv3']; ?>" async defer></script>
    <?php } ?>

    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/jQuery/jquery.min.js?c='.$counter ?>"></script>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/jQuery/jquery-ui.js?c='.$counter ?>"></script>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/bootstrap/js/bootstrap.js?c='.$counter ?>"></script>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/scripts/script.js?c='.$counter ?>"></script>

    <?php 
        $counterJsKey = Engine_Api::_()->getApi('settings','core')->getSetting("core.scripts.counter",0); 
        if(!empty($counterJsKey)){ 
            for($i = 1; $i <= $counterJsKey; $i++){  ?>
                <script type="text/javascript" src="<?php echo $staticBaseUrl . "externals/scripts/script_$i.js?c=".$counter ?>"></script>
            <?php }
        } 
    ?>

    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/nprogress.js?c='.$counter ?>"></script>
    
    <?php
      // Process
      foreach ($this->headScript()->getContainer() as $dat) {
          if (!empty($dat->attributes['src'])) {
              if (false === strpos($dat->attributes['src'], '?')) {
                  $dat->attributes['src'] .= '?c=' . $counter;
              } else {
                  $dat->attributes['src'] .= '&c=' . $counter;
              }
          }
      }
    ?>

    <?php echo $this->headScript()->toString()."\n" ?>
    <script type="text/javascript">
      var $ = scriptJquery;
      <?php if(defined('_ENGINE_ADMIN_PANEL')) { ?>
        var isAdminUrl = true;
      <?php } ?>
    </script>
    <?php } else if(empty($this->viewer()->getIdentity()) && !empty($flagLoginSignup)) { ?>
      
      <script src='<?php echo $staticBaseUrl . 'externals/jQuery/jquery.min.js'; ?>'></script>
      <script src='https://www.google.com/recaptcha/api.js' async defer></script>
      <?php 
      $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
      $recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;
      if($recaptchaVersionSettings == 0  && $spamSettings['recaptchaprivatev3'] && $spamSettings['recaptchapublicv3']) { ?>
        <script type="text/javascript">
          en4.core.runonce.add(function() {
            scriptJquery('#captcha-wrapper').hide();
            scriptJquery('<input>').attr({ 
              name: 'recaptcha_response', 
              id: 'recaptchaResponse', 
              type: 'hidden', 
            }).appendTo('.global_form'); 
          });
        </script>
      <?php } ?>
    <?php } ?>


    <?php echo $headIncludes ?>

    <?php
    if (file_exists($currentThemeHeader)) {
        require($currentThemeHeader);
    }
    ?>
    <style type="text/css">
    @media (max-width: 600px){    
    	.iskeyboard-enabled #TB_iframeContent{max-height:calc(100vh - 330px);}
    }
    </style>
</head>



<body id="global_page_<?php echo $identity ?>"<?php if ($bodyClass): ?> class="<?php // echo $bodyClass; ?>"<?php endif; ?>>
<script type="javascript/text">
    if(DetectIpad()){
      scriptJquery('a.album_main_upload').css('display', 'none');
      scriptJquery('a.album_quick_upload').css('display', 'none');
      scriptJquery('a.icon_photos_new').css('display', 'none');
    }
</script>
<script>
    var isGoogleKeyEnabled = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mapApiKey', '') ? 1 : 0; ?>;
    var isEnablegLocation = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) ? 1 : 0; ?>;
    var isEnableTooltip = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.enabletooltip', 1) ? 1 : 0; ?>;

    function hidecollapswidget()
    {
        var windowWidth = window.innerWidth
            || document.documentElement.clientWidth
            || document.body.clientWidth;

        if (windowWidth <= 950) {
            var hasSidebar = (document.querySelector('.layout_main .layout_left')
            || document.querySelector('.layout_main .layout_right'));
            if (hasSidebar !== null) {
                document.body.className += ' has-sidebar';
            }
            if(document.getElementById('show-sidebar'))
            document.getElementById('show-sidebar').onclick = function () {
                document.body.classList.toggle('sidebar-active');
            };
        }
    }
    window.onload = function() {
        hidecollapswidget();
    };
</script>
<script type="text/javascript">
    
    function makeSelectizeItem(){
        // if(scriptJquery(".show_multi_select").length > 0){
        //     if(scriptJquery('.show_multi_select.selectized').length > 0){
        //         scriptJquery('.show_multi_select.selectized').selectize()[0].selectize.destroy();
        //         scriptJquery('.show_multi_select').selectize({});
        //     } else {
        //         scriptJquery('.show_multi_select').selectize({});
        //     }
        // }
    }
    var ajaxRequestObjApp = null;
    var isFullLoadPageSE = false;
    function loadAjaxContentApp(url, stopPushState = false,type = "") {
    
        if(!stopPushState) {
          window.history.pushState({state:'new', url: url.replace('?getContentOnly=1', '')},'', url.replace('?getContentOnly=1', ''));   
        }
        
        //Ajaxsmoothbox close
        if(scriptJquery('#ajaxsmoothbox_main').length > 0){
          ajaxsmoothboxclose();
        }

        scriptJquery.ajaxSetup({cache: false}); // assures the cache is empty
        if (ajaxRequestObjApp != null) {
            ajaxRequestObjApp.abort();
            ajaxRequestObjApp = null;
        }
        NProgress.start();
        let getParams = {}
        getParams.getContentOnly = true;
        if(type == "full" || isFullLoadPageSE){
            type = "full";
            getParams.getFullContent = true;
        }
        scriptJquery('#script-default-data').remove();
        if(scriptJquery('.header-nav-open').length > 0) {
            scriptJquery('.header-nav-open').removeClass('header-nav-open');
        }
        if(scriptJquery('.header_body_open').length > 0) {
            scriptJquery('.header_body_open').removeClass('header_body_open');
        }
        if(scriptJquery('.navigation_submenu').length > 0) {
            scriptJquery('.navigation_submenu').hide();
        }
        if(scriptJquery('#mainmenuclosebtn').length > 0) {
            scriptJquery('#mainmenuclosebtn').trigger('click');
        }
        
        ajaxRequestObjApp = scriptJquery.get(url,getParams,function(response){
            isFullLoadPageSE = false;
            setProxyLocation();
            try {
              var parser = JSON.parse(response);
              if(parser.redirectFullURL) {
                loadAjaxContentApp(parser.redirectFullURL, false, 'full');
                return;
              } else if(parser.redirect) {
                loadAjaxContentApp(parser.redirect, false);
                return;
              }
            }catch(e){

            } 
            scriptJquery(window).unbind('scroll');
            NProgress.done();
            isLoadedFromAjax = true;
            if(scriptJquery(".tinymce_editor").length > 0) {
                tinymce.remove("textarea.tinymce_editor");
            }
            if(scriptJquery('#navigation_menu').length > 0) {
                scriptJquery('#navigation_menu').find('.active').removeClass('active');
            }
           
            if(scriptJquery('.navigation').length > 0) {
                scriptJquery('.navigation').find('.active').removeClass('active');
            }
            scriptJquery('#append-script-data').html("");
            if(type != "full"){
                scriptJquery('#global_wrapper').addClass("_loading");
                scriptJquery("#global_wrapper").html(response);
            }else{
                scriptJquery("body").html(response);
            }
            // make select selectize
            // makeSelectizeItem();
            // app default data
            updateMetaTags();
            if(typeof changeHeaderLayout != "undefined"){
                changeHeaderLayout();
            }
            hidecollapswidget();
            // Sticky Sidebar
            if (matchMedia('only screen and (min-width: 768px)').matches) {

                let headerDiv =scriptJquery(".layout_page_header");
                let margin = 0;
                if(headerDiv.css("position") === "fixed"){
                    margin = headerDiv.height() + 10;
                }


                scriptJquery('.layout_left, .layout_right')
                .theiaStickySidebar({
                        additionalMarginTop: 10 + margin
                })
            };
            
            Smoothbox.bind();
            setTimeout(() => {
                scriptJquery('#global_wrapper').removeClass("_loading")
            }, 1000);
            
            scriptJquery('html, body').animate({
             scrollTop: 0
            }, 0);
            en4.core.shutdown.trigger();
        });
    }
    function updateMetaTags(){
        let data = scriptJquery("#script-default-data");
        scriptJquery(document).prop('title', data.find("#script-page-title").html().replace(/&amp;/g, '&'))
        scriptJquery('body').removeAttr('script');
        scriptJquery('body').removeAttr('style');
        scriptJquery('body').attr('id', data.find("#script-page-id").html());
        scriptJquery('html').attr('dir', data.find("#header-orientation").html());
        scriptJquery('html').attr('locale', data.find("#header-locale").html());
        scriptJquery('html').attr('xml:lang', data.find("#header-locale").html());
        scriptJquery('body').attr('class', data.find("#script-page-class").html());
    }
    scriptJquery(document).ajaxComplete(function(e) {
        makeSelectizeItem();
        if(ajaxRequestObjApp){
            en4.core.runonce.trigger();
        }
        Smoothbox.bind();
    })
    
    AttachEventListerSE('click','a',function (e){
        if(e.which == 2 || (e.which == 1 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey))) { 
            return;
        }
        let url = scriptJquery(this).attr('href');

        // Check if the href starts with http or https
        if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
          // Get the current domain of the website
          var currentDomain = window.location.protocol + '//' + window.location.hostname;

          // Check if the URL starts with the current domain
          if (url.startsWith(currentDomain)) {
            // URL is the same as the current site, do something (or ignore it)
            return; // Optionally, you can return here to ignore processing
          }

          // Check if the URL is from a development server (assuming development URLs contain 'localhost' or similar)
          if (currentDomain.includes('localhost') || currentDomain.includes('127.0.0.1')) {
            // Handle development server logic here (e.g., different behavior or logging)
            console.log('Development server URL: ' + url);
          }

          // Redirect to the link
          window.open(url, '_blank');
          e.preventDefault();
          return;  // Stop processing after the first valid link is found
        }

        let isValid = true;
        if(scriptJquery(this).attr('target') == '_blank'){
            isValid = false;
        }
        let clickObj = this;
        if(typeof openPhotoInLightBoxSesalbum != "undefined" && openPhotoInLightBoxSesalbum == 1 && scriptJquery(clickObj).parent().hasClass("feed_attachment_photo")) {
            return;
        }
        if(typeof openVideoInLightBoxsesbasic != "undefined" && openVideoInLightBoxsesbasic == 1 && scriptJquery(clickObj).parent().hasClass("sesvideo_thumb")) {
            return;
        }
        if(scriptJquery(e.target).prop("tagName") != "A"){
            clickObj = scriptJquery(this).closest('a')[0];
            url = scriptJquery(this).closest('a').attr('href');
        }
        var isOpenSmoothbox = scriptJquery(this).attr("onclick");
        if(isOpenSmoothbox && isOpenSmoothbox.indexOf("opensmoothboxurl") > -1){
            return;
        }
        if(isValid && !scriptJquery(clickObj).hasClass('ajaxPrevent') && !scriptJquery(clickObj).hasClass('openSmoothbox') && !scriptJquery(clickObj).hasClass('smoothboxOpen') && !scriptJquery(clickObj).hasClass('opensmoothboxurl') && url && url != "javascript:;" && url != "#" && url.indexOf("mailto:") == -1 && url.indexOf("javascript:void(0)") == -1 && url.indexOf("javascript:void(0);") == -1 && url.indexOf(".mp3") == -1 && url.indexOf(".mp4") == -1 && !scriptJquery(clickObj).hasClass('ajaxsmoothbox') && !scriptJquery(clickObj).hasClass('smoothbox') && !scriptJquery(clickObj).hasClass('core_dashboard_nopropagate') && !scriptJquery(clickObj).hasClass('core_dashboard_nopropagate_content')) {
            e.preventDefault();  
            if(scriptJquery("#ajaxsmoothbox_main").length > 0 && scriptJquery("#ajaxsmoothbox_main").css("display") == "block"){
                ajaxsmoothboxclose()
            }
            //Coming soon page check
            if(typeof comingSoonEnable != 'undefined') {
                loadAjaxContentApp(en4.core.baseUrl+'comingsoon', false, 'full');
            } else {
                loadAjaxContentApp(url);
            }
        }
    });
    
    en4.core.runonce.add(function(){
        makeSelectizeItem();
    });

    window.onpopstate = function(e) {

      var URL = window.location.href;
        
      //Container tab work
      const params2 = new URLSearchParams(URL.split('?')[1]);
			var params3 = params2.get('tab');
			
      if(params3) {
        var mainTab = scriptJquery('.main_tabs');
        if(mainTab.length > 0 && mainTab.parent().length > 0 && mainTab.parent().parent().length > 0 && mainTab.parent().parent().find(`div.tab_${params3}`).length > 0) {
          scriptJquery('.main_tabs').find('li').removeClass('active');
          scriptJquery('.main_tabs').find(`li.tab_${params3}`).addClass('active');
          scriptJquery('.main_tabs').parent().parent().find('div.generic_layout_container').hide();
          scriptJquery('.main_tabs').parent().parent().find(`div.tab_${params3}`).show();
          return;
        }
      }

      if(e.state && e.state.url)
        loadAjaxContentApp(e.state.url, true);
      else 
        loadAjaxContentApp(URL, true);
    };

		// Sticky Sidebar
		en4.core.runonce.add(function() {
			if (matchMedia('only screen and (min-width: 768px)').matches) { 
                let headerDiv =scriptJquery(".layout_page_header");
                let margin = 0;
                if(headerDiv.css("position") === "fixed"){
                    margin = headerDiv.height() + 10;
                }
				scriptJquery('.layout_left, .layout_right')
				.theiaStickySidebar({
						additionalMarginTop: 10 + margin,
				})
			};
		});
</script>
<?php if (file_exists($currentTheme)): ?>
    <?php $this->content()->renderThemeLayout($this, $currentTheme); ?>
<?php else: ?>
    <div id="global_header">
        <?php echo $headerContent ?>
    </div>
    <div id='global_wrapper'>
        <div id='global_content'>
        <script>var currentPageUrl = '<?php echo $_SERVER["REQUEST_URI"]; ?>';</script>
            <span id="show-sidebar"><span><i class="fa fa-angle-down"></i></span></span>
            <?php echo $this->layout()->content ?>
        </div>
    </div>
    <div id="global_footer">
        <?php echo $footerContent ?>
    </div>
<?php endif; ?>
<div id="append-script-data"></div>
</body>
</html>