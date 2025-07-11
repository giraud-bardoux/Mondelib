<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: emojicontent.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $getGallery = Engine_Api::_()->getDbTable('emotiongalleries', 'comment')->getGallery(array('fetchAll' => 1, 'type' => 1)); ?>
<div class="emoji_content comment_emotion_container  _emoji_content notclose" id="sticker_close">
  <div class="comment_emotion_container_inner clearfix">
    <div class="comment_emotion_container_header">
      <?php if (engine_count($getGallery) > 0): ?>
        <a class="_headbtn _headbtn_add ajaxsmoothbox" href="javascript:;" data-url="comment/ajax/reaction-add"
          onclick="stickerClose(sticker_close)"><i></i></a>
      <?php endif; ?>
      <div class="comment_emotion_container_header_tabs">
        <div class="comment_emotion_tabs owl-theme" id="comment_emotion_tabs">
          <a class="_headbtn _headbtn_search activity_emotion_btn_clk complete" href="javascript:;" <?php if (engine_count($getGallery) == 0): ?> style="display:none;" <?php endif; ?>><i></i></a>
          <?php $useremotions = Engine_Api::_()->getDbTable('useremotions', 'comment')->getEmotion(array('type' => 'user')); ?>
          <?php foreach ($useremotions as $useremotion) { ?>
            <?php if (Engine_Api::_()->storage()->get($useremotion->file_id, '')) { ?>
              <a data-galleryid="<?php echo $useremotion->gallery_id; ?>"
                class="_headbtn activity_tooltip activity_emotion_btn_clk" title="<?php echo $useremotion->title; ?>">
                <img src="<?php echo Engine_Api::_()->storage()->get($useremotion->file_id, '')->getPhotoUrl(); ?>"
                  alt="<?php echo $useremotion->title; ?>">
              </a>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="comment_emotion_holder" style="min-height:300px">
      <div class="loading_container empty_cnt" style="height:250px;"></div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function stickerClose(sticker_close) {
    sticker_close.style.display = "none";
  }
</script>