/* $Id: core.js 9968 2013-03-19 00:20:56Z john $ */

// (function() { // START NAMESPACE
// var $ = 'id' in document ? document.id : window.$;

var isLoadedFromAjax;
function AttachEventListerSE(eventName,object,func){

  if(typeof object == "function"){
    if(!isLoadedFromAjax) {
      scriptJquery(document).on(eventName,object);
    } else {
      scriptJquery(document).off(eventName,object).on(object);
    }
  }else {
    if(!isLoadedFromAjax) {
      scriptJquery(document).on(eventName,object,func);
    } else {
      scriptJquery(document).off(eventName,object).on(eventName,object,func);
    }
  }
}

en4 = {};




/**
 * Core methods
 */
en4.core = {

  baseUrl : '',

  basePath : '',

  loader : false,

  environment : 'production',

  setBaseUrl : function(url)
  {
    this.baseUrl = url;
    var m = this.baseUrl.match(/^(.+?)index[.]php/i);
    this.basePath = ( m ? m[1] : this.baseUrl );
  },

  subject : {
    type : '',
    id : 0,
    guid : ''
  },

  showError : function(text){
    Smoothbox.close();
    Smoothbox.instance = new Smoothbox.Modal.String({
      bodyText : text
    });
  }

};


/**
 * Run Once scripts
 */
en4.core.runonce = {

  executing : false,

  fns : [],

  add : function(fn){
    this.fns.push(fn);
  },

  trigger : function(){
    if( this.executing ) return;
    this.executing = true;
    var fn;
    while( (fn = this.fns.shift()) ){
      try {
        fn();
      }catch(err){}
    }
    this.fns = [];
    this.executing = false;
  }

};


/**
 * shutdown scripts
 */
en4.core.shutdown = {

  executing : false,

  fns : [],

  add : function(fn){
    this.fns.push(fn);
  },

  trigger : function(){
    if( this.executing ) return;
    this.executing = true;
    var fn;
    while( (fn = this.fns.shift()) ){
      try{fn();}catch(err){};
    }
    this.fns = [];
    this.executing = false;
  }

};

window.addEventListener('load', function(){
  en4.core.runonce.trigger();
});
// This is experimental
window.addEventListener('DOMContentLoaded', function(){
  en4.core.runonce.trigger();
});

window.addEventListener('unload', function() {
  en4.core.shutdown.trigger();
});


/**
 * Dynamic page loader
 */
