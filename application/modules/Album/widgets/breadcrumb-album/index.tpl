<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10248 2014-05-30 21:48:38Z andres $
 * @author     Jung
 */
?>
<div class="breadcrumb_wrap">
	<div class="album_breadcrumb">
		<p>
			<?php echo $this->htmlLink(array('route' => 'album_general'), $this->translate("Albums"), array()); ?>

			<?php if($this->album->category_id): ?>
				<?php $category = Engine_Api::_()->getItem('album_category', $this->album->category_id); ?>
				<?php if($category) { ?>
					<?php echo $this->translate('&#187;'); ?>
					<a href="<?php echo $this->url(array('action' => 'browse'), 'album_general', true).'?category_id='.urlencode($category->getIdentity()) ; ?>"><?php echo $this->translate($category->category_name); ?></a>
					<?php if($this->album->subcat_id): ?>
						<?php $subCat = Engine_Api::_()->getItem('album_category', $this->album->subcat_id); ?>
						<?php echo $this->translate('&#187;'); ?>
						<a href="<?php echo $this->url(array('action' => 'browse'), 'album_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) ; ?>"><?php echo $this->translate($subCat->category_name); ?></a>   
					<?php endif; ?>
					<?php if($this->album->subsubcat_id): ?>
						<?php $subSubCat = Engine_Api::_()->getItem('album_category', $this->album->subsubcat_id); ?>
						<?php echo $this->translate('&#187;'); ?>
						<a class="catlabel" href="<?php echo $this->url(array('action' => 'browse'), 'album_general', true).'?category_id='.urlencode($category->category_id) . '&subcat_id='.urlencode($subCat->category_id) .'&subsubcat_id='.urlencode($subSubCat->category_id) ; ?>"><?php echo $this->translate($subSubCat->category_name); ?></a>
					<?php endif; ?>
				<?php } ?>
			<?php endif; ?>
			<?php echo $this->translate('&#187;'); ?> 
			<?php echo $this->translate('%1$s\'s Album: %2$s', $this->album->getOwner()->__toString(), ( '' != trim($this->album->getTitle()) ? $this->translate($this->album->getTitle()) : '<em>' . $this->translate('Untitled') . '</em>')); ?>
		</p>
	</div>
	<?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->album)); ?>
</div>
