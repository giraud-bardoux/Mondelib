<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: general.tpl 9874 2013-02-13 00:48:05Z shaun $
 * @author     Steve
 */
?>
<?php 
$settings = Engine_Api::_()->getApi('settings', 'core'); 
$otpsms_signup_phonenumber = $settings->getSetting('otpsms.signup.phonenumber', 0);

if(!empty($otpsms_signup_phonenumber)) {
  $defaultCountry = !empty($this->user->country_code) ? $this->user->country_code : Engine_Api::_()->getApi('settings', 'core')->getSetting('otpsms.default.countries','US');
  $getCountry = Engine_Api::_()->getDbTable('countries', 'core')->getCountry($defaultCountry);
  if(!empty($getCountry)) {
    $country = Engine_Api::_()->getItem('core_country', $getCountry);
  }
}
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'user')); ?>

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
      <div class="user_setting_global_form">
        <div class="global_form">
          <?php if ($this->form->saveSuccessful): ?>
            <h3><?php echo $this->translate('Settings were successfully saved.');?></h3>
          <?php endif; ?>
          <?php echo $this->form->render($this) ?>
        </div>
      </div>
      <?php if( Zend_Controller_Front::getInstance()->getRequest()->getParam('format') == 'html' ): ?>
        <script type="text/javascript">
          en4.core.runonce.add(function(){
            var req = new Form.Request($$('.global_form')[0], $('global_content'), {
              requestOptions : {
                url : '<?php echo $this->url(array()) ?>'
              },
              extraData : {
                format : 'html'
              }
            });
          });
        </script>
      <?php endif; ?>
      <?php if(Engine_Api::_()->authorization()->getPermission($this->user,'user', 'changeEmail')) { ?>
        <?php $url = $this->url(array('module' => 'user', 'controller' => 'settings','action' => 'edit-email', 'param' => 1), 'user_extended', true); ?>
        <script type="text/javascript">
          en4.core.runonce.add(function() {
            var editEmail = '<a href="<?php echo $url; ?>"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Edit Email Address"); ?>" class="smoothbox edit_email_setting"  ><?php echo $this->translate('<i class="fa fa-pencil-alt"></i>'); ?></a>';
            scriptJquery('#email-element').after(editEmail);
          });
        </script>
      <?php } ?>
    </div>
  </div>
