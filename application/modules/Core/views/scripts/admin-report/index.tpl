<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_reports')); ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('th.admin_table_short input[type=checkbox]').on('click', function(event) {
      var el = scriptJquery(event.target);
      scriptJquery('input[type=checkbox]').prop('checked', el.prop('checked'));
    });
  });
  
  var delectSelected = function() {
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
  }

</script>

<h2 class="page_heading">
  <?php echo $this->translate("Abuse Reports") ?>
</h2>
<p>
  <?php echo $this->translate("This page lists all of the reports your users have sent in regarding inappropriate content, system abuse, spam, and so forth. You can use the search field to look for reports that contain a particular word or phrase. Very old reports are periodically deleted by the system.") ?>
</p>
<p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/21/abuse-reports" target="_blank">See KB article</a>.';
  } 
  ?>
</p>  	
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <script type="text/javascript">
    var currentOrder = '<?php echo $this->filterValues['order'] ?>';
    var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        scriptJquery('#direction').val( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        scriptJquery('#order').val(order);
        scriptJquery('#direction').val(default_direction);
      }
      scriptJquery('#filter_form').trigger("submit");
    }
  </script>
  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

<?php endif; ?>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s report found", "%s reports found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>
<?php if( engine_count($this->paginator) ): ?>
<div class="admin_table_form">
  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox'></th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('report_id', 'ASC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th>
          <a href="javascript:void(0);" onclick="javascript:changeOrder('description', 'ASC');">
            <?php echo $this->translate("Description") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Reporter") ?>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');">
            <?php echo $this->translate("Date") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('subject_type', 'ASC');">
            <?php echo $this->translate("Content Type") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('category', 'ASC');">
            <?php echo $this->translate("Reasons") ?>
          </a>
        </th>
        <th style="width: 1%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item ): ?>
      <tr>
        <td><input type='checkbox' class='checkbox' value="<?php echo $item->report_id?>"></td>
        <td data-label="ID"><?php echo $item->report_id ?></td>
        <td data-label="<?php echo $this->translate("Description") ?>" style="white-space: normal;"><?php echo $this->escape($item->description) ?></td>
        <td data-label="<?php echo $this->translate("Reporter") ?>" class="nowrap"><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->getTitle(), array('target' => '_blank')) ?></td>
        <td data-label="<?php echo $this->translate("Date") ?>" class="nowrap"><?php echo $item->creation_date ?></td>
        <td data-label="<?php echo $this->translate("Content Type") ?>" class="nowrap"><?php echo $item->subject_type ?></td>
        <td data-label="<?php echo $this->translate("Reasons") ?>" class="nowrap"><?php echo $item->category ?></td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(array('action' => 'action', 'id' => $item->getIdentity(), 'reset' => false, 'format' => 'smoothbox'), $this->translate("take action"), array('class' => 'smoothbox')) ?>
          |
          <span class="sep"></span>
          <?php if( !empty($item->subject_type) ): ?>
            <?php echo $this->htmlLink(array('action' => 'view', 'id' => $item->getIdentity(), 'reset' => false), $this->translate("view content"), array('target' => '_blank')) ?>
            <span class="sep"></span>
          <?php endif; ?>
          |
          <?php echo $this->htmlLink(array('action' => 'delete', 'id' => $item->getIdentity(), 'reset' => false), $this->translate("dismiss")) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class='buttons'>
    <button onclick="javascript:delectSelected();" type='submit'><?php echo $this->translate("Dismiss Selected") ?></button>
  </div>

  <form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
    <input type="hidden" id="ids" name="ids" value=""/>
  </form>

<?php else:?>

  <div class="tip">
    <span><?php echo $this->translate("There are currently no outstanding abuse reports.") ?></span>
  </div>

<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_reports').addClass('active');
</script>
