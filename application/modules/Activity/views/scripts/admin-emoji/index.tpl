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
        var url = '<?php echo $this->url(array('action' => 'order-manage-emoji')) ?>';
        scriptJquery.ajax({
          url: url,
          dataType: 'json',
          data: {
            format: 'json',
            order: ids
          }
        });
      }
    });
  });

  function multiDelete() {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected emojis category ?") ?>");
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

<h3><?php echo $this->translate("Categories & Emojis"); ?></h3>
<p>
  <?php echo $this->translate("The Emoji are unicode emojis which will be compatible for iOS, Android Apps and all supporting Browsers. This plugin comes with pre-configured emojis and categories in various categories.<br />Below, you can change the name and photo of the categories by editing them. Since, the unicode images are universal, these can not be edited.<br />To reorder the categories, click on their names and drag them up or down."); ?>
</p>

<?php if (is_countable($this->paginator) && engine_count($this->paginator)): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url(); ?>" onSubmit="return multiDelete()">
  <?php endif; ?>
  <div>
    <?php //if (is_countable($this->paginator) && engine_count($this->paginator)): ?>
      <!-- <div class="admin_results">
        <?php //echo $this->translate(array('%s emoji category found.', '%s emoji categories found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      </div> -->
    <?php //endif; ?>
    <?php if (engine_count($this->paginator) > 0): ?>
      <div class="activity_packs_listing" id='menu_list'>
        <?php foreach ($this->paginator as $item): ?>
          <div class="activity_packs_item move" id="manageemojis_<?php echo $item->emoji_id ?>">
            <div class="activity_packs_item_inner d-flex">
              <input type='hidden' name='order[]' value='<?php echo $item->emoji_id; ?>'>
              <div class="_icon">
                <?php $icon = Engine_Api::_()->storage()->get($item->file_id, '');
                if ($icon) {
                  $iconURL = $icon->getPhotoUrl(); ?>
                  <img alt="" src="<?php echo $iconURL; ?>" />
                <?php } else { ?>
                <?php } ?>
              </div>
              <div class="_cont">
                <div class="_title">
                  <?php echo $item->title ?>
                </div>
                <div class="_options">
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'emoji', 'action' => 'create-emojicategory', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
                  |
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'activity', 'controller' => 'emoji', 'action' => 'emojiicons', 'emoji_id' => $item->getIdentity()), $this->translate("Reorder Emojis"), array()); ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("There are no emojis category created by you yet."); ?>
      </span>
    </div>
  <?php endif; ?>
  </div>
</form>