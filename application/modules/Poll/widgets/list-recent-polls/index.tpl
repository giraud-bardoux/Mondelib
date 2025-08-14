<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<div class="poll_list_wrapper">
  <ul class="sidebar_list">
    <?php foreach( $this->paginator as $item ): ?>
      <li class="sidebar_list_item">
        <div class="sidebar_list_item_thumb">
          <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.profile'), array('class' => 'thumb_icon bg_thumb_icon d-block')) ?>
        </div>
        <div class="sidebar_list_item_info">
          <div class="sidebar_list_item_title">
            <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
          </div>
          <div class="sidebar_list_item_stats">
            <?php echo $this->timestamp($item->{$this->recentCol}) ?>
          </div>
          <div class="sidebar_list_item_owner">
            <?php
              $owner = $item->getOwner();
              echo $this->translate('Posted by %1$s', $this->htmlLink($owner->getHref(), $owner->getTitle()));
            ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
    <?php echo $this->partial('_widgetLinks.tpl', 'core', array(
      'url' => $this->url(array('action' => 'browse'), 'poll_general', true),
      'param' => array('order' => 'recent')
      )); ?>
  <?php endif; ?>
</div>
