<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: create-slide.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php
      $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js');
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<h2>
  <?php echo $this->translate("Native iOS Mobile App") ?>
</h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class="sesiosapp_search_result">
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'slideshow', 'action' => 'index'), $this->translate("Back to Manage Photo") , array('class'=>'sesiosapp_icon_back buttonlink')); ?>
</div>
<div class='clear'>
  <div class='settings sesiosapp_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<style type="text/css">
.settings div.form-label label.required:after{
	content:" *";
	color:#f00;
}
</style>
