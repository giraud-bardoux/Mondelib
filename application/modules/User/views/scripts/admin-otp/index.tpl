<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_otp', 'childMenuItemName' => 'core_admin_otp_settings')); ?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl.'externals/autocompleter/autocomplete.js'); ?>

<h2 class="page_heading"><?php echo $this->translate('OTP Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class='settings'>
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  
  function hideShow (value) {
    if(value == 1) {
      scriptJquery('#otpsms_login_options-wrapper').show();  
      scriptJquery('#otpsms_test_mobilenumber-wrapper').show();  
      scriptJquery('#otpsms_test_code-wrapper').show();  
    } else {
      scriptJquery('#otpsms_login_options-wrapper').hide();  
      scriptJquery('#otpsms_test_mobilenumber-wrapper').hide();  
      scriptJquery('#otpsms_test_code-wrapper').hide();  
    }
  }
  en4.core.runonce.add(function() {
    hideShow("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.signup.phonenumber', 0); ?>");
  }); 

  en4.core.runonce.add(function() {
    AutocompleterRequestJSON('otpsms_test_mobilenumber', "<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'getusers', 'type' => 'phonenumber'), 'default', true) ?>", function(selecteditem) {
      document.getElementById('otpsms_test_user_id').value = selecteditem.id;
    })
  });

  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_otp').addClass('active');
</script>
