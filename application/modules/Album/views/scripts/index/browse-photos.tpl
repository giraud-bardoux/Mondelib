<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: browse-photos.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>

<?php if( $this->tag ): ?>
  <h3>
    <?php echo $this->translate('Photos using the tag');?> #<?php echo $this->tag;?> <a href="<?php echo $this->url(array('module' => 'album', 'controller' => 'index', 'action' => 'browse-photos'), 'album_general', true) ?>">(x)</a>
  </h3>
<?php endif; ?>

 <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="container no-padding">
    <div class="row">
       <?php foreach( $this->paginator as $photo ): ?>
        <div class="col-lg-4 col-6 grid_outer">
          <div class="grid_wrapper albums_grid" id="thumbs-photo-<?php echo $photo->photo_id ?>">
            <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
              <?php echo $this->itemBackgroundPhoto($photo, 'thumb.normal')?>
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
              <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1)) { ?>
                <div class="browse_photos_rating">
                  <?php echo $this->partial('_rating.tpl', 'core', array('item' => $photo, 'module' => 'album', 'param' => 'show')); ?>
                </div>
              <?php } ?>
            </a>
          </div>
       </div>
      <?php endforeach;?>
    </div>
  </div>
  <?php echo $this->paginationControl(
    $this->paginator, null, null, array(
      'pageAsQuery' => false,
      'query' => $this->formValues
      )); ?>
<?php elseif( $this->tag || $this->search ): ?>
  <div class="tip">
    <span id="no-album-criteria">
      <?php echo $this->translate('No one has uploaded a photo with that criteria.');?>
      <?php if( $this->canCreate ): ?>
        <?php $create = $this->translate('Be the first to %1$supload%2$s one!',
          '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>');
        ?>
        <script type="text/javascript">
          if(!DetectMobileQuick() && !DetectIpad()){
            var create = '<?php echo $create ?>';
            var text = document.getElementById('no-album-criteria');
            text.innerHTML = text.innerHTML + create ;
          }
        </script>
      <?php endif; ?>
    </span>
  </div>
<?php else: ?>
  <div class="tip">
    <span id="no-album">
      <?php echo $this->translate('No one has uploaded a photo yet.');?>
      <?php if( $this->canCreate ): ?>
        <?php
         $create = $this->translate('Get started by %1$suploading%2$s your first one!',
          '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>');
        ?>
        <script type="text/javascript">
          if(!DetectMobileQuick() && !DetectIpad()){
            var create = '<?php echo $create ?>';
            var text = document.getElementById('no-album');
            text.innerHTML = text.innerHTML + create ;
          }
        </script>
      <?php endif; ?>
    </span>
  </div>
<?php endif; ?>
