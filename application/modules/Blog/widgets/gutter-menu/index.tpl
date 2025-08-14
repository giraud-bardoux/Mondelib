<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="quicklinks">
  <ul class="navigation blogs_gutter_options">
    <?php foreach( $this->gutterNavigation as $link ): ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), array('class' => 'buttonlink' . ( $link->getClass() ? ' ' . $link->getClass() : '' ) . ' ' . (!empty($link->get('icon')) ? $link->get('icon') : ''),'target' => $link->get('target'))) ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
