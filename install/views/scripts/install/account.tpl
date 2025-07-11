<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: account.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<ul class="intsall_admin_step">
  <li class="active">
    <a href="<?php echo $this->url(array('action' => 'sanity')) ?>">
      <span class="cont_number">
        1
      </span>
    </a>
  </li>
  <li class="active">
    <a href="<?php echo $this->url(array('action' => 'db-info'), '', true) ?>?clear=1';">
      <span class="cont_number">
        2
      </span>
    </a>
  </li>
  <li class="active">
    <a href="<?php echo $this->url(array('action' => 'account')) ?>">
      <span class="cont_number">
        3
      </span>
    </a>
  </li>
</ul>
<div class="install_heding_description">
  <h1>
    <?php echo $this->translate('Step 3: Create Admin Account') ?>
  </h1>
  <p>
    <?php echo $this->translate('Now that you\'ve setup SocialEngine, let\'s get started by naming your community and creating an administrator account. Please provide your email address and choose a password. You will use this information to sign in to your control panel and manage your social network.') ?>
  </p>
</div>  
<?php if( !empty($this->form) ): ?>
  <div class="create-admin-form">
    <?php echo $this->form->render($this) ?>
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

    scriptJquery(document).on('keyup', '#password_conf', function(e) {
      var passwordconf = scriptJquery(this).val();
      
      if(passwordconf && scriptJquery('#confirmtogglePassword'))
        scriptJquery('#confirmtogglePassword').show();
      if(passwordconf == '')
        scriptJquery('#confirmtogglePassword').hide();
    });

    var confirmtogglePassword = document.querySelector('#confirmtogglePassword');
    var password_conf = document.querySelector('#password_conf');
    
    confirmtogglePassword.addEventListener('click', function (e) {
        // toggle the type attribute
        var type = password_conf.getAttribute('type') === 'password' ? 'text' : 'password';
        password_conf.setAttribute('type', type);
        // toggle the eye / eye slash icon
        this.classList.toggle('fa-eye-slash');
    });
  </script>
<?php endif; ?>
