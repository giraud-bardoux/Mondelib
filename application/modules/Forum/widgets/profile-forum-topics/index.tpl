<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Char
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('#forum_topics').getParent();
    scriptJquery('#forum_topics_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    scriptJquery('#forum_topics_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    scriptJquery('#forum_topics_previous').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('#forum_topics_next').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>

<ul class="discussions_listing" id="forum_topics">
  <?php foreach( $this->paginator as $topic ):
    $last_post = $topic->getLastCreatedPost();
    if( $last_post ) {
      $last_user = $this->user($last_post->user_id);
    } else {
      $last_user = $this->user($topic->user_id);
    }
  ?>
    <li class="discussions_listing_item">
      <div class="discussions_listing_item_thumb">
        <?php if( $topic->isViewed($this->viewer()) ): ?>
          <?php // echo $this->htmlLink($topic->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Forum/externals/images/topic.png')) ?>
        <?php else: ?>
          <?php // echo $this->htmlLink($topic->getHref(), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Forum/externals/images/topic_unread.png')) ?>
        <?php endif; ?>
        <?php echo $this->htmlLink($topic->getOwner()->getHref(), $this->itemBackgroundPhoto($topic->getOwner(), 'thumb.icon')) ?>
      </div>
      <div class="discussions_listing_item_content d-flex flex-wrap">
        <div class="discussions_listing_item_info">
          <h3><?php if( $topic->closed ): ?> <i>❌</i><?php endif; ?><?php if( $topic->sticky ): ?><i>📌</i><?php endif; ?><?php echo $this->htmlLink($topic->getHref(), $topic->getTitle(), array("class" => "font_color"));?></h3>
          <div class="discussions_listing_item_stats font_small font_color_light">
            <span><?php echo $this->translate(array('%1$s view', '%1$s views', $topic->view_count), $this->locale()->toNumber($topic->view_count)) ?></span>
            <span><?php $post_count = $topic->postCount() > 1 ? $topic->postCount() - 1 : 0; ?><?php echo $this->translate(array('%1$s reply', '%1$s replies', $post_count), $this->locale()->toNumber($post_count)) ?></span>
          </div>
          <?php echo $this->pageLinks($topic, $this->forum_topic_pagelength, null, 'forum_pagelinks') ?>
        </div>

        <div class="discussions_listing_item_lastpost d-flex flex-wrap align-items-center">
          <?php echo $this->htmlLink($last_post->getHref(), $this->itemBackgroundPhoto($last_user, 'thumb.icon')) ?>
          <span class="discussions_listing_item_lastpost_info font_color_light font_small">
            <?php if( $last_post):
              list($openTag, $closeTag) = explode('-----', $this->htmlLink($last_post->getHref(array('slug' => $topic->getSlug())), '-----'));
              ?>
              <?php echo $this->translate(
                '%1$sLast post%2$s by %3$s',
                $openTag,
                $closeTag,
                $this->htmlLink($last_user->getHref(), $last_user->getTitle())
              )?><br>
              <?php echo $this->timestamp($topic->modified_date) ?>
            <?php endif; ?>
          </span>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>


<div class="profile_paginator">
  <div id="forum_topics_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'icon_previous'
    )); ?>
  </div>
  <div id="forum_topics_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'icon_next'
    )); ?>
  </div>
</div>
