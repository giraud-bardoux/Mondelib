<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: emoji-content.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class="comment_emotion_search_content">
  <?php
  if (engine_count($this->files)) { ?>
    <ul class="_sickers">
      <?php
      foreach ($this->files as $key => $file) { ?>
        <?php if (!empty($file->files_id)) { ?>
          <li rel="<?php echo $file->files_id; ?>">
            <a href="javascript:;" class="_simemoji_reaction">
              <img src="<?php echo Engine_Api::_()->storage()->get($file->photo_id, '')->getPhotoUrl(); ?>" alt="" />
            </a>
          </li>
        <?php } ?>
        <?php
      } ?>
    </ul>
    <?php
  } else {
    ?>
    <div class="comment_emotion_search_noresult">
      <i class="far fa-frown font_color_light" aria-hidden="true"></i>
      <span class="font_color_light"><?php echo $this->translate("No Stickers to Show") ?></span>
    </div>
  <?php } ?>
</div>
<?php die; ?>