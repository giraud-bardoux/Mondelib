<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _approved_tip.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php
  $item = $this->item;
  $viewer = Engine_Api::_()->user()->getViewer();
  
  $isExists = Engine_Api::_()->getDbTable('tickets', 'core')->isExists(array('resource_type' => $item->getType(), 'resource_id' => $item->getIdentity()));
?>
<?php if(!$item->approved && ($item->isOwner($viewer) || $viewer->isAdmin())) { ?>
  <div class="tip content_approval_msg">
    <span>
      <?php if(!empty($item->resubmit) && $item->resubmit == 1) { ?>
        <?php echo $this->translate("This content is disapproved by our site’s admin, which means that it can be viewed by you and our site's administrator."); ?>
        <a href="<?php echo $this->url(array('action' => "resubmit", 'resource_id' => $item->getIdentity(), 'resource_type' => $item->gettype()), 'core_tickets', true); ?>" class="smoothbox"><?php echo $this->translate("Resubmit Again"); ?></a>
      <?php } else if($item->resubmit == 0) { ?>
        <?php echo $this->translate("This content is currently waiting for admin approval, which means that it can be viewed by you and our site's administrator."); ?>
      <?php } ?>
      <?php if(!empty($item->resubmit) && $item->resubmit == 2) { ?>
        <?php echo $this->translate("This content is re-submitted for admin approval, which means that it can be viewed by you and our site's administrator."); ?>
      <?php } ?>
    <span>
  </div>
<?php } ?>
