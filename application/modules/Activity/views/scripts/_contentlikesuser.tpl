<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _contentlikesuser.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php
foreach ($this->users as $likes) {
  if ($likes->getType() != "user")
    $user = Engine_Api::_()->getItem($likes->subject_type, $likes->subject_id);
  else
    $user = $likes;
  if (!$user)
    continue;
  ?>
  <li class="users_listing_popup_item">
    <?php if ($this->checkbox) { ?>
      <div class="users_listing_popup_item_checkbox">
        <input class="commentcheckbox" type="checkbox" name="users[]" value="<?php echo $user->getIdentity(); ?>">
      </div>
    <?php } ?>
    <div class="users_listing_popup_item_photo">
      <span>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon', $user->getTitle()), array()) ?>
        <?php if ($likes->getType() != 'user') { ?>
          <i style="background-image:url(<?php echo Engine_Api::_()->getDbtable('reactions', 'comment')->likeImage($likes->type); ?>);"></i>
        <?php } ?>
      </span>
    </div>
    <div class="users_listing_popup_item_info">
      <div class="users_listing_popup_item_title">
        <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array("class" => "font_color")); ?>
        <!-- <a href="<?php // echo $user->getHref(); ?>"><?php // echo $user->getDescription(); ?></a> -->
      </div>
      <div class="users_listing_popup_item_stats font_color_light">
        <?php if ($user->getType() == "user" && ($this->viewer()->getIdentity() && !$this->viewer()->isSelf($user)) && $mcount = Engine_Api::_()->user()->getMutualFriendCount($user, $this->viewer())) { ?>
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
      'url': en4.core.baseUrl + "activity/ajax/comment-likes/",
      'data': {
        format: 'html',
        id: '<?php echo $this->resource_id; ?>',
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
<?php if(!$this->notdie) die; ?>