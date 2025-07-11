<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _commentlikeusers.tpl 2024-10-29 00:00:00Z 
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
foreach ($this->users as $user) {
  if ($likes->getType() != "user")
    $user = Engine_Api::_()->getItem($likes->poster_type, $likes->poster_id);
  else
    $user = $likes;
  if (!$user)
    continue;
  ?>
  <li class="users_listing_popup_item">
    <?php if ($this->checkbox) { ?>
      <div>
        <input class="commentcheckbox" type="checkbox" name="users[]" value="<?php echo $user->getIdentity(); ?>">
      </div>
    <?php } ?>
    <div class="users_listing_popup_item_photo">
      <span>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon', $user->getTitle()), array()) ?>
        <i style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($likes->type); ?>);"></i>
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
<?php } ?>
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
      'url': en4.core.baseUrl + "comment/ajax/comment-likes/",
      'data': {
        format: 'html',
        id: '<?php echo $this->resource_id; ?>',
        resource_type: '<?php echo $this->resource_type; ?>',
        comment_id: '<?php echo $this->comment_id; ?>',
        page: page<?php echo $randonNumber; ?>,    
        is_ajax_content: 1,
      },
      success: function (responseHTML) {
        scriptJquery('#like_contnent').append(responseHTML);
        scriptJquery('.view_more_loading_<?php echo $randonNumber; ?>').hide();
        viewMoreHide_<?php echo $randonNumber; ?>();
      }
    });
    return false;
  }
</script>