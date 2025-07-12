<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcements
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>

<ul class="announcements">
  <?php foreach( $this->announcements as $item ): ?>
		<li>
			<div class="announcements_title">
				<?php echo $this->translate($item->title); ?>
			</div>
			<div class="announcements_info">
				<span class="announcements_author">
					<?php echo $this->translate('Posted by %1$s %2$s', $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()), $this->timestamp($item->creation_date)) ?>
				</span>
			</div>
			<div class="announcements_body rich_content_body">
				<?php $column = @$_COOKIE['en4_language'] . '_body'; ?>
				<?php if(isset($item->$column) && !empty($item->$column)) { ?>
						<?php echo $this->translate(Engine_Api::_()->core()->smileyToEmoticons($item->$column)); ?>
				<?php } else { ?>
					<?php echo $this->translate(Engine_Api::_()->core()->smileyToEmoticons($item->body)); ?>
				<?php } ?>
			</div>
		</li>
  <?php endforeach; ?>
</ul>

<script type="text/javascript">
  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
