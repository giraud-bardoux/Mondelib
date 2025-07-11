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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'childMenuItemName' => 'core_admin_main_payment_packages')); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
</h2>	
<?php if( count($this->navigation) ): ?>
<div class='tabs'>
    <?php
    // Render the menu
    //->setUlClass()
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
</div>
<?php endif; ?>
<div class="settings package_create_form">
  <?php echo $this->form->render($this) ?>
</div>
<script>

  scriptJquery(window).ready(function() {
    sendReminder('<?php echo $this->package->send_reminder; ?>');
  });

  function sendReminder(value) { 
    if(value == 1) { 
      scriptJquery('#reminder_email-wrapper').show();
    } else {
      scriptJquery('#reminder_email-wrapper').hide();
    }
  }
  if(document.getElementById('reminder_email-select'))
  document.getElementById('reminder_email-select').style.display = "none";
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');
</script>
