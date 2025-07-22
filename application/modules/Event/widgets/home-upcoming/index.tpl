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

<ul class="sidebar_list events_sidebar_list" id="events-upcoming">
  <?php foreach( $this->paginator as $event ):
    // Convert the dates for the viewer
    $startDateObject = new Zend_Date(strtotime($event->starttime));
    $endDateObject = new Zend_Date(strtotime($event->endtime));
    if( $this->viewer() && $this->viewer()->getIdentity() ) {
      $tz = $this->viewer()->timezone;
      $startDateObject->setTimezone($tz);
      $endDateObject->setTimezone($tz);
    }
    $isOngoing = ( $startDateObject->toValue() < time() );
    ?>
    <li class="sidebar_list_item <?php if( $isOngoing ):?> ongoing<?php endif ?>">
      <div class="sidebar_list_item_thumb">
        <?php echo $this->htmlLink($event->getHref(), $this->itemBackgroundPhoto($event, 'thumb.icon')) ?>
      </div>
      <div class="sidebar_list_item_info">
        <div class="sidebar_list_item_title">
          <?php echo $event->__toString() ?>
        </div>
        <div class="sidebar_list_item_owner">
          <span><?php echo $this->timestamp($event->starttime, array('class'=>'eventtime')) ?></span>
          <?php if( $isOngoing ): ?>
            <span class="events-upcoming-ongoing"><?php echo $this->translate('Ongoing') ?></span>
          <?php endif; ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
    <li class="sidebar_list_item_more"><?php echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array(), 'event_general', true))); ?></li>
  <?php endif; ?>
</ul>


