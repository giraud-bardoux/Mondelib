
/* $Id: core.js 9984 2013-03-20 00:00:04Z john $ */



(function() { // START NAMESPACE
  en4.user = {
    viewer : {
      type : false,
      id : false
    },
  
    attachEmailTaken : function(element, callback)
    {
      var bind = this;
      scriptJquery(element).on('blur', function(){
        bind.checkEmailTaken(scriptJquery(this).val(), callback);
      });
  
      /*
      var lastElementValue = element.value;
      (function(){
        if( element.value != lastElementValue )
        {
  
          lastElementValue = element.value;
        }
      }).periodical(500, this);
      */
    },
  
    attachUsernameTaken : function(element, callback)
    {
      var bind = this;
      scriptJquery(element).on('blur', function(){
        bind.checkUsernameTaken(scriptJquery(this).val(), callback);
      });
      
      /*
      var lastElementValue = element.value;
      (function(){
        if( element.value != lastElementValue )
        {
          bind.checkUsernameTaken(element.value, callback);
          lastElementValue = element.value;
        }
      }).periodical(500, this);
      */
    },
  
    checkEmailTaken : function(email, callback)
    {
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/signup/taken',
        dataType:'json',
        method:'post',
        data : {
          format : 'json',
          email : email
        },
        success : function(responseObject)
        {
          if( $type(responseObject.taken) ){
            callback(responseObject.taken);
          }
        }
      }));
      return this;
    },
  
    checkUsernameTaken : function(username)
    {
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/signup/taken',
        dataType:'json',
        method:'post',
        data : {
          format : 'json',
          username : username
        },
        success : function(responseObject)
        {
          if( $type(responseObject.taken) ){
            callback(responseObject.taken);
          }
        }
      }));
  
      return this;
    },
  
    clearStatus : function() {
      var request = scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/edit/clear-status',
        method : 'post',
        dataType: 'json',
        data : {
          format : 'json'
        }
      });
      if(scriptJquery('#user_profile_status_container').length) {
        scriptJquery('#user_profile_status_container').empty();
      }
      return request;
    },
    
    buildFieldPrivacySelector : function(elements, privacyExemptFields) {
      var idEx = {};
      privacyExemptFields = typeof (privacyExemptFields) !== 'object' ? {} : privacyExemptFields;
      // Clear when body click, if not inside selector
      AttachEventListerSE('click', function(event) {
        let ele = scriptJquery(event.target);
        if(ele.hasClass('field-privacy-selector')) {
          return;
        } else if(ele.closest('.field-privacy-selector').length) {
          return;
        } else {
          scriptJquery('.field-privacy-selector').removeClass('active');
        }
      });
      
      // Register selectors
      elements.each(function(e) {
        let el = scriptJquery(this);
        if(el.prop('tagName').toLowerCase() == 'span') {
          return;
        }
        var fuid = el.attr('id');
        var tmp;
        if( (tmp = fuid.match(/^\d+_\d+_\d+/)) ) {
          fuid = tmp[0];
        }
        var id = el.attr('data-field-id');
        if( id in idEx ) {
          return;
        }
        if( Object.values(privacyExemptFields).indexOf(parseInt(id)) > -1 ) {
          return;
        }
        idEx[id] = true;
        var wrapperEl = el.parents('.form-wrapper');
        var privacyValue = el.attr('data-privacy');
        
        var selector = scriptJquery.crtEle('div', {
          'class' : 'field-privacy-selector',
          'data-privacy' : privacyValue || 'everyone',
        });
        selector = selector.html('\
                    <span class="icon"></span>\n\
                    <span class="caret"></span>\n\
                    <ul>\n\
                      <li data-value="everyone" class="field-privacy-option-everyone"><span class="icon"></span><span class="text">' 
                        + en4.core.language.translate('Everyone') + '</span></li>\n\
                      <li data-value="registered" class="field-privacy-option-registered"><span class="icon"></span><span class="text">' 
                        + en4.core.language.translate('All Members') + '</span></li>\n\
                      <li data-value="friends" class="field-privacy-option-friends"><span class="icon"></span><span class="text">' 
                        + en4.core.language.translate('Friends') + '</span></li>\n\
                      <li data-value="self" class="field-privacy-option-self"><span class="icon"></span><span class="text">' 
                        + en4.core.language.translate('Only Me') + '</span></li>\n\
                    </ul>\n\
                    <input type="hidden" name="privacy[' + fuid + ']" />');
        selector.appendTo(wrapperEl);
        selector.off().on('click', function(e) {
          var prevState = selector.hasClass('active');
          scriptJquery('.field-privacy-selector').removeClass('active');
          if(!prevState) {
            selector.addClass('active');
          }
        });
        selector.find('li').off().on('click', function(event) {
          var el = scriptJquery(event.target);
          if(el.prop('tagName').toLowerCase() != 'li' ) {
            el = el.parent();
          }
          var value = el.attr('data-value');
          selector.find('input').attr('value', value);
          selector.find('.active').removeClass('active');
          el.addClass('active');
          selector.attr('data-privacy', value);
        });
        selector.find('*[data-value="' + (privacyValue || 'everyone') + '"]').addClass('active');
        selector.find('input').attr('value', privacyValue || 'everyone');
      });
    }
    
  };
  
  en4.user.friends = {
  
    refreshLists : function(){
      
    },
    
    addToList : function(list_id, user_id){
      var request = scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/friends/list-add',
        dataType : 'json',
        method : 'post',
        data : {
          format : 'json',
          friend_id : user_id,
          list_id : list_id
        }
      });
      return request;
  
    },
  
    removeFromList : function(list_id, user_id){
      var request = scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/friends/list-remove',
        dataType : 'json',
        method : 'post',
        data : {
          format : 'json',
          friend_id : user_id,
          list_id : list_id
        }
      });
      return request;
  
    },
  
    createList : function(title, user_id){
      var request = scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/friends/list-create',
        dataType : 'json',
        method : 'post',
        data : {
          format : 'json',
          friend_id : user_id,
          title : title
        }
      });
      return request;
    },
  
    deleteList : function(list_id){
  
      var bind = this;
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'user/friends/list-delete',
        dataType : 'json',
        method : 'post',
        data : {
          format : 'json',
          user_id : en4.user.viewer.id,
          list_id : list_id
        }
      }));
  
      return this;
    },
  
  
    showMenu : function(user_id){
      scriptJquery('#profile_friends_lists_menu_' + user_id).css("visibility",'visible');
      scriptJquery('#friends_lists_menu_input_' + user_id).trigger("focus");
      scriptJquery('#friends_lists_menu_input_' + user_id).trigger("select");
    },
  
    hideMenu : function(user_id){
      scriptJquery('#profile_friends_lists_menu_' + user_id).css("visibility",'hidden');
    },
  
    clearAddList : function(user_id){
      scriptJquery('#friends_lists_menu_input_' + user_id).val("");
    }
  
  };
  
  /*
  * Multi Select
  * */
  window.addEventListener('DOMContentLoaded', function() {
      if (typeof scriptJquery != "undefined" && scriptJquery('#global_page_user-index-browse').length) {
          scriptJquery('.show_multi_select').closest('li').css('overflow',"visible");
          scriptJquery('.show_multi_select').selectize({});
      }
  })
  
  })(); // END NAMESPACE
  
  function userWidgetRequestSend(action, data) {
    var url;
    if( action == 'confirm' ) {
      url = en4.core.baseUrl + 'user/friends/confirm';
    } else if( action == 'reject' ) {
      url = en4.core.baseUrl + 'user/friends/reject';
    } else if( action == 'ignore' ) {
      url = en4.core.baseUrl + 'user/friends/ignore';
    } else {
      return false;
    }
    (scriptJquery.ajax({
      dataType: 'json',
      'url' : url,
      'data' : data,
      success : function(responseJSON) {
        if( !responseJSON.status ) {
          document.getElementById('notifications_' + data.notification_id).innerHTML = '<div class="request_success">' + responseJSON.error + '</div>';
        } else {
          document.getElementById('notifications_' + data.notification_id).innerHTML = '<div class="request_success">' +responseJSON.message+'</div>';
        }
      }
    }));
  }
  
  function otpsmsTimerData(email) {
    var elem = scriptJquery('.otpsms_timer_class');
    if (elem.length > 0) {
      for(i=0;i<elem.length;i++) {
        var dataTime = scriptJquery(elem[i]).attr('data-time');
  
        var startTimeData = scriptJquery(elem[i]).attr('data-created');
        if (startTimeData == "") {
            var startTime = new Date();
            var startTimeData = startTime.toJSON();
            scriptJquery(elem[i]).attr('data-created', startTimeData);
        }else{
            var startTime = new Date(startTimeData);
        }
  
        var endtime = scriptJquery(elem[i]).attr('data-time');
        var endTime = new Date();
        var expireTime = new Date(startTime.getTime() + 1000*endtime);
  
        var isValid = true;
        if (endTime.getTime() >= expireTime.getTime()){
            isValid = false;
        }
  
        var currentTime = new Date();
        //remaining time in seconds
        var timeDiff = expireTime - currentTime; //in ms
        // strip the ms
        timeDiff /= 1000;
  
        // get seconds
        var remaining = Math.round(timeDiff);
        if(remaining > 0 && isValid == true) {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            scriptJquery(elem[i]).html(m + ':' + s);
        } else {
          scriptJquery('#timer').remove();
          if(scriptJquery('#otp_timer'))
          scriptJquery('#otp_timer').html(en4.core.language.translate("Code Expired")).css('color','red');
          if(email.match(/^\d+$/)) {
            scriptJquery('#resend_otp').show();
          }
          if(scriptJquery('#resend')) {
            scriptJquery('#resend').show();
          }
        }
      }
    }
  
    setTimeout(function() {
      otpsmsTimerData(email);
    }, 100);
  }
  
  var otpsmsVerifyText;
  AttachEventListerSE('submit','.otpsms_login_verify',function(e){
    e.preventDefault();
    var obj = scriptJquery(this);
    var value = scriptJquery(obj).find('.form-elements').find('#code-wrapper').find('#code-element').find('#code').val();
    if(!value || value == ""){
      scriptJquery(obj).find('.form-elements').find('#code-wrapper').find('#code-element').find('#code').css('border','1px solid red');
      return;
    }
    scriptJquery(obj).find('.form-elements').find('#code-wrapper').find('#code-element').find('#code').css('border','');
    var url = scriptJquery(this).attr('action');
    
    var elem = scriptJquery(obj).find('.form-elements').find('#buttons-wrapper').find('#buttons-element').find('#submit');
    otpsmsVerifyText = elem.html();
    if(elem.hasClass('active'))
      return;
    elem.addClass('active');
    resendHTML = elem.html();
    scriptJquery.ajax({
     dataType: 'json',
     url: url,
      method: 'post',
      data: {
        user_id : scriptJquery(obj).find('.form-elements').find('#email_data').val(),
        code : scriptJquery(obj).find('.form-elements').find('#code-wrapper').find('#code-element').find('#code').val(),
        email: scriptJquery(obj).find('.form-elements').find('#email').val(),
        country_code: scriptJquery(obj).find('.form-elements').find('#country_code').val(),
        type:'login',
        format: 'json',
      },
      onRequest: function(){
        elem.html('<img src="application/modules/Core/externals/images/loading.gif" alt="Loading">');
      },
      success: function(responseJSON) {
        elem.removeClass('active');
        if (responseJSON.error == 1) {
          //show error
          var html = '<ul class="form-errors"><li><ul class="errors"><li>'+responseJSON.message+'</li></ul></li></ul>';
          scriptJquery(obj).find('.form-elements').parent().find('.form-errors').remove();
          scriptJquery(html).insertBefore(scriptJquery(obj).find('.form-elements'));
        } else {
          window.location.href = responseJSON.url;
          return;
        }
        elem.html(otpsmsVerifyText);
      }
    });
  });
  function loginAsUser(user_id, password, popup) {
    
    if(popup)
      var password = scriptJquery("#poplogin_password").val();
    if(password.length == 0) {
      scriptJquery("#poplogin_password_error").css('display','inline-block');
      return false;
    }
    
    var url = en4.core.baseUrl + 'user/auth/quicklogin';
    (scriptJquery.ajax({
      url : url,
      dataType: 'json',
      method : "post",
      data : {
        format : 'json',
        user_id : user_id,
        password: password,
        popup: popup,
      },
      success : function(response) {
        if(response.status) {
          loadAjaxContentApp(response.redirect_url, false,"full");
          //window.proxyLocation.href = response.redirect_url;
        } else {
          scriptJquery("#poplogin_password_error").css('display','inline-block').html(response.message);
        }
      }
    }));
  }
  
  function closeRemoveUser() {
    scriptJquery('#recent_login_remove').modal('hide');;
    scriptJquery('#send_recentremove_form').hide();
    scriptJquery('#removeUserId').val('');
  }
  
  function removeRecentLoginUser() {
    var user_id = scriptJquery('#removeUserId').val();
    scriptJquery("#core_loading_cont_overlay").show();
    var url = en4.core.baseUrl + 'user/auth/removerecentlogin';
    (scriptJquery.ajax({
      url: url,
      dataType: 'json',
      data: {
        format: 'json',
        user_id: user_id,
        redirectURL: scriptJquery('#redirectURL').val(),
      },
      success: function (response) {
        scriptJquery("#core_loading_cont_overlay").hide();
        if(response.status) {
          document.getElementById('send_recentremove_form').innerHTML = "<div class='success_msg  alert_message m-2'><span>"+en4.core.language.translate(response.message)+"</span></div>";
          //window.location.replace(response.redirect_url);
          loadAjaxContentApp(response.redirect_url, false,"full");
        }
      }
    }));
  }
  
  AttachEventListerSE('click', '#remove_account', function(e){
    removeRecentLoginUser();
  });
  
  function removeRecentUser(user_id, id) {
    scriptJquery('#'+id).show();
    scriptJquery('#send_recentremove_form').show();
    scriptJquery('#removeUserId').val(user_id);
  }
  
  //Follow
  AttachEventListerSE('click', '.user_follow', function () {
    
    var element = scriptJquery(this);
    if (!scriptJquery (element).attr('data-url'))
      return;
    var id = scriptJquery (element).attr('data-url');
    var widget = scriptJquery (element).attr('data-widget');
    var follow_id = scriptJquery (element).attr('data-follow_id');
    var action = scriptJquery (element).attr('data-action');
    var notification_id = scriptJquery (element).attr('data-notification-id');
  
    var iconType = scriptJquery (element).attr('data-icontype');
    var datacoretooltipOrigin = coretooltipOrigin;
    (scriptJquery.ajax({
      dataType: 'html',
      method: 'post',
      'url': en4.core.baseUrl + 'user/follow/index',
      'data': {
        format: 'html',
        id: scriptJquery(element).attr('data-url'),
        widget:widget,
        follow_id: follow_id,
        actiontype:action,
        notification_id: notification_id,
        
        iconType: iconType,
      },
      success: function(responseHTML) {
  
        var response = jQuery.parseJSON(responseHTML);
        if (response.error)
          alert(en4.core.language.translate('Something went wrong,please try again later'));
        else {
          if(response.autofollow == 1)  {
            var followElement = '.user_follow_'+id;
            if (response.condition == 'reduced') {
              //scriptJquery (followElement).find('i').removeClass('fa-times').addClass('fa-check');
              //scriptJquery (followElement).find('span').html(en4.core.language.translate("Follow"));
              scriptJquery('.user_follow_'+id).each(function() {
                scriptJquery(followElement).replaceWith(response.data);
                scriptJquery (followElement).addClass('user_followers');
              });
  
              showSuccessTooltip('<i class="fa fa-times"></i><span>'+(en4.core.language.translate("User unfollowed successfully."))+'</span>','core_reject_notification');
            }
            else {
              scriptJquery (followElement).find('span').html(en4.core.language.translate("Following"));
              scriptJquery (followElement).find('i').removeClass('fa-check').addClass('fa-times');
              scriptJquery (followElement).removeClass('user_followers');
              showSuccessTooltip('<i class="fa fa-check"></i><span>'+(en4.core.language.translate("You are now following this user."))+'</span>');
            }
          } else {
            if (response.condition == 'reject') {
              scriptJquery ("#user_follow_accept_"+follow_id).remove();
              scriptJquery ("#user_follow_reject_"+follow_id).remove();
              showSuccessTooltip('<i class="fa fa-times"></i><span>'+(en4.core.language.translate(response.message))+'</span>','core_reject_notification');
              if(notification_id) {
                scriptJquery('#notifications_'+notification_id).html(en4.core.language.translate(response.message));
              }
            } else if (response.condition == 'accept') {
              scriptJquery ("#user_follow_accept_"+follow_id).remove();
              scriptJquery ("#user_follow_reject_"+follow_id).remove();
              showSuccessTooltip('<i class="fa fa-check"></i><span>'+(en4.core.language.translate(response.message))+'</span>');
              if(notification_id) {
                scriptJquery('#notifications_'+notification_id).html(en4.core.language.translate(response.message));
              }
            } else {
              scriptJquery('.user_follow_'+id).each(function() {
                scriptJquery('.user_follow_'+id).replaceWith(response.data);
              });
              if (response.condition == 'reduced') {
                showSuccessTooltip('<i class="fa fa-times"></i><span>'+(en4.core.language.translate(response.message))+'</span>','core_reject_notification');
              } else {
                showSuccessTooltip('<i class="fa fa-check"></i><span>'+(en4.core.language.translate(response.message))+'</span>');
              }
            }
          }
  
          if(scriptJquery('.user_follow_user_'+id))
            scriptJquery('.user_follow_user_'+id).remove();
          
          if((response.condition == 'accept' || response.condition == 'reject') && document.getElementById('coverphoto_follow_inner')) {
            scriptJquery('#coverphoto_follow_inner').remove();
          }
          datacoretooltipOrigin.tooltipster('content', scriptJquery(".tooltipster-content").html());
        }
        return true;
      }
    }));
  });
  
  //Add Friend ajax based
  AttachEventListerSE('click', '.user_addfriend_request', function() {
    var userthis = this;
    var datacoretooltipOrigin = coretooltipOrigin;
    scriptJquery.ajax({ 
      url: en4.core.baseUrl + 'user/membership/add-friend',
      'data': {
        'user_id' : scriptJquery(this).attr('data-src'),
        'format' : 'html',
        'parambutton': scriptJquery(this).attr('data-rel'),
      },
      success: function(responseHTML) {
        var result = scriptJquery.parseJSON(responseHTML);
        if(result.status == 1){
          scriptJquery(userthis).parent().html(result.message);
          datacoretooltipOrigin.tooltipster('content', scriptJquery(".tooltipster-content").html());
          showSuccessTooltip('<i class="fa fa-check-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>');
        }
        else
           en4.core.showError(en4.core.language.translate(result.message));
      }
    });
  });
  
  AttachEventListerSE('click', '.user_cancelfriend_request', function() {
    var userthis = this;
    var datacoretooltipOrigin = coretooltipOrigin;
    scriptJquery.ajax({
      url: en4.core.baseUrl + 'user/membership/cancel-friend',
      'data': {
        'user_id' : scriptJquery(this).attr('data-src'),
        'format' : 'html',
        'parambutton': scriptJquery(this).attr('data-rel'),
      },
      success: function(responseHTML) {
       var result = scriptJquery.parseJSON(responseHTML);
        if(result.status == 1){
          scriptJquery(userthis).parent().html(result.message);
          datacoretooltipOrigin.tooltipster('content', scriptJquery(".tooltipster-content").html());
          showSuccessTooltip('<i class="fa fa-times-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>');
        }
        else
          en4.core.showError(en4.core.language.translate(result.message));
      }
    });
  });
  
  AttachEventListerSE('click', '.user_removefriend_request', function() {
    var userthis = this;
    var datacoretooltipOrigin = coretooltipOrigin;
    scriptJquery.ajax({
    url: en4.core.baseUrl + 'user/membership/remove-friend',
    'data': {
      'user_id' : scriptJquery(this).attr('data-src'),
      'format' : 'html',
      'parambutton': scriptJquery(this).attr('data-rel'),
    },
    success: function(responseHTML) {
      var result = scriptJquery.parseJSON(responseHTML);
        if(result.status == 1){
          scriptJquery(userthis).parent().html(result.message);
          datacoretooltipOrigin.tooltipster('content', scriptJquery(".tooltipster-content").html());
          showSuccessTooltip('<i class="fa fa-times-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>');
        }
        else
        en4.core.showError(en4.core.language.translate(result.message));
      }
    });
  });
  
  AttachEventListerSE('click', '.user_acceptfriend_request', function() {
    var userthis = this;
    var datacoretooltipOrigin = coretooltipOrigin;
    scriptJquery.ajax({
    url: en4.core.baseUrl + 'user/membership/accept-friend',
    'data': {
      'user_id' : scriptJquery(this).attr('data-src'),
      'format' : 'html',
      'parambutton': scriptJquery(this).attr('data-rel'),
    },
    success: function(responseHTML) {
      var result = scriptJquery.parseJSON(responseHTML);
        if(result.status == 1){
          scriptJquery(userthis).parent().html(result.message);
          datacoretooltipOrigin.tooltipster('content', scriptJquery(".tooltipster-content").html());
          showSuccessTooltip('<i class="fa fa-check-circle"></i><span>'+(en4.core.language.translate(result.tip))+'</span>');
        }
        else
          en4.core.showError(en4.core.language.translate(result.message));
      }
    });
  });
  