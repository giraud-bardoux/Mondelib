<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: read-message.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<div class="global_form_popup admin_member_stats">
  <h3><?php echo $this->translate('Message') ?></h3>
  <p><?php echo $this->item->body; ?></p>
  <br/>
  <button type="submit" onclick="parent.Smoothbox.close();return false;" name="close_button" value="Close"><?php echo $this->translate("Close"); ?></button>
</div>