en4.core.dloader = {

  loopId : false,

  currentHref : false,

  activeHref : false,

  xhr : false,

  frame : false,

  enabled : false,

  previous : false,

  hash : false,

  registered : false,

  setEnabled : function(flag) {
    this.enabled = ( flag == true );
  },

  start : function(options) {
    if( this.frame || this.xhr ) return this;

    this.activeHref = options.url;

    // Use an iframe for get requests
    if( $type(options.conntype) && options.conntype == 'frame' ) {
      options = scriptJquery.extend({
        data : {
          format : 'async',
          mode : 'frame'
        },
        styles : {
          'position' : 'absolute',
          'top' : '-200px',
          'left' : '-200px',
          'height' : '100px',
          'width' : '100px'
        },
        events : {
          //load : this.handleLoad.bind(this)
        }
      }, options);

      if( $type(options.url) ) {
        options.src = options.url;
        delete options.url;
      }
      // Add format as query string
      if( $type(options.data) ) {
        var separator = ( options.src.indexOf('?') > -1 ? '&' : '?' );
        options.src += separator + $H(options.data).toQueryString();
        delete options.data;
      }
      this.frame = scriptJquery.crtEle('iframe',options);
      this.frame.appendTo(scriptJquery(document.body));
    } else {
      options = scriptJquery.extend({
        method : 'get',
        dataType : 'html',
        data : {
          'format' : 'html',
          'mode' : 'xhr'
        },
        complete : this.handleLoad.bind(this)
      }, options);
      this.xhr = scriptJquery.ajax(options);
    }

    return this;
  },

  cancel : function() {
    if( this.frame ) {
      this.frame.destroy();
      this.frame = false;
    }
    if( this.xhr ) {
      this.xhr.cancel();
      this.xhr = false;
    }
    this.activeHref = false;
    return this;
  },

  attach : function(els) {
    var bind = this;

    if( !$type(els) ) {
      els = scriptJquery('a');
    }

    // Attach to links
    els.each(function(element) {
      if( !this.shouldAttach(element) ) {
        return;
      } else if( element.hasEvents() ) {
        return;
      }

      element.addEventListener('click', function(event) {
        if( !this.shouldAttach(element) ) {
          return;
        }

        var events = element.getEvents('click');
        if( events && events.length > 1 ) {
          return;
        }


        // Remove host + basePath
        var basePath = window.location.protocol + '//' + window.location.hostname + en4.core.baseUrl;
        var newPath;
        if( element.href.indexOf(basePath) === 0 ) {
          // Cancel link click
          if( event ) {
            event.stopPropagation();
            event.preventDefault();
          }

          // Start request
          newPath = element.href.substring(basePath.length);

          // Update url
          if( this.hasPushState() ) {
            this.push(element.href);
          } else {
            this.push(newPath);
          }

          // Make request
          this.startRequest(newPath);
        }
      }.bind(this));
    }.bind(this));

    // Monitor location
    //window.addEventListener('unload', this.monitorAddress.bind(this));
    this.currentHref = window.location.href;

    if( !this.registered ) {
      this.registered = true;
      if( this.hasPushState() ) {
        window.addEventListenerListener("popstate", function(e) {
          this.pop(e)
        }.bind(this));
      } else {
        this.loopId = this.monitor.periodical(200, this);
      }
    }
  },

  shouldAttach : function(element) {
    return (
      element.get('tag') == 'a' &&
      !element.onclick &&
      element.href &&
      !element.href.match(/^(javascript|[#])/) &&
      !element.hasClass('no-dloader') &&
      !element.hasClass('smoothbox')
    );
  },

  handleLoad : function(response1, response2, response3, response4) {
    var response;

    if( this.frame ) {
      try {
        response = (function() {
          return response1;
        }, function(){
          return this.frame.contentWindow.document.documentElement.innerHTML;
        }.bind(this));
      } catch(err){}
    } else if( this.xhr ) {
      response = response3;
    }

    if( response ) {
      // Shutdown previous scripts
      en4.core.shutdown.trigger();
      // Replace HTML
      scriptJquery('#global_content').html(response);
      // Evaluate scripts in content
      en4.core.request.evalScripts(scriptJquery('#global_content'));
      // Attach dloader to a's in content
      this.attach(scriptJquery('#global_content').find('a'));
      // Execute runonce
      en4.core.runonce.trigger();
    }

    this.cancel();
    this.activeHref = false;
  },

  handleRedirect : function(url) {
    this.push(url);
    this.startRequest(url);
  },

  startRequest : function(url) {

    var fullUrl = window.location.protocol + '//' + window.location.hostname + en4.core.baseUrl + url;
    //console.log(url, fullUrl);

    // Cancel current request if active
    if( this.activeHref ) {
      // Ignore if equal
      if( this.activeHref == url ) {
        return;
      }
      // Otherwise cancel an continue
      this.cancel();
    }

    //$('global_content').innerHTML = '<h1>Loading...</h1>';

    this.start({
      url : fullUrl,
      conntype : 'frame'
    });

  },



  // functions for history
  hasPushState : function() {
    //return false;
    return ('pushState' in window.history);
  },

  push : function(url, title, state) {
    if( this.previous == url ) return;

    if( this.hasPushState() ) {
      window.history.pushState(state || null, title || null, url);
      this.previous = url;
    } else {
      window.location.hash = url;
    }
  },

  replace : function(url, title, state) {
    if( this.hasPushState() ) {
      window.history.replaceState(state || null, title || null, url);
    } else {
      this.hash = '#' + url;
      this.push(url);
    }
  },

  pop : function(event) {
    if( this.hasPushState() ) {
      if( window.location.pathname.indexOf(en4.core.baseUrl) === 0 ) {
        this.onChange(window.location.pathname.substring(en4.core.baseUrl.length));
      } else {
        this.onChange(window.location.pathname);
      }
    } else {
      var hash = window.location.hash;
      if( this.hash == hash ) {
        return;
      }

      this.hash = hash;
      this.onChange(hash.substr(1));
    }
  },

  onChange : function(url) {
    this.startRequest(url);
  },

  back : function() {
    window.history.back();
  },

  forward : function() {
    window.history.forward();
  },

  monitor : function() {
    if( this.hash != window.location.hash ) {
      this.pop();
    }
  }
};


/**
 * Request pipeline
 */
en4.core.request = {

  activeRequests : [],

  isRequestActive : function(){
    return ( this.activeRequests.length > 0 );
  },

  send : function(req, options){
    options = options || {};
    if( !$type(options.force) ) options.force = false;


    // If there are currently active requests, ignore
    if(this.activeRequests.length > 0 && !options.force ){
      req.abort();
      return req;
    }
    this.activeRequests.push(req);
    // Process options
    if( !$type(options.htmlJsonKey) ) options.htmlJsonKey = 'body';
    if( $type(options.element) ){
      options.updateHtmlElement   = options.element;
      options.evalsScriptsElement = options.element;
    }

    // OnComplete
    var bind = this;
    req.success(function(response, response2, response3, response4){
      bind.activeRequests.forEach((re,i)=>{
        if(req == re){
          bind.activeRequests.splice(i,1);
        }
      });
      if(options.successCallBack){
        options.successCallBack(response, response2, response3, response4);
      }
      var htmlBody;
      var jsBody;

      // Get response
      if( $type(response) == 'object' ){ // JSON response
        htmlBody = response[options.htmlJsonKey];
      } else if( $type(response) == 'string' ){ // HTML response
        htmlBody = response;
        jsBody = response;
      }

      // An error probably occurred
      if( !response && !response3 && $type(options.updateHtmlElement) ){
        en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
        return;
      }

      if( $type(response) == 'object' && $type(response.status) && response.status == false  && $type(response.error) === 'string' )
      {
        en4.core.showError(response.error + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
        return;
      }

      if( $type(response) == 'object' && $type(response.status) && response.status == false /* && $type(response.error) */ )
      {
        en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
        return;
      }
      if( $type(options.updateHtmlElement) && htmlBody ){
        if( $type(options.updateHtmlMode) && options.updateHtmlMode == 'append' ){
          scriptJquery(htmlBody).appendTo(scriptJquery(options.updateHtmlElement));
        } else if( $type(options.updateHtmlMode) && options.updateHtmlMode == 'prepend' ){

          scriptJquery(htmlBody).prependTo(scriptJquery(options.updateHtmlElement));

        } else if ($type(options.updateHtmlMode) && options.updateHtmlMode == 'comments' && scriptJquery(htmlBody).length > 1 && scriptJquery(htmlBody).eq(0).find('.comments').length) {
            scriptJquery(options.updateHtmlElement).find('.comments').remove();
            scriptJquery(options.updateHtmlElement).find('.feed_item_date').remove();
            if (scriptJquery(htmlBody).eq(0).find('.feed_item_date').length)
                scriptJquery(htmlBody).eq(0).find('.feed_item_date').appendTo(scriptJquery(options.updateHtmlElement.find('.feed_item_body')));
            scriptJquery(htmlBody).eq(0).find('.comments').appendTo(scriptJquery(options.updateHtmlElement.find('.feed_item_body')));
        } else if ($type(options.updateHtmlMode) && options.updateHtmlMode == 'comments2') {
          scriptJquery(options.updateHtmlElement).empty();
          scriptJquery(htmlBody).appendTo(scriptJquery(options.updateHtmlElement));
        } else {
          scriptJquery(options.updateHtmlElement).empty();
          scriptJquery(htmlBody).appendTo(scriptJquery(options.updateHtmlElement));
        }
        Smoothbox.bind(scriptJquery(options.updateHtmlElement));
      }

      if( !$type(options.doRunOnce) || !options.doRunOnce ){
        en4.core.runonce.trigger();
      }
    });

    req.error(function(){
      bind.activeRequests.forEach((re,i)=>{
        if(req == re){
          bind.activeRequests.splice(i,1);
        }
      });
    });
    return this;
  },

  evalScripts : function(e) {
    element = scriptJquery(this);
    if( !element ) return this;
    element.find('script').each(function(script){
      if( script.type != 'text/javascript' ) return;
      if( script.src ){
        scriptJquery.getScript(script.src);
      }
      else if( script.innerHTML.trim() ) {
        eval(script.innerHTML);
      }
    });

    return this;
  }

};


/**
 * Comments
 */
// en4.core.comments = {

//   loadComments : function(type, id, page){
//     en4.core.request.send(scriptJquery.ajax({
//       url : en4.core.baseUrl + 'core/comment/list',
//       method:'post',
//       dataType : 'html',
//       data : {
//         format : 'html',
//         type : type,
//         id : id,
//         page : page
//       }
//     }), {
//       'element' : scriptJquery('#comments')
//     });
//   },

//   attachCreateComment : function(formElement){
//     var bind = this;
//     formElement.addEventListener('submit', function(event){
//       event.stop();
//       var form_values  = formElement.toQueryString();
//           form_values += '&format=json';
//           form_values += '&id='+formElement.identity.value;
//       en4.core.request.send(scriptJquery.ajax({
//         url : en4.core.baseUrl + 'core/comment/create',
//         data : form_values
//       }), {
//         'element' : $('comments')
//       });
//       //bind.comment(formElement.type.value, formElement.identity.value, formElement.body.value);
//     })
//   },

//  comment : function(formData){
//     if( formData.body.trim() == '') return;
//     scriptJquery('#comment-compose-container').after('<div class="comment_loading_overlay"></div>');
//     en4.core.request.send(scriptJquery.ajax({
//       method:'post',
//       dataType: 'json',
//       url : en4.core.baseUrl + 'core/comment/create',
//       data : formData,
//     }), {
//       'element' : scriptJquery('#comments')
//     });
//   },

//   like : function(type, id, comment_id) {
//     en4.core.request.send(scriptJquery.ajax({
//       url : en4.core.baseUrl + 'core/comment/like',
//       method:'post',
//       dataType:'json',
//       data : {
//         format : 'json',
//         type : type,
//         id : id,
//         comment_id : comment_id
//       }
//     }), {
//       'element' : scriptJquery('#comments')
//     });
//   },

//   unlike : function(type, id, comment_id) {
//     en4.core.request.send(scriptJquery.ajax({
//       url : en4.core.baseUrl + 'core/comment/unlike',
//       method:'post',
//       dataType:'json',
//       data : {
//         format : 'json',
//         type : type,
//         id : id,
//         comment_id : comment_id
//       }
//     }), {
//       'element' : scriptJquery('#comments')
//     });
//   },

//   showLikes : function(type, id){
//     en4.core.request.send(scriptJquery.ajax({
//       url : en4.core.baseUrl + 'core/comment/list',
//       method:'post',
//       dataType:'html',
//       data : {
//         format : 'html',
//         type : type,
//         id : id,
//         viewAllLikes : true
//       }
//     }), {
//       'element' : scriptJquery('#comments')
//     });
//   },

//   deleteComment : function(type, id, comment_id) {
//     if( !confirm(en4.core.language.translate('Are you sure you want to delete this?')) ) {
//       return;
//     }
//     (scriptJquery.ajax({
//       url : en4.core.baseUrl + 'core/comment/delete',
//       method:'post',
//       dataType:'json',
//       data : {
//         format : 'json',
//         type : type,
//         id : id,
//         comment_id : comment_id
//       },
//       complete: function() {
//         if(scriptJquery('#comment-' + comment_id).length) {
//           scriptJquery('#comment-' + comment_id).remove();
//         }
//         try {
//           var commentCount = scriptJquery('.comments_options span');
//           var m = commentCount.html().match(/\d+/);
//           var newCount = ( parseInt(m[0]) != 'NaN' && parseInt(m[0]) > 1 ? parseInt(m[0]) - 1 : 0 );
//           commentCount.html(commentCount.html().replace(m[0], newCount));
//         } catch( e ) {}
//       }
//     }));
//   }
// };


en4.core.layout = {
  setLeftPannelMenu: function (type) {
      var pannelElement = scriptJquery(document).find('body')
      var navigationElement = pannelElement.find('.layout_core_menu_main .main_menu_navigation');
			var navMain = pannelElement.find('.navbar');
      var setContent = function () {
        var windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        if (type == 'horizontal' && windowWidth >= 1025) {
          pannelElement.removeClass('global_left_panel');
          navigationElement.addClass('horizontal_core_main_menu');
          return;
        }
				navMain.removeClass('navbar-expand-lg')
        pannelElement.addClass('global_left_panel panel-collapsed');
        navigationElement.removeClass('horizontal_core_main_menu');
      };
      window.addEventListener('resize', setContent);
      setContent();
      // scrollBar.element.find('.scrollbar-content').on('scroll', function () {
      //   hideMenuTip();
      // });
    }
};
en4.core.languageAbstract = function(){
  var name = 'language';
  this.options = {
    locale : 'en',
    defaultLocale : 'en'
  }
  var data = {

  }

  this.initialize = function(options, data) {
    // b/c
    if(typeof options == 'object' ) {
      if(typeof options.lang !== "undefined") {
        this.addData(options.lang);
        delete options.lang;
      }
      if(typeof options.data !== "undefined") {
        this.addData(options.data);
        delete options.data;
      }
      this.setOptions(options);
    }
    if(typeof data == 'object' ) {
      this.setData(data);
    }
  }

  this.getName = function() {
    return this.name;
  }

  this.setLocale = function(locale) {
    this.options.locale = locale;
    return this;
  }

  this.getLocale = function() {
    return this.options.locale;
  }

  this.translate = function() {
    //try {
      if( arguments.length < 1 ) {
        return '';
      }

      // Process arguments
      var locale = this.options.locale;
      var messageId = arguments[0];
      var options = new Array();
      if( arguments.length > 1 ) {
        for( var i = 1, l = arguments.length; i < l; i++ ) {
          options.push(arguments[i]);
        }
      }

      // Check plural
      var plural = false;
      var number = 1;
      if(typeof messageId == 'object' ) {
        if( messageId.length > 2 ) {
          number = messageId.pop();
          plural = messageId;
        }
        messageId = messageId[0];
      }

      // Get message
      var message;
      if(typeof (this.data) !== "undefined" && typeof (this.data[messageId]) !== "undefined") {
        message = this.data[messageId];
      } else if( plural ) {
        message = plural;
        locale = this.options.defaultLocale;
      } else {
        message = messageId;
      }

      // Get correct message from plural
      if(typeof message == 'object') {
        var rule = this.getPlural(locale, number);
        if(typeof message[rule] !== "undefined") {
          message = message[rule];
        } else {
          message = message[0];
        }
      }

      if( options.length <= 0 ) {
        return message;
      }
      return message.vsprintf(options);
    // } catch( e ) {
    //   alert(e);
    // }
  }
  function setData(data) {
    if(typeof data != 'object' && typeof data != 'hash' ) {
      return this;
    }
    this.data = data;
    return this;
  }

  this.addData = function(data) {
    if(typeof data != 'object' && typeof data != 'hash' ) {
      return this;
    }
    this.data = scriptJquery.extend(this.data, data);
    return this;
  }

  this.getData = function(data) {
    return this.data;
  }


  this.getPlural = function(locale, number) {

    if(typeof locale != 'string' ) {
      return 0;
    }

    if( locale == "pt_BR" ) {
      locale = "xbr";
    }

    if( locale.length > 3 ) {
      locale = locale.substring(0, locale.indexOf('_'));
    }

    switch( locale ) {
      case 'bo': case 'dz': case 'id': case 'ja': case 'jv': case 'ka':
      case 'km': case 'kn': case 'ko': case 'ms': case 'th': case 'tr':
      case 'vi':
        return 0;
        break;

      case 'af': case 'az': case 'bn': case 'bg': case 'ca': case 'da':
      case 'de': case 'el': case 'en': case 'eo': case 'es': case 'et':
      case 'eu': case 'fa': case 'fi': case 'fo': case 'fur': case 'fy':
      case 'gl': case 'gu': case 'ha': case 'he': case 'hu': case 'is':
      case 'it': case 'ku': case 'lb': case 'ml': case 'mn': case 'mr':
      case 'nah': case 'nb': case 'ne': case 'nl': case 'nn': case 'no':
      case 'om': case 'or': case 'pa': case 'pap': case 'ps': case 'pt':
      case 'so': case 'sq': case 'sv': case 'sw': case 'ta': case 'te':
      case 'tk': case 'ur': case 'zh': case 'zu':
        return (number == 1) ? 0 : 1;
        break;

      case 'am': case 'bh': case 'fil': case 'fr': case 'gun': case 'hi':
      case 'ln': case 'mg': case 'nso': case 'xbr': case 'ti': case 'wa':
        return ((number == 0) || (number == 1)) ? 0 : 1;
        break;

      case 'be': case 'bs': case 'hr': case 'ru': case 'sr': case 'uk':
        return ((number % 10 == 1) && (number % 100 != 11)) ? 0 :
          (((number % 10 >= 2) && (number % 10 <= 4) && ((number % 100 < 10)
          || (number % 100 >= 20))) ? 1 : 2);

      case 'cs': case 'sk':
        return (number == 1) ? 0 : (((number >= 2) && (number <= 4)) ? 1 : 2);

      case 'ga':
        return (number == 1) ? 0 : ((number == 2) ? 1 : 2);

      case 'lt':
        return ((number % 10 == 1) && (number % 100 != 11)) ? 0 :
          (((number % 10 >= 2) && ((number % 100 < 10) ||
          (number % 100 >= 20))) ? 1 : 2);

      case 'sl':
        return (number % 100 == 1) ? 0 : ((number % 100 == 2) ? 1 :
          (((number % 100 == 3) || (number % 100 == 4)) ? 2 : 3));

      case 'mk':
        return (number % 10 == 1) ? 0 : 1;

      case 'mt':
        return (number == 1) ? 0 :
          (((number == 0) || ((number % 100 > 1) && (number % 100 < 11))) ? 1 :
          (((number % 100 > 10) && (number % 100 < 20)) ? 2 : 3));

      case 'lv':
        return (number == 0) ? 0 : (((number % 10 == 1) &&
          (number % 100 != 11)) ? 1 : 2);

      case 'pl':
        return (number == 1) ? 0 : (((number % 10 >= 2) && (number % 10 <= 4) &&
          ((number % 100 < 10) || (number % 100 > 29))) ? 1 : 2);

      case 'cy':
        return (number == 1) ? 0 : ((number == 2) ? 1 : (((number == 8) ||
          (number == 11)) ? 2 : 3));

      case 'ro':
        return (number == 1) ? 0 : (((number == 0) || ((number % 100 > 0) &&
          (number % 100 < 20))) ? 1 : 2);

      case 'ar':
        return (number == 0) ? 0 : ((number == 1) ? 1 : ((number == 2) ? 2 :
          (((number >= 3) && (number <= 10)) ? 3 : (((number >= 11) &&
          (number <= 99)) ? 4 : 5))));

      default:
        return 0;
    }
  }
};


en4.core.language = new en4.core.languageAbstract();

/**
 * ReCaptcha scripts
 */
en4.core.reCaptcha = {
  lodedJs: [],
  render: function () {
    scriptJquery('.g-recaptcha').each(function (e) {
      let $el = scriptJquery(this);
      if ($el.data('recaptcha-loaded')) {
        return;
      }
      $el.empty();
      grecaptcha.render($el[0], {
        sitekey: $el.attr('data-sitekey'),
        theme: $el.attr('data-theme'),
        type: $el.attr('data-type'),
        tabindex: $el.attr('data-tabindex'),
        size: $el.attr('data-size'),
      });
      $el.data('recaptcha-loaded', true);
    });
  },
  loadJs: function(js) {
    if (this.lodedJs.indexOf(js) != -1) {
      return;
    }
    this.lodedJs.push(js);
    scriptJquery.getScript(js);
  }
};

window.en4CoreReCaptcha = function () {
  en4.core.reCaptcha.render();
};

// })(); // END NAMESPACE


//Check upload file size.
AttachEventListerSE('change',"input[type='file']",function() {
  if(this.files.length > 0) {
    var FileSize = this.files[0].size; // in byte
    if(FileSize > post_max_size) {
      alert("The size of the file exceeds the limits set on the server.");
      scriptJquery(this).val('');
    } else {
      if(scriptJquery(this).data('function')){
        eval(scriptJquery(this).data('function')+"()");
      }
    }
  }
});

// Tooltip
function seTootip(){
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    if(scriptJquery(tooltipTriggerEl).hasClass("executed")){
      return null;
    }
    scriptJquery(tooltipTriggerEl).addClass("executed");
    //scriptJquery(tooltipTriggerEl).tooltip('hide')
    return new bootstrap.Tooltip(tooltipTriggerEl,{ trigger: "hover" })
  });
  scriptJquery('[data-bs-toggle="tooltip"]').on('click', function () {
    scriptJquery(this).tooltip('hide')
  })
}
en4.core.runonce.add(function() {
  seTootip();
});
scriptJquery(document).ajaxComplete(function() {
  seTootip();
});
setTimeout(() => {seTootip();}, 2000);

//Cookie get and set function
function setCoreCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+d.toGMTString();
  document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/";
}

function getCoreCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1);
      if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
  }
  return "";
}

