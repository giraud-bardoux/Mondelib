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
            <?php if( $this->popularType == 'view_count' ): ?>
              <?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>
            <?php elseif( $this->popularType == 'comment_count' ): ?>
              <?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>
            <?php elseif( $this->popularType == 'vote_count' ): ?>
              <?php echo $this->translate(array('%s vote', '%s votes', $item->vote_count), $this->locale()->toNumber($item->vote_count)) ?>
            <?php elseif( $this->popularType == 'like_count' ): ?>
              <?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?>
            <?php endif; ?>
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
      'param' => array('order' => 'popular')
      )); ?>
  <?php endif; ?>
</div>
