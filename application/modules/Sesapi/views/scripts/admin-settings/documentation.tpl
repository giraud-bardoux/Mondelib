<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: documentation.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesapi/views/scripts/dismiss_message.tpl';?>
<h2 class="page_heading">
  <?php echo $this->translate("SocialEngine REST APIs Plugin") ?>
</h2>
<div class="sesapi_nav_btns">
  <a href="<?php echo $this->url(array('module' => 'sesapi', 'controller' => 'settings', 'action' => 'support'),'admin_default',true); ?>" target = "_blank" class="help-btn">Help</a>
</div>
<?php if(is_countable($this->navigation) && engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class="settings sesapi_admin_form">
  <div class='settings'>
    Comming soon
  </div>
</div>