AttachEventListerSE('click','.openSmoothbox',function(e){
  var url = scriptJquery(this).attr('href');
  openSmoothBoxInUrl(url);
  return false;
});

function openSmoothBoxInUrl(url){
  Smoothbox.open(url);
  parent.Smoothbox.close;
  return false;
}

en4.core.runonce.add(function() {

  if(scriptJquery('#togglePassword'))
    scriptJquery('#togglePassword').hide();
  if(scriptJquery('#confirmtogglePassword'))
    scriptJquery('#confirmtogglePassword').hide();
  if(scriptJquery('#newtogglePassword'))
    scriptJquery('#newtogglePassword').hide();
  if(scriptJquery('#oldtogglePassword'))
    scriptJquery('#oldtogglePassword').hide();

  AttachEventListerSE('keyup', '#signup_password, #password', function(e) {
    var password = scriptJquery(this).val();

    if(password && scriptJquery('#togglePassword'))
      scriptJquery('#togglePassword').show();

    var strength = 0;

    // Length check
    if (password.length >= 6) strength += 1;

    // Lowercase and uppercase check
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;

    // Number check
    if (password.match(/[0-9]/)) strength += 1;

    // Special character check
    if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/)) strength += 1;

    // Displaying output
    if (strength < 3) {
      scriptJquery('#passwordroutine_length').removeClass().addClass('weak');
      scriptJquery('#passwordroutine_text').html(en4.core.language.translate('Weak'));
    } else if (strength == 3) {
      scriptJquery('#passwordroutine_length').removeClass().addClass('weak');
      scriptJquery('#passwordroutine_text').html(en4.core.language.translate('Weak'));
    } else {
      scriptJquery('#passwordroutine_length').removeClass().addClass('strong');
      scriptJquery('#passwordroutine_text').html(en4.core.language.translate('Strong'));
    }

    if(password == '') {
      scriptJquery('#passwordroutine_length').removeClass();
      scriptJquery('#passwordroutine_text').html(en4.core.language.translate('Enter your password.'));
      scriptJquery('#togglePassword').hide();
    }
  });

  AttachEventListerSE('keyup', '#passconf', function(e) {
    var passwordconf = scriptJquery(this).val();

    if(passwordconf && scriptJquery('#confirmtogglePassword'))
      scriptJquery('#confirmtogglePassword').show();
    if(passwordconf == '')
      scriptJquery('#confirmtogglePassword').hide();
  });

  AttachEventListerSE('keyup', '#oldPassword', function(e) {
    var passwordconf = scriptJquery(this).val();
    if(passwordconf && scriptJquery('#oldtogglePassword'))
      scriptJquery('#oldtogglePassword').show();
    if(passwordconf == '')
      scriptJquery('#oldtogglePassword').hide();
  });

  AttachEventListerSE('keyup', '#passwordConfirm', function(e) {
    var passwordconf = scriptJquery(this).val();
    if(passwordconf && scriptJquery('#confirmtogglePassword'))
      scriptJquery('#confirmtogglePassword').show();
    if(passwordconf == '')
      scriptJquery('#confirmtogglePassword').hide();
  });
});
function showSuccessTooltip(contents, className) {

  if(typeof className == 'undefined') {
    className = 'core_success_notification';
  }

  if(scriptJquery('.core_notification').length > 0)
    scriptJquery('.core_notification').hide();
    scriptJquery('<div class="'+className+'">' + contents + '</div>').css( {
    display: 'block',
  }).appendTo("body").fadeOut(5000,'',function(){
    scriptJquery(this).remove();
  });
}
// search form submit
AttachEventListerSE('submit', '.core_search_form form', function(e) {
  e.preventDefault();
  let baseUrl = scriptJquery(this).attr("action")
  if(!baseUrl) {
    baseUrl = window.location.href.split('?')[0];
  }
  const formData = new FormData(e.target);
  const params = new URLSearchParams(formData);
  let url = baseUrl+"?"+params;
  window.history.pushState({state:'new'},'', url);
  loadAjaxContentApp(url);
});

