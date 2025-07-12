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

<div class="admin_page_packges">
  <h2 class="page_heading"><?php echo $this->translate("Plugins") ?></h2>
  <div class="admin_manage_package_tabs">
    <div class="admin_manage_package_tabs_links">
      <ul>
        <li>
          <a href="admin/core/manage-packages">
            <p><?php echo $this->translate("All Plugins"); ?>  <span class="admin_plugin_count"> (<?php echo engine_count($this->allModules); ?>) </span> </p>
          </a>
        </li>
        <li>
          <a href="admin/core/manage-packages/enabled">
            <p><?php echo $this->translate("Enabled Plugins"); ?> <span class="admin_plugin_count active_plugin"> (<?php echo engine_count($this->allEnabledModules); ?>)</span></p>
          </a>
        </li>
        <li class="active">
          <a href="admin/core/manage-packages/disabled">
            <p><?php echo $this->translate("Disabled Plugins"); ?> <span class="admin_plugin_count disable_plugin"> (<?php echo engine_count($this->allDisabledModules); ?>) </span></p>
          </a>
        </li>
        <?php if($this->viewer()->isSuperAdmin()) { ?>
          <li>
            <a href="<?php echo $this->url(array("module" => "core","controller" => "packages"), 'admin_default', true); ?>" target="_blank">
              <p><?php echo $this->translate("Install & Manage Packages"); ?></p>
            </a>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="admin_manage_package_search">
      <div class="form-group">
        <input type="text" name="pluginname" id="pluginname" placeholder="<?php echo $this->translate('Search plugins…'); ?>" value="<?php echo $this->name; ?>">
      </div>
    </div>
  </div>
  <?php if(engine_count($this->results) > 0) { ?>
    <table class='admin_table admin_responsive_table'>
      <thead>
        <tr>
          <th style="width:30%;"><?php echo $this->translate("Name") ?></th>
          <th><?php echo $this->translate("Description") ?></th>
        </tr>
      </thead>
      <tbody id="pluginsbody">
        <?php foreach( $this->results as $item ): ?>
          <?php
            $manifest_file = include APPLICATION_PATH . DIRECTORY_SEPARATOR .'application'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.ucfirst($item->name).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'manifest.php';
            $getMenuItem = Engine_Api::_()->getApi('menus', 'core')->getMenuItem(array('module' => $item->name, 'menu' => 'core_admin_main_plugins'));
            $params = $getMenuItem->params;
          ?>
          <tr data-name="<?php echo $this->translate(strtolower($manifest_file['package']['title'])) ?>" class="plugins">
            <td>
              <div class="admin_active_btn">
                <div class="admin_active_btn_left">
                  <img src="<?php echo !empty($manifest_file['package']['thumb']) ? $manifest_file['package']['thumb'] : 'application/modules/Core/externals/images/thumb.png'; ?>" alt="modules icon" class="admin_modules_icon">
                </div>
                <div class="admin_active_btn_right">
                  <span class="_title">
                    <?php if($item->enabled) { ?>
                      <a href="<?php echo $this->url(array('module' => $params['module'], 'controller' => $params['controller'], 'action' => @$params['action']), $params['route'], true) ?>"><?php echo $manifest_file['package']['title'] ?></a>
                    <?php } else { ?>
                      <?php echo $manifest_file['package']['title'] ?>
                    <?php } ?>
                  </span>
                  <div class="admin_btn_group">
                    <?php if($item->enabled) { ?>
                      <a href="<?php echo $this->url(array('module' => $params['module'], 'controller' => $params['controller'], 'action' => @$params['action']), $params['route'], true) ?>" class="active"> <?php echo $this->translate(" Settings "); ?> </a> | 
                    <?php } ?>
                    <?php if($item->enabled) { ?>
                      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'manage-packages', 'action' => "disable", 'package' => $this->packages[$params['module']]), 'admin_default', true); ?>" class="active smoothbox text_light"><?php echo $this->translate("Disable"); ?></a>
                    <?php } else { ?>
                      <a href="<?php echo $this->url(array('module' => 'core', 'controller' => 'manage-packages', 'action' => "enable", 'package' => $this->packages[$params['module']]), 'admin_default', true); ?>" class="active smoothbox"><?php echo $this->translate("Enable"); ?></a>
                    <?php } ?>
                  </div> 
                </div>
              </div> 
            </td>
            <td data-label="<?php echo $this->translate("Description") ?>">
              <div class="admin_description">
                <p><?php echo $manifest_file['package'] ? $manifest_file['package']['description'] : ''; ?></p>
                <div class="admin_description_version">
                  <span><?php echo $item->version ? $item->version : ''; ?> </span>
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php } else { ?>
    <div class="tip" id="errormessage">
      <span>
        <?php echo $this->translate("No plugins found."); ?>
      </span>
    </div>
  <?php } ?>
  <div class="tip" id="error_message" style="display:none;">
    <span>
      <?php echo $this->translate("No plugins found."); ?>
    </span>
  </div>
</div>
<script type="application/javascript">
  scriptJquery("#pluginname").keyup(function() {
    var val = this.value.trim().toLowerCase();
    if ('' != val) {
      var split = val.split(/\s+/);
      var selector = 'tr';
      for(var i=0;i<split.length;i++){
        selector = selector+'[data-name*='+split[i]+']';
      }
      scriptJquery('.plugins').hide();
      scriptJquery(selector).show();

      if(scriptJquery("#pluginsbody").find('tr:not([style*="display: none"])').length == 0) {
        scriptJquery('#errormessage').hide();
        scriptJquery('#error_message').show();
      } else {
        scriptJquery('#error_message').hide();
      }
    } else {
      scriptJquery('.plugins').show();
      scriptJquery('#errormessage').show();
      scriptJquery('#error_message').hide();
    }
  });
  scriptJquery('.core_admin_main_plugins').parent().addClass('active');
</script>
