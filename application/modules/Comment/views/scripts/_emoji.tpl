<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _emoji.tpl 2024-10-29 00:00:00Z 
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
<?php if (!$this->edit) { ?>
  <!-- Sickers Search Box -->
  <div class="comment_emotion_search_container clearfix emoji_content" id="only_stickers" <?php if (engine_count($getGallery) == 0): ?> style="display:none;" <?php endif; ?>>
    <div class="comment_emotion_search_bar">
      <div class="comment_emotion_search_input font_color_light">
        <input type="text" placeholder='<?php echo $this->translate("Search stickers"); ?>' class="search_reaction_adv" />
        <button type="reset" value="Reset" class="fa-solid fa-times comment_emotion_reset_emoji"></button>
      </div>
    </div>
    <div class="comment_emotion_search_content main_search_category_srn">
      <div class="comment_emotion_search_cat">
        <?php $emotioncategories = Engine_Api::_()->getDbTable('emotioncategories', 'comment')->getCategories(array('fetchAll' => true));
        foreach ($emotioncategories as $cat) {
          ?>
          <div class="comment_emotion_search_cat_item">
            <a href="javascript:;" data-title="<?php echo $cat->title; ?>" class=" activity_reaction_cat"
              style="background-color:<?php echo $cat->color ?>;">
              <img src="<?php echo Engine_Api::_()->storage()->get($cat->file_id, '')->getPhotoUrl(); ?>"
                alt="<?php echo $cat->title; ?>" />
              <span><?php echo $cat->getTitle() ?></span>
            </a>
          </div>
        <?php } ?>
      </div>
    </div>
    <div style="display:none;position:relative;" class="comment_stickers_tab_content custom_scrollbar main_search_cnt_srn">
      <div class="loading_container" style="height:100%;"></div>
    </div>
  </div>
<?php } ?>

<?php if (!$this->edit) { ?>
  <?php $useremotions = Engine_Api::_()->getDbTable('useremotions', 'comment')->getEmotion();
  foreach ($useremotions as $useremotion) {
    ?>
    <div style="display:none;position:relative;" class="comment_stickers_tab_content custom_scrollbar emoji_content">
      <div class="loading_container" style="height:100%;"></div>
    </div>
  <?php } ?>
<?php } ?>