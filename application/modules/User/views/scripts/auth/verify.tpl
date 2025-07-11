<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: verify.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <?php echo $this->form->render($this) ?>
  </div>
</div>
<script type="text/javascript">
 
  <?php if(is_numeric($this->email) && $this->country_code) { ?> 
    en4.core.runonce.add(function() {
      scriptJquery('#code-element').append(scriptJquery('#otptimer').html());
      scriptJquery('#otptimer').remove();
    });
    otpsmsTimerData('<?php echo $this->email; ?>');
  <?php } ?>
  
  AttachEventListerSE('click', '#resend_otp', function(e){
    resendOtpCode();
  });
  
  function resendOtpCode() {
    if(scriptJquery('#resend_otp').hasClass('active'))
      return;
    scriptJquery('#resend_otp').html('<i class="fas fa-spinner fa-spin"></i>');
    scriptJquery('#resend_otp').addClass('active');
    scriptJquery.ajax({
      dataType: 'json',
      url: en4.core.baseUrl + 'core/otp/sendotp',
      method: 'post',
      data: {
        format: 'json',
        user_id: "<?php echo $this->user_id; ?>",
        email: '<?php echo $this->email; ?>',
        country_code: <?php echo $this->country_code; ?>,
        type:'login',
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
  en4.core.runonce.add(function(){
    scriptJquery("body").addClass('authpage');
  })
</script>
<?php if(is_numeric($this->email) && $this->country_code) { ?> 
  <div id="otptimer" class="mt-2 input_field d-flex justify-content-between" style="display:none;">
    <div>
      <div id="otp_timer" class="font_small">
        <div id="timer" class="font_small"><?php echo Engine_Api::_()->getApi('otp', 'core')->getOtpExpire(); ?></div>
      </div>
    </div>
    <a href="javascript:void(0);" type="button" style="display:none;" id="resend_otp" class="font_small font_color_hl"><?php echo $this->translate('Resend OTP'); ?></a>
  </div>
<?php } ?>
