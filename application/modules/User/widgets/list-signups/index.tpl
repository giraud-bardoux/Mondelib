<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10167 2014-04-15 19:18:29Z lucas $
 * @author     John
 */
?>


<ul class="sidebar_list users_sidebar_list">
  <?php foreach( $this->paginator as $user ): ?>
    <li class="sidebar_list_item">
      <div class="sidebar_list_item_thumb">
        <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon', $user->getTitle())) ?>
      </div>
      <div class='sidebar_list_item_info'>
        <div class='sidebar_list_item_title member_name'>
          <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
        </div>
        <div class='sidebar_list_item_owner'>
          <span><?php echo $this->timestamp($user->creation_date) ?></span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  <?php //if( $this->paginator->getPages()->pageCount > 1 ): ?>
  <li class="sidebar_list_item_more">
    <?php echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array('action' => 'browse'), 'user_general', true), 'param' => array('orderby' => 'creation_date'))); ?></li>
  <?php //endif; ?>
</ul>
  

