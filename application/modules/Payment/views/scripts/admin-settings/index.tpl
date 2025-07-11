<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_payment', 'childMenuItemName' => 'core_admin_main_payment_settings')); ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    autoUpdateCurrency('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting("payment.autoupdate",0); ?>');
  });
  
  function autoUpdateCurrency(value) { 
    if(value == 1) { 
      scriptJquery('#currencyapikey-wrapper').show();
    } else {
      scriptJquery('#currencyapikey-wrapper').hide();
    }
  }
  
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_payment').addClass('active');
</script>
<h2 class="page_heading">
  <?php echo $this->translate("Billing Settings") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
