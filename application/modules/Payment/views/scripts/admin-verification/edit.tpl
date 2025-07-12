<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_verification', 'childMenuItemName' => 'core_admin_main_settings_verification')); ?>

<h2 class="page_heading"><?php echo $this->translate('Manage Verifications') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="settings package_create_form">
  <?php echo $this->form->render($this) ?>
</div>

<a class="smoothbox" id="verfication_popup" href="<?php echo $this->url(array('action' => 'autoverify-existing-members', 'controller' => 'settings', 'module' => 'core', 'level_id' => $this->level_id), 'admin_default', true); ?>"></a>
<script type="text/javascript">

  var fetchLevelSettings =function(level_id){
    window.location.href= en4.core.baseUrl+'admin/payment/verification/edit/level_id/'+level_id;
  }

  AttachEventListerSE('click','#verfication_popup', function(e){
    e.preventDefault();
  });
  
  scriptJquery("ready").ready(function() {
    verifiedCheck('<?php echo $this->package->verified; ?>');
    
    <?php if($this->popup) { ?>
      setTimeout(function() {
        scriptJquery('#verfication_popup')[0].click();
      }, 100);
    <?php } ?>
  });
  
  function verifiedCheck(value) {
    if(value == 4) {
      scriptJquery('#price-wrapper').show();
      scriptJquery('#recurrence-wrapper').show();
      scriptJquery('#gateway_error-wrapper').show();
    } else { 
      scriptJquery('#price-wrapper').hide();
      scriptJquery('#recurrence-wrapper').hide();
      scriptJquery('#gateway_error-wrapper').hide();
    }
  }

  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_verification').addClass('active');
</script>
