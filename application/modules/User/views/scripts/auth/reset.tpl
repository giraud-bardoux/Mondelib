<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: reset.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if( empty($this->reset) ): ?>
  <div class="layout_middle">
    <div class="generic_layout_container layout_core_content">
      <?php echo $this->form->render($this) ?>
    </div>
  </div>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      scriptJquery("body").addClass('authpage');
      scriptJquery('#password-element').append('<div id="passwordroutine" class="password_checker"><div id="passwordroutine_length"></div><div class="d-flex justify-content-between align-content-center"><div id="passwordroutine_text" class="font_small"><?php echo $this->translate("Enter your password.")?></div><div id="password-hint"><i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $this->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.'); ?>" data-bs-original-title="" title=""></i></div></div></div>');
    });
    
  </script>
<?php else: ?>
  <div class="layout_middle core_reset_signin_link">
    <div class="generic_layout_container layout_core_content">
      <div class="auth_page_icon">
        <svg width="800px" height="800px" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg"><circle cx="512" cy="512" r="512" style="fill:#2491eb;"></circle><path d="m458.15 617.7 18.8-107.3a56.94 56.94 0 0 1 35.2-101.9V289.4h-145.2a56.33 56.33 0 0 0-56.3 56.3v275.8a33.94 33.94 0 0 0 3.4 15c12.2 24.6 60.2 103.7 197.9 164.5V622.1a313.29 313.29 0 0 1-53.8-4.4zM656.85 289h-144.9v119.1a56.86 56.86 0 0 1 35.7 101.4l18.8 107.8A320.58 320.58 0 0 1 512 622v178.6c137.5-60.5 185.7-139.9 197.9-164.5a33.94 33.94 0 0 0 3.4-15V345.5a56 56 0 0 0-16.4-40 56.76 56.76 0 0 0-40.05-16.5z" style="fill:#fff"></path></svg>
      </div>
      <div class="tip">
        <span>
          <?php echo $this->translate("Your password has been reset. Click %s to sign-in.", $this->htmlLink(array('route' => 'user_login'), $this->translate('here'))) ?>
        </span>
      </div>
    </div>
 </div> 
<?php endif; ?>

<script type="text/javascript">
  if(typeof loginSignupPlaceHolderActive != 'undefined') {
    en4.core.runonce.add(function() {
      scriptJquery("body").addClass('authpage');
      scriptJquery('#email-label').hide();
      scriptJquery('#password-label').hide();
      scriptJquery('#password_confirm-label').hide();
      scriptJquery('#email').attr('placeholder',scriptJquery('#email-label').find('label').html());
      scriptJquery('#password').attr('placeholder',scriptJquery('#password-label').find('label').html());
      scriptJquery('#password_confirm').attr('placeholder',scriptJquery('#password_confirm-label').find('label').html());
    });
  }
  
</script>
