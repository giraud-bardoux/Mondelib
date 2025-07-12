<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _commentAttachments.tpl 2024-10-29 00:00:00Z 
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
  $enableattachementComment = (Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.enableattachement', ''));
  $item = $this->item ? $this->item : '';
  $comment = $this->comment ? $this->comment : '';
  $type = $this->type;

  if(engine_in_array($type, array('commentsubject', 'activitycommentbody'))) {
    $placeHolder = $this->translate('Write a reply...');
  } else if(engine_in_array($type, array('commentlist', 'activitycomments'))) {
    $placeHolder = $this->translate('Write a comment...');
  }
?>
<div class="comment_form_container">
  <div class="comment_form_main">
    <div class="comment_form">
      <?php if($type == 'activitycomments') { ?>
        <textarea  class="body" name="body" cols="45" rows="1" placeholder="<?php echo $placeHolder; ?>"  id="comment<?php echo $item->getIdentity();?>"></textarea><span><?php $commentTextarea_id = $item->getIdentity(); ?></span>
      <?php } else { ?>
        <textarea class="body" name="body" cols="45" rows="1" placeholder="<?php echo $placeHolder; ?>"></textarea>
      <?php } ?>
    </div>
    <div class="comment_post_options">
      <div class="comment_post_icons">
        <span>
          <?php if ((Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesalbum')) && Engine_Api::_()->authorization()->isAllowed('album', null, 'create') && engine_in_array('photos', $enableattachementComment)) { ?>
            <a href="javascript:;" class="activity_tooltip file_comment_select" data-bs-toggle="tooltip" title="<?php echo $this->translate('Attach 1 or more Photos'); ?>"><i><svg viewBox="0 0 24 24"><path d="M19,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V5A5.006,5.006,0,0,0,19,0ZM5,2H19a3,3,0,0,1,3,3V19a2.951,2.951,0,0,1-.3,1.285l-9.163-9.163a5,5,0,0,0-7.072,0L2,14.586V5A3,3,0,0,1,5,2ZM5,22a3,3,0,0,1-3-3V17.414l4.878-4.878a3,3,0,0,1,4.244,0L20.285,21.7A2.951,2.951,0,0,1,19,22Z"/><path d="M16,10.5A3.5,3.5,0,1,0,12.5,7,3.5,3.5,0,0,0,16,10.5Zm0-5A1.5,1.5,0,1,1,14.5,7,1.5,1.5,0,0,1,16,5.5Z"/></svg></i></a>
          <?php } ?>
          <input type="file" name="Filedata" accept="image/*" class="select_file" multiple value="0" style="display:none;">
          <input type="hidden" name="emoji_id" class="select_emoji_id" value="0" style="display:none;">
          <input type="hidden" name="gif_id" class="select_gif_id" value="0" style="display:none;">
          <input type="hidden" name="file_id" class="file_id" value="0">
          <?php if($type == 'commentsubject') { ?>
            <input type="hidden" class="file" name="resource_id" value="<?php echo $item->getIdentity(); ?>">
            <input type="hidden" class="file" name="resource_type" value="<?php echo $item->getType(); ?>">
            <input type="hidden" class="comment_id" name="comment_id" value="<?php echo $comment->comment_id; ?>">
          <?php } ?>
          <?php if($type == 'commentlist') { ?>
            <input type="hidden" class="file" name="subject_id" value="<?php echo $item->getIdentity(); ?>">
            <input type="hidden" class="file_type" name="subject_type" value="<?php echo $item->getType(); ?>">
          <?php } ?>
          <?php if($type == 'activitycommentbody') { ?>
            <input type="hidden" class="file" name="action_id" value="<?php echo $item->getIdentity(); ?>">
            <input type="hidden" class="comment_id" name="comment_id" value="<?php echo $comment->comment_id; ?>">
          <?php } ?>
          <?php if($type == 'activitycomments') { ?>
            <input type="hidden" class="file" name="action_id" value="<?php echo $item->getIdentity(); ?>">
          <?php } ?>
        </span>

        <?php if ((Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo')) && Engine_Api::_()->authorization()->isAllowed('video', $this->viewer(), 'create') && engine_in_array('videos', $enableattachementComment) ) { ?>
          <span><a href="javascript:;" class="activity_tooltip video_comment_select" data-bs-toggle="tooltip" title="<?php echo $this->translate('Attach 1 or more Videos'); ?>"><i><svg viewBox="0 0 24 24"><path d="m19 24h-14a5.006 5.006 0 0 1 -5-5v-14a5.006 5.006 0 0 1 5-5h14a5.006 5.006 0 0 1 5 5v14a5.006 5.006 0 0 1 -5 5zm-14-22a3 3 0 0 0 -3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-14a3 3 0 0 0 -3-3zm4.342 15.005a2.368 2.368 0 0 1 -1.186-.323 2.313 2.313 0 0 1 -1.164-2.021v-5.322a2.337 2.337 0 0 1 3.5-2.029l5.278 2.635a2.336 2.336 0 0 1 .049 4.084l-5.376 2.687a2.2 2.2 0 0 1 -1.101.289zm-.025-8a.314.314 0 0 0 -.157.042.327.327 0 0 0 -.168.292v5.322a.337.337 0 0 0 .5.293l5.376-2.688a.314.314 0 0 0 .12-.266.325.325 0 0 0 -.169-.292l-5.274-2.635a.462.462 0 0 0 -.228-.068z"/></svg></i></a></span>
        <?php } ?>

        <?php if(engine_in_array('stickers', $enableattachementComment)) { ?>
          <span>
            <a href="javascript:;" class="activity_tooltip emoji_comment_select" data-bs-toggle="tooltip" title="<?php echo $this->translate('Post a Sticker'); ?>"><i><svg viewBox="0 0 24 24"><path d="m23.967 10.417a12.04 12.04 0 1 0 -13.55 13.55 3.812 3.812 0 0 0 .489.032 3.993 3.993 0 0 0 2.805-1.184l9.1-9.1a3.962 3.962 0 0 0 1.156-3.298zm-21.9.474a10.034 10.034 0 0 1 19.8-.884 12.006 12.006 0 0 0 -11.86 11.852 9.988 9.988 0 0 1 -7.944-10.968zm10.233 10.509a2.121 2.121 0 0 1 -.278.225 10 10 0 0 1 9.606-9.607 2.043 2.043 0 0 1 -.224.279z"/></svg></i></a>
          </span>
        <?php } ?>
        
        <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.giphyapi', '') && engine_in_array('gif', $enableattachementComment)) { ?>
          <span class="tool_i_gif">
            <a href="javascript:;" class="activity_tooltip gif_comment_select" data-bs-toggle="tooltip" title="<?php echo $this->translate('Post GIF'); ?>"><i><svg viewBox="0 0 24 24"><path d="m19,2H5C2.243,2,0,4.243,0,7v10c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5V7c0-2.757-2.243-5-5-5Zm3,15c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3V7c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v10Zm-9-9v8c0,.552-.447,1-1,1s-1-.448-1-1v-8c0-.552.447-1,1-1s1,.448,1,1Zm7,0c0,.552-.447,1-1,1h-2c-.552,0-1,.449-1,1v1h2c.553,0,1,.448,1,1s-.447,1-1,1h-2v3c0,.552-.447,1-1,1s-1-.448-1-1v-6c0-1.654,1.346-3,3-3h2c.553,0,1,.448,1,1Zm-14,2v4c0,.551.448,1,1,1s1-.449,1-1c-.553,0-1-.448-1-1s.447-1,1-1c1.299,0,2,1.03,2,2,0,1.654-1.346,3-3,3s-3-1.346-3-3v-4c0-1.654,1.346-3,3-3s3,1.346,3,3c0,.552-.447,1-1,1s-1-.448-1-1-.448-1-1-1-1,.449-1,1Z"/></svg></i></a>
          </span>
        <?php } ?>

        <?php if((engine_in_array('emotions', $enableattachementComment))) {   ?>
          <span class="activity_post_tool_i tool_i_emoji">
            <a href="javascript:;" class="activity_tooltip feeling_emoji_comment_select" data-bs-toggle="tooltip" title="<?php echo $this->translate('Post Emojis'); ?>"></a>
          </span>
        <?php } ?>
      </div>
      <button type="submit" class="disabled"><i><svg viewBox="0 0 24 24"><path d="m4.173,13h19.829L4.201,23.676c-.438.211-.891.312-1.332.312-.696,0-1.362-.255-1.887-.734-.84-.77-1.115-1.905-.719-2.966l.056-.123,3.853-7.165Zm-.139-12.718C2.981-.22,1.748-.037.893.749.054,1.521-.22,2.657.18,3.717l3.979,7.283h19.841L4.11.322l-.076-.04Z"/></svg></i></button>
    </div>
  </div>
  <div class="uploaded_file" style="display:none;"></div>
  <div class="link_preview" style="display:none;"></div>
  <div class="sticker_preview" style="display:none;"></div>
  <div class="gif_preview" style="display:none;"></div>
</div>