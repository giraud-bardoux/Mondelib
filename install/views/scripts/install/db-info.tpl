<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: db-info.tpl 9747 2012-07-26 02:08:08Z john $
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
    <a href="<?php echo $this->url(array('action' => 'db-info')) ?>">
      <span class="cont_number">
        2
      </span>
    </a>
  </li>
  <li>
    <a href="<?php echo $this->url(array('action' => 'account')) ?>">
      <span class="cont_number">
        3
      </span>
    </a>
  </li>
</ul>
<h1>
  <?php echo $this->translate('Step 2: Setup MySQL Database') ?>
</h1>
<p>
  <?php echo $this->translate('Please be sure that you\'ve already created a MySQL database for SocialEngine. Enter the connection information for this database below. If you don\'t know this information, please contact your hosting provider for assistance. If you do not have a database yet, you can likely use your hosting provider\'s control panel (cPanel, Plesk, etc.) to create one.') ?>
</p>
  <div class="admin_install_form">
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
</script>
