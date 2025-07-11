<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: admin-simple.tpl 10227 2014-05-16 22:43:27Z andres $
 * @author     John
 */
?>
<?php echo $this->doctype()->__toString() ?>
<?php $locale = $this->locale()->getLocale()->__toString(); $orientation = ( $this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr' ); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>" dir="<?php echo $orientation ?>" <?php if(!empty($_COOKIE['adminmode_theme']) && $_COOKIE['adminmode_theme'] == 'dark'): ?> class="dark_mode" <?php endif; ?>>
<head>
  <base href="<?php echo rtrim('//' . $_SERVER['HTTP_HOST'] . $this->baseUrl(), '/'). '/' ?>" />

  <?php // ALLOW HOOKS INTO META ?>
  <?php echo $this->hooks('onRenderLayoutAdminSimple', $this) ?>

  <?php // TITLE/META ?>
  <?php
    $counter = (int) $this->layout()->counter;
    $staticBaseUrl = $this->layout()->staticBaseUrl;
    
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
      ->headTitle($this->translate("Control Panel"), Zend_View_Helper_Placeholder_Container_Abstract::PREPEND)
      ;
    $this->headMeta()
      ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
      ->appendHttpEquiv('Content-Language', $this->locale()->getLocale()->__toString());
    if( $this->subject() && $this->subject()->getIdentity() ) {
      $this->headTitle($this->subject()->getTitle());
      $this->headMeta()->appendName('description', $this->subject()->getDescription());
      $this->headMeta()->appendName('keywords', $this->subject()->getKeywords());
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

  <?php // LINK/STYLES ?>
  <?php $favicon = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon',false); ?>
  <?php 
    $this->headLink(array(
      'rel' => 'shortcut icon',
      'href' => ($favicon ? Engine_Api::_()->core()->getFileUrl($favicon) : $staticBaseUrl . ( isset($this->layout()->favicon) ? $this->layout()->favicon : 'favicon.ico' )),
      'type' => 'image/x-icon'),
      'PREPEND');
    if( APPLICATION_ENV != 'development' ) {
      $this->headLink()
        ->prependStylesheet($staticBaseUrl.'application/css.php?request=application/modules/Core/externals/styles/admin/main.css');
    } else {
      $this->headLink()
        ->prependStylesheet(rtrim($this->baseUrl(), '/') . '/application/css.php?request=application/modules/Core/externals/styles/admin/main.css');
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
  
  <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/font-awesome/css/all.min.css?c='.$counter; ?>">
  <link href="<?php echo $staticBaseUrl . 'externals/bootstrap/css/bootstrap.css?c='.$counter; ?>" media="screen" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="<?php echo $staticBaseUrl . 'externals/jQuery/jquery-ui.css?c='.$counter; ?>">
  <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/jQuery/jquery.min.js?c='.$counter ?>"></script>
  <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/jQuery/jquery-ui.js?c='.$counter ?>"></script>
  <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/bootstrap/js/bootstrap.js?c='.$counter ?>"></script>
  <script type="text/javascript" src="<?php echo $staticBaseUrl . 'externals/scripts/script.js?c='.$counter ?>"></script>
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
  <?php if(engine_in_array($request->getActionName(), array('admin-content', 'widget'))) { ?>
    <?php if(0) { ?>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/admin/layoutchoo.js?c='.$counter ?>"></script>
    <?php } ?>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/admin/adminlayout.js?c='.$counter; ?>"></script>
    <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/admin/layout.js?c='.$counter; ?>"></script>
  <?php } ?>
  <script type="text/javascript">
    var $ = scriptJquery;
  </script>
  
  <!-- vertical scrollbar fix -->
  <style type="text/css">
    html, body
    {
      overflow-y: auto;
      margin: 0px;
    }
  </style>
</head>
<body id="global_page_<?php echo $identity ?>" class="smoothbox_body admin">
  <span id="global_content_simple">
    <?php echo $this->layout()->content ?>
  </span>
</body>
</html>
