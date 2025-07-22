<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>
<h3>
  <?php echo $this->translate('Event Details') ?>
</h3>
<div id='event_stats'>
  <ul>
    <?php if( !empty($this->subject->description) ): ?>
    <li>
      <div class="rich_content_body">
        <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->subject->description); ?>
      </div>
    </li>
    <?php endif ?>
    <li class="event_date">
      <?php
        // Convert the dates for the viewer
        $startDateObject = new Zend_Date(strtotime($this->subject->starttime));
      $endDateObject = new Zend_Date(strtotime($this->subject->endtime));
      if( $this->viewer() && $this->viewer()->getIdentity() ) {
      $tz = $this->viewer()->timezone;
      $startDateObject->setTimezone($tz);
      $endDateObject->setTimezone($tz);
      }
      ?>
      <?php if( $this->subject->starttime == $this->subject->endtime ): ?>
      <div class="label">
        <?php echo $this->translate('Date') ?>
      </div>
      <div class="event_stats_content">
        <?php echo $this->locale()->toDate($startDateObject) ?>
      </div>

      <div class="label">
        <?php echo $this->translate('Time') ?>
      </div>
      <div class="event_stats_content">
        <?php echo $this->locale()->toTime($startDateObject) ?>
      </div>

      <?php elseif( $startDateObject->toString('y-MM-dd') == $endDateObject->toString('y-MM-dd') ): ?>
      <div class="label">
        <?php echo $this->translate('Date')?>
      </div>
      <div class="event_stats_content">
        <?php echo $this->locale()->toDate($startDateObject) ?>
      </div>

      <div class="label">
        <?php echo $this->translate('Time')?>
      </div>
      <div class="event_stats_content">
        <?php echo $this->locale()->toTime($startDateObject) ?>
        -
        <?php echo $this->locale()->toTime($endDateObject) ?>
      </div>

      <?php else: ?>
      <div class="event_stats_content">
        <?php echo $this->translate('%1$s at %2$s',
        $this->locale()->toDate($startDateObject),
        $this->locale()->toTime($startDateObject)
        ) ?>
        - <br />
        <?php echo $this->translate('%1$s at %2$s',
        $this->locale()->toDate($endDateObject),
        $this->locale()->toTime($endDateObject)
        ) ?>
      </div>
      <?php endif ?>
    </li>

    <?php if(empty($this->subject->is_online) && !empty($this->subject->location) ) { ?>
      <li>
        <div class="label"><?php echo $this->translate('Where')?></div>
        <div class="event_stats_content"><?php echo $this->subject->location; ?> <?php echo $this->htmlLink('https://maps.google.com/?q='.urlencode($this->subject->location), $this->translate('Map'), array('target' => 'blank')) ?></div>
      </li>
    <?php } else if(!empty($this->subject->is_online && !empty($this->subject->website))) { ?>
      <li>
        <div class="label"><?php echo $this->translate('Where')?></div>
        <div class="event_stats_content"><a href="<?php echo $this->subject->website; ?>"><?php echo $this->subject->website ?></a> </div>
      </li>
    <?php } ?>

    <?php if( !empty($this->subject->host) ): ?>
    <?php if( $this->subject->host != $this->subject->getParent()->getTitle()): ?>
    <li>
      <div class="label"><?php echo $this->translate('Host') ?></div>
      <div class="event_stats_content"><?php echo $this->subject->host ?></div>
    </li>
    <?php endif ?>
      <li>
        <div class="label"><?php echo $this->translate('Led by') ?></div>
        <div class="event_stats_content"><?php echo $this->subject->getParent()->__toString() ?></div>
      </li>
    <?php endif ?>

    <?php if( !empty($this->subject->category_id) && $this->subject->categoryName()): ?>
      <li>
        <div class="label"><?php echo $this->translate('Category')?></div>
        <div class="event_stats_content">
          <?php echo $this->htmlLink(array(
          'route' => 'event_general',
          'action' => 'browse',
          'category_id' => $this->subject->category_id,
          ), $this->translate((string) $this->subject->categoryName())) ?>
        </div>
      </li>
    <?php endif ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('event.enable.rating', 1)) { ?>
      <li>
        <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->subject, 'module' => 'event', 'param' => 'create', 'notificationType' => 'event_rating')); ?>
      </li>
    <?php } ?>
    <li class="event_stats_info">
      <div class="label"><?php echo $this->translate('RSVPs');?></div>
      <div class="event_stats_content">
        <ul>
          <li>
            <?php echo $this->locale()->toNumber($this->subject->getAttendingCount()) ?>
            <span><?php echo $this->translate('attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject->getMaybeCount()) ?>
            <span><?php echo $this->translate('maybe attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject->getNotAttendingCount()) ?>
            <span><?php echo $this->translate('not attending');?></span>
          </li>
          <li>
            <?php echo $this->locale()->toNumber($this->subject->getAwaitingReplyCount()) ?>
            <span><?php echo $this->translate('awaiting reply');?></span>
          </li>
        </ul>
      </div>
    </li>
  </ul>
</div>

<script type="text/javascript">
  scriptJquery('.core_main_event').parent().addClass('active');
    
  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
