<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: forgot.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php 
$settings = Engine_Api::_()->getApi('settings', 'core'); 
$otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);

if(!empty($otpsms_signup_phonenumber)) {
  $getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US'));
  if(!empty($getCountry)) {
    $country = Engine_Api::_()->getItem('core_country', $getCountry);
  }
}
?>
<?php if( empty($this->sent) ): ?>
  <div class="auth_page_icon">
    <svg x="0px" y="0px" width="80px" height="80px" viewBox="0 0 106.079 122.879" enable-background="new 0 0 106.079 122.879" xml:space="preserve"><g><path fill-rule="evenodd" clip-rule="evenodd" d="M43.146,62.341L32.389,81.274l2.012,7.505l7.452-1.997l-1.512-5.642l6.174-1.654 l-1.654-6.175l5.191-0.34l0.217-6.023c3.206,1.086,6.791,1.265,10.349,0.312c9.315-2.496,14.919-11.806,12.512-20.789 c-2.407-8.984-11.915-14.244-21.23-11.749c-9.314,2.496-14.918,11.806-12.51,20.79C40.093,58.141,41.407,60.451,43.146,62.341 L43.146,62.341L43.146,62.341z M63.191,118.729c0.936,0.983,0.896,2.539-0.086,3.474c-0.983,0.936-2.539,0.896-3.475-0.087 l-7.876-8.298c-0.897-0.943-0.897-2.413-0.028-3.357l7.876-8.576c0.919-0.999,2.475-1.063,3.474-0.145s1.063,2.475,0.145,3.474 l-3.315,3.609c15.661-2.799,26.639-10.495,33.299-20.363c4.664-6.911,7.231-14.897,7.822-23.016 c0.593-8.152-0.798-16.427-4.05-23.878c-5.176-11.862-15.08-21.651-29.21-25.526c-1.308-0.356-2.079-1.704-1.723-3.012 c0.355-1.307,1.704-2.079,3.012-1.723C84.753,15.61,95.745,26.46,101.48,39.603c3.573,8.188,5.102,17.262,4.453,26.187 c-0.652,8.957-3.49,17.778-8.649,25.422c-7.653,11.338-20.372,20.068-38.58,22.79L63.191,118.729L63.191,118.729z M43.065,4.15 c-0.936-0.983-0.896-2.539,0.087-3.474c0.982-0.935,2.538-0.896,3.474,0.087l7.876,8.299c0.897,0.943,0.898,2.414,0.028,3.357 l-7.875,8.576c-0.92,0.999-2.476,1.064-3.475,0.145c-0.998-0.919-1.063-2.475-0.145-3.474l3.563-3.879 c-13.063,1.565-23.924,8.677-31.28,18.435c-5.057,6.708-8.457,14.652-9.783,22.898c-1.32,8.217-0.581,16.738,2.635,24.634 c4.656,11.434,14.555,21.591,30.976,27.67c1.275,0.467,1.93,1.881,1.462,3.156c-0.467,1.275-1.881,1.93-3.156,1.463 C19.582,105.427,8.757,94.242,3.609,81.602C0.048,72.856-0.773,63.43,0.686,54.351C2.141,45.3,5.862,36.595,11.392,29.259 c8.384-11.12,20.924-19.129,36.044-20.505L43.065,4.15L43.065,4.15z M57.285,45.128c-1.662,0.446-2.65,2.156-2.204,3.819 c0.445,1.663,2.156,2.65,3.819,2.205s2.65-2.156,2.204-3.819C60.659,45.67,58.949,44.683,57.285,45.128L57.285,45.128z"/></g></svg>
  </div>
  <?php echo $this->form->render($this) ?>
<?php else: ?>
  <div class="auth_forgot_success_tip text-center">
    <div class="auth_page_icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="80px" height="80px" viewBox="0 0 1024 1024"><path fill="#000000" d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zm-55.808 536.384-99.52-99.584a38.4 38.4 0 1 0-54.336 54.336l126.72 126.72a38.272 38.272 0 0 0 54.336 0l262.4-262.464a38.4 38.4 0 1 0-54.272-54.336L456.192 600.384z"/></svg>
    </div>
    <p class="text-center mb-3 pb-3"><?php echo $this->translate("USER_VIEWS_SCRIPTS_AUTH_FORGOT_DESCRIPTION") ?></p>
    <a href="<?php echo $this->url(array(), 'default', true) ?>" class="back_login_button"><?php echo $this->translate("Back to Home") ?></a>
  </div>
  <script>
    setTimeout(function(){
      window.location = '<?php echo $this->url(array('action' => 'reset', 'code' => $this->code, 'uid' => $this->user->getIdentity())); ?>'; 
    },100);
  </script>
