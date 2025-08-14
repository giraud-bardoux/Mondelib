<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>


<ul class="sidebar_list topics_sidebar_list">
  <?php foreach( $this->paginator as $topic ):
    $user = $topic->getOwner('user');
    $forum = $topic->getParent();
    ?>
    <li class="sidebar_list_item">
      <div class='sidebar_list_item_thumb'>
        <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon')) ?>
      </div>
      <div class='sidebar_list_item_info'>
        <div class='sidebar_list_item_title'>
          <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
        </div>
        <div class='sidebar_list_item_owner'>
          <?php echo $this->translate('By') ?>
          <?php echo $this->htmlLink($user->getHref(), $this->translate($user->getTitle())) ?>
          <?php echo $this->translate('In') ?>
          <?php echo $this->htmlLink($forum->getHref(), $this->translate($forum->getTitle())) ?>
        </div>
        <div class='sidebar_list_item_owner'>
          <?php echo $this->timestamp($topic->creation_date) ?>
        </div>
        <div class='sidebar_list_item_desc'>
          <?php echo $this->viewMore(strip_tags($topic->getDescription()), 45) ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<?php //if( $this->paginator->getPages()->pageCount > 1 ): ?>
  <?php //echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array(), 'forum_general', true))); ?>
<?php //endif; ?>