var submitAjaxRequestSend;

//submit form via ajax
AttachEventListerSE('submit', '.form_submit_ajax', function(e) {

  var formObj = scriptJquery(this);
  var formURL  = formObj.attr('action') ? formObj.attr('action') : window.location.href;

  if(submitAjaxRequestSend) return;
  if(scriptJquery('body').hasClass('admin'))
    return true;
  if(scriptJquery('html').attr('id') == 'smoothbox_window') return;
  if(formURL.indexOf("add-location") == -1 && formURL.indexOf("edit-location") == -1 && formURL.indexOf("delete-location") == -1) {
    if(scriptJquery(this).closest('#ajaxsmoothbox_main').length > 0 && !scriptJquery(this).hasClass('allow_submit_ajax')) return;
  }

  if(formURL.indexOf("?") == -1){
    formURL = formURL+"?getContentOnly=1"
  }else{
    formURL = formURL+"&getContentOnly=1"
  }

  if(scriptJquery(this).parent().hasClass("core_search_form")) return;
  if(scriptJquery(this).hasClass("ignore_ajax_form")) return;
  if(scriptJquery(this).closest('.core_search_form_ignore').length > 0) return;
  if(scriptJquery('body').find('#compose-music-form').length > 0 ) return;

  if(formObj.attr('id') == 'signup_account_form') return;
  if(formObj.attr('id') == 'user_form_settings_delete') return;
  if(formObj.attr('id') == 'user_form_auth_forgot') return;
  //if(formObj.attr('id') == 'user_form_login') return;
  if(formObj.attr('id') == 'ignore_ajax_form') return;


  e.preventDefault();

  // Check if all required fields are filled out
  var formData = new FormData(this);
  formData.append('isFormAjaxPost', true);
  var submitButtonLabel = formObj.find('button[type=submit]').html();
  formObj.find('button[type=submit]').html('<i class="fas fa-spinner fa-spin"></i>');
  formObj.find('button[type=submit]').attr("disabled",true);
  submitAjaxRequestSend = (scriptJquery.ajax({
    url : formURL,
    type: "POST",
    dataType: 'json',
    contentType:false,
    processData: false,
    cache: false,
    data: formData,
    error : function(response) {
      submitAjaxRequestSend = null;
      // if(scriptJquery(response.responseText).find('#global_content').length > 0) {
        var searchGlobalContent = scriptJquery(response.responseText);
        if(searchGlobalContent)
          scriptJquery('#global_content').html(searchGlobalContent);
      // }
      if(scriptJquery('#ajaxsmoothbox_main').length > 0){
        ajaxsmoothboxclose();
      }
      let data = scriptJquery("#script-default-data");
      let url = data.find("#script-page-url").html()
      window.history.pushState({state:'new', url: url.replace('?getContentOnly=1', '')},'', url.replace('?getContentOnly=1', ''));
      updateMetaTags();
      en4.core.runonce.trigger();
      formObj.find('button[type=submit]').removeAttr("disabled");
      formObj.find('button[type=submit]').html(submitButtonLabel);
    },
    success : function(response) {
      submitAjaxRequestSend = null;
      if(response.status) {
        if(formObj.find('#form_errors').length)
          formObj.find('#form_errors').remove();
        if(scriptJquery('#ajaxsmoothbox_main').length > 0){
          ajaxsmoothboxclose();
        }
        if(response.redirectFullURL) {
          if(typeof isAdminUrl != 'undefined') {
            window.location.href = response.redirectFullURL;
          } else {
            loadAjaxContentApp(response.redirectFullURL,false,"full");
          }
        } else if(response.redirectURL) {
          if(typeof isAdminUrl != 'undefined') {
            window.location.href = response.redirectURL;
          } else {
            loadAjaxContentApp(response.redirectURL);
          }
        } else {
          formObj.find('button[type=submit]').removeAttr("disabled");
          formObj.find('button[type=submit]').html(submitButtonLabel);
          if(formObj.find('#form_errors').length)
            formObj.find('#form_errors').remove();
          if(formObj.find('.form-notices').length)
            formObj.find('.form-notices').remove();
          formObj.find('.form-elements').prepend('<ul class="form-notices"><li>'+response.success_message+'</li></ul>');
          scriptJquery('html, body').animate({
            scrollTop: formObj.find('.form-notices').offset().top
          }, 2000);
        }
      } else {
        formObj.find('button[type=submit]').removeAttr("disabled");
        formObj.find('button[type=submit]').html(submitButtonLabel);
        if(formObj.find('.form-notices').length)
          formObj.find('.form-notices').remove();
        if(formObj.find('#form_errors').length)
          formObj.find('#form_errors').remove();
        var errors = '<ul class="form-errors" id="form_errors">';
        for (var i = 0; i < response.error_message.length; i++) {
          var error_message = response.error_message[i];
          if(error_message.isRequired) {
            errors += '<li>'+error_message.label+'<ul class="errors"><li>'+error_message.errorMessage+'</li></ul></li>';
          } else if(error_message.errorMessage) {
            errors += '<li><ul class="errors"><li>'+error_message.errorMessage+'</li></ul></li>';
          }
        }
        errors += '</ul>';
        formObj.find('.form-elements').prepend(errors);
        scriptJquery('html, body').animate({
          scrollTop: formObj.find('#form_errors').offset().top
        }, 2000);
      }
    }
  }));
  return false;
});


