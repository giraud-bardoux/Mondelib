<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_signup', 'childMenuItemName' => 'core_admin_manageavatars')); ?>

<h2 class="page_heading"><?php echo $this->translate('Signup & Profile Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">

  en4.core.runonce.add(function() {
    scriptJquery('#menu_list').addClass('sortable');
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
</script>

<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected avatar images?") ?>");
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

  <h3><?php echo $this->translate("Manage Avatar Images"); ?></h3>
  <p><?php echo $this->translate("This page lists all the Avatar Images uploaded by you. From below you can upload Avatar Images from your computer or hard drive. <br />") ?></p>

  <?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
    <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
  <p>
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'avatars', 'action' => 'create'), $this->translate("Upload Avatar Image"), array('class'=>'smoothbox admin_link_btn')); ?>
  </p>
  <?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
    <div class="mb-2">
      <?php echo $this->translate(array('%s avatar image found.', '%s avatar images found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
    </div>
  <?php endif; ?>
  <?php if(engine_count($this->paginator) > 0):?>
    <div class="clear">
      <ul class="user_avatar_list d-flex flex-wrap" id='menu_list'>
        <?php foreach ($this->paginator as $item) : ?>
          <?php if(empty($item->file_id)) continue; ?>
          <li class="user_avatar_list_item" id="manageimages_<?php echo $item->avatar_id ?>">
          	<article>
              <div class="user_packs_list_item_head d-flex mb-2">
                <div class="user_packs_list_item_input flex-grow-1">
                  <input type='checkbox' class='checkbox' name='delete_<?php echo $item->avatar_id;?>' value='<?php echo $item->avatar_id ?>' />
                </div>
                <div class="user_avatar_list_item_options">
                  <?php echo ($item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'avatars', 'action' => 'enabled', 'avatar_id' => $this->avatar_id, 'id' => $item->avatar_id), '', array('title' => $this->translate('Disable'), 'class' => 'icon_enable icon')) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'avatars', 'action' => 'enabled', 'avatar_id' => $this->avatar_id, 'id' => $item->avatar_id), '', array('title' => $this->translate('Enable'), 'class' => 'icon_disable icon'))) ?>&nbsp;
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'avatars', 'action' => 'delete', 'id' => $item->avatar_id), '', array('class' => 'smoothbox icon_delete icon', 'title' => $this->translate('Delete'))) ?>
                </div>
              </div>
              <div class="user_avatar_list_item_img">
                <?php $photo = Engine_Api::_()->storage()->get($item->file_id, '');
                if($photo) { ?>
                <img alt="" src="<?php echo $photo->getPhotoUrl(); ?>" />
                <?php } else { ?> 
                <?php echo "---"; ?>
                <?php } ?>
              </div>
            </article>
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
	      <?php echo $this->translate("There are no avatar images added by you."); ?>
      </span>
    </div>
  <?php endif;?>
</form>
<div>
<?php echo $this->paginationControl($this->paginator); ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_signup').addClass('active');
</script>
