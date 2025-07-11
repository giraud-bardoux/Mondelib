<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: feelingicons.tpl 2024-10-28 00:00:00Z 
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
        var url = '<?php echo $this->url(array('action' => 'order-manage-feelingicons')) ?>';
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
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected feeling icons?") ?>");
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

<?php if($this->feeling->type == 1) { ?>
  <h3><?php echo $this->translate("Manage Lists for %s category", $this->feeling->title); ?></h3>
  <p><?php echo $this->translate("Below, you can add new feelings or activities for %s category. Users will see this list on selecting this category from the Feeling/Activity option in the status updates box.", $this->feeling->title); ?></p>
<?php } else { ?>
  <h3><?php echo $this->translate("Manage Modules for %s category", $this->feeling->title); ?></h3>
  <p><?php echo $this->translate("Below, you can add modules on website to be shown as feelings or activities for %s category. Users will see content from selected modules in auto-suggest box on selecting this category from the Feeling/Activity option in the status updates box.", $this->feeling->title); ?></p>
<?php } ?>
<p>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'index'), $this->translate("Back to Manage Categories"), array('class'=>'admin_link_btn')); ?>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'add-feelingicon','feeling_id' => $this->feeling_id, 'type' => $this->type), $this->translate("Add New Feeling/Activity List Item"), array('class'=>'admin_link_btn smoothbox')); ?>
</p>

<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>
  <?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
    <div class="admin_results">
      <?php if($this->feeling->type == 1) { ?>
        <?php echo $this->translate(array('%s feeling/activity list item found.', '%s feeling/activity list items found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      <?php } else { ?>
        <?php echo $this->translate(array('%s module found.', '%s modules found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      <?php } ?>
    </div>
  <?php endif; ?>
  <?php if(engine_count($this->paginator) > 0):?>

    <div class="activity_packs_listing" id='menu_list'>
        <?php foreach ($this->paginator as $item): ?>
        <div class="activity_packs_item move" id="managefeelingicons_<?php echo $item->feelingicon_id ?>">
          <div class="activity_packs_item_inner d-flex">
            <input type='hidden'  name='order[]' value='<?php echo $item->feelingicon_id; ?>'>
            <div class="_input">
              <input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
            </div>
            <?php if($item->type == 1){ ?>
              <div class="_icon">
                <img style="width:32px;" alt="" src="<?php echo Engine_Api::_()->storage()->get($item->feeling_icon, '')->getPhotoUrl(); ?>" />
              </div>
            <?php } ?>
            <div class="_cont">
              <div class="_title" title="<?php echo $item->title ?>">
                <?php echo $item->title ?>
              </div>
              <div class="_options">
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'add-feelingicon', 'id' => $item->getIdentity(),'feeling_id'=>$this->feeling_id, 'type' => $this->type), $this->translate("Edit"), array('title'=> $this->translate("Edit"), 'class' => 'smoothbox')) ?>
                |
                <?php echo $this->htmlLink(
                    array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'feeling', 'action' => 'delete-feelingicon', 'id' => $item->getIdentity(), 'type' => $this->type), $this->translate("Delete"), array('title'=> $this->translate("Delete"), 'class' => 'smoothbox')) ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
    </div>
  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("There are no feeling icon created by you yet.");?>
      </span>
    </div>
  <?php endif;?>
</form>
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
