<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="row">
    <?php foreach( $this->paginator as $photo ): ?>
      <div class="col-lg-4 col-md-6 grid_outer">
      <div class="grid_wrapper albums_block" id="thumbs-photo-<?php echo $photo->photo_id ?>">
        <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
          <?php echo $this->itemBackgroundPhoto($photo, 'thumb.normal')?>
          <?php if($photo->like_count || $photo->comment_count || $photo->view_count) { ?>
            <div class="info_stat_grid">
              <?php if( $photo->like_count > 0 ) :?>
                <span>
                  <i class="fa fa-thumbs-up"></i>
                  <?php echo  $this->locale()->toNumber($photo->like_count) ?>
                </span>
              <?php endif; ?>
              <?php if( $photo->comment_count > 0 ) :?>
                <span>
                  <i class="fa fa-comment"></i>
                  <?php echo  $this->locale()->toNumber($photo->comment_count) ?>
                </span>
              <?php endif; ?>
              <?php if( $photo->view_count > 0 ) :?>
                <span class="album_view_count">
                  <i class="fa fa-eye"></i>
                  <?php echo  $this->locale()->toNumber($photo->view_count) ?>
                </span>
              <?php endif; ?>
            </div>
          <?php } ?>
          <div class="browse_photos_rating">
            <?php echo $this->partial('_rating.tpl', 'core', array('item' => $photo, 'module' => 'album', 'param' => 'show')); ?>
          </div>
        </a>
      </div>
    </div>
    <?php endforeach;?>
  </div>
<?php else:?>
<div class="tip">
    <span>
      <?php echo $this->translate('No one has uploaded a photo with that criteria.'); ?>
    </span>
</div>
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true, 'query' => $this->formValues)); ?>