let corecache = new Map();
//tooltip code
var coretooltipOrigin;
AttachEventListerSE('mouseover mouseout', '.core_tooltip', function(event) {
  if(!isEnableTooltip) return;
  if(scriptJquery(this).parent().hasClass('notification_item_title') == true) return;
	scriptJquery(this).tooltipster({
    interactive: true,
    content: '<div class="corebasic_tooltip_loading">Loading...</div>',
    contentCloning: false,
    contentAsHTML: true,
    animation: 'fade',
    updateAnimation:false,
    functionBefore: function(origin, continueTooltip) {
      //get attr
      if(typeof scriptJquery(origin).attr('data-rel') == 'undefined')
        var guid = scriptJquery(origin).attr('data-src');
      else
        var guid = scriptJquery(origin).attr('data-rel');
        // we'll make this function asynchronous and allow the tooltip to go ahead and show the loading notification while fetching our data.
        continueTooltip();
        coretooltipOrigin = scriptJquery(this);
        if (origin.data('ajax') !== 'cached') {
          // if (!corecache.has(guid)) {
            scriptJquery.ajax({
              type: 'POST',
              url: en4.core.baseUrl+'core/tooltip/index/guid/'+guid,
              success: function(data) {
                corecache.set(guid, data);
                origin.tooltipster('content', corecache.get(guid)).data('ajax', 'cached');
              }
            });
          // } else {
          //   origin.tooltipster('content', corecache.get(guid)).data('ajax', 'cached');
          // }
        }
    }
	});
	scriptJquery(this).tooltipster('show');
});


