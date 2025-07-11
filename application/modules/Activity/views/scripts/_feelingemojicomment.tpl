<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _feelingemojicomment.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<div class="feeling_emoji_content">
  <?php
  if ($this->edit)
    $class = "edit";
  else
    $class = '';
  $getEmojis = Engine_Api::_()->getDbTable('emojis', 'activity')->getEmojis(array('fetchAll' => 1)); ?>
  <div class="custom_scrollbar emoji_listing">
    <ul id="core_custom_scrollul">
      <?php foreach ($getEmojis as $key => $getEmoji) { ?>
        <?php $getEmojiicons = Engine_Api::_()->getDbTable('emojiicons', 'activity')->getEmojiicons(array('emoji_id' => $getEmoji->emoji_id, 'fetchAll' => 1)); ?>
        <?php if (engine_count($getEmojiicons) > 0) { ?>
          <li id="main_emoji_<?php echo $getEmoji->getIdentity(); ?>">
            <span class="core_text_light"><?php echo $this->translate($getEmoji->title); ?></span>
            <ul>
              <?php foreach ($getEmojiicons as $key => $getEmojiicon) { ?>
                <li title="<?php echo $getEmojiicon->title; ?>" rel="<?php echo $getEmojiicon->emoji_icon; ?>"
                  data-icon="<?php echo $getEmojiicon->emoji_icon; ?>">
                  <a href="javascript:;" class="select_feeling_emoji_adv<?php echo $class; ?>">
                    <span><?php echo $getEmojiicon->emoji_icon; ?></span>
                  </a>
                </li>
              <?php } ?>
            </ul>
          </li>
        <?php } ?>
      <?php } ?>
    </ul>
  </div>
  <?php if (engine_count($getEmojis) > 0): ?>
    <div class="feeling_emoji_content_footer">
      <?php foreach ($getEmojis as $key => $getEmoji): ?>
        <?php $getEmojiicons = Engine_Api::_()->getDbTable('emojiicons', 'activity')->getEmojiicons(array('emoji_id' => $getEmoji->emoji_id, 'fetchAll' => 1)); ?>
        <?php $photo = Engine_Api::_()->storage()->get($getEmoji->file_id, '');
        if ($photo) {
          $photo = $photo->getPhotoUrl(); ?>
          <?php if (engine_count($getEmojiicons) > 0) { ?>
            <a id="emojis_clicka_<?php echo $getEmoji->getIdentity(); ?>" rel="<?php echo $getEmoji->getIdentity(); ?>" class="emojis_clicka <?php echo ($key == 0) ? "active" : ""; ?>" href="javascript:void(0);" title="<?php echo $getEmoji->title; ?>"><img src="<?php echo $photo; ?>"></a>
          <?php }
        } ?>

      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if (!$this->edit) { ?>
    <script type="application/javascript">
      function activityFeedAttachmentFeelingEmoji(that) {
        var fromEdit = scriptJquery("#edit_activity_body").length > 0
        if(!fromEdit){

          var feeling_emoji_icon = scriptJquery(that).parent().parent().attr('data-icon');
          var html = scriptJquery('.compose-content').html();
          if (html == '<br>')
            scriptJquery('.compose-content').html('');

          //composeInstance.setContent(composeInstance.getContent()+' '+feeling_emoji_icon);
          composeInstance.setContent(composeInstance.getContent() + feeling_emoji_icon);

          var data = composeInstance.getContent();
          EditFieldValue = data;

          scriptJquery('#activity_body').trigger('focus');
          scriptJquery('#activity_body').trigger('input');
          autosize($(that));
        }else{
          var feeling_emoji_icon = scriptJquery(that).parent().parent().attr('data-icon');
          var html = scriptJquery('#edit_activity_body').val(); 
          if(html == '<br>')
            scriptJquery('#edit_activity_body').val('');
          scriptJquery('textarea#edit_activity_body').val(scriptJquery('textarea#edit_activity_body').val()+' '+feeling_emoji_icon);
          
          var data = scriptJquery('#edit_activity_body').val();
            EditFieldValue = data;

          scriptJquery('textarea#edit_activity_body').trigger('focus');
        }

        scriptJquery(".activity_post_box").removeClass('_blank');


      }

      function commentContainerSelectFeelingEmoji(that) {

        var feeling_emoji_icon = scriptJquery(that).parent().parent().attr('data-icon');

        var elem = scriptJquery(clickFeelingEmojiContentContainer).parent().parent().parent().parent().find('.body');
        scriptJquery(elem).trigger('focus');

        scriptJquery(clickFeelingEmojiContentContainer).parent().parent().parent().parent().find('button[type=submit]').removeClass('disabled');

        setTimeout(() => {
          if (elem.html() == '<br>')
            elem.html('');
          elem.val(elem.val() + ' ' + feeling_emoji_icon);

          EditFieldValue = elem.val()
        }, 200)
      }

      AttachEventListerSE('click','.select_feeling_emoji_adv > span',function(e){
        if (scriptJquery(clickFeelingEmojiContentContainer).attr('id') == 'activity_feeling_emojis') {
          activityFeedAttachmentFeelingEmoji(this);
        } else {
          commentContainerSelectFeelingEmoji(this);
        }
      });

      AttachEventListerSE('click', '.emojis_clicka', function (e) {
        var emojiId = scriptJquery(this).attr('rel');
        
        scriptJquery(".feeling_emoji_content_footer").children(".emojis_clicka").each(function (index) {
          if (index + 1 == emojiId) {
            scriptJquery('#emojis_clicka_'+emojiId).addClass("active");
          } else {
            scriptJquery(this).removeClass("active");
          }
        });
       
        
        scriptJquery("#main_emoji_"+emojiId)[0].scrollIntoView({ behavior: "smooth", block: "nearest", inline: "start" }); 
             
      });
    </script>
  <?php } ?>
</div>