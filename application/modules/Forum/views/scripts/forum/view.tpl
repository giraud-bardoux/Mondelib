<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     John
 */
?>
<div class="generic_layout_container">
  <div class="breadcrumb_wrap">
    <div class="forum_breadcrumb">
      <p><?php echo $this->htmlLink(array('route'=>'forum_general'), $this->translate("Forums"));?> &#187; <?php echo $this->translate($this->forum->getTitle()) ?></p>
    </div>
  </div>
</div>
<h1 class="mb-3"><?php echo $this->translate($this->forum->getTitle()) ?></h1>
<div class="forum_header d-flex gap-3 mb-3 align-items-center">
  <?php if( $this->canPost ): ?>
    <div class="topic_create_btn">
      <?php echo $this->htmlLink($this->forum->getHref(array('action' => 'topic-create', )), $this->translate('Post New Topic'), array('class' => 'btn btn-primary icon_add')) ?>
    </div>
  <?php endif; ?>
  <div class="forum_moderators">
    <span><?php echo $this->translate('Moderators:');?></span>
    <span><?php echo $this->fluentList($this->moderators) ?></span>
  </div>
</div>
<?php if( engine_count($this->paginator) > 0 ): ?>
  <ul class="discussions_listing">
    <?php foreach( $this->paginator as $i => $topic ):
      $last_post = $topic->getLastCreatedPost();
      if( $last_post ) {
        $last_user = $this->user($last_post->user_id);
      } else {
        $last_user = $this->user($topic->user_id);
      }
      ?>
      <li class="discussions_listing_item forum_nth_<?php echo $i % 2 ?> <?php if( $topic->sticky ): ?>forum_sticky<?php endif; ?>">
        <div class="discussions_listing_item_thumb">
          <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemBackgroundPhoto($topic->getOwner(), 'thumb.icon')) ?>
          <?php // if( $topic->isViewed($this->viewer()) ): ?>
            <?php // echo $this->htmlLink($topic->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Forum/externals/images/topic.png')) ?>
          <?php // else: ?>
            <?php // echo $this->htmlLink($topic->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Forum/externals/images/topic_unread.png')) ?>
          <?php // endif; ?>
        </div>
        <div class="discussions_listing_item_content d-flex flex-wrap">
          <div class="discussions_listing_item_info">
            <h3><?php if( $topic->closed && $topic->sticky): ?><i>📌 ❌</i><?php elseif( $topic->sticky ): ?><i>📌</i><?php elseif( $topic->closed ): ?><i>❌</i><?php endif; ?><?php echo $this->htmlLink($topic->getHref(), $topic->getTitle(), array("class"=> "font_color"));?></h3>
            <div class="discussions_listing_item_stats font_small font_color_light">
              <span><?php echo $this->htmlLink($topic->getOwner()->getHref(), $topic->getOwner()->getTitle(), array("class" => "font_color font_bold")); ?></span>
              <span><?php echo $this->timestamp($topic->creation_date) ?></span>
              <span><?php echo $this->translate(array('%1$s view', '%1$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count)) ?></span>
              <span>
                <?php $post_count = $topic->postCount() > 1 ? $topic->postCount() - 1 : 0; ?>
                <?php echo $this->translate(array('%1$s reply', '%1$s replies', $post_count), $this->locale()->toNumber($post_count)) ?>
              </span>
            </div>
            <?php echo $this->pageLinks($topic, $this->forum_topic_pagelength, null, 'discussions_listing_item_pagelinks') ?>
          </div>
          <div class="discussions_listing_item_lastpost d-flex flex-wrap align-items-center">
            <?php if( $last_post):
              list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
              ?>
              <?php echo $this->htmlLink($last_post->getHref(), $this->itemBackgroundPhoto($last_user, 'thumb.icon')) ?>
              <span class="discussions_listing_item_lastpost_info font_color_light font_small">
                <?php echo $this->translate(
                  '%1$sLast post%2$s by %3$s',
                  $openTag,
                  $closeTag,
                  $this->htmlLink($last_user->getHref(), $last_user->getTitle())
                )?><br>
                <?php echo $this->timestamp($topic->modified_date) ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php elseif( preg_match("/search=/", $_SERVER['REQUEST_URI'] )): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No one has created a forum with that criteria.');?></p>
  </div>   
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('There are no forums yet.') ?></p>
  </div>
<?php endif; ?>
<div class="forum_pages">
  <?php echo $this->paginationControl($this->paginator);?>
</div>


<script type="text/javascript">
  scriptJquery('.core_main_forum').parent().addClass('active');
</script>
