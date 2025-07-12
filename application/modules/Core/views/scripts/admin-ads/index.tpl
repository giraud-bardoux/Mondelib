<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'childMenuItemName' => 'core_admin_main_ads_manage')); ?>

<script type="text/javascript">
  function selectAll(obj)
  {
    scriptJquery('.checkbox').each(function(){
      scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
    });
  }

  var changeOrder =function(orderby, direction){
    scriptJquery('#orderby').val(orderby);
    scriptJquery('#orderby_direction').val(direction);
    scriptJquery('#filter_form').trigger("submit");
  }


  var delectSelected =function(){
    var checkboxes = scriptJquery('input[type=checkbox]');
    var selecteditems = [];

    checkboxes.each(function(){
      var item = scriptJquery(this);
      var checked = item.prop('checked');
      var value = item.val();
      if (checked == true && value != 'on'){
        selecteditems.push(value);
      }
    });

    scriptJquery('#ids').val(selecteditems);
    scriptJquery('#delete_selected').trigger("submit");
    //en4.core.baseUrl+'admin/announcements/deleteselected/selected/'+selecteditems;
    //window.location = "http://www.google.com/";
  }

 function changeStatus(adcampaign_id) {
    (scriptJquery.ajax({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'admin-ads', 'action' => 'status'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'adcampaign_id' : adcampaign_id
      },
      success : function(responseJSON)
      {
        window.location.reload();
      }
    }));

  }
</script>

<h2 class="page_heading">
  <?php echo $this->translate("Ads") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class="admin_common_top_section">
  <h3><?php echo $this->translate("Manage Ad Campaigns") ?></h3>
  <p><?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINADS_INDEX_DESCRIPTION") ?></p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/73/create-and-manage-ad-campaigns" target="_blank">See KB article</a>.';
  } 
  ?>	
</div>
<div>
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), 
        $this->translate("Create New Campaign"),
        array('class' => 'admin_link_btn admin_ads_create')) ?>
</div>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s ad campaign found", "%s ad campaigns found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>

<?php if( engine_count($this->paginator) ): ?>
  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <th style="width: 1%;"><input onclick='selectAll(this);' type='checkbox' class='checkbox'></th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('adcampaign_id', '<?php if($this->orderby == 'adcampaign_id') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('name', '<?php if($this->orderby == 'name') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Name") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("Status") ?>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("Ads") ?>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('views', '<?php if($this->orderby == 'views') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Views") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('clicks', '<?php if($this->orderby == 'clicks') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Clicks") ?>
          </a>
        </th>
        <th class='admin_table_centered' style="width: 1%;">
          <?php echo $this->translate("CTR") ?>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
      <tr>
        <td>
          <input type='checkbox' class='checkbox' value="<?php echo $item->adcampaign_id?>" />
        </td>
        <td data-label="ID">
          <?php echo $item->adcampaign_id ?>
        </td>
        <td data-label="<?php echo $this->translate("Name") ?>" class='admin_table_bold'>
          <?php echo $item->name ?>
        </td>
        <td data-label="<?php echo $this->translate("Status") ?>" class='admin_table_centered nowrap'>
          <?php echo join($this->translate(',') . '<br />', $this->status[$item->getIdentity()]) ?>
        </td>
        <td data-label="<?php echo $this->translate("Ads") ?>" class='admin_table_centered'>
          <?php echo $this->locale()->toNumber($item->getAdCount()) ?>
        </td>
        <td data-label="<?php echo $this->translate("Views") ?>" class='admin_table_centered'>
          <?php echo $this->locale()->toNumber($item->views) ?>
        </td>
        <td data-label="<?php echo $this->translate("Clicks") ?>" class='admin_table_centered'>
          <?php echo $this->locale()->toNumber($item->clicks) ?>
        </td>
        <td data-label="<?php echo $this->translate("CTR") ?>" class='admin_table_centered'>
          <?php if( $item->views <= 0 ): ?>
            <?php echo $this->translate('%s%%', $this->locale()->toNumber(0)) ?>
          <?php else: ?>
            <?php echo $this->translate('%s%%', $this->locale()->toNumber(100 * $item->clicks / $item->views)) ?>
          <?php endif ?>
        </td>
        <td class="admin_table_options">
          <a href="javascript:void(0);" onclick="javascript:changeStatus('<?php echo $item->adcampaign_id?>');">
            <?php if($item->status) echo $this->translate("pause"); else echo $this->translate("un-pause"); ?>
          </a> 
          |
          <?php echo $this->htmlLink(array('action' => 'manageads', 'id' => $item->adcampaign_id, 'reset' => false), $this->translate("manage")) ?> 
          |
          <?php echo $this->htmlLink(array('action' => 'edit', 'id' => $item->adcampaign_id, 'reset' => false), $this->translate("edit")) ?> 
          |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->getIdentity())) ?>'>
            <?php echo $this->translate("delete") ?>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class='buttons'>
    <button onclick="javascript:delectSelected();" type='submit'><?php echo $this->translate("Delete Selected") ?></button>
  </div>

  <form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
    <input type="hidden" id="ids" name="ids" value=""/>
  </form>

<?php else:?>

  <div class="tip">
    <span><?php echo $this->translate("You currently have no advertising campaigns.") ?></span>
  </div>

<?php endif; ?>


