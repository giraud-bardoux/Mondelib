<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: gallery.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'comment_admin_emotio')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  function multiDelete()
  {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected galleries ?") ?>");
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

<?php if( engine_count($this->subnavigation) ): ?>
  <div class='sub_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
  </div>
<?php endif; ?>

<p><?php echo $this->translate('Here, you can create new packs and upload stickers in them.'); ?></p>
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>
<div>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'create-gallery'), $this->translate("Create New Pack"), array('class'=>'admin_link_btn smoothbox')); ?>
</div>
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <div class="admin_results">
    <?php echo $this->translate(array('%s pack found.', '%s packs found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
<?php endif; ?>
  <?php if(engine_count($this->paginator) > 0):?>
    <table class="admin_table" style="width:100%;">
      <thead>
        <tr>
          <th>
            <input onclick='selectAll();' type='checkbox' class='checkbox' />
          </th>
          <th style="width:15%;">
          <?php echo $this->translate("Title") ?>
          </th>
          <th class="admin_table_centered" style="width:40%;">
            <?php echo $this->translate("Photo") ?>
          </th>
          <th class="admin_table_centered" style="width:10%;">
            <?php echo $this->translate("Status") ?>
          </th>
          <th style="width:30%;">
            <?php echo $this->translate("Options"); ?>
          </th>
          </tr>  
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item) : ?>
          <tr class="item_label" id="slide_<?php echo $item->getIdentity(); ?>">
            <td>
              <input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
            </td>
            <td class="">
              <?php echo $item->title ?>
            </td>
            <td class="admin_table_centered">
              <?php $icon = Engine_Api::_()->storage()->get($item->file_id, ''); ?>
              <?php if($icon) { ?>
                <img alt="" src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl(); ?>" style="max-height:60px;max-width:60px;" />
              <?php } else { ?>
                <?php echo "---"; ?>
              <?php } ?>
            </td>
            <td class='admin_table_centered'>
              <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'enabled', 'gallery_id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('title' => $this->translate('Disabled'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'enabled', 'gallery_id' => $item->getIdentity()), $this->htmlImage('application/modules/Core/externals/images/admin/uncheck.png', '', array('title' => $this->translate('Enabled')))) ) ?>
            </td>
            <td>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'create-gallery', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
              |
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'files', 'gallery_id' => $item->getIdentity()), $this->translate("Manage Stickers"), array()); ?>
              |
              <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'delete-gallery', 'id' => $item->getIdentity()),
              $this->translate("Delete"),
              array('class' => 'smoothbox')) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
    </div>
  </form>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no galleries created by you yet."); ?>
    </span>
  </div>
<?php endif;?>
<div><?php echo $this->paginationControl($this->paginator); ?></div>
