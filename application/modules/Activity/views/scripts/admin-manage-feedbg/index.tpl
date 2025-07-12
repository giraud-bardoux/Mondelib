<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'activity_admin_main_febgsettings')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">

  var SortablesInstance;

  scriptJquery( window ).load(function() {
    var SortablesInstance = scriptJquery('#menu_list').sortable({
      stop: function( event, ui ) {
        var ids = [];
        scriptJquery('#menu_list > li').each(function(e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        // Send request
        var url = '<?php echo $this->url(array('action' => 'order')) ?>';
        scriptJquery.ajax({
            url : url,
            dataType : 'json',
            data : {
                format : 'json',
                order : ids
            }
        });
      }
    });
  });

  function multiDelete()
  {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected feed backgrounds?") ?>");
  }
  function selectAll() {
    var i;
    var multidelete_form = document.getElementById('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }
</script>

<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>

<h3><?php echo $this->translate("Manage Background Images for Status Updates"); ?></h3>
<p><?php echo $this->translate("This page lists all the background images uploaded by you. You can add new background images individually. To reorder the background images, click on and drag them up or down.") ?></p>
<p><?php echo $this->translate("You can also mark background images as Featured. These Featured images will always show in the status update boxes before other images. We recommend to mark maximum 12 images as Featured, so that users can see other images also.");?></p>
<p class="">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'create'), $this->translate("Upload Image"), array('class'=>'admin_link_btn smoothbox')); ?>
</p>
      
<?php if(engine_count($this->paginator) > 0):?>
  <p class="mb-2">
    <input onclick="selectAll()" type='checkbox' class='checkbox'> Select All
  </p>
<?php endif; ?>
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <div class="admin_results">
    <?php echo $this->translate(array('%s background image found.', '%s background images found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
<?php endif; ?>
<?php if(engine_count($this->paginator) > 0):?>
  <div class="clear">
    <ul class="activity_grid_list" id='menu_list'>
      <?php foreach ($this->paginator as $item) : ?>
        <li class="item_label move" id="managebackgrounds_<?php echo $item->background_id ?>">
          <div class="activity_grid_list_item">
            <div class="activity_grid_list_header">
              <div class="activity_grid_list_input">
                <input type='checkbox' class='checkbox' name='delete_<?php echo $item->background_id;?>' value='<?php echo $item->background_id ?>' />
              </div>
              <div class="activity_grid_list_options">
                <?php echo ( $item->featured ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'featured', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title'=> $this->translate('Remove From Featured'), 'class' => 'fa-solid activity_icon_featured')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'featured', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title'=> $this->translate('Mark Featured'), 'class' => 'fa-solid activity_icon_unfeatured')) ) ?>&nbsp;         
                
                <?php echo ($item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'enabled', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title' => $this->translate('Disable'), 'class' => 'fa-solid activity_icon_enabled')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'enabled', 'background_id' => $this->background_id, 'id' => $item->background_id), '', array('title' => $this->translate('Enable'), 'class' => 'fa-solid activity_icon_disabled'))) ?>&nbsp;
                
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'create', 'id'=>$item->background_id), '', array('class' => 'smoothbox fa-solid activity_icon_edit', 'title' => $this->translate('Edit'))) ?>&nbsp;

                <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'manage-feedbg', 'action' => 'delete', 'id' => $item->background_id), '', array('class' => 'smoothbox fa-solid activity_icon_delete', 'title' => $this->translate('Delete'))) ?>
              </div>
            </div>
            <div class="activity_grid_list_img">
              <?php $photo = Engine_Api::_()->storage()->get($item->file_id, ''); ?>
              <?php if($photo) { ?>
                <?php $photo = $photo->map(); ?>
                <img alt="" src="<?php echo $photo; ?>" />
              <?php } ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
    </div>
  </div>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no images added by you.");?>
    </span>
  </div>
<?php endif;?>
</form>
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