<?php endif; ?>

<script type="text/javascript">
  
  en4.core.runonce.add(function() {
    scriptJquery(scriptJquery('#signup_pop_wrap').html()).appendTo('#append-script-data');
    scriptJquery('#signup_pop_wrap').remove();
  });

  var formCheck = false;
  AttachEventListerSE('submit', '#user_form_auth_forgot', function(e) {
    if(!formCheck) {
      e.preventDefault();
      
      // Check if all required fields are filled out
      var formData = new FormData(this);
      //formData.append('formName', 'User_Form_Auth_Forgot');
      scriptJquery('#core_submit_forgot').html('<i class="fas fa-spinner fa-spin"></i>');

      var url = en4.core.baseUrl + 'user/auth/forgot';
      (scriptJquery.ajax({
        url : url,
        type: "POST",
        dataType: 'json',
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success : function(response) {
          scriptJquery('#core_submit_forgot').html('<?php echo $this->translate("Send Code"); ?>');
          if(response.status) {
            if(scriptJquery('#form_errors').length)
              scriptJquery('#form_errors').remove();
            sendEmailCode();
            // show popup
            scriptJquery('#user_signup_email_btn').trigger('click');
          } else {
            if(scriptJquery('#form_errors').length)
              scriptJquery('#form_errors').remove();
            var errors = '<ul class="form-errors" id="form_errors">';
            for (var i = 0; i < response.error_message.length; i++) {
              var error_message = response.error_message[i];
              if(error_message.isRequired) {
                errors += '<li>'+error_message.label+'<ul class="errors"><li>'+error_message.errorMessage+'</li></ul></li>';
              }
            }
            errors += '</ul>';
            scriptJquery('.form-elements').prepend(errors);
            //scriptJquery('html, body').animate({
           //   scrollTop: scriptJquery('#form_errors').offset().top
           // }, 2000);
          }
        }
      }));
      return false;
    } else {
      return true;
    }
  });

  <?php //if( $settings->getSetting('user.signup.enabletwostep', 0) == 1 || !empty($otpsms_signup_phonenumber)) { ?>
    en4.core.runonce.add(function() {
      <?php if(!empty($otpsms_signup_phonenumber)) { ?>
        scriptJquery('#email-element').prepend(scriptJquery('#forgot_country_code').html());
        scriptJquery('#forgot_country_code').remove();
        
        scriptJquery('#country_code').selectize({
          onInitialize: function(){
            var $select = scriptJquery('#country_code');
            var selectize = $select[0].selectize; 
            
            if(typeof selectize.options[selectize.items[0]] != 'undefined') {
              var image = selectize.options[selectize.items[0]].image;
              
              scriptJquery(selectize.$control[0]).find('.item').remove();
              if(typeof image != 'undefined') {
                scriptJquery(selectize.$control[0]).prepend(`<div class="item" data-value="${selectize.options[selectize.items[0]].value.split('_')[0]}"><img src="${image}" height="20" width="20"> +${selectize.options[selectize.items[0]].value.split('_')[0]}</div>`);
              } else {
                scriptJquery(selectize.$control[0]).prepend(`<div class="item" data-value="<?php echo $country->phonecode; ?>"> +<?php echo $country->phonecode; ?></div>`);
              }
            } else {
              <?php  if(!empty($country->icon)) { 
                if(!empty($country->icon)) {
                  $path = Engine_Api::_()->core()->getFileUrl($country->icon); 
                ?>
                scriptJquery(selectize.$control[0]).prepend(`<div class="item" data-value="<?php echo $country->phonecode; ?>"><img src="<?php echo $path; ?>" height="20" width="20"> +<?php echo $country->phonecode; ?></div>`);
                <?php } else { ?>
                scriptJquery(selectize.$control[0]).prepend(`<div class="item" data-value="<?php echo $country->phonecode; ?>"> +<?php echo $country->phonecode; ?></div>`);
                <?php } ?>
              <?php } else { ?>
                scriptJquery(selectize.$control[0]).prepend(`<div class="item" data-value="<?php echo $country->phonecode; ?>"> +<?php echo $country->phonecode; ?></div>`);
              <?php } ?>
            }
          },
          render: {
            option: function (data, escape) {
              if(data.image) {
                return '<div><img src="' + data.image + '" alt="' + escape(data.text) + '" style="width: 20px; height: 20px;"> ' + escape(data.text) + '</div>';
              } else {
                return '<div>' + escape(data.text) + '</div>';
              }
            },
          },
          onDropdownClose: function(dropdown) {
            var that = scriptJquery(dropdown).prev().find('.item');
            var image = scriptJquery('.selectize-dropdown-content').find(`[data-value="${that.data('value')}"]`).find('img').attr('src');
            if(typeof image != 'undefined') {
              that.html(`<img src="${image}" height="20" width="20"> +${that.data('value').split('_')[0]}`);
            } else {
              if(typeof that.find('img') != 'undefined' && that.find('img').length) {
                that.html(`<img src="${that.find('img').attr('src')}" height="20" width="20"> +${that.data('value').split('_')[0]}`);
              } else {
                that.html(`+${that.data('value').split('_')[0]}`);
              }
            }
          },
        });
        scriptJquery('#country_code').hide();
      <?php } ?>

      if(scriptJquery('#country_code').val())
        scriptJquery('#countrycode').val(scriptJquery('#country_code').val());
    });
    
    AttachEventListerSE('keyup', '#email', function(e) {
      var emailVal = scriptJquery("#email").val();
      //if(emailVal === '') {
        scriptJquery("#verify_email").remove().hide();
      //}
      <?php if(!empty($otpsms_signup_phonenumber)) { ?>
        if(emailVal.match(/^\d+$/)) {
          scriptJquery('#country_code_element').show();
          scriptJquery('#email-wrapper').addClass('country_code_main');
          //scriptJquery('#country_code').show();
        } else {
          scriptJquery('#country_code_element').hide();
          scriptJquery('#email-wrapper').removeClass('country_code_main');
          //scriptJquery('#country_code').hide();
        }
      <?php } ?>
    });
    
    function sendEmailCode() {
      var email = scriptJquery('#email').val();
      var country_code = scriptJquery('#country_code').val();
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
          type: 'forgot',
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
	  scriptJquery(".core_loading_cont_overlay").show();
      var email = scriptJquery('#validEmail').val();
      scriptJquery("#core_loading_cont_overlayf").show();
      var url = en4.core.baseUrl + 'core/otp/validateotp';
      (scriptJquery.ajax({
        url : url,
        dataType: 'json',
        data : {
          format : 'json',
          email : email,
          code: code,
          type: 'forgot',
        },
        success : function(response) {
          scriptJquery("#core_loading_cont_overlayf").hide();
		  scriptJquery(".core_loading_cont_overlay").hide();
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
	  scriptJquery('#otp_code').val('');
	  scriptJquery('#error_message').html("");
    }
    
    AttachEventListerSE('click', '#verify_otp', function(e){
      validateTwoStepCode();
    });
    
    AttachEventListerSE('click', '#resend_otp', function(e){
      resendOtpCode();
    });
    
    var resendHTML;
    function resendOtpCode() {
      if(scriptJquery('#resend_otp').hasClass('active'))
        return;
      scriptJquery('#resend_otp').html('<i class="fas fa-spinner fa-spin"></i>');
      scriptJquery('#resend_otp').addClass('active');
      resendHTML = scriptJquery('#resend_otp').html();
      scriptJquery.ajax({
        dataType: 'json',
        url: en4.core.baseUrl + 'core/otp/sendotp',
        method: 'post',
        data: {
          format: 'json',
          email: scriptJquery('#email').val(),
          country_code: scriptJquery('#country_code').val(),
          type: 'forgot',
        },
        success: function(responseJSON) {
          scriptJquery('#resend_otp').removeClass('active');
          if (responseJSON.status) {
            scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').html(responseJSON.timerdata);
          } else if(responseJSON.error == 1 && responseJSON.message) {
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').html(responseJSON.message);
          }
        }
      });
    }
  <?php //} ?>
</script>
<?php if(!empty($otpsms_signup_phonenumber)) { ?>
  <?php echo Engine_Api::_()->getDbTable('countries', 'core')->getAllCountryHtml(array('id' => 'forgot_country_code')); ?>
<?php } ?>
<?php echo $this->partial('_otpPopUp.tpl', 'user', array()); ?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    scriptJquery("body").addClass('authpage')
  })
</script>
