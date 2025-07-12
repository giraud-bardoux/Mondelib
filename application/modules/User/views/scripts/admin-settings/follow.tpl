<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: follow.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_settings_friends', 'childMenuItemName' => 'core_admin_main_follow')); ?>

<h2 class="page_heading"><?php echo $this->translate('Friendship / Follow') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class='settings'>
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">

	function enableFollow(value){
		if(value == 1) {
      document.getElementById('core_autofollow-wrapper').style.display = 'flex';
      document.getElementById('core_allowuserverfication-wrapper').style.display = 'flex';	
    } else {
      document.getElementById('core_autofollow-wrapper').style.display = 'none';	
      document.getElementById('core_allowuserverfication-wrapper').style.display = 'none';	
    }
	}
	
	function allowuser(value){
		if(value == 0) {
      document.getElementById('core_autofollow-wrapper').style.display = 'flex';	
    } else{
      document.getElementById('core_autofollow-wrapper').style.display = 'none';	
    }
	}

  en4.core.runonce.add(function() {
    if(document.getElementById('core_followenable'))
      enableFollow(document.getElementById('core_followenable').value);
    
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.followenable', 1) != 0) { ?>
    if(document.getElementById('core_allowuserverfication'))
      allowuser(document.getElementById('core_allowuserverfication').value);
    <?php } ?>
  });

  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_friends').addClass('active');
</script>
