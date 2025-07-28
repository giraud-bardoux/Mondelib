<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: settings.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesandroidapp/views/scripts/dismiss_message.tpl';?>
<h2 class="page_heading">
  <?php echo $this->translate('Native Android Mobile App'); ?>
</h2>
<?php if( engine_count($this->navigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?> </div>
<?php endif; ?>
<?php if(is_countable($this->subnavigation) &&  engine_count($this->subnavigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render(); ?> </div>
<?php endif; ?>
<div class="settings sesandroidapp_admin_form sesandroidapp_notification">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
