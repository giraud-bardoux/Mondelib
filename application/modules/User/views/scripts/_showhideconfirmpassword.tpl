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
  <i id="confirmtogglePassword" style="display:none;"></i>
</div>
<script>
  var confirmtogglePassword = document.querySelector('#confirmtogglePassword');
  if(document.getElementById('passconf')) {
    var passconf = document.querySelector('#passconf');
  } else if(document.getElementById('passwordConfirm')) {
    var passconf = document.querySelector('#passwordConfirm');
  } else if(document.getElementById('password_confirm')) {
    var passconf = document.querySelector('#password_confirm');
  } else if(document.getElementById('password_conf')) {
    var passconf = document.querySelector('#password_conf');
  }
  
  confirmtogglePassword.addEventListener('click', function (e) {
      // toggle the type attribute
      var type = passconf.getAttribute('type') === 'password' ? 'text' : 'password';
      passconf.setAttribute('type', type);
      // toggle the eye / eye slash icon
      this.classList.toggle('showpassword');
  });
</script>
