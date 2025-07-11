<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _generatePassword.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<div id="generatepassword-wrapper" class="form-wrapper">
  <div id="generatepassword-label" class="form-label">
    <label for="generatepassword"><?php echo $this->translate("Password"); ?></label>
  </div>
  <div id="generatepassword-element" class="form-element">
    <a class="generatepassword_btn" href="javascript:void(0);" id="generatePassword"><?php echo $this->translate("Generate Password"); ?></a>
  </div>
</div>
<script>
  function generatePassword(length) {
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*-=?";
    let password = "";
    
    // Ensure at least one uppercase, one lowercase, one number, and one special character
    password += getRandomChar("ABCDEFGHIJKLMNOPQRSTUVWXYZ"); // Uppercase
    password += getRandomChar("abcdefghijklmnopqrstuvwxyz"); // Lowercase
    password += getRandomChar("0123456789"); // Number
    password += getRandomChar("!@#$%^&*-=?"); // Special character
    
    // Fill the rest of the password
    for (let i = 6; i < length; i++) {
      const randomIndex = Math.floor(Math.random() * charset.length);
      password += charset.charAt(randomIndex);
    }
    
    //Shuffle the password to randomize the order
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    return password;
  }

  function getRandomChar(charset) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    return charset.charAt(randomIndex);
  }
  
  en4.core.runonce.add(function() {
    scriptJquery('#password_settings_group-wrapper').hide();
    scriptJquery('#passwordroutine-wrapper').hide();
    scriptJquery('#generatePassword').click(function() {
      const minLength = 10;
      const maxLength = 20;
      const passwordLength = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
      const generatedPassword = generatePassword(passwordLength);
      scriptJquery('#signup_password').val(generatedPassword);
      scriptJquery('#password_settings_group-wrapper').show();
      scriptJquery('#passwordroutine-wrapper').show();
      scriptJquery('#generatepassword-wrapper').hide();
      scriptJquery('#togglePassword').addClass('showpassword');
      scriptJquery('#signup_password').attr('type', 'text');
      //passwordRoutine(generatedPassword);
    });
  });
</script>
