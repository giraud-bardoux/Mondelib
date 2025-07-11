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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'childMenuItemName' => 'core_admin_main_membership_settings')); ?>

<?php $enablefooter = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.footer.enable', 1);?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js'); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>

<script type="text/javascript">
  hashSign = '#';
  
  en4.core.runonce.add(function() {
    showFooterNote('<?php echo $enablefooter;?>');
  });
  
  function showFooterNote(value) {
    if(value == 1)
      scriptJquery('#footer_note-wrapper').show();
    else
      scriptJquery('#footer_note-wrapper').hide();
  }
  
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');
</script>
