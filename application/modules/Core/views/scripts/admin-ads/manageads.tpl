<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manageads.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_ads', 'childMenuItemName' => 'adcampaign_admin_main_manageads')); ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('th.admin_table_short input[type=checkbox]').on('click', function(event) {
      var el = scriptJquery(event.target);
      scriptJquery('input[type=checkbox]').prop('checked', el.prop('checked'));
    });
  });

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
    scriptJquery('input[type=radio]').attr('disabled', true);
    (scriptJquery.ajax({
      dataType: 'json',
      url : '<?php echo $this->url(array('module' => 'core', 'controller' => 'admin-ads', 'action' => 'status'), 'default', true) ?>',
      data : {
        format : 'json',
        adcampaign_id : adcampaign_id
      },
      success : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    }));
  }
</script>



<h2 class="page_heading">
  <?php echo $this->translate('Editing Ad Campaign: %1$s', $this->campaign->name) ?>
</h2>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<h2>
  <?php echo $this->translate("Manage Advertisements") ?>
</h2>

<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINADS_MANAGEADS_DESCRIPTION") ?>
</p>
<div>
  <?php echo $this->htmlLink(array('action' => 'createad', 'id'=> $this->campaign_id, 'reset' => false),
      $this->translate("Add New Advertisement"), array(
      'class' => 'admin_link_btn')) ?>
</div>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s ads found", "%s ads found", $count), $count) ?>
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
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('ad_id', '<?php if($this->orderby == 'ad_id') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('name', '<?php if($this->orderby == 'name') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Name") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('views', '<?php if($this->orderby == 'views') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Views") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('clicks', '<?php if($this->orderby == 'clicks') echo "ASC"; else echo "DESC"; ?>');">
            <?php echo $this->translate("Clicks") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("CTR") ?>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
    <?php foreach( $this->paginator as $item ): ?>
      <tr>
        <td data-label="ID"><?php echo $item->ad_id ?></td>
        <td data-label="<?php echo $this->translate("Name") ?>" style="white-space: normal;"><?php echo $item->name ?></td>
        <td data-label="<?php echo $this->translate("Views") ?>"><?php echo $item->views ?></td>
        <td data-label="<?php echo $this->translate("Clicks") ?>"><?php echo $item->clicks ?></td>
        <td data-label="<?php echo $this->translate("CTR") ?>"><?php if($item->views) {echo (int)($item->clicks/$item->views*100);} else {echo 0;} ?>%</td>
        <td class="admin_table_options">
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'editad', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("edit") ?>
          </a> 
          |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'preview', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("preview") ?>
          </a> 
          |
          <a class='smoothbox' href='<?php echo $this->url(array('action' => 'deletead', 'id' => $item->ad_id)) ?>'>
            <?php echo $this->translate("delete") ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

<?php else:?>

  <div class="tip">
    <span><?php echo $this->translate("There are no advertisements added to this campaign.") ?></span>
  </div>

<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_ads').addClass('active');
</script>
