<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: footer.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Harmony/views/scripts/_adminHeader.tpl';?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_harmony').parent().addClass('active');
  
  en4.core.runonce.add(function() {
    showFooterLogo("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('harmony.footer.enablelogo', 1); ?>");
  });
  
  function showFooterLogo(value) {
    if(value == 1) {
      scriptJquery('#harmony_footer_logo-wrapper').show();
      scriptJquery('#harmony_footer_logocontrast-wrapper').show();
    } else {
      scriptJquery('#harmony_footer_logo-wrapper').hide();
      scriptJquery('#harmony_footer_logocontrast-wrapper').hide();
    }
  }
</script>
