<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10125 2013-12-16 19:25:52Z andres $
 * @author     John
 */
?>

<div class="forums_header mb-3">
  <h1 class="m-0"><?php echo $this->translate('Forums') ?></h1>
  <form class="forums_header_search d-flex align-items-center gap-2" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
    <div class="forums_header_search_input">
      <input type='text' class='text suggested' name='query' id='global_search_field1' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>' placeholder='<?php echo $this->translate('Search Forum') ?>' />
      <input type='hidden' name='type' id='type' value="forum_topic" label="Forum Topics"/>
    </div>
    <div class="forums_header_search_btn">
      <button class="btn btn-primary" name="submit" id="submit" type="submit"><?php echo $this->translate('Search') ?></button>
    </div>
  </form>
</div>
<ul class="forum_listing">
  <?php foreach( $this->categories as $category ):
    if( empty($this->forums[$category->category_id]) ) {
      continue;
    }
    ?>
    <li>
      <div class="forum_listing_title">
        <h3><?php echo $this->translate($category->getTitle()) ?></h3>
      </div>
      <ul class="forum_categories">
        <?php foreach( $this->forums[$category->category_id] as $forum ):
          $last_topic = $forum->getLastUpdatedTopic();
          $last_post = null;
          $last_user = null;
            if( $last_topic ) {
              $last_post = $last_topic->getLastCreatedPost();
              $last_user = $this->user($last_post->user_id);
            }
          ?>
          <li class="forum_categories_item d-flex">
            <div class="forum_categories_item_icon">
              <?php echo $this->htmlLink($forum->getHref(),'<i class="fa-solid fa-comments"></i>', array("class" => "font_color_light")) ?>
            </div>
            <div class="forum_categories_item_content d-flex flex-wrap">
              <div class="forum_info">
                <h3><?php echo $this->htmlLink($forum->getHref(), $this->translate($forum->getTitle()), array("class" => "font_color")) ?></h3>
                <span class="forum_desc">
                  <?php echo $forum->getDescription() ?>
                </span>
              </div>
              <div class="forum_stats d-flex  gap-3">
                <div class="forum_posts">
                  <span><?php echo $forum->topicPostCount();?></span>
                  <span>
                    <?php echo $this->translate(array('post', 'posts', $forum->topicPostCount()),$this->locale()->toNumber($forum->topicPostCount())) ?>
                  </span>
                </div>
                <div class="forum_topics">
                  <span><?php echo $forum->topicCount();?></span>
                  <span><?php echo $this->translate(array('topic', 'topics', $forum->topicCount()),$this->locale()->toNumber($forum->topicCount())) ?></span>
                </div>
              </div>
              <div class="forum_lastpost">
                <?php if( $last_topic && $last_topic->approved && $last_post ): ?>
                  <?php echo $this->htmlLink($last_post->getHref(), $this->itemBackgroundPhoto($last_user, 'thumb.icon')) ?>
                  <span class="forum_lastpost_info font_color_light">
                    <?php echo $this->translate('Last reply by %1$s in %2$s', $last_user->__toString(), $this->htmlLink($last_post->getHref(), $last_topic->getTitle())) ?>
                    <?php echo $this->timestamp($last_post->creation_date, array('class' => 'forum_lastpost_date')) ?>
                  </span>
                <?php endif;?>
              </div>
            </div>
          </li>
        <?php endforeach;?>
      </ul>
    </li>
  <?php endforeach;?>
</ul>


