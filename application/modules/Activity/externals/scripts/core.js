/* $Id:core.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

var activityfeedactive;
function getMentionDataActivity(url,sharingPostText,orginalSharingText,formObj,getMentionText){
  if(scriptJquery('textarea#activity_body').attr('data-mentions-input')){
    scriptJquery('textarea#activity_body').mentionsInput('val', function(data) {
       submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,data);
    });
  }else{
      submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,'');
  }
}
function submitActivityFeedWithAjax(url,sharingPostText,orginalSharingText,formObj,getMentionText){
  if(typeof getMentionText == 'undefined')
  {
    getMentionDataActivity(url,sharingPostText,orginalSharingText,formObj,getMentionText);
    return;
  }
  url  = url +'/userphotoalign/'+userphotoalign;
  scriptJquery('#file_multi').remove();
  scriptJquery(formObj).addClass("_request-going");
  var formData = new FormData(formObj);
	formData.append('is_ajax', 1);
  formData.append('subject',en4.core.subject.guid);
  formData.append('body',getMentionText);
  var hashtag = scriptJquery('#hashtagtext').val();

  //page feed work
  var elemParent = scriptJquery('#activity_post_box_status').find('.custom_switch_val').find('._feed_change_option_a');
  if(elemParent.length){
    formData.append('postingType', elemParent.attr('data-rel'));
  }

  //var data = composeInstance.getForm().toQueryString();
  if(url.indexOf('&') <= 0)
    url = url+'?';
  url = url+'is_ajax=true';
  if(hashtag)
    url = url+"&hashtag="+hashtag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage;

  if(typeof itemSubjectGuid != "undefined")
    var itemSubject = itemSubjectGuid;
  else
    var itemSubject = "";

  url = url+'&subjectPage='+itemSubject;

  scriptJquery('#compose-submit').html(sharingPostText);
  activityfeedactive = scriptJquery.ajax({
      type:'POST',
      url: url,
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(responseHTML){
        scriptJquery(formObj).removeClass("_request-going");
        try{

          var parseJson = scriptJquery.parseJSON(responseHTML);
          if(parseJson.status){
            if(hashtag && !parseJson.existsHashTag){
               var html = "Your post has been added to your <a href='"+parseJson.userhref+"'>profile</a> but won't appear in this feed because it doesn't mention ‪#"+hashtag+".";
              scriptJquery("<div class='schedule_post_cnt success_msg mb-3'><span>"+html+"</span></div>").insertBefore('.activity_noresult_tip');
              setTimeout(function() {scriptJquery('.schedule_post_cnt').remove();}, 5000);
            }else if(parseJson.approveFeed != ""){
                var html = parseJson.approveFeed;
              scriptJquery("<div class='schedule_post_cnt success_msg  mb-3'><span>"+html+"</span></div>").insertBefore('.activity_noresult_tip');
              setTimeout(function() {scriptJquery('.schedule_post_cnt').remove();}, 5000);
            }else if(parseJson.videoProcess == 1){
              var html = en4.core.language.translate("Your feed is currently being processed - you will be notified when it is ready to be viewed.");
              scriptJquery("<div class='schedule_post_cnt success_msg mb-3'><span>"+html+"</span></div>").insertBefore('.activity_noresult_tip');
              setTimeout(function() {scriptJquery('.schedule_post_cnt').remove();}, 30000);
            }else if(parseJson.scheduled_post && parseJson.scheduled_post_time){
              var html = en4.core.language.translate("Your post successfully scheduled on ") + parseJson.scheduled_post_time;
              scriptJquery("<div class='schedule_post_cnt success_msg  mb-3'><span>"+html+"</span></div>").insertBefore('.activity_noresult_tip');
              setTimeout(function() {scriptJquery('.schedule_post_cnt').remove();}, 5000);
            }else{
              scriptJquery('#activity-feed').prepend(parseJson.feed);
              Smoothbox.bind(scriptJquery('#activity-feed'));
            }
            scriptJquery('.composer_crosspost_toggle').removeClass('composer_crosspost_toggle_active');
            scriptJquery('.activity_content_pulldown').hide();
            scriptJquery('.activity_content_pulldown_wrapper').find('a').removeClass('activity_post_media_options_active');
            scriptJquery('.activity_content_pulldown_list').find('input[type=checkbox]').prop('checked',false);
            // dont set if on action view page.
            if(typeof ActivityUpdateHandler.options != 'undefined')
            ActivityUpdateHandler.options.last_id = parseJson.last_id;
          }else{
             en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
             scriptJquery('#compose-submit').html(orginalSharingText);
            // clearInterval(dotsAnimationWhenPostingInterval);
             return;
          }
        }catch(e){
           en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
           scriptJquery('#compose-submit').html(orginalSharingText);
          // clearInterval(dotsAnimationWhenPostingInterval);
             return;
        }
        activitytooltip();
        scriptJquery('.activity_noresult_tip').hide();
        resetComposerBoxStatus();
        hideStatusBoxSecond();
        scriptJquery('#compose-submit').html(orginalSharingText);
        en4.core.runonce.trigger();
        if(scriptJquery('#hashtagtext').val()) {
          composeInstance.setContent('#'+scriptJquery('#hashtagtext').val());
        }
        scriptJquery('.activity_post_loader').addClass('d-none');
        activateFunctionalityOnFirstLoad();
      },
     error: function(data){
        scriptJquery(formObj).removeClass("_request-going");
        en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
        scriptJquery('#compose-submit').html(orginalSharingText);
      },
    });
}
AttachEventListerSE('click','.composer_crosspost_toggle',function(e){
  if(scriptJquery(this).hasClass('composer_crosspost_toggle_active')){
    scriptJquery(this).removeClass('composer_crosspost_toggle_active');
    scriptJquery('#crosspostVal').val('');
  }else{
    scriptJquery(this).addClass('composer_crosspost_toggle_active') ;
    scriptJquery('#crosspostVal').val(1);
  }
});

AttachEventListerSE('click','.activity_approve_btn',function(){
  var url = scriptJquery(this).closest('form').attr('action');
  var actionid = scriptJquery(this).attr('data-url');
  scriptJquery('#activity-item-'+actionid).fadeOut("slow", function(){
    scriptJquery('#activity-item-'+actionid).remove();
  });
  ajaxsmoothboxclose();
  scriptJquery.post(url,{approve:"1"},function(){});
})
var dotsAnimationWhenPosting = 0,dotsAnimationWhenPostingInterval;
function dotsAnimationWhenPostingFn(sharingPostText)
{
    if(dotsAnimationWhenPosting < 3)
    {
        if(dotsAnimationWhenPosting == 0)
          scriptJquery('#compose-submit').text(sharingPostText+'.');
        else if(dotsAnimationWhenPosting == 1)
          scriptJquery('#compose-submit').text(sharingPostText+'..');
        else
          scriptJquery('#compose-submit').text(sharingPostText+'...');
        dotsAnimationWhenPosting++;
    }
    else
    {
        scriptJquery('#compose-submit').text(sharingPostText);
        dotsAnimationWhenPosting = 0;
    }
}
AttachEventListerSE('click','.close_parent_notification_activity',function(e){
  scriptJquery(this).closest('.parent_notification_activity').remove();
})
AttachEventListerSE('click','.activity_popup_preview',function(e){
   e.preventDefault();
    en4.core.showError('<div class="activity_img_preview_popup"><div class="activity_img_preview_popup_img"><img src="'+scriptJquery(this).attr('href')+'"> </div><div class="activity_img_preview_popup_btm"><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button></div></div>');
		scriptJquery ('.activity_img_preview_popup').parent().parent().addClass('activity_img_preview_popup_wrapper');
});
AttachEventListerSE('click','.buysell_img_a',function(){
  var image = scriptJquery(this).find('img').attr('src');
  scriptJquery('.activity_sellitem_popup_photos_strip').find('.selected').removeClass('selected');
  scriptJquery(this).find('img').addClass('selected');
  scriptJquery('.selected_image_buysell').attr('src',image);
});
AttachEventListerSE('click','.mark_as_sold_buysell',function(){
  var sold = scriptJquery(this).attr('data-sold');
  var href = scriptJquery(this).attr('data-href');
  scriptJquery('.mark_as_sold_buysell_'+href).removeClass('mark_as_sold_buysell');
  scriptJquery('.mark_as_sold_buysell_'+href).html('<i class="fa fa-check"></i>' + sold);
   var activitybuysellsold = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/buysellsold/action_id/'+href,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
        //silence
    },
   error: function(data){

    },
  });
});
AttachEventListerSE('click','.icon_activity_save, .icon_activity_unsave',function(){
  var save = scriptJquery(this).attr('data-save');
  var unsave = scriptJquery(this).attr('data-unsave');
  var actionid = scriptJquery(this).attr('data-actionid');
  if(!save || !unsave || !actionid)
    return false;
  if(scriptJquery(this).hasClass('icon_activity_save')){
    scriptJquery(this).find('span').html(unsave);
    scriptJquery(this).removeClass('icon_activity_save').addClass('icon_activity_unsave');
  }else{
    scriptJquery(this).find('span').html(save);
    scriptJquery(this).addClass('icon_activity_save').removeClass('icon_activity_unsave');
  }
  var that = this;
  var elem = scriptJquery('.activity_active_tabs');
  if(elem.length)
  {
     var data = elem.find('a').attr('data-src');
     if(data == 'saved_feeds')
      scriptJquery('#activity-item-'+actionid).fadeOut("slow", function(){
        scriptJquery('#activity-item-'+actionid).remove();
        if(scriptJquery('#activity-feed').children().length)
       scriptJquery('.activity_noresult_tip').hide();
      else
       scriptJquery('.activity_noresult_tip').show();
      });

  }
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/savefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     //silence

    },
   error: function(data){

    },
  });
});
AttachEventListerSE('click','.activity_feed_link',function(e){
  e.preventDefault();
  en4.core.showError("<div class='activity_feedlink_popup'><div class='activity_feedlink_popup_head'>"+en4.core.language.translate('Permalink of this Post')+"</div><div class='activity_feedlink_popup_cont'><p>"+en4.core.language.translate('Copy link of this feed:')+"</p><p>" + '<input type="text" value="'+this.href+'" id="activity_link_feed_sel"></p>' + '<p><button onclick="openHrefWindow(\''+this.href+'\');">'+en4.core.language.translate("Go to this feed")+' </button><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button></p></div></div>');
  scriptJquery('#activity_link_feed_sel').select();
	scriptJquery ('.activity_feedlink_popup').parent().parent().addClass('activity_feedlink_popup_wrapper');
});
function openHrefWindow(href){
  Smoothbox.close();
  loadAjaxContentApp(href);
}

AttachEventListerSE('click','.commentable',function(e){
  e.preventDefault();
  var url = scriptJquery(this).attr('data-href');
  var enable = scriptJquery(this).attr('data-save');
  var disable = scriptJquery(this).attr('data-unsave');
  var commentable = scriptJquery(this).attr('data-commentable');
  if(!enable || !disable)
    return false;
  if(commentable == 0){
    scriptJquery(this).find('span').html(disable);
    scriptJquery(this).attr('data-commentable',1);
  }else{
    scriptJquery(this).find('span').html(enable);
    scriptJquery(this).attr('data-commentable',0);
  }
  var that = this;
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: url,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     if(responseHTML){
       var jsonObj = scriptJquery.parseJSON(responseHTML);
       if(jsonObj.status){
         var action_id = jsonObj.action_id;
         var feed  = jsonObj.feed;
         scriptJquery('#activity-item-'+action_id).replaceWith(feed);
       }
     }

    },
   error: function(data){

    },
  });
});
AttachEventListerSE('click','.icon_activity_hide',function(){
  var name = scriptJquery(this).attr('data-name');
  var actionid = scriptJquery(this).attr('data-actionid');
  var subjectid = scriptJquery(this).attr('data-subjectid');
  if(!name || !actionid || !subjectid)
    return false;

  var parent = scriptJquery(this).closest('.activity_feed_header').parent();
  parent.find('.activity_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.activity_comments').hide();
  parent.find('.activity_hide').remove();
  parent.append('<div class="activity_hide block"><a href="javasctipt:;" class="fas fa-times activity_hide_close activity_hide_close_fn font_color" title="Close"></a><p>'+en4.core.language.translate("You won\'t see this post in Feed.")+' <a href="javascript:;" data-name="'+name+'" class="activity_undo_hide_feed" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p><div><p><a href="javascript:;" class="icon_activity_hide_all_feed" data-name="'+name+'" data-actionid="'+actionid+'">'+en4.core.language.translate("Hide all from")+'  '+name+'</a></p></div></div>');

  var that = this;
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/hidefeed/action_id/'+actionid+'/subject_id/'+subjectid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });
});
AttachEventListerSE('click','.activity_report_feed',function(){
  var name = scriptJquery(this).attr('data-name');
  var actionid = scriptJquery(this).attr('data-actionid');
  var guid = scriptJquery(this).attr('data-guid');
  if(!name || !actionid || !guid)
    return false;
  var reportLink = en4.core.baseUrl + "report/create/subject/"+guid;
  var parent = scriptJquery(this).closest('.activity_feed_header').parent();
  parent.find('.activity_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.activity_comments').hide();
  parent.find('.activity_hide').remove();
  parent.append('<div class="activity_hide"><a href="javasctipt:;" class="fas fa-times activity_hide_close activity_hide_close_fn font_color" title="Close"></a><p>'+en4.core.language.translate("You won\'t see this post in Feed.")+' <a href="javascript:;" data-name="'+name+'" class="activity_undo_hide_feed" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p><div><p>'+en4.core.language.translate("If you find it offensive, please")+' <a href="javascript:;" onclick="openSmoothBoxInUrl(&#39;'+reportLink+'&#39;)" class="activity_report_feed" >'+en4.core.language.translate("file a report.")+'</a></p></div></div>');

  var that = this;
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: en4.core.baseUrl + '/activity/ajax/hidefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });

});
AttachEventListerSE('click','.activity_hide_close_fn',function(e){
	scriptJquery(this).closest('li').remove();
});
AttachEventListerSE('click','.icon_activity_hide_all',function(e){
  var name = scriptJquery(this).attr('data-name');
  var actionid = scriptJquery(this).attr('data-actionid');
  if(!name || !actionid)
    return false;

  var parent = scriptJquery(this).closest('.activity_feed_header').parent();
   parent.find('.activity_feed_header').hide();
  parent.find('.feed_item_body').hide();
	parent.find('.activity_comments').hide();
  parent.find('.activity_hide').remove();
  parent.append('<div class="activity_hide"><a href="javascript:;" class="fas fa-times activity_hide_close activity_hide_close_fn font_color" title="Close"></a><p>'+en4.core.language.translate("You won\'t see")+' '+name+en4.core.language.translate(" post in Feed.")+'  <a href="javascript:;" data-name="'+name+'" class="activity_undo_hide_feed_all" data-actionid="'+actionid+'">'+en4.core.language.translate("Undo")+'</a></p></div>');


  var list = getAllElementsWithAttributeElem('data-activity-feed-item');
  var lists = (list.join(','));
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/hidefeed/action_id/'+actionid+'/type/user/lists/'+lists,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      if(responseHTML){
        var json = scriptJquery.parseJSON(responseHTML);
        if(json.list){
          var list = json.list;
          for(i=0;i<list.length;i++){
            if(!scriptJquery('#activity-item-'+list[i]).find('.activity_hide').length)  {
                scriptJquery('#activity-item-'+list[i]).hide();
            }
          }
        }
      }
    },
   error: function(data){

    },
  });
});
function getAllElementsWithAttributeElem(attribute) {
    var matchingElements = [];
    var values = [];
    var allElements = document.getElementsByTagName('*');
    for (var i = 0; i < allElements.length; i++) {
      if (allElements[i].getAttribute(attribute)) {
        // Element exists with attribute. Add to array.
        matchingElements.push(allElements[i]);
        values.push(allElements[i].getAttribute(attribute));
        }
      }
    return values;
  }
AttachEventListerSE('click','.activity_undo_hide_feed_all',function(e){
  var name = scriptJquery(this).attr('data-name');
  var actionid = scriptJquery(this).attr('data-actionid');
  if(!name || !actionid)
    return false;
  var parent = scriptJquery(this).closest('li');
  parent.find('.activity_feed_header').show();
  parent.find('.feed_item_body').show();
	 parent.find('.activity_comments').show();
  parent.find('.activity_hide').remove();
  var that = this;
  var list = getAllElementsWithAttributeElem('data-activity-feed-item');
  var lists = (list.join(','));
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/hidefeed/action_id/'+actionid+'/remove/true/type/user/lists/'+lists,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      if(responseHTML){
        var json = scriptJquery.parseJSON(responseHTML);
        if(json.list){
          var list = json.list;
          for(i=0;i<list.length;i++){
            if(!scriptJquery('#activity-item-'+list[i]).find('.activity_hide').length)  {
                scriptJquery('#activity-item-'+list[i]).show();
            }
          }
        }
      }
    },
   error: function(data){

    },
  });
});
AttachEventListerSE('click','.icon_activity_hide_all_feed',function(){
  var actionid = scriptJquery(this).attr('data-actionid');
  scriptJquery('.icon_activity_hide_all_'+actionid).trigger('click');
});
AttachEventListerSE('click','.activity_undo_hide_feed',function(e){
  var name = scriptJquery(this).attr('data-name');
  var actionid = scriptJquery(this).attr('data-actionid');
  if(!name || !actionid)
    return false;
  var parent = scriptJquery(this).closest('li');
	parent.find('.activity_feed_header').show();
  parent.find('.feed_item_body').show();
	 parent.find('.activity_comments').show();
  parent.find('.activity_hide').remove();
  var that = this;
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/hidefeed/action_id/'+actionid+'/remove/true',
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
    },
   error: function(data){

    },
  });
});
function resetComposerBoxStatus(){
  composeInstance.getTray().empty();
  Object.entries(composeInstance.plugins).forEach(function([key,plugin]) {
    if(key != "targetpost")
    plugin.detach();
    plugin.active = false;
    scriptJquery('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
  });
  composeInstance.signalPluginReady(false);
  scriptJquery('.resetaftersubmit').val('');
  composeInstance.setContent('');
  scriptJquery('.highlighter').html('');
  scriptJquery('#activity_body').css('height','auto');
  scriptJquery('#toValues-element, #tag_friend_cnt, #locValues-element, #location_elem_act').html('');
  scriptJquery('#compose-tray').hide();
  if(scriptJquery('#activity_tag').hasClass('active')){
   scriptJquery('#activity_tag').removeClass('active');
   scriptJquery('.activity_post_tag_cnt').hide();
  }

  scriptJquery('.activity_post_page_container').hide();
  if(scriptJquery('#activity_location').hasClass('active')){
    scriptJquery('#activity_location').removeClass('active');
    scriptJquery('.activity_post_location_container').hide();
  }

  //Feeling Work
  if(scriptJquery('#activity_feelings').hasClass('active')){
    scriptJquery('#activity_feelings').removeClass('active');
    scriptJquery('.activity_post_feeling_container').hide();
    scriptJquery('#feeling_elem_act').hide();
    scriptJquery('#feelingActType').html('');
    scriptJquery('#feelingActType').hide();
  }

// (function() { // START NAMESPACE
// var $ = 'id' in document ? document.id : window.$;

  if(scriptJquery('#activity_shedulepost').hasClass('active')){
    scriptJquery('#activity_shedulepost').removeClass('active');
    scriptJquery('#scheduled_post').hide();
  }
  scriptJquery('.fileupload-cnt').html('');
  if(typeof removeTargetPostValues == 'function')
    removeTargetPostValues();
  scriptJquery('#tag_location').css('display','inline-block');
  scriptJquery('#dash_elem_act, #tag_friend_cnt, #location_elem_act').hide();

  if(scriptJquery('#hashtagtext').val()) {
    scriptJquery('#activity_body').val('#'+scriptJquery('#hashtagtext').val()).trigger('keyup');

    //composeInstance.setContent('#'+scriptJquery('#hashtagtext').val()).trigger('keyup');
  }
}
AttachEventListerSE('submit','#activity_settings_form',function(e){
  e.preventDefault();
    var checkbox_value = "";
    scriptJquery(".commentcheckbox").each(function () {
        var ischecked = scriptJquery(this).is(":checked");
        if (ischecked) {
            checkbox_value += scriptJquery(this).val() + ",";
        }
    });
  if(!checkbox_value)
    return false;
  var that = this;
  scriptJquery(this).find('.core_loading_cont_overlay').show();
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/settingremove/user/'+checkbox_value,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      //ajaxsmoothboxclose();
      if(responseHTML){
        scriptJquery(that).html('<p style="margin:10px;">Your changes saved successfully.</p>');
        location.reload();
      }
    },
   error: function(data){

    },
  });
});
 en4.core.runonce.add(function() {
  //tooltip
   activitytooltip();
});
//edit feed from delete
AttachEventListerSE('click','.edit_feed_edit',function(e){
  e.preventDefault();
  var id = scriptJquery('#activity_adv_delete').find('.hidden_actn').val();
  ajaxsmoothboxclose();
  setTimeout(function() {scriptJquery('#activity_edit_'+id).trigger('click');}, 600);
});
AttachEventListerSE('submit','#activity_adv_delete',function(e){
  e.preventDefault();
   var id = scriptJquery('#activity_adv_delete').find('.hidden_actn').val();
   if(typeof itemSubjectGuid != "undefined")
    var itemSubject = itemSubjectGuid;
  else
    var itemSubject = "";

  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/index/delete/action_id/'+id+'/subjectPage/'+itemSubject,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      ajaxsmoothboxclose();
      if(responseHTML){
        ajaxsmoothboxclose();
        scriptJquery('#activity-item-'+id).fadeOut("slow", function(){
          scriptJquery('#activity-item-'+id).remove();
          if(!scriptJquery('#activity-feed >li').length)
            scriptJquery('.activity_noresult_tip').show();
        });
      }
    },
   error: function(data){

    },
  });
});
AttachEventListerSE('submit','#activity_adv_comment_delete',function(e){
  e.preventDefault();
  var id = scriptJquery('#activity_adv_comment_delete').find('.hidden_cmnt').val();
  var action_id = scriptJquery('#activity_adv_comment_delete').find('.hidden_actn').val();
  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: scriptJquery( this ).attr( 'action' ),
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML) {
      scriptJquery("#comment-"+id).remove();
      scriptJquery('.comment_stats_'+action_id).find('.comment_btn_open').html(responseHTML);
      ajaxsmoothboxclose();
    },
   error: function(data){

    },
  });
});
function isTouchDevice(){
    return true == ("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch);
}

function activitytooltip(){
  if(typeof displayCommunityadsCarousel == "function"){
    displayCommunityadsCarousel()
  }
  feedUpdateFunction();
}
//reschedule post
AttachEventListerSE('click','.activity_reschedule_post',function(e){
  scriptJquery('.activity_shedulepost_edit_overlay').remove();
  scriptJquery('.activity_shedulepost_edit_select').remove();
  e.preventDefault();
  var action_id = scriptJquery(this).data('actionid');
  var value = scriptJquery(this).data('value');
  var html = '<div class="activity_shedulepost_edit_overlay activity_popup_overlay"></div><div class="activity_shedulepost_edit_select  activity_popup"><div class="activity_popup_header">Schedule Post</div><div class="activity_popup_cont"><b>Schedule Your Post</b><p>Select date and time on which you want to publish your post.</p><div class="activity_time_input_wrapper"><div id="datetimepicker_edit" class="input-append date activity_time_input"><input type="text" name="scheduled_post" id="scheduled_post_edit" value="'+value+'" /><span class="add-on" title="Select Time" ><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span></div><input type="hidden" id="schedule_post_reschedule_action_id" value="'+action_id+'"><div class="activity_error activity_shedulepost_edit_error"></div></div></div><div class="activity_popup_btns activity_shedulepost_edit_btns"><button type="submit" class="schedule_post_schedue_edit">Reshedule</button><button class="close schedule_post_close_edit">Cancel</button></div></div>';
  scriptJquery(html).appendTo('#append-script-data');
  scriptJquery('#schedule_post_reschedule_action_id').val(action_id);
  makeDateTimePicker();
  //activitytooltip();
});
AttachEventListerSE('click','.schedule_post_close_edit',function(e){
  e.preventDefault();
  scriptJquery('.activity_shedulepost_edit_overlay').remove();
  scriptJquery('.activity_shedulepost_edit_select').remove();
});
AttachEventListerSE('click','.schedule_post_schedue_edit',function(e){
  var value = scriptJquery('#scheduled_post_edit').val();
  if(scriptJquery('.activity_shedulepost_edit_error').css('display') == 'block' || !value){
    return;
   }
   e.preventDefault();
   var actionid = scriptJquery('#schedule_post_reschedule_action_id').val();
   value = value.replace(/\//g,'_');
   scriptJquery('.activity_shedulepost_edit_btns > buttons').prop('disabled',true);
  scriptJquery.ajax({
    type:'POST',
    url: 'activity/index/reschedule-post/action_id/'+actionid+'/value/'+value,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
      responseHTML = scriptJquery.parseJSON(responseHTML);
      if(responseHTML.status){
        scriptJquery('.activity_shedulepost_edit_overlay').remove();
        scriptJquery('.activity_shedulepost_edit_select').remove();
        scriptJquery('#activity-item-'+actionid).fadeOut("slow", function(){
           scriptJquery('#activity-item-'+actionid).replaceWith(responseHTML.feed);
           scriptJquery('#activity-item-'+actionid).fadeIn("slow");
           activitytooltip();
           return;
        });
      }else{
        alert('Something went wrong, please try again later.');
        scriptJquery('.activity_shedulepost_edit_btns > buttons').prop('disabled',false);
        return;
      }
    },
   error: function(data){
     alert('Something went wrong, please try again later.');
     scriptJquery('.activity_shedulepost_edit_btns > buttons').prop('disabled',false);
     return;
    },
  });
});

  var CommentLikesTooltips;
  en4.core.runonce.add(function() {
    // Add hover event to get likes
    AttachEventListerSE('mouseover','.comments_comment_likes', function(event) {
      var el = scriptJquery(event.target);
      if( !el.data('tip-loaded', false) ) {
        el.data('tip-loaded', true);
        el.data('tip:title', 'Loading...');
        el.data('tip:text', '');
        var id = el.get('id').match(/\d+/)[0];
        // Load the likes
        var url = 'activity/index/get-likes';
        var req = scriptJquery.ajax({
          url : url,
          data : {
            format : 'json',
            //type : 'core_comment',
            action_id : el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
            comment_id : id
          },
          success : function(responseJSON) {
            el.data('tip:title', responseJSON.body);
            el.data('tip:text', '');
            CommentLikesTooltips.elementEnter(event, el); // Force it to update the text
          }
        });
      }
    });
    // Add tooltips
    CommentLikesTooltips = new Tips(scriptJquery('.comments_comment_likes'), {
      fixed : true,
      className : 'comments_comment_likes_tips',
      offset : {
        'x' : 48,
        'y' : 16
      }
    });
    // Enable links in comments
    scriptJquery('.comments_body').enableLinks();
  });
(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
en4.activity = {

  load : function(next_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;
    if(typeof itemSubjectGuid != "undefined")
    var itemSubject = itemSubjectGuid;
  else
    var itemSubject = "";

    document.getElementById('feed_viewmore').style.display = 'none';
    document.getElementById('feed_loading').style.display = '';
    var hashTag = scriptJquery('#hashtagtext').val();
    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/widget/feed?search='+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
      data : {
        //format : 'json',
        'maxid' : next_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : subject_guid,
        'filterFeed':scriptJquery('.activity_filter_tabs .active > a').attr('data-src'),
      }
      /*
      success : function(){
        document.getElementById('feed_viewmore').style.display = '';
        document.getElementById('feed_loading').style.display = 'none';
      }*/
    }), {
      'element' : scriptJquery('#activity-feed'),
      'updateHtmlMode' : 'append'
    });
  },

  like : function(action_id, comment_id) {
    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/index/like',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'updateHtmlMode': 'comments'
      'element' : scriptJquery('#comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'activityivity'
    });
  },
  unlike : function(action_id, comment_id) {
    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/index/unlike',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      //'element' : sc('activity-item-'+action_id),
      //'updateHtmlMode': 'comments'
      'element' : scriptJquery('#comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'activityivity'
    });
  },

  comment : function(action_id, body) {
    if( body.trim() == '' )
    {
      return;
    }

    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/index/comment',
      data : {
        format : 'json',
        action_id : action_id,
        body : body,
        subject : en4.core.subject.guid
      }
    }), {
      //'updateHtmlMode': 'comments'
      'element' : scriptJquery('#comment-likes-activity-item-'+action_id),
      'updateHtmlMode': 'activityivity'
    });
  },

  attachComment : function(formElement){
    var bind = this;
    formElement.addEvent('submit', function(event){
      event.stop();
      bind.comment(formElement.action_id.value, formElement.body.value);
    });
  },

  viewComments : function(action_id){
    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/index/viewComment',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : scriptJquery('#activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  viewLikes : function(action_id){
    (scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/index/viewLike',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : scriptJquery('#activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },
 
  hideNotifications : function(reset_text) {
    en4.core.request.send(scriptJquery.ajax({
      'url' : en4.core.baseUrl + 'activity/notifications/hide'
    }));
    scriptJquery('#updates_toggle').removeClass('new_updates');
    if(scriptJquery('#update_count').length)
    scriptJquery('#update_count').removeClass('minimenu_update_count_bubble_active');
    /*
    var notify_link = $('core_menu_mini_menu_updates_count').clone();
    $('new_notification').destroy();
    notify_link.setAttribute('id', 'core_menu_mini_menu_updates_count');
    notify_link.innerHTML = "0 updates";
    notify_link.inject($('core_menu_mini_menu_updates'));
    $('core_menu_mini_menu_updates').setAttribute('id', '');
    */
    if(scriptJquery('#notifications_main').length){
      var notification_children = scriptJquery('#notifications_main').children('li');
      notification_children.each(function(el){
        scriptJquery(this).attr('class', '');
      });
    }

    if(scriptJquery('#notifications_menu').length){
      var notification_children = scriptJquery('#notifications_menu').children('li');
      notification_children.each(function(el){
        scriptJquery(this).attr('class', '');
      });
    }
    //$('core_menu_mini_menu_updates').setStyle('display', 'none');
  },
 
  updateNotifications : function() {
    var self = this;
    if(en4.core.request.isRequestActive() ) return;
    en4.core.request.send(scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/notifications/update',
      method:'post',
      dataType:'json',
      data : {
        format : 'json'
      },
      success : function(){
        self.showNotifications.bind(self);
      },
    }));
  },
 
  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      scriptJquery('#updates_toggle').addClass('new_updates');
    }
  },

  markRead : function (action_id){
    en4.core.request.send(scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/notifications/test',
      method:'post',
      dataType:'json',
      data : {
        format     : 'json',
        'actionid' : action_id
      }
    }));
  },

  cometNotify : function(responseObject){
    scriptJquery('#core_menu_mini_menu_updates')[0].style.display = '';
    scriptJquery('#core_menu_mini_menu_updates_count')[0].innerHTML = responseObject.text;
  },
  alignHtml : function(){
    scriptJquery(".feed_item_body_content > .feed_item_bodytext").each(function(){
      var element = scriptJquery(this);
      element.clone().insertAfter(scriptJquery(this).closest(".feed_item_body_content"));
      element.remove();
    });
  },
};

