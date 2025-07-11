<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: preview-reaction.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>

<div class="comment_emotion_store_preview">
  <div class="comment_emotion_store_preview_back_link">
    <a href="javascript:;" class="activity_back_store">
      <i class="fa fa-chevron-left"></i>
      <span><?php echo $this->translate("Sticker Store"); ?></span>
    </a>
  </div>
  <?php $gallery = $this->gallery; ?>
  <div class="comment_emotion_store_preview_cont custom_scrollbar">
    <div class="comment_emotion_store_preview_info d-flex gap-3 mb-4">
      <?php if (Engine_Api::_()->storage()->get($gallery->file_id, '')) { ?>
        <div class="comment_emotion_store_preview_info_img">
          <img src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>">
        </div>
      <?php } ?>
      <div class="comment_emotion_store_preview_info_cont">
        <div class="comment_emotion_store_preview_title">
          <?php echo $gallery->getTitle(); ?>
        </div>
        <div class="comment_emotion_store_preview_des">
          <?php echo $gallery->getDescription(); ?>
        </div>
        <div class="comment_emotion_store_preview_btn">
          <?php if ($this->useremotions && Engine_Api::_()->storage()->get($gallery->file_id, '')) { ?>
            <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>"
              data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>"
              class="activity_reaction_remove_emoji  activity_reaction_remove_emoji_<?php echo $gallery->getIdentity(); ?>"
              data-title="<?php echo $gallery->getTitle(); ?>"
              data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Remove'); ?></button>
          <?php } else if (Engine_Api::_()->storage()->get($gallery->file_id, '')) { ?>
              <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>"
                data-remove="<?php echo $this->translate('Remove'); ?>" data-add="<?php echo $this->translate('Add') ?>"
                class="activity_reaction_add_emoji  activity_reaction_add_emoji_<?php echo $gallery->getIdentity(); ?>"
                data-title="<?php echo $gallery->getTitle(); ?>"
                data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>"><?php echo $this->translate('Add'); ?></button>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="comment_emotion_store_preview_stickers d-flex flex-wrap">
      <?php
      $files = Engine_Api::_()->getItemTable('comment_emotionfile')->getFiles(array('fetchAll' => true, 'gallery_id' => $gallery->getIdentity()));
      foreach ($files as $file) { ?>
        <div class="comment_emotion_store_preview_stickers_icon d-flex flex-wrap">
          <span
            style="background-image:url(<?php echo Engine_Api::_()->storage()->get($file->photo_id, '')->getPhotoUrl(); ?>);"></span>
        </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php die; ?>