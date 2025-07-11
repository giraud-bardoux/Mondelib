/* $Id: core.js 10019 2013-03-27 01:52:21Z john $ */

AttachEventListerSE('submit', '#comment_contact_owner', function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  var jqXHR = scriptJquery.ajax({
    url: en4.core.baseUrl + "comment/index/contact",
    type: "POST",
    contentType: false,
    processData: false,
    data: formData,
    success: function (response) {
      response = scriptJquery.parseJSON(response);
      if (response.status == 'true') {
        scriptJquery('#ajaxsmoothbox_container').html("<div id='comment_contact_message' class='comment_contact_popup '><div class='clearfix'><img src='application/modules/Comment/externals/images/success.png' alt=''><span>" + en4.core.language.translate('Message sent successfully') + "</span></div></div>");
        scriptJquery('#comment_contact_message').fadeOut("slow", function () {
          setTimeout(function () { ajaxsmoothboxclose(); }, 3000);
        });
      }
    }
  });
  return false;
});

AttachEventListerSE('keypress', '.body', function (event) {
  
  scriptJquery(this).closest('form').css('position', 'relative');
  if (scriptJquery(this).closest('form').hasClass('activity_form_submitting'))
    return false;

  if (event.keyCode == 13 && !event.shiftKey) {
    var body = scriptJquery(this).closest('form').find('.body').val();

    if(scriptJquery(this).closest('form').find('button[type=submit]').hasClass('disabled')) {
      return false;
    }

    var file_id = scriptJquery(this).closest('form').find('.file_id').val();
    var action_id = scriptJquery(this).closest('form').find('.file').val();;
    var emoji_id = scriptJquery(this).closest('form').find('.select_emoji_id').val();
    if (((!body && (file_id == 0)) && emoji_id == 0))
      return false;
    scriptJquery(this).closest('form').trigger('submit');
    scriptJquery(this).closest('form').addClass('submitting');
    scriptJquery(this).closest('form').append('<div class="core_loading_cont_overlay" style="display:block;"></div>');
    return false;
  }
});

AttachEventListerSE('click', '.comment_media_more', function () {
  var elem = scriptJquery(this).parent().find('.comment_media_container');
  if (elem.hasClass('less')) {
    elem.removeClass('less');
    elem.css('height', '204px');
    scriptJquery(this).text('Show All');
  } else {
    elem.addClass('less');
    elem.css('height', 'auto');
    scriptJquery(this).text('Show Less');
  }
});

var isonCommentBox = isOnEditField = false;
var EditFieldValue = '';
function getDataMentionEditComment(that, data) {
  if (scriptJquery(that).attr('data-mentions-input') === 'true') {
    updateEditValComment(that, data);
  }
}

function updateEditValComment(that, data) {
  EditFieldValue = data;
  scriptJquery(that).mentionsInput("update");
}

var mentiondataarray = [];
AttachEventListerSE('keyup', '.body', function (e) {
  var data = scriptJquery(this).val();
  EditFieldValue = data;
  var elem = scriptJquery(this).closest("form").find("button[type='submit']");
  if (data.length > 0 && data.trim() != '') {
    elem.removeClass("disabled");
  } else {
    if (!elem.hasClass("disabled")) {
      elem.addClass("disabled");
    }
  }
});

