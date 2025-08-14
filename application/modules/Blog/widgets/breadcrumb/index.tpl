<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10248 2014-05-30 21:48:38Z andres $
 * @author     Jung
 */
?>
<div class="breadcrumb_wrap">
  <div class="blog_breadcrumb">
    <p>
      <?php if($this->blog->getParentItem()): ?>
        <?php echo $this->blog->getParentItem()->__toString(); ?>
      <?php else: ?>
        <?php echo $this->htmlLink(array('route' => 'blog_general'), $this->translate("Blogs"), array()); ?>
      <?php endif; ?>
      <?php if($this->blog->category_id): ?>
        <?php $category = Engine_Api::_()->getItem('blog_category', $this->blog->category_id); ?>
        <?php if($category) { ?>
          <?php echo $this->translate('&#187;'); ?>
          <a href="<?php echo $this->url(array('action' => 'index'), 'blog_general', true).'?category_id='.urlencode($category->getIdentity()) ; ?>"><?php echo $this->translate($category->category_name); ?></a>
          <?php if($this->blog->subcat_id): ?>
            <?php $subCat = Engine_Api::_()->getItem('blog_category', $this->blog->subcat_id); ?>
            <?php echo $this->translate('&#187;'); ?>
            <a href="<?php echo $this->url(array('action' => 'index'), 'blog_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) ; ?>"><?php echo $this->translate($subCat->category_name); ?></a>   
          <?php endif; ?>
          <?php if($this->blog->subsubcat_id): ?>
            <?php $subSubCat = Engine_Api::_()->getItem('blog_category', $this->blog->subsubcat_id); ?>
            <?php echo $this->translate('&#187;'); ?>
            <a class="catlabel" href="<?php echo $this->url(array('action' => 'index'), 'blog_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) .'&subsubcat_id='.urlencode($subSubCat->category_id) ; ?>"><?php echo $this->translate($subSubCat->category_name); ?></a>
          <?php endif; ?>
        <?php } ?>
      <?php endif; ?>
      <?php echo $this->translate('&#187;'); ?>
      <?php echo $this->blog->getTitle(); ?>
    </p>
  </div>
  <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->blog)); ?>
</div>