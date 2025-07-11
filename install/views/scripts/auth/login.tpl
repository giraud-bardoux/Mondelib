<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: login.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="topbar topbar_login">
  <div class='logo'>
    <img src="externals/images/logo.svg" alt="" />
  </div>
</div>  
<div class="admin_install_login_form">
  <h1>
  <?php echo $this->translate('Login Admin Panel') ?>
</h1>
  <?php echo $this->form->render($this) ?>
  <div class="admin_install_login_back">
    <a href="<?php echo _ENGINE_SITE_URL; ?>"> 
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
      </svg>
      <?php echo $this->translate("Back to %s", $this->sitetitle->value); ?>
    </a>
  </div>
</div>
<script>
  var togglePassword = document.querySelector('#togglePassword');
  var password = document.querySelector('#password');

  togglePassword.addEventListener('click', function (e) {
      // toggle the type attribute
      var type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      // toggle the eye / eye slash icon
      this.classList.toggle('fa-eye-slash');
  });
</script>
