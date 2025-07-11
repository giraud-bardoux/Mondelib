<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: account.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="settings">
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">

  en4.core.runonce.add(function() {
    showHideEmail("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.adminemail', 0); ?>");
    showUserName("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1); ?>");
  });
  
  function showHideEmail(value) {
    if(value == 1) {
      scriptJquery('#adminemailaddress-wrapper').show();
    } else {
      scriptJquery('#adminemailaddress-wrapper').hide();
    }
  }
  
  function showUserName(value) {
    if(value == 1) {
      scriptJquery('#showusername-wrapper').show();
    } else {
      scriptJquery('#showusername-wrapper').hide();
    }
  }

  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_signup').addClass('active');
</script>
