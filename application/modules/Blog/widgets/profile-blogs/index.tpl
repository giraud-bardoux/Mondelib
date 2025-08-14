<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>

<div class='container no-padding blogs_listing'>
  <div class='row grid_listing'>
    <?php foreach( $this->paginator as $item ): ?>
      <div class='col-lg-4 col-md-6 grid_listing_item'>
        <article>
          <div class='grid_listing_item_thumb'>
            <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.normal')) ?>
          </div>
          <div class='grid_listing_item_info'>
            <div class='grid_listing_item_title'>
              <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?><?php if(!empty($item->draft)) { ?><i class="icon_draft" data-bs-toggle="tooltip" title="<?php echo $this->translate("Draft")?>"></i><?php } ?>
            </div>
            <div class='grid_listing_item_owner'>
              <span><?php echo $this->translate('Posted');?> <?php echo $this->timestamp($item->creation_date) ?></span>
            </div>
            <div class='grid_listing_item_desc'>
              <?php echo $this->string()->truncate($this->string()->stripTags($item->body),110) ?>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
  <?php
    // show view all link even if all are listed
    if( $this->paginator->count() > 0 ):
  ?>
    <div class="profile_paginator">
      <div class="paginator_next ">
        <?php echo $this->htmlLink($this->url(array('user_id' => Engine_Api::_()->core()->getSubject()->getIdentity()), 'blog_view'), $this->translate('View All Entries'), array('class' => 'buttonlink_right icon_next')) ?>
      </div>
    </div>
  <?php endif;?>
</div>


