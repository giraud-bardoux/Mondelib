<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'activity_admin_main_managereactions')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  function multiDelete()
  {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected reactions ?") ?>");
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

<h3><?php echo $this->translate("Manage Reactions"); ?></h3>
<p><?php echo $this->translate("Here, you can manage reactions for the feeds and content on your website. You can edit, delete or create new reactions from below."); ?></p>

<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
<?php endif; ?>

<div class="admin_results">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'manage-reactions', 'action' => 'add-reaction'), $this->translate("Add a New Reaction"), array('class'=>'admin_link_btn smoothbox')); ?>
</div>

<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <div class="admin_results">
    <?php echo $this->translate(array('%s reaction found.', '%s reactions found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
<?php endif; ?>
  <?php if(engine_count($this->paginator) > 0):?>
    <table class="admin_table" style="width:50%;">
      <thead>
        <tr>
          <th>
            <?php echo $this->translate("Name") ?>
          </th>
          <th class="admin_table_centered">
            <?php echo $this->translate("Photo") ?>
          </th>   
          <!--<th class="admin_table_centered"><?php //echo $this->translate("Status") ?></th>-->
          <th>
            <?php echo $this->translate("Options"); ?>
          </th>  
        </tr>
      </thead>  
      <tbody>
        <?php foreach ($this->paginator as $item) : ?>
          <tr class="item_label" id="slide_<?php echo $item->getIdentity(); ?>">
            <td>
              <?php echo $item->title ?>
            </td>
            <td class="admin_table_centered">
              <img alt="" src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '') ? Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl() : ""; ?>" style="max-width:48px;" />
            </td>
            <?php if(0): ?>
              <?php if($item->reaction_id != 1): ?>
                <td class="admin_table_centered">
                  <?php if($item->enabled == 1):?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'manage-reactions', 'action' => 'status', 'id' => $item->reaction_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/icons/check.png', '', array('title'=> $this->translate('Disable')))) ?>
                  <?php else: ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'manage-reactions', 'action' => 'status', 'id' => $item->reaction_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable')))) ?>
                  <?php endif; ?>
                </td>
              <?php else: ?>
              <td class="admin_table_centered">
                <?php echo "---"; ?>
              </td>
              <?php endif; ?>
            <?php endif; ?>
            <td>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'manage-reactions', 'action' => 'add-reaction', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
              <?php if($item->reaction_id != 1): ?>
              |
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'comment', 'controller' => 'manage-reactions', 'action' => 'delete-reaction', 'id' => $item->getIdentity()), $this->translate("Delete"), array('class' => 'smoothbox')); ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else:?>
    <div class="tip">
      <span>
        <?php echo $this->translate("There are no reaction added by you yet."); ?>
      </span>
    </div>
  <?php endif;?>
  </div>
</form>
