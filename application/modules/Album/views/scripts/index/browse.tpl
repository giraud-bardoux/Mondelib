<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Album
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: browse.tpl 10217 2014-05-15 13:41:15Z lucas $
* @author     Sami
*/
?>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
<div class="container no-padding albums_listing">
  <div class="row grid_listing">
    <?php $auth = Engine_Api::_()->authorization()->context; ?>
    <?php foreach( $this->paginator as $album ): ?>
      <?php
        if( $album->view_privacy == 'owner_network' && !engine_in_array($this->viewer()->level_id, array(1, 2, 3)) ) {
          if( !$auth->isAllowed($album, $this->viewer(), 'view') ) {
          continue;
          }
        }
      ?>
      <div class="col-lg-4 col-md-6 grid_listing_item">
        <article>
          <div class="grid_listing_item_thumb">
            <a class="slideshow-container" href="<?php echo $album->getHref(); ?>">
              <?php echo $this->itemBackgroundPhoto($album, 'thumb.normal', '', array('class' => 'slideshow-item'))?>
              <?php $photoCount = 0; ?>
              <?php foreach( $album->getPhotos(5) as $photo ): ?>
                <?php if( $photo->photo_id != $album->photo_id && $photoCount < 4 ): ?>
                  <?php echo $this->itemBackgroundPhoto($photo, 'thumb.normal', '', array('class' => 'slideshow-item'))?>
                <?php $photoCount++; ?>
              <?php endif; ?>
              <?php endforeach;?>
              <?php if( $album->count() > 0 ): ?>
                <div class="item_count">
                  <i class="icon_photos"></i>
                  <span><?php echo $this->locale()->toNumber($album->count()) ?></span>
                </div>
              <?php endif; ?>
            </a>
          </div>

          <div class="grid_listing_item_info">
            <div class="grid_listing_item_title">
              <?php echo $this->htmlLink($album, $this->string()->chunk($this->string()->truncate($this->translate($album->getTitle()), 45), 10)) ?>
            </div>
            <div class="grid_listing_item_owner">
              <span><?php echo $this->translate('By');?> <?php echo $this->htmlLink($album->getOwner()->getHref(), $album->getOwner()->getTitle()) ?></span>
            </div>
            <div class="grid_listing_item_stats">
              <?php if( $album->like_count > 0 ) :?>
                <span>
                  <i class="icon_like"></i><?php echo $this->locale()->toNumber($album->like_count) ?>
                </span>
              <?php endif; ?>
              <?php if( $album->comment_count > 0 ) :?>
                <span>
                  <i class="icon_comment"></i>
                  <?php echo $this->locale()->toNumber($album->comment_count) ?>
                </span>
              <?php endif; ?>
              <?php if( $album->view_count > 0 ) :?>
                <span class="album_view_count">
                  <i class="icon_view"></i>
                  <?php echo $this->locale()->toNumber($album->view_count) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach;?>
  </div>
</div>
<script>
  en4.core.runonce.add(function() {
    /* photo slideshow */
    var showDuration = 1000;
    var containers = scriptJquery('.slideshow-container');
    containers.each(function () {
      var container = scriptJquery(this);
      var currentIndex = 0;
      var items = container.find('.slideshow-item');
      var interval;
      var start = function() {
        interval = setInterval(show,showDuration);
      };
      var stop = function() {
        clearInterval(interval);
      };
      /* worker */
      var show = function() {
        items.eq(currentIndex).hide();
        items.eq(currentIndex = currentIndex < items.length - 1 ? currentIndex+1 : 0).css('display','block');
      };
      /* control: start/stop on mouseover/mouseout */
      container.mouseenter(function() {
        start();
      }
      ).mouseleave(function() {
        stop();
      }
    );
    }
  );
  });
</script>
<?php if( $this->paginator->count() > 1 ): ?>
  <?php echo $this->paginationControl(
    $this->paginator, null, null, array('pageAsQuery' => false, 'query' => $this->searchParams)); ?>
  <?php endif; ?>
<?php elseif( $this->searchParams['category_id'] ): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p id="no-album-criteria">
      <?php echo $this->translate('Nobody has created an album with that criteria.');?><br>
      <?php if( Engine_Api::_()->authorization()->isAllowed('album', null, 'create') ): ?>
      <?php $create = $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>'); ?>
      <script type="text/javascript">
        if(!DetectMobileQuick() && !DetectIpad()){
          var create = '<?php echo $create ?>';
          var text = document.getElementById('no-album-criteria');
          text.innerHTML = text.innerHTML + create ;
        }
      </script>
      <?php endif; ?>
    </p>
  </div>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p id="no-album">
      <?php echo $this->translate('Nobody has created an album yet.');?><br />
      <?php if( Engine_Api::_()->authorization()->isAllowed('album', null, 'create') ): ?>
      <?php $create = $this->translate('Get started by %1$screating%2$s your first album!',  '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>'); ?>
      <script type="text/javascript">
        if(!DetectMobileQuick() && !DetectIpad()){
          var create = '<?php echo $create ?>';
          var text = document.getElementById('no-album');
          text.innerHTML = text.innerHTML + create ;
        }
      </script>
      <?php endif; ?>
    </p>
  </div>
<?php endif; ?>
