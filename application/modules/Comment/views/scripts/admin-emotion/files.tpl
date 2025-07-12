<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: files.tpl 2024-10-29 00:00:00Z 
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
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected stickers?") ?>");
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

<?php if($this->subsubNavigation && engine_count($this->subsubNavigation) ): ?>
  <div class='sub_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->subsubNavigation)->render();?>
  </div>
<?php endif; ?>
  
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>
<div>
  <div class="admin_results">
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'gallery'), $this->translate("Back to Sticker Packs"), array('class'=>'admin_link_btn')); ?>
    
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'create-file','gallery_id'=>$this->gallery_id), $this->translate("Add Sticker"), array('class'=>'admin_link_btn smoothbox')); ?>
    
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'upload-zip-file','gallery_id'=>$this->gallery_id), $this->translate("Upload Stickers in Zip"), array('class'=>'admin_link_btn smoothbox')); ?>
  </div>
  <?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
    <div class="admin_results">
      <?php echo $this->translate(array('%s sticker found.', '%s stickers found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
    </div>
  <?php endif; ?>
  <?php if(engine_count($this->paginator) > 0):?>
    <ul class="activity_grid_list">
      <?php foreach ($this->paginator as $item) : 
        $itemTags = $item->tags()->getTagMaps();
      ?>
        <li id="slide_<?php echo $item->getIdentity(); ?>">
          <div class="activity_grid_list_item">
            <div class="activity_grid_list_header">
              <div class="activity_grid_list_input">
                <input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
              </div>
              <div class="activity_grid_list_options">
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'create-file', 'id' => $item->getIdentity(),'gallery_id'=>$this->gallery_id), $this->translate(""), array('title'=> $this->translate("Edit"), 'class' => 'smoothbox fa-solid activity_icon_edit')) ?>

                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'emotion', 'action' => 'delete-file', 'id' => $item->getIdentity()), $this->translate(""), array('title'=> $this->translate("Delete"), 'class' => 'smoothbox fa-solid activity_icon_delete')) ?>
              </div>
            </div>
            <div class="activity_grid_list_img _contain">
              <img alt="" src="<?php echo Engine_Api::_()->storage()->get($item->photo_id, '')->getPhotoUrl(); ?>" />
            </div>
            <?php if (engine_count($itemTags)):?>  	
              <?php $finaltags = '';
              foreach ($itemTags as $tag): ?>
                <?php $finaltags .= $tag->getTag()->text .', '; ?>
              <?php endforeach;
              $finaltags = trim($finaltags);
              $finaltags = rtrim($finaltags, ', '); ?>
              <div class="activity_grid_list_footer" title="<?php echo $finaltags; ?>">
                <?php echo $finaltags; ?>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class='buttons' style="margin-top:15px;">
      <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
    </div>
  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("There are no stickers uploaded by you yet."); ?>
      </span>
    </div>
  <?php endif;?>
</div>
</form>
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
