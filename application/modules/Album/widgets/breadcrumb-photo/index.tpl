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
  <div class="photo_breadcrumb">
    <p>
      <?php if(!$this->message_view && !$this->isprivate): ?>
        <?php echo $this->translate('%1$s\'s Album: %2$s', $this->album->getOwner()->__toString(), $this->htmlLink($this->album, $this->album->getTitle())); ?>
      <?php else: ?>
        <?php echo $this->translate('%1$s\'s Album: %2$s', $this->album->getOwner()->__toString(), $this->album->getTitle()); ?>
      <?php endif; ?>
    </p>
  </div>
  <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->photo)); ?>
</div>
