<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Jung
 */
?>
<?php if (($this->current_count >= $this->quota) && !empty($this->quota)):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('You have already created the maximum number of videos allowed. If you would like to post a new video, please delete an old one first.');?>
    </span>
  </div>
  <br/>
<?php endif; ?>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
<ul class='manage_listing videos_manage'>
  <?php foreach( $this->paginator as $item ): ?>
    <li class="manage_listing_item">
      <article>
        <div class="manage_listing_thumb">
          <?php if ($item->duration):?>
          <span class="item_length">
            <?php
              if( $item->duration >= 3600 ) {
                $duration = gmdate("H:i:s", $item->duration);
              } else {
                $duration = gmdate("i:s", $item->duration);
              }
              echo $duration;
            ?>
          </span>
          <?php endif;?>
          <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.normal')); ?>
        </div>
        <div class="manage_listing_info">
          <div class="manage_listing_header">
            <div class="manage_listing_title">
              <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
            </div>
            <div class="dropdown options_menu">
              <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></button>
              <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'video', 'controller' => 'index', 'action' => 'edit', 'video_id' => $item->video_id), $this->translate('Edit Video'), array('class' => 'dropdown-item icon_edit')) ?></li>
                <?php
                if ($item->status !=2){ ?>
                  <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'video', 'controller' => 'index', 'action' => 'delete', 'video_id' => $item->video_id, 'format' => 'smoothbox'), $this->translate('Delete Video'), array('class' => 'dropdown-item smoothbox icon_delete')); ?></li>
                <?php }?>
              </ul>
            </div>
          </div>
          <!-- <div class="video_desc">
            <?php // echo $this->string()->truncate($this->string()->stripTags($item->description), 300) ?>
          </div> -->
          <div class="manage_listing_owner">
            <span><?php echo $this->translate('Added');?> <?php echo $this->timestamp(strtotime($item->creation_date)) ?></span>
          </div>
          <div class="manage_listing_stats">
            <span><i class="icon_like"></i><?php echo $this->translate(array('%s like', '%s likes', $item->likes()->getLikeCount()),$this->locale()->toNumber($item->likes()->getLikeCount())) ?></span>
            <span><i class="icon_comment"></i><?php echo $this->translate(array('%s comment', '%s comments', $item->comments()->getCommentCount()),$this->locale()->toNumber($item->comments()->getCommentCount())) ?></span>
            <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $item->view_count),$this->locale()->toNumber($item->view_count)) ?></span>
          </div>
          <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $item)); ?>
          <?php if($item->status == 0):?>
            <div class="error_msg">
              <span>
                <?php echo $this->translate('Your video is in queue to be processed - you will be notified when it is ready to be viewed.')?>
              </span>
            </div>
          <?php elseif($item->status == 2):?>
            <div class="error_msg">
              <span>
                <?php echo $this->translate('Your video is currently being processed - you will be notified when it is ready to be viewed.')?>
              </span>
            </div>
          <?php elseif($item->status == 3):?>
            <div class="error_msg">
              <span>
              <?php echo $this->translate('Video conversion failed. Please try %1$suploading again%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=> 'upload')).'">', '</a>'); ?>
              </span>
            </div>
          <?php elseif($item->status == 4):?>
            <div class="error_msg">
              <span>
              <?php echo $this->translate('Video conversion failed. Video format is not supported by FFMPEG. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=>'upload')).'">', '</a>'); ?>
              </span>
            </div>
          <?php elseif($item->status == 5):?>
            <div class="error_msg">
              <span>
              <?php echo $this->translate('Video conversion failed. Audio files are not supported. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create', 'type'=>'upload')).'">', '</a>'); ?>
              </span>
            </div>
          <?php elseif($item->status == 7):?>
            <div class="error_msg">
              <span>
              <?php echo $this->translate('Video conversion failed. You may be over the site upload limit.  Try %1$suploading%2$s a smaller file, or delete some files to free up space.', '<a href="'.$this->url(array('action' => 'create', 'type'=>'upload')).'">', '</a>'); ?>
              </span>
            </div>
          <?php endif;?>
        </div>
      </article>
    </li>
  <?php endforeach; ?>
</ul>
<?php else:?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('You do not have any videos.');?></p>
    <?php if ($this->can_create): ?>
      <p><?php echo $this->translate('Get started by %1$sposting%2$s a new video.', '<a href="'.$this->url(array('action' => 'create')).'">', '</a>'); ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator); ?>
<script type="text/javascript">
  scriptJquery('.core_main_video').parent().addClass('active');
</script>