/**
 * Create and edit category
*/
function showSubCategory(category_id,selectedId) {
  var selected;
  if(selectedId != '')
    selected = selectedId;

  if(typeof type != 'undefined')
    type = type;
  else
    type = modulename;

  if(modulename == 'music') {
    var URL = en4.core.baseUrl + modulename + '/subcategory/category_id/' + category_id;
  } else {
    var URL = en4.core.baseUrl + modulename + '/index/subcategory/category_id/' + category_id;
  }
  scriptJquery.ajax({
    url: URL,
    dataType: 'html',
    data: {
      'selected' : selected,
      'category_id': category_id,
      'type': type,
    },
    success: function(responseHTML) {
      if (document.getElementById('subcat_id') && responseHTML) {
        if (document.getElementById('subcat_id-wrapper')) {
          document.getElementById('subcat_id-wrapper').style.display = "block";
        }
        document.getElementById('subcat_id').innerHTML = responseHTML;
      } else {
        if (document.getElementById('subcat_id-wrapper')) {
          document.getElementById('subcat_id-wrapper').style.display = "none";
          document.getElementById('subcat_id').innerHTML = '<option value="0"></option>';
        }
      }
      if (document.getElementById('subsubcat_id-wrapper')) {
        document.getElementById('subsubcat_id-wrapper').style.display = "none";
        document.getElementById('subsubcat_id').innerHTML = '<option value="0"></option>';
      }
    }
  });
}

