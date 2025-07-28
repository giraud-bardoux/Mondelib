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
          ?></span>
          <span><?php echo $this->timestamp($item->creation_date) ?></span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