</div>
<?php if(!empty($otpsms_signup_phonenumber)) { ?>

  <script type="text/javascript">
  
    var currentPhoneNumber = '';
    <?php if($this->user->phone_number) { ?>
      currentPhoneNumber = '<?php echo $this->user->country_code.$this->user->phone_number; ?>';
    <?php } ?>
    en4.core.runonce.add(function() {
      scriptJquery('#phone_number-element').prepend(scriptJquery('#settings_country_code').html());
      scriptJquery('#settings_country_code').remove();
      
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
		  scriptJquery('#phone_number').trigger('blur');
        },
      });
      
      scriptJquery('#country_code_element').show();
      scriptJquery('#phone_number-wrapper').addClass('country_code_main');
      scriptJquery(scriptJquery('#signup_pop_wrap').html()).appendTo('#append-script-data');
      scriptJquery('#signup_pop_wrap').remove();
    });
    
    AttachEventListerSE('blur', '#phone_number', function(e) {
      if(scriptJquery('#verifed_text').length == 0) {
        scriptJquery("#verify_email").remove();
        scriptJquery("#verifed_text").remove();
        scriptJquery("#otp_verifed").remove();
        var Originalcode = scriptJquery('#country_code').val().split('_')[0];
        if(scriptJquery('#phone_number').val() && currentPhoneNumber != Originalcode+ scriptJquery('#phone_number').val() && !scriptJquery('#otp_verifed').val()) {
          varifyPhoneNumber(scriptJquery('#phone_number').val(), scriptJquery('#country_code').val());
        }
      }
    });

    AttachEventListerSE('keyup', '#phone_number', function(e) {
      var emailVal = scriptJquery("#phone_number").val();
      //if(emailVal === '') {
        scriptJquery("#verify_email").remove();
        scriptJquery("#verifed_text").remove();
        scriptJquery("#otp_verifed").remove();
      //}
    });
    
    function varifyPhoneNumber(phone_number, country_code) {
      
      if(scriptJquery('#verify_email').length == 1) {
        scriptJquery('#verify_email').remove();
      }
      if(scriptJquery('#phone_number_exists').length) {
        scriptJquery('#phone_number_exists').remove();
      }
      if(scriptJquery('#phone_number_loading').length == 0) {
        scriptJquery('#phone_number-element').append('<a href="javascript:;" class="verify_email verify_email_icon" id="phone_number_loading"><i class="fas fa-spinner fa-spin"></i></a>');
      }
      
      var url = en4.core.baseUrl + 'core/otp/phonenumberexists';
      (scriptJquery.ajax({
        url : url,
        dataType: 'json',
        data : {
          format : 'json',
          phone_number : phone_number,
          country_code : country_code,
          param: 1,
        },
        success : function(response) {
          scriptJquery('#phone_number_loading').remove();
          if(response.status) {
            if(scriptJquery('#verify_email').length == 0) {
              scriptJquery('#phone_number-element').append('<a href="javascript:;" data-bs-toggle="modal" data-bs-target="#user_signup_email" class="verify_email verify_email_icon" id="verify_email"><?php echo $this->translate('Verify'); ?></a>');
            }
          } else {
            scriptJquery("#verify_email").remove();
            scriptJquery('#phone_number').after('<div class="error font_small mt-2" id="phone_number_exists">'+response.message+'</div>');
          }
        }
      }));
    }

    function sendEmailCode() {
      var email = scriptJquery('#phone_number').val();
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
          type: 'addnumber',
          user_id: '<?php echo $this->viewer()->getIdentity(); ?>',
        },
        success : function(response) {
          if(response.status) {
            if(country_code) {
              otpsmsTimerData(email);
            }
            
            scriptJquery('#user_signup_email').show();
            scriptJquery('#send_signup_form').show();
            scriptJquery('#success_msg').hide();
            scriptJquery('#twostep_auth_form').show();
            scriptJquery('#otp_code').val('');
            scriptJquery('#validEmail').val(email);
            scriptJquery('#verify_email').html('<?php echo $this->translate('Verify'); ?>');
          } else if(response.error == 1 && response.message) {
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').attr("style", "color: rgb(255, 0, 0);");
            scriptJquery('#otp_timer').html(response.message);
          } else {
            scriptJquery('#verify_email').html('<?php echo $this->translate('Verify'); ?>');
          }
        }
      }));
    }

    AttachEventListerSE('click', '#verify_email', function(e){
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
          type: 'addnumber',
        },
        success : function(response) {
          scriptJquery("#verify_popup_loading").hide();
          if(response.status) {
            scriptJquery('#twostep_auth_form').hide();
            scriptJquery('#send_signup_form').append("<div id='success_msg' class='success_msg success_msg m-2'><span>"+response.message+"</span></div>");
            setTimeout(() => {
              scriptJquery('#cancel_verify_otp').trigger('click');
              closeVerifyPopup();
              scriptJquery('#verify_email').remove();
              scriptJquery('#phone_number-element').append('<div id="verifed_text" class="font_small"><?php echo $this->translate("Verified"); ?></div>');
              scriptJquery('#submit-wrapper').after('<input type="hidden" name="otp_verifed" id="otp_verifed" value="1" />');
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

    function resendOtpCode() {
      if(scriptJquery('#resend_otp').hasClass('active'))
        return;
      var email = scriptJquery('#phone_number').val();
      var country_code = scriptJquery('#country_code').val();
      scriptJquery('#resend_otp').html('<i class="fas fa-spinner fa-spin"></i>');
      scriptJquery('#resend_otp').addClass('active');
      scriptJquery.ajax({
        dataType: 'json',
        url: en4.core.baseUrl + 'core/otp/sendotp',
        method: 'post',
        data: {
          format : 'json',
          email : email,
          country_code : country_code,
          type: 'addnumber',
          user_id: '<?php echo $this->viewer()->getIdentity(); ?>',
        },
        success: function(responseJSON) {
          scriptJquery('#resend_otp').removeClass('active');
          if (responseJSON.status) {
            scriptJquery('#resend_otp').html('<?php echo $this->translate("Resend OTP"); ?>');
            scriptJquery('#resend_otp').hide();
            scriptJquery('#otp_timer').html(responseJSON.timerdata);
          }
        }
      });
    }
  </script>

  <?php echo Engine_Api::_()->getDbTable('countries', 'core')->getAllCountryHtml(array('id' => 'settings_country_code', 'country_code' => $this->user->country_code ? $this->user->country_code : '')); ?>
  
  <?php echo $this->partial('_otpPopUp.tpl', 'user', array()); ?>
<?php } ?>
