<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _csvimport.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php
$importedData = $this->importedData;
$importmethod = $this->importmethod;
?>
<div id="importsend_email" class="invite_import_contacts_modal core-bxs" style="display:none">
  <div class="invite_import_contacts_modal_content  ">
    <span class="ic_close" id="invitepopup_close">&times;</span>
    <div class="invite_import_contactlist">
      <?php if($importmethod == 'facebook') { ?>
        <h2><span><?php echo $this->translate("Facebook"); ?></span></h2>
      <?php } else if($importmethod == 'csv') { ?>
        <h2><span><?php echo $this->translate("CSV Import"); ?></span></h2>
      <?php } else if($importmethod == 'gmail') { ?>
        <h2><span><?php echo $this->translate("Gmail"); ?></span></h2>
      <?php } else if($importmethod == 'yahoo') { ?>
        <h2><i class="fa fa-yahoo"></i> <?php echo $this->translate("Yahoo"); ?></h2>
      <?php } else if($importmethod == 'twitter') { ?>
        <h2><span><?php echo $this->translate("X"); ?></span></h2>
      <?php } else if($importmethod == 'hotmail') { ?>
        <h2><span><?php echo $this->translate("Hotmail"); ?></span></h2>
      <?php } ?>
      <?php if(isset($importedData) && engine_count($importedData)){ ?>
        <div id="emailimport_content" class="invite_import_contactlist_content" style="display:none;">
          <p class="head_line"><?php echo $this->translate("Connect with people you know."); ?></p>
          <p class="discription" id="import_email_description"><?php echo $this->translate("We found %s people from your CSV file. Select the people you'd like to connect to.", engine_count($importedData)); ?></p>
          <div id="emailerror_message" style="display:none;"><?php echo $this->translate("Please choose at least one email id."); ?></div>
          <div class="invite_import_contactlist_search">
            <input type="text" name="" id="namesearch" onkeyup="importContentSearch()" placeholder="<?php echo $this->translate("Search..."); ?>"/>
            <p class="select_all">
              <input type="checkbox" name="checkall" id="checkall" value=""><?php echo $this->translate("Select All"); ?>
            </p>
          </div>
          <div class="contacts_list">
            <ul id="allcontact_list" class="imp-list invite_import_popup_list">
              <?php foreach($importedData as $key => $valS) { ?>
                <li class="invite_import_popup_list_item">
                  <div class="_input">
                    <input name="imcon_<?php echo $key; ?>" id="imcon_<?php echo $key; ?>" class="checkbox_slt_sm_im" type="checkbox" value="">
                    <span></span>
                  </div>
                  <?php if($importmethod == 'twitter'): ?>
                    <div class="_details">
                    <?php if($valS['name']): ?>
                      <p class="name" id="imp_name_<?php echo $key; ?>" title="<?php echo $valS['name']; ?>"><?php echo $valS['name']; ?></p>
                      <?php endif; ?>
                      <p class="email_id" id="imp_email_<?php echo $key; ?>" title="<?php echo $valS['screen_name']; ?>"><?php echo $valS['screen_name']; ?><span style="display:none"><?php echo $valS['user_id']; ?></span></p>
                    </div>
                  <?php else: ?>
                    <div class="_details">
                    <?php if($valS['name']): ?>
                      <p class="name" id="imp_name_<?php echo $key; ?>" title="<?php echo $valS['name']; ?>"><?php echo $valS['name']; ?></p>
                      <?php endif; ?>
                      <p class="email_id" id="imp_email_<?php echo $key; ?>" title="<?php echo $valS['email']; ?>"><?php echo $valS['email']; ?></p>
                    </div>
                  <?php endif; ?>
                </li>
              <?php } ?>
            </ul>
          </div>
          <div class="invite_import_contacts_modal_footer">
            <div></div>
            <div>
              <input class="core_btn" id="importcnt" type="submit" value="Proceed" name="">
            </div>
          </div>
        </div>
      <?php } ?>
      <div class="invite_send_invitions_form send_invitions_form" id="send_invitions_form" style="display:none">
        <form action="" method="post" id="inviteImportIds" enctype="multipart/form-data">
          <div class="torow mailing_row">
            <div class="mailing_label"><?php echo $this->translate("To"); ?></div>
            <div class="mailing_box_holdier">
              <ul class="mailing_box_names_tabs" id="mailing_box_names_tabs" style="display:none;"></ul>
              <input type="text" name="import_emails" placeholder=""  readonly />
            </div>
          </div>
          <?php if(0 && $importmethod != 'twitter'): ?>
            <div class="subjectrow mailing-row">
              <div class="mailing_label"><?php echo $this->translate("Subject*"); ?></div>
              <div class=" mailing_box_holdier">
                <input type="text" name="" id="import_subject">
              </div>
            </div>
          <?php endif; ?>
          <div class="messagerow mailing-row">
            <div class="mailing_label"><?php echo $this->translate("Message*"); ?></div>
            <div class="mailing_box_holdier">
              <textarea id="import_message"><?php echo $this->translate("Hi, I would like to invite you to join me at %s",$_SERVER['HTTP_HOST']); ?></textarea>
            </div>
          </div>
          <?php if($importmethod == 'csv') { ?>
            <div class="messagerow mailing-row">
              <input type="checkbox" name="friendship" id="friendship" placeholder="" /><?php echo $this->translate("Send a friend request if the user(s) join(s) the network"); ?>
            </div>
          <?php } ?>
          <input type="hidden" name="importEmails" id="importEmails" />
          <div class="invite_import_contacts_modal_footer">
            <div class="mailing_footer_back" id="mailing_footer_back"><input type="button" value="Back" /></div>
            <div class="mailing_footer_send"><input class="core_btn" id="import_invitesentemail" type="submit" value="Send" /></div>
          </div>
        </form>
        <div class="core_loading_cont_overlay" id="invite_loading_contoverlay" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>

