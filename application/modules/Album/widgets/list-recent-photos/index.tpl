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

<ul class="generic_list_widget">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class="photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.profile'), array('class' => 'thumb_icon')) ?>
      </div>
      <div class="info">
        <div class="title">
          <?php echo $this->htmlLink($item->getHref(), $this->string()->truncate($item->getTitle(), 13)) ?>
        </div>
        <div class="stats">
          <?php echo $this->timestamp($item->{$this->recentCol}) ?>
        </div>
        <div class="owner">
          <?php
            $owner = $item->getOwner();
            $parent = $item->getParent();
            echo $this->translate('Posted by %1$s in the album %2$s',
                $this->htmlLink($owner->getHref(), $owner->getTitle()),
                $this->htmlLink($parent->getHref(), $parent->getTitle()));
          ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
