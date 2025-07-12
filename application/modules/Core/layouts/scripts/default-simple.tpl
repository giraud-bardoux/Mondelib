<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: default-simple.tpl 10227 2014-05-16 22:43:27Z andres $
 * @author     John
 */
if (APPLICATION_ENV == 'development') {
  Engine_Api::_()->core()->generateJsCss();
}

$settings = Engine_Api::_()->getApi('settings', 'core');

$themeFontSize = isset($_SESSION['font_theme']) && !empty($_SESSION['font_theme']) ? $_SESSION['font_theme'] : "";
$htmlClass = "";

$contrast_mode = $settings->getSetting('contrast.mode', 'dark_mode');
$themeModeColor = !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] ? $_SESSION['mode_theme'] : "";
if($contrast_mode == 'dark_mode' && $themeModeColor == 'dark_mode') {
  $htmlClass .= " ".$themeModeColor;
} else if($contrast_mode == 'light_mode' && $themeModeColor == 'light_mode') {
  $htmlClass .= " ".$themeModeColor;
}
?>
<?php echo $this->doctype()->__toString() ?>
<?php $locale = $this->locale()->getLocale()->__toString(); $orientation = ( $this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr' ); ?>
<html id="smoothbox_window" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>" dir="<?php echo $orientation ?>" class="<?php echo $htmlClass; ?>" <?php if ($themeFontSize): ?> style="font-size: <?php echo $themeFontSize; ?>"<?php endif; ?>>
<head>
  <base href="<?php echo rtrim($this->serverUrl($this->baseUrl()), '/'). '/' ?>" />

  <?php // ALLOW HOOKS INTO META ?>
  <?php echo $this->hooks('onRenderLayoutDefaultSimple', $this) ?>


  <?php // TITLE/META ?>
  <?php
    $counter = (int) $this->layout()->counter;
    $staticBaseUrl = $this->layout()->staticBaseUrl;
    $headIncludes = $this->layout()->headIncludes;
    
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->headTitle()
      ->setSeparator(' - ');
    $pageTitleKey = 'pagetitle-' . $request->getModuleName() . '-' . $request->getActionName()
        . '-' . $request->getControllerName();
    $pageTitle = $this->translate($pageTitleKey);
    if( $pageTitle && $pageTitle != $pageTitleKey ) {
      $this
        ->headTitle($pageTitle, Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
    }
    $this
      ->headTitle($this->translate($this->layout()->siteinfo['title']))
      ;
    $this->headMeta()
      ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
      ->appendHttpEquiv('Content-Language', $this->locale()->getLocale()->__toString());

    // Make description and keywords
    $description = $this->layout()->siteinfo['description'];
    $keywords = $this->layout()->siteinfo['keywords'];

    if( $this->subject() && $this->subject()->getIdentity() ) {
      $this->headTitle($this->subject()->getTitle(), Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);

      $description = $this->subject()->getDescription() . ' ' . $description;
      // Remove the white space from left and right side
      $keywords = trim($keywords);
      if ( !empty($keywords) && (strrpos($keywords, ',') !== (strlen($keywords) - 1)) ) {
        $keywords .= ',';
      }
      $keywords .= $this->subject()->getKeywords(',');
    }

    $keywords = trim($keywords, ',');

    $this->headMeta()->appendName('description', trim($description));
    $this->headMeta()->appendName('keywords', trim($keywords));
    $this->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');

    //Adding open graph meta tag for video thumbnail
    if( $this->subject() && $this->subject()->getPhotoUrl() ) {
     $this->headMeta()->setProperty('og:image', $this->absoluteUrl($this->subject()->getPhotoUrl()));
    }

    // Get body identity
    if( isset($this->layout()->siteinfo['identity']) ) {
      $identity = $this->layout()->siteinfo['identity'];
    } else {
      $identity = $request->getModuleName() . '-' .
          $request->getControllerName() . '-' .
          $request->getActionName();
    }
  ?>
  <?php echo $this->headTitle()->toString()."\n" ?>
  <?php echo $this->headMeta()->toString()."\n" ?>

  <link href="<?php echo $staticBaseUrl . 'externals/bootstrap/css/bootstrap.css?c='.$counter; ?>" media="screen" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/styles/styles.css?c='.$counter; ?>">

  <?php // LINK/STYLES ?>
  <?php $favicon = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon',false); ?>
  <?php
    $this->headLink(array(
      'rel' => 'shortcut icon',
      'href' => ($favicon ? Engine_Api::_()->core()->getFileUrl($favicon) : $staticBaseUrl . ( isset($this->layout()->favicon) ? $this->layout()->favicon : 'favicon.ico')),
      'type' => 'image/x-icon'),
      'PREPEND');
    $themes = array();
    if( !empty($this->layout()->themes) ) {
      $themes = $this->layout()->themes;
    } else {
      $themes = array('default');
    }
    
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
    foreach( $this->headLink()->getContainer() as $dat ) {
      if( !empty($dat->href) ) {
        if( false === strpos($dat->href, '?') ) {
          $dat->href .= '?c=' . $counter;
        } else {
          $dat->href .= '&c=' . $counter;
        }
      }
    }
  ?>
  <?php echo $this->headLink()->toString()."\n" ?>
  <?php echo $this->headStyle()->toString()."\n" ?>
  
  <?php // TRANSLATE ?>
  <?php $this->headScript()->prependScript($this->headTranslate()->toString()) ?>

  <?php // SCRIPTS ?>
  <script type="text/javascript">
    <?php echo $this->headScript()->captureStart(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>

    Date.setServerOffset('<?php echo date('D, j M Y G:i:s O', time()) ?>');

    en4.orientation = '<?php echo $orientation ?>';
    en4.core.environment = '<?php echo APPLICATION_ENV ?>';
    en4.core.language.setLocale('<?php echo $this->locale()->getLocale()->__toString() ?>');
    en4.core.setBaseUrl('<?php echo $this->url(array(), 'default', true) ?>');
    en4.core.staticBaseUrl = '<?php echo $this->escape($staticBaseUrl) ?>';
    en4.core.loader = scriptJquery.crtEle('img', {src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});

    <?php if( $this->subject() ): ?>
      en4.core.subject = {
        type : '<?php echo $this->subject()->getType(); ?>',
        id : <?php echo $this->subject()->getIdentity(); ?>,
        guid : '<?php echo $this->subject()->getGuid(); ?>'
      };
    <?php endif; ?>
    <?php if( $this->viewer()->getIdentity() ): ?>
      en4.user.viewer = {
        type : '<?php echo $this->viewer()->getType(); ?>',
        id : <?php echo $this->viewer()->getIdentity(); ?>,
        guid : '<?php echo $this->viewer()->getGuid(); ?>'
      };
    <?php endif; ?>
    if( <?php echo ( Zend_Controller_Front::getInstance()->getRequest()->getParam('ajax', false) ? 'true' : 'false' ) ?> ) {
      en4.core.dloader.attach();
    }
    
    <?php echo $this->headScript()->captureEnd(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>
    
    var post_max_size = '<?php echo Engine_Api::_()->core()->convertPHPSizeToBytes(ini_get('upload_max_filesize')); ?>';
    var max_photo_upload_limit = 50;
    var photo_upload_text = "<?php echo $this->string()->escapeJavascript($this->translate('Max upload of %s allowed.', 50)); ?>";
    var dateFormatCalendar = "<?php echo Engine_Api::_()->core()->dateFormatCalendar(); ?>";
  </script>

  <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/jQuery/jquery-ui.css?c='.$counter; ?>">

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
  
  <?php
    // Process
    foreach( $this->headScript()->getContainer() as $dat ) {
      if( !empty($dat->attributes['src']) ) {
        if( false === strpos($dat->attributes['src'], '?') ) {
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
    
    var isGoogleKeyEnabled = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) == 1 && Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mapApiKey', '') ? 1 : 0; ?>;
    
    var isEnablegLocation = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) ? 1 : 0; ?>;

    if(!!navigator.platform.match(/iPhone|iPod|iPad/)){ 
      AttachEventListerSE('focus', 'input, textarea', function(){
        scriptJquery(parent.document.body).addClass('iskeyboard-enabled');

      });
      AttachEventListerSE('blur', 'input, textarea', function(){
        scriptJquery(parent.document.body).removeClass('iskeyboard-enabled');
      });
    }
  </script>
  <!-- vertical scrollbar fix -->
  <style type="text/css">
    html, body
    {
      overflow-y: auto;
      margin: 0px;
    }
  </style>
  <?php if ($request->getParam('format') !== 'smoothbox') { ?>
  <?php echo $headIncludes ?>
  <?php } ?>
</head>
<body id="global_page_<?php echo $identity ?>">
  <span id="global_content_simple">
    <?php echo $this->layout()->content ?>
  </span>
  <div id="append-script-data"></div>
</body>
</html>
