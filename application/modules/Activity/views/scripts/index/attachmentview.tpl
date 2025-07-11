<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: attachmentview.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php if($this->type == 'album_photo') { ?>
  <?php $this->headTranslate(array('Save', 'Cancel', 'delete')); ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      var descEls = scriptJquery('.albums_viewmedia_info_caption');
      if( descEls.length > 0 ) {
         descEls.enableLinks();
      }
      var taggerInstance = window.taggerInstance = new Tagger('#media_photo_next',{
      'title' : '<?php echo $this->string()->escapeJavascript($this->translate('ADD TAG'));?>',
      'description' : '<?php echo $this->string()->escapeJavascript($this->translate('Type a tag or select a name from the list.'));?>',
      'createRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'add'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject->getGuid() ?>'
        }
      },
      'deleteRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'remove'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject->getGuid() ?>'
        }
      },
      'cropOptions' : {
        'container' : scriptJquery('#media_photo_next')
      },
      'tagListElement' : '#media_tags',
      'existingTags' : <?php echo Zend_Json::encode($this->tags) ?>,
      'suggestProto' : 'request.json',
      'suggestParam' : "<?php echo $this->url(array('module' => 'user', 'controller' => 'friends', 'action' => 'suggest', 'includeSelf' => true), 'default', true) ?>",
      'guid' : <?php echo ( $this->viewer()->getIdentity() ? "'".$this->viewer()->getGuid()."'" : 'false' ) ?>,
      'enableCreate' : <?php echo ( $this->canTag ? 'true' : 'false') ?>,
      'enableDelete' : <?php echo ($this->canUntagGlobal ? 'true' : 'false') ?>
    });   
    });

    var tagAction = window.tagAction = function(tag) {
      scriptJquery('#tag').val(tag);
      const formData = new FormData(scriptJquery('#filter_form')[0]);
      const params = new URLSearchParams(formData);
      let url = scriptJquery('#filter_form').attr("action")+"?"+params;
      window.history.pushState({state:'new'},'', url);
      loadAjaxContentApp(url);
    }

    scriptJquery(document).keydown(function(e) {
      // ESCAPE key pressed   
      if (e.keyCode == 27) {
        parent.Smoothbox.close();
      }
    });
  </script>

  <form id='filter_form' class='global_form_box' method='post' action='<?php echo $this->url(array('module' => 'album', 'controller' => 'index', 'action' => 'browse-photos'), 'album_general', true) ?>' style='display:none;'><input type="hidden" id="tag" name="tag" value=""/></form>
    <?php if(!empty($this->format) && $this->format == 'smoothbox') { ?>
     <a href="javascript:void(0)" class="media_lightbox_close" onclick="parent.Smoothbox.close();">
       <i class="icon_cross"></i>
     </a>
    <?php } ?>
  <div class="photo_view_container">
    <div class='photo_view_wrapper'>
      <?php if( !$this->message_view): ?>
        <?php if( $this->action->attachment_count > 1 ): ?>
          <div class="photo_view_nav">
            <?php echo $this->htmlLink(( $this->previousPhoto && $this->previousPhotoItem ? $this->previousPhotoItem->getHref().$this->formaturl : null ), $this->translate('<i class="fa-solid fa-angle-left"></i>'), array('id' => 'photo_prev', 'title' => $this->translate('Prev'))) ?>
            <?php echo $this->htmlLink(( $this->nextPhoto && $this->nextPhotoItem ? $this->nextPhotoItem->getHref().$this->formaturl : null ), $this->translate('<i class="fa-solid fa-angle-right"></i>'), array('id' => 'photo_next', 'title' => $this->translate('Next'))) ?>
          </div>
        <?php endif ?>
      <?php endif ?>
      <div class="photo_view_media_container">    
        <div class='photo_view_media' id='media_photo_div'>
          <a id='media_photo_next'  href='<?php echo (0 && $this->nextPhoto && !$this->message_view)? $this->escape($this->nextPhoto->getHref()) : 'javascript::void()' ?>'>
          <?php echo $this->htmlImage($this->photo->getPhotoUrl(), $this->photo->getTitle(), array('id' => 'media_photo')); ?>
          </a>
        </div>
      </div>
      <div class="photo_view_footer d-flex align-items-center">
        <div class="photo_view_footer_left">
          <?php if ($this->viewer()->getIdentity()):?>
            <?php if( $this->canTag ): ?>
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Add Tag'), array('class' => 'icon_tag', 'onclick'=>'taggerInstance.begin();')) ?>
            <?php endif; ?>
          <?php endif ?>
        </div>
        
        <?php if( $this->canEdit ): ?>
          <div class="photo_view_footer_options">
            <a class="buttonlink icon_photos_rotate_ccw" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.rotate(<?php echo $this->photo->getIdentity() ?>, 90).then(function(){ this.attr('class', 'buttonlink icon_photos_rotate_ccw') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_rotate_cw" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.rotate(<?php echo $this->photo->getIdentity() ?>, 270).then(function(){ this.attr('class', 'buttonlink icon_photos_rotate_cw') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_flip_horizontal" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.flip(<?php echo $this->photo->getIdentity() ?>, 'horizontal').then(function(){ this.attr('class', 'buttonlink icon_photos_flip_horizontal') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_flip_vertical" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.flip(<?php echo $this->photo->getIdentity() ?>, 'vertical').then(function(){ this.attr('class', 'buttonlink icon_photos_flip_vertical') }.bind(scriptJquery(this)));">&nbsp;</a>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

<?php } elseif($this->type == 'video') { ?>
  <?php if( !$this->subject || $this->subject->status !=1 ): ?>
    <div class="error_msg">
      <?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.');?>
    </div>
  <?php 
  return; // Do no render the rest of the script in this mode
  endif; ?>

  <form id='filter_form' class='global_form_box' method='post' action='<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'browse'), 'default', true) ?>' style='display:none;'>
    <input type="hidden" id="tag" name="tag" value=""/>
  </form>
  <?php if(!empty($this->format) && $this->format == 'smoothbox') { ?>
    <a href="javascript:void(0)" class="media_lightbox_close" onclick="parent.Smoothbox.close();">
      <i class="icon_cross"></i>
    </a>
  <?php } ?>
  <div class="photo_view_container">
    <div class='photo_view_wrapper'>
      <?php if( !$this->message_view): ?>
        <?php if( $this->action->attachment_count > 1 ): ?>
          <div class="photo_view_nav">
            <?php echo $this->htmlLink(( $this->previousPhoto && $this->previousPhotoItem ? $this->previousPhotoItem->getHref().$this->formaturl : null ), $this->translate('<i class="fa-solid fa-angle-left"></i>'), array('id' => 'photo_prev', 'title' => $this->translate('Prev'))) ?>
            <?php echo $this->htmlLink(( $this->nextPhoto && $this->nextPhotoItem ? $this->nextPhotoItem->getHref().$this->formaturl : null ), $this->translate('<i class="fa-solid fa-angle-right"></i>'), array('id' => 'photo_next', 'title' => $this->translate('Next'))) ?>
          </div>
        <?php endif ?>
      <?php endif ?>
      <div class="photo_view_media_container">
        <?php if( $this->subject->type == 'upload' ): ?>
          <div id="video_embed" class="photo_view_media video_embed">
            <?php 
            	$storage_file = Engine_Api::_()->getItem('storage_file', $this->subject->file_id);
            	$video_location =  Engine_Api::_()->video()->returncurrentvideo($this->subject->getIdentity(),$storage_file->map());
            ?>
            <video id="video_<?php echo $this->subject->getIdentity(); ?>" playsinline controls preload="auto" width="480" style="height:97%;object-fit:fill;">
            	<?php echo $video_location ?>
            </video>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    var tagAction = function(tag_id){
      var url = "<?php echo $this->url(array('module' => 'video','action'=>'browse'), 'video_general', true) ?>?tag_id="+tag_id;
      loadAjaxContentApp(url);
    }

    en4.core.runonce.add(function() {
  		new Plyr('#video_<?php echo $this->subject->getIdentity(); ?>');
  	});

    scriptJquery(document).keydown(function(e) {
      // ESCAPE key pressed     
      if (e.keyCode == 27) {
        parent.Smoothbox.close();
      }
    });
  </script>
<?php } ?>
<?php if(!empty($this->format) && $this->format == 'smoothbox') { ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      parent.scriptJquery('#TB_window').addClass('lightbox_photo_video');
    });
  </script>
<?php } ?>