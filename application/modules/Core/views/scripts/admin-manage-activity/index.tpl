<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_settings_activity', 'childMenuItemName' => 'core_admin_main_manage_activity')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class="admin_common_top_section">
  <h2><?php echo $this->translate("Manage Activity Feeds") ?></h2>
  <p><?php echo $this->translate("This page lists all of the activity feeds your users have posted on your site. You can use this page to monitor these activity feeds and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific activity feeds. Leaving the filter fields blank will show all of the activity feeds on your social network.") ?> </p>
</div>
<script type="text/javascript">
function multiModify(){
  var multimodify_form = scriptJquery('#multimodify_form');
  if (multimodify_form.submit_button.value == 'delete')
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected activities?")) ?>');
  }
}
function selectAll(obj){
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}
</script>
<div class='admin_search admin_common_search admin_manage_activity_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php $count = $this->paginator->getTotalItemCount() ?>
<?php if($count > 0) { ?>
  <div class='admin_results'>
    <div>
      <?php echo $this->translate(array("%s activity found.", "%s activities found.", $count), $this->locale()->toNumber($count)) ?>
    </div>
    <div>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'pageAsQuery' => true,
        'query' => $this->formValues,
      )); ?>
    </div>
  </div>
  <div class="admin_table_form">
    <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
      <table class='admin_table admin_responsive_table'>
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
            <th><?php echo $this->translate("Activity") ?></th>
            <th><?php echo $this->translate("Posted Date") ?></th>
            <th style='width:220px;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( engine_count($this->paginator) ): ?>
            <?php foreach( $this->paginator as $item ): ?>
              <tr>
                <td ><input name='modify_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>
                <td data-label="<?php echo $this->translate("ID") ?>"><?php echo $item->getIdentity() ?></td>
                <td data-label="<?php echo $this->translate("Activity") ?>">
                  <?php $contentData = $this->getContent($item, array('resource_id' => $item->resource_id, 'resource_type' => $item->resource_type)); ?>
                  <?php if (!empty($contentData[1])) { ?>
                   <?php echo $contentData[1]; ?>
                  <?php } else { ?>
                    <?php echo $this->getActionContent($item)?>
                  <?php } ?>
                </td>
                <td class="admin_table_options" data-label="<?php echo $this->translate("Posted Date") ?>">
                  <?php echo $this->timestamp($item->date) ?>
                </td>
                <td class='admin_table_options _comment_options'>
									<a target="_blank" href='<?php echo $item->getHref(); ?>'><?php echo $this->translate("View") ?></a>
                  |
                  <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->getIdentity()));?>'>
                    <?php echo $this->translate("Delete") ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class='buttons'>
        <button type='submit' name="submit_button" value="delete">
          <?php echo $this->translate("Delete Selected") ?>
        </button>
      </div>
    </form>
  </div>

<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no activity yet."); ?>
    </span>
  </div>
<?php } ?>
<script type="text/javascript">
  scriptJquery(``).insertBefore(scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","From").datepicker({
      timepicker: false,
    })
  );
  scriptJquery(``).insertBefore(scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","To").datepicker({
    timepicker: false,
   })
  );
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_activity').addClass('active');
</script>
