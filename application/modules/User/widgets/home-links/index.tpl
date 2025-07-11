<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if( $this->showPhoto == 1): ?>
<div class="home-links-user">
    <div class="image">
        <?php echo $this->htmlLink($this->viewer()->getHref(), $this->itemBackgroundPhoto($this->viewer(), 'thumb.icon')) ?>
    </div>
    <div class="user member_name">
        <?php echo $this->htmlLink($this->viewer()->getHref(), $this->viewer()->getTitle()); ?>
    </div>
</div>
<?php endif; ?>

<div class="quicklinks">
	<ul>
		<?php foreach( $this->navigation as $link ): ?>
			<li>
				<?php if(!empty($this->showMenuIcon)) { ?>
					<?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), array('class' => 'buttonlink' . ( $link->getClass() ? ' ' . $link->getClass() : '' ) . ' ' . (!empty($link->get('icon')) ? $link->get('icon') : ''),'target' => $link->get('target'))) ?>
				<?php } else { ?>
					<?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), array('target' => $link->get('target'))) ?>
				<?php } ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
