<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _reactionlikeuser.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>

<?php
$isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
if ($this->execute) {
  foreach ($this->users as $user) { ?>
    <li class="users_listing_popup_item">
      <div class="users_listing_popup_item_photo">
        <span>
          <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon', $user->getTitle()), array()) ?>
          <i style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($this->type[$user->getIdentity()]); ?>);"></i>
        </span>
      </div>

      <div class="users_listing_popup_item_info">
        <div class="users_listing_popup_item_title">
          <a href="<?php echo $user->getHref(); ?>" class="font_color"><?php echo $user->getTitle(); ?></a>
        </div>
        <div class="users_listing_popup_item_stats font_color_light">
          <?php if ($user->getType() == 'user' && ($this->viewer()->getIdentity() && !$this->viewer()->isSelf($user)) && $mcount = Engine_Api::_()->user()->getMutualFriendCount($user, $this->viewer())) { ?>
            <?php echo $this->translate(array('%s mutual friend', '%s mutual friends', $mcount), $this->locale()->toNumber($mcount)) ?>
          <?php } ?>
        </div>
      </div>
    </li>
  <?php }
} else { ?>
  <div data-typeselected="<?php echo $this->typeSelected; ?>" data-resourcetype="<?php echo $this->resource_type; ?>" data-id="<?php echo $this->resource_id; ?>" data-itemid="<?php echo $this->item_id; ?>"
    class="loading_container nocontent" style="display: block;"></div>
<?php
}
?>
<?php $randonNumber = $this->randonNumber; ?>
<script type="application/javascript">
  var page<?php echo $randonNumber; ?> = <?php echo $this->page + 1; ?>;
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if (document.getElementById('view_more_<?php echo $randonNumber; ?>'))
      document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '')) ?>";
  }
  en4.core.runonce.add(function () {
    viewMoreHide_<?php echo $randonNumber; ?>();
  });
  function viewMore_<?php echo $randonNumber; ?> () {
    scriptJquery('#view_more_<?php echo $randonNumber; ?>').hide();
    scriptJquery('#loading_image_<?php echo $randonNumber; ?>').show(); 

    requestViewMore_<?php echo $randonNumber; ?> = scriptJquery.ajax({
      method: 'post',
      'url': en4.core.baseUrl + "comment/ajax/likes/",
      'data': {
        format: 'html',
        id: '<?php echo $this->resource_id; ?>',
        resource_type: '<?php echo $this->resource_type; ?>',
        typeSelected: '<?php echo $this->typeSelected; ?>',
        type: '<?php echo $this->typeSelected; ?>',
        item_id: '<?php echo $this->item_id; ?>',
        page: page<?php echo $randonNumber; ?>,    
        is_ajax_content: 1,
      },
      success: function (responseHTML) {
        scriptJquery('#like_contnent_<?php echo $randonNumber; ?>').append(responseHTML);
        scriptJquery('.view_more_loading_<?php echo $randonNumber; ?>').hide();
        viewMoreHide_<?php echo $randonNumber; ?>();
      }
    });
    return false;
  }
</script>