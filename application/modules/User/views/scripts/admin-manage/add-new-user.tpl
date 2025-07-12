<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: add-new-user.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl."externals/selectize/css/normalize.css");
$headScript = new Zend_View_Helper_HeadScript();
$headScript->appendFile($this->layout()->staticBaseUrl.'externals/selectize/js/selectize.js');

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

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_members', 'lastMenuItemName' => 'Add New User')); ?>

<?php echo $this->partial('_jsSwitch.tpl', 'fields', array()); ?>
<div class='clear'>
  <div class='settings admin_user_edit_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<style>
#signup_account_form #name-wrapper{
  display: none;
}
</style>

<script type="text/javascript">

  AttachEventListerSE('click','.copy_password',function (e) {
    if(scriptJquery('#signup_password').val().length) {
      scriptJquery("<textarea/>").appendTo("body").val(scriptJquery('#signup_password').val()).select().each(function () {
        document.execCommand('copy');
      }).remove();
      showSuccessTooltip('<i class="fas fa-check-circle"></i><span>'+('<?php echo $this->translate("Password copied successfully."); ?>')+'</span>');
    }
  });
  
  en4.core.runonce.add(function() {
    scriptJquery('#password-element').append('<div id="passwordroutine" class="password_checker"><div id="passwordroutine_length"></div><div class="d-flex justify-content-between align-content-center"><div id="passwordroutine_text" class="font_small"><?php echo $this->translate("Enter your password.")?></div><div id="password-hint"><i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="<?php echo $this->translate('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.'); ?>" data-bs-original-title="" title=""></i></div></div></div>');
  });

  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_members').addClass('active');


  scriptJquery(document).on('keyup', '#email', function(e) {
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

  scriptJquery(document).ready(function() {
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

    scriptJquery(scriptJquery('#signup_pop_wrap').html()).appendTo('body');
    scriptJquery('#signup_pop_wrap').remove();
    if(scriptJquery('#country_code').val())
      scriptJquery('#countrycode').val(scriptJquery('#country_code').val());
      
    <?php if (!empty($_SESSION['facebook_signup']) || !empty($_SESSION['twitter_signup']) || !empty($_SESSION['google_signup']) || !empty($_SESSION['linkedin_signup'])) { ?>
      scriptJquery('#email-element').append('<div id="verifed_text" class="font_small"><?php echo $this->string()->escapeJavascript($this->translate("Verified")); ?></div>');
      scriptJquery('#submit_signup-wrapper').after('<input type="hidden" name="otp_verifed" id="otp_verifed" value="1" />');
    <?php } ?>
  });
</script>
<?php if(!empty($otpsms_signup_phonenumber)) { ?>
  <?php echo Engine_Api::_()->getDbTable('countries', 'core')->getAllCountryHtml(array('id' => 'signup_country_code')); ?>
<?php } ?>
