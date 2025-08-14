<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: list.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Jung
 */
?>

<h2><?php echo $this->translate('Recent Entries')?></h2>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <ul class='manage_listing blogs_manage'>
    <?php foreach ($this->paginator as $item): ?>
      <li class="manage_listing_item">
        <article>
          <div class='manage_listing_thumb'>
            <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.profile')) ?>
          </div>
          <div class="manage_listing_info">
            <div class="manage_listing_header">
              <div class='manage_listing_title'>
                <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
              </div>
            </div>
            <div class="manage_listing_owner">
              <span]><?php echo $this->translate('by');?> <?php echo $this->htmlLink($item->getParent(), $item->getParent()->getTitle()) ?></span>
              <span><?php echo $this->timestamp($item->creation_date) ?></span>
            </div>
            <div class="manage_listing_desc">
              <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
            </div>
          </div>
        </article>
      </li>
    <?php endforeach; ?>
  </ul>
<?php elseif( $this->category || $this->tag ): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
      <p><?php echo $this->translate('%1$s has not published a blog entry with that criteria.', $this->owner->getTitle()); ?></p>
  </div>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('%1$s has not written a blog entry yet.', $this->owner->getTitle()); ?></p>
  </div>
<?php endif; ?>

<?php echo $this->paginationControl($this->paginator, null, null, array(
  'pageAsQuery' => true,
  'query' => $this->formValues,
  //'params' => $this->formValues,
)); ?>


<script type="text/javascript">
  scriptJquery('.core_main_blog').parent().addClass('active');
</script>
