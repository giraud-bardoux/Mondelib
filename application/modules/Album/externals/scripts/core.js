
/* $Id: core.js 9572 2011-12-27 23:41:06Z john $ */



// (function() { // START NAMESPACE
// var $ = 'id' in document ? document.id : window.$;



en4.album = {

  composer : false,

  getComposer : function(){
    if( !this.composer ){
      this.composer = new en4.album.acompose();
    }

    return this.composer;
  },

  rotate : function(photo_id, angle) {
    request = scriptJquery.ajax({
      url: en4.core.baseUrl + 'album/photo/rotate',
      data : {
        format : 'json',
        photo_id : photo_id,
        angle : angle
      },
      method:'post',
      dataType: 'json',
      success: function (response) {
        if(typeof response == 'object' &&
            typeof response.status !=="undefined" &&
            response.status == false ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        } else if( typeof response != 'object' ||
          typeof response.status ==="undefined" ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        }
        window.location.reload(true);
      },
      error: function () {
         
      }
    });
    return request;
  },
  flip : function(photo_id, direction) {
    request = scriptJquery.ajax({
      url: en4.core.baseUrl + 'album/photo/flip',
      data : {
        format : 'json',
        photo_id : photo_id,
        direction : direction
      },
      method:'post',
      dataType: 'json',
      success: function (response) {
        if(typeof response == 'object' &&
            typeof response.status !=="undefined" &&
            response.status == false ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        } else if( typeof response != 'object' ||
          typeof response.status ==="undefined" ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        }
        window.location.reload(true);
      },
      error: function () {}
    });
    return request;
  },

  crop : function(photo_id, x, y, w, h) {
    if( $type(x) == 'object' ) {
      h = x.h;
      w = x.w;
      y = x.y;
      x = x.x;
    }
    request = scriptJquery.ajax({
      url : en4.core.baseUrl + 'album/photo/crop',
      data : {
        format : 'json',
        photo_id : photo_id,
        x : x,
        y : y,
        w : w,
        h : h
      },
      success: function(response) {
        // Check status
        if( $type(response) == 'object' &&
            $type(response.status) &&
            response.status == false ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        } else if( $type(response) != 'object' ||
          !$type(response.status) ) {
          en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
          return;
        }

        // Ok, let's refresh the page I guess
        window.location.reload(true);
      }
    });
    return request;
  }

};
en4_activity_compose_icompose = typeof en4_activity_compose_icompose !== "undefined" ? en4_activity_compose_icompose : class{};
class en4_album_acompose extends en4_activity_compose_icompose {
  name = 'photo';
  active = false;
  options = {};
  frame = false;
  photo_id = false;
  constructor(element, options){
    if( !element ) element = scriptJquery('#activity-compose-photo');
    super(element, options);
  }
  activate(){
    super.parent();
    this.element.css("display",'');
    scriptJquery('#activity-compose-photo-input').css("display",'');
    scriptJquery('#activity-compose-photo-loading').css("display",'none');
    scriptJquery('#activity-compose-photo-preview').css("display",'none');
    scriptJquery('#activity-form').on('beforesubmit', this.checkSubmit.bind(this));
    this.active = true;
    // @todo this is a hack
    scriptJquery('#activity-post-submit').css("display",'none');
  }

  deactivate(){
    if( !this.active ) return;
    this.active = false
    this.photo_id = false;
    if(this.frame.length) this.frame.remove();
    this.frame = false;
    scriptJquery('#activity-compose-photo-preview').empty();
    scriptJquery('#activity-compose-photo-input').css("display",'');
    this.element.css("display",'none');
    scriptJquery('#activity-form').off('submit', this.checkSubmit.bind(this));;

    // @todo this is a hack
    scriptJquery('#activity-post-submit').css("display",'block');
    scriptJquery('#activity-compose-photo-activate').css("display",'');
    scriptJquery('#activity-compose-link-activate').css("display",'');
  }
  process(){
    if( this.photo_id ) return;
    
    if( !this.frame ){
      this.frame = scriptJquery.ajax({
        src : 'about:blank',
        name : 'albumComposeFrame',
      }).css({
          display : 'none'
      });
      this.frame.appendTo(this.element);
    }
    scriptJquery('#activity-compose-photo-input').css("display",'none');
    scriptJquery('#activity-compose-photo-loading').css("display",'');
    scriptJquery('#activity-compose-photo-form')[0].target = 'albumComposeFrame';
    scriptJquery('#activity-compose-photo-form').trigger("submit");
  }
  processResponse(responseObject){
    if( this.photo_id ) return;
    
    (scriptJquery.crtEle('img', {
      src : responseObject.src,
    })).appendTo(scriptJquery('#activity-compose-photo-preview'));
    scriptJquery('#activity-compose-photo-loading').css("display",'none');
    scriptJquery('#activity-compose-photo-preview').css("display",'');
    this.photo_id = responseObject.photo_id;

    // @todo this is a hack
    scriptJquery('#activity-post-submit').css("display",'block');
    scriptJquery('#activity-compose-photo-activate').css("display",'none');
    scriptJquery('#activity-compose-link-activate').css("display",'none');
  }

  checkSubmit(event)
  {
    if( this.active && this.photo_id )
    {
      //event.stop();
      scriptJquery('#activity-form')[0].attachment_type.value = 'album_photo';
      scriptJquery('#activity-form')[0].attachment_id.value = this.photo_id;
    }
  }
};
en4.album.acompose = en4_album_acompose;
// })(); // END NAMESPACE