<script type="application/javascript">
//static search function
function importContentSearch() {
  // Declare variables
  var namesearch, namesearchfilter, allcontact_list, allcontact_list_li, allcontact_list_p, i;
  namesearch = document.getElementById('namesearch');
  namesearchfilter = namesearch.value.toUpperCase();
  allcontact_list = document.getElementById("allcontact_list");
  allcontact_list_li = allcontact_list.getElementsByTagName('li');
  
  // Loop through all list items, and hide those who don't match the search query
  for (i = 0; i < allcontact_list_li.length; i++) {
    allcontact_list_p = allcontact_list_li[i].getElementsByTagName("p")[0];
    if (allcontact_list_p.innerHTML.toUpperCase().indexOf(namesearchfilter) > -1) {
        allcontact_list_li[i].style.display = "";
    } else {
        allcontact_list_li[i].style.display = "none";
    }
  }
}
function inviteValidateEmail(email) {
  const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test( email );
} 
function inviteUser(){
  var importEmails = scriptJquery('#importEmails').val();
  var str_array = importEmails.split(',');
  //var import_subject = scriptJquery('#import_subject').val();
  var import_message = scriptJquery('#import_message').val();
  //var friendship = scriptJquery('#friendship').checked;
  
  if(scriptJquery('input[name="friendship"]:checked').val() == 'on') {
    var friendship = 1;
  } else {
    var friendship = 0;
  }

  if(str_array.length == 1) {
    alert('Please enter at least one email.');
    return;
  }
  if(import_message.length == '') {
    alert('Please enter a message.');
    return;
  }
  <?php if($importmethod != 'twitter'): ?>
    for(var i = 0; i < str_array.length; i++) {
      // Trim the excess whitespace.
      let email = str_array[i].replace(/^\s*/, "").replace(/\s*$/, "");
      if(!email) continue;
      // Add additional code here, such as:
      
      if(inviteValidateEmail(email)) {
      } else {
        alert('Please enter valid email address.' + email);
        return;
      }
    }
  <?php endif; ?>
  document.getElementById('invite_loading_contoverlay').style.display='block';
  scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + 'invite/index/importinvite',
    'data': {
      format: 'html',
      importEmails: importEmails,
      //import_subject: import_subject,
      import_message: import_message, 
      import_method: '<?php echo $importmethod ?>',
      friendship: friendship,
      is_ajax : 1,
    },
    success: function(responseHTML) { 
      var response = scriptJquery.parseJSON( responseHTML );
      document.getElementById('invite_loading_contoverlay').style.display='none';
      scriptJquery('#inviteImportIds').fadeOut("slow", function(){
        scriptJquery('#inviteImportIds').remove();
        scriptJquery('#invitepopup_close').trigger('click');
      });
      document.getElementById('send_invitions_form').innerHTML = "<div class='invite_invite_success_message alert_message'><div class='success_msg'><span>Invitations Sent Successfully.</span></div></div>";
    }
  });
  return false;
};
//Click on import button and show pop up
scriptJquery('#importcnt').click(function(e) {
		
		e.preventDefault();

		var elem = scriptJquery('.checkbox_slt_sm_im');

		var openerElem = window.opener; 
		
		var emailIds = [] ;
		var counter = 0;
		
		var importEmails = scriptJquery('#importEmails').val();
    var emailToContent = '';
    var importuserEmail = '';
		
		for(var i=0;i < elem.length; i++) {
		
      var id = scriptJquery(elem[i]).attr('id').replace('imcon_','');
      if(scriptJquery(elem[i]).prop('checked')) {
      
        //if(openerElem.checkEmailExists(scriptJquery('#imp_email_'+id).html().trim()))
          //continue;
          
        importuserEmail = scriptJquery('#imp_email_'+id).html().trim().replace(/<[^>]*>?/gm, '');
        
        if(scriptJquery('#imp_email_'+id).find("span").length > 0)
          importEmails += scriptJquery('#imp_email_'+id).find("span").html().trim() + ",";
        else
          importEmails += scriptJquery('#imp_email_'+id).html().trim() + ",";
        emailToContent += '<li id="useremail_'+importuserEmail+'"><div class="names_tabs"><div class="mailing_name" title="'+importuserEmail+'">'+scriptJquery('#imp_name_'+id).html().trim()+'</div><div id="mailing_name_close" class="mailing_name_close" data-rel="'+importuserEmail+'">x</div></div></li>';
        emailIds[counter] = scriptJquery('#imp_email_'+id).html().trim()+'||'+scriptJquery('#imp_name_'+id).html().trim();
        counter++;
      }
		}
		
		if(emailIds.length == 0) {
      if(document.getElementById('emailerror_message'))
        document.getElementById('emailerror_message').style.display = 'block';
		} else {
		
      if(document.getElementById('emailerror_message'))
        document.getElementById('emailerror_message').style.display = 'none';
        
      scriptJquery('#emailimport_content').hide();
        
      scriptJquery('#importsend_email').show();
      document.getElementById('send_invitions_form').style.display = 'block';
      
      if(emailToContent) {
        scriptJquery('#mailing_box_names_tabs').show();
        scriptJquery('#mailing_box_names_tabs').html(emailToContent);
      }
      
      if(importEmails) {
        scriptJquery('#importEmails').val(importEmails);
      }
    }

		//openerElem.printEmail(emailIds);
		//window.close();
});

