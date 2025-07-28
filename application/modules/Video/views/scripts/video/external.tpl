<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: external.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>

<?php if( $this->error == 1 ): ?>
  <?php echo $this->translate('Embedding of videos has been disabled.') ?>
  <?php return ?>
<?php elseif( $this->error == 2 ): ?>
  <?php echo $this->translate('Embedding of videos has been disabled for this video.') ?>
  <?php return ?>
<?php elseif( !$this->video || $this->video->status != 1 ): ?>
  <?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.') ?>
  <?php return ?>
<?php endif; ?>

<?php if ( $this->video->type == 'upload' && $this->video_extension == 'mp4' )
    $this->headScript()
         ->appendFile($this->layout()->staticBaseUrl . 'externals/html5media/html5media.min.js');
?>

<?php if( $this->video->type == 'upload' && $this->video_extension == 'flv' ):
  $this->headScript()
  ->appendFile($this->layout()->staticBaseUrl . 'externals/flowplayer/flowplayer.min.js');
  $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'externals/flowplayer/skin/skin.css');
  ?>
<?php endif ?>

<div class='video_externals_container'>
  <div class="video_view">
    <?php if( $this->video->type == 'upload' ): ?>
      <div id="video_embed" class="video_embed video_view_player">
        <video id="video" controls preload="auto" width="480" height="300">
          <source type='video/mp4;' src="<?php echo $this->video_location ?>">
        </video>
      </div>
    <?php else: ?>
      <div class="video_embed video_view_player">
        <?php echo $this->videoEmbedded ?>
      </div>
    <?php endif; ?>
    <div class="video_view_content mt-3">
      <h1><?php echo $this->video->getTitle() ?></h1>

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
      </div>

      <div class="video_desc">
        <?php echo $this->video->description;?>
      </div>
      <div class="mt-3 font_color_light font_small">

        <?php if( $this->category ): ?>
          <?php echo $this->translate('Filed in') ?>
          <?php echo $this->htmlLink(array(
              'route' => 'video_general',
              'QUERY' => array('category' => $this->category->category_id)
            ), $this->translate($this->category->category_name)
          ) ?>
        <?php endif; ?>
        <?php if (engine_count($this->videoTags ) && $this->category ): ?>
          -
        <?php endif; ?> 
        <?php if (engine_count($this->videoTags) ): ?>
          <?php foreach ($this->videoTags as $tag): ?>
            <a href='javascript:void(0);'>#<?php echo $tag->getTag()->text?></a>&nbsp;
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1)) { ?>
        <div class="mt-3"><?php echo $this->partial('_rating.tpl', 'core', array('item'=>$this->category,'rated' => $this->rated, 'param' => 'create', 'module' => 'video')); ?></div>
      <?php } ?>
    </div>
  </div>
</div>
