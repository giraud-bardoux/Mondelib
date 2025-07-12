<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: redirection.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_redirection')); ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    hideShowLogout('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.logout', 3); ?>');
    hideShowLogin('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.login', 4); ?>');
    hideShowSignup('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.after.signup', 4); ?>');
  });
  
  function hideShowLogout(value) {
    if(value == 1) {
      scriptJquery('#core_logouturl-wrapper').show();
    } else {
      scriptJquery('#core_logouturl-wrapper').hide();
    }
  }
  
  function hideShowLogin(value) {
    if(value == 1) {
      scriptJquery('#core_loginurl-wrapper').show();
    } else {
      scriptJquery('#core_loginurl-wrapper').hide();
    }
  }
  
  function hideShowSignup(value) {
    if(value == 1) {
      scriptJquery('#core_signupurl-wrapper').show();
    } else {
      scriptJquery('#core_signupurl-wrapper').hide();
    }
  }
</script>
