<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 10248 2014-05-30 21:48:38Z andres $
 * @author     Jung
 */
?>

<?php if( !$this->video || $this->video->status !=1 ): ?>
  <div class="error_msg">
    <?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.');?>
  </div>
<?php 
return; // Do no render the rest of the script in this mode
endif; ?>

<?php if ( $this->video->type == 'upload' && $this->video_extension == 'mp4' )
$this->headScript()
->appendFile($this->layout()->staticBaseUrl . 'externals/html5media/html5media.min.js');
?>

<?php if( $this->video->type == 'upload' && $this->video_extension == 'flv' ):
    $this->headScript()
         ->appendFile($this->layout()->staticBaseUrl . 'externals/flowplayer/flowplayer.js');
    $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'externals/flowplayer/skin/skin.css');
  ?>
<?php endif ?>

<form id='filter_form' class='global_form_box' method='post' action='<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'browse'), 'default', true) ?>' style='display:none;'>
<input type="hidden" id="tag" name="tag" value=""/>
</form>
<div class="video_view">
  <?php if( $this->video->type == 'upload' ): ?>
    <div id="video_embed" class="video_embed">
      <?php 
        $storage_file = Engine_Api::_()->getItem('storage_file', $this->video->file_id);
        $video_location =  Engine_Api::_()->video()->returncurrentvideo($this->video->getIdentity(),$storage_file->map());
      ?>
      <video id="video_<?php echo $this->video->getIdentity(); ?>" playsinline controls preload="auto" width="480" style="height:97%;object-fit:fill;">
        <?php echo $video_location ?>
      </video>
    </div>
  <?php else: ?>
    <div class="video_embed video_view_player">
      <?php echo $this->videoEmbedded ?>
    </div>
  <?php endif; ?>
  <div class="video_view_content mt-3">
    <h1><?php echo $this->video->getTitle(); ?></h1>
    <div class="video_view_info d-flex flex-wrap mb-3 gap-2">
      <div class="video_view_info_left">
        <div class="font_small font_color_light">
          <div class="item_view_date font_color_light font_small">
            <span><?php echo $this->translate('By') ?> <?php echo $this->htmlLink($this->video->getParent(), $this->video->getParent()->getTitle()) ?></span>
            <span><?php echo $this->timestamp($this->video->creation_date) ?></span>
          </div>
          <div class="item_view_stats font_color_light font_small">
            <span><i class="icon_like"></i><?php echo $this->translate(array('%s like', '%s likes', $this->video->like_count), $this->locale()->toNumber($this->video->like_count)) ?></span>
            <span><i class="icon_comment"></i><?php echo $this->translate(array('%s comment', '%s comments', $this->video->comment_count), $this->locale()->toNumber($this->video->comment_count)) ?></span>
            <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $this->video->view_count), $this->locale()->toNumber($this->video->view_count)) ?></span>
          </div>
        </div>
      </div>
      <div class="video_view_info_btns d-flex">
        <?php if( Engine_Api::_()->user()->getViewer()->getIdentity() ): ?>
          <div>
            <?php echo $this->htmlLink(array('module'=> 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => 'video', 'id' => $this->video->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'smoothbox btn btn-alt btn-small icon_share')); ?>
          </div>
        <?php endif ?>
        <?php if( $this->can_embed ): ?>
          <div>
            <?php echo $this->htmlLink(array('module'=> 'video', 'controller' => 'video', 'action' => 'embed', 'route' => 'default', 'id' => $this->video->getIdentity(), 'format' => 'smoothbox'), $this->translate("Embed"), array('class' => 'smoothbox btn btn-alt btn-small icon_embed')); ?>
          </div>
        <?php endif ?>
        <?php if( Engine_Api::_()->user()->getViewer()->getIdentity() ): ?>
          <div class="dropdown options_menu">
            <button class="btn btn-alt btn-small" type="button" id="videooptions" data-bs-toggle="dropdown" aria-expanded="true"><i class="icon_option_menu"></i></button>
            <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="videooptions">
              <?php if( $this->can_edit ): ?>
                <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'video', 'controller' => 'index', 'action' => 'edit', 'video_id' => $this->video->video_id), $this->translate('Edit Video'), array('class' => 'dropdown-item icon_edit')) ?></li>
              <?php endif;?>
              <?php if( $this->can_delete && $this->video->status != 2 ): ?>
                <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'video', 'controller' => 'index', 'action' => 'delete', 'video_id' => $this->video->video_id, 'format' => 'smoothbox'), $this->translate('Delete Video'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
              <?php endif;?>
              <?php if( Engine_Api::_()->user()->getViewer()->getIdentity() ): ?>
                <li><?php echo $this->htmlLink(array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->video->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'dropdown-item smoothbox icon_report')); ?></li>
              <?php endif;?>
            </ul>
          </div>
        <?php endif ?>
      </div>
    </div>
    <div class="video_desc">
      <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->video->description);?>
    </div>      
    <div class="mt-3 font_color_light font_small">
      <?php if( $this->category ): ?>
        <?php echo $this->translate('Filed in') ?>
        <?php echo $this->htmlLink(array('route' => 'video_general', 'QUERY' => array('category' => $this->category->category_id)), $this->translate($this->category->category_name)) ?>
      <?php endif; ?>
      <?php if (engine_count($this->videoTags ) && $this->category ): ?>
        -
      <?php endif; ?>  
      <?php if (engine_count($this->videoTags )):?>
        <?php foreach ($this->videoTags as $tag): ?>
          <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->tag_id; ?>);'>#<?php echo $tag->getTag()->text?></a>&nbsp;
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1)) { ?>
    <div class="mt-3">
      <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->video, 'module' => 'video', 'param' => 'create', 'notificationType' => 'video_rating')); ?>
    </div>
  <?php } ?>
</div>
<script type="text/javascript">
    var tagAction = function(tag_id){
      var url = "<?php echo $this->url(array('module' => 'video','action'=>'browse'), 'video_general', true) ?>?tag_id="+tag_id;
      loadAjaxContentApp(url);
    }
    scriptJquery('.core_main_video').parent().addClass('active');
</script>
<script type="text/javascript">
  en4.core.runonce.add(function() {
		new Plyr('#video_<?php echo $this->video->getIdentity(); ?>');
	});
</script>
