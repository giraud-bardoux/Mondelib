<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: admin.tpl 10227 2014-05-16 22:43:27Z andres $
 * @author     John
 */
?>
<?php echo $this->doctype()->__toString() ?>
<?php $locale = $this->locale()->getLocale()->__toString(); $orientation = ($this->layout()->orientation == 'right-to-left' ? 'rtl' : 'ltr'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale ?>" lang="<?php echo $locale ?>" dir="<?php echo $orientation ?>" <?php if(!empty($_COOKIE['adminmode_theme']) && $_COOKIE['adminmode_theme'] == 'dark'): ?> class="dark_mode" <?php endif; ?>>
<head>
    <base href="<?php echo rtrim('//' . $_SERVER['HTTP_HOST'] . $this->baseUrl(), '/'). '/' ?>" />
    <?php $this->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');?>
   <?php // ALLOW HOOKS INTO META?>
    <?php echo $this->hooks('onRenderLayoutAdmin', $this) ?>
    <script type="text/javascript">
        isLoadedFromAjax = true;
    </script>
    <?php // TITLE/META?>
    <?php
    $counter = (int) $this->layout()->counter;
    $staticBaseUrl = $this->layout()->staticBaseUrl;

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->headTitle()
        ->setSeparator(' - ');
    $pageTitleKey = strtoupper('pagetitle-' . $request->getModuleName() . '-' . $request->getActionName()
        . '-' . $request->getControllerName());
    $pageTitle = $this->translate($pageTitleKey);
    if ($pageTitle && $pageTitle != $pageTitleKey) {
        $this
            ->headTitle($pageTitle, Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
    }
    $this
        ->headTitle($this->translate("Control Panel"))
    ;
    $this->headMeta()
        ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
        ->appendHttpEquiv('Content-Language', $this->locale()->getLocale()->__toString());
    if ($this->subject() && $this->subject()->getIdentity()) {
        $this->headTitle($this->subject()->getTitle());
        $this->headMeta()->appendName('description', $this->subject()->getDescription());
        $this->headMeta()->appendName('keywords', $this->subject()->getKeywords());
    }

    // Get body identity
    if (isset($this->layout()->siteinfo['identity'])) {
        $identity = $this->layout()->siteinfo['identity'];
    } else {
        $identity = $request->getModuleName() . '-' .
            $request->getControllerName() . '-' .
            $request->getActionName();
    }
    ?>
    <?php echo $this->headTitle()->toString()."\n" ?>
    <?php echo $this->headMeta()->toString()."\n" ?>

    <?php // LINK/STYLES?>
    <?php $favicon = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon',false); ?>
    <?php
    $this->headLink(array(
        'rel' => 'shortcut icon',
        'href' => ($favicon ? Engine_Api::_()->core()->getFileUrl($favicon) : $staticBaseUrl . ( isset($this->layout()->favicon) ? $this->layout()->favicon : 'favicon.ico')),
        'type' => 'image/x-icon'),
        'PREPEND');
    if (APPLICATION_ENV != 'development') {
        $this->headLink()
            ->prependStylesheet($staticBaseUrl.'application/css.php?request=application/modules/Core/externals/styles/admin/main.css');
    } else {
        $this->headLink()
            ->prependStylesheet(rtrim($this->baseUrl(), '/') . '/application/css.php?request=application/modules/Core/externals/styles/admin/main.css');
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
    ?>
    <?php echo $this->headLink()->toString()."\n" ?>
    <?php echo $this->headStyle()->toString()."\n" ?>

    <?php // TRANSLATE?>
    <?php $this->headScript()->prependScript($this->headTranslate()->toString()) ?>

    <?php // SCRIPTS?>
    <script type="text/javascript">
        <?php echo $this->headScript()->captureStart(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>

        Date.setServerOffset('<?php echo date('D, j M Y G:i:s O', time()); ?>');
        
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
        if( <?php echo(Zend_Controller_Front::getInstance()->getRequest()->getParam('ajax', false) ? 'true' : 'false') ?> ) {
            en4.core.dloader.attach();
        }
        <?php echo $this->headScript()->captureEnd(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) ?>
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
    <?php if($request->getControllerName() == 'admin-content') { ?>
      <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/admin/adminlayout.js?c='.$counter; ?>"></script>
      <script type="text/javascript" src="<?php echo $staticBaseUrl . 'application/modules/Core/externals/scripts/admin/layout.js?c='.$counter; ?>"></script>
    <?php } ?>
    <script type="text/javascript">
      var $ = scriptJquery;
    </script>
    <script type="text/javascript">
        //<![CDATA[
        var changeEnvironmentMode = function(mode, btn) {
            btn = scriptJquery(btn);
            if( btn ) {
                // btn.attr('class', '');
            }
            if(scriptJquery('div.admin_environment #modeloading') ) {
                scriptJquery('div.admin_environment #modeloading').attr('class', 'loading_enable');
            }
            if(scriptJquery('div.admin_home_environment_description')) {
                scriptJquery('div.admin_home_environment_description').attr('text', 'Changing mode - please wait...');
            }
            scriptJquery.ajax({
                url: '<?php echo $this->url(array('action'=>'change-environment-mode'), 'admin_default', true) ?>?'+'format=json&environment_mode='+mode,
                method: 'post',
                success: function(responseJSON){
                    if ($type(responseJSON) == 'object') {
                        if (responseJSON.success || !$type(responseJSON.error))
                            window.location.href = window.location.href;
                        else
                            alert(responseJSON.error);
                    } else
                        alert('An unknown error occurred; changes have not been saved.');
                }
            });
        }
        var post_max_size = '<?php echo Engine_Api::_()->core()->convertPHPSizeToBytes(ini_get('upload_max_filesize')); ?>';
        var max_photo_upload_limit = 50;
        var photo_upload_text = "<?php echo $this->string()->escapeJavascript($this->translate('Max upload of %s allowed.', 50)); ?>";
        //]]>
    </script>
</head>
<body id="global_page_<?php echo $identity ?>" class="admin">

<div class="admin_panel_wrapper">
  <!-- TOP HEADER BAR -->
  <div id='global_header'>    
    <div class="global_header_top">
      <?php if ('development' == APPLICATION_ENV): ?>
      <div class="development_mode_warning">
          Your site is currently in development mode (which may decrease performance).
          When you've finished changing your settings, remember to
          <a href="javascript:void(0)" onClick="changeEnvironmentMode('production', this);this.blur();">return to production mode</a>.
      </div>
      <?php endif ?> 
      <div class="global_header_menu_mini">
        <?php echo $this->content()->renderWidget('core.admin-menu-mini') ?>
      </div>
    </div>
    <div class="global_header_left <?php if ('development' == APPLICATION_ENV): ?> global_header_development <?php endif ?> ">
      <?php echo $this->content()->renderWidget('core.admin-menu-main') ?>
      <div class="admin_header_version">
        <h5>Network Information</h5>
        <?php 
        // License info
          $site = Engine_Api::_()->getApi('settings', 'core')->core_site;
          // Get the core module version
          $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->select()
          ->from('engine4_core_modules', 'version')
          ->where('name = ?', 'core')
          ->query()
          ->fetchColumn();
        ?>
        <ul class="admin_header_version_inner">
          <li> 
            <span class="_header_version_title"><?php echo $this->translate('Created') ?></span> 
            <span><?php echo $this->timestamp($site['creation']) ?></span>
          </li>
          <li>
            <span class="_header_version_title"><?php echo $this->translate('Version') ?></span> 
            <span><?php echo $coreVersion ?></span> 
          </li>
          <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) { ?>
            <?php $countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers(); ?>
            <?php $maxusers = Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.maxusers', 0); ?>
            <li>
              <span class="_header_version_title"><?php echo $this->translate('Active Members') ?></span> 
              <span><?php echo ($countActiveMembers .' / '.$maxusers);  ?></span> 
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
   <!-- BEGIN CONTENT -->
   <div id='global_wrapper'>
     <div id='global_content'>
       <?php echo $this->layout()->content ?>
      </div>

    </div>
</div>

  <a class="admin_scroll_top">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
    </svg>
  </a>

  <script>
    var btn = scriptJquery('.admin_scroll_top');
    scriptJquery(window).scroll(function() {
    if (scriptJquery(window).scrollTop() > 300) {
    btn.addClass('show');
    } else {
    btn.removeClass('show');
    }
    });
    btn.on('click', function(e) {
    e.preventDefault();
    scriptJquery('html, body').animate({scrollTop:0}, '300');
    });
  </script>
 </body>
</html>
