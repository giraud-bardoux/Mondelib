<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>


<ul class="sidebar_list events_sidebar_list">
  <?php foreach( $this->paginator as $item ): ?>
    <li class="sidebar_list_item">
      <div class="sidebar_list_item_thumb">
        <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.icon')) ?>
      </div>
      <div class="sidebar_list_item_info">
        <div class="sidebar_list_item_title">
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
        </div>
        <div class="sidebar_list_item_owner">
          <span><?php echo $this->translate('hosted by %1$s', $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle())) ?></span>
          <span><?php echo $this->timestamp(strtotime($item->creation_date)) ?></span>
        </div>
        <div class="sidebar_list_item_stats">
          <?php if( $this->popularType == 'view_count' ): ?>
            <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?></span></div>
          <?php elseif( $this->popularType == 'member_count' ): ?>
            <span><i class="icon_users"></i><?php echo $this->translate(array('%s member', '%s members', $item->member_count), $this->locale()->toNumber($item->member_count)) ?></span>
          <?php elseif( $this->popularType == 'comment_count' ): ?>
            <span><i class="icon_comment"></i><?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?></span>
          <?php elseif( $this->popularType == 'like_count' ): ?>
            <span><i class="icon_like"></i><?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
    <li class="sidebar_list_item_more"><?php echo $this->partial('_widgetLinks.tpl', 'core', array( 'url' => $this->url(array(), 'event_general', true), 'param' => array('order' => 'member_count+DESC'))); ?></li>
  <?php endif; ?>
</ul>
