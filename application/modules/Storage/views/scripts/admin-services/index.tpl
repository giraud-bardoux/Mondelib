<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_storage')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Manage Storage Services") ?></h2>
  <p> <?php echo $this->translate("STORAGE_VIEWS_ADMIN_SERVICES_INDEX_DESCRIPTION") ?>
    <a class="admin help" href="https://community.socialengine.com/blogs/597/123/how-to-use-the-cloud-storage-feature" target="_blank"> </a>
  </p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/43/storage-system" target="_blank">See KB article</a>.';
  } 
  ?>	
</div>
<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Service'), array(
    'class' => 'admin_link_btn',
  )) ?>
</div>
<?php if( !empty($this->activeJobs) && $this->activeJobs->count() > 0 ): ?>
  <ul class="form-notices">
    <?php foreach( $this->activeJobs as $activeJob ): ?>
      <li>
        <?php echo $this->translate('File are currently being transferred.') ?>
        <?php echo $this->translate('%1$s%% complete.', $this->locale()->toNumber(100 * $activeJob->progress)) ?>
      </li>
    <?php endforeach ?>
  </ul>
<?php endif; ?>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s service found",
        "%s services found", $count), $this->locale()->toNumber($count)) ?>
  </div>
  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'query' => $this->filterValues,
    'pageAsQuery' => true,
  )); ?>
</div>

<script type="text/javascript">
  function setDefaultStorageService(service_id) {
    <?php if( _ENGINE_ADMIN_NEUTER ) { ?>
      alert('disabled');
      return false;
    <?php } ?>
    scriptJquery('input[type=radio]').attr('disabled', true);
    var req = scriptJquery.ajax({
      format: 'json',
      url : '<?php echo $this->url(array('action' => 'set-default')) ?>',
      data : {
        'format' : 'json',
        'service_id' : service_id
      },
      success : function(responseJSON, responseText) {
        window.location.reload();
      }
    });
  }
</script>


<table class='admin_table admin_responsive_table'>
  <thead>
    <tr>
      <th style='width: 1%;'>
        <?php echo $this->translate("ID") ?>
      </th>
      <th>
        <?php echo $this->translate("Title") ?>
      </th>
      <th style='width: 1%;'>
        <?php echo $this->translate('Files') ?>
      </th>
      <th>
        <?php echo $this->translate('Storage Used') ?>
      </th>
      <th style='width: 1%;' class='admin_table_centered'>
        <?php echo $this->translate("Enabled") ?>
      </th>
      <th style='width: 1%;' class='admin_table_centered'>
        <?php echo $this->translate("Default") ?>
      </th>
      <th style='width: 150px;'><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if( engine_count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td data-label="ID">
            <?php echo $this->locale()->toNumber($item->service_id) ?>
          </td>
          <td data-label="<?php echo $this->translate("Title") ?>" class='admin_table_bold'>
            <?php echo $this->translate($this->serviceTypes[$item->servicetype_id]['title']) ?>
          </td>
          <td data-label="<?php echo $this->translate("Files") ?>" class='admin_table_centered'>
            <?php echo $this->locale()->toNumber($this->serviceFileInfo[$item->service_id]['count']) ?>
          </td>
          <td data-label="<?php echo $this->translate("Storage Used") ?>"  style="width: 1%; white-space: nowrap;">
            <?php echo $this->translate('%1$s bytes',
                $this->locale()->toNumber((integer) $this->serviceFileInfo[$item->service_id]['size'])) ?>
          </td>
          <td data-label="<?php echo $this->translate("Enabled") ?>" class='admin_table_centered'>
            <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td data-label="<?php echo $this->translate("Default") ?>" class='admin_table_centered'>
            <?php if( $item->default ): ?>
              <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/notice.png" alt="Default" />
            <?php else: ?>
              <?php echo $this->formRadio('default', $item->service_id, array('onchange' => "setDefaultStorageService({$item->service_id});",'disable'=>!$item->enabled), '') ?>
            <?php endif; ?>
          </td>
          <td class='admin_table_options'>
            <a href='<?php echo $this->url(array('action' => 'edit', 'service_id' => $item->service_id)) ?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            |
            <?php if( empty($this->serviceFileInfo[$item->service_id]['count']) && !$item->default ): ?>
              <a href='<?php echo $this->url(array('action' => 'delete', 'service_id' => $item->service_id)) ?>'>
                <?php echo $this->translate("delete") ?>
              </a>
              |
            <?php endif ?>
            <?php if( $item->enabled ): ?>
              <a href='<?php echo $this->url(array('action' => 'transfer', 'service_id' => $item->service_id)) ?>'>
                <?php echo $this->translate("transfer") ?>
              </a>
            <?php endif ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_storage').addClass('active');
</script>