function showSubSubCategory(category_id, selectedId) {
  if(category_id == 0) {
    if (document.getElementById('subsubcat_id-wrapper')) {
      document.getElementById('subsubcat_id-wrapper').style.display = "none";
      document.getElementById('subsubcat_id').innerHTML = '';
    }
    return false;
  }

  if(typeof type != 'undefined')
    type = type;
  else
    type = modulename;

  var selected;
  if(selectedId != '')
    selected = selectedId;
  if(modulename == 'music') {
    var URL = en4.core.baseUrl + modulename + '/subsubcategory/subcategory_id/' + category_id;
  } else {
    var URL = en4.core.baseUrl + modulename + '/index/subsubcategory/subcategory_id/' + category_id;
  }
  scriptJquery.ajax({
    url: URL,
    dataType: 'html',
    data: {
      'selected': selected,
      'subcategory_id':category_id,
      'type': type,
    },
    success: function(responseHTML) {
      if (document.getElementById('subsubcat_id') && responseHTML) {
        if (document.getElementById('subsubcat_id-wrapper')) {
          document.getElementById('subsubcat_id-wrapper').style.display = "block";
        }
        document.getElementById('subsubcat_id').innerHTML = responseHTML;
      } else {
        if (document.getElementById('subsubcat_id-wrapper')) {
          document.getElementById('subsubcat_id-wrapper').style.display = "none";
          document.getElementById('subsubcat_id').innerHTML = '<option value="0"></option>';
        }
      }
    }
  });
}

/**
 * Rating
*/
function rating_over(rating) {
  if(rated == 1 ) {
    scriptJquery('#rating_text').html(en4.core.language.translate('you already rated'));
    //set_rating();
  } else if( viewer == 0 ) {
    scriptJquery('#rating_text').html(en4.core.language.translate('please login to rate'));
  } else {
    scriptJquery('#rating_text').html(en4.core.language.translate('click to rate'));
    for(var x=1; x<=5; x++) {
      if(x <= rating) {
        scriptJquery('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big ' + ratingIcon);
      } else {
        scriptJquery('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled ' + ratingIcon);
      }
    }
  }
}

function rating_out() {
  if (new_text != ''){
    scriptJquery('#rating_text').html(new_text);
  }
  else{
    scriptJquery('#rating_text').html(rating_text);
  }
  if (pre_rate != 0){
    set_rating();
  }
  else {
    for(var x=1; x<=5; x++) {
      scriptJquery('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled ' + ratingIcon);
    }
  }
}

function set_rating() {
  var rating = pre_rate;
  if (new_text != ''){
    scriptJquery('#rating_text').html(new_text);
  }
  else{
    scriptJquery('#rating_text').html(rating_text);
  }
  for(var x=1; x<=parseInt(rating); x++) {
    scriptJquery('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big ' + ratingIcon);
  }

  for(var x=parseInt(rating)+1; x<=5; x++) {
    scriptJquery('#rate_'+x).attr('class', 'rating_star_big_generic rating_star_big_disabled ' + ratingIcon);
  }

  var remainder = Math.round(rating)-rating;
  if (remainder <= 0.5 && remainder !=0){
    var last = parseInt(rating)+1;
    scriptJquery('#rate_'+last).attr('class', 'rating_star_big_generic rating_star_big_half ' + ratingIcon);
  }
}

function rate(rating) {
  scriptJquery('#rating_text').html(en4.core.language.translate('Thanks for rating!'));
  for(var x=1; x<=5; x++) {
    scriptJquery('#rate_'+x).attr('onclick', '');
  }
  rated = 1;
  total_votes = total_votes+1;
  pre_rate = (pre_rate+rating)/total_votes;
  set_rating();

  (scriptJquery.ajax({
    format: 'json',
    url : en4.core.baseUrl + 'core/rating/rate',
    data : {
      format : 'json',
      rating : rating,
      resource_id: resource_id,
      resource_type: resource_type,
      modulename: modulename,
      notificationType: notificationType,
    },
    success : function(responseJSON) {
      scriptJquery('#rating_text').html(responseJSON[0].total+" ratings");
      new_text = responseJSON[0].total+" ratings";
    }
  }));
}


function mapApiLoaded() {
  if(typeof initGoogleMap == 'function') {
    initGoogleMap();
  }
}
// override window location for ajax redirect
// Define a list of allowed domains
var allowedDomains = [];
// Helper function to check if the URL is allowed
function isAllowed(url) {
    if(url.indexOf('http://') == -1 && url.indexOf('https://') == -1) {
        return true;
    } else {
        return false;
    }

}

