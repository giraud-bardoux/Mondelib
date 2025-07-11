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
  <i id="newtogglePassword"  style="display:none;"></i>
</div>
<script>
  var newtogglePassword = document.querySelector('#newtogglePassword');
  if(document.getElementById('password')) {
    var passnew = document.querySelector('#password');
  }
  
  newtogglePassword.addEventListener('click', function (e) {
      // toggle the type attribute
      var type = passnew.getAttribute('type') === 'password' ? 'text' : 'password';
      passnew.setAttribute('type', type);
      // toggle the eye / eye slash icon
      this.classList.toggle('showpassword');
  });
</script>
