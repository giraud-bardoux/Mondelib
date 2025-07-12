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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_tickets')); ?>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="admin_common_top_section">
  <p><?php echo $this->translate('This page lists all the support tickets opened by admins and members on your website. You can create a new ticket from here by clicking on "Open New Ticket" button below.') ?> </p>
</div>
<div class="admin_results">
  <a href='<?php echo $this->url(array('action' => 'create')) ?>' class="smoothbox admin_link_btn"><?php echo $this->translate("Open New Ticket") ?></a>
</div>

<script type="text/javascript">

  var modulename = 'core';
  var type = 'tickets';
  var category_id = '<?php echo $this->category_id; ?>';
  var subcat_id = '<?php echo $this->subcat_id; ?>';
  var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

  en4.core.runonce.add(function() {
    if(category_id && category_id != 0) {
      showSubCategory(category_id, subcat_id);
    } else {
      if(scriptJquery('#category_id').val()) {
        showSubCategory(scriptJquery('#category_id').val());
      } else {
        if(document.getElementById('subcat_id-wrapper'))
          document.getElementById('subcat_id-wrapper').style.display = "none";
      }
    }

    if(subsubcat_id) {
      if(subcat_id && subcat_id != 0) {
        showSubSubCategory(subcat_id, subsubcat_id);
      } else {
        if(document.getElementById('subsubcat_id-wrapper'))
          document.getElementById('subsubcat_id-wrapper').style.display = "none";
      }
    } else if(subcat_id) {
      showSubSubCategory(subcat_id);
    }
    else {
      if(document.getElementById('subsubcat_id-wrapper'))
        document.getElementById('subsubcat_id-wrapper').style.display = "none";
    }
  });

  en4.core.runonce.add(function() {
    scriptJquery("#selectall").click(function(){
      if(this.checked){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', true);
        });
      } else {
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', false);
        });
      }
    });
    
    scriptJquery("input[name='delete']").on('click', function( event ) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to perform this action on selected entries?")) ?>')) {
          scriptJquery('#multidelete_form').append("<input type='hidden' value='"+name+"' name='"+name+"'>");
          scriptJquery('#multidelete_form').trigger("submit");
        }
      }
    });
  });
</script>
<div class='admin_search admin_common_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php $count = $this->paginator->getTotalItemCount() ?>
<?php if($count > 0) { ?>
  <div class="admin_table_form">
    <form id="multidelete_form" action="<?php echo $this->url();?>" method="POST">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="_count">
          <?php echo $this->translate(array('%s ticket found.', '%s tickets found.', $count), $count) ?>
        </div>
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <input type='submit' value="Delete" name="delete" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true, 'query' => $this->formValues)); ?>
        </div>
      </div>
      <div class="admin_responsive_table">
        <table class='admin_table'>
          <thead>
            <tr>
              <th style='width: 1%;'><input id="selectall" type='checkbox'></th>
              <th style='width: 1%;'><?php echo $this->translate("Ticket ID") ?></th>
              <th><?php echo $this->translate("Subject") ?></th>
              <?php if(engine_count($this->categories) > 0) { ?>
                <th><?php echo $this->translate("Category") ?></th>
              <?php } ?>
              <th><?php echo $this->translate("Requestor") ?></th>
              <th><?php echo $this->translate("Created") ?></th>
              <th><?php echo $this->translate("Last Reply") ?></th>
              <th style='width:220px;'><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if( engine_count($this->paginator) ): ?>
              <?php foreach( $this->paginator as $item ): ?>
                <?php if(!empty($item->resource_type) && $item->resource_id) { ?>
                  <?php $resource = Engine_Api::_()->getItem($item->resource_type, $item->resource_id); ?>
                <?php } ?>
                <?php $poster = Engine_Api::_()->getItem('user', $item->user_id); ?>
                <tr>
                  <td><input name='selectedItems[]' value='<?php echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>
                  <td data-label="Ticket ID"><a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'>#<?php echo $item->getIdentity() ?></a></td>
                  <td data-label="Subject"><a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'><?php echo $item->subject ? $item->subject : '---'; ?></a></td>
                  <?php if(engine_count($this->categories) > 0) { ?>
                    <td data-label="Category">
                      <?php if(!empty($item->category_id)) { ?>
                        <?php $category = Engine_Api::_()->getItem('core_category', $item->category_id); ?>
                        <?php echo $category->category_name; ?>
                      <?php } else echo "---"; ?>
                    </td>
                  <?php } ?>
                  <td data-label="Requestor" class="nowrap"><a href="<?php echo $poster->getHref(); ?>"><?php echo $poster->getTitle(); ?></a></td>
                  <td data-label="Created" class="nowrap"><?php echo $this->timestamp($item->creation_date); ?></td>
                  <td data-label="Last Reply" class="nowrap"><?php echo $this->timestamp($item->lastreply_date); ?></td>
                  <td class='admin_table_options _comment_options nowrap'>
                    <a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'><?php echo $this->translate("Read") ?></a>
                    <?php if(!empty($item->resource_type) && $item->resource_id && $resource) { ?>
                      |
                      <a target='_blank' href='<?php echo $resource->getHref();?>'><?php echo $this->translate("View Content") ?></a>
                    <?php } ?>
                    |
                    <a href='<?php echo $this->url(array('action' => 'delete', 'ticket_id' => $item->ticket_id)) ?>' class="smoothbox"><?php echo $this->translate("Delete") ?></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>  
    </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no tickets yet."); ?>
    </span>
  </div>
<?php } ?>

<script type="text/javascript">
  var sesselectedDate;
  scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('From'); ?>").datepicker({
  }).on('change', function(ev){
    sesselectedDate = scriptJquery('#date-date_from').val();
    scriptJquery('#date-date_to').datepicker('option', 'minDate', scriptJquery('#date-date_from').val());
  });
  
  scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('To'); ?>").datepicker({
    minDate: sesselectedDate,
  });

  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_tickets').addClass('active');
</script>
