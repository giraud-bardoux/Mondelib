<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9929 2013-02-18 10:15:55Z alex $
 * @author     John
 */
?>

<h3><?php echo $this->translate("Manage Packages") ?></h3>
<p>
  <?php echo $this->translate("Packages are plugins, themes, mods, and other extensions that you can add to your social network.") ?>
 </p>
 <p>
   <?php echo 'More info: <a href="https://community.socialengine.com/blogs/597/22/packages-plugins" target="_blank">See KB article</a>.' ?>
 </p>
<div class="btn_new_install">
  <a class="install_packages_add" href="<?php echo $this->url(array('action' => 'select')) ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
      <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
    </svg>
    <?php echo $this->translate("Install New Packages") ?>
  </a>
  <div class="install_package_search">
    <input type="text" name="pluginname" id="pluginname" placeholder="<?php echo $this->translate('Search packages…'); ?>">
  </div>
</div>
<?php if( !empty($this->installedPackages) ): ?>
  <ul class="admin_packages" id="admin_packages">
    <?php foreach( $this->installedPackages as $packageInfo ):
    $package = $packageInfo['package'];
    $upgradeable = $packageInfo['upgradeable'];
    $upgrade_version = null;
    if( isset($this->remoteVersions[$package->getGuid()]) && version_compare($this->remoteVersions[$package->getGuid()]['version'], $package->getVersion(), '>') ) {
        $upgradeable = true;
        $upgrade_version = $this->remoteVersions[$package->getGuid()]['version'];
    }
    ?>
      <li class="packages" <?php if( $upgradeable ) echo ' class="upgradeable"' ?> data-name="<?php echo strtolower($package->getMeta()->getTitle()) ?>">
          <div class="admin_packages_title">
            <div class="admin_packages_left">
              <?php if(!empty($package->getThumb())) { ?>
                <?php if($package->getType() == 'theme') { ?> 
                  <img src="<?php echo $this->siteURL .DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$package->getName() . DIRECTORY_SEPARATOR . $package->getThumb(); ?>" alt="package img">
                <?php } else { ?>
                  <img src="<?php echo $this->siteURL . DIRECTORY_SEPARATOR . $package->getThumb(); ?>" alt="package img">
                <?php } ?>
              <?php } else { ?>
                <img src="externals/images/package-default.png" alt="package img">
              <?php } ?>
            </div>
            <div class="admin_packages_right">
              <h4>
                <?php echo $package->getMeta()->getTitle() ?>
              </h4>
              <span class="admin_packages_author">
                by <?php echo join(', ', $package->getMeta()->getAuthors()) ?>
              </span>
              <?php if( isset($packageInfo['database']['version']) && version_compare($packageInfo['database']['version'], $package->getVersion(), '<') ): ?>
                <span class="admin_packages_warning">
                  Warning: Your database structure for this package is out of date.
                  The version you currently have is <?php echo $packageInfo['database']['version'] ?>.
                  Please complete the installation of this package to resolve this problem.
                </span>
              <?php endif; ?>
            </div>  
          </div>
          <div class="admin_packages_version">
            <span> 
              <?php echo $package->getVersion() ?> 
            </span>
          </div> 
         <?php if( !empty($packageInfo['navigation']) ): ?>
          <div class='admin_packages_options'>
           <ul>
             <?php foreach( $packageInfo['navigation'] as $navInfo ): ?>
               <li>
                 <a href="<?php echo $navInfo['href'] ?>" class="<?php echo $navInfo['class'] ? $navInfo['class'] : '' ?>">
                    <?php echo $this->translate($navInfo['label']); ?>
                 </a>
               </li>
             <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>  
      </li>
    <?php endforeach; ?>
  </ul>
 <?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("You do not have any packages installed yet."); ?>
    </span>
 </div>
<?php endif; ?>
<div class="tip" id="error_message" style="display:none;">
  <span>
    <?php echo $this->translate("No plugins found."); ?>
  </span>
</div>
<script type="application/javascript">
  scriptJquery("#pluginname").keyup(function() {
    var val = this.value.trim().toLowerCase();
    if ('' != val) {
      var split = val.split(/\s+/);
      var selector = 'li';
      for(var i=0;i < split.length;i++){
        selector = selector+'[data-name*='+split[i]+']';
      }
      scriptJquery('.packages').hide();
      scriptJquery(selector).show();
      if(scriptJquery("#admin_packages").find('li[style*="display: flex"]').length == 0) {
        scriptJquery('#error_message').show();
      } else {
        scriptJquery('#error_message').hide();
      }
    } else {
      scriptJquery('.packages').show();
    }
  });
</script>