NotificationUpdateHandler = class{

  options = {
      debug : false,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      minDelay : 5000,
      maxDelay : 600000,
      delayFactor : 1.5,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      subject_guid : null
    };

  state = true;

  activestate = 1;

  fresh = true;

  lastEventTime = false;

  title= document.title;

  constructor(options) {
    this.options = scriptJquery.extend(this.options,options);
    this.options.minDelay = this.options.delay;
  }
 

  start = function() {
    this.state = true;

    // Do idle checking
    // this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    // this.idleWatcher.register();
    // this.addEvents({
    //   'onStateActive' : function() {
    //     this.activestate = 1;
    //     this.state= true;
    //   }.bind(this),
    //   'onStateIdle' : function() {
    //     this.activestate = 0;
    //     this.state = false;
    //   }.bind(this)
    // });

    this.loop();
  }

  stop = function() {
    this.state = false;
  }

  updateNotifications = function() {
    if( en4.core.request.isRequestActive()) return;
    en4.core.request.send(scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/notifications/update',
      method : 'post',
      dataType : 'json',
      data : {
        format : 'json'
      },
    })
    ,{
      successCallBack : this.showNotifications.bind(this)
    });
  }

  showNotifications = function(responseJSON){
    if (responseJSON.notificationCount>0){
      this.options.delay = this.options.minDelay;
      if (!document.getElementById('updates_toggle')) {
        return;
      }
      if(document.getElementById('update_count'))
      scriptJquery('#update_count').html(responseJSON.notificationCount).addClass('minimenu_update_count_bubble_active');
    } else {
      this.options.delay = Math.min(this.options.maxDelay, this.options.delayFactor * this.options.delay);
    }
  }

  loop = function() {
    if( !this.state) {
      setTimeout(this.loop.bind(this),this.options.delay);
      return;
    }
    try {
      this.updateNotifications().complete(function() {
        setTimeout(this.loop.bind(this),this.options.delay);
      }.bind(this));
    } catch( e ) {
      setTimeout(this.loop.bind(this),this.options.delay);
      this._log(e);
    }
  }

  // Utility

  _log = function(object) {
    if( !this.options.debug ) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
      if( typeof(console) && $type(console) ) {
      }
    } catch( e ) {
      // Silence
    }
  }
}