AttachEventListerSE('click', '#mailing_footer_back', function(){
  scriptJquery('#send_invitions_form').hide();
  scriptJquery('#emailimport_content').show();
  scriptJquery('#importEmails').val('');
  
});

AttachEventListerSE('click', '#invitepopup_close', function(){
  scriptJquery('#importsend_email').remove();
  scriptJquery('#invite_import_popup_list_container').remove();
});

AttachEventListerSE('click','#mailing_name_close',function(){

  var dataid = scriptJquery(this).attr('data-rel');
  scriptJquery(this).closest('li').remove();
  var partsOfStr = scriptJquery('#importEmails').val().split(',');
  partsOfStr = scriptJquery.grep(partsOfStr, function(value) {
    return value != dataid;
  });
  partsOfStr = partsOfStr.join();
  scriptJquery('#importEmails').val(partsOfStr);
});

scriptJquery('#checkall').change(function(e){
	scriptJquery('.checkbox_slt_sm_im').prop('checked',scriptJquery(this).prop("checked"));
	if(document.getElementById('emailerror_message'))
    document.getElementById('emailerror_message').style.display = 'none';
});

AttachEventListerSE('change','.checkbox_slt_sm_im',function() {

	if(!scriptJquery(this).prop('checked')) {
		scriptJquery('#checkall').prop('checked',false);
// 		if(document.getElementById('emailerror_message'))
//         document.getElementById('emailerror_message').style.display = 'block';
	} else {
		if(!inviteCheckAllsmCheckbox()) {
			scriptJquery('#checkall').prop('checked',false);
      if(document.getElementById('emailerror_message'))
        document.getElementById('emailerror_message').style.display = 'none';
		} else {
			scriptJquery('#checkall').prop('checked',true);
			if(document.getElementById('emailerror_message'))
        document.getElementById('emailerror_message').style.display = 'none';
		}
	}
});

function inviteCheckAllsmCheckbox() {

	var check = true;	
	var elem = scriptJquery('.checkbox_slt_sm_im');
	for(var i =0 ; i < elem.length;i++){
		if(!scriptJquery(elem[i]).prop('checked')){
			check  = false;
			break;	
		}
	}
	return check;	
}
</script>