AttachEventListerSE('focus', '.body', function () {
  if (!scriptJquery(this).attr('id'))
    scriptJquery(this).attr('id', new Date().getTime());
  if (typeof activitybigtext == 'undefined')
    activitybigtext = false;
  isonCommentBox = true;
  var data = scriptJquery(this).val();

  if (!scriptJquery(this).val() || isOnEditField) {
    if (!scriptJquery(this).val())
      EditFieldValue = '';
    if(userTagsEnable) {
      scriptJquery(this).mentionsInput({
        onDataRequest: function (mode, query, callback) {
          scriptJquery.getJSON('comment/ajax/friends/query/' + query, function (responseData) {
            responseData = _.filter(responseData, function (item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
            callback.call(this, responseData);
          });
        },
        //defaultValue: EditFieldValue,
        onCaret: true
      });
    }
  }
  if (data) {
    getDataMentionEditComment(this, data);
  }

  if (!scriptJquery(this).parent().hasClass('typehead')) {
    scriptJquery(this).hashtags();
    scriptJquery(this).focus();
  }
  autosize(scriptJquery(this));
});

var CommentLikesTooltips;
en4.core.runonce.add(function () {
  // Add hover event to get likes
  AttachEventListerSE('mouseover', '.comments_comment_likes', function (event) {
    var el = scriptJquery(event.target);
    if (!el.data('tip-loaded')) {
      el.data('tip-loaded', true);
      el.data('tip:title', 'Loading...');
      el.data('tip:text', '');
      var id = el.get('id').match(/\d+/)[0];
      // Load the likes
      var url = en4.core.baseUrl + 'comment/index/get-likes';
      var req = scriptJquery.ajax({
        url: url,
        data: {
          format: 'json',
          //type : 'core_comment',
          action_id: el.getParent('li').getParent('li').getParent('li').get('id').match(/\d+/)[0],
          comment_id: id
        },
        success: function (responseJSON) {
          el.data('tip:title', responseJSON.body);
          el.data('tip:text', '');
          CommentLikesTooltips.elementEnter(event, el); // Force it to update the text
        }
      });
    }
  });
  // Add tooltips
  CommentLikesTooltips = new Tips(scriptJquery('.comments_comment_likes'), {
    fixed: true,
    className: 'comments_comment_likes_tips',
    offset: {
      'x': 48,
      'y': 16
    }
  });
  // Enable links in comments
  scriptJquery('.comments_body').enableLinks();
});

//reply comment
AttachEventListerSE('click', '.commentreply', function (e) {
  e.preventDefault();
  scriptJquery('.comment_reply_form').hide();
  let elem = scriptJquery(this).closest('.comment_cnt_li').find('.comments_reply').find('.comment_reply_form');
  elem.show();
  elem.find('.activity-comment-form-reply').show();
  var body = elem.find('.activity-comment-form-reply').find('.comment_form').find('.body');
  //var ownerInfo = scriptJquery.parseJSON(elem.find(".owner-info").html());
  var ownerInfo = scriptJquery.parseJSON(scriptJquery(this).parent().parent().parent().parent().find('.owner-info').html());
  body.focus();
  var data = "";
  body.mentionsInput('val', function (data) {
    data = data;
  });
  if (body.val().length) {
    body.val(' ');
  }
  if (!body.val().length) {
    scriptJquery(body).mentionsInput("addmention", ownerInfo);
    body.val(body.val() + ' ');
  }
  complitionRequestTrigger();
});

function commentlike(action_id, comment_id, obj, page_id, type, sbjecttype, subjectid, guid) {
  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/like',
    data: {
      format: 'json',
      action_id: action_id,
      page_id: page_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      guid: guid,
      sbjecttype: sbjecttype,
      subjectid: subjectid,
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        scriptJquery(obj).parent().parent().replaceWith(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
}

function commentunlike(action_id, comment_id, obj, page_id, type, sbjecttype, subjectid, guid) {
  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/unlike',
    data: {
      format: 'json',
      page_id: page_id,
      action_id: action_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      sbjecttype: sbjecttype,
      guid: guid,
      subjectid: subjectid,
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        scriptJquery(obj).parent().parent().replaceWith(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
}

//like feed action content
AttachEventListerSE('click', '.commentunlike', function () {
  var obj = scriptJquery(this);
  var action_id = scriptJquery(this).attr('data-actionid');
  var comment_id = scriptJquery(this).attr('data-commentid');
  var type = scriptJquery(this).attr('data-type');
  var datatext = scriptJquery(this).attr('data-text');
  var likeWorkText = scriptJquery(this).attr('data-like');
  var unlikeWordText = scriptJquery(this).attr('data-unlike');

  //check for unlike
  scriptJquery(this).find('i').removeAttr('style');
  scriptJquery(this).find('span').html(likeWorkText);
  scriptJquery(this).removeClass('commentunlike').removeClass('_reaction').addClass('commentlike');
  scriptJquery(this).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/unlike',
    data: {
      format: 'json',
      action_id: action_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      sbjecttype: scriptJquery(this).attr('data-sbjecttype'),
      subjectid: scriptJquery(this).attr('data-subjectid'),
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        var elemnt = scriptJquery(obj).closest('.comment-feed').find('._comment_comments').find('.comments_cnt_ul');
        if (elemnt.find('.comment_stats').length) {
          elemnt = elemnt.find('.comment_stats');
          var getPreviousSearchComment = scriptJquery('.comment_stats_' + action_id).html();
          scriptJquery(elemnt).replaceWith(responseHTML.body);
          scriptJquery('.comment_stats_' + action_id).html(getPreviousSearchComment);
        } else
          scriptJquery(elemnt).prepend(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
});

AttachEventListerSE("mouseover", ".comment_hoverbox_wrapper", function (e) {
  scriptJquery(this).removeClass("_close");
});

var previousCommentLikeObj;
//unlike feed action content
AttachEventListerSE('click', '.commentlike', function () {
  var obj = scriptJquery(this);
  previousCommentLikeObj = obj.closest('.comment_hoverbox_wrapper');
  var action_id = scriptJquery(this).attr('data-actionid');
  var guid = "";
  var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .custom_switch_val').find('a').first();
  if (!guidItem.length)
    var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
  if (guidItem)
    guid = guidItem.data('rel');
  var comment_id = scriptJquery(this).attr('data-commentid');
  var type = scriptJquery(this).attr('data-type');
  var datatext = scriptJquery(this).attr('data-text');
  var subject_id = scriptJquery(this).attr('data-subjectid');
  //check for like
  var isLikeElem = false;
  if (scriptJquery(this).hasClass('reaction_btn')) {
    var image = scriptJquery(this).find('.reaction').find('i').css('background-image');
    image = image.replace('url(', '').replace(')', '').replace(/\"/gi, "");
    var elem = scriptJquery(this).parent().parent().parent().find('a');
    isLikeElem = true;
  } else {
    var image = scriptJquery(this).parent().find('.comment_hoverbox').find('span').first().find('.reaction_btn').find('.reaction').find('i').css('background-image');
    image = image.replace('url(', '').replace(')', '').replace(/\"/gi, "");
    var elem = scriptJquery(this);
    isLikeElem = false
  }

  var likeWorkText = scriptJquery(elem).attr('data-like');
  var unlikeWordText = scriptJquery(elem).attr('data-unlike');

  //unlike
  if (scriptJquery(elem).hasClass('_reaction') && !isLikeElem) {
    scriptJquery(elem).find('i').removeAttr('style');
    scriptJquery(elem).find('span').html(unlikeWordText);
    scriptJquery(elem).removeClass('commentunlike').removeClass('_reaction').addClass('commentlike');
    scriptJquery(elem).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  } else {
    //like  
    scriptJquery(elem).find('i').css('background-image', 'url(' + image + ')');
    scriptJquery(elem).find('span').html(datatext);
    scriptJquery(elem).removeClass('commentlike').addClass('_reaction').addClass('commentunlike');
    scriptJquery(elem).parent().addClass('feed_item_option_unlike').removeClass('feed_item_option_like');
  }

  // 	var parentObject = previousCommentLikeObj.parent().html();
  // 	var parentElem = previousCommentLikeObj.parent();
  // 	previousCommentLikeObj.parent().html('');
  // 	parentElem.html(parentObject);

  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/like',
    data: {
      format: 'json',
      action_id: action_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      guid: guid,
      sbjecttype: scriptJquery(this).attr('data-sbjecttype'),
      subjectid: scriptJquery(this).attr('data-subjectid'),
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        var elemnt = scriptJquery(obj).closest('.comment-feed').find('._comment_comments').find('.comments_cnt_ul');

        if (elemnt.find('.comment_stats').length) {
          elemnt = elemnt.find('.comment_stats');
          if (!action_id)
            action_id = subject_id;
          var getPreviousSearchComment = scriptJquery('.comment_stats_' + action_id).html();
          scriptJquery(elemnt).replaceWith(responseHTML.body);
          scriptJquery('.comment_stats_' + action_id).html(getPreviousSearchComment);
        } else
          scriptJquery(elemnt).prepend(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
});

//cancel comment edit
AttachEventListerSE('click', '.comment_cancel', function (e) {
  e.preventDefault();
  var parentElem = scriptJquery(this).closest('.comment_cnt_li');
  parentElem.find('.comment_edit').remove();
  parentElem.find('.comments_info').show();
  var topParentElement = parentElem.closest('.comments');
  topParentElement = topParentElement.find('.activity-comment-form').show();
  complitionRequestTrigger();
});

//cancel comment reply edit
AttachEventListerSE('click', '.comment_cancel_reply', function (e) {
  e.preventDefault();
  var parentElem = scriptJquery(this).closest('li');
  parentElem.find('.comments_reply_info').show();
  parentElem.find('.comment_edit').remove();
  complitionRequestTrigger();
});

//cancel file upload image
AttachEventListerSE('click', '.cancel_upload_file', function (e) {
  e.preventDefault();
  
  var id = scriptJquery(this).attr('data-url'); 
  var form = scriptJquery(this).closest('form');
  
  var value = scriptJquery(this).parent().parent().parent().parent().find('.comment_post_options').find('.file_id').val().replace(id + '_album_photo', '');
  scriptJquery(this).parent().parent().parent().find('.comment_post_options').find('.file_id').val(value);
  value = scriptJquery(this).parent().parent().parent().parent().find('.comment_post_options').find('.file_id').val().replace(id + '_video', '');
  scriptJquery(this).parent().parent().parent().parent().find('.comment_post_options').find('.file_id').val(value);

  var fileIds = scriptJquery(this).parent().parent().parent().parent().find('.comment_post_options').find('.file_id').val().split(',');
  // Filter out empty strings and count the remaining ones
  var fileIdCount = fileIds.filter(function(fileId) {
      return fileId.trim() !== ''; // remove empty entries
  }).length;
  if(fileIdCount == 0) {
    form.find("button[type='submit']").removeClass('active').addClass('disabled');
  }

  scriptJquery(this).parent().hide().remove('');
  complitionRequestTrigger();
});

function getEditCommentMentionData(obj) {
  scriptJquery(obj).find('.body').mentionsInput('val', function (data) {
    submiteditcomment(obj, data);
  });
}

//edit comment
AttachEventListerSE('submit', '.activity-comment-form-edit', function (e) {
  e.preventDefault();
  getEditCommentMentionData(this);
});

function submiteditcomment(that, data) {
  if (scriptJquery(that).hasClass("submitting")) {
    return false;
  }
  scriptJquery(that).addClass("submitting");

  var body = data;
  var file_id = scriptJquery(that).find('.file_id').val();
  if ((!body && file_id == 0))
    return false;

  var formData = new FormData(that);
  formData.append('bodymention', body);
  submitCommentFormAjax = scriptJquery.ajax({
    type: 'POST',
    url: en4.core.baseUrl + 'comment/index/edit-comment/',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      scriptJquery(that).removeClass('submitting');
      scriptJquery(that).find('.core_loading_cont_overlay').remove();
      try {
        var dataJson = scriptJquery.parseJSON(data);
        if (dataJson.status == 1) {
          var parentElem = scriptJquery(that).parent().parent();
          parentElem.find('.comments_info').find('.comments_body').html(dataJson.content);
          parentElem.find('.comments_info').show();
          parentElem.find('.comment_edit').remove();
          parentElem.closest('.comments').find('.activity-comment-form').show();
          // en4.core.runonce.trigger();
          complitionRequestTrigger();
          //silence
        } else {
          alert('Something went wrong, please try again later');
        }

      } catch (err) {
        //silence
      }
    },
    error: function (data) {
      //silence
    }
  });
}

function commentreplyedit(that, data) {
  if (scriptJquery(that).hasClass("submitting")) {
    return false;
  }
  scriptJquery(that).addClass("submitting");
  var body = data;
  var file_id = scriptJquery(that).find('.file_id').val();
  if ((!body && file_id == 0))
    return false;
  var formData = new FormData(that);
  formData.append('bodymention', body);
  submitCommentFormAjax = scriptJquery.ajax({
    type: 'POST',
    url: en4.core.baseUrl + 'comment/index/edit-reply/',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      scriptJquery(that).removeClass('submitting');
      scriptJquery(that).find('.core_loading_cont_overlay').remove();
      try {
        var dataJson = scriptJquery.parseJSON(data);
        if (dataJson.status == 1) {
          var parentElem = scriptJquery(that).parent().parent();
          parentElem.find('.comments_reply_info').find('.comments_reply_body').html(dataJson.content);
          parentElem.find('.comments_reply_info').show();
          parentElem.find('.comment_edit').remove();
          // en4.core.runonce.trigger();
          complitionRequestTrigger();
          //silence
        } else {
          alert('Something went wrong, please try again later');
        }
      } catch (err) {
        //silence
      }
    },
    error: function (data) {
      //silence
    }
  });
}

function getCommentReplyEditMentionData(obj) {
  scriptJquery(obj).find('.body').mentionsInput('val', function (data) {
    commentreplyedit(obj, data);
  });
}

//edit comment reply
AttachEventListerSE('submit', '.activity-comment-form-edit-reply', function (e) {
  e.preventDefault();
  getCommentReplyEditMentionData(this);
});

function commentReply(that, data) {
  if (scriptJquery(that).hasClass("submitting")) {
    return false;
  }
  scriptJquery(that).addClass("submitting");

  var body = data;
  var file_id = scriptJquery(that).find('.comment_form').find('.file_id').val();
  var emoji_id = scriptJquery(that).find('.select_emoji_id').val();
  var action_id = scriptJquery(that).find('.file[name=action_id]').val();
  var gif_id = scriptJquery(that).find('.select_gif_id').val();
  if (((!body && (file_id == 0)) && emoji_id == 0 && gif_id == 0))
    return false
  if (!scriptJquery(that).find('.select_file').val()) {
    scriptJquery(that).find('.select_file').remove();
    executed = true;
  }
  var formData = new FormData(that);
  if (executed == true)
    scriptJquery(that).find('.file_comment_select').parent().append('<input type="file" name="Filedata" class="select_file" accept="image/*" multiple="" value="0" style="display:none;">');
  formData.append('bodymention', body);
  //page
  var elem = scriptJquery(that).closest('.comment-feed').find('.feed_item_date ul').find('.custom_switch_val').find('._feed_change_option_a');
  if (elem.length) {
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }

  //store
  var elem = scriptJquery(that).closest('.comment-feed').find('.feed_item_date ul').find('.estore_switcher_cnt').find('.estore_feed_change_option_a');
  if (elem.length) {
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }
  submitCommentFormAjax = scriptJquery.ajax({
    type: 'POST',
    url: en4.core.baseUrl + 'comment/index/reply/',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      scriptJquery(that).removeClass('submitting');
      scriptJquery(that).find('.core_loading_cont_overlay').remove();
      try {
        var dataJson = scriptJquery.parseJSON(data);
        if (dataJson.status == 1) {
          //scriptJquery(dataJson.content).insertBefore(scriptJquery(that).closest('.comments_reply').find('.comment_reply_form').find('.activity-comment-form-reply'));
          scriptJquery(that).parent().parent().find('.comments_reply_cnt').append(dataJson.content);
          scriptJquery(that).find('.comment_form_container').find('.comment_form').find('.body').val('');
          scriptJquery(that).find('.comment_form_container').find('.comment_form').find('.body').css('height', 'auto');
          scriptJquery(that).find('.comment_form_container').find('.comment_form').find('.body').parent().parent().find('div').eq(0).html('');
          var fileElem = scriptJquery(that).find('.comment_form_container').find('.comment_post_options').find('.comment_post_icons').find('span');
          fileElem.find('.select_file').val('');
          fileElem.find('.file_id').val('');
          fileElem.find('.select_emoji_id').val('');
          fileElem.find('.select_gif_id').val('');
          var getPreviousSearchComment = scriptJquery('.comment_stats_' + action_id).html(getPreviousSearchComment).find('a.comment_btn_open').html();
          var commentCount = getPreviousSearchComment.replace ( /[^\d.]/g, '' );
          var changecommentCount = parseInt(commentCount, 10) + 1
          scriptJquery('.comment_stats_' + action_id).html(scriptJquery('.comment_stats_' + action_id).html()).find('a.comment_btn_open').html(getPreviousSearchComment.replace(commentCount,changecommentCount));
          scriptJquery(that).find('.comment_form_container').find('.uploaded_file').html('');
          scriptJquery(that).find('.comment_form_container').find('.uploaded_file').hide();
          scriptJquery(that).find('.comment_form_container').find('.upload_file_cnt').remove();

          scriptJquery(that).find('.comment_form_container').find('.sticker_preview').html('');
          scriptJquery(that).find('.comment_form_container').find('.gif_preview').html('');
          // en4.core.runonce.trigger();
          complitionRequestTrigger();
          //silence
        } else {
          alert('Something went wrong, please try again later');
        }
      } catch (err) {
        //silence
      }
    },
    error: function (data) {
      //silence
    }
  });
}

//create reply comment
AttachEventListerSE('submit', '.activity-comment-form-reply', function (e) {
  e.preventDefault();
  getCommentMentionData(this);
});

function getCommentMentionData(obj) {
  scriptJquery(obj).find('.body').mentionsInput('val', function (data) {
    commentReply(obj, data);
  });
}

//comment edit form
AttachEventListerSE('click', '.activity_comment_edit', function (e) {
  e.preventDefault();
  var parentElem = scriptJquery(this).closest('.comment_cnt_li');
  var topParentElement = parentElem.closest('.comments');
  topParentElement = topParentElement.find('.activity-comment-form').hide();
  parentElem.find('.comments_info').hide();
  var textBody = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').html();
  if (textBody != "" && textBody) {
    textBody = textBody.trim();
  }
  //Feeling work
  EditFieldValue = textBody;


  isOnEditField = true;
  var datamention = parentElem.find('.comments_info').find('.comments_body').find('#data-mention').html();
  if (datamention) {
    mentionsCollectionValEdit = JSON.parse(datamention);
  }
  var module = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('rel');

  
  //Gif Edit
  var gifEdit = '';
  var gifImage = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').find('img').attr('src');
  var gifId = 0;
  if(gifImage) {
    gifId = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').find('img').attr('src');
    gifEdit = '<div class="gif_preview"><img src="'+gifImage+'"><a href="javascript:;" data-url="'+parentElem.find('.comments_info').find('.comments_body').find('.emoji').attr('data-rel')+'_comment_sticker" class="cancel_upload_gif fas fa-times" title="Cancel"></a></div>';
  }
  //Gif Edit

  //Stricker Edit
  var sticker = '';
  var stickerImage = parentElem.find('.comments_info').find('.comments_body').find('.emoji').find('img').attr('src');
  var stickerId = 0;
  if(stickerImage) {
    stickerId = parentElem.find('.comments_info').find('.comments_body').find('.emoji').attr('data-rel');
    sticker = '<div class="sticker_preview"><img src="'+stickerImage+'"><a href="javascript:;" data-url="'+stickerId+'_comment_sticker" class="cancel_upload_sticker fas fa-times" title="Cancel"></a></div>';
  }
  //Stricker Edit

  module = '<input type="hidden" name="modulecomment" value="' + module + '"><input type="hidden"  class="select_emoji_id" name="emoji_id" value="'+stickerId+'"><input type="hidden"  class="select_gif_id" name="gif_id" value="'+gifId+'">';

  var subject = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('data-subject');
  var subjectid = parentElem.find('.comments_info').find('.comments_body').find('.comments_body_actual').attr('data-subjectid');
  var subjectInputs = '';
  if (subject) {
    subjectInputs = '<input type="hidden" name="resource_type" value="' + subject + '"><input type="hidden" name="resource_id" value="' + subjectid + '">';
  }
  var fileid, filesrc, image = '';
  var display = 'none';
  var comment_id = parentElem.attr('id').replace('comment-', '');
  fileid = 0;
  files = '';
  filesLength = parentElem.find('.comments_info').find('.comments_body').find('.comment_image');
  if (filesLength.length) {
    for (var i = 0; i < filesLength.length; i++) {
      if (fileid == 0)
        fileid = '';
      if (scriptJquery(filesLength[i]).attr('data-type') == 'album_photo') {
        fileid = fileid + scriptJquery(filesLength[i]).attr('data-fileid') + '_album_photo,';
        var videoBtn = '';
      } else {
        fileid = fileid + scriptJquery(filesLength[i]).attr('data-fileid') + '_video,';
        var videoBtn = '<a href="javascript:;" class="comment_play_btn fa fa-play"></a>';
      }
      filesrc = scriptJquery(filesLength[i]).find('img').attr('src');
      image = '<img src="' + filesrc + '"><a href="javascript:;" data-url="' + scriptJquery(filesLength[i]).attr('data-fileid') + '" class="cancel_upload_file fas fa-times" title="Cancel"></a>' + videoBtn;
      display = 'block';
      files = '<div class="uploaded_file" style="display:block;">' + image + '</div>' + files;
    }
  }
  videoLink = '';
  if (videoModuleEnable == 1) {
    //videoLink = '<span><a href="javascript:;" class="video_comment_select"><i><svg viewBox="0 0 24 24"><path d="m19 24h-14a5.006 5.006 0 0 1 -5-5v-14a5.006 5.006 0 0 1 5-5h14a5.006 5.006 0 0 1 5 5v14a5.006 5.006 0 0 1 -5 5zm-14-22a3 3 0 0 0 -3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-14a3 3 0 0 0 -3-3zm4.342 15.005a2.368 2.368 0 0 1 -1.186-.323 2.313 2.313 0 0 1 -1.164-2.021v-5.322a2.337 2.337 0 0 1 3.5-2.029l5.278 2.635a2.336 2.336 0 0 1 .049 4.084l-5.376 2.687a2.2 2.2 0 0 1 -1.101.289zm-.025-8a.314.314 0 0 0 -.157.042.327.327 0 0 0 -.168.292v5.322a.337.337 0 0 0 .5.293l5.376-2.688a.314.314 0 0 0 .12-.266.325.325 0 0 0 -.169-.292l-5.274-2.635a.462.462 0 0 0 -.228-.068z"></path></svg></i></a></span>';
  }
  imageLink = '';
  if (AlbumModuleEnable == 1) {
    //imageLink = '<a href="javascript:;" class="file_comment_select" title="'+ en4.core.language.translate("Attach 1 or more Photos") +'" data-bs-toggle="tooltip"><i><svg viewBox="0 0 24 24"><path d="M19,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V5A5.006,5.006,0,0,0,19,0ZM5,2H19a3,3,0,0,1,3,3V19a2.951,2.951,0,0,1-.3,1.285l-9.163-9.163a5,5,0,0,0-7.072,0L2,14.586V5A3,3,0,0,1,5,2ZM5,22a3,3,0,0,1-3-3V17.414l4.878-4.878a3,3,0,0,1,4.244,0L20.285,21.7A2.951,2.951,0,0,1,19,22Z"></path><path d="M16,10.5A3.5,3.5,0,1,0,12.5,7,3.5,3.5,0,0,0,16,10.5Zm0-5A1.5,1.5,0,1,1,14.5,7,1.5,1.5,0,0,1,16,5.5Z"></path></svg></i></a>';
  }
  commentFeelings = '';
  var d = new Date();
  var time = d.getTime();
  var html = '<div class="comment_edit comment_form_container"><form class="activity-comment-form-edit" method="post"><div class="comment_form_main"><div class="comment_form"><textarea class="body" name="body" id="' + time + '" cols="45" rows="1" placeholder="' + en4.core.language.translate("Write a comment...") + '"></textarea></div><div class="comment_post_options"><div class="comment_post_icons"><span>' + imageLink + '<input type="file" name="Filedata" class="select_file" multiple style="display:none;">' + module + subjectInputs + '<input type="hidden" name="file_id" class="file_id" value="' + fileid + '"><input type="hidden" class="file" name="comment_id" value="' + comment_id + '"></span>' + videoLink + '<span style="display:none;"><a href="javascript:;" class="emoji_comment_select"><i><svg viewBox="0 0 24 24"><path d="m23.967 10.417a12.04 12.04 0 1 0 -13.55 13.55 3.812 3.812 0 0 0 .489.032 3.993 3.993 0 0 0 2.805-1.184l9.1-9.1a3.962 3.962 0 0 0 1.156-3.298zm-21.9.474a10.034 10.034 0 0 1 19.8-.884 12.006 12.006 0 0 0 -11.86 11.852 9.988 9.988 0 0 1 -7.944-10.968zm10.233 10.509a2.121 2.121 0 0 1 -.278.225 10 10 0 0 1 9.606-9.607 2.043 2.043 0 0 1 -.224.279z"></path></svg></i></a></span>' + commentFeelings + '</div><button type="submit"><i><svg viewBox="0 0 24 24"><path d="m4.173,13h19.829L4.201,23.676c-.438.211-.891.312-1.332.312-.696,0-1.362-.255-1.887-.734-.84-.77-1.115-1.905-.719-2.966l.056-.123,3.853-7.165Zm-.139-12.718C2.981-.22,1.748-.037.893.749.054,1.521-.22,2.657.18,3.717l3.979,7.283h19.841L4.11.322l-.076-.04Z"></path></svg></i></button></div></div><div class="uploaded_file"  style="display:none;"></div>'+sticker+gifEdit+'<div class="upload_file_cnt">' + files + '</div><div class="comment_btns" style="margin-top:0px;"><a href="javascript:;" class="comment_cancel">cancel</a></div></form></div>';

  scriptJquery(html).insertBefore(parentElem.find('.comments_info'));
  // parentElem.parent().find('.comment_edit').find('form').find('.comment_form').find('.body').trigger('focus');
  complitionRequestTrigger();

  scriptJquery('#' + time).val(textBody+" ");
  scriptJquery('#' + time).trigger("focus");
});

//comment reply edit form
AttachEventListerSE('click', '.comment_reply_edit', function (e) {
  e.preventDefault();
  var parent = scriptJquery(this).closest('.comments_reply_cnt');
  parent.find('.comment_edit').remove();
  parent.find('.comments_reply_info').show();
  var parentElem = scriptJquery(this).closest('.comments_reply_info');
  parentElem.find('.comments_reply').find('.comment_reply_form').find('.activity-comment-form-reply').hide();
  parentElem.hide();
  var textBody = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').html();
  if (textBody != "") {
    textBody = textBody.trim();
  }
  //Feeling work
  EditFieldValue = textBody;


  isOnEditField = true;
  var datamention = parentElem.find('.comments_reply_body').find('#data-mention').html();
  if (datamention) {
    mentionsCollectionValEdit = JSON.parse(datamention);
  }
  var module = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('rel');

  //Gif Edit
  var gifEdit = '';
  var gifImage = parentElem.find('.comments_reply_body').find('.comments_body_actual').find('img').attr('src');
  var gifId = 0;
  if(gifImage) {
    gifId = gifImage;
    gifEdit = '<div class="gif_preview"><img src="'+gifImage+'"><a href="javascript:;" data-url="'+parentElem.find('.comments_reply_body').find('.emoji').attr('data-rel')+'_comment_sticker" class="cancel_upload_gif fas fa-times" title="Cancel"></a></div>';
  }
  //Gif Edit

  //Stricker Edit
  var sticker = '';
  var stickerImage = parentElem.find('.comments_reply_body').find('.emoji').find('img').attr('src');
  var stickerId = 0;
  if(stickerImage) {
    stickerId = parentElem.find('.comments_reply_body').find('.emoji').attr('data-rel');
    sticker = '<div class="sticker_preview"><img src="'+stickerImage+'"><a href="javascript:;" data-url="'+stickerId+'_comment_sticker" class="cancel_upload_sticker fas fa-times" title="Cancel"></a></div>';
  }
  //Stricker Edit

  module = '<input type="hidden" name="modulecomment" value="' + module + '"><input type="hidden" name="emoji_id" class="select_emoji_id" value="'+stickerId+'"><input type="hidden" name="gif_id" class="select_gif_id" value="'+gifId+'">';

  var subject = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('data-subject');
  var subjectid = parentElem.find('.comments_reply_body').find('.comments_reply_body_actual').attr('data-subjectid');
  var subjectInputs = '';
  if (subject) {
    subjectInputs = '<input type="hidden" name="resource_type" value="' + subject + '"><input type="hidden" name="resource_id" value="' + subjectid + '">';
  }
  var fileid, filesrc, image = '';
  var display = 'none';
  var comment_id = parentElem.closest('li').attr('id').replace('comment-', '');
  fileid = 0;
  files = '';
  filesLength = parentElem.find('.comments_reply_body').find('.comment_reply_image');
  if (filesLength.length) {
    for (var i = 0; i < filesLength.length; i++) {
      if (fileid == 0)
        fileid = '';
      if (scriptJquery(filesLength[i]).attr('data-type') == 'album_photo') {
        fileid = fileid + scriptJquery(filesLength[i]).attr('data-fileid') + '_album_photo,';
        var videoBtn = '';
      } else {
        fileid = fileid + scriptJquery(filesLength[i]).attr('data-fileid') + '_video,';
        var videoBtn = '<a href="javascript:;" class="play_upload_file fa fa-play"></a>';
      }
      filesrc = scriptJquery(filesLength[i]).find('img').attr('src');
      image = '<img src="' + filesrc + '"><a href="javascript:;" data-url="' + scriptJquery(filesLength[i]).attr('data-fileid') + '" class="cancel_upload_file fas fa-times" title="Cancel"></a>' + videoBtn;
      display = 'block';
      files = '<div class="uploaded_file" style="display:block;">' + image + '</div>' + files;
    }
  }
  videoLink = '';
  if (videoModuleEnable == 1) {
    //videoLink = '<span><a href="javascript:;" class="video_comment_select"><i><svg viewBox="0 0 24 24"><path d="m19 24h-14a5.006 5.006 0 0 1 -5-5v-14a5.006 5.006 0 0 1 5-5h14a5.006 5.006 0 0 1 5 5v14a5.006 5.006 0 0 1 -5 5zm-14-22a3 3 0 0 0 -3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3v-14a3 3 0 0 0 -3-3zm4.342 15.005a2.368 2.368 0 0 1 -1.186-.323 2.313 2.313 0 0 1 -1.164-2.021v-5.322a2.337 2.337 0 0 1 3.5-2.029l5.278 2.635a2.336 2.336 0 0 1 .049 4.084l-5.376 2.687a2.2 2.2 0 0 1 -1.101.289zm-.025-8a.314.314 0 0 0 -.157.042.327.327 0 0 0 -.168.292v5.322a.337.337 0 0 0 .5.293l5.376-2.688a.314.314 0 0 0 .12-.266.325.325 0 0 0 -.169-.292l-5.274-2.635a.462.462 0 0 0 -.228-.068z"></path></svg></i></a></span>';
  }
  imageLink = '';
  if (AlbumModuleEnable == 1) {
    //imageLink = '<a href="javascript:;" class="file_comment_select"><i><svg viewBox="0 0 24 24"><path d="M19,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V5A5.006,5.006,0,0,0,19,0ZM5,2H19a3,3,0,0,1,3,3V19a2.951,2.951,0,0,1-.3,1.285l-9.163-9.163a5,5,0,0,0-7.072,0L2,14.586V5A3,3,0,0,1,5,2ZM5,22a3,3,0,0,1-3-3V17.414l4.878-4.878a3,3,0,0,1,4.244,0L20.285,21.7A2.951,2.951,0,0,1,19,22Z"></path><path d="M16,10.5A3.5,3.5,0,1,0,12.5,7,3.5,3.5,0,0,0,16,10.5Zm0-5A1.5,1.5,0,1,1,14.5,7,1.5,1.5,0,0,1,16,5.5Z"></path></svg></i></a>';
  }

  //Feeling Work
  commentFeelings = '';

  var d = new Date();
  var time = d.getTime();
  var html = '<div class="comment_edit comment_form_container"><form class="activity-comment-form-edit-reply" method="post"><div class="comment_form_main"><div class="comment_form"><textarea class="body" id="' + time + '" name="body" cols="45" rows="1" placeholder="Write a reply...">' + textBody + '</textarea></div><div class="comment_post_options"><div class="comment_post_icons"><span>' + imageLink + '<input type="file" name="Filedata" class="select_file" multiple style="display:none;">' + module + subjectInputs + '<input type="hidden" name="file_id" class="file_id" value="' + fileid + '"><input type="hidden" class="file" name="comment_id" value="' + comment_id + '"></span>' + videoLink + '<span style="display:none;"><a href="javascript:;" class="emoji_comment_select"><i><svg viewBox="0 0 24 24"><path d="m23.967 10.417a12.04 12.04 0 1 0 -13.55 13.55 3.812 3.812 0 0 0 .489.032 3.993 3.993 0 0 0 2.805-1.184l9.1-9.1a3.962 3.962 0 0 0 1.156-3.298zm-21.9.474a10.034 10.034 0 0 1 19.8-.884 12.006 12.006 0 0 0 -11.86 11.852 9.988 9.988 0 0 1 -7.944-10.968zm10.233 10.509a2.121 2.121 0 0 1 -.278.225 10 10 0 0 1 9.606-9.607 2.043 2.043 0 0 1 -.224.279z"></path></svg></i></a></span>' + commentFeelings + '</div><button type="submit"><i><svg viewBox="0 0 24 24"><path d="m4.173,13h19.829L4.201,23.676c-.438.211-.891.312-1.332.312-.696,0-1.362-.255-1.887-.734-.84-.77-1.115-1.905-.719-2.966l.056-.123,3.853-7.165Zm-.139-12.718C2.981-.22,1.748-.037.893.749.054,1.521-.22,2.657.18,3.717l3.979,7.283h19.841L4.11.322l-.076-.04Z"></path></svg></i></button></div></div><div class="uploaded_file" style="display:none;"></div>'+sticker+gifEdit+'<div class="upload_file_cnt">' + files + '</div><div class="comment_btns" style="margin-top:0px;"><a href="javascript:;" class="comment_cancel_reply">cancel</a></div></form></div>';
  scriptJquery(html).insertBefore(parentElem);
  //var textArea = parentElem.parent().find('.comment_edit').find('form').find('.comment_form').find('.body').focus();
  //autosize(textArea);
  complitionRequestTrigger();
  
  scriptJquery('#' + time).val(textBody);
  scriptJquery('#' + time).trigger("focus");
});

//video in comment
var clickVideoAddBtn;
AttachEventListerSE('click', '.video_comment_select', function (e) {

  //If any other attachment is active previously
  scriptJquery(this).closest('form').find('.upload_file_cnt').hide();
  scriptJquery(this).closest('form').find('.sticker_preview').html('');
  scriptJquery(this).closest('form').find('.gif_preview').html('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.file_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_gif_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_emoji_id').val('');

  clickVideoAddBtn = this;
  if (youtubePlaylistEnable == 1) {
    var text = 'Paste a Youtube or Vimeo link here';
  } else
    var text = 'Paste Vimeo link here';
  en4.core.showError('<div class="comment_add_video_popup"><div class="comment_add_video_popup_header">Add Video</div><div class="comment_add_video_popup_cont"><p><input type="text" value="" placeholder="' + text + '" id="commentvideo_txt"><img src="application/modules/Core/externals/images/loading.gif" style="display:none;" id="commentvideo_img"></p></div><div class="comment_add_video_popup_btm d-flex gap-2"><button class="btn btn-primary" type="button" id="commentbtnsubmit">Add</button><button class="btn btn-link" onclick="Smoothbox.close()">Close</button></div></div>');
  scriptJquery('.comment_add_video_popup').parent().parent().addClass('comment_add_video_popup_wrapper ');
  scriptJquery('#commentvideo_txt').focus();
});

AttachEventListerSE('click', '#commentbtnsubmit', function (e) {
  var value = scriptJquery('#commentvideo_txt').val();
  if (!value) {
    scriptJquery('#commentvideo_txt').css('border', '1px solid red');
    return false;
  } else {
    scriptJquery('#commentvideo_txt').css('border', '');
  }
  if (youtubePlaylistEnable == 1 && validYoutube(value))
    type = 1;
  else if (validVimeo(value))
    type = 2;
  else {
    scriptJquery('#commentvideo_txt').css('border', '1px solid red');
    return false;
  }

  scriptJquery('#commentbtnsubmit').prop('disabled', true);
  scriptJquery('#commentvideo_img').show();

  scriptJquery.ajax({
    method: "POST",
    url: en4.core.baseUrl + videoModuleName + '/index/compose-upload/format/json/c_type/wall',
    data: {
      format: 'json',
      uri: value,
      type: type
    },
    'success': function (responseHTML) {
      if (typeof responseHTML.status != 'undefined' && responseHTML.status) {
        var videoid = responseHTML.video_id;
        var src = responseHTML.src;
        var form = scriptJquery(clickVideoAddBtn).closest('form');
        if (!form.find('.upload_file_cnt').length) {
          var container = scriptJquery('<div class="upload_file_cnt"></div>').insertAfter(scriptJquery(form).find('.uploaded_file'));
        } else
          var container = form.find('.upload_file_cnt');
        var uploadFile = scriptJquery('<div class="uploaded_file"></div>')
        var uploadImageLoader = scriptJquery('<img src="application/modules/Core/externals/images/loading.gif" class="_loading" />').appendTo(uploadFile);
        scriptJquery(uploadFile).appendTo(container);
        if (scriptJquery(form).find('.file_id').val() == 0)
          uploadFileId = '';
        else
          uploadFileId = scriptJquery(form).find('.file_id').val();
        scriptJquery(form).find('.file_id').val(uploadFileId + videoid + '_video' + ',');
        scriptJquery(uploadFile).html('<img src="' + src + '"><a href="javascript:;" data-url="' + videoid + '" class="cancel_upload_file fas fa-times" title="Cancel"></a><a href="javascript:;" class="comment_play_btn fa fa-play"></a>');
        complitionRequestTrigger();
        Smoothbox.close();

        //Active post button
        form.find("button[type='submit']").removeClass('disabled').addClass('active');
      } else {
        scriptJquery('#commentvideo_txt').css('border', '1px solid red');
      }
      scriptJquery('#commentbtnsubmit').prop('disabled', false);
      scriptJquery('#commentvideo_img').hide();
    }
  });
});

function validYoutube(myurl) {
  var matches = myurl.match(/watch\?v=([a-zA-Z0-9\-_]+)/);
  if (matches || myurl.indexOf('youtu.be') > -1)
    return true;
  else
    return false;
}

function validVimeo(myurl) {
  //var myurl = "https://vimeo.com/23374724";
  if (myurl.indexOf('https://vimeo.com') >= 0) {
    return true;
  } else {
    return false;
  };
}

//click on reply reply
AttachEventListerSE('click', '.commentreplyreply', function (e) {
  e.preventDefault();
  scriptJquery('.comment_reply_form').hide();
  var parent = scriptJquery(this).closest('.comments_reply');
  let elem = parent.find('.comment_reply_form');

  elem.show();
  elem.find('.activity-comment-form-reply').show();
  var body = elem.find('.activity-comment-form-reply').find('.comment_form').find('.body');

  var ownerInfo = scriptJquery.parseJSON(scriptJquery(this).parent().parent().parent().parent().find('.owner-info').html());
  body.focus();
  var data = "";
  body.mentionsInput('val', function (data) {
    data = data;
  });
  if (body.val().length) {
    body.val(' ');
  }
  if (!body.val().length) {
    scriptJquery(body).mentionsInput("addmention", ownerInfo);
    body.val(body.val() + ' ');
  }
  complitionRequestTrigger();

});

//view more comment
function commentactivitycomment(action_id, page, obj, subjecttype) {
  var type = scriptJquery(obj).closest('.comments_cnt_ul');
  if (type.length) {
    type = type.find('.comment_pulldown_wrapper').find('.search_adv_comment').find('li > a.active').data('type');
  } else
    type = '';
  if (typeof subjecttype != 'undefined') {
    var url = en4.core.baseUrl + 'comment/comment/list';
    viewcomment = 0;
  }
  else {
    subjecttype = "activity_action"
    var url = en4.core.baseUrl + 'comment/comment/list';
    viewcomment = 0;

  }
  scriptJquery.ajax({
    'url': url,
    'data': {
      'format': 'html',
      'page': page,
      'action_id': action_id,
      'id': action_id,
      'type': subjecttype,
      'searchtype': type,
      'viewcomment': viewcomment,
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        try {
          var dataJson = scriptJquery.parseJSON(responseHTML);
          dataJson = dataJson.body;
        } catch (err) {
          var dataJson = responseHTML;
        }
        var onbView = scriptJquery(obj).closest('.comment-feed').find('.comments').find('.comments_cnt_ul').find('.comment_more');
        var elem = scriptJquery(obj).closest('.comment-feed').find('.comments').find('.comments_cnt_ul');
        if (typeof activitycommentreverseorder != "undefined" && activitycommentreverseorder) {
          scriptJquery(dataJson).insertAfter(elem.find("li[id^='comment-']:last"));
        } else {
          scriptJquery(dataJson).insertBefore(elem.find("li[id^='comment-']:first"));
        }
        onbView.remove();
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  })
}

//view more comment
function commentactivitycommentreply(action_id, comment_id, page, obj, module, type) {
  if (typeof type == 'undefined')
    var url = en4.core.baseUrl + 'comment/index/viewcommentreply';
  else
    var url = en4.core.baseUrl + 'comment/index/viewcommentreplysubject';
  scriptJquery.ajax({
    'url': url,
    'data': {
      'format': 'html',
      'page': page,
      'comment_id': comment_id,
      'action_id': action_id,
      'moduleN': module,
      'type': type,
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        var dataJson = scriptJquery.parseJSON(responseHTML);
        var onbView = scriptJquery(obj).closest('.comment_reply_view_more');
        onbView.parent().prepend(dataJson.body);
        onbView.remove();
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  })
}

//open url in smoothbox
AttachEventListerSE('click', '.commentsmoothbox', function (e) {
  e.preventDefault();
  var url = scriptJquery(this).attr('href');
  ajaxsmoothboxopen(this);
  parent.Smoothbox.close;
  return false;
});

AttachEventListerSE('click', '.comment_btn_open', function () {
  var actionId = scriptJquery(this).attr('data-actionid');
  var commentCnt = scriptJquery(this).closest('.comment-feed');
  if (!actionId) {
    actionId = scriptJquery(this).attr('data-subjectid');
    scriptJquery('#adv_comment_subject_btn_' + actionId).trigger('click');
  } else {
    commentCnt.find('.advanced_comment_btn').trigger('click');
    //scriptJquery('#adv_comment_btn_' + actionId).trigger('click');
  }
  complitionRequestTrigger();
});

//comment button click
AttachEventListerSE('click', '.advanced_comment_btn', function (e) {
  var commentCnt = scriptJquery(this).closest('.comment-feed').find('.comments');
  if (scriptJquery(this).hasClass('active')) {
    scriptJquery(this).removeClass('active');
    commentCnt.hide();
    return;
  }
  scriptJquery(this).addClass('active');
  commentCnt.show();
  // load comment ajax
  if(commentCnt.attr("data-json") && commentCnt.find(".comments_cnt_ul").find("li").length == 0){
    let data = commentCnt.attr("data-json") ? JSON.parse(commentCnt.attr("data-json")) : {};
    var url = en4.core.baseUrl + 'comment/index/load-comment';
    scriptJquery.ajax({
      'url': url,
      type: 'POST',
      'data': {
        'format': 'html',
        'action_id': commentCnt.attr("id").replace("activity-comment-item-",''),
        isOnThisDayPage:data.isOnThisDayPage,
        isPageSubject:data.isPageSubject,
        onlyComment:data.onlyComment,
        searchType:data.searchType,

      },
      'success': function (responseHTML) {
        if (responseHTML) {
          commentCnt.find(".comments_cnt_ul").html(responseHTML);
          commentCnt.find('.advcomment_form').show();
          body = commentCnt.find('.advcomment_form').find('.comment_form').find('.body');
          body.focus();
          complitionRequestTrigger();
          return;
        }
      }
    })
  }else{
    commentCnt.find('.advcomment_form').show();
    body = commentCnt.find('.advcomment_form').find('.comment_form').find('.body');
    body.focus();
    complitionRequestTrigger();
    return;
  }
});

function getMentionData(obj) {
  scriptJquery(obj).find('.body').mentionsInput('val', function (data) {
    submitCommentForm(obj, data);
  });
}

function submitCommentForm(that, data) {
  var body = data;
  if (scriptJquery(that).hasClass("submitting")) {
    return false;
  }
  
  var file_id = scriptJquery(that).find('.file_id').val();
  var action_id = scriptJquery(that).find('.file').val();;
  var emoji_id = scriptJquery(that).find('.select_emoji_id').val();
  var gif_id = scriptJquery(that).find('.select_gif_id').val();
  var attachment = scriptJquery(that).find('._compose-link-body').length;
  if (((!body && (file_id == 0)) && emoji_id == 0 && gif_id == 0 && attachment == 0))
    return false;
  var guid = "";
  var executed = false;
  if (!scriptJquery(that).closest(".advcomment_form").find('.select_file').val()) {
    scriptJquery(that).closest(".advcomment_form").find('.select_file').remove();
    executed = true;
  }

  var formData = new FormData(that);
  if (executed == true)
    scriptJquery(that).find('.file_comment_select').parent().append('<input type="file" name="Filedata" class="select_file" accept="image/*" multiple="" value="0" style="display:none;">');
  //page
  var elem = scriptJquery(that).closest('.comment-feed').find('.feed_item_date ul').find('.custom_switch_val').find('._feed_change_option_a');
  if (elem.length) {
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }

  //store
  var elem = scriptJquery(that).closest('.comment-feed').find('.feed_item_date ul').find('.estore_switcher_cnt').find('.estore_feed_change_option_a');
  if (elem.length) {
    guid = elem.attr('data-subject');
    formData.append('guid', guid);
  }

  formData.append('bodymention', body);
  scriptJquery(that).addClass("submitting");
  submitCommentFormAjax = scriptJquery.ajax({
    type: 'POST',
    url: en4.core.baseUrl + 'comment/index/comment/',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      scriptJquery(that).removeClass('submitting');
      scriptJquery(that).find('.core_loading_cont_overlay').remove();
      try {
        var dataJson = scriptJquery.parseJSON(data);
        if (dataJson.status == 1) {
          var elemS = scriptJquery(that).closest('.comment-feed').find('.comments').find('.comments_cnt_ul');
          var getPreviousSearchComment = scriptJquery('.comment_stats_' + action_id).html();
          if (elemS.find("li[id^='comment']").length) {
            if (typeof activitycommentreverseorder != "undefined" && activitycommentreverseorder) {
              scriptJquery(dataJson.content).insertBefore(elemS.find("li[id^='comment']:first"));
            } else {
              scriptJquery(dataJson.content).insertAfter(elemS.find("li[id^='comment']:last"));
            }
          } else {
            elemS.append(dataJson.content);
          }
          var elemC = scriptJquery(that).closest('.comment-feed').find('._comments').find('.comments_cnt_ul');
          if (elemC.find('.comment_stats').length) {
            elemC.find('.comment_stats').replaceWith(dataJson.commentStats);
            var commentCount = elemC.find('.comment_stats').find('a.comment_btn_open').html();
          } else {
            elemC.prepend(dataJson.commentStats);
            var commentCount = elemC.find('.comment_stats').find('a.comment_btn_open').html();
          }
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.comment_form').find('.highlighter').html('');

          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.comment_post_options').find('button[type=submit]').addClass('disabled');

          scriptJquery('.comment_stats_' + action_id).html(getPreviousSearchComment).find('a.comment_btn_open').html(commentCount);
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.comment_form').find('.body').val('');
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.comment_form').find('.body').css('height', 'auto');
          var fileElem = scriptJquery(that);
          fileElem.find('.select_file').val('');
          fileElem.find('.select_emoji_id').val('');
          fileElem.find('.select_gif_id').val('');
          fileElem.find('.file_id').val('0');
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.link_preview').remove();
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.uploaded_file').html('');
          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.upload_file_cnt').remove();

          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.sticker_preview').html('');

          scriptJquery(that).closest('.comment-feed').find('.comments').find('.activity-comment-form').find('.comment_form_container').find('.gif_preview').html('');

          // en4.core.runonce.trigger();
          complitionRequestTrigger();
          //silence
        } else {
          alert('Something went wrong, please try again later');
        }
      } catch (err) {
        //silence
      }
    },
    error: function (data) {
      //silence
    }
  });
}

AttachEventListerSE('submit', '.activity-comment-form', function (e) {
  e.preventDefault();
  getMentionData(this);
});

//upload image in comment
AttachEventListerSE('click', '.file_comment_select', function (e) {

  //If any other attachment is active previously
  scriptJquery(this).closest('form').find('.sticker_preview').hide();
  scriptJquery(this).closest('form').find('.sticker_preview').html('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_emoji_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_gif_id').val('');

  scriptJquery(this).closest(".advcomment_form").find('.select_file').trigger('click');
});

//input file change value
AttachEventListerSE('change', '.select_file', function (e) {
  var files = this.files;
  for (var i = 0; i < files.length; i++) {
    var url = files[i].name;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if ((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp")) {
      uploadImageOnServer(this, files[i]);
    }
  }
  scriptJquery(this).val('');
});

function uploadImageOnServer(that, file) {
  var form = scriptJquery(that).closest('form');
  if (!form.find('.upload_file_cnt').length) {
    var container = scriptJquery('<div class="upload_file_cnt"></div>').insertAfter(scriptJquery(form).find('.uploaded_file'));
  } else
    var container = form.find('.upload_file_cnt');
  container.find('.file_comment_select').remove();
  var uploadFile = scriptJquery('<div class="uploaded_file"></div>')
  var uploadImageLoader = scriptJquery('<img src="application/modules/Core/externals/images/loading.gif" class="_loading" />').appendTo(uploadFile);
  scriptJquery(uploadFile).appendTo(container);
  complitionRequestTrigger();
  var formData = new FormData(scriptJquery(that).closest('form').get(0));
  formData.append('Filedata', file);
  submitCommentFormAjax = scriptJquery.ajax({
    type: 'POST',
    url: en4.core.baseUrl + 'comment/index/upload-file/',
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      var dataJson = data;
      try {
        var dataJson = scriptJquery.parseJSON(data);
        if (dataJson.status == 1) {
          if (scriptJquery(form).find('.file_id').val() == 0)
            uploadFileId = '';
          else
            uploadFileId = scriptJquery(form).find('.file_id').val();
          scriptJquery(form).find('.file_id').val(uploadFileId + dataJson.photo_id + '_album_photo' + ',');
          scriptJquery(uploadFile).html('<img src="' + dataJson.src + '"><a href="javascript:;" data-url="' + dataJson.photo_id + '" class="cancel_upload_file fas fa-times" title="Cancel"></a>');
          complitionRequestTrigger();
          container.find('.file_comment_select').remove();
          container.append(`<div class="file_comment_select activity_compose_photo_uploader center_item" title="Choose a file to upload"><i class="fa fa-plus"></i></div>`);
          //silence
          form.find("button[type='submit']").removeClass('disabled').addClass('active');
        } else {
          //scriptJquery(form).find('.file_id').val('');
          //scriptJquery(form).find('.uploaded_file').hide();
          scriptJquery(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');
        }
      } catch (err) {
        scriptJquery(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');
        //silence
      }
    },
    error: function (data) {
      scriptJquery(uploadFile).append('<a href="javascript:;" class="cancel_upload_file fas fa-times" title="Cancel"></a>');
      //silence
    }
  });

}
//emoji select in comment
scriptJquery(document).click(function (e) {
  if ((scriptJquery(".gif_comment_select").length > 0 && (scriptJquery(".gif_comment_select")[0].contains(e.target) || scriptJquery(e.target).closest('a').hasClass('gif_comment_select')))  || (scriptJquery(".emoji_comment_select").length > 0 && (scriptJquery(".emoji_comment_select")[0].contains(e.target) || scriptJquery(e.target).closest('a').hasClass('emoji_comment_select')) ) || scriptJquery(e.target).hasClass('emoji_comment_select') || scriptJquery(e.target).hasClass('feeling_emoji_comment_select') || scriptJquery(e.target).attr('id') == 'activityemoji-edit-a' || scriptJquery(e.target).attr('id') == "emotions_target" || scriptJquery(e.target).attr('id') == "activity_feeling_emojis" || scriptJquery(e.target).attr('id') == 'activity_feeling_emojisa'){
    return;
  }
  var container = scriptJquery('.comment_emotion_container').eq(0);
  if ((!container.is(e.target) && container.has(e.target).length === 0) && !scriptJquery(e.target).closest('.comment_emotion_container').length) {
    scriptJquery('.emoji_comment_select').removeClass('active');
    scriptJquery('.comment_emotion_container').hide();
  }
  if (scriptJquery(e.target).closest('.comment_emotion_container').length && !scriptJquery(e.target).hasClass("exit_gif_btn") && !scriptJquery(e.target).parent().hasClass('_activitygif_gif')) {
    // scriptJquery('.comment_emotion_container').show();
  }
  //Feeling Plugin: Emojis Work
  var container = scriptJquery('.activity_feeling_emoji_container');
  if ((!container.is(e.target) && container.has(e.target).length === 0)) {
    scriptJquery('.feeling_emoji_comment_select').removeClass('active');
    scriptJquery('.activity_feeling_emoji_container').hide();
  }
  //Feeling Plugin: Emojis Work
  scriptJquery('.gif_comment_select').removeClass('active');

});

var requestEmojiA;
AttachEventListerSE('click', '#activityemoji-statusbox', function () {
  scriptJquery("#comment_emotion_close").hide();
  scriptJquery("#sticker_close").hide();
  var topPositionOfParentDiv = scriptJquery(this).offset().top + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;
  var leftSub = 264;
  var leftPositionOfParentDiv = scriptJquery(this).offset().left - leftSub;
  leftPositionOfParentDiv = leftPositionOfParentDiv + 'px';
  scriptJquery(this).parent().find('.comment_emotion_container').css('right', 0);
  scriptJquery(this).parent().find('.comment_emotion_container').show();

  if (scriptJquery(this).hasClass('active')) {
    scriptJquery(this).removeClass('active');
    scriptJquery('#activityemoji_statusbox').hide();
    return false;
  }
  scriptJquery(this).addClass('active');

  scriptJquery('#activityemoji_statusbox').show();
  if (scriptJquery(this).hasClass('complete'))
    return false;

  var that = this;
  var url = en4.core.baseUrl + 'activity/ajax/emoji/';
  requestEmojiA = scriptJquery.ajax({
    url: url,
    data: {
      format: 'html',
    },
    evalScripts: true,
    success: function (responseHTML) {
      scriptJquery('#activityemoji_statusbox').find('.comment_emotion_container_inner').find('.comment_emotion_holder').html(responseHTML);
      scriptJquery(that).addClass('complete');
      scriptJquery('#activityemoji_statusbox').show();
    }
  });
});

AttachEventListerSE('click', 'a.emoji_comment_select', function () {

  if (scriptJquery(this).hasClass('active')) {
    scriptJquery(this).removeClass('active');
    scriptJquery('._emoji_content').hide();
    complitionRequestTrigger();
    return;
  }

  //If any other attachment is active previously
  scriptJquery(this).closest('form').find('.upload_file_cnt').hide();
  scriptJquery(this).closest('form').find('.gif_preview').html('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.file_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_gif_id').val('');

  scriptJquery("#comment_emotion_close").hide();
  scriptJquery('.emoji_comment_select').removeClass('active');
  scriptJquery('.feeling_emoji_comment_select').removeClass('active');
  scriptJquery('.activity_feeling_emoji_container').hide();
  scriptJquery("#sticker_close").show();
  clickEmojiContentContainer = this;
  scriptJquery('.emoji_content').removeClass('from_bottom');
  var topPositionOfParentDiv = scriptJquery(this).offset().top + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;
  if (scriptJquery(this).hasClass('activity_outer_emoji')) {
    var leftSub = 265;
  } else if (scriptJquery(this).hasClass('activity_emoji_content_a') && typeof activityDesign != 'undefined' && activityDesign == 2) {
    var leftSub = 55;
    var left = (scriptJquery(this).width() + leftSub) / 3;
    scriptJquery('._emoji_content ').find(".comment_emotion_container_arrow").css('left', left);
  } else {
    var leftSub = 264;
    // scriptJquery('._emoji_content').find(".comment_emotion_container_arrow").css('left', '');
  }
  var leftPositionOfParentDiv = scriptJquery(this).offset().left - leftSub;
  leftPositionOfParentDiv = leftPositionOfParentDiv + 'px';
  if (scriptJquery('#core_media_lightbox_container').length || scriptJquery('#core_media_lightbox_container_video').length)
    topPositionOfParentDiv = topPositionOfParentDiv + offsetY;

  scriptJquery('._emoji_content').css('top', topPositionOfParentDiv + 'px');
  scriptJquery('._emoji_content').css('left', leftPositionOfParentDiv).css('z-index', 100);
  scriptJquery('._emoji_content').show();
  var eTop = scriptJquery(this).offset().top; //get the offset top of the element
  var availableSpace = scriptJquery(document).height() - eTop;
  if (availableSpace < 400 && !scriptJquery('#core_media_lightbox_container').length) {
    scriptJquery('.emoji_content').addClass('from_bottom');
  }
 
  scriptJquery(this).addClass('active');
  //scriptJquery('.comment_stickers_tab_content').hide();
  scriptJquery('.comment_emotion_search_container').show();
  scriptJquery("#activityemoji_statusbox").hide();
  complitionRequestTrigger();

  if (!scriptJquery('.comment_emotion_holder').find('.empty_cnt').length)
    return;
  var that = this;
  var url = en4.core.baseUrl + 'comment/index/emoji/';
    scriptJquery.ajax({
      url: url,
      data: {
        format: 'html',
      },
      evalScripts: true,
      success: function (responseHTML) {
        scriptJquery("#comment_emotion_close").hide();
        scriptJquery('.emoji_comment_select').removeClass('active');
        scriptJquery('.feeling_emoji_comment_select').removeClass('active');
        scriptJquery('.activity_feeling_emoji_container').hide();

        scriptJquery('.emoji_content').find('.comment_emotion_container_inner').find('.comment_emotion_holder').html(responseHTML);
        scriptJquery(that).addClass('complete');
        scriptJquery('._emoji_content').show();
        complitionRequestTrigger();
        scriptJquery('._emoji_content').find(".comment_emotion_search_container").show();
        if (enablesearch == 0) {
          scriptJquery('._emoji_content').find(".comment_emotion_search_bar").hide();
        }
      }
    });
});
AttachEventListerSE('click', '.select_comment_emoji_adv > img', function (e) {
  var code = scriptJquery(this).parent().parent().attr('rel');
  var form = scriptJquery(this).closest('form');
  if (!scriptJquery(form).find('.comment_form').length) {
    var html = form.find('.body').html();
    form.find('.body').val(html + ' ' + code);
  } else {
    var html = form.find('.comment_form').find('.body').val();
    form.find('.comment_form').find('.body').val(html + ' ' + code);
  }
  var aEmoji = scriptJquery(this).closest('.emoji_content').first().parent().find('a.emoji_comment_select').trigger('click');
  complitionRequestTrigger();
});

//GIF Work
AttachEventListerSE('click', 'a.gif_comment_select', function () {
  scriptJquery("#sticker_close").hide();

  //If any other attachment is active previously
  scriptJquery(this).closest('form').find('.upload_file_cnt').hide();
  scriptJquery(this).closest('form').find('.sticker_preview').html('');
  scriptJquery(this).closest('form').find('.upload_file_cnt').find('.uploaded_file').html('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.file_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_gif_id').val('');
  scriptJquery(this).closest('form').find('.comment_post_options').find('.select_emoji_id').val('');
  scriptJquery('.comment_emotion_search_content_gif').show();

  scriptJquery(".emoji_comment_select").removeClass("active");
  scriptJquery('.feeling_emoji_comment_select').removeClass('active');
  scriptJquery('.activity_feeling_emoji_container').hide();
  scriptJquery("#comment_emotion_close").show();
  clickGifContentContainer = this;
  scriptJquery('.gif_content').removeClass('from_bottom');
  var position = scriptJquery(this).offset().top
  var topPositionOfParentDiv = position + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;
  if (scriptJquery(this).hasClass('activity_gif_content_a') && typeof activityDesign != 'undefined' && activityDesign == 2) {
    var leftSub = 55;
  } else
    var leftSub = 264;

  var leftPositionOfParentDiv = scriptJquery(this).offset().left - leftSub;
  if (scriptJquery(this).hasClass('activity_gif_content_a')) {
    var left = (scriptJquery(this).width() + leftSub) / 3;
    // scriptJquery('._gif_content').find(".comment_emotion_container_arrow").css('left', left);
  } else {
    // scriptJquery('._gif_content').find(".comment_emotion_container_arrow").css('left', '');
  }
  leftPositionOfParentDiv = leftPositionOfParentDiv + 'px';
  if (scriptJquery('#core_media_lightbox_container').length || scriptJquery('#core_media_lightbox_container_video').length)
    topPositionOfParentDiv = topPositionOfParentDiv;
  scriptJquery('._gif_content').css('top', topPositionOfParentDiv + 'px');
  scriptJquery('._gif_content').css('left', leftPositionOfParentDiv).css('z-index', 100);
  scriptJquery('._gif_content').show();

  var eTop = scriptJquery(this).offset().top; //get the offset top of the element
  var availableSpace = scriptJquery(document).height() - eTop;
  if (availableSpace < 400 && !scriptJquery('#core_media_lightbox_container').length) {
    scriptJquery('.gif_content').addClass('from_bottom');
  }

  if (scriptJquery(this).hasClass('active')) {
    scriptJquery(this).removeClass('active');
    scriptJquery('.gif_content').hide();
    complitionRequestTrigger();
    return;
  }

  scriptJquery(this).addClass('active');
  scriptJquery('.gif_content').show();
  complitionRequestTrigger();

  if (!scriptJquery('.activity_gif_holder').find('.empty_cnt').length)
    return;

  var that = this;
  var url = en4.core.baseUrl + 'activity/index/gif/',
    requestComentGif = scriptJquery.ajax({
      url: url,
      data: {
        format: 'html',
      },
      evalScripts: true,
      success: function (responseHTML) {
        scriptJquery("#sticker_close").hide();
        scriptJquery(".emoji_comment_select").removeClass("active");
        scriptJquery('.feeling_emoji_comment_select').removeClass('active');
        scriptJquery('.activity_feeling_emoji_container').hide();

        scriptJquery('.gif_content').find('.activity_gif_container_inner').find('.activity_gif_holder').html(responseHTML);
        scriptJquery(that).addClass('complete');
        scriptJquery('._gif_content').show();
        complitionRequestTrigger();
      }
    });
});

var clickGifContentContainer;
function activityGifFeedAttachment(that) {
  var code = scriptJquery(that).parent().attr('rel');
  var image = scriptJquery(that).find("img").attr('src');
  Object.entries(composeInstance.plugins).forEach(function ([key, plugin]) {
    plugin.deactivate();
    scriptJquery('#compose-' + plugin.getName() + '-activator').parent().removeClass('active');
  });
  scriptJquery('#fancyalbumuploadfileids').val('');
  scriptJquery('.fileupload-cnt').html('');
  composeInstance.getTray().empty();
  scriptJquery('#compose-tray').show();
  scriptJquery('#compose-tray').html('<div class="activity_composer_gif"><img src="' + image + '"><a class="remove_gif_image_feed notclose fas fa-times" href="javascript:;"></a></div>');
  scriptJquery('#image_id').val(code);
  scriptJquery('.gif_content').hide();
  scriptJquery('.gif_comment_select').removeClass('active');

  //Feed Background Image Work
  if (document.getElementById('feedbgid') && scriptJquery('#image_id').val()) {
    scriptJquery('#activity_body').css('height','auto');
    document.getElementById('hideshowfeedbgcont').style.display = 'none';
    scriptJquery('#feedbgid_isphoto').val(0);
    scriptJquery('.activity_post_box').css('background-image', 'none');
    scriptJquery('#activity-form').removeClass('feed_background_image');
    scriptJquery('#feedbg_content').css('display', 'none');
  }
}

/*ACTIVITY FEED*/
AttachEventListerSE('click', '.remove_gif_image_feed', function () {
  composeInstance.getTray().empty();
  scriptJquery('#image_id').val('');
  scriptJquery('#compose-tray').hide();

  //Feed Background Image Work
  if (document.getElementById('feedbgid') && scriptJquery('#image_id').val() == '') {
    var feedbgid = scriptJquery('#feedbgid').val();
    document.getElementById('hideshowfeedbgcont').style.display = 'block';
    scriptJquery('#feedbg_content').css('display', 'inline-block');
    var feedagainsrcurl = scriptJquery('#feed_bg_image_' + feedbgid).attr('src');
    scriptJquery('.activity_post_box').css("background-image", "url(" + feedagainsrcurl + ")");
    scriptJquery('#feedbgid_isphoto').val(1);
    scriptJquery('#feedbg_main_continer').css('display', 'block');
    if (feedbgid && scriptJquery('#image_id').val() != '') {
      scriptJquery('#activity-form').addClass('feed_background_image');
    }
  }
});
var gifsearchAdvReq;

var canPaginatePageNumber = 1;
AttachEventListerSE('keyup change', '.search_gif', function () {
  var value = scriptJquery(this).val();
  if (!value) {
    scriptJquery('.main_search_category_srn').show();
    scriptJquery('.main_search_cnt_srn_gif').hide();
    return;
  }
  scriptJquery('.main_search_category_srn').hide();
  scriptJquery('.main_search_cnt_srn_gif').show();
  if (typeof gifsearchAdvReq != 'undefined') {

    isGifRequestSend = false;
  }
  document.getElementById('main_search_cnt_srn_gif').innerHTML = '<div class="activitygifsearch loading_container" style="height:100%;"></div>';
  canPaginatePageNumber = 1;
  searchGifContent();
});

var isGifRequestSend = false;
function searchGifContent(valuepaginate) {

  var value = '';
  var search_gif = scriptJquery('.search_gif').val();

  if (isGifRequestSend == true)
    return;

  if (typeof valuepaginate != 'undefined') {
    value = 1;
    document.getElementById('main_search_cnt_srn_gif').innerHTML = document.getElementById('main_search_cnt_srn_gif').innerHTML + '<div class="activitygifsearchpaginate loading_container" style="height:100%;"></div>';
  }

  isGifRequestSend = true;
  gifsearchAdvReq = (scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + "activity/index/search-gif/",
    'data': {
      format: 'html',
      text: search_gif,
      page: canPaginatePageNumber,
      is_ajax: 1,
      searchvalue: value,
    },
    success: function (responseHTML) {

      scriptJquery('.activitygifsearch').remove();
      scriptJquery('.activitygifsearchpaginate').remove();

      if (scriptJquery('.activity_search_results').length == 0)
        scriptJquery('#main_search_cnt_srn_gif').append(responseHTML);
      else
        scriptJquery('.activity_search_results').append(responseHTML);
      scriptJquery('.main_search_cnt_srn_gif').slimscroll({
        height: 'auto',
        alwaysVisible: true,
        color: '#000',
        railOpacity: '0.5',
        disableFadeOut: true,
      });

      scriptJquery('.main_search_cnt_srn_gif').slimscroll().bind('slimscroll', function (event, pos) {
        if (canPaginateExistingPhotos == '1' && pos == 'bottom' && scriptJquery('.activitygifsearchpaginate').length == 0) {
          scriptJquery('.loading_container').css('position', 'absolute').css('width', '100%').css('bottom', '5px');
          searchGifContent(1);
        }
      });
      isGifRequestSend = false;
    }
  }))
}
//GIF Work End


//Emojis Work
AttachEventListerSE('click', '.feeling_emoji_comment_select', function () {
  scriptJquery("#sticker_close").hide();
  scriptJquery("#comment_emotion_close").hide();
  scriptJquery(".gif_comment_select").removeClass("active");
  scriptJquery(".emoji_comment_select").removeClass("active");

  clickFeelingEmojiContentContainer = this;
  scriptJquery('.feeling_emoji_content').removeClass('from_bottom');
  if( scriptJquery('#ajaxsmoothbox_main').length > 0)
    scriptJquery('._feeling_emoji_content').addClass('from_bottom');
  var position = scriptJquery(this).offset().top

  var topPositionOfParentDiv = position + 35;
  topPositionOfParentDiv = topPositionOfParentDiv;

  if (scriptJquery(this).hasClass('feeling_activity_emoji_content_a') && typeof activityDesign != 'undefined' && activityDesign == 2) {
    var leftSub = 55;
  } else
    var leftSub = 264;

  var leftPositionOfParentDiv = scriptJquery(this).offset().left - leftSub;
  leftPositionOfParentDiv = leftPositionOfParentDiv + 'px';

  if (scriptJquery('#core_media_lightbox_container').length || scriptJquery('#core_media_lightbox_container_video').length)
    topPositionOfParentDiv = topPositionOfParentDiv;

  scriptJquery('._feeling_emoji_content').css('top', topPositionOfParentDiv + 'px');
  scriptJquery('._feeling_emoji_content').css('left', leftPositionOfParentDiv).css('z-index', 100);
  scriptJquery('._feeling_emoji_content').show();
  var eTop = scriptJquery(this).offset().top; //get the offset top of the element
  var availableSpace = scriptJquery(document).height() - eTop;

  if (availableSpace < 400 && !scriptJquery('#core_media_lightbox_container').length) {
    scriptJquery('.feeling_emoji_content').addClass('from_bottom');
  }

  if (scriptJquery(this).hasClass('active')) {
    scriptJquery(this).removeClass('active');
    scriptJquery('.feeling_emoji_content').hide();
    scriptJquery('._feeling_emoji_content').hide();
    complitionRequestTrigger();
    return false;
  }
  scriptJquery(this).addClass('active');
  scriptJquery('.feeling_emoji_content').show();

  complitionRequestTrigger();

  if (!scriptJquery('.activity_emoji_holder').find('.empty_cnt').length)
    return;


  var that = this;
  var url = en4.core.baseUrl + 'activity/index/feelingemojicomment/',
  feeling_requestEmoji = scriptJquery.ajax({
    url: url,
    data: {
      format: 'html',
    },
    evalScripts: true,
    success: function (responseHTML) {
      scriptJquery("#sticker_close").hide();
      scriptJquery("#comment_emotion_close").hide();
      scriptJquery(".gif_comment_select").removeClass("active");
      scriptJquery(".emoji_comment_select").removeClass("active");

      scriptJquery('.activity_emoji_holder').html(responseHTML);
      scriptJquery(that).addClass('complete');
      scriptJquery('.feeling_emoji_content').show();
      complitionRequestTrigger();
    }
  });
});
//Feeling Plugin: Emojis Work


//like member
AttachEventListerSE('click', 'ul.like_main_cnt_reaction li > a', function () {
  var relAttr = scriptJquery(this).attr('data-rel');
  var typeData = scriptJquery(this).attr('data-type');
  scriptJquery('.like_main_cnt_reaction > li').removeClass('active');
  scriptJquery(this).parent().addClass('active');
  
  scriptJquery('.users_listing_popup_cont > .container_like_contnent_main').hide();
  var elem = scriptJquery('#container_like_contnent_' + relAttr);
  elem.show();
  if (typeData == 'comment')
    var typeData = 'comment';
  else
    var typeData = 'activity';
  if (elem.find('ul').find('.nocontent').length) {
    var url = en4.core.baseUrl + typeData + '/ajax/likes/';
    complitionRequestTrigger();
    var requestComentEmojiContent = scriptJquery.ajax({
      url: url,
      data: {
        format: 'html',
        id: elem.find('ul').find('.nocontent').attr('data-id'),
        resource_type: elem.find('ul').find('.nocontent').attr('data-resourcetype'),
        typeSelected: elem.find('ul').find('.nocontent').attr('data-typeselected'),
        item_id: elem.find('ul').find('.nocontent').attr('data-itemid'),
        page: 1,
        type: relAttr,
        is_ajax_content: 1,
      },
      evalScripts: true,
      success: function (responseHTML) {
        scriptJquery(elem.find('ul')).html(responseHTML);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    });

  }

});
function complitionRequestTrigger() {
  if (typeof feedUpdateFunction == "function")
    feedUpdateFunction();
  scriptJquery(window).trigger('resize');
  //page
  var elem = scriptJquery('.epage_feed_change_option_a');
  for (i = 0; i < elem.length; i++) {
    var imageItem = scriptJquery(elem[i]).attr('data-src');
    scriptJquery(elem[i]).closest('.comment-feed').find('.comment_user_img').find('img').attr('src', imageItem);
  }
  //group
  var elem = scriptJquery('.egroup_feed_change_option_a');
  for (i = 0; i < elem.length; i++) {
    var imageItem = scriptJquery(elem[i]).attr('data-src');
    scriptJquery(elem[i]).closest('.comment-feed').find('.comment_user_img').find('img').attr('src', imageItem);
  }
  //business
  var elem = scriptJquery('.ebusiness_feed_change_option_a');
  for (i = 0; i < elem.length; i++) {
    var imageItem = scriptJquery(elem[i]).attr('data-src');
    scriptJquery(elem[i]).closest('.comment-feed').find('.comment_user_img').find('img').attr('src', imageItem);
  }
  //store
  var elem = scriptJquery('.estore_feed_change_option_a');
  for (i = 0; i < elem.length; i++) {
    var imageItem = scriptJquery(elem[i]).attr('data-src');
    scriptJquery(elem[i]).closest('.comment-feed').find('.comment_user_img').find('img').attr('src', imageItem);
  }

};
/*Emotion Sticker*/
AttachEventListerSE('click', '.activity_emotion_btn_clk', function (e) {
  var index = scriptJquery(this).parent().index();
  //For enable search work
  if (enablesearch == 0) {
    index = index - 1;
  }
  var emojiCnt = scriptJquery('.comment_emotion_holder');
  emojiCnt.find('.emoji_content').hide();
  emojiCnt.find('.emoji_content').eq(index).show();
  var isComplete = scriptJquery(this).hasClass('complete')
  if (isComplete)
    return;
  var id = scriptJquery(this).attr('data-galleryid');
  var that = this;
  var emoji = scriptJquery.ajax({
    type: 'POST',
    url: 'comment/ajax/emoji-content/gallery_id/' + id,
    cache: false,
    contentType: false,
    processData: false,
    success: function (responseHTML) {
      scriptJquery(that).addClass('complete');
      emojiCnt.find('.emoji_content').eq(index).html(responseHTML);
    },
    error: function (data) {
      //silence
    },
  });
});
var clickEmojiContentContainer;
function activityFeedAttachment(that) {
  var code = scriptJquery(that).parent().parent().attr('rel');
  var image = scriptJquery(that).attr('src');
  Object.entries(composeInstance.plugins).forEach(function ([key, plugin]) {
    plugin.deactivate();
    scriptJquery('#compose-' + plugin.getName() + '-activator').parent().removeClass('active');
  });
  scriptJquery('#fancyalbumuploadfileids').val('');
  scriptJquery('.fileupload-cnt').html('');
  composeInstance.getTray().empty();
  scriptJquery('#compose-tray').show();
  scriptJquery('#compose-tray').html('<div class="activity_composer_sticker"><img src="' + image + '"><a class="remove_reaction_image_feed notclose fas fa-times" href="javascript:;"></a></div>');
  scriptJquery('#reaction_id').val(code);
  scriptJquery('.emoji_content').hide();
  scriptJquery('.emoji_comment_select').removeClass('active');

  //Feed Background Image Work
  if (document.getElementById('feedbgid') && scriptJquery('#reaction_id').val()) {
    scriptJquery('#activity_body').css('height','auto');
    //scriptJquery('#activity_post_tags_activity').css('display', 'block');
    document.getElementById('hideshowfeedbgcont').style.display = 'none';
    scriptJquery('#feedbgid_isphoto').val(0);
    //scriptJquery('#feedbgid').val(0);
    scriptJquery('.activity_post_box').css('background-image', 'none');
    scriptJquery('#activity-form').removeClass('feed_background_image');
    scriptJquery('#feedbg_content').css('display', 'none');
    
  }


}
AttachEventListerSE('click', '._simemoji_reaction > img', function (e) {
  if (scriptJquery(clickEmojiContentContainer).hasClass('activity_emoji_content_a')) {
    activityFeedAttachment(this);
  } else {
    commentContainerSelect(this);
  }
  scriptJquery('.exit_emoji_btn').trigger('click');
});

function commentContainerSelect(that) {
  var code = scriptJquery(that).parent().parent().attr('rel');
  var elem = scriptJquery(clickEmojiContentContainer).parent();
  var elemInput = elem.parent().find('span').eq(0).find('.select_emoji_id').val(code);
  var form = elem.closest('form');

  form.find("button[type='submit']").removeClass('disabled');
  var container = form.find('.sticker_preview');
  container.show();
  container.html('<img src="' + scriptJquery(that).parent().parent().find('img').attr('src') + '"><a href="javascript:;" data-url="' + code + '_comment_sticker" class="cancel_upload_sticker fas fa-times" title="Cancel"></a>');
  
  //elem.closest('form').trigger('submit');
}

//cancel sticker upload
AttachEventListerSE('click', '.cancel_upload_sticker', function (e) {
  e.preventDefault();
  var id = scriptJquery(this).attr('data-url');

  scriptJquery(this).parent().parent().parent().find("button[type='submit']").addClass('disabled');
  scriptJquery(this).parent().parent().parent().find('.comment_post_options').find('.select_emoji_id').val(0);
  scriptJquery(this).parent().parent().parent().parent().find('.sticker_preview').html('');
  complitionRequestTrigger();
});

AttachEventListerSE('click', '._activitygif_gif', function (e) {
  if (scriptJquery(clickGifContentContainer).hasClass('activity_gif_content_a')) {
    activityGifFeedAttachment(this);
  } else
    commentGifContainerSelect(this);
  scriptJquery('.exit_gif_btn').trigger('click');
});

function commentGifContainerSelect(that) {
  var code = scriptJquery(that).parent().attr('rel');
  var elem = scriptJquery(clickGifContentContainer).parent();
  var elemInput = elem.parent().find('span').eq(0).find('.select_gif_id').val(code);
  var form = elem.closest('form');

  form.find("button[type='submit']").removeClass('disabled');
  var container = form.find('.gif_preview');
  container.show();
  container.html('<img src="' + code + '"><a href="javascript:;" data-url="' + code + '_comment_gif" class="cancel_upload_gif fas fa-times" title="Cancel"></a>');
  
  //elem.closest('form').trigger('submit');
}

//cancel gif upload
AttachEventListerSE('click', '.cancel_upload_gif', function (e) {
  e.preventDefault();
  var id = scriptJquery(this).attr('data-url');

  scriptJquery(this).parent().parent().parent().find("button[type='submit']").addClass('disabled');
  scriptJquery(this).parent().parent().parent().find('.comment_post_options').find('.select_gif_id').val(0);
  scriptJquery(this).parent().parent().parent().parent().find('.gif_preview').html('');
  complitionRequestTrigger();
});

/*ACTIVITY FEED*/
AttachEventListerSE('click', '.remove_reaction_image_feed', function () {
  composeInstance.getTray().empty();
  scriptJquery('#reaction_id').val('');
  scriptJquery('#compose-tray').hide();

  //Feed Background Image Work
  if (document.getElementById('feedbgid') && document.getElementById('feedbgid').value != 0 && scriptJquery('#reaction_id').val() == '') {
    var feedbgid = scriptJquery('#feedbgid').val();
    document.getElementById('hideshowfeedbgcont').style.display = 'block';
    scriptJquery('#feedbg_content').css('display', 'inline-block');
    var feedagainsrcurl = scriptJquery('#feed_bg_image_' + feedbgid).attr('src');
    scriptJquery('.activity_post_box').css("background-image", "url(" + feedagainsrcurl + ")");
    scriptJquery('#feedbgid_isphoto').val(1);
    scriptJquery('#feedbg_main_continer').css('display', 'block');
    if (feedbgid) {
      scriptJquery('#activity-form').addClass('feed_background_image');
    }
  }
  if(document.getElementById('feedbgid')) {
    scriptJquery('#feedbg_content').css('display', 'inline-block');
  }
});
var reactionsearchAdvReq;
AttachEventListerSE('keyup change', '.search_reaction_adv', function () {
  var value = scriptJquery(this).val();
  if (!value) {
    scriptJquery('.main_search_category_srn').show();
    scriptJquery('.main_search_cnt_srn').hide();
    return;
  }
  scriptJquery('.main_search_category_srn').hide();
  scriptJquery('.main_search_cnt_srn').show();

  reactionsearchAdvReq = (scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + "comment/ajax/search-reaction/",
    'data': {
      format: 'html',
      text: value,
    },
    success: function (responseHTML) {
      scriptJquery('.main_search_cnt_srn').html(responseHTML);
    }
  }))
});
AttachEventListerSE('click', '.activity_reaction_cat', function () {
  var title = scriptJquery(this).data('title');
  scriptJquery('.search_reaction_adv').val(title);
  scriptJquery('.main_search_cnt_srn').html('')
  scriptJquery('.search_reaction_adv').trigger('change');
});
AttachEventListerSE('click', '.activity_reaction_remove_emoji, .activity_reaction_add_emoji', function (e) {
  var add = scriptJquery(this).data('add');
  var remove = scriptJquery(this).data('remove');
  var gallery = scriptJquery(this).data('gallery');
  var title = scriptJquery(this).data('title');
  var src = scriptJquery(this).data('src');
  var index = scriptJquery(this).closest('._emoji_cnt').index() + 2;
  scriptJquery(this).prop("disabled", true);
  if (scriptJquery(this).hasClass('activity_reaction_remove_emoji')) {
    var action = 'remove';
    scriptJquery('.activity_reaction_remove_emoji_' + gallery).html(add);
    scriptJquery('.activity_reaction_remove_emoji_' + gallery).removeClass('activity_reaction_remove_emoji').removeClass('activity_reaction_remove_emoji+' + gallery).addClass('activity_reaction_add_emoji').addClass('activity_reaction_add_emoji_' + gallery);
  } else {
    var action = 'add';
    scriptJquery('.activity_reaction_add_emoji_' + gallery).html(remove);
    scriptJquery('.activity_reaction_add_emoji_' + gallery).addClass('activity_reaction_remove_emoji').addClass('activity_reaction_remove_emoji_' + gallery).removeClass('activity_reaction_add_emoji').removeClass('activity_reaction_add_emoji_' + gallery);
  }
  var that = this;
  reactionsearchAdvReq = (scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + "comment/ajax/action-reaction/",
    'data': {
      format: 'html',
      gallery_id: gallery,
      actionD: action,
    },
    success: function (responseHTML) {
      scriptJquery(that).prop("disabled", false);
      if (action == 'add') {
        var content = '<a data-galleryid="' + gallery + '" class="_headbtn activity_tooltip activity_emotion_btn_clk" title="' + title + '"><img src="' + src + '" alt="' + title + '"></a>';
        owlJqueryObject('.comment_emotion_tabs')
          .trigger('add.owl.carousel', [content])
          .trigger('refresh.owl.carousel');
        scriptJquery(".comment_emotion_holder").append("<div style='display:none;position:relative;height:100%;' class='emoji_content'><div class='loading_container _emoji_cnt' style='height:100%;'></div></div>");
        activitytooltip();
      } else {
        let indexItem = 0
        let items = owlJqueryObject('.comment_emotion_tabs').find("a");
        items.each((index, item) => {
          if (parseInt(scriptJquery(item).attr("data-galleryid")) == parseInt(gallery)) {
            indexItem = index;
          }
        })
        owlJqueryObject('.comment_emotion_tabs').trigger('remove.owl.carousel', [indexItem]).trigger('refresh.owl.carousel');
        scriptJquery(".comment_emotion_holder > .emoji_content").eq(index).remove();
      }
    }
  }))
});
AttachEventListerSE('click', '.activity_reaction_preview_btn', function () {
  var gallery = scriptJquery(this).data('gallery');
  scriptJquery('#activity_reaction_gallery_cnt').hide();
  scriptJquery('.activity_reaction_gallery_preview_cnt').show();
  if (scriptJquery('#activity_reaction_preview_cnt_' + gallery).length) {
    scriptJquery('#activity_reaction_preview_cnt_' + gallery).show();
    return;
  }
  scriptJquery('.activity_reaction_gallery_preview_cnt').append('<div class="loading_container _emoji_cnt activity_reaction_gallery_preview_cnt_" id="activity_reaction_preview_cnt_' + gallery + '" style="height:100%;"></div>');
  var reactionpreviewReq = (scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + "comment/ajax/preview-reaction",
    'data': {
      format: 'html',
      gallery_id: gallery,
    },
    success: function (responseHTML) {
      scriptJquery('#activity_reaction_preview_cnt_' + gallery).html(responseHTML);
      scriptJquery('#activity_reaction_preview_cnt_' + gallery).removeClass('loading_container');
    }
  }));
});
AttachEventListerSE('click', '.activity_back_store', function () {
  scriptJquery('#activity_reaction_gallery_cnt').show();
  scriptJquery('.activity_reaction_gallery_preview_cnt').hide();
  scriptJquery('.activity_reaction_gallery_preview_cnt > .activity_reaction_gallery_preview_cnt_').hide();
});
AttachEventListerSE('click', '.comment_emotion_reset_emoji', function () {
  scriptJquery('.search_reaction_adv').val('').trigger('change');
});

function carouselReaction() {
  owlJqueryObject(".comment_emotion_tabs").owlCarousel({
    items: 6,
    itemsDesktop: [1199, 6],
    itemsDesktopSmall: [979, 6],
    itemsTablet: [768, 6],
    itemsMobile: [479, 6],
    nav: true,
    dots: false,
    loop: false,
    afterAction: function () {
      if (this.itemsAmount > this.visibleItems.length) {
        scriptJquery('.owl-next').show();
        scriptJquery('.owl-prev').show();
        scriptJquery('.owl-next').show('');
        scriptJquery('.owl-prev').show('');
        if (this.currentItem == 0) {
          scriptJquery('.owl-prev').hide();
        }
        if (this.currentItem == this.maximumItem) {
          scriptJquery('.owl-next').hide('');
        }
      } else {
        scriptJquery('.owl-next').hide();
        scriptJquery('.owl-prev').hide();
      }
    },
  });
}
/*FILTERING OPTIONS*/
AttachEventListerSE('click', '.search_adv_comment_a', function (e) {
  if (scriptJquery(this).hasClass('active'))
    return;
  scriptJquery(this).closest('.search_adv_comment').find('li a').removeClass('active');
  scriptJquery(this).closest('.comment_pulldown_wrapper').find('.search_advcomment_txt').find('span').text(scriptJquery(this).text());
  scriptJquery(this).addClass('active');
  var action_id = scriptJquery(this).closest('.comment_pulldown_wrapper').data('actionid');
  var ulObj = scriptJquery(this).closest('.comments_cnt_ul');
  var type = scriptJquery(this).data('type');
  if (ulObj.find('.comment_stats').length) {
    ulObj.children().not(':first').remove();
    ulObj.append('<li style="position:relative" class="loading_container_li"><div class="loading_container" style="display:block;"></div></li>');
  } else {
    ulObj.html('<li style="position:relative"  class="loading_container_li"><div class="loading_container" style="display:block;"></div></li>');
  }

  commentsearchaction(action_id, 1, this, type, ulObj, scriptJquery(this).data('subjectype'));
});


//view more comment
function commentsearchaction(action_id, page, obj, type, ulObj, subjectType) {
  var viewcomment = 0;
  if (typeof subjectType != 'undefined') {
    var url = en4.core.baseUrl + 'comment/comment/list';
  } else {
    var url = en4.core.baseUrl + 'comment/index/load-comment';
    viewcomment = 1;
  }
  scriptJquery.ajax({
    'url': url,
    'data': {
      'format': 'html',
      'page': page,
      'action_id': action_id,
      'id': action_id,
      'type': subjectType,
      'searchtype': type,
    },
    success: function (responseHTML) {
      if (responseHTML) {
        try {
          var dataJson = scriptJquery.parseJSON(responseHTML);
          dataJson = dataJson.body;
        } catch (err) {
          var dataJson = responseHTML;
        }
        ulObj.find('.loading_container_li').remove();
        //dataJson = scriptJquery(dataJson);
        //dataJson = dataJson.find(".comments_cnt_ul").html();
        ulObj.append(dataJson);
        ulObj.find('ul.comment-feed').show();
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
        // if (viewcomment) {
        //   ulObj.find(".search_advcomment_txt span").html(`<b>Sort By:</b> ${scriptJquery(obj).html()} `);
        // }
      }
    }
  })

}

function removePreview(commentde_id, comment_id, type) {
  (scriptJquery.ajax({
    method: 'post',
    'url': en4.core.baseUrl + 'comment/index/removepreview',
    'data': {
      format: 'html',
      comment_id: commentde_id,
      type: type,

    },
    success: function (responseHTML) {
      //if(document.getElementById('remove_previewli_'+ comment_id))
      scriptJquery('#remove_previewli_' + comment_id).remove();
      //if(document.getElementById('remove_preview_'+ comment_id))
      scriptJquery('#remove_preview_' + comment_id).remove();
      //if(document.getElementById('commentpreview_'+ comment_id))
      scriptJquery('#commentpreview_' + comment_id).remove();
    }
  }));
  return false;
}

function showhidecommentsreply(comment_id, action_id) {
  if (document.getElementById('comments_reply_' + comment_id + '_' + action_id).style.display == 'block') {

    if (document.getElementById('comments_reply_' + comment_id + '_' + action_id))
      document.getElementById('comments_reply_' + comment_id + '_' + action_id).style.display = 'none';

    if (document.getElementById('comments_reply_reply_' + comment_id + '_' + action_id))
      document.getElementById('comments_reply_reply_' + comment_id + '_' + action_id).style.display = 'none';

    if (document.getElementById('comments_reply_body_' + comment_id))
      document.getElementById('comments_reply_body_' + comment_id).style.display = 'none';

    if (document.getElementById('comments_body_' + comment_id))
      document.getElementById('comments_body_' + comment_id).style.display = 'none';

    if (scriptJquery('#hideshow_' + comment_id + '_' + action_id))
      scriptJquery('#hideshow_' + comment_id + '_' + action_id).removeClass('fa-regular fa-square-minus').addClass('fa-regular fa-square-plus');
  } else {

    if (document.getElementById('comments_reply_' + comment_id + '_' + action_id))
      document.getElementById('comments_reply_' + comment_id + '_' + action_id).style.display = 'block';

    if (document.getElementById('comments_reply_reply_' + comment_id + '_' + action_id))
      document.getElementById('comments_reply_reply_' + comment_id + '_' + action_id).style.display = 'block';

    if (document.getElementById('comments_reply_body_' + comment_id))
      document.getElementById('comments_reply_body_' + comment_id).style.display = 'block';

    if (document.getElementById('comments_body_' + comment_id))
      document.getElementById('comments_body_' + comment_id).style.display = 'block';

    if (scriptJquery('#hideshow_' + comment_id + '_' + action_id))
      scriptJquery('#hideshow_' + comment_id + '_' + action_id).removeClass('fa-regular fa-square-plus').addClass('fa-regular fa-square-minus');
  }
}

AttachEventListerSE('click', '.activity_upvote_btn', function () {
  // if (scriptJquery(this).hasClass('_disabled'))
  //   return;
  if (scriptJquery(this).closest('.feed_votebtn').hasClass('active'))
    return;
  scriptJquery(this).closest('.feed_votebtn').addClass('active');
  var itemguid = scriptJquery(this).data('itemguid');
  var that = this;
  //var userguid  = scriptJquery(this).data('userguid');
  var guid = "";
  var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .custom_switch_val').find('a').first();
  if (!guidItem.length)
    var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
  if (guidItem)
    guid = guidItem.data('rel');
  var url = en4.core.baseUrl + 'comment/index/voteup';
  scriptJquery.ajax({
    'url': url,
    'data': {
      'format': 'html',
      'itemguid': itemguid,
      'userguid': guid,
      'type': 'upvote',
    },
    success: function (responseHTML) {
      if (responseHTML) {
        scriptJquery(that).closest('.feed_votebtn').replaceWith(responseHTML);
      }
      scriptJquery(that).closest('.feed_votebtn').removeClass('active');
    }
  })
});
AttachEventListerSE('click', '.activity_downvote_btn', function () {
  // if (scriptJquery(this).hasClass('_disabled'))
  //   return;
  if (scriptJquery(this).closest('.feed_votebtn').hasClass('active'))
    return;
  scriptJquery(this).closest('.feed_votebtn').addClass('active');
  var itemguid = scriptJquery(this).data('itemguid');
  var that = this;
  //var userguid  = scriptJquery(this).data('userguid');
  var guid = "";
  var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .custom_switch_val').find('a').first();
  if (!guidItem.length)
    var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
  if (guidItem)
    guid = guidItem.data('rel');
  var url = en4.core.baseUrl + 'comment/index/voteup';
  scriptJquery.ajax({
    'url': url,
    'data': {
      'format': 'html',
      'itemguid': itemguid,
      'userguid': guid,
      'type': 'downvote',
    },
    success: function (responseHTML) {
      if (responseHTML) {
        scriptJquery(that).closest('.feed_votebtn').replaceWith(responseHTML);
      }
      scriptJquery(that).closest('.feed_votebtn').removeClass('active');
    }
  })
})
//like comment
AttachEventListerSE('click', '.commentcommentlike', function () {
  var obj = scriptJquery(this);
  previousCommentLikeObj = obj.closest('.comment_hoverbox_wrapper');
  var action_id = scriptJquery(this).attr('data-actionid');
  //var guid = scriptJquery(this).attr('data-guid');
  var guid = "";
  var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .custom_switch_val').find('a').first();
  if (!guidItem.length)
    var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
  if (guidItem.length)
    guid = guidItem.data('rel');
  var comment_id = scriptJquery(this).attr('data-commentid');
  var type = scriptJquery(this).attr('data-type');
  var datatext = scriptJquery(this).attr('data-text');
  var subject_id = scriptJquery(this).attr('data-subjectid');
  //check for like
  var isLikeElem = false;
  if (scriptJquery(this).hasClass('reaction_btn')) {
    var image = scriptJquery(this).find('.reaction').find('i').css('background-image');
    image = image.replace('url(', '').replace(')', '').replace(/\"/gi, "");
    var elem = scriptJquery(this).parent().parent().parent().find('a');
    isLikeElem = true;
  } else {
    var image = scriptJquery(this).parent().find('.comment_hoverbox').find('span').first().find('.reaction_btn').find('.reaction').find('i').css('background-image');
    image = image.replace('url(', '').replace(')', '').replace(/\"/gi, "");
    var elem = scriptJquery(this);
    isLikeElem = false
  }

  var likeWorkText = scriptJquery(elem).attr('data-like');
  var unlikeWordText = scriptJquery(elem).attr('data-unlike');

  //unlike
  if (scriptJquery(elem).hasClass('_reaction') && !isLikeElem) {
    scriptJquery(elem).find('i').removeAttr('style');
    scriptJquery(elem).find('span').html(unlikeWordText);
    scriptJquery(elem).removeClass('commentcommentunlike').removeClass('_reaction').addClass('commentcommentlike');
    scriptJquery(elem).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  } else {
    //like  
    scriptJquery(elem).find('i').css('background-image', 'url(' + image + ')');
    scriptJquery(elem).find('span').html(datatext);
    scriptJquery(elem).removeClass('commentcommentlike').addClass('_reaction').addClass('commentcommentunlike');
    scriptJquery(elem).parent().addClass('feed_item_option_unlike').removeClass('feed_item_option_like');
  }

  // 	var parentObject = previousCommentLikeObj.parent().html();
  // 	var parentElem = previousCommentLikeObj.parent();
  // 	previousCommentLikeObj.parent().html('');
  // 	parentElem.html(parentObject);
  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/like',
    data: {
      format: 'json',
      action_id: action_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      guid: guid,
      sbjecttype: scriptJquery(this).attr('data-sbjecttype'),
      subjectid: scriptJquery(this).attr('data-subjectid'),
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        scriptJquery(obj).closest(".comments_info").find(".comments_likes_total").eq(0).remove();
        scriptJquery(obj).closest('.comments_date').replaceWith(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
});
//like feed action content
AttachEventListerSE('click', '.commentcommentunlike', function () {
  var obj = scriptJquery(this);
  var action_id = scriptJquery(this).attr('data-actionid');
  var comment_id = scriptJquery(this).attr('data-commentid');
  var type = scriptJquery(this).attr('data-type');
  var datatext = scriptJquery(this).attr('data-text');
  var likeWorkText = scriptJquery(this).attr('data-like');
  var unlikeWordText = scriptJquery(this).attr('data-unlike');

  var guid = "";
  var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .custom_switch_val').find('a').first();
  if (!guidItem.length)
    var guidItem = scriptJquery(this).closest('.comment-feed').find('.feed_item_date > ul > .estore_switcher_cnt').find('a').first();
  if (guidItem)
    guid = guidItem.data('rel');
  //check for unlike
  scriptJquery(this).find('i').removeAttr('style');
  scriptJquery(this).find('span').html(likeWorkText);
  scriptJquery(this).removeClass('commentcommentunlike').removeClass('_reaction').addClass('commentcommentlike');
  scriptJquery(this).parent().addClass('feed_item_option_like').removeClass('feed_item_option_unlike');
  var ajax = scriptJquery.ajax({
    url: en4.core.baseUrl + 'comment/index/unlike',
    data: {
      format: 'json',
      action_id: action_id,
      comment_id: comment_id,
      subject: en4.core.subject.guid,
      guid: guid,
      sbjecttype: scriptJquery(this).attr('data-sbjecttype'),
      subjectid: scriptJquery(this).attr('data-subjectid'),
      type: type
    },
    'success': function (responseHTML) {
      if (responseHTML) {
        scriptJquery(obj).closest(".comments_info").find(".comments_likes_total").eq(0).remove();
        scriptJquery(obj).closest('.comments_date').replaceWith(responseHTML.body);
        // en4.core.runonce.trigger();
        complitionRequestTrigger();
      }
    }
  });
});
function setCommentFocus(comment_id) {
  document.getElementById("comment" + comment_id).focus();
}

AttachEventListerSE("click", ".body", function () {
  if (!scriptJquery(this).is(":focus")) {
    scriptJquery(this).focus();
  }
});

function translateTextWithLink(action_id, languageTranslate) {

  var tempDiv = document.createElement("div"); // Create a temporary div
  tempDiv.innerHTML = scriptJquery('#activity_feed_item_bodytext_'+action_id).html(); // Set the inner HTML
  var text = tempDiv.textContent || tempDiv.innerText; // Extract and return text

  // Construct Google Translate URL for translating text
  var translateUrl = "https://translate.google.com/?sl=auto&tl="+languageTranslate+"&text=" + encodeURIComponent(text) + "&op=translate";
  
  // Open translation in a new tab
  javascript:window.open(translateUrl, "_blank",'height=500,width=800');
}
