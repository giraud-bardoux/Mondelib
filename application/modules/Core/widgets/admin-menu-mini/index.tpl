<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if (!empty($this->code)): ?>
	<div class="admin_maintenance_mode">
		<?php echo $this->translate('Your community is currently in maintenance mode and can only be accessed with a passcode: %s', "{$this->code}") ?>
		<span id="exit-maintenance-mode">
			[<a href='javascript:void(0);' onClick='exit_maintenance_mode();'><?php echo $this->translate('exit maintenance mode'); ?></a>]
		</span>
	</div>

	<script type="text/javascript">
    //<![CDATA[
    var exit_maintenance_mode = function(){
      scriptJquery('#exit-maintenance-mode').hide();
      scriptJquery.ajax({
        url: '<?php echo $this->url(array('controller'=>'settings', 'action'=>'general'), 'admin_default') ?>',
        method: 'post',
        data : {
          maintenance_mode:0,
        },
        success: function(response){
          window.location.href=window.location.href;
        },
        error: function(xhr){
          scriptJquery('#exit-maintenance-mode').show();
        }
      });
    }
    //]]>
	</script>
<?php endif; ?>
<div id='global_header_mini_menu_wrapper'>
	<div id='global_header_left_menu'>
	  <?php echo $this->content()->renderWidget('core.admin-menu-logo') ?>
	</div>
  <div id='global_header_right_menu'>
		<div id='global_header_mini_left'>
			<a href="javascript:void(0)" class="toggle_button"> 
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
				</svg>
			</a>
			<div class="user_setting">
				<?php if( $this->viewer()->getIdentity() ) : ?>
					<a href="javascript:void(0)" class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
						<?php echo $this->itemPhoto($this->viewer(), 'thumb.icon') ?>
						<span> <?php echo $this->translate($this->viewer()->getTitle()) ?></span>
					</a>
					<div class="dropdown-menu">
						<div class="header-user-info">
							<h6 id="user-name"><?php echo $this->translate($this->viewer()->getTitle()) ?></h6>
							<p id="user-designation"><?php echo $this->translate(Engine_Api::_()->getItem('authorization_level', $this->viewer()->level_id)->getTitle()); ?></p>
						</div>
						<ul>
						  <li><a href="<?php echo $this->viewer()->getHref(); ?>" target="_blank"> <i class="fas fa-user"></i>  <?php echo $this->translate("My Profile") ?></a></li>
					  	<li>
								<a href='<?php echo $this->url(array(), 'user_logout') ?>'>
								<i class="fas fa-sign-out-alt"></i> <?php echo $this->translate("sign out") ?>
								</a>
							</li>
					 </ul>
					</div>
				<?php endif; ?>
			</div>
    </div>
    <div id='global_header_mini_right'>
			<ul>
				<li>
					<div class="production_btn">
						<span class="_mode"><?php echo $this->translate("Mode"); ?> 
						<?php if ('production' != APPLICATION_ENV): ?>
							<i data-bs-toggle="tooltip" data-bs-placement="bottom" title='<?php echo $this->translate("Your community is currently in development mode. Most error messages are shown and caching is disabled. Changes to your CSS, theme, or view scripts will appear immediately, but your system may run more slowly. Only use this mode when making changes or troubleshooting."); ?>' class="fas fa-question-circle dBlock-inline"></i>
						 <?php else: ?>
							<i data-bs-toggle="tooltip" data-bs-placement="bottom" title='<?php echo $this->translate("Your community is currently in production mode. Most error messages are hidden and caching is enabled. If you want to make changes to your CSS layout or view scripts, please switch to Development Mode first."); ?>' class="fas fa-question-circle dBlock-inline"></i>
						<?php endif; ?>
						</span>
						<div class="production admin_environment" for="prodcutionmode">
							<div class="slider_tab <?php if ('development' == APPLICATION_ENV): ?> check <?php endif ?> ">
								<a href="javascript:void(0)" class="_production_title" onclick="changeEnvironmentMode('development', this);this.blur();"><?php echo $this->translate("Production") ?></a>
								<a href="javascript:void(0)" class="_development_title"   onclick="changeEnvironmentMode('production', this);this.blur();"> <?php echo $this->translate("Development") ?></a>
							</div>
							<div id="modeloading"><i class="fas fa-spinner fa-spin"></i></div>
						</div>
					</div>
				</li>
				<li>
					<div class="toggle_color_mode">
						<a href="javascript:void(0)" class="_light_title dBlock"   onclick="themeMode('light', this);this.blur();"	data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->translate(" Light Mode") ?>">
					  	<i class="fas fa-sun"></i>
						</a>
						<a href="javascript:void(0)" class="_dark_title dBlock"  onclick="themeMode('dark', this);this.blur();" 	data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->translate(" Dark Mode") ?>">
							<i class="fas fa-moon"></i>
						</a>
					</div>
				</li>
			  <li>
					<a href='<?php echo $this->url(array(), 'default', true) ?>' target="_blank" class='admin_website_link_icon dBlock-inline'
					data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->translate(" Back to Network") ?>">  
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
						<path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/>
						</svg>
					</a>
				</li>
				<?php if( 1 !== engine_count($this->languageNameList) ): ?>
          <li>
            <?php $selectedLanguage = !empty($_COOKIE['en4_language']) ? $_COOKIE['en4_language'] : Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en'); //$this->translate()->getLocale(); ?>
            <?php $isLanguageExist = Engine_Api::_()->getDbTable('languages', 'core')->isLanguageExist($selectedLanguage); ?>
            <?php if($isLanguageExist) {
              $languageItem = Engine_Api::_()->getItem('core_language', $isLanguageExist);
              $path = '';
              if($languageItem && !empty($languageItem->icon)) {
                $path = Engine_Api::_()->core()->getFileUrl($languageItem->icon);
              }
            } ?>
            <a href="javascript:void(0))" class='right_side_icon dBlock'  data-bs-toggle="dropdown" aria-expanded="false"> 
              <?php if($path) { ?>
                <img src="<?php echo $path; ?>" alt="img" class="dBlock" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->translate("Language") ?>">
              <?php } else { ?>
                <span><?php echo $languageItem->name; ?></span>	
              <?php } ?>
            </a>
            <ul class="dropdown-menu">
              <?php foreach($this->languageNameList as $key => $languageNameList) { ?>
                <?php $isLanguageExist = Engine_Api::_()->getDbTable('languages', 'core')->isLanguageExist($key); ?>
                <?php if($isLanguageExist) {
                  $languageItem = Engine_Api::_()->getItem('core_language', $isLanguageExist);
                  $path = '';
                  if($languageItem && !empty($languageItem->icon)) {
                    $path = Engine_Api::_()->core()->getFileUrl($languageItem->icon);
                  }
                } ?>
                <li id="footer_language_<?php echo $this->identity; ?>" <?php if($selectedLanguage == $key) { ?> selected="selected" <?php } ?> >
                  <a class="dropdown-item" href="javascript:void(0);" onclick="setLanguage('<?php echo $key; ?>')">
                    <?php if(!empty($path)) { ?>
                      <img src="<?php echo $path; ?>" alt="img">
                    <?php } ?>
                    <span><?php echo $this->translate($languageNameList) ?></span>	
                  </a>
                </li>
              <?php } ?>
            </ul>
          </li>
        <?php endif; ?>
			</ul>
    </div>
  </div>
