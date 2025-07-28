<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
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
<div class="settings sesiosapp_admin_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<div class="sesiosapp_waiting_msg_box" style="display:none;">
	<div class="sesiosapp_waiting_msg_box_cont">
    <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
    <i></i>
  </div>
</div>
<script type="application/javascript">

scriptJquery('.loading_img').click(function(e){
   en4.core.showError('<div class="sesact_img_preview_popup"><div class="sesact_img_preview_popup_img"><img src="application/modules/Sesiosapp/externals/images/admin/loading_admin.gif"> </div><div class="sesact_img_preview_popup_btm"><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button></div></div>');
		scriptJquery ('.sesact_img_preview_popup').parent().parent().addClass('sesact_img_preview_popup_wrapper');  
})
</script>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesiosapp.pluginactivated',0)){  ?>

	<script type="application/javascript">
  	scriptJquery('.global_form').submit(function(e){
			scriptJquery('.sesiosapp_waiting_msg_box').show();
		});
  </script>
<?php } ?>
<style> 
	button[disabled] { 
	  background:#bdbdbd; 
	  border-color:#bdbdbd; 
	  cursor:not-allowed; 
  }
</style>