<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: reaction-add.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class="activity_reaction_add_cnt comment_emotion_store ">
  <div id="activity_reaction_gallery_cnt" class="clearfix">
    <div class="comment_emotion_store_header">
      <div class="comment_emotion_store_header_cont">
        <div class="comment_emotion_store_header_cont_title text-center">
          <i class="fa fa-shopping-cart "></i>
          <span><?php echo $this->translate($this->storepopupTitle); ?></span>
        </div>
        <div class="comment_emotion_store_header_cont_des font_color_light text-center">
          <?php echo $this->translate($this->storepopupDesciption); ?>
        </div>
      </div>
    </div>
    <div class="comment_emotion_store_content custom_scrollbar">
      <div class="comment_emotion_store_content_inner">
        <?php foreach ($this->gallery as $gallery) { ?>
          <div class="comment_emotion_store_item _emoji_cnt">
            <div>
              <a href="javascript:;" data-gallery="<?php echo $gallery->getIdentity(); ?>"
                class="anc_activity_reaction activity_reaction_preview_btn">
                <div class="comment_emotion_store_item_top d-flex">
                  <?php if (Engine_Api::_()->storage()->get($gallery->file_id, '')) { ?>
                    <div class="comment_emotion_store_item_main_icon text-center">
                      <img src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl(); ?>">
                    </div>
                  <?php } ?>
                  <div class="comment_emotion_store_item_top_icons d-flex flex-wrap">
                    <?php
                    $files = Engine_Api::_()->getItemTable('comment_emotionfile')->getFiles(array('fetchAll' => true, 'gallery_id' => $gallery->getIdentity(), 'limit' => 8));
                    foreach ($files as $file) { ?>
                      <?php if (Engine_Api::_()->storage()->get($file->photo_id, '')) { ?>
                        <div class="text-center">
                          <img src="<?php echo Engine_Api::_()->storage()->get($file->photo_id, '')->getPhotoUrl(); ?>" />
                        </div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </div>
              </a>
              <div class="comment_emotion_store_item_btm d-flex">
                <div class="comment_emotion_store_item_btm_title">
                  <?php echo $gallery->getTitle(); ?>
                </div>
                <div class="comment_emotion_store_item_btm_btns floatR">
                  <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>"
                    class="btn btn-alt activity_reaction_preview_btn"><?php echo $this->translate("Preview"); ?></button>
                  <?php if (engine_in_array($gallery->getIdentity(), $this->useremotions)) { ?>
                    <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>"
                      data-remove="<?php echo $this->translate('Remove'); ?>"
                      data-add="<?php echo $this->translate('Add') ?>"
                      class="btn btn-primary activity_reaction_remove_emoji activity_reaction_remove_emoji_<?php echo $gallery->getIdentity(); ?>"
                      data-title="<?php echo $gallery->getTitle(); ?>"
                      data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '') ? Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl() : ''; ?>"><?php echo $this->translate('Remove'); ?></button>
                  <?php } else { ?>
                    <button type="button" data-gallery="<?php echo $gallery->getIdentity(); ?>"
                      data-remove="<?php echo $this->translate('Remove'); ?>"
                      data-add="<?php echo $this->translate('Add') ?>"
                      class="btn btn-primary activity_reaction_add_emoji activity_reaction_add_emoji_<?php echo $gallery->getIdentity(); ?>"
                      data-title="<?php echo $gallery->getTitle(); ?>"
                      data-src="<?php echo Engine_Api::_()->storage()->get($gallery->file_id, '') ? Engine_Api::_()->storage()->get($gallery->file_id, '')->getPhotoUrl() : ''; ?>"><?php echo $this->translate('Add'); ?></button>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="activity_reaction_gallery_preview_cnt" style="display:none;height:100%;"></div>
</div>
<script type="text/javascript">
  function ajaxsmoothboxcallback() {
    scriptJquery('#ajaxsmoothbox_main').css('z-index', '101');
  }
</script>
<?php die; ?>