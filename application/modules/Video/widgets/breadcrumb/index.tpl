<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10248 2014-05-30 21:48:38Z andres $
 * @author     Jung
 */
?>
<div class="breadcrumb_wrap">
  <div class="video_breadcrumb">
    <p>
      <?php if($this->video->getParentItem()): ?>
        <?php echo $this->video->getParentItem()->__toString(); ?>
      <?php else: ?>
        <?php echo $this->htmlLink(array('route' => 'video_general','action' => 'browse'), $this->translate("Videos"), array()); ?>
      <?php endif; ?>
      <?php if($this->video->category_id): ?>
        <?php $category = Engine_Api::_()->getItem('video_category', $this->video->category_id); ?>
        <?php if($category) { ?>
          <?php echo $this->translate('&#187;'); ?>
          <a href="<?php echo $this->url(array('action' => 'browse'), 'video_general', true).'?category_id='.urlencode($category->getIdentity()) ; ?>"><?php echo $this->translate($category->category_name); ?></a>
          <?php if($this->video->subcat_id): ?>
            <?php $subCat = Engine_Api::_()->getItem('video_category', $this->video->subcat_id); ?>
            <?php echo $this->translate('&#187;'); ?>
            <a href="<?php echo $this->url(array('action' => 'browse'), 'video_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) ; ?>"><?php echo $this->translate($subCat->category_name); ?></a>   
          <?php endif; ?>
          <?php if($this->video->subsubcat_id): ?>
            <?php $subSubCat = Engine_Api::_()->getItem('video_category', $this->video->subsubcat_id); ?>
            <?php echo $this->translate('&#187;'); ?>
            <a class="catlabel" href="<?php echo $this->url(array('action' => 'browse'), 'video_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) .'&subsubcat_id='.urlencode($subSubCat->category_id) ; ?>"><?php echo $this->translate($subSubCat->category_name); ?></a>
          <?php endif; ?>
        <?php } ?>
      <?php endif; ?>
      <?php echo $this->translate('&#187;'); ?>
      <?php echo $this->video->getTitle(); ?>
    </p>
  </div>
  <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->video)); ?>
</div>
