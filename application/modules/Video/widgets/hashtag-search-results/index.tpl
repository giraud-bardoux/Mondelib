<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9859 2013-02-12 02:06:55Z john $
 * @author     Jung
 */
?>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="container no-padding video_listing">
    <div class="row grid_listing">
      <?php foreach( $this->paginator as $item ): ?>
        <div class="col-lg-4 col-md-6 grid_listing_item">
          <article>
            <div class="grid_listing_item_thumb">
              <?php if( $item->duration ): ?>
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
              <?php endif ?>
              <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.normal')) ?>
            </div>
            <div class="grid_listing_item_info">
              <div class="grid_listing_item_title">
                <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('area-label' => $item->getTitle())) ?>
              </div>
              <div class="grid_listing_item_owner">
                <span><?php echo $this->translate('By') ?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?></span>
                <span><?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?></span>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php else:?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No one has created a video yet.');?></p>
  </div>
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues, 'pageAsQuery' => true)); ?>
