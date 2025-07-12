<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _otpLogin.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php 
$this->headTranslate(array(
  'Back to log in form', 'Code Expired'
));
?>
<div id="otp-wrapper" class="form-wrapper">
  <div id="otp-label" class="form-label"></div>
  <div id="otp-element" class="form-element">
    <span class="font_color_light"><?php echo $this->translate("Or"); ?></span> <a href="javascript:void(0);" class="sendOtpsms" onClick="sendOptsms(this,'<?php echo $this->emailFieldName; ?>')"><?php echo $this->translate("Send OTP"); ?></a>
		<img class="_loadingimg" src="application/modules/Core/externals/images/loading.gif" alt="Loading" style="display:none;">
  </div>
</div>
<script type="application/javascript">

  function sendOptsms(obj,objName) {
  
    var elem = scriptJquery(obj);
    var formObject = elem.closest('form');
    var parentElem = elem.closest('.form-elements');
    var emailObj = parentElem.find('#'+objName+'-wrapper').find('#'+objName+'-element').find('#'+objName);
    var value = emailObj.val();
    if(!value || value == ""){
      emailObj.css('border','1px solid red');
      return;
    }
    var country_code = scriptJquery('#country_code').val();
    scriptJquery(elem).parent().find('img').show();
    emailObj.css('border','');
    var formData = new FormData(formObject[0]);
    formData.append('emailField',value);
    //formData.append('country_code', country_code);
    formData.append('type','login');
    scriptJquery.ajax({
      url:  en4.core.baseUrl+'user/auth/login-otp/',
      type: "POST",
      contentType:false,
      processData: false,
      cache: false,
      data: formData,
      success: function(response) {
        scriptJquery(elem).parent().find('img').hide();
        var data = JSON.parse(response);
        if(data.error == 1) {
          //show error
          var html = '<ul class="form-errors"><li><ul class="errors"><li>'+data.message+'</li></ul></li></ul>';
          parentElem.parent().find('.form-errors').remove();
          scriptJquery(html).insertBefore(parentElem);
        } else {
          //show form
          scriptJquery(formObject).parent().find('.otpsms_form_back').remove();
          scriptJquery(formObject).parent().find('#otpsms_signup_verify').remove();
          scriptJquery(formObject).hide();
          var dataform = "<div class='otpsms_form_back'><a href='javascript:;' class='otpsms_back_form'><i class='fas fa-long-arrow-alt-left'></i><span><?php echo $this->translate("Back to log in form"); ?></span></a></div>"+data.form;
          scriptJquery(dataform).insertBefore(scriptJquery(formObject));
          scriptJquery('#code-element').append('<div class="d-flex justify-content-between mt-2"><div id="otp_timer" class="font_small"></div><a href="javascript:void(0);" type="button" style="display:none;" id="resend_otp" class="font_small font_color_hl" onClick="resendLoginData(this)"><?php echo $this->translate('Resend OTP'); ?></a></div>');
          scriptJquery('#resend').hide();
          scriptJquery('#otp_timer').html(data.timerdata);
          otpsmsTimerData(value);
        }
      }
    });
  }

  AttachEventListerSE('click','.otpsms_back_form',function(e){
    var parentElem = scriptJquery(this).parent().parent();
    parentElem.find('.otpsms_form_back').hide();
    parentElem.find('#otpsms_signup_verify, #otpsms_login_verify').hide();
    scriptJquery('#otpsms_login_verify').remove();
    parentElem.find('.otpsms_login_form, #user_form_login').show();
  });

  var resendHTML;
  function resendLoginData(obj) {
    var elem = scriptJquery(obj);
    if(elem.hasClass('active'))
      return;
    scriptJquery('#resend_otp').html('<i class="fas fa-spinner fa-spin"></i>');
    elem.addClass('active');
    resendHTML = elem.html();
    scriptJquery.ajax({
      dataType: 'json',
      url: "<?php echo $this->url(array('module' => 'user', 'controller' => 'auth', 'action' => 'resend-login-code'), 'default', true); ?>",
      method: 'post',
      data: {
        user_id : scriptJquery(obj).closest('.form-elements').find('#email_data').val(),
        email: scriptJquery(obj).closest('.form-elements').find('#email').val(),
        country_code: scriptJquery(obj).closest('.form-elements').find('#country_code').val(),
        type: 'login',
        format: 'json',
      },
      success: function(responseJSON) {
        elem.removeClass('active');
        scriptJquery(obj).closest('.form-elements').parent().find('.form-errors').remove();
        if (responseJSON.error == 1) {
          scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
          scriptJquery('#resend_otp').hide();
          scriptJquery(obj).closest('.form-elements').parent().find('.form-description').html('');
          //show error
          var html = '<ul class="form-errors"><li><ul class="errors"><li>'+responseJSON.message+'</li></ul></li></ul>';
          scriptJquery(html).insertBefore(scriptJquery(obj).closest('.form-elements'));
        } else {
          scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
          scriptJquery('#resend_otp').hide();
          scriptJquery(obj).closest('.form-elements').parent().find('.form-description').html(responseJSON.description);
          if(scriptJquery('#otp_timer')) {
            scriptJquery('#otp_timer').parent().remove();
            scriptJquery('#otp_timer').remove();
          }
          scriptJquery('#code-element').append('<div class="d-flex justify-content-between mt-2"><div id="otp_timer" class="font_small"></div><a href="javascript:void(0);" type="button" style="display:none;" id="resend_otp" class="font_small font_color_hl" onClick="resendLoginData(this)"><?php echo $this->translate('Resend OTP'); ?></a></div>');
          scriptJquery('#resend').hide();
          scriptJquery('#otp_timer').html(responseJSON.timerdata);
          otpsmsTimerData(scriptJquery(obj).closest('.form-elements').find('#email').val());
        }
        elem.html(resendHTML);
        scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
      }
    });
  }
</script>
