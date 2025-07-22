<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 9989 2013-03-20 01:13:58Z john $
 * @author     Sami
 */
?>


<?php if( engine_count($this->paginator) > 0 ): ?>
  <ul class='manage_listing events_manage'>
    <?php foreach( $this->paginator as $event ): ?>
      <li class="manage_listing_item">
        <article>
          <div class="manage_listing_thumb">
            <?php echo $this->htmlLink($event->getHref(), $this->itemBackgroundPhoto($event, 'thumb.profile')) ?>
          </div>
          <div class="manage_listing_info">
            <div class="manage_listing_header">
              <div class="manage_listing_title">
                <?php echo $this->htmlLink($event->getHref(), $event->getTitle()) ?>
              </div>
              <div class="dropdown options_menu">
                <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></button>
                <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                  <?php if( $this->viewer() && $event->isOwner($this->viewer()) ): ?>
                    <li><?php echo $this->htmlLink(array('route' => 'event_specific', 'action' => 'edit', 'event_id' => $event->getIdentity()), $this->translate('Edit Event'), array('class' => 'dropdown-item icon_edit')) ?></li>
                    <li><?php echo $this->htmlLink(array('route' => 'event_specific', 'module' => 'event', 'controller' => 'event', 'action' => 'delete', 'event_id' => $event->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete Event'), array('class' => 'dropdown-item smoothbox icon_delete')); ?></li>
                  <?php endif; ?>
                  <?php if( $this->viewer() && !$event->membership()->isMember($this->viewer(), null) ): ?>
                    <li><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller'=>'member', 'action' => 'join', 'event_id' => $event->getIdentity()), $this->translate('Join Event'), array('class' => 'dropdown-item smoothbox icon_event_join')) ?></li>
                  <?php elseif( $this->viewer() && $event->membership()->isMember($this->viewer()) && !$event->isOwner($this->viewer()) ): ?>
                    <li><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller'=>'member', 'action' => 'leave', 'event_id' => $event->getIdentity()), $this->translate('Leave Event'), array('class' => 'dropdown-item smoothbox icon_event_leave')) ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
            <div class="manage_listing_owner">
              <span><?php echo $this->translate('led by') ?> <?php echo $this->htmlLink($event->getOwner()->getHref(), $event->getOwner()->getTitle()) ?></span>
              <span><?php echo $this->translate(array('%s guest responses', '%s guest responses', $event->membership()->getMemberCount()),$this->locale()->toNumber($event->membership()->getMemberCount())) ?></span>
            </div>
            <div class="manage_listing_stats">
              <span><i class="icon_calendar"></i><?php echo $this->locale()->toDateTime($event->starttime) ?></span>
            </div>                     
            <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $event)); ?>
          </div>
        </article>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if( $this->paginator->count() > 1 ): ?>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => array('view'=>$this->view, 'text'=>$this->text)
    )); ?>
  <?php endif; ?>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('You have not joined any events yet.') ?></p>
    <?php if( $this->canCreate): ?>
      <p><?php echo $this->translate('Why don\'t you %1$screate one%2$s?', '<a href="'.$this->url(array('action' => 'create'), 'event_general').'">', '</a>') ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>
<script type="text/javascript">
  scriptJquery('.core_main_event').parent().addClass('active');
</script>
