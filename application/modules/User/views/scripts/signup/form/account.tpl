<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: account.tpl 10143 2014-03-26 16:18:25Z andres $
 * @author     John
 */
?>
<?php
$settings = Engine_Api::_()->getApi('settings', 'core'); 
$otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);

$getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry(Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US'));
if(!empty($getCountry)) {
  $country = Engine_Api::_()->getItem('core_country', $getCountry);
}

$spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
$recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;

$this->headTranslate(array(
  'Weak', 'Strong', 'Enter your password.','Sign Up', 'Token Expired'
));
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'user')); ?>
<style>
  #signup_account_form #name-wrapper,#submit_signup, #submit_signup-wrapper{
    display: none;
  }
</style>
<div class="signup_form">
  <?php echo $this->form->render($this) ?>
</div>
<?php $profileUrl = '<br />'._ENGINE_SITE_URL.'/profile/'."<span id='profile_address'>yourname</span>"; ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#username-element').find('p').append("<?php echo $profileUrl; ?>");
    if( scriptJquery('#username') && scriptJquery('#profile_address') ) {
      var profile_address = scriptJquery('#profile_address').html();
      profile_address = profile_address.replace('<?php echo /*$this->translate(*/'yourname'/*)*/?>',
          '<span id="profile_address_text"><?php echo $this->translate('yourname') ?></span>');
      scriptJquery('#profile_address').html(profile_address);

      AttachEventListerSE('keyup','#username', function() {
        var text = '<?php echo $this->translate('yourname') ?>';
        if( this.value != '' ) {
          text = this.value;
        }
        scriptJquery('#profile_address_text').html(text.replace(/[^a-z0-9]/gi,''));
      });
      // trigger on page-load
      if( document.getElementById('username').value.length ) {
        document.getElementById('username').fireEvent('keyup');
      }
    }
  });

  scriptJquery(document).ready(function() {
    scriptJquery('#password-element').append('<div id="passwordroutine" class="password_checker"><div id="passwordroutine_length"></div><div class="d-flex justify-content-between align-content-center"><div id="passwordroutine_text" class="font_small"><?php echo $this->string()->escapeJavascript($this->translate("Enter your password."))?></div><div id="password-hint"><i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $this->string()->escapeJavascript($this->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.')); ?>" data-bs-original-title="" title=""></i></div></div></div>');

  });

  var formCheck = false;
  function submitSignupForm(e, obj) {
    if(!formCheck) {
      
      e.preventDefault();

      // Check if all required fields are filled out
      var formObj = scriptJquery(obj);
      var formData = new FormData(obj);
      var submitButtonLabel = formObj.find('button[type=submit]').html();
      formObj.find('button[type=submit]').html('<i class="fas fa-spinner fa-spin"></i>');
      formObj.find('button[type=submit]').attr("disabled",true);
      //scriptJquery('#submit').html('<i class="fas fa-spinner fa-spin"></i>');
      var url = en4.core.baseUrl + 'signup';
      (scriptJquery.ajax({
        url : url,
        type: "POST",
        dataType: 'json',
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        error : function(response) {
          submitAjaxRequestSend = null;
          if(scriptJquery(response.responseText).find('#global_content').length > 0) {
            var searchGlobalContent = scriptJquery(response.responseText).find('#global_content').html();
            if(searchGlobalContent) {
              scriptJquery('#global_content').html(searchGlobalContent);
              if(scriptJquery('#global_content').find('form'))
                scriptJquery('#global_content').find('form').removeClass('form_submit_ajax').addClass('signup_account_form');
            }
          }
          scriptJquery("body").attr("id",'global_page_user-signup-index');
          formObj.find('button[type=submit]').removeAttr("disabled");
          formObj.find('button[type=submit]').html(submitButtonLabel);

          if(scriptJquery('.layout_core_content').length) {
            scriptJquery('html, body').scrollTop(0);
          }
        },
        success : function(response) {
          if(response.status) {
            formCheck = true;
            if(scriptJquery('#form_errors').length)
              scriptJquery('#form_errors').remove();
            
            if(typeof grecaptcha != 'undefined') {
              grecaptcha.execute("<?php echo @$spamSettings['recaptchapublicv3']; ?>", {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                scriptJquery('#recaptchaResponse').val(token);
                loadAjaxContentApp(response.redirectFullURL,false,"full");  
                //scriptJquery('#submit_signup').trigger('click');
              });
            } else if(response.redirectFullURL) {
              loadAjaxContentApp(response.redirectFullURL,false,"full");
              //scriptJquery('#submit_signup').trigger('click');
            }
          } else {
            formObj.find('button[type=submit]').removeAttr("disabled");
            formObj.find('button[type=submit]').html(submitButtonLabel);
            scriptJquery('#submit').html('<?php echo $this->string()->escapeJavascript($this->translate("Sign Up")) ; ?>');
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
            formObj.find('.form-elements').prepend(errors);
            
            if(scriptJquery('.layout_core_content').length) {
              scriptJquery('html, body').animate({
                scrollTop: scriptJquery('.layout_core_content').offset().top
              }, 2000);
            }
            
            <?php if($spamSettings['signup'] && !empty($recaptchaVersionSettings)) { ?>
              refreshCaptcha(formObj);
            <?php } ?>

            if(typeof grecaptcha != 'undefined') {
              grecaptcha.execute("<?php echo @$spamSettings['recaptchapublicv3']; ?>", {action: 'submit'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                scriptJquery('#recaptchaResponse').val(token);
                //scriptJquery('#submit_signup').trigger('click');
              });
            }
          }
        }
      }));
      return false;
    } else {
      return true;
    }
  }

  AttachEventListerSE('submit', '#signup_account_form', function(e) {
    submitSignupForm(e, this);
  });
  AttachEventListerSE('submit', '.signup_account_form', function(e) {
    submitSignupForm(e, this);
  });
  
  <?php if( $settings->getSetting('user.signup.enabletwostep', 0) == 1 || !empty($otpsms_signup_phonenumber)) { ?>
  
    en4.core.runonce.add(function() {
      <?php if(!empty($otpsms_signup_phonenumber)) { ?>
        scriptJquery('#email-element').prepend(scriptJquery('#signup_country_code').html());
        scriptJquery('#signup_country_code').remove();
        
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

      scriptJquery(scriptJquery('#signup_pop_wrap').html()).appendTo('#append-script-data');
      scriptJquery('#signup_pop_wrap').remove();
      if(scriptJquery('#country_code').val())
        scriptJquery('#countrycode').val(scriptJquery('#country_code').val());
        
      <?php if (!empty($_SESSION['facebook_signup']) || !empty($_SESSION['twitter_signup']) || !empty($_SESSION['google_signup']) || !empty($_SESSION['linkedin_signup'])) { ?>
        if(scriptJquery("#email").val()){
          scriptJquery('#email-element').append('<div id="verifed_text" class="font_small"><?php echo $this->string()->escapeJavascript($this->translate("Verified")); ?></div>');
          scriptJquery('#submit_signup-wrapper').after('<input type="hidden" name="otp_verifed" id="otp_verifed" value="1" />');
        }
      <?php } ?>
    });

    AttachEventListerSE('blur', '#signup_account_form #email', function(e) {
      
      var formObj = scriptJquery('#signup_account_form');
      if(scriptJquery('#verifed_text').length == 0 && typeof scriptJquery(this).val() != 'undefined') {
        scriptJquery("#verify_email").remove();
        scriptJquery("#verifed_text").remove();
        scriptJquery("#otp_verifed").remove();

        if(scriptJquery(this).val()) {
          verifyPhoneNumber(scriptJquery(this).val(), scriptJquery('#country_code').val());
        }
      }
    });
    
    function verifyPhoneNumber(phone_number, country_code) {
      var formObj = scriptJquery('#signup_account_form');
      
      if(!phone_number.match(/^\d+$/)) {
        <?php if(empty($settings->getSetting('user.signup.enabletwostep', 0))) { ?> 
          return;
        <?php } ?>
      }

      if(scriptJquery('#phone_number_exists').length) {
        scriptJquery('#phone_number_exists').remove();
      }
      if(scriptJquery('#phone_number_loading').length == 0) {
        formObj.find('#email-element').append('<a href="javascript:;" class="verify_email verify_email_icon" id="phone_number_loading"><i class="fas fa-spinner fa-spin"></i></a>');
      }

      var url = en4.core.baseUrl + 'core/otp/phonenumberexists';
      (scriptJquery.ajax({
        url : url,
        dataType: 'json',
        data : {
          format : 'json',
          phone_number : phone_number,
          country_code : country_code,
        },
        success : function(response) {
          scriptJquery('#phone_number_loading').remove();
          if(response.status) {
            if(scriptJquery('#verify_email').length == 0) {
              formObj.find('#email-element').append('<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#user_signup_email" class="verify_email verify_email_icon" id="verify_email"><?php echo $this->string()->escapeJavascript($this->translate('Verify')); ?></a>');
            }
          } else {
            scriptJquery("#verify_email").remove();
            formObj.find('#email-element').after('<div class="error font_small mt-2" id="phone_number_exists">'+response.message+'</div>');
          }
        }
      }));
    }

    AttachEventListerSE('keyup', '#email', function(e) {
      var formObj = scriptJquery('#signup_account_form');
      var emailVal = scriptJquery("#email").val();
      //if(emailVal === '') {
        scriptJquery("#verify_email").remove();
        scriptJquery("#verifed_text").remove();
        scriptJquery("#otp_verifed").remove();
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
      var email = scriptJquery('#signup_account_form').find('input[name="email"]').val(); //scriptJquery('#email').val();
      var country_code = scriptJquery('#country_code').val();
      scriptJquery('#verify_email').html('<i class="fas fa-spinner fa-spin"></i>');
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
          type: 'signup'
        },
        success : function(response) {
          if(response.status) {
            scriptJquery('#user_signup_email').show();
            scriptJquery('#send_signup_form').show();
            scriptJquery('#success_msg').hide();
            scriptJquery('#twostep_auth_form').show();
            scriptJquery('#otp_code').val('');
            scriptJquery('#validEmail').val(email);
            scriptJquery('#verify_email').html('<?php echo $this->string()->escapeJavascript($this->translate('Verify')); ?>');
            if(country_code) {
              otpsmsTimerData(email);
            }
          } else if(response.error == 1 && response.message) {
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').attr("style", "color: rgb(255, 0, 0);");
            scriptJquery('#otp_timer').html(response.message);
          } else {
            scriptJquery('#verify_email').html('<?php echo $this->string()->escapeJavascript($this->translate('Verify')); ?>');
          }
        }
      }));
    }
    
    en4.core.runonce.add(function() {
      if(scriptJquery("#signup_account_form").find("#email").val()){
        scriptJquery("#signup_account_form").find("#email").trigger("blur");
      }
    })
    
    AttachEventListerSE('click', '#signup_account_form #verify_email', function(e){
      sendEmailCode();
    });

    function validateTwoStepCode() {
    
      var code = scriptJquery('#otp_code').val();
      if(code === '') {
        scriptJquery('#error_message').show().html("<span><?php echo $this->string()->escapeJavascript($this->translate('The security code you have entered is not correct. Please check your code and try again.')); ?></span>");
        return;
      }
      var email = scriptJquery('#validEmail').val();
      scriptJquery("#verify_popup_loading").show();
      var url = en4.core.baseUrl + 'core/otp/validateotp';
      (scriptJquery.ajax({
        url : url,
        dataType: 'json',
        data : {
          format : 'json',
          email : email,
          code: code,
          type: 'signup',
        },
        success : function(response) {
          scriptJquery("#verify_popup_loading").hide();
          if(response.status) {
            scriptJquery('#twostep_auth_form').hide();
            scriptJquery('#send_signup_form').append("<div id='success_msg' class='success_msg success_msg m-2'><span>"+response.message+"</span></div>");
            setTimeout(() => {
              //closeVerifyPopup();
              scriptJquery('#cancel_verify_otp').trigger('click');
              scriptJquery('#send_signup_form').hide();
              scriptJquery('#verify_email').remove();
              scriptJquery('#email-element').append('<div id="verifed_text" class="font_small"><?php echo $this->string()->escapeJavascript($this->translate("Verified")); ?></div>');
              scriptJquery('#submit_signup-wrapper').after('<input type="hidden" name="otp_verifed" id="otp_verifed" value="1" />');
            }, 2000);
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
    
    AttachEventListerSE('change', '#country_code', function(e){
      var item = scriptJquery(this);
      scriptJquery('#countrycode').val(item.val());
    });
    
    function resendOtpCode() {
      if(scriptJquery('#resend_otp').hasClass('active'))
        return;
      var email = scriptJquery('#email').val();
      var country_code = scriptJquery('#country_code').val();
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
          type: 'signup'
        },
        success: function(responseJSON) {
          scriptJquery('#resend_otp').removeClass('active');
          if (responseJSON.status) {
            scriptJquery('#resend_otp').html('<?php echo $this->string()->escapeJavascript($this->translate("Resend OTP")); ?>');
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').html(responseJSON.timerdata);
          } else if(responseJSON.error == 1 && responseJSON.message) {
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').html(responseJSON.message);
          }
        }
      });
    }
  <?php } ?>


  if(typeof loginSignupPlaceHolderActive != 'undefined') {
    en4.core.runonce.add(function() {
      scriptJquery ('#signup_account_form input,#signup_account_form input[type=email], #signup_account_form select').each(
        function(index){
          var input = scriptJquery (this);
          if(scriptJquery (this).closest('div').parent().css('display') != 'none' && scriptJquery (this).closest('div').parent().find('.form-label').find('label').first().length && scriptJquery (this).prop('type') != 'hidden' && scriptJquery (this).closest('div').parent().attr('class') != 'form-elements') {
            if(scriptJquery (this).prop('type') == 'email' || scriptJquery (this).prop('type') == 'text' || scriptJquery (this).prop('type') == 'password') {
              scriptJquery (this).attr('placeholder',scriptJquery (this).closest('div').parent().find('.form-label').find('label').html());
              scriptJquery (this).closest('div').parent().find('.form-label').hide();
            }
          }
        }
      )
    });
  }
</script>

<?php if(!empty($otpsms_signup_phonenumber)) { ?>
  <?php echo Engine_Api::_()->getDbTable('countries', 'core')->getAllCountryHtml(array('id' => 'signup_country_code')); ?>
<?php } ?>

<?php echo $this->partial('_otpPopUp.tpl', 'user', array()); ?>

<script type='text/javascript'>
  //en4.core.runonce.add(function() {
    if(scriptJquery("#global_wrapper").hasClass('signup_subscriptions_plans'))
      scriptJquery("#global_wrapper").removeClass('signup_subscriptions_plans');
  //});
</script>