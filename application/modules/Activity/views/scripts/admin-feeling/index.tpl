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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'activity_admin_main_flngsettings')); ?>

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
        scriptJquery('#menu_list > div').each(function(e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        // Send request
        var url = '<?php echo $this->url(array('action' => 'order-manage-feeling')) ?>';
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
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected feelings category ?") ?>");
  }
  function selectAll()
  {
    var i;
    var multidelete_form = document.getElementById('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length - 1; i++) {
      inputs[i].checked = inputs[0].checked;
    }
  }
</script>
<h3><?php echo $this->translate("Manage Categories for Feelings & Activities"); ?></h3>
<p><?php echo $this->translate("Here, you can create Categories and manage Feelings & Activities in them. The categories can be of List type or Module type."); ?></p>
<p><?php echo $this->translate("The List type categories will be simple in which users will see only the activities and feelings entered by you in the manage section of each category."); ?></p>
<p><?php echo $this->translate("The Module type categories will have activities as content from the selected modules from the manage section of each category. Example: you can create a category Watching and select Module Video. Now, when your users will try to add watching activity they will see a list of all videos on your website in autosuggest box."); ?></p>
<p><?php echo $this->translate("Each category will have its own icon which will be shown in the status update box while adding a new feeling/activity."); ?></p>
<p><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'create-feelingcategory'), "<i class='fa fa fa-plus'></i> ". $this->translate("Create New Category"), array('class'=>'admin_link_btn smoothbox')); ?></p>
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>
  <?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
    <div class="admin_results">
      <?php echo $this->translate(array('%s category found.', '%s categories found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
    </div><?php endif; ?>
    <?php if(engine_count($this->paginator) > 0):?>
      <div class="activity_packs_listing" id='menu_list'>
        <?php foreach ($this->paginator as $item) : ?>
          <div class="activity_packs_item move" id="managefeelings_<?php echo $item->feeling_id ?>">
            <div class="activity_packs_item_inner d-flex">
              <input type='hidden'  name='order[]' value='<?php echo $item->feeling_id; ?>'>
              <div class="_icon">
                <?php $photo = Engine_Api::_()->storage()->get($item->file_id, '');
                if($photo) {
                $photo = $photo->getPhotoUrl(); ?>
                <img style="width:32px;" alt="" src="<?php echo $photo; ?>" />
                <?php } else { ?>
                  <?php echo "---"; ?>
                <?php } ?>
              </div>
              <div class="_cont">
                <div class="_title">
                  <?php echo $item->title ?>
                </div>
                <div class="_options">
                  <?php echo ($item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'enabled', 'feeling_id' => $this->feeling_id, 'id' => $item->feeling_id), '', array('title' => $this->translate('Disable'), 'class' => 'fa activity_icon_enabled')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'enabled', 'feeling_id' => $this->feeling_id, 'id' => $item->feeling_id), '', array('title' => $this->translate('Enable'), 'class' => 'fa activity_icon_disabled'))) ?>
                  |
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'create-feelingcategory', 'id' => $item->feeling_id), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
                  |
                  <?php if($item->type == 1) { ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'feelingicons', 'feeling_id' => $item->feeling_id, 'type' => $item->type), $this->translate("Manage Lists"), array()); ?>
                  <?php } else { ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'feelingicons', 'feeling_id' => $item->feeling_id, 'type' => $item->type), $this->translate("Manage Modules"), array()); ?>
                  <?php }?>
                  |
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'delete-feelingcategory', 'id' => $item->feeling_id),
                  $this->translate("Delete"),
                  array('class' => 'smoothbox')) ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
      </div>
    </div>
    <?php else:?>
      <div class="tip">
        <span>
          <?php echo "There are no feelings category created by you yet.";?>
        </span>
      </div>
    <?php endif;?>
  </form>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
