<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @access	   John
 */
?>

<?php if( $this->canPost || $this->paginator->count() > 1 ): ?>
  <div class="profile_tab_options">
    <?php if( $this->canPost ):?>
      <div>
        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'create','subject' => $this->subject()->getGuid()), $this->translate('Post New Topic'), array('class' => 'btn btn-alt icon_add'));?>
      </div>
    <?php endif;?>
    <?php if( $this->paginator->count() > 1 ): ?>
      <div>
        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'index', 'subject' => $this->subject()->getGuid()), 'View All '.$this->paginator->getTotalItemCount().' Topics', array('class' => 'btn btn-alt icon_viewall')) ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif;?>


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <ul class="discussions_listing">
    <?php foreach( $this->paginator as $topic ):
      $lastpost = $topic->getLastPost();
      $lastposter = $topic->getLastPoster();
      ?>
      <li class="discussions_listing_item">
        <div class="discussions_listing_item_thumb">
          <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemBackgroundPhoto($topic->getOwner(), 'thumb.icon')) ?>
        </div>
        <div class="discussions_listing_item_content d-flex flex-wrap">
          <div class="discussions_listing_item_info">
            <h3><?php if( $topic->sticky ): ?>📌<?php endif; ?><?php echo $this->htmlLink($topic->getHref(), $topic->getTitle(), array("class" => "font_color")) ?></h3>
            <div class="discussions_listing_item_stats font_small font_color_light">
              <span><?php echo $this->htmlLink($topic->getOwner()->getHref(), $topic->getOwner()->getTitle(), array("class" => "font_color font_bold")); ?></span>
              <span><?php echo $this->timestamp($topic->creation_date) ?></span>
              <span>
                <?php echo $this->locale()->toNumber($topic->post_count - 1) ?>
                <?php echo $this->translate(array('reply', 'replies', $topic->post_count - 1)) ?>
              </span>
            </div>
            <div class="discussions_listing_item_desc font_small">
              <?php echo $this->viewMore(strip_tags($topic->getDescription()), 255, 1027, 511, false); ?>
            </div>
          </div>
        </div>
        <div class="discussions_listing_item_lastpost d-flex flex-wrap">
          <?php echo $this->htmlLink($lastposter->getHref(), $this->itemBackgroundPhoto($lastposter, 'thumb.icon')) ?>
          <div class="discussions_listing_item_lastpost_info font_color_light font_small">
            <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> <?php echo $this->translate('by');?> <?php echo $lastposter->__toString() ?>
            <br />
            <?php echo $this->timestamp(strtotime($topic->modified_date)) ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No topics have been posted in this event yet.');?></p>
  </div>
<?php endif; ?>
