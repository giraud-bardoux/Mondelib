<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: search-gif.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php if($this->paginator->getTotalItemCount() > 0) { ?>
<?php if(empty($this->searchvalue)) { ?>
<div class="comment_emotion_search_content_gif custom_scrollbar">
  
    <ul class="activity_search_results">
<?php } ?>
      <?php
      foreach($this->paginator as $gif) {
        if($gif->file_id == 0) continue; ?>
        <li rel="<?php echo $gif->image_id; ?>">
          <a href="javascript:;" class="_activitygif_gif">
            <img src="<?php echo Engine_Api::_()->storage()->get($gif->file_id, '')->getPhotoUrl(); ?>" alt="" />
          </a>
        </li>
      <?php 
      } ?>
<?php if(empty($this->searchvalue)) { ?>
    </ul>
  
</div>
<?php } ?>
<?php } ?>
<?php if($this->paginator->getTotalItemCount() == 0) { ?>
  <div class="comment_emotion_search_noresult">
    <i class="far fa-frown font_color_light" aria-hidden="true"></i>
    <span class="font_color_light"><?php echo $this->translate("No GIF image found.") ?></span>
  </div>
<?php } ?>
<script type="application/javascript">
  canPaginateExistingPhotos = "<?php echo ($this->paginator->count() == 0 ? '0' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? '0' : '1' ))  ?>";
  canPaginatePageNumber = "<?php echo $this->page + 1; ?>";
</script>
<?php die; ?>
