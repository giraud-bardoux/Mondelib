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
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class='container no-padding blogs_listing'>
    <div class='row grid_listing'>
      <?php foreach( $this->paginator as $item ): ?>
        <div class='col-lg-4 col-md-6 grid_listing_item'>
          <article>
            <div class='grid_listing_item_thumb'>
              <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.profile')) ?>
            </div>
            <div class='grid_listing_item_info'>
              <div class='grid_listing_item_title'>
                <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
              </div>
              <div class='grid_listing_item_owner'>
                <span><?php echo $this->translate('By');?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?></span>
                <span><?php echo $this->timestamp(strtotime($item->creation_date)) ?></span>
              </div>
              <p class='grid_listing_item_desc'>
                <?php $readMore = ' ' . $this->translate('Read More') . '...';?>
                <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 110, $this->htmlLink($item->getHref(), $readMore) ) ?>
              </p>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php elseif( $this->category || $this->show == 2 || $this->search ): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No one has written a blog entry with that criteria.');?></p>
    <?php if (TRUE): // @todo check if user is allowed to create a poll ?>
      <p><?php echo $this->translate('Be the first to %1$swrite%2$s one!', '<a href="'.$this->url(array('action' => 'create'), 'blog_general').'">', '</a>'); ?></p>
    <?php endif; ?>
  </div>
<?php else:?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No one has written a blog entry yet.'); ?></p>
    <?php if( $this->canCreate ): ?>
      <p><?php echo $this->translate('Be the first to %1$swrite%2$s one!', '<a href="'.$this->url(array('action' => 'create'), 'blog_general').'">', '</a>'); ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php echo $this->paginationControl($this->paginator, null, null, array(
'pageAsQuery' => true,
'query' => $this->formValues,
//'params' => $this->formValues,
)); ?>
