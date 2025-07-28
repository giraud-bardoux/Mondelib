<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<ul class="sidebar_list video_sidebar_list">
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
          <span>
            <?php
              $owner = $item->getOwner();
              echo $this->translate('by %1$s', $this->htmlLink($owner->getHref(), $owner->getTitle()));
            ?>
          </span>
          <span>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1) && $this->popularType == 'rating' ): ?>
              <?php echo $this->translate('%s / %s', $this->locale()->toNumber(sprintf('%01.1f', $item->rating)), $this->locale()->toNumber('5.0')) ?>
            <?php elseif( $this->popularType == 'comment_count' ): ?>
              <?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>
            <?php elseif( $this->popularType == 'view_count' ): ?>
              <?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>
            <?php elseif( $this->popularType == 'like_count' ): ?>
              <?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?>
            <?php endif; ?>
          </span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
    <li class="sidebar_list_item_more">
      <?php echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array(), 'video_general', true), 'param' => array('orderby' => 'view_count'))); ?>
    </li>
  <?php endif; ?>
</ul>
    


