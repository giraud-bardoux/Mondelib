<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<div class="user_setting_menu">
  <ul>
    <li class="user_menu_heading"><?php echo $this->translate("Edit My Profile"); ?></li>
    <?php foreach( $this->user_edit_navigation as $link ): ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <a href='<?php echo $link->getHref() ?>' class="buttonlink <?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
          <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
          <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
          <span><?php echo $this->translate($link->getlabel()) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
   <li class="user_menu_heading"> <?php echo $this->translate("Account Settings"); ?><li>
    <?php foreach( $this->navigation as $link ): ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <a href='<?php echo $link->getHref() ?>' class="buttonlink <?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
          <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
          <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
          <span><?php echo $this->translate($link->getlabel()) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<script type="application/javascript">
  	en4.core.runonce.add(function() {
		var htmlElement = scriptJquery("#global_wrapper");
		htmlElement.addClass('user_settings_page');
	});
</script>
