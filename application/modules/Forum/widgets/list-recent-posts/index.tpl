<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 9756 2012-08-09 23:01:42Z richard $
 * @author     John
 */
?>

<ul class="sidebar_list topic_posts_sidebar_list">
  <?php foreach( $this->paginator as $post ):
    $user = $post->getOwner();
    $topic = $post->getParent();
    $forum = $topic->getParent();
    ?>
    <li class="sidebar_list_item">
      <div class="sidebar_list_item_thumb">
        <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon'), array('class' => 'thumb_icon')) ?>
      </div>
      <div class='sidebar_list_item_info'>
        <div class='sidebar_list_item_title'>
          <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
        </div>
        <div class="sidebar_list_item_owner">
          <?php echo $this->translate('In') ?>
          <?php echo $this->htmlLink($topic->getHref(), $this->translate($topic->getTitle())) ?>
          -
          <?php echo $this->htmlLink($forum->getHref(), $this->translate($forum->getTitle())) ?>
        </div>
        <div class='sidebar_list_item_owner'>
          <?php echo $this->timestamp($post->creation_date) ?>
        </div>
        <div class='sidebar_list_item_desc'>
          <?php echo $this->viewMore(strip_tags($post->getDescription()), 45) ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<?php //if( $this->paginator->getPages()->pageCount > 1 ): ?>
  <?php //echo $this->partial('_widgetLinks.tpl', 'core', array('url' => $this->url(array(), 'forum_general', true))); ?>
<?php //endif; ?>
