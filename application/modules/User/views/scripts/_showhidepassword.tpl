<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _formSignupImage.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<div class="user_showhidepassword">
  <i id="togglePassword" style="display:none;"></i>
</div>
<script>
  var togglePassword = document.querySelector('#togglePassword');
  if(document.getElementById('password')) {
    var password = document.querySelector('#password');
  } else if(document.getElementById('oldPassword')) {
    var password = document.querySelector('#oldPassword');
  } else {
    var password = document.querySelector('#signup_password');
  }

  togglePassword.addEventListener('click', function (e) {
      // toggle the type attribute
      var type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      // toggle the eye / eye slash icon
      this.classList.toggle('showpassword');
  });
</script>
