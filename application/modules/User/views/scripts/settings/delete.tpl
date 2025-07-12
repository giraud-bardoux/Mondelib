<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 10003 2013-03-26 22:48:26Z john $
 * @author     Steve
 */
?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
			<?php if( $this->isSuperAdmin ):?>
				<div class="tip">
					<span>
						<?php echo $this->translate('Super Admins can\'t be deleted.'); ?>
					</span>
				</div>
			<?php return; endif; ?>
			<div class="user_setting_global_form user_delete_page">
				<?php echo $this->form->setAttrib('id', 'user_form_settings_delete')->render($this) ?>
			</div>
    </div>
  </div>
</div>
<style>
  #user_form_settings_delete #submit_signup, #submit_signup-wrapper{
    display: none;
  }
</style>
<script type="text/javascript">

  en4.core.runonce.add(function() {
    scriptJquery(scriptJquery('#signup_pop_wrap').html()).appendTo('#append-script-data');
    scriptJquery('#signup_pop_wrap').remove();
  });
  
  var formCheck = false;
  AttachEventListerSE('submit', '#user_form_settings_delete', function(e) {
    if(!formCheck) {
      e.preventDefault();
      sendEmailCode();
      scriptJquery('#user_signup_email_btn').trigger('click');
      return false;
    } else {
      return true;
    }
  });

  function sendEmailCode() {
    var email = '<?php echo !empty($this->user->email) ? $this->user->email : $this->user->phone_number; ?>';
    var country_code = '<?php echo !empty($this->user->country_code) ? $this->user->country_code : ""; ?>';
    if(!email.match(/^\d+$/)) {
      scriptJquery('#otp_timer').hide();
    }
    var url = en4.core.baseUrl + 'core/otp/sendotp';
    (scriptJquery.ajax({
      url : url,
      dataType: 'json',
      data : {
        format : 'json',
        email : email,
        country_code : country_code,
        type: 'deleteaccount',
        user_id: '<?php echo $this->user->getIdentity(); ?>',
      },
      success : function(response) {
        if(response.status) {
          if(country_code) {
            otpsmsTimerData(email);
          }
          scriptJquery('#user_signup_email').show();
          scriptJquery('#send_signup_form').show();
          scriptJquery("#core_loading_cont_overlay").hide();
          scriptJquery('#validEmail').val(email);
        }
      }
    }));
  }
  
  function validateTwoStepCode() {
  
    var code = scriptJquery('#otp_code').val();
    if(code === '') {
      scriptJquery('#error_message').show().html("<span><?php echo $this->string()->escapeJavascript($this->translate('The security code you have entered is not correct. Please check your code and try again.')); ?></span>");
      return;
    }
    var email = '<?php echo !empty($this->user->email) ? $this->user->email : $this->user->phone_number; ?>';
    scriptJquery("#core_loading_cont_overlayf").show();
    var url = en4.core.baseUrl + 'core/otp/validateotp';
    (scriptJquery.ajax({
      url : url,
      dataType: 'json',
      data : {
        format : 'json',
        email : email,
        code: code,
        type: 'deleteaccount',
      },
      success : function(response) {
        scriptJquery("#core_loading_cont_overlayf").hide();
        if(response.status) {
          document.getElementById('send_signup_form').innerHTML = "<div class='success_msg success_msg m-2''><span>"+response.message+"</span></div>";
          formCheck = true;
          scriptJquery('#cancel_verify_otp').trigger('click');
          closeVerifyPopup();
          scriptJquery('#submit_signup').trigger('click');
        } else {
          scriptJquery('#error_message').show().html(response.message);
        }
      }
    }));
  }
  
  function closeVerifyPopup() {
    scriptJquery('#user_signup_email').hide();
    scriptJquery('#send_signup_form').hide();
  }
  
  AttachEventListerSE('click', '#verify_otp', function(e){
    validateTwoStepCode();
  });
  
  AttachEventListerSE('click', '#resend_otp', function(e){
    resendOtpCode();
  });
  
  function resendOtpCode() {
    if(scriptJquery('#resend_otp').hasClass('active'))
      return;
    var email = '<?php echo !empty($this->user->email) ? $this->user->email : $this->user->phone_number; ?>';
    var country_code = '<?php echo !empty($this->user->country_code) ? $this->user->country_code : ""; ?>';
    scriptJquery('#resend_otp').html('<i class="fas fa-spinner fa-spin"></i>');
    scriptJquery('#resend_otp').addClass('active');
    scriptJquery.ajax({
      dataType: 'json',
      url: en4.core.baseUrl + 'core/otp/sendotp',
      method: 'post',
      data: {
        format: 'json',
        email: email,
        country_code: country_code,
        type: 'deleteaccount',
        user_id: '<?php echo $this->user->getIdentity(); ?>',
      },
      success: function(responseJSON) {
        scriptJquery('#resend_otp').removeClass('active');
        if (responseJSON.status) {
          scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
          scriptJquery('#resend_otp').hide();
          scriptJquery('#otp_timer').html(responseJSON.timerdata);
        } else if(responseJSON.error == 1 && responseJSON.message) {
          scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
          scriptJquery('#resend_otp').hide();
          scriptJquery('#otp_timer').html(responseJSON.message);
        }
      }
    });
  }
</script>
<?php echo $this->partial('_otpPopUp.tpl', 'user', array()); ?>
