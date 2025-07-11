<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: default.tpl 10160 2014-04-11 19:49:31Z andres $
 */
?>
<?php echo $this->doctype()->__toString() ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <base href="<?php echo rtrim('//' . $_SERVER['HTTP_HOST'] . $this->baseUrl(), '/'). '/' ?>" />

    <?php // TITLE/META ?>
    <?php
      $this->headTitle()
        ->setSeparator(' - ');
      $this->headMeta()
        ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')
        ->appendHttpEquiv('Content-Language', 'en-US');
    ?>
    <?php echo $this->headTitle()->toString()."\n" ?>
    <?php echo $this->headMeta()->toString()."\n" ?>

    <?php // LINK/STYLES ?>
    <?php
      $this->headLink()
        ->prependStylesheet($this->baseUrl() . '/externals/styles/sdk.css')
        ->prependStylesheet($this->baseUrl() . '/externals/styles/styles.css')
        ;
    ?>
    <?php echo $this->headLink()->toString()."\n" ?>
    <?php echo $this->headStyle()->toString()."\n" ?>

    <?php // SCRIPTS ?>
    <?php 
      $appBaseHref = str_replace('install/', '', $this->url(array(), 'default', true));
      $appBaseUrl = rtrim(str_replace('\\', '/', dirname($this->baseUrl())), '/');
      $this->headScript()
        ->prependFile($appBaseUrl . '/externals/jQuery/jquery.min.js')
        ->prependFile($appBaseUrl . '/externals/jQuery/jquery-ui.js')
        
        ->prependFile($appBaseUrl . '/externals/jQuery/core.js')
        ->prependFile($appBaseUrl . '/externals/smoothbox/smoothbox4.js');
        
			$this->headScript()->prependFile($appBaseUrl . '/application/modules/Core/externals/scripts/admin/adminlayout.js')
          ->prependFile($appBaseUrl . '/application/modules/Core/externals/scripts/admin/layout.js')
          ->prependFile($appBaseUrl . '/application/modules/Core/externals/scripts/admin/layoutchoo.js')
          ;
    ?>
		

    <script>
      var dateFormatCalendar = "";
    </script>
    <?php echo $this->headScript()->toString()."\n" ?>
	<script type="text/javascript" src="<?php echo $appBaseUrl . '/application/modules/Core/externals/scripts/core.js' ?>"></script>
  </head>
  <body class="admin">
      <?php if( empty($this->layout()->hideIdentifiers) ): ?>
        <div class='topbar_wrapper'>
          <?php if( $this->layout()->inInstall ): ?>
            <div class="topbar">
              <div class='logo'>
                <img src="externals/images/logo.svg" alt="" />
              </div>
            </div>
          <?php endif; ?>
          <!--After Install Header Start-->
          <?php if( !$this->layout()->inInstall ): ?>
            <div class="topbar_manage_page">
              <div class='logo'>
                <img src="externals/images/logo.svg" alt="" />
              </div>
              <div class='topmenu_manage_page'>
                <p>
                  <?php echo $this->translate('You are currently signed-in to the ' .
                      'package manager, a tool used for adding plugins, <br/> mods, ' .
                      'themes, languages, and other extensions to ' .
                      'your community.') ?>
                </p>
                <a href="<?php echo $this->url(array(), 'logout') ?>?return=<?php echo urlencode($appBaseHref . 'admin/') ?>" class=" package_return_btn"> <img src="externals/images/admin-return.svg">  <?php echo $this->translate("Return to Admin Panel")?> </a>
                </div>
            </div>
          <?php endif ?> 
          <!--After Install Header End-->
        </div>
        <!--After Install Manage Section Start-->
        <?php if( !$this->layout()->inInstall ): ?> 
          <div class="install_main_packages">
            <div class='install_main_packages_left'>
                <?php echo $this->render('_managerMenu.tpl') ?>       
              </div>
              <div class='install_main_packages_right'>
                <?php echo $this->layout()->content ?>
              </div>
          </div>
        <?php endif ?> 

        <!--After Install Manage Section End-->
        <!--Install Time Section Start-->
        <div class="content main_packages" id="main_packages">
          <div class="tabs_packagemanager">
            <h2>SocialEngine Installation</h2>
          </div>
      <?php endif; ?>     
          <div class='packagemanager'>
            <?php echo $this->layout()->content ?>
          </div>
        </div>  
        <!-- Install Time Section Start-->
    <?php if( !$this->layout()->inInstall ): ?>
      <script>
        scriptJquery('#main_packages').hide();
      </script>
    <?php endif; ?>
    <?php if( APPLICATION_ENV == 'development' ): ?>
      <div style="margin-bottom: 40px; text-align: center;">
        <span>
          Peak Memory Usage: <?php echo number_format(memory_get_peak_usage()) ?>
          <br />

          Load time (approx): 
          <?php
            $deltaTime = microtime(true) - _ENGINE_REQUEST_START;
            $hours = floor($deltaTime / 3600);
            $minutes = floor(($deltaTime % 3600) / 60);
            $seconds = floor((($deltaTime % 3600) % 60));
            $milliseconds = floor(($deltaTime - floor($deltaTime)) * 1000);
            if( $hours > 0 ) {
              echo $this->translate(array('%d hour', '%d hours', $hours), $hours);
              echo ", ";
            }
            if( $minutes > 0 ) {
              echo $this->translate(array('%d minute', '%d minutes', $minutes), $minutes);
              echo ", ";
            }
            if( $seconds > 0 ) {
              echo $this->translate(array('%d second', '%d seconds', $seconds), $seconds);
              echo ", ";
            }
            if( $milliseconds > 0 ) {
              echo $this->translate(array('%d millisecond', '%d milliseconds', $milliseconds), $milliseconds);
              echo ", ";
            }
            echo number_format($deltaTime, 3);
            echo ' seconds total';
          ?>
          <br />
        </span>
      </div>
    <?php endif; ?>
  </body>
</html>
