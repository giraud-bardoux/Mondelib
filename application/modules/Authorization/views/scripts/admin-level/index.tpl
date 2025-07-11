<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'authorization_admin_main_manage')); ?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    scriptJquery('th.admin_table_short input[type=checkbox]').on('click', function(event){
      var el = scriptJquery(event.target);
      scriptJquery('input[type=checkbox]:not(:disabled)').prop('checked', el.prop('checked'));
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

  function setDefault(level_id) {
    scriptJquery('input[type=radio]').attr('disabled', true);
    (scriptJquery.ajax({
      format: 'json',
      url : '<?php echo $this->url(array('module' => 'authorization', 'controller' => 'admin-level', 'action' => 'set-default'), 'default', true) ?>',
      data : {
        format : 'json',
        level_id : level_id
      },
      success : function(responseJSON)
      {
        window.location.reload();
      }
    }));
  }
</script>
<h2 class="page_heading"> <?php echo $this->translate("Member Levels") ?> </h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<p>
  <?php $link = $this->htmlLink(
    array('module' => 'user', 'controller' => 'manage', 'action' => 'index', "route"=>"admin_default"),
    $this->translate("View Members")) ?>
  <?php echo $this->translate("AUTHORIZATION_VIEWS_SCRIPTS_ADMINLEVEL_DESCRIPTION", $link) ?>
</p>

<?php
	$settings = Engine_Api::_()->getApi('settings', 'core');
	if( $settings->getSetting('user.support.links', 0) == 1 ) {
		echo 'More info: <a href="https://community.socialengine.com/blogs/597/14/member-levels" target="_blank">See KB article</a>.';
	} 
?>	
<?php echo $this->formFilter->render($this) ?>
<div class="admin_results">
  <div>
    <?php echo $this->htmlLink(array('action' => 'create', 'reset' => false), $this->translate('Add Member Level'), array(
      'class' => 'admin_link_btn',
    )) ?>
  </div>
  <div>
    <?php $levelCount = $this->paginator->getTotalItemCount(); ?>
    <?php echo $this->translate(array("%d level found","%d levels found", $levelCount), $levelCount); ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
</div>

<table class='admin_table admin_responsive_table'>
  <thead>
    <tr>
      <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox' /></th>
      <th style="width: 1%;"><a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', '<?php if($this->orderby == 'level_id') echo "ASC"; else echo "DESC"; ?>');">ID</a></th>
      <th>
        <a href="javascript:void(0);" onclick="javascript:changeOrder('title', '<?php if($this->orderby == 'title') echo "ASC"; else echo "DESC"; ?>');">
          <?php echo $this->translate("Level Name") ?>
        </a>
      </th>
      <th style="width: 150px;"><?php echo $this->translate("Members") ?></th>
      <th style="width: 1%;"><?php echo $this->translate("Type") ?></th>
      <th style="width: 1%;" class="admin_table_centered"><?php echo $this->translate("Default Level") ?></th>
      <th style="width: 150px;"><?php echo $this->translate("Options") ?></th>
    </tr>

  </thead>
  <tbody>
    <?php if( engine_count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
        <td><input <?php if ($item->flag) echo 'disabled';?> type='checkbox' class='checkbox' value="<?php echo $item->level_id?>"></td>
          <td data-label="ID">
            <?php echo $item->level_id ?>
          </td>
          <td data-label="<?php echo $this->translate("Level Name") ?>" class="admin_table_bold">
            <?php echo $this->translate($item->title) ?>
          </td>
          <td data-label="<?php echo $this->translate("Members") ?>" class="nowrap">
            <?php $membershipCount = $item->getMembershipCount(); ?>
            <?php echo $this->htmlLink(array('module' => 'user', 'controller' => 'manage', 'level_id' => $item->level_id, 'reset' => false),
             $this->translate(array("%s member", "%s members", $membershipCount), $this->locale()->toNumber($membershipCount))) ?>
          </td>
          <td data-label="<?php echo $this->translate("Type") ?>">
            <?php echo $this->translate(ucfirst($item->type == 'user' ? 'normal' : $item->type)) ?>
          </td>
          <td data-label="<?php echo $this->translate("Default Level") ?>" class="admin_table_centered">
            <?php if( $item->flag == 'default' ): ?>
              <img src="application/modules/Core/externals/images/notice.png" alt="Default" />
            <?php else: ?>
              <?php echo $this->formRadio('default', $item->level_id, array('onchange' => "setDefault({$item->level_id});",'disable'=>($item->flag || $item->type != 'user')), '') ?>
            <?php endif; ?>
          </td>
          <td class="admin_table_options">
            <a href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->level_id)) ?>'>
              <?php echo $this->translate("edit") ?>
            </a>
            <?php if (!$item->flag) :?>
            |
            <a href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->level_id)) ?>'>
              <?php echo $this->translate("delete") ?>
            </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>

</table>

<br/>
<div class='buttons'>
  <button onclick="javascript:delectSelected();" type='submit'>
    <?php echo $this->translate("Delete Selected") ?>
  </button>
</div>

<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' =>'deleteselected')) ?>'>
  <input type="hidden" id="ids" name="ids" value=""/>
</form>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_levels').addClass('active');
</script>
