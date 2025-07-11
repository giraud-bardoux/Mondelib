<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: filter-content.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'activity_admin_filter')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<?php if(is_countable($this->subNavigation) && engine_count($this->subNavigation) ): ?>
  <div class='sub_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
  </div>
<?php endif; ?>

<h3><?php echo $this->translate("Manage Filters"); ?></h3>
<p><?php echo $this->translate('In this page you can manage various filters for displaying feeds on the member home page of your website. Here, in addition to the default filters, you can also create new filter of different modules.To create a new filter click on "Create New Filter" link. You can also enable, disable or edit any module.<br />To reorder the filters, click on their row and drag them up or down.'); ?></p>
<div class="admin_results">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'settings', 'action' => 'create'), $this->translate("Create New Filter"), array('class'=>'admin_link_btn smoothbox')); ?>
</div>

<?php if(engine_count($this->paginator) > 0):?>
  <form method="post" action="">
    <table class="admin_table admin_responsive_table">
      <thead class="" style="width:100%;">
        <tr>
          <th style="width:25%">
            <?php echo "Module";?>
          </th>
          <th style="width:25%">
            <?php echo $this->translate("Title") ?>
          </th>
          <th style="width:25%" class="admin_table_centered">
            <?php echo $this->translate("Status") ?>
          </th>
          <th style="width:25%">
            <?php echo $this->translate("Options") ?>
          </th>
        </tr>
      </thead>
      <tbody class="" id='menu_list'>
      <?php $notinclude = array('all', 'my_networks', 'my_friends', 'posts', 'saved_feeds', 'post_self_buysell', 'post_self_file', 'scheduled_post', "share"); ?>
      <?php foreach ($this->paginator as $item) : ?>
        <?php if(!engine_in_array($item->filtertype, $notinclude) && !Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled($item->filtertype)) continue; ?>
        <tr class="item_label" id="filter_<?php echo $item->getIdentity(); ?>">
        <input type="hidden" name="order[]" value="<?php echo $item->getIdentity(); ?>">
          <td data-label="<?php echo $this->translate("Module"); ?>">
            <?php echo ucfirst($item->module); ?>
          </td>
          <td data-label="<?php echo $this->translate("Title") ?>">
            <?php echo $item->title; ?>
          </td>
          <td data-label="<?php echo $this->translate("Status") ?>" class="admin_table_centered">
            <?php echo ( $item->active ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'settings', 'action' => 'enabled', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('title' => $this->translate('Disable'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'settings', 'action' => 'enabled', 'id' => $item->getIdentity()), $this->htmlImage('application/modules/Core/externals/images/admin/uncheck.png', '', array('title' => $this->translate('Enable')))) ) ?>
          </td>  
          <td class="admin_table_options">          
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'settings', 'action' => 'create','id'=>$item->getIdentity()), $this->translate("Edit"), array('class'=>'smoothbox'));
            if($item->is_delete){
            ?>
            |
            <?php echo $this->htmlLink(
              array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'settings', 'action' => 'delete', 'id' => $item->getIdentity()),
                $this->translate("Delete"),
                array('class' => 'smoothbox'));
                }
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate('Save Order'); ?></button>
    </div>          
  </form>
<?php endif;?>

<script type="text/javascript"> 
var SortablesInstance = scriptJquery('#menu_list').sortable({
  stop: function( event, ui ) {
    var ids = [];
    scriptJquery('#menu_list > tr').each(function(e) {
      var el = scriptJquery(this);

    });
  }
}); 
</script>