</div>
<style>
  .dBlock {
    display:inline-flex !important;
  }
  .dBlock-inline {
    display:inline-block !important;
  }
</style>
<script type="application/javascript">

  function themeMode(value, obj) {
    setCoreCookie("adminmode_theme", value, 10);
    location.reload();
	}
	
  function setLanguage(value) {
    scriptJquery.post("core/utility/locale",{
      language:value, 
      return:'<?php echo $this->url(); ?>',
      admin: true,
    },function (response) {
      location.reload();
    });
    scriptJquery('#footer_language_<?php echo $this->identity; ?>').submit();
  }

	scriptJquery(".toggle_button").click(function(){
	scriptJquery(".global_header_left, body").toggleClass("nav-show");
	});

	//Menu Toggle
	en4.core.runonce.add(function() {
		scriptJquery(".admin_menu_setting_button a").click(function(){
			scriptJquery(".admin_menu_setting").toggleClass("active");
		});
	});

	// Submenu Dropdown
	en4.core.runonce.add(function() {
		var menuElement = scriptJquery('.global_header_left').find('.menu_core_admin_main').parent();
		menuElement.addClass('menu_link');
		var submenu = scriptJquery('.main_menu_submenu > li > .active');
		submenu.closest('.menu_link').addClass('active');
		submenu.closest('.menu_link').children().eq(0).addClass('active');
		menuElement.find('ul').hide();
		if(menuElement.find('ul').length)
			menuElement.find('a').addClass('toggled_menu');
		scriptJquery('.navigation').children().eq(0).find ('a').removeClass('toggled_menu')
		scriptJquery('.menu_link.active').find('ul').show();
	});
	AttachEventListerSE('click', '.toggled_menu', function () {
		if(scriptJquery(this).hasClass('active')){
			scriptJquery(this).removeClass('active')
			scriptJquery(this).parent().find('ul').slideUp()
		}
		else{
			scriptJquery(this).addClass('active')
			scriptJquery(this).parent().find('ul').slideToggle()
		}
	});
</script>
