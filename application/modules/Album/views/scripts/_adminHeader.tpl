<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _admin_header.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenuItemName' => 'core_admin_main_plugins', 'childMenuItemName' => 'core_admin_main_plugins_album')); ?>

<h2 class="page_heading">
  <?php echo $this->translate('Photo Albums Plugin') ?>
</h2>
<?php $flushData = Engine_Api::_()->album()->getFlushPhotoData(); ?>
<?php if($flushData >0){ ?>
  <div class="unmapped_warning">
    You have <span class="_num"><?php echo $flushData; ?></span> unmapped photos. <?php echo $this->htmlLink(array('module' => 'album', 'controller' => 'settings', 'action' => 'flush-photo'), $this->translate('Click here'), array('class' => 'smoothbox icon_photos_delete')); ?> to remove them.
  </div>
  <br />
<?php } ?>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
