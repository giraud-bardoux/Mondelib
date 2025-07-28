<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesapi/views/scripts/dismiss_message.tpl';?>
<h2 class="page_heading">
  <?php echo $this->translate("SocialEngine REST APIs Plugin") ?>
</h2>
<?php if(is_countable($this->navigation) && engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class="settings sesapi_admin_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<div class="sesapi_waiting_msg_box" style="display:none;">
	<div class="sesapi_waiting_msg_box_cont">
    <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
    <i></i>
  </div>
</div>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesapi.pluginactivated',0)){ ?>
	<script type="application/javascript">
  	scriptJquery('.global_form').submit(function(e){
			scriptJquery('.sesapi_waiting_msg_box').show();
		});
  </script>
<?php } ?>

<script type="application/javascript">
function hideShow(showHide){console.log(showHide);
  if(document.getElementById('sesapi_tip_title-wrapper'))
    document.getElementById('sesapi_tip_title-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_description-wrapper'))
    document.getElementById('sesapi_tip_description-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_iosid-wrapper'))
    document.getElementById('sesapi_tip_iosid-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_buttoninstall-wrapper'))
    document.getElementById('sesapi_tip_buttoninstall-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_daysHidden-wrapper'))
    document.getElementById('sesapi_tip_daysHidden-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_daysReminder-wrapper'))
    document.getElementById('sesapi_tip_daysReminder-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_image-wrapper'))
    document.getElementById('sesapi_tip_image-wrapper').style.display = showHide;
  if(document.getElementById('sesapi_tip_androidid-wrapper'))
    document.getElementById('sesapi_tip_androidid-wrapper').style.display = showHide;
}
<?php 
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if($settings->getSetting('sesapi.tip.enable', 1)){ ?>
  hideShow('block');
<?php }else{ ?>
  hideShow('none');
<?php } ?>
function tipMessage(value){
  if(value == 0){
      hideShow('none');
  }else
      hideShow('block');
}
function openURL(name){
  if(name == "manual"){
    Smoothbox.open('admin/sesapi/settings/manual');
    parent.Smoothbox.close;
    return false;
  }else{
    Smoothbox.open('admin/sesapi/settings/automatic');
    parent.Smoothbox.close;
    return false;
  }  
}
</script>
<style> 
	button[disabled] { 
	  background:#bdbdbd; 
	  border-color:#bdbdbd; 
	  cursor:not-allowed; 
  }
</style>
