<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10051 2013-06-11 23:36:56Z jung $
 * @author     Sami
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_announcements')); ?>

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
  }
</script>
<h2 class="page_heading"><?php echo $this->translate('Manage Announcements') ?></h2>
<p>
  <?php echo $this->translate('ANNOUNCEMENT_VIEW_SCRIPTS_ADMINMANAGE_DESCRIPTION', $this->url(array('module'=>'core','controller'=>'content'), 'admin_default')) ?>
</p>
<p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/16/announcements" target="_blank">See KB article</a>';
  } 
  ?>	
</p>
<?php echo $this->formFilter->render($this) ?>

<div class="admin_search">
  <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), 
    $this->translate("Post New Announcement"),
    array(
      'class' => 'admin_link_btn',)) ?>
  <?php if($this->paginator->getTotalItemCount()!=0): ?>
    <?php echo $this->translate(array('%s announcement total.', '%s announcements total.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())); ?>
  <?php endif;?>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
<?php if( engine_count($this->paginator) ): ?>
  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox'></th>
        <th style="width: 1%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('announcement_id', '<?php if($this->orderby == 'announcement_id') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("ID") ?>
        </a></th>
        <th style="width: 65%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('title', '<?php if($this->orderby == 'title') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("Title") ?>
        </a></th>
        <th style="width: 30%;"><?php echo $this->translate("Author") ?></th>
        <th style="width: 15%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', '<?php if($this->orderby == 'creation_date') echo "DESC"; else echo "ASC"; ?>');">
          <?php echo $this->translate("Date") ?>
        </a></th>
        <th style="width: 15%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
      <tr>
        <td><input type='checkbox' class='checkbox' value="<?php echo $item->announcement_id?>"></td>
        <td  data-label="ID"><?php echo $item->announcement_id ?></td>
        <td data-label="<?php echo $this->translate("Title") ?>" class="admin_table_bold"><?php echo $item->title ?></td>
        <td data-label="<?php echo $this->translate("Author") ?>"><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->getTitle(), array('target' => '_blank')) ?></td>
        <td class="admin_announcement_date" data-label="<?php echo $this->translate("Date") ?>"><?php echo $this->locale()->toDateTime( $item->creation_date ) ?></td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(
            array('action' => 'edit', 'id' => $item->getIdentity(), 'reset' => false),
            $this->translate('edit')) ?> 
            |
          <?php echo $this->htmlLink(
            array('action' => 'delete', 'id' => $item->getIdentity(), 'reset' => false),
            $this->translate('delete')) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class='buttons'>
    <button onclick="javascript:delectSelected();" type='submit'>
      <?php echo $this->translate("Delete Selected") ?>
    </button>
  </div>
  <form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
    <input type="hidden" id="ids" name="ids" value=""/>
  </form>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are currently no announcements.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_announcements').addClass('active');
</script>