function setProxyLocation() {
  // Create a proxy for window.location
  var locationProxy = new Proxy({
      ...window.location,
      reload: function(type){
        if(scriptJquery("body").hasClass("admin")){
          window.location.reload();
        }else
          loadAjaxContentApp(window.location.href,false,type);
      },
      toString:function(){
          return window.location.toString();
      },
      replace:function(){
          return window.location.replace()
      }
  }, {
      set: function(target, property, value) {
          if (property === 'href') {
              if (isAllowed(value)) {
                if(!scriptJquery("body").hasClass("admin")){
                  loadAjaxContentApp(value);
                  return false;
                }
              } else {
                  target[property] = value;
              }
              return true;
          }
          target[property] = value;
          return true;
      }
  });
  // Overwrite the global window.location with the proxy
  window.proxyLocation = locationProxy;
}
setProxyLocation();



//Content Favourite
AttachEventListerSE('click', '.content_favourite', function () {

  var element = scriptJquery(this);
  if (!scriptJquery (element).attr('data-id'))
    return;

  if (!scriptJquery (element).attr('data-type'))
    return;

  if(scriptJquery(element).hasClass('button_active')) {
      scriptJquery(element).removeClass('button_active');
  } else
      scriptJquery(element).addClass('button_active');

  var id = scriptJquery(element).attr('data-id');
  var type = scriptJquery(element).attr('data-type');

  (scriptJquery.ajax({
    method: 'post',
    'url':  en4.core.baseUrl + 'core/favourite/index',
    'data': {
      format: 'json',
      id: id,
      type: type,
    },
    success: function(response) {
      var response = jQuery.parseJSON(response);
      var favouriteElement = '.favourite_'+type+'_'+id;
      if(response.error) {
        alert(en4.core.language.translate('Something went wrong,please try again later'));
      } else {
        scriptJquery(favouriteElement).find('span').html(response.count);
        scriptJquery(element).find('span').html(response.count);

        if(response.condition == 'reduced') {
          scriptJquery(element).removeClass('button_active');
          showSuccessTooltip('<i class="fa fa-heart"></i><span>'+(response.message)+'</span>','core_reject_notification');
        } else {
          scriptJquery(element).addClass('button_active');
          showSuccessTooltip('<i class="fa fa-heart"></i><span>'+(response.message)+'</span>');
        }
      }
    }
  }));
});

//User Recent search
en4.core.runonce.add(function() {
  scriptJquery("body").on('click',function(event) {
    if(document.getElementById("global_search_field") && !document.getElementById("global_search_field").contains(event.target) && document.getElementById("recent_search_data") && !document.getElementById("recent_search_data").contains(event.target) && event.target.getAttribute('data-class') != 'notifications_donotclose') {
      if(scriptJquery('.recent_search_data'))
        scriptJquery('.recent_search_data').hide();
    }
  });
});

AttachEventListerSE('keyup', '.global_search_field', function(event) {
  var item = scriptJquery(this);
  var global_search_field = item.val();
  // Check if the key pressed is any key (you can specify a specific key by event.which)
  if (event.which) {
    if(item.parent().parent().find('.recent_search_data')) {
      if(global_search_field) {
        item.parent().parent().find('.recent_search_data').hide();
      } else {
        item.parent().parent().find('.recent_search_data').show();
      }
    }
    if(event.which == 13 && global_search_field != '') {
      var randomId = Math.floor(Math.random() * 1000000000);
      var searchData = '<li class="search_query_'+randomId+'" id="search_query_'+randomId+'"><a href="search/index/query/'+global_search_field+'/type/" class="header_search_recent_list_item"><div class="_thumb"><i class="fa-regular fa-clock"></i></div><div class="_info"><p class="m-0 _title">'+global_search_field+'</p></div></a><a href="javascript:void(0);" class="user_recent_search_remove _clear link_inherit center_item rounded-circle" data-id="'+randomId+'" data-query="'+global_search_field+'"><i class="icon_cross"></i></a></li>';
      scriptJquery('body').find('.header_search_recent_list_recent').prepend(searchData);
    }
  }
});

AttachEventListerSE('click', '.global_search_field', function () {
  var item = scriptJquery(this).parent().parent();
  var global_search_field = scriptJquery(this).val();
  if(item.find('.header_search_recent_list_recent').find('li').length > 0)
    item.find('.header_search_recent_head_recent').show();
  if(item.find('.header_search_recent_list_trending').find('li').length > 0)
    item.find('.header_search_recent_head_trending').show();

  if(item.find('.header_search_recent_list_recent').find('li').length == 0 && item.find('.header_search_recent_list_trending').find('li').length == 0) {
    item.find('.recent_search_data').hide();
  } else if(!global_search_field) {
    item.find('.recent_search_data').show();
  }
});

AttachEventListerSE('click', '.header_search_recent_list_item', function(){
  if(scriptJquery('#recent_search_data')) {
    scriptJquery('#recent_search_data').hide();
  }
});

AttachEventListerSE('click', '.user_recent_search_remove', function () {

  var element = scriptJquery(this);
  var query = scriptJquery(element).attr('data-query');
  var dataId = scriptJquery(element).attr('data-id');
  var data = element.parent().parent();
  if(query) {
    scriptJquery('.search_query_'+dataId).remove();
    if(data.find('li').length == 0) {
      data.parent().hide();
    }
  } else {
    data.find('.header_search_recent_head_recent').hide();
    data.find('.header_search_recent_list_recent').find('li').remove();
  }
  if(data.find('.user_search_query_all').find('div').length == 0) {
    data.find('.header_search_recent_head_recent').hide();
  }

  (scriptJquery.ajax({
    method: 'post',
    'url':  en4.core.baseUrl + 'core/search/remove',
    'data': {
      format: 'json',
      query: query,
    },
    success: function(response) {
      var response = jQuery.parseJSON(response);
      if(response.error) {
        alert(en4.core.language.translate('Something went wrong,please try again later'));
      } else {
        showSuccessTooltip('<i class="fa fa-heart"></i><span>'+(response.message)+'</span>');
      }
    }
  }));
});


function refreshCaptcha(obj) {
  scriptJquery.ajax({
    url: en4.core.baseUrl + 'core/index/refresh-captcha',
    dataType:'json',
    success: function(data) {
        scriptJquery(obj).parent().find('img').attr('src', data.src);
        scriptJquery(obj).parent().parent().find('#captcha-id').attr('value', data.id);
    }
  });
}
