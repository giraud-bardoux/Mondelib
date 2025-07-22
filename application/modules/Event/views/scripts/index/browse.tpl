<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Event
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: browse.tpl 9987 2013-03-20 00:58:10Z john $
* @author     John Boehr <john@socialengine.com>
*/

?>
<?php if( engine_count($this->paginator) > 0 ): ?>
  <?php $tabClass = "upcoming_events"; ?>
  <?php if( $this->filter == "past" ): ?>
    <?php $tabClass = "past_events"; ?>
  <?php endif; ?>
  <div class="container no-padding events_listing">
    <div class='row grid_listing <?php echo $tabClass ?>'>
      <?php foreach( $this->paginator as $event ): ?>
        <div class="col-lg-4 col-md-6 grid_listing_item">
          <article>
            <div class="grid_listing_item_thumb">
              <?php $startTime = $this->locale()->toDateTime($event->starttime); ?>
              <?php $endTime = $this->locale()->toDateTime($event->endtime); ?>
              <?php if( $this->filter == "past" ): ?>
                <?php $imgTitle = array('title' => "Ends on : $endTime");?>
              <?php else: ?>
                <?php $imgTitle = array('title' => "Starts on : $startTime");?>
              <?php endif; ?>
              <?php echo $this->htmlLink($event->getHref(), $this->itemBackgroundPhoto($event, 'thumb.normal'), $imgTitle) ?>
            </div>
            <div class="grid_listing_item_info">
              <div class="grid_listing_item_title">
                <?php echo $this->htmlLink($event->getHref(), $event->getTitle()) ?>
              </div>
              <div class="grid_listing_item_owner">
                <span><?php echo $this->translate('led by') ?> <?php echo $this->htmlLink($event->getOwner()->getHref(), $event->getOwner()->getTitle()) ?></span>
                <span><?php echo $this->translate(array('%s guest response', '%s guest responses', $event->membership()->getMemberCount()),$this->locale()->toNumber($event->membership()->getMemberCount())) ?></span>
              </div>
              <div class="grid_listing_item_stat" title="<?php echo $startTime; ?> - <?php echo $endTime; ?>">
                <i class="icon_calendar"></i>
                <span>
                  <span class="event_start_date"><?php echo $startTime; ?></span>
                  <span class="event_end_date"><?php echo $endTime; ?></span>
                </span>
              </div>
              <?php if(empty($event->is_online) && $event->location ): ?>
                <div class="grid_listing_item_stat">
                  <i class="icon_location"></i><span><?php echo $event->location ?></span>
                </div>
              <?php elseif(!empty($event->is_online && $event->website)): ?>
                <div class="grid_listing_item_stat">
                  <i class="icon_location"></i>
                  <?php $website = (preg_match("#https?://#", $event->website) === 0) ? 'http://'.$event->website : $event->website; ?>
                  <span>
                    <a href="<?php echo $website ?>" target="_blank"><?php echo $website ?></a>
                  </span>
                </div>
              <?php endif; ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'query' => $this->formValues,
      )); ?>
    <?php endif; ?>
  </div>
<?php elseif( preg_match("/category_id=/", $_SERVER['REQUEST_URI'] )): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No one has created an event with that criteria.');?></p>
    <?php if( $this->canCreate ): ?>
      <p><?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action'=>'create'), 'event_general').'">', '</a>'); ?></p>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
  <?php if( $this->filter != "past" ): ?>
    <p><?php echo $this->translate('No one has created an event yet.') ?></p>
    <?php if( $this->canCreate ): ?>
      <p><?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action'=>'create'), 'event_general').'">', '</a>'); ?></p>
    <?php endif; ?>
  <?php else: ?>
    <p><?php echo $this->translate('There are no past events yet.') ?></p>
  <?php endif; ?>
  </div>
<?php endif; ?>
<script type="text/javascript">
  scriptJquery('.core_main_event').parent().addClass('active');
</script>
