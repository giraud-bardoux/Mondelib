<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>


<ul class="sidebar_list blogs_sidebar_list">
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
          <span><?php echo $this->timestamp($item->{$this->recentCol}) ?></span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
    <li class="sidebar_list_item_more">
      <?php echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array('action' => 'index'), 'blog_general', true), 'param' => array('orderby' => 'creation_date'))); ?>
    </li>
  <?php endif; ?>
</ul>



