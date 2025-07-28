<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9859 2013-02-12 02:06:55Z john $
 * @author     Jung
 */
?>

<?php $uid = md5(time() . rand(1, 1000)) ?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    var uid = '<?php echo $uid ?>';
    var hasTitle = Boolean(scriptJquery('.profile_videos_' + uid).parent().find('h3'));
    
    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('.profile_videos_' + uid).parent();
    scriptJquery('.profile_videos_previous_' + uid).css("display",'<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>');
    scriptJquery('.profile_videos_next_' + uid).css("display",'<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>');

    scriptJquery('.profile_videos_previous_' + uid).off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('.profile_videos_next_' + uid).off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
      en4.core.runonce.add(function() {
        if( !hasTitle ) {
          anchor.find('h3').remove();
        }
      });
    });
    <?php endif; ?>
  });
</script>

<div class="container no-padding video_listing">
  <div class="row grid_listing profile_videos_<?php echo $uid ?>">
    <?php foreach( $this->paginator as $item ): ?>
      <div class="col-lg-4 col-md-6 grid_listing_item">
        <article>
          <div class="grid_listing_item_thumb">
            <?php if( $item->duration ): ?>
              <span class="item_length">
                <?php
                  if( $item->duration >= 3600 ) {
                    $duration = gmdate("H:i:s", $item->duration);
                  } else {
                    $duration = gmdate("i:s", $item->duration);
                  }
                  //$duration = ltrim($duration, '0:');
      //              if( $duration[0] == '0' ) {
      //                $duration= substr($duration, 1);
      //              }
                  echo $duration;
                ?>
              </span>
            <?php endif ?>
            <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.normal')) ?>
          </div>
          <div class="grid_listing_item_info">
            <div class="grid_listing_item_title">
              <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('area-label' => $item->getTitle())); ?>
            </div>
            <div class="grid_listing_item_owner">
              <span><?php echo $this->translate('By') ?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?></span>
              <span><?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?></span>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="profile_paginator">
    <div id="profile_videos_previous" class="paginator_previous profile_videos_previous_<?php echo $uid ?>">
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
        'onclick' => '',
        'class' => 'buttonlink icon_previous'
      )); ?>
    </div>
    <div id="profile_videos_next" class="paginator_next profile_videos_next_<?php echo $uid ?>">
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
        'onclick' => '',
        'class' => 'buttonlink_right icon_next'
      )); ?>
    </div>
  </div>
</div>