//(function(){

  en4.activity.compose = {

    composers : {},

    register : function(object){
      name = object.getName();
      this.composers[name] = object;
    },

    deactivate : function(){
      for( var x in this.composers ){
        this.composers[x].deactivate();
      }
      return this;
    }
  };


  en4.activity.compose.icompose = class{


    name = false;

    element = false;

    options = {};

    // initialize = function(element, options){
    //   this.element = $(element);
    //   this.setOptions(options);
    // };
    constructor(options) {
      this.options = scriptJquery.extend(this.options,options);
      this.options.minDelay = this.options.delay;
    }

    getName = function(){
      return this.name;
    };

    activate = function(){
      en4.activity.compose.deactivate();
    };

    deactivate = function(){

    }
  };

//})();

ActivityUpdateHandler =  class{

  options = {
      debug : true,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      next_id : null,
      subject_guid : null,
      showImmediately : false
    };

  state = true;

  activestate = 1;

  fresh = true;

  lastEventTime = false;
  isRequestFeedSend = false;
  title = document.title;

  //loopId : false,

  // initialize = function(options) {
  //   this.setOptions(options);
  // },
  constructor(options) {
    this.options = scriptJquery.extend(this.options,options);
  }

  start = function() {
    this.state = true;
    this.loop();
    //this.loopId = this.loop.periodical(this.options.delay, this);
  };

  stop = function() {
    this.state = false;
  }

  checkFeedUpdate = function(action_id, subject_guid){
    if(this.isRequestFeedSend) return;
    if( !activityGetFeeds || activityGetAction_id) return;
    this.isRequestFeedSend = true;
    function getAllElementsWithAttribute(attribute) {
      var matchingElements = [];
      var values = [];
      var allElements = document.getElementsByTagName('*');
      for (var i = 0; i < allElements.length; i++) {
        if (allElements[i].getAttribute(attribute)) {
          // Element exists with attribute. Add to array.
          matchingElements.push(allElements[i]);
          values.push(allElements[i].getAttribute(attribute));
          }
        }
      return values;
    }
    var list = getAllElementsWithAttribute('data-activity-feed-item');
    this.options.last_id = Math.max.apply( Math, list );
    const min_id = this.options.last_id + 1;
    var hashTag = scriptJquery('#hashtagtext').val();
    let obj = this;
    var req = scriptJquery.ajax({
      url : en4.core.baseUrl + 'widget/index/name/activity.feed?search='+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage,
      data : {
        'format' : 'html',
        'getUpdates':1,
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true,
        'filterFeed':scriptJquery('.activity_filter_tabs .active > a').attr('data-src'),
      },
      success: function(responseHTML) {
        this.isRequestFeedSend = false;
        if( obj.options.showImmediately && scriptJquery('#feed-update').children().length > 0 ) {
          scriptJquery('#feed-update').css('display', 'none');
          scriptJquery('#feed-update').html('');
          obj.getFeedUpdate(obj.options.next_id);
        }
      }
    });
    en4.core.request.send(req, {
      'element' : scriptJquery('#feed-update'),
      }
    );
    // req.addEvent('complete', function() {
    //   (function() {
    //     if( this.options.showImmediately && scriptJquery('#feed-update').children().length > 0 ) {
    //       scriptJquery('#feed-update').css('display', 'none');
    //       scriptJquery('#feed-update').html('');
    //       this.getFeedUpdate(this.options.next_id);
    //       }
    //     }).delay(50, this);
    // }.bind(this));



   // Start LOCAL STORAGE STUFF
   if(localStorage) {
     var pageTitle = document.title;
     //@TODO Refill Locally Stored Activity Feed

     // For each activity-item, get the item ID number Data attribute and add it to an array
     var feed  = document.getElementById('activity-feed');
     // For every <li> in Feed, get the Feed Item Attribute and add it to an array
     var items = feed.getElementsByTagName("li");
     var itemObject = { };
     // Loop through each item in array to get the InnerHTML of each Activity Feed Item
     var c = 0;
     for (var i = 0; i < items.length; ++i) {
       if(items[i].getAttribute('data-activity-feed-item') != null){
         var itemId = items[i].getAttribute('data-activity-feed-item');
         itemObject[c] = {id: itemId, content : document.getElementById('activity-item-'+itemId).innerHTML };
         c++;
         }
       }
     // Serialize itemObject as JSON string
     var activityFeedJSON = JSON.stringify(itemObject);
     localStorage.setItem(pageTitle+'-activity-feed-widget', activityFeedJSON);
   }


   // Reconstruct JSON Object, Find Highest ID
   if(localStorage.getItem(pageTitle+'-activity-feed-widget')) {
     var storedFeedJSON = localStorage.getItem(pageTitle+'-activity-feed-widget');
     var storedObj = eval ("(" + storedFeedJSON + ")");

     //alert(storedObj[0].id); // Highest Feed ID
    // @TODO use this at min_id when fetching new Activity Feed Items
   }
   // END LOCAL STORAGE STUFF


   return req;
  }

  getFeedUpdate = function(last_id){
    if( !activityGetFeeds || activityGetAction_id) return;
    scriptJquery("#count_new_feed").html('');
    scriptJquery("#count_new_feed").hide();
    var min_id = this.options.last_id + 1;
    this.options.last_id = last_id;
    document.title = this.title;
    scriptJquery('.activity_noresult_tip').hide();
    var hashTag = scriptJquery('#hashtagtext').val();
     if(typeof itemSubjectGuid != "undefined")
    var itemSubject = itemSubjectGuid;
  else
    var itemSubject = "";
    var req = (scriptJquery.ajax({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/name/activity.feed?search="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage+'&subjectPage='+itemSubject,
      'data': {
        'format' : 'html',
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid,
        'filterFeed':scriptJquery('.activity_filter_tabs .active > a').attr('data-src'),
      },
      success: function(responseHTML) {
        scriptJquery('#activity-feed').prepend(responseHTML);
        activitytooltip();
      }
    }));
    return req;
  };

  loop = function() {
    this._log('activity update loop start');

    if( !this.state ) {
      setTimeout(this.loop.bind(this),this.options.delay);
      return;
    }

    try {
      this.checkFeedUpdate().complete(function() {
        setTimeout(this.loop.bind(this),this.options.delay);
      }.bind(this));
      
    } catch( e ) {
      setTimeout(this.loop.bind(this),this.options.delay);
      this._log(e);
    }

    this._log('activity update loop stop');
  };

  // Utility
  _log = function(object) {
    if( !this.options.debug ) {
      return;
    }

    try {
      if( 'console' in window && typeof(console) && 'log' in console ) {
        //console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  }
};
})(); // END NAMESPACE

AttachEventListerSE('click','.activity_schedule_btn',function(e){
  scriptJquery(this).parent().hide();
})
//buy sell navigation
scriptJquery(document).keydown(function(e) {
  if(!scriptJquery('.activity_sellitem_popup_header').length)
    return;
  var elem = scriptJquery('.activity_sellitem_popup_photos_strip').find('div').find('a');
  var length = elem.length;
  if(length < 2)
    return;
  var selectedIndex = elem.find('img.selected').parent().index();
  if(e.keyCode == 37 || e.keyCode == 38) { // left
   if(length <= (selectedIndex-1))
    elem.eq(length-1).trigger('click');
   else
    elem.eq(selectedIndex-1).trigger('click');
  }else if(e.keyCode == 39 || e.keyCode == 40) { // right
    if(length <= (selectedIndex+1))
      elem.eq(0).trigger('click');
    else
      elem.eq(selectedIndex+1).trigger('click');
  }
});
AttachEventListerSE('click','.allowed_hide_post_activity',function(e){
  var actionid = scriptJquery(this).attr('data-src');
  if(!actionid)
    return;
  scriptJquery(this).closest('li').remove();
  if(!scriptJquery('#activity-feed').find('li').length)
  scriptJquery('.activity_noresult_tip').show();

  var savefeed = scriptJquery.ajax({
    type:'POST',
    url: 'activity/ajax/unhidefeed/action_id/'+actionid,
    cache:false,
    contentType: false,
    processData: false,
    success:function(responseHTML){
     //silence

    },
   error: function(data){

    },
  });
});
function isUrl(s) {
       return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(s);
}
AttachEventListerSE('click','.pintotopfeedactivity',function(e){
  var url = scriptJquery(this).data('url')+"?url="+window.location.href;
  loadAjaxContentApp(url,true);
});



/* $Id:hashtags.js  2017-01-12 00:00:00 SocialEngineSolutions $*/
(function($) { 	
    $.fn.hashtags = function() {     
        if(scriptJquery('#ajaxsmoothbox_main').length){       
          var className = 'highlighter_edit';       
          var classMain = 'jqueryHashtags_edit';    
        }else{       
          var classMain = '';       
          var className = '';     
       } 		
       $(this).wrap('<div class="jqueryHashtags '+classMain+'"><div style="font-size:18px" class="highlighter '+className+'"></div></div>').unwrap().before('<div class="highlighter '+className+'"></div>').wrap('<div class="typehead"></div></div>'); 		
       $(this).addClass("theSelector"); 		
       autosize($(this)); 		
       $(this).parent().prev().on('click', function() { 			
        $(this).parent().find(".theSelector").focus(); 		
      });   
    }; 
})(scriptJquery);



// hashTag autosize.min.js
!(function (e, t) {
    "use strict";
    "function" == typeof define && define.amd ? define([], t) : "object" == typeof exports ? (module.exports = t()) : (e.autosize = t());
})(this, function () {
    function e(e) {
        function t() {
            var t = window.getComputedStyle(e, null);
            "vertical" === t.resize ? (e.style.resize = "none") : "both" === t.resize && (e.style.resize = "horizontal"), (e.style.wordWrap = "break-word");
            var i = e.style.width;
            (e.style.width = "0px"),
                e.offsetWidth,
                (e.style.width = i),
                (n = "none" !== t.maxHeight ? parseFloat(t.maxHeight) : !1),
                (r = "content-box" === t.boxSizing ? -(parseFloat(t.paddingTop) + parseFloat(t.paddingBottom)) : parseFloat(t.borderTopWidth) + parseFloat(t.borderBottomWidth)),
                o();
        }
        function o() {
            var t = e.style.height,
                o = document.documentElement.scrollTop,
                i = document.body.scrollTop;
            e.style.height = "auto";
            var s = e.scrollHeight + r;
            if (
                (n !== !1 && s > n ? ((s = n), "scroll" !== e.style.overflowY && (e.style.overflowY = "scroll")) : "hidden" !== e.style.overflowY && (e.style.overflowY = "hidden"),
                (e.style.height = s + "px"),
                (document.documentElement.scrollTop = o),
                (document.body.scrollTop = i),
                t !== e.style.height)
            ) {
                var d = document.createEvent("Event");
                d.initEvent("autosize.resized", !0, !1), e.dispatchEvent(d);
            }
        }
        if (e && e.nodeName && "TEXTAREA" === e.nodeName && !e.hasAttribute("data-autosize-on")) {
            var n, r;
            "onpropertychange" in e && "oninput" in e && e.addEventListener("keyup", o),
                window.addEventListener("resize", o),
                e.addEventListener("input", o),
                e.addEventListener("autosize.update", o),
                e.addEventListener(
                    "autosize.destroy",
                    function (t) {
                        window.removeEventListener("resize", o),
                            e.removeEventListener("input", o),
                            e.removeEventListener("keyup", o),
                            e.removeEventListener("autosize.destroy"),
                            Object.keys(t).forEach(function (o) {
                                e.style[o] = t[o];
                            }),
                            e.removeAttribute("data-autosize-on");
                    }.bind(e, { height: e.style.height, overflow: e.style.overflow, overflowY: e.style.overflowY, wordWrap: e.style.wordWrap, resize: e.style.resize })
                ),
                e.setAttribute("data-autosize-on", !0),
                (e.style.overflow = "hidden"),
                (e.style.overflowY = "hidden"),
                t();
        }
    }
    return "function" != typeof window.getComputedStyle
        ? function (e) {
              return e;
          }
        : function (t) {
              return t && t.length ? Array.prototype.forEach.call(t, e) : t && t.nodeName && e(t), t;
          };
});

/* $Id:pinboardcomment.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

/**
 * Comments
 */
 function activity_like(type, id, comment_id,widget_id){
		 (scriptJquery.ajax({
      dataType: 'json',
      method: 'post',
      url : en4.core.baseUrl + 'activity/comment/like',
      'data': {
        format : 'json',
        item_type : type,
        item_id : id,
				widget_identity:widget_id,
        comment_id : comment_id
      },
      success: function(response, response2, response3, response4) {
				// Get response
				var htmlBody;
				var jsBody;
				if( $type(response) == 'object' ){ // JSON response
					htmlBody = response['body'];
				} else if( $type(response) == 'string' ){ // HTML response
					htmlBody = response3;
					jsBody = response4;
				}else{
						htmlBody = JSON.decode(response3);
				}
				// An error probably occurred
				if( !response && !response3 && $type('comments_'+id) ){
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
					return;
				}
				if( $type(response) == 'object' && $type(response.status) && response.status == false )
				{
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
					return;
				}
				document.getElementById('comments_'+id).innerHTML = htmlBody;
			if(widget_id)
				eval("pinboardLayout_"+widget_id+"('',true)");
				}
    }));
}
function activity_unlike(type, id, comment_id,widget_id){
		 (scriptJquery.ajax({
       dataType: 'json',
      method: 'post',
      url : en4.core.baseUrl + 'activity/comment/unlike',
      'data': {
        format : 'json',
        item_type : type,
				widget_identity:widget_id,
        item_id : id,
        comment_id : comment_id
      },
      success: function(response, response2, response3, response4) {
				// Get response
				var htmlBody;
				var jsBody;
				if( $type(response) == 'object' ){ // JSON response
					htmlBody = response['body'];
				} else if( $type(response) == 'string' ){ // HTML response
					htmlBody = response3;
					jsBody = response4;
				}else{
						htmlBody = JSON.decode(response3);
				}
				// An error probably occurred
				if( !response && !response3 && $type('comments_'+id) ){
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
					return;
				}
				if( $type(response) == 'object' && $type(response.status) && response.status == false )
				{
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
					return;
				}
				document.getElementById('comments_'+id).innerHTML = htmlBody;
				if(widget_id)
					eval("pinboardLayout_"+widget_id+"('',true)");
				}
    }));
}
function activity_comment_submit(thisObj){
	var body = thisObj.elements[0].value;
	var type = thisObj.elements[1].value;
	var id = thisObj.elements[2].value;
	var widget_id = thisObj.elements[3].value;
	if(!body || !type || !id)
		return;	
		(scriptJquery.ajax({
      dataType: 'json',
      method: 'post',
      url : en4.core.baseUrl + 'activity/comment/create',
       'data': {
        format : 'json',
        item_type : type,
				widget_identity:widget_id,
        item_id : id,
        body : body
      },
      success: function(response, response2, response3, response4) {
				// Get response
				var htmlBody;
				var jsBody;
				if( $type(response) == 'object' ){ // JSON response
					htmlBody = response['body'];
				} else if( $type(response) == 'string' ){ // HTML response
					htmlBody = response3;
					jsBody = response4;
				}else{
						htmlBody = JSON.decode(response3);
				}
				// An error probably occurred
				if( !response && !response3 && $type('comments_'+id) ){
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
					return;
				}
				if( $type(response) == 'object' && $type(response.status) && response.status == false )
				{
					en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
					return;
				}
				document.getElementById('comments_'+id).innerHTML = htmlBody;
				if(widget_id)
					eval("pinboardLayout_"+widget_id+"('',true)");
				}
    }));
}
function activity_listcomment(type, id, page,widget_id){
		 (scriptJquery.ajax({
      dataType: 'html',
      method: 'post',
      url : en4.core.baseUrl + 'activity/comment/list',
      'data': {
        format : 'html',
        item_type : type,
        item_id : id,
				widget_identity:widget_id,
        page_id : page
      },
      success: function(response3) {
				document.getElementById('comments_'+id).innerHTML = response3;
				if(widget_id)
					eval("pinboardLayout_"+widget_id+"('',true)");
				}
    }));

}
en4.core.activitycomments = {
  deleteComment : function(type, id, comment_id,widget_id) {
    if( !confirm(en4.core.language.translate('Are you sure you want to delete this?')) ) {
      return;
    }
    (scriptJquery.ajax({
      dataType: 'json',
      url : en4.core.baseUrl + 'activity/comment/delete',
      data : {
        format : 'json',
        item_type : type,
        item_id : id,
        comment_id : comment_id
      },
      success: function() {
        if( document.getElementById('comment-' + comment_id) ) {
          scriptJquery('#comment-' + comment_id).remove();
        }
        try {
          var commentCount = $$('.comments_options_'+id+' span')[0];
          var m = commentCount.get('html').match(/\d+/);
          var newCount = ( parseInt(m[0]) != 'NaN' && parseInt(m[0]) > 1 ? parseInt(m[0]) - 1 : 0 );
          commentCount.set('html', commentCount.get('html').replace(m[0], newCount));
        } catch( e ) {}
				if(widget_id)
					eval("pinboardLayout_"+widget_id+"('',true)");
      }
    }));
  }
};
function isEnterPressed(e,thisVar){
	if (e.which == 13 && !e.shiftKey){
		//stop cursor from going to new line.
		e.preventDefault();
		scriptJquery(thisVar).parent().submit();
		scriptJquery(thisVar).val('');
	}
}
