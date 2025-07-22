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
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="breadcrumb_wrap">
      <div class="event_breadcrumb">
        <p><?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?></p>
      </div>
    </div>
  </div>
  <div class="generic_layout_container">
    <div class="forum_header d-flex gap-3 mb-3 align-items-center">
      <div><?php echo $this->htmlLink(array('route' => 'event_profile', 'id' => $this->event->getIdentity(),'slug' => $this->event->getSlug()), $this->translate('Back to Event'), array('class' => 'btn btn-alt icon_back')) ?></div>
      <?php if ($this->can_post) { ?>
        <div><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'create', 'subject' => $this->event->getGuid()), $this->translate('Post New Topic'), array('class' => 'btn btn-primary icon_add'));?> </div>
      <?php } ?>
    </div>
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
                <?php echo $this->viewMore($topic->getDescription()) ?>
              </div>
            </div>
            <div class="discussions_listing_item_lastpost d-flex flex-wrap">
              <?php echo $this->htmlLink($lastposter->getHref(), $this->itemBackgroundPhoto($lastposter, 'thumb.icon')) ?>
              <div class="discussions_listing_item_lastpost_info font_color_light font_small">
                <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> by <?php echo $lastposter->__toString() ?>
                <br />
                <?php echo $this->timestamp(strtotime($topic->modified_date)) ?>
              </div>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->paginationControl($this->paginator) ?>
    <?php endif; ?>
  </div>
</div>

<script type="text/javascript">
  scriptJquery('.core_main_event').parent().addClass('active');
</script>
