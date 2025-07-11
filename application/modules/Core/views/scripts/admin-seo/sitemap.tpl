<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: sitemap.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_seo_sitemap')); ?>

<h2 class="page_heading"><?php echo $this->translate('SEO Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<?php $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap'; ?>

<h3><?php echo $this->translate('Index Sitemap'); ?></h3>
<p><?php echo $this->translate('Here, you can view all content sitemap files.'); ?></p>

<div class="core_sitemap_admin_options">
  <?php if(file_exists($filepath .DIRECTORY_SEPARATOR.'sitemap'.'.xml')) { ?>
    <a href="public/sitemap/sitemap.xml" target="_blank" class="admin_link_btn"><?php echo $this->translate('View Sitemap'); ?></a>
    <a href="<?php echo $this->url(array('action' => 'downloadxml')) ?>" class="admin_link_btn"><?php echo $this->translate('Download Sitemap XML'); ?></a>
    <a href="<?php echo $this->url(array('action' => 'downloadgzip')) ?>" class="admin_link_btn"><?php echo $this->translate('Download Sitemap Gzip File'); ?></a>
    <a href="<?php echo $this->url(array('action' => 'generateall')) ?>" class="smoothbox admin_link_btn"><?php echo $this->translate('Regenerate Sitemap'); ?></a>
  <?php } else { ?>
    <a href="<?php echo $this->url(array('action' => 'generateall')) ?>" class="smoothbox admin_link_btn"><?php echo $this->translate('Generate Sitemap'); ?></a>
  <?php } ?>
</div>
<br />
<h3><?php echo $this->translate('Content Sitemap'); ?></h3>
<p><?php echo $this->translate('From here, you can create sitemap for particular content. You can first select menu for which you want to create sitemap by clicking on "Select Menus" link below. You can also modify settings by click on "Edit Link".'); ?></p>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s entry found.", "%s entries found.", $count),
        $this->locale()->toNumber($count)) ?>
  </div>
</div>

<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form'>
    <table class='admin_table admin_responsive_table' style="width:100%;">
      <thead>
        <tr>
          <th>
            <?php echo $this->translate("Content Title"); ?>
          </th>
          <th>
            <?php echo $this->translate("Frequency"); ?>
          </th>
          <th>
            <?php echo $this->translate("Priority"); ?>
          </th>
          <th class="text-center">
            <?php echo $this->translate("Status"); ?>
          </th>
          <th>
            <?php echo $this->translate("Sitemap File"); ?>
          </th>
          <th>
            <?php echo $this->translate("Options"); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
          <?php if(!engine_in_array($item->resource_type, array('menu_urls'))) { ?>
            <?php $hasItemType = Engine_Api::_()->hasItemType($item->resource_type); if(empty($hasItemType)) continue; ?>
          <?php } ?>
          <?php if(engine_in_array($item->resource_type, array('core_page'))) continue; ?>
          <tr>
            <td data-label="<?php echo $this->translate("Content Title"); ?>"><?php if( !empty($item->title) ){ echo $this->translate($item->title); } ?></td>
            <td data-label="<?php echo $this->translate("Frequency"); ?>"><?php if( !empty($item->frequency) ){ echo $item->frequency; } ?></td>  
            <td data-label="<?php echo $this->translate("Priority"); ?>"><?php if( !empty($item->priority) ){ echo $item->priority; } ?></td>  
            <td data-label="<?php echo $this->translate("Status"); ?>" class="text-center">
              <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'core', 'controller' => 'seo', 'action' => 'enabled', 'sitemap_id' => $item->sitemap_id ), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('title' => $this->translate('Disable'))), array())  : $this->htmlLink(array('route' => 'admin_default', 'module' => 'core', 'controller' => 'seo', 'action' => 'enabled', 'sitemap_id' => $item->sitemap_id ), $this->htmlImage('application/modules/Core/externals/images/admin/uncheck.png', '', array('title' => $this->translate('Enable')))) ) ?>
            </td>
            <td data-label="<?php echo $this->translate("Sitemap File"); ?>">
              <?php if(file_exists($filepath .DIRECTORY_SEPARATOR.'sitemap_'.$item->resource_type.'.xml')) { ?>
                <a href="<?php echo 'public'. DIRECTORY_SEPARATOR .'sitemap' .DIRECTORY_SEPARATOR.'sitemap_'.$item->resource_type.'.xml'; ?>" target="_blank"><?php echo $this->translate("Open File"); ?></a>
              <?php } else { ?>
              <?php echo $this->translate("No File"); ?>
              <?php } ?>
            </td>
            <td class="admin_table_options nowrap">
              <?php if($item->resource_type == 'menu_urls') { ?>
                <a href="<?php echo $this->url(array('action' => 'selectedmenus', 'sitemap_id' => $item->sitemap_id)) ?>" class="smoothbox"><?php echo $this->translate("Select Menus") ?></a> | 
              <?php } ?>
              <a href="<?php echo $this->url(array('action' => 'edit-settings', 'sitemap_id' => $item->sitemap_id)) ?>" class="smoothbox"><?php echo $this->translate("Edit") ?></a>
              
              <a href="<?php echo $this->url(array('action' => 'generate', 'sitemap_id' => $item->sitemap_id)) ?>" class="smoothbox">
                <?php if(file_exists($filepath .DIRECTORY_SEPARATOR.'sitemap_'.$item->resource_type.'.xml')) { ?>
                  |
                  <?php echo $this->translate("Regenerate Sitemap") ?>
                <?php } else { ?>
                  <?php $coreseo_select_menus = Engine_Api::_()->getApi('settings','core')->getSetting('coreseo_select_menus',''); ?>
                  <?php if($item->resource_type == 'menu_urls' &&!empty(json_decode($coreseo_select_menus))) { ?>
                    |
                    <?php echo $this->translate("Generate Sitemap") ?>
                  <?php } elseif($item->resource_type != 'menu_urls') { ?>
                    |
                    <?php echo $this->translate("Generate Sitemap") ?>
                  <?php } ?>
                <?php } ?>
              </a>
            </td>
          </tr>
        <?php  endforeach; ?>
      </tbody>
    </table>
  </form>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There is not any entry found with this criteria.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_seo').addClass('active');
</script>
