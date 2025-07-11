<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: emojiicons.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'activity_admin_main_emojisettings')); ?>

<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  scriptJquery(document).ready(function () {
    scriptJquery('#menu_list').addClass('sortable');
    var SortablesInstance = scriptJquery('#menu_list').sortable({
      stop: function (event, ui) {
        var ids = [];
        scriptJquery('#menu_list > div').each(function (e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        // Send request
        var url = '<?php echo $this->url(array('action' => 'order-manage-emojiicons')) ?>';
        scriptJquery('#global_content').append("<div class='admin_loading_icon' id='admin_loading_icon'><img src='application/modules/Core/externals/images/large-loading.gif' /></div>");
        scriptJquery.ajax({
          url: url,
          dataType: 'json',
          data: {
            format: 'json',
            order: ids
          },
          success: function (responseJSON) {
            scriptJquery("#admin_loading_icon").remove();
          }
        });
      }
    });
  });

  function multiDelete() {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected emoji icons?") ?>");
  }
  function selectAll() {
    var i;
    var multidelete_form = document.getElementById('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length - 1; i++) {
      inputs[i].checked = inputs[0].checked;
    }
  }
</script>
<h3><?php echo $this->translate("Reorder Emojis for Browsers"); ?></h3>
<p>
  <?php echo $this->translate("Here, you can reorder the emoji images. These will be reflected when users try to add emojis in their posts and comments from browsers.<br />To reorder the emojis, click on their names and drag them up or down."); ?>
</p>
<?php if (is_countable($this->paginator) && engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url(); ?>" onSubmit="return multiDelete()">
  <?php endif; ?>
  <div>
    <div class="admin_results">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'emoji', 'action' => 'index'), $this->translate("Back to Categories & Emojis"), array('class' => 'admin_link_btn')); ?>

      <?php //echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'emoji', 'action' => 'add-emojiicon','emoji_id' => $this->emoji_id), $this->translate("Add Emoji Icon"), array('class'=>'admin_link_btn fa fa-plus smoothbox')); ?>

      <?php //echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'emoji', 'action' => 'upload-zip-file','emoji_id'=>$this->emoji_id), $this->translate("Upload Stickers in Zip"), array('class'=>'admin_link_btn fa fa-plus smoothbox')); ?>
    </div>
    <?php if (is_countable($this->paginator) && engine_count($this->paginator)): ?>
      <div class="admin_results">
        <?php echo $this->translate(array('%s emoji found.', '%s emojis found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      </div>
    <?php endif; ?>
    <?php if (engine_count($this->paginator) > 0): ?>
      <div class="activity_packs_listing activity_emoji_listing" id='menu_list'>
        <?php foreach ($this->paginator as $item): ?>
          <div class="activity_packs_item move" id="manageemojiicons_<?php echo $item->getIdentity() ?>">
            <div class="activity_packs_item_inner d-flex">
              <input type='hidden' name='order[]' value='<?php echo $item->getIdentity(); ?>'>
              <div class="_icon">
                <?php echo $item->emoji_icon; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="tip">
        <span>
          <?php echo $this->translate("There are no emoji icon created by you yet."); ?>
        </span>
      </div>
    <?php endif; ?>
  </div>
</form>