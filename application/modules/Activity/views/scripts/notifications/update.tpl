<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: update.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<div id='new_notification'>
  <span>
    <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'notifications'),
                               $this->translate(array('%s update', '%s updates', $this->notificationCount), $this->locale()->toNumber($this->notificationCount)),
                               array('id' => 'core_menu_mini_menu_updates_count')) ?>
  </span>
  <span id="core_menu_mini_menu_updates_close">
    <a href="javascript:void(0);" onclick="en4.activity.hideNotifications();">x</a>
  </span>
</div>
