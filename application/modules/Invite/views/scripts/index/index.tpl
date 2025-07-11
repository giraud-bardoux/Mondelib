<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<?php  $socialmediaoptions = unserialize($settings->getSetting('invite.socialmediaoptions', '')); ?>
<?php if($socialmediaoptions && engine_in_array('facebook', $socialmediaoptions)) { ?>
  <script src="//connect.facebook.net/en_US/all.js" type="text/javascript"></script>
<?php } ?>

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
    <div class="user_invite_tabs">
      <div class="tabs">
        <ul class="navigation">
          <li class="active">
            <a class="" href="<?php echo $this->url(array("module" => "invite", "controller" => "index", "action" => "index"), 'default', true); ?>"><?php echo $this->translate("Invite Your Friends"); ?></a>
          </li>
          <li>
            <a class="" href="<?php echo $this->url(array("module" => "invite", "controller" => "settings", "action" => "manage-invites"), 'default', true); ?>"><?php echo $this->translate("Manage Invites / Referrals"); ?></a>
          </li>
        </ul>
        <?php if($settings->getSetting('invite.enable', 1) && !empty($settings->getSetting('invite.signupenable', 0)) && Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.referralforsingup', 1)) { ?>
          <div class="copy_link_container">
            <div class="copy_link_field">
              <div class="_des font_color_light"><?php echo $this->translate("Referral Code"); ?></div>
              <input disabled="disabled" type="type" value="<?php echo $this->referral_code;?>" id="myreferralcode" />
              <button class="copy_link copy_referral_code" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Copy");?>"><i class="far fa-copy"></i></button>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
    <div class="invite_main">
      <div class="invite_section position-relative">
        <?php if(engine_count($socialmediaoptions) > 0 && $settings->getSetting('invite.enable', 1)) { ?>
          <div class="invite_tab">
            <ul>
              <?php if(engine_in_array('csv', $socialmediaoptions) || engine_in_array('emailinvite', $socialmediaoptions)){ ?>
                <li><a href="javascript:void(0);" id="socialContacts" class="tablinks active" onclick="openInviteTab(event, 'input_contact')"><?php echo $this->translate("Import Your Contacts"); ?></a>
              </li>
              <?php } ?>
              <?php if(engine_in_array('csv', $socialmediaoptions)){ ?>
              <li>
                <a href="javascript:void(0);" id="uploadContacts" class="tablinks" onclick="openInviteTab(event, 'upload_contact')"><?php echo $this->translate("Upload Your Contacts"); ?></a>
              </li>
              <?php } ?>
              <?php if(engine_in_array('emailinvite', $socialmediaoptions)){ ?>
              <li>
                <a href="javascript:void(0);" id="inviteContacts" class="tablinks <?php if($this->param == 'friends'): ?> active <?php endif; ?>" onclick="openInviteTab(event, 'add_address')"><?php echo $this->translate("Invite Your Friends"); ?></a>
              </li>
              <?php } ?>
            </ul>
          </div>
        <?php } ?>
        <div id="input_contact" class="tabcontent invite_tab_content" style="display:block;">
          <h4 class="invite_tab_content_heading"><?php echo $this->translate("Invite Your Friends"); ?></h4>
          <p class="invite_tab_content_des"><?php echo $this->translate("How do you want to invite people to join this community? Pick a service to send invitations."); ?></p>
          <div class="invite_social_buttons">
            <?php if($settings->getSetting('invite.enable', 1)) { ?>
              <ul> 
                <?php if($settings->getSetting('invite.facebookclientid', '') && engine_in_array('facebook', $socialmediaoptions)) { ?>
                  <li class="facebook" id="facebookInviteContact"><a href="javascript:void(0);" onclick="FbInviteRequest('<?php echo $settings->getSetting("invite.facebookmessage", "This page is amazing, check it out!"); ?>','44530');"><i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"/></svg>
                  </i><span><?php echo $this->translate("Facebook"); ?></span></a></li>
                <?php } ?>
                <?php if(engine_in_array('csv', $socialmediaoptions)){ ?>
                  <li class="csv"><a href="javascript:void(0);" onclick="showUploadContacts('csv');"><i><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128z"/></svg></i><span><?php echo $this->translate("CSV"); ?></span></a></li>
                <?php } ?>
                <?php if(engine_in_array('emailinvite', $socialmediaoptions)){ ?>
                  <li class="email"><a href="javascript:void(0);" onclick="showUploadContacts('email');"><i>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
                  </i><span><?php echo $this->translate("Email"); ?></span></a></li>
                <?php } ?>
              </ul>
            <?php } ?>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.referralforsingup', 1)) { ?>
             <div class="invite_firends_container_main">
                <h4 class="invite_tab_content_heading"><?php echo $this->translate("Share URL"); ?></h4>
                <div class="copy_link_container">
                  <div class="copy_link_field">
                  <div class="_des font_color_light"><?php echo $this->translate("Referral Link"); ?></div>
                    <input disabled="disabled" type="type" value="<?php echo $this->referral;?>" id="myreferrallink" />
                    <button class="copy_link copy_referral" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Copy");?>"><i class="far fa-copy"></i></button>
                  </div>
                  <div class="copy_link_container_btn">
                    <a href="javascript:void(0);" class="btn btn-primary" class="core_animation" id="referralLinkShare"><?php echo $this->translate("Share"); ?></a>
                  </div>
                </div>
              </div>
              <script type="text/javascript">
                // Referral Code Copy Button 
                AttachEventListerSE('click','.copy_referral',function (e) {
                  if(scriptJquery('#myreferrallink').val().length) {
                    scriptJquery("<textarea/>").appendTo("body").val(scriptJquery('#myreferrallink').val()).select().each(function () {
                      document.execCommand('copy');
                    }).remove();
                    showSuccessTooltip('<i class="fas fa-check-circle"></i><span>'+('<?php echo $this->translate("Referral link copied successfully."); ?>')+'</span>');
                  }
                });
                AttachEventListerSE('click','.copy_referral_code',function (e) {
                  if(scriptJquery('#myreferralcode').val().length) {
                    scriptJquery("<textarea/>").appendTo("body").val(scriptJquery('#myreferralcode').val()).select().each(function () {
                      document.execCommand('copy');
                    }).remove();
                    showSuccessTooltip('<i class="fas fa-check-circle"></i><span>'+('<?php echo $this->translate("Referral code copied successfully."); ?>')+'</span>');
                  }
                });

                if (navigator.share) {
                  // Enable the Web Share API button
                  const shareButton = document.getElementById('referralLinkShare');
                  if(shareButton) {
                    shareButton.addEventListener('click', () => {
                      navigator.share({
                        title: '<?php echo $this->translate("Invitation"); ?>',
                        text: '<?php echo $this->translate("You are being invited to join our social network."); ?>',
                        url: '<?php echo $this->referral; ?>',
                      })
                      .then(() => console.log('<?php echo $this->translate("Shared successfully"); ?>'))
                      .catch((error) => console.error('Sharing failed:', error));
                    });
                  }
                } else {
                  // If Web Share API is not supported, hide the button
                  const shareButton = document.getElementById('referralLinkShare');
                  shareButton.style.display = 'none';
                }
              </script>
            <?php } ?>
          </div>
          <?php if($settings->getSetting('invite.enable', 1)) { ?>
            <form action="" method="post" id="socialuploadfile" enctype="multipart/form-data">
              <input type="hidden" name="socialMediaEmails" id="socialMediaEmails" />
            </form>
          <?php } ?>
        </div>
        <?php if($settings->getSetting('invite.enable', 1)) { ?>
          <?php if($settings->getSetting('invite.enable', 1) && engine_in_array('csv', $socialmediaoptions)) { ?>
            <div id="upload_contact" class="tabcontent invite_tab_content">
              <div class="invite_upload_contacts ">
                <p><?php echo $this->translate("Import your contacts by uploading the file in the required format and invite them to join this community."); ?></p> 
                <p class="helplink"><a id="myBtn" href="javascript:void(0);" onclick="showCSVMoreOption('1');" ><i class="fas fa-question-circle"></i><span><?php echo $this->translate("How to create a contact file."); ?></span></a></p> 
                <div id="option_1" class="invite_help_modal">
                  <div class="invite_help_modal_content">
                    <span class="close" onclick="optionClose();">&times;</span>
                    <div class="invite_help_modal_content_inner">
                      <div class="invite_help_modal_sec">
                        <p class="title"><a href="javascript:void(0);" onclick="showCSVMoreOption('2');"><?php echo $this->translate("Outlook"); ?></a></p>
                        <p id="option_2" class="discription">
                          <span><?php echo $this->translate("1. Sign into your Outlook Email account."); ?></span>
                          <span><?php echo $this->translate("2. Click on the People menu at the upper-left corner of your screen."); ?></span>
                          <span><?php echo $this->translate("3. Click Manage contacts in the menu bar."); ?></span>
                          <span><?php echo $this->translate("4. Choose Export Contacts."); ?></span>
						              <span><?php echo $this->translate("5. Now you can Export the contacts by clicking on Export button."); ?></span>
                        </p>
                      </div>
                      <div class="invite_help_modal_sec">
                        <p class="title"><a href="javascript:void(0);" onclick="showCSVMoreOption('3');"><?php echo $this->translate("Yahoo"); ?></a></p>
                        <p id="option_3" class="discription">
                          <span><?php echo $this->translate("1. Launch Yahoo email and sign in."); ?></span>
                          <span><?php echo $this->translate("2. Click on the Contacts tab."); ?></span>
                          <span><?php echo $this->translate("3. Click the Actions dropdown box."); ?></span>
                          <span><?php echo $this->translate("4. Choose Export."); ?></span>
                          <span><?php echo $this->translate('5. Click "Export to CSV file" which is the type of file to which you want to export your contacts.'); ?></span>
                        </p>
                      </div>
                      <div class="invite_help_modal_sec">
                        <p class="title"><a href="javascript:void(0);" onclick="showCSVMoreOption('5');"><?php echo $this->translate("Other"); ?></a></p>
                        <p id="option_5" class="discription">
                        <span><?php echo $this->translate("We support the following types of contact file:"); ?></span>
                        <span><?php echo $this->translate("- Comma-separated values (.csv)"); ?></span>
                        <span><a download target="_blank" href="<?php  echo "application/modules/Invite/externals/sampleTemplate.csv"; ?>"><?php echo $this->translate("Click here"); ?></a><?php echo $this->translate(" to download the sample csv file."); ?></span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <form action="" class="upload_csv_file" method="post" id="csvuploadfile" enctype="multipart/form-data">
                  <p class="select_file"><input type="file" name="csv" onchange="checkcsvfile(this)" />
                  </p>
                  <div id="errorUploadCsvFile"></div>
                </form>
                <div id="contactImportData"></div>
              </div>
            </div>
          <?php } ?>
          <?php if(engine_in_array('emailinvite', $socialmediaoptions)) { ?>
            <div id="add_address" class="tabcontent invite_tab_content">
              <div class="invite_invite_form" id="invite_invite_form">
                <?php echo $this->form->render($this) ?>
              </div>
              <div class="content_loading" id="invite_loading_cont_overlay" style="display:none;"></div>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<script type="application/javascript">

  scriptJquery('.user_settings_invites ').parent().addClass('active');
  
  function optionClose() {
    document.getElementById('option_1').style.display = 'none';
  }
  
  <?php if($settings->getSetting('invite.enable', 1)) { ?>
    function showCSVMoreOption(option) {
      if (document.getElementById('option_'+option).style.display == "block") {
        document.getElementById('option_'+option).style.display = "none";
      } else {
        document.getElementById('option_'+option).style.display = "block";
      }
    }

    <?php if($socialmediaoptions && engine_in_array('facebook', $socialmediaoptions)) { ?>

      function FbInviteRequest(message, data) {
        FB.ui(
          {
            method:'send',
            message:message,
            data:data,
            link: '<?php echo $this->referral; //(_ENGINE_SSL ? 'https://' : 'http://')  . $_SERVER['HTTP_HOST'].$this->baseUrl("signup") ?>',
            title:'<?php echo $settings->getSetting("invite.facebookmessage", $this->translate("Share this site with your friends")); ?>'
          },
          function(response){
            // response.request_ids holds an array of user ids that received the request
          }
        );
      }

      window.fbAsyncInit = function() {
        FB.init({
          appId : '<?php echo $settings->getSetting("invite.facebookclientid", ""); ?>',
          xfbml : true,
          version : 'v2.0'
        });
        open_facebook_invite_dialog();
      }; 

      (function(d, s, id) {

        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
          return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
      }
      (document, 'script', 'facebook-jssdk'));

      function open_facebook_invite_dialog() {
        if ( typeof FB == 'undefined')
          return;
      }

    <?php } ?>
    
    //invite from work
    function validateEmail(email) {
      const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test( email );
    }

    en4.core.runonce.add(function() {
    
      scriptJquery('#invite_invite').submit(function(e) {
        e.preventDefault();
        var subscribe_email = scriptJquery('#recipients').val();

        if(subscribe_email.length <= 0) {
          alert('<?php echo $this->translate("Please enter valid email address."); ?>');
          return;
        }
        
        var str_array = subscribe_email.split(',');
        
        if(str_array.length > 10) {
          alert('<?php echo $this->translate("You have entered more than 10 email addresses. Please enter up to 10 email addresses, separated by commas."); ?>');
          return;
        }

        for(var i = 0; i < str_array.length; i++) {
          // Trim the excess whitespace.
          let email = str_array[i].replace(/^\s*/, "").replace(/\s*$/, "");
          if(!email) continue;
          // Add additional code here, such as:
          if(validateEmail(email)) {
          } else {
            alert('<?php echo $this->translate("Please enter valid email address."); ?>');
            return;
          }
        }
        
        scriptJquery("#invite_invite_form").hide();
        document.getElementById('invite_loading_cont_overlay').style.display = '';
        
        invitepeopleinvite = scriptJquery.ajax({
          method: 'post',
          'url': en4.core.baseUrl + 'invite/index',
          'data': {
            format: 'html',
            params : scriptJquery(this).serialize(), 
            is_ajax : 1,
          },
          success: function(responseHTML) {
            document.getElementById('invite_loading_cont_overlay').style.display = 'none';
            //scriptJquery("#invite_invite_form").show();
            
            scriptJquery('#invite_invite_form').before(responseHTML);
            //scriptJquery('#invite_invite_form').before("<div id='invite_invite_success_message' class='alert_message'><div class='success_msg'><span><?php //echo $this->translate("Invitations Sent.");  ?></span></div></div>");
            setTimeout(function() {
              formShow();
            }, 7000);
          }
        });
        return false;
      });
      
      function formShow() {
        scriptJquery("#invite_invite_form").show();
        scriptJquery('#recipients').val('');
        scriptJquery('#message').val('<?php echo $this->translate("You are being invited to join our social network."); ?>');
        scriptJquery('#friendship').prop('checked', false);
        scriptJquery('#invite_invite_success_message').remove();
        scriptJquery('#sent_invite').remove();
      }
    });
    

    var inputCSVFile;
    var socialMediaEmail = [];
    var socialMediaName = '';
    function socialMediaInviteContacts(id) {
      //var id = scriptJquery(this).attr('data-rel');
      url = 'invite/index/csvimport/socialMediaName/'+id; 
      if (id == 'windowslive' || id == 'aol') {
        popup_height = '550';
        popup_width = '600';
      } else {
        popup_height = '600';
        popup_width = '987';
      }
      window.open(url, "_popupWindow", 'height='+popup_height+',width='+popup_width+',location=no,menubar=no,resizable=no,status=no,toolbar=no');
    }
    
    AttachEventListerSE('submit', '#inviteImportIds', function(e){
      e.preventDefault();
      inviteUser();
    });
    
    AttachEventListerSE('submit', '#socialuploadfile', function(e){
      var socialEmails = scriptJquery('#socialMediaEmails').val();
      socialMediaEmails = scriptJquery.ajax({
        method: 'post',
        'url': en4.core.baseUrl + 'invite/index/csvimport/',
        'data': {
          format: 'html',
          socialEmails: socialEmails,
          socialMediaName: socialMediaName,
          is_ajax : 1,
        },
        success: function(responseHTML) { 
        
          response = scriptJquery.parseJSON(responseHTML);
          if(response.status == 1) {
            scriptJquery(response.message).appendTo('#script-default-data');
            scriptJquery('#importsend_email').show();
            scriptJquery('#emailimport_content').show();
          }
        }
      });
      return false;
    });

    function checkEmailExists(value){
      var returnV = false;
      var emailsS = scriptJquery('.email_inst_snt');
      for(var i =0 ; i < emailsS.length; i++){
        if(scriptJquery(emailsS[i]).val() == value)	{
          return true;
          break;	
        }
      }
    }

    AttachEventListerSE('submit', '#csvuploadfile', function(e) {

      e.preventDefault();
      var formData = new FormData();
      formData.append('contact', inputCSVFile);
      scriptJquery.ajax({
        xhr:  function() {
          var xhrobj = scriptJquery.ajaxSettings.xhr();
          if (xhrobj.upload) {
            xhrobj.upload.addEventListener('progress', function(event) {
              var percent = 0;
              var position = event.loaded || event.position;
              var total = event.total;
              if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
              }
              //Set progress
            }, false);
          }
          return xhrobj;
        },
        url:  en4.core.baseUrl+'invite/index/csvimport/',
        type: "POST",
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success: function(response) {
          text = JSON.parse(response);
          if(text.status == 1) {
            scriptJquery(".select_file").find("input").val("");
            scriptJquery(text.message).appendTo('#script-default-data');
            scriptJquery('#importsend_email').show();
            scriptJquery('#emailimport_content').show();
            //scriptJquery('#contactImportData').html(text.message);
          }
          scriptJquery('#errorUploadCsvFile').hide();
        }
        });
    });

    function checkcsvfile(input) {
      var url = input.value;
      var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
      if (input.files && input.files[0] && (ext == "csv")) { 
        inputCSVFile = input.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
          scriptJquery('#errorUploadCsvFile').html('');
          scriptJquery('#errorUploadCsvFile').show();
          scriptJquery('#errorUploadCsvFile').html('<div class="core_loading_cont_overlay" id="core_loading_cont_overlay" style="display:block;"></div>');
          scriptJquery('#csvuploadfile').trigger('submit');
          //var formData = new FormData(this);
          //uplaodCSV(input.files[0]);
        }
        reader.readAsDataURL(input.files[0]);
      } else {
        scriptJquery('#errorUploadCsvFile').css('color','red').html('<?php echo $this->translate("Please select a valid CSV file."); ?>');
      }
    }

    function openInviteTab(evt, fieldName) {

      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      
      document.getElementById(fieldName).style.display = "block";
      evt.currentTarget.className += " active";
    }
    
    function showUploadContacts(type) {
      if(type == 'csv') {
        scriptJquery('#socialContacts').removeClass('active');
        scriptJquery('#input_contact').hide();
        scriptJquery('#uploadContacts').addClass('active');
        scriptJquery('#upload_contact').show();
      } else if(type == 'email') {
        scriptJquery('#socialContacts').removeClass('active');
        scriptJquery('#input_contact').hide();
        scriptJquery('#inviteContacts').addClass('active');
        scriptJquery('#add_address').show();
      }
    }

    // Get the element with id="defaultOpen" and click on it
    //document.getElementById("defaultOpen").click();
    
  //   //Yahoo window open call
  //   function inviteYahooData(yahoodata) {
  //     if(yahoodata.length > 0) {
  //       socialMediaName = 'yahoo';
  //       scriptJquery('#socialMediaEmails').val(yahoodata);
  //       scriptJquery('#socialuploadfile').trigger('submit');
  //     }
  //   }
  //   function inviteHotmailData(hotmaildata) {
  //     if(hotmaildata.length > 0) {
  //       socialMediaName = 'hotmail';
  //       scriptJquery('#socialMediaEmails').val(hotmaildata);
  //       scriptJquery('#socialuploadfile').trigger('submit');
  //     }
  //   }
    
  //   //Yahoo window open call
  //   function inviteTwitterData(twitterdata) {
  // 
  //     if(twitterdata.length > 0) {
  //       socialMediaName = 'twitter';
  //       scriptJquery('#socialMediaEmails').val(twitterdata);
  //       scriptJquery('#socialuploadfile').trigger('submit');
  //     }
  //   }
<?php } ?>
</script>
<?php if(!empty($_GET['typesmoothbox'])) { die; } ?>
