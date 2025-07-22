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
(function(){
  var Hash = function(object = {}){
  	var proto = {
  		has: Object.prototype.hasOwnProperty,
  		getClean: function(){
  			var clean = {};
  			for (var key in this){
  				if (this.hasOwnProperty(key)) clean[key] = this[key];
  			}
  			return clean;
  		},
  		extend: function(properties){
  			Object.entries((properties || {})).forEach(([key,value])=>{
  				this.set(this, key, value);
  			});
  			return this;
  		},
  		getLength: function(){
  			var length = 0;
  			for (var key in this){
  				if (this.hasOwnProperty(key)) length++;
  			}
  			return length;
  		},
  		erase: function(key){
  			if (this.hasOwnProperty(key)) delete this[key];
  			return this;
  		},
  		get: function(key){
  			return (this.hasOwnProperty(key)) ? this[key] : null;
  		},
  		set: function(key, value){
  			if (!this[key] || this.hasOwnProperty(key)) this[key] = value;
  			return this;
  		},
  		empty: function(){
  			Object.entries(this).forEach(function([key,value]){
  				delete this[key];
  			}, this);
  			return this;
  		},
  	};
  	for (var key in proto) Hash.prototype[key] = proto[key];
    for (var key in object) this[key] = object[key];
    return this;
  }
  this.Hash = Hash;
})();
(function(){
  var serverOffset = 0;
  Date.setServerOffset = function(ts){
    var server = new Date(ts);
    var client = new Date();
    serverOffset = server - client;
  };

  Date.getServerOffset = function() {
    return serverOffset;
  };
})();
$type = function(object){
  var type = typeof (object);
  return (object == null || type == 'null' || type == 'undefined') ? false : type;
}

$time = function() {
  return (new Date()).getTime();
}
String.prototype.vsprintf = function(args) {
  str = this;
  // Check for no params
  if( !args || !args.length )
  {
    return str;
  }
  // Replace params
  var out = '';
  var m;
  var masterIndex = 0;
  var currentIndex;
  var arg;
  var instr;
  var meth;
  var sign;
  while( str.length > 0 )
  {
    // Check for no more expressions
    if( !str.match(/[%]/) )
    {
      out += str;
      break;
    }
    // Remove any preceeding non-expressions
    m = str.match(/^([^%]+?)([%].+)?$/)
    if( m )
    {
      out += m[1];
      str = typeof(m[2]) ? m[2] : '';
      if( str == '' )
      {
        break;
      }
    }
    // Check for escaped %
    if( str.substring(0, 2) == '%%' )
    {
      str = str.substring(2);
      out += '%';
      continue;
    }
    // Proc next params
    m = str.match(/^[%](?:([0-9]+)\x24)?(\x2B)?(\x30|\x27[^$])?(\x2D)?([0-9]+)?(?:\x2E([0-9]+))?([bcdeEfosuxX])/)
    if( m )
    {
      instr = m[7];
      meth = m[6] || false;
      sign = m[2] || false;
      currentIndex = ( m[1] ? m[1] - 1 : masterIndex++ );
      if($type(args[currentIndex]) )
      {
        arg = args[currentIndex];
      }
      else
      {
        throw('Undefined argument for index ' + currentIndex);
      }
      // Make sure passed sane argument type
      switch( typeof(arg) )
      {
        case 'number':
        case 'string':
        case 'boolean':
          // Okay
          break;
        case 'undefined':
          if( arg == null )
          {
            arg = '';
            break;
          }
        default:
          throw('Unknown argument type: ' + typeof(arg));
          break;
      }
      // Now proc instr
      switch( instr )
      {
        // Binary
        case 'b':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(2);
          break;
        // Char
        case 'c':
          arg = String.fromCharCode(arg);
          break;
        // Integer
        case 'd':
          arg = parseInt(arg);
          break;
        // Scientific notation
        case 'E':
        case 'e':
          if( typeof(arg) != 'number' ) arg = parseFloat(arg);
          if( meth )
          {
            arg = arg.toExponential(meth);
          }
          else
          {
            arg = arg.toExponential();
          }
          if( instr == 'E' ) arg = arg.toUpperCase();
          break;

        // Unsigned integer
        case 'u':
          arg = Math.abs(parseInt(arg));
          break;

        // Float
        case 'f':
          if( meth )
          {
            arg = parseFloat(arg).toFixed(meth)
          }
          else
          {
            arg = parseFloat(arg);
          }
          break;
        // Octal
        case 'o':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(8);
          break;

        // String
        case 's':
          if( typeof(arg) != 'string' ) arg = String(arg);
          if( meth )
          {
            arg = arg.substring(0, meth);
          }
          break;

        // Hex
        case 'x':
        case 'X':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(8);
          if( instr == 'X' ) arg = arg.toUpperCase();
          break;
      }

      // Add a sign if requested
      if( (instr == 'd' || instr == 'e' || instr == 'f') && sign && arg > 0 )
      {
        arg = '+' + arg;
      }
      // Do repeating if necessary
      var repeatChar, repeatCount;
      if( m[3] )
      {
        repeatChar = m[3];
      }
      else
      {
        repeatChar = ' ';
      }
      if( m[5] )
      {
        repeatCount = m[5];
      }
      else
      {
        repeatCount = 0;
      }
      repeatCount -= arg.length;

      // Do the repeating
      if( repeatCount > 0 )
      {
        var paddedness = function(str, count)
        {
          var ret = '';
          while( count > 0 )
          {
            ret += str;
            count--;
          }
          return ret;
        }(repeatChar, repeatCount);

        if( m[4] )
        {
          out += arg + paddedness;
        }
        else
        {
          out += paddedness + arg;
        }
      }
      // Just add the string
      else
      {
        out += arg;
      }
      // Remove from str
      str = str.substring(m[0].length);
    }
    else
    {
      throw('Malformed expression in string: ' + str);
    }
  }
  return out;
}

var Cookie = function(key, options){
  defaultOptions = {
    path: '/',
    domain: false,
    duration: false,
    secure: false,
    document: document,
    encode: true
  }
  this.write = function(value){
    if (this.options.encode) value = encodeURIComponent(value);
    if (this.options.domain) value += '; domain=' + this.options.domain;
    if (this.options.path) value += '; path=' + this.options.path;
    if (this.options.duration){
      var date = new Date();
      date.setTime(date.getTime() + this.options.duration * 24 * 60 * 60 * 1000);
      value += '; expires=' + date.toGMTString();
    }
    if (this.options.secure) value += '; secure';
    this.options.document.cookie = this.key + '=' + value;
    return this;
  }

  this.read = function(){
    var value = this.options.document.cookie.match('(?:^|;)\\s*' + this.key.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)');
    return (value) ? decodeURIComponent(value[1]) : null;
  }

  this.dispose = function(){
    new Cookie(this.key, scriptJquery.extend(true,{}, this.options, {duration: -1})).write('');
    return this;
  }
  this.key = key;
  this.options = scriptJquery.extend(true,{},defaultOptions,options);
};

Cookie.write = function(key, value, options){
  return new Cookie(key, options).write(value);
};

Cookie.read = function(key){
  return new Cookie(key).read();
};

Cookie.dispose = function(key, options){
  return new Cookie(key, options).dispose();
};

scriptJquery.extend(scriptJquery,{
	crtEle:function(tagName,options){
		function makeInline(options,sub= false){
			let eleContent = '';
			if(typeof options === "object"){
				Object.entries(options).forEach(([key, value])=>{
					if(typeof value === "object"){
						eleContent += `${key}="${makeInline(value,1)}"`;
					} else {
						eleContent += `${key}${sub ? ":" : "="}'${value}'${sub ? ";" : ""}`;
					}
				});
			} else if(typeof options === "string"){
				eleContent = options;
			}
			return eleContent;
		}
		eleContent = makeInline(options);
		return scriptJquery(`<${tagName} ${eleContent}>`);
	}
});

scriptJquery.fn.enableLinks = function(){
  this.each(function(){
    scriptJquery(this).html(scriptJquery.parseHTML(scriptJquery(this).html()));
  });
}


class Occlude{
  constructor(){
  }
  occlude(property, element){
    element = (element || this.element);
    var instance = element.data(property || this.property);
    if (instance && !this.occluded)
      return (this.occluded = instance);

    this.occluded = false;
    element.data(property || this.property, this);
    return this.occluded;
  }

};

class OverText extends Occlude{
  Binds = ['reposition', 'assert', 'focus', 'hide'];
  options = {
    element: 'label',
    labelClass: 'overTxtLabel',
    positionOptions: {
      position: 'upperLeft',
      edge: 'upperLeft',
      offset: {
        x: 4,
        y: 2
      }
    },
    poll: false,
    pollInterval: 250,
    wrap: false
  }
  property = 'OverText';
  constructor(element, options){
    super();
    element = this.element = element;

    if (this.occlude()) return this.occluded;
      this.options = scriptJquery.extend(this.options,options);

    this.attach(element);
    OverText.instances.push(this);
    if (this.options.poll) this.poll();
  }
  toElement(){
    return this.element;
  }

  attach(){
    var element = this.element,
      options = this.options,
      value = options.textOverride || element.attr('alt') || element.attr('title');

    if (!value) return this;

    var text = this.text = scriptJquery.crtEle(options.element, {
      'class': options.labelClass,
    }).css({
        lineHeight: 'normal',
        position: 'absolute',
        cursor: 'text',
        top : 2,
        left : 4
    }).click(this.hide.bind(this,options.element == 'label')).html(value)
    .insertAfter(element);

    if (options.element == 'label'){
      if (!element.attr('id')) element.attr('id', 'input_' + $time().toString(36));
      text.attr('for', element.attr('id'));
    }

    if (options.wrap){
      this.textHolder = scriptJquery.crtEle('div', {
        class: 'overTxtWrapper',
      }).css({
          lineHeight: 'normal',
          position: 'relative'
      }).append(text).insertBefore(element);
    }
    this.element.parent().css("position","relative");
    return this.enable();
  }

  destroy(){
   // this.element.eliminate(this.property); // Class.Occlude storage
    this.disable();
    if (this.text) this.text.remove();
    if (this.textHolder) this.textHolder.remove();
    return this;
  }

  disable(){
    this.element.off({
      focus: this.focus.bind(this),
      blur: this.assert.bind(this),
      input: this.assert.bind(this)
    });
    //scriptJquery(window).off('resize', this.reposition.bind(this));
    this.hide(true, true);
    return this;
  }

  enable(){
    this.element.on({
      focus: this.focus.bind(this),
      blur: this.assert.bind(this),
      input: this.assert.bind(this)
    });
    //scriptJquery(window).off().on('resize', this.reposition.bind(this));
    this.reposition();
    return this;
  }

  wrap(){
    if (this.options.element == 'label'){
      if (!this.element.attr('id')) this.element.attr('id', 'input_' + $time().toString(36));
      this.text.attr('for', this.element.attr('id'));
    }
  }

  startPolling(){
    this.pollingPaused = false;
    return this.poll();
  }

  poll(stop){
    //start immediately
    //pause on focus
    //resumeon blur
    if (this.poller && !stop) return this;
    if (stop){
      clearInterval(this.poller);
    } else {
      this.poller = setInterval(function(){
        if (!this.pollingPaused) this.assert(true);
      }.bind(this),this.options.pollInterval);
    }

    return this;
  }

  stopPolling(){
    this.pollingPaused = true;
    return this.poll(true);
  }

  focus(){
    if (this.text && (!this.text.is(":visible") || this.element.prop('disabled'))) return this;
    return this.hide();
  }

  hide(suppressFocus, force){
    if (this.text && (this.text.is(":visible") && (!this.element.prop('disabled') || force))){
      this.text.hide();
      this.pollingPaused = true;
    }
    return this;
  }

  show(){
    if (this.text && !this.text.is(":visible")){
      this.text.show();
      this.reposition();
      //scriptJquery(this).trigger('textShow',this.text,this.element);
      this.pollingPaused = false;
    }
    return this;
  }

  test(){
    return !this.element.val();
  }

  assert(suppressFocus){
    return this[this.test() ? 'show' : 'hide'](suppressFocus);
  }

  reposition(){
    if (!this.element.is(":visible")) return this.stopPolling().hide();
    if (this.text && this.test()){
      let obj = this.options.positionOptions;
      this.text.css({top:obj.offset.y,left:obj.offset.x});
    }
    return this;
  }
};


function htmlspecialchars_decode (string, quote_style) {
  // Convert special HTML entities back to characters
  //
  // version: 1004.2314
  // discuss at: http://phpjs.org/functions/htmlspecialchars_decode
  // +   original by: Mirek Slugen
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Mateusz "loonquawl" Zalega
  // +      input by: ReverseSyntax
  // +      input by: Slawomir Kaniecki
  // +      input by: Scott Cariss
  // +      input by: Francois
  // +   bugfixed by: Onno Marsman
  // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Ratheous
  // +      input by: Mailfaker (http://www.weedem.fr/)
  // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
  // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  // *     returns 1: '<p>this -> &quot;</p>'
  // *     example 2: htmlspecialchars_decode("&amp;quot;");
  // *     returns 2: '&quot;'
  var optTemp = 0, i = 0, noquotes= false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE' : 1,
    'ENT_HTML_QUOTE_DOUBLE' : 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE' : 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i=0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      }
      else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');
  
  return string;
}

OverText.instances = [];


// Auto complete / Auto suggest

AutocompleterRequestJSON = function (field_name, url, hiddenFunction, extraParams, customParams) {
  scriptJquery('#'+field_name).parent().addClass('acWrap');
  scriptJquery('#'+field_name).parent().append('<div class="acBox"></div>');
  scriptJquery('#'+field_name).autocomplete({
    source: function (request, response) {
			var extraParamsObj = {};
			if(extraParams) {
				if(typeof extraParams != 'object') {
					extraParams = [extraParams];
				}
				extraParams.forEach(item => {
					extraParamsObj[item] = scriptJquery('#'+item).val();
				});
			}
      scriptJquery.ajax({
        type: "POST",
        url: url,
          data: {
            text: scriptJquery('#'+field_name).val(),
						...extraParamsObj,
            //type: "user-all"
          },
          success: function( data ) {  
            response(data);
          },
          dataType: 'json',
          minLength: 1,
          delay: 500
      });
    },
    select : function(event, ui) {
      var label = ui.item.label;
      scriptJquery('#'+field_name).val(label.replace(/(<([^>]+)>)/ig,""));
      hiddenFunction(ui.item);
      return false;
    }
  }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    if(customParams && customParams.class)
    ul.addClass(customParams.class); //Ul custom class here
    if(item.photo) {
      return scriptJquery( "<li></li>" ).data("item.autocomplete", item).append(item.photo + '<span>' + item.label + '</span>').appendTo(ul); 
    } else if(item.icon) {
      return scriptJquery( "<li></li>" ).data("item.autocomplete", item).append(item.icon + '<span>' + item.label + '</span>').appendTo(ul);  
    }
  }
}

//Ajax Smoothbox 

//Prevent javascript error before the content has loaded
var executetimesmoothbox = false;

//Add ajaxsmoothbox to href elements that have a class of .ajaxsmoothbox
AttachEventListerSE('click','.ajaxsmoothbox',function(event){
	event.preventDefault();
	ajaxsmoothboxopen(this);
});

function ajaxsmoothboxopen(obj) {
  
	if(!scriptJquery('.ajaxsmoothbox_main').length) {

    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_overlay',
      'class': 'ajaxsmoothbox_overlay'
    }).appendTo(document.body);
    
		scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_main',
      'class': 'ajaxsmoothbox_main'
    }).appendTo(document.body);
    
		scriptJquery("#ajaxsmoothbox_main").html('<div class="ajaxsmoothbox_container" id="ajaxsmoothbox_container"><div class="ajaxsmoothbox_loading"></div></div>');
    
    if(scriptJquery(obj).data('addclass')){
      scriptJquery('#ajaxsmoothbox_container').addClass(scriptJquery(obj).data('addclass'));
    }
    loaddefaultcontent();
	}
	// display the box for the elements href
	ajaxsmoothboxshow(obj);
	return false;
}

//esc key close
scriptJquery(document).on('keyup', function (e) {
  if(scriptJquery('#'+e.target.id).prop('tagName') == 'INPUT' || scriptJquery('#'+e.target.id).prop('tagName') == 'TEXTAREA' || !scriptJquery('#ajaxsmoothbox_container').length)
    return true;
  //ESC key close
  if (e.keyCode === 27) {
    ajaxsmoothboxclose();return false; 
  }
});

scriptJquery(document).on('click','.ajaxsmoothbox_main',function(e){
  if (e.target !== this)
    return;
	ajaxsmoothboxclose();
});

function loaddefaultcontent(){
	var htmlElement = document.getElementsByTagName("html")[0];
  htmlElement.style.overflow = 'hidden';
	scriptJquery("#ajaxsmoothbox_container").css({
		left: ((scriptJquery(window).width() - 300 ) / 2) + 'px',
		top: ((scriptJquery(window).height() - 100 ) / 2) + 'px',
		display: "block"
	});	
}

var Ajaxsmoothbox = {
		javascript : [],
		css : [],
}

// called when the user clicks on a ajaxsmoothbox link
function ajaxsmoothboxshow(obj) {
    if(obj){
      //initialize blank array value
      Ajaxsmoothbox.javascript = Array();
      Ajaxsmoothbox.css = [];
      var url = scriptJquery(obj).attr('href');
      if(url == 'javascript:;' || scriptJquery(obj).hasClass('open'))
        url = scriptJquery(obj).attr('data-url');
      var params = scriptJquery(obj).attr('rel');
      var requestSmoothbox = scriptJquery.ajax({
      dataType: 'html',
      url: url,
      method: 'get',
      data: {
        format: 'html',
        params:params,
        typesmoothbox:'ajaxsmoothbox'
      },
      evalScripts: true,
      success: function(responseHTML) {
        executeCssJavascriptFiles(responseHTML);
      }
    });
  }
}

function ajaxsmoothboxExecuteCode(responseHTML,prevWidth){
  if(typeof ajaxsmoothboxcallbackBefore == 'function')
		ajaxsmoothboxcallbackBefore(responseHTML);

	responseHTML = '<a title="'+en4.core.language.translate("Close")+'" class="ajaxsmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:ajaxsmoothboxclose();"></a>'+responseHTML;
	scriptJquery('#ajaxsmoothbox_container').html(responseHTML);	
	//execute code at run once
	if(!executetimesmoothbox){
		executetimesmoothboxTimeinterval = 10;	
	}
	setTimeout(function(){en4.core.runonce.trigger(); }, executetimesmoothboxTimeinterval);
	resizeajaxsmoothbox(prevWidth);
}

function ajaxsmoothboxclose(){
	scriptJquery('.ajaxsmoothbox_main').remove();
	scriptJquery('#ajaxsmoothbox_overlay').remove();
	var htmlElement = document.getElementsByTagName("html")[0];
	htmlElement.style.overflow = '';
	executetimesmoothbox = false;
  ajaxsmoothboxcallback = function () {};
  ajaxsmoothboxcallbackBefore = function () {};
  if(typeof ajaxsmoothboxcallbackclose == 'function')
		ajaxsmoothboxcallbackclose();
}

function resizeajaxsmoothbox(prevWidth) {

 var linkClose = '<a title="'+en4.core.language.translate("Close")+'" class="ajaxsmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:ajaxsmoothboxclose();"></a>';
 scriptJquery('#ajaxsmoothbox_container').prepend(linkClose);
 var windowheight = scriptJquery(window).height();
 var objHeight =	scriptJquery('#ajaxsmoothbox_container').height();
 var windowwidth= scriptJquery(window).width();
 var objWidth=	scriptJquery('#ajaxsmoothbox_container').width();
 if(objHeight >= windowheight){
  var top = '10'; 
 } else if(objHeight <= windowheight){
  var top = (windowheight - objHeight)/2;		 
 }
 var width = scriptJquery('#ajaxsmoothbox_container').find('div').first().width();
 var	setwidth= width /2 ;
 scriptJquery("#ajaxsmoothbox_container").animate({
		top: top+'px',
		width: width+'px',
		left: (((scriptJquery(window).width() ) / 2) - setwidth) + 'px',
 },0,function() {
    if(typeof ajaxsmoothboxcallback == 'function')
		  ajaxsmoothboxcallback();
    // Animation complete.
  });
}

var successLoad;
function executeCssJavascriptFiles(responseHTML) {
	var jsCount = Ajaxsmoothbox.javascript.length;
	var cssCount = Ajaxsmoothbox.css.length;
	//store the total file so we execute all required function after css and js load.
	var totalFiles = jsCount + cssCount;
	successLoad= 0;
	var isLoaded = 0;
	var prevWidth = scriptJquery('#ajaxsmoothbox_container').width();
	if(jsCount == cssCount){
		isLoaded = 1;
		ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
	}
	//execute jsvascript files
	for(var i=0;i < jsCount;i++){
			Asset.javascript(Ajaxsmoothbox.javascript[i], {
			onLoad: function(e) {
				successLoad++;
				if (successLoad === totalFiles){
				    isLoaded = 1;
					ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
				}
			}});
	}
		//execute css files
	for(var i=0;i < cssCount;i++){
			Asset.css(Ajaxsmoothbox.css[i], {
			onLoad: function() {
				successLoad++;
				if (successLoad === totalFiles){
				    isLoaded = 1;
					ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
				}
			}});
	}
	if(!isLoaded){
	    ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
	}
}


function ajaxsmoothboxDialoge(html) {  
	if(!scriptJquery('.ajaxsmoothbox_main').length){
    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_overlay',
      'class': 'ajaxsmoothbox_overlay'
    }).appendTo(document.body);
    
    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_main',
      'class': 'ajaxsmoothbox_main'
    }).appendTo(document.body);

		document.getElementById("ajaxsmoothbox_main").innerHTML = '<div class="ajaxsmoothbox_container" id="ajaxsmoothbox_container"><div class="sesbasic_loading_container"></div></div>';
    loaddefaultcontent();
    executeCssJavascriptFiles("<div class='sesbasic_smoothbox_view_number'><div class='_header'>"+en4.core.language.translate("Phone Number")+"</div><div class='_cont'>" + html + "</div></div>");
	}
	// display the box for the elements href
	return false;
}

//Ajax Smoothbox 


/* Modifided script from the simple-page-ordering plugin */
var ajaxurl;
var type_id;
scriptJquery(function($) {
  scriptJquery('table.widefat.admin_table_order tbody th, table.admin_table_order tbody tr').css('cursor','move');
  scriptJquery("table.admin_table_order").sortable({
		items: 'tbody tr:not(.inline-edit-row)',
		cursor: 'move',
		axis: 'y',
		forcePlaceholderSize: true,
		helper: function (e, item) {
			return item.clone();
		},
		opacity: .3,
		placeholder: 'product-cat-placeholder',
		scrollSensitivity: 40,
		start: function(event, ui) {
			ui.placeholder.html(ui.item.html());
			if ( ! ui.item.hasClass('alternate') ) ui.item.css( 'background-color', '#ffffff' );
			ui.item.children('td,th').css('border-bottom-width','0');
			ui.item.css( 'outline', '1px solid #aaa' );
		},
		stop: function(event, ui) {
			ui.item.removeAttr('style');
			ui.item.css('cursor','move');
			ui.item.children('td,th').css('border-bottom-width','1px');
		},
		update: function(event, ui) {
			$('table.admin_table_order tbody th, table.widefat tbody td').css('cursor','default');
			//$("table.admin_table_order tbody").sortable('disable');
			var termid = ui.item.find('.check-column').val();	// this post id
			var termparent = ui.item.find('.parent').html(); 	// post parent

			var prevtermid = ui.item.prev().find('.check-column').val();
			var nexttermid = ui.item.next().find('.check-column').val();
			
			// can only sort in same tree
			var prevtermparent = undefined;
			if ( prevtermid != undefined ) {
				var prevtermparent = ui.item.prev().find('.parent').html();
				if ( prevtermparent != termparent) prevtermid = undefined;
			}

			var nexttermparent = undefined;
			if ( nexttermid != undefined ) {
				nexttermparent = ui.item.next().find('.parent').html();
				if ( nexttermparent != termparent) nexttermid = undefined;
			}
			// if previous and next not at same tree level, or next not at same tree level and the previous is the parent of the next, or just moved item beneath its own children
			if ( ( prevtermid == undefined && nexttermid == undefined ) || ( nexttermid == undefined && nexttermparent == prevtermid ) || ( nexttermid != undefined && prevtermparent == termid ) ) {
				$("table.admin_table_order").sortable('cancel');
				return;
			}
			var categoryorder = "";
			scriptJquery(".ui-sortable tbody tr").each(function(i) {
        if (categoryorder=='')
          categoryorder = scriptJquery(this).attr('data-id');
        else
          categoryorder += "," + scriptJquery(this).attr('data-id');
      });
			// show spinner
      var imageURL = en4.core.baseUrl+"application/modules/Core/externals/images/large-loading.gif";
			ui.item.find('.check-column').hide().after('<img alt="processing" src="'+imageURL+'" class="waiting" style="margin-left: 6px;" />');
			// go do the sorting stuff via ajax
      $.post( ajaxurl, {id: termid, nextid: nexttermid,categoryorder:categoryorder, type_id: type_id}, function(response){
        scriptJquery('table.admin_table_order tbody th, table.admin_table_order tbody td').css('cursor','move');
				//$("table.admin_table_order tbody").sortable('enable');
				if ( response == 'children' ) window.location.reload();
				else {
					ui.item.find('.check-column').show().siblings('img').remove();
				}
			});
			// fix cell colors
      scriptJquery( 'table.admin_table_order tbody tr' ).each(function(){
				 scriptJquery(this).css('cursor','move');
			});
		}
	});

});

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
  /* *******************************************
// Copyright 2010, Anthony Hand
//
// File version date: November 28, 2010
//
// LICENSE INFORMATION
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//        http://www.apache.org/licenses/LICENSE-2.0
// Unless required by applicable law or agreed to in writing,
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
// either express or implied. See the License for the specific
// language governing permissions and limitations under the License.
//
//
// ABOUT THIS PROJECT
//   Project Owner: Anthony Hand
//   Email: anthony.hand@gmail.com
//   Web Site: http://www.mobileesp.com
//   Source Files: http://code.google.com/p/mobileesp/
//
//   Versions of this code are available for:
//      PHP, JavaScript, Java, ASP.NET (C#), and Ruby
//
//
// WARNING:
//   These JavaScript-based device detection features may ONLY work
//   for the newest generation of smartphones, such as the iPhone,
//   Android and Palm WebOS devices.
//   These device detection features may NOT work for older smartphones
//   which had poor support for JavaScript, including
//   older BlackBerry, PalmOS, and Windows Mobile devices.
//   Additionally, because JavaScript support is extremely poor among
//   'feature phones', these features may not work at all on such devices.
//   For better results, consider using a server-based version of this code,
//   such as Java, APS.NET, PHP, or Ruby.
//
// *******************************************
*/

//Optional: Store values for quickly accessing same info multiple times.
//Stores whether the device is an iPhone or iPod Touch.
var isIphone = false;
//Stores whether is the iPhone tier of devices.
var isTierIphone = false;
//Stores whether the device can probably support Rich CSS, but JavaScript support is not assumed. (e.g., newer BlackBerry, Windows Mobile)
var isTierRichCss = false;
//Stores whether it is another mobile device, which cannot be assumed to support CSS or JS (eg, older BlackBerry, RAZR)
var isTierGenericMobile = false;

//Initialize some initial string variables we'll look for later.
var engineWebKit = "webkit";
var deviceIphone = "iphone";
var deviceIpod = "ipod";
var deviceIpad = "ipad";
var deviceMacPpc = "macintosh"; //Used for disambiguation

var deviceAndroid = "android";
var deviceGoogleTV = "googletv";

var deviceNuvifone = "nuvifone"; //Garmin Nuvifone

var deviceSymbian = "symbian";
var deviceS60 = "series60";
var deviceS70 = "series70";
var deviceS80 = "series80";
var deviceS90 = "series90";

var deviceWinPhone7 = "windows phone os 7";
var deviceWinMob = "windows ce";
var deviceWindows = "windows";
var deviceIeMob = "iemobile";
var devicePpc = "ppc"; //Stands for PocketPC
var enginePie = "wm5 pie";  //An old Windows Mobile

var deviceBB = "blackberry";
var vndRIM = "vnd.rim"; //Detectable when BB devices emulate IE or Firefox
var deviceBBStorm = "blackberry95"; //Storm 1 and 2
var deviceBBBold = "blackberry97"; //Bold
var deviceBBTour = "blackberry96"; //Tour
var deviceBBCurve = "blackberry89"; //Curve 2
var deviceBBTorch = "blackberry 98"; //Torch

var devicePalm = "palm";
var deviceWebOS = "webos"; //For Palm's new WebOS devices
var engineBlazer = "blazer"; //Old Palm browser
var engineXiino = "xiino";

var deviceKindle = "kindle"; //Amazon Kindle, eInk one.

//Initialize variables for mobile-specific content.
var vndwap = "vnd.wap";
var wml = "wml";

//Initialize variables for random devices and mobile browsers.
//Some of these may not support JavaScript
var deviceBrew = "brew";
var deviceDanger = "danger";
var deviceHiptop = "hiptop";
var devicePlaystation = "playstation";
var deviceNintendoDs = "nitro";
var deviceNintendo = "nintendo";
var deviceWii = "wii";
var deviceXbox = "xbox";
var deviceArchos = "archos";

var engineOpera = "opera"; //Popular browser
var engineNetfront = "netfront"; //Common embedded OS browser
var engineUpBrowser = "up.browser"; //common on some phones
var engineOpenWeb = "openweb"; //Transcoding by OpenWave server
var deviceMidp = "midp"; //a mobile Java technology
var uplink = "up.link";
var engineTelecaQ = 'teleca q'; //a modern feature phone browser

var devicePda = "pda";
var mini = "mini";  //Some mobile browsers put 'mini' in their names.
var mobile = "mobile"; //Some mobile browsers put 'mobile' in their user agent strings.
var mobi = "mobi"; //Some mobile browsers put 'mobi' in their user agent strings.

//Use Maemo, Tablet, and Linux to test for Nokia's Internet Tablets.
var maemo = "maemo";
var maemoTablet = "tablet";
var linux = "linux";
var qtembedded = "qt embedded"; //for Sony Mylo and others
var mylocom2 = "com2"; //for Sony Mylo also

//In some UserAgents, the only clue is the manufacturer.
var manuSonyEricsson = "sonyericsson";
var manuericsson = "ericsson";
var manuSamsung1 = "sec-sgh";
var manuSony = "sony";
var manuHtc = "htc"; //Popular Android and WinMo manufacturer

//In some UserAgents, the only clue is the operator.
var svcDocomo = "docomo";
var svcKddi = "kddi";
var svcVodafone = "vodafone";

//Disambiguation strings.
var disUpdate = "update"; //pda vs. update



//Initialize our user agent string.
var uagent = navigator.userAgent.toLowerCase();


//**************************
// Detects if the current device is an iPhone.
function DetectIphone()
{
   if (uagent.search(deviceIphone) > -1)
   {
      //The iPad and iPod Touch say they're an iPhone! So let's disambiguate.
      if (DetectIpad() ||
          DetectIpod())
         return false;
      else
         return true;
   }
   else
      return false;
}

//**************************
// Detects if the current device is an iPod Touch.
function DetectIpod()
{
   if (uagent.search(deviceIpod) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is an iPad tablet.
function DetectIpad()
{
   if (uagent.search(deviceIpad) > -1  && DetectWebkit())
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is an iPhone or iPod Touch.
function DetectIphoneOrIpod()
{
   //We repeat the searches here because some iPods
   //  may report themselves as an iPhone, which is ok.
   if (uagent.search(deviceIphone) > -1 ||
       uagent.search(deviceIpod) > -1)
       return true;
    else
       return false;
}

//**************************
// Detects if the current device is an Android OS-based device.
function DetectAndroid()
{
   if (uagent.search(deviceAndroid) > -1)
      return true;
   else
      return false;
}


//**************************
// Detects if the current device is an Android OS-based device and
//   the browser is based on WebKit.
function DetectAndroidWebKit()
{
   if (DetectAndroid() && DetectWebkit())
      return true;
   else
      return false;
}


//**************************
// Detects if the current device is a GoogleTV.
function DetectGoogleTV()
{
   if (uagent.search(deviceGoogleTV) > -1)
      return true;
   else
      return false;
}


//**************************
// Detects if the current browser is based on WebKit.
function DetectWebkit()
{
   if (uagent.search(engineWebKit) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is the Nokia S60 Open Source Browser.
function DetectS60OssBrowser()
{
   if (DetectWebkit())
   {
     if ((uagent.search(deviceS60) > -1 ||
          uagent.search(deviceSymbian) > -1))
        return true;
     else
        return false;
   }
   else
      return false;
}

//**************************
// Detects if the current device is any Symbian OS-based device,
//   including older S60, Series 70, Series 80, Series 90, and UIQ,
//   or other browsers running on these devices.
function DetectSymbianOS()
{
   if (uagent.search(deviceSymbian) > -1 ||
       uagent.search(deviceS60) > -1 ||
       uagent.search(deviceS70) > -1 ||
       uagent.search(deviceS80) > -1 ||
       uagent.search(deviceS90) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a
// Windows Phone 7 device.
function DetectWindowsPhone7()
{
   if (uagent.search(deviceWinPhone7) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a Windows Mobile device.
// Excludes Windows Phone 7 devices.
// Focuses on Windows Mobile 6.xx and earlier.
function DetectWindowsMobile()
{
   //Exclude new Windows Phone 7.
   if (DetectWindowsPhone7())
      return false;
   //Most devices use 'Windows CE', but some report 'iemobile'
   //  and some older ones report as 'PIE' for Pocket IE.
   if (uagent.search(deviceWinMob) > -1 ||
       uagent.search(deviceIeMob) > -1 ||
       uagent.search(enginePie) > -1)
      return true;
   //Test for Windows Mobile PPC but not old Macintosh PowerPC.
   if ((uagent.search(devicePpc) > -1) &&
       !(uagent.search(deviceMacPpc) > -1))
      return true;
   //Test for Windwos Mobile-based HTC devices.
   if (uagent.search(manuHtc) > -1 &&
       uagent.search(deviceWindows) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a BlackBerry of some sort.
function DetectBlackBerry()
{
   if (uagent.search(deviceBB) > -1)
      return true;
   if (uagent.search(vndRIM) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a BlackBerry device AND uses a
//    WebKit-based browser. These are signatures for the new BlackBerry OS 6.
//    Examples: Torch
function DetectBlackBerryWebKit()
{
   if (uagent.search(deviceBB) > -1 &&
       uagent.search(engineWebKit) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a BlackBerry Touch
//    device, such as the Storm or Torch.
function DetectBlackBerryTouch()
{
   if ((uagent.search(deviceBBStorm) > -1) ||
    (uagent.search(deviceBBTorch) > -1))
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a BlackBerry OS 5 device AND
//    has a more capable recent browser.
//    Examples, Storm, Bold, Tour, Curve2
//    Excludes the new BlackBerry OS 6 browser!!
function DetectBlackBerryHigh()
{
   //Disambiguate for BlackBerry OS 6 (WebKit) browser
   if (DetectBlackBerryWebKit())
      return false;
   if (DetectBlackBerry())
   {
     if (DetectBlackBerryTouch() ||
        uagent.search(deviceBBBold) > -1 ||
        uagent.search(deviceBBTour) > -1 ||
        uagent.search(deviceBBCurve) > -1)
        return true;
     else
        return false;
   }
   else
      return false;
}

//**************************
// Detects if the current browser is a BlackBerry device AND
//    has an older, less capable browser.
//    Examples: Pearl, 8800, Curve1.
function DetectBlackBerryLow()
{
   if (DetectBlackBerry())
   {
     //Assume that if it's not in the High tier, then it's Low.
     if (DetectBlackBerryHigh())
        return false;
     else
        return true;
   }
   else
      return false;
}


//**************************
// Detects if the current browser is on a PalmOS device.
function DetectPalmOS()
{
   //Most devices nowadays report as 'Palm',
   //  but some older ones reported as Blazer or Xiino.
   if (uagent.search(devicePalm) > -1 ||
       uagent.search(engineBlazer) > -1 ||
       uagent.search(engineXiino) > -1)
   {
     //Make sure it's not WebOS first
     if (DetectPalmWebOS())
        return false;
     else
        return true;
   }
   else
      return false;
}

//**************************
// Detects if the current browser is on a Palm device
//   running the new WebOS.
function DetectPalmWebOS()
{
   if (uagent.search(deviceWebOS) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a
//   Garmin Nuvifone.
function DetectGarminNuvifone()
{
   if (uagent.search(deviceNuvifone) > -1)
      return true;
   else
      return false;
}


//**************************
// Check to see whether the device is a 'smartphone'.
//   You might wish to send smartphones to a more capable web page
//   than a dumbed down WAP page.
function DetectSmartphone()
{
   if (DetectIphoneOrIpod())
      return true;
   if (DetectS60OssBrowser())
      return true;
   if (DetectSymbianOS())
      return true;
   if (DetectWindowsMobile())
      return true;
   if (DetectWindowsPhone7())
      return true;
   if (DetectAndroid())
      return true;
   if (DetectBlackBerry())
      return true;
   if (DetectPalmWebOS())
      return true;
   if (DetectPalmOS())
      return true;
   if (DetectGarminNuvifone())
      return true;

   //Otherwise, return false.
   return false;
};

//**************************
// Detects if the current device is an Archos media player/Internet tablet.
function DetectArchos()
{
   if (uagent.search(deviceArchos) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects whether the device is a Brew-powered device.
function DetectBrewDevice()
{
   if (uagent.search(deviceBrew) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects the Danger Hiptop device.
function DetectDangerHiptop()
{
   if (uagent.search(deviceDanger) > -1 ||
       uagent.search(deviceHiptop) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is on one of
// the Maemo-based Nokia Internet Tablets.
function DetectMaemoTablet()
{
   if (uagent.search(maemo) > -1)
      return true;
   //Must be Linux + Tablet, or else it could be something else.
   if (uagent.search(maemoTablet) > -1 &&
       uagent.search(linux) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current browser is a Sony Mylo device.
function DetectSonyMylo()
{
   if (uagent.search(manuSony) > -1)
   {
     if (uagent.search(qtembedded) > -1 ||
         uagent.search(mylocom2) > -1)
        return true;
     else
        return false;
   }
   else
      return false;
}

//**************************
// Detects if the current browser is Opera Mobile or Mini.
function DetectOperaMobile()
{
   if (uagent.search(engineOpera) > -1)
   {
     if (uagent.search(mini) > -1 ||
         uagent.search(mobi) > -1)
        return true;
     else
        return false;
   }
   else
      return false;
}

//**************************
// Detects if the current device is a Sony Playstation.
function DetectSonyPlaystation()
{
   if (uagent.search(devicePlaystation) > -1)
      return true;
   else
      return false;
};

//**************************
// Detects if the current device is a Nintendo game device.
function DetectNintendo()
{
   if (uagent.search(deviceNintendo) > -1   ||
	uagent.search(deviceWii) > -1 ||
	uagent.search(deviceNintendoDs) > -1)
      return true;
   else
      return false;
};

//**************************
// Detects if the current device is a Microsoft Xbox.
function DetectXbox()
{
   if (uagent.search(deviceXbox) > -1)
      return true;
   else
      return false;
};

//**************************
// Detects if the current device is an Internet-capable game console.
function DetectGameConsole()
{
   if (DetectSonyPlaystation())
      return true;
   if (DetectNintendo())
      return true;
   if (DetectXbox())
      return true;
   else
      return false;
};

//**************************
// Detects if the current device is a Kindle.
function DetectKindle()
{
   if (uagent.search(deviceKindle) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is a mobile device.
//  This method catches most of the popular modern devices.
//  Excludes Apple iPads.
function DetectMobileQuick()
{
   //Let's say no if it's an iPad, which contains 'mobile' in its user agent.
   if (DetectIpad())
      return false;

   //Most mobile browsing is done on smartphones
   if (DetectSmartphone())
      return true;

   if (uagent.search(deviceMidp) > -1 ||
	DetectBrewDevice())
      return true;

   if (DetectOperaMobile())
      return true;

   if (uagent.search(engineNetfront) > -1)
      return true;
   if (uagent.search(engineUpBrowser) > -1)
      return true;
   if (uagent.search(engineOpenWeb) > -1)
      return true;

   if (DetectDangerHiptop())
      return true;

   if (DetectMaemoTablet())
      return true;
   if (DetectArchos())
      return true;

   if ((uagent.search(devicePda) > -1) &&
        (uagent.search(disUpdate) < 0)) //no index found
      return true;
   if (uagent.search(mobile) > -1)
      return true;

   if (DetectKindle())
      return true;

   return false;
};


//**************************
// Detects in a more comprehensive way if the current device is a mobile device.
function DetectMobileLong()
{
   if (DetectMobileQuick())
      return true;
   if (DetectGameConsole())
      return true;
   if (DetectSonyMylo())
      return true;

   //Detect for certain very old devices with stupid useragent strings.
   if (uagent.search(manuSamsung1) > -1 ||
	uagent.search(manuSonyEricsson) > -1 ||
	uagent.search(manuericsson) > -1)
      return true;

   if (uagent.search(svcDocomo) > -1)
      return true;
   if (uagent.search(svcKddi) > -1)
      return true;
   if (uagent.search(svcVodafone) > -1)
      return true;


   return false;
};


//*****************************
// For Mobile Web Site Design
//*****************************

//**************************
// The quick way to detect for a tier of devices.
//   This method detects for devices which can
//   display iPhone-optimized web content.
//   Includes iPhone, iPod Touch, Android, WebOS, etc.
function DetectTierIphone()
{
   if (DetectIphoneOrIpod())
      return true;
   if (DetectAndroid())
      return true;
   if (DetectAndroidWebKit())
      return true;
   if (DetectWindowsPhone7())
      return true;
   if (DetectBlackBerryWebKit())
      return true;
   if (DetectPalmWebOS())
      return true;
   if (DetectGarminNuvifone())
      return true;
   if (DetectMaemoTablet())
      return true;
   else
      return false;
};

//**************************
// The quick way to detect for a tier of devices.
//   This method detects for devices which are likely to be
//   capable of viewing CSS content optimized for the iPhone,
//   but may not necessarily support JavaScript.
//   Excludes all iPhone Tier devices.
function DetectTierRichCss()
{
    if (DetectMobileQuick())
    {
       if (DetectTierIphone())
          return false;

       //The following devices are explicitly ok.
       if (DetectWebkit())
          return true;
       if (DetectS60OssBrowser())
          return true;

       //Note: 'High' BlackBerry devices ONLY
       if (DetectBlackBerryHigh())
          return true;

       if (DetectWindowsMobile())
          return true;

       if (uagent.search(engineTelecaQ) > -1)
          return true;

       else
          return false;
    }
    else
      return false;
};

//**************************
// The quick way to detect for a tier of devices.
//   This method detects for all other types of phones,
//   but excludes the iPhone and RichCSS Tier devices.
// NOTE: This method probably won't work due to poor
//  support for JavaScript among other devices.
function DetectTierOtherPhones()
{
    if (DetectMobileLong())
    {
       //Exclude devices in the other 2 categories
       if (DetectTierIphone())
          return false;
       if (DetectTierRichCss())
          return false;

       //Otherwise, it's a YES
       else
          return true;
    }
    else
      return false;
};
(function(){
  this.Smoothbox = {
  instance : false,
  bind : function(selector)
  {
    // All children of element
    var elements;
    if( $type(selector) == 'element' ){
      elements = selector.find('a.smoothbox');
    } else if( $type(selector) == 'string' ){
      elements = scriptJquery(selector);
    } else {
      elements = scriptJquery("a.smoothbox");
    }
    elements.each(function(el)
    {
      if( scriptJquery(this).prop("tagName") != 'A' || typeof scriptJquery(this).data('smoothboxed') !=="undefined")
      {
        return;
      }
      var params = {};
      params.title = scriptJquery(this).attr('title');
      params.url = scriptJquery(this).attr('href');
      scriptJquery(this).data('smoothbox', params);
      scriptJquery(this).data('smoothboxed', true);
      scriptJquery(this).on('click', function(event)
      {
        if(scriptJquery(this).attr('href') == 'javascript:;' || scriptJquery(this).attr('href') == 'javascript:void(0)' || scriptJquery(this).attr('href') == 'javascript:void(0);')
          return;
        event.preventDefault(); // Maybe move this to after next line when done debugging
        Smoothbox.open(scriptJquery(this));
      });
    });
  },
  close : function()
  {
    if( this.instance )
    {
      this.instance.close();
      scriptJquery('html').removeClass('overflow-hidden');
      scriptJquery('body').removeClass('overflow-hidden');
    }
  },
  open : function(spec, options)
  {
    if(this.instance )
    {
      this.instance.close();
    } 
    // Check the options array
    if( $type(options) == 'object' ) {
      options = new Hash(options);
    } else if( $type(options) != 'hash' ) {
      options = new Hash();
    }
    // Check the arguments
    // Spec as element
    if( $type(spec) == 'object' && Object.getPrototypeOf(spec) === scriptJquery.prototype) {
      // This is a link
      if(spec.prop("tagName").toLowerCase() == 'a' ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'link' : spec,
          'element' : spec,
          'url' : spec.attr('href'),
          'title' : spec.attr('title')
        });
      }
      // This is some other element
      else {
        spec = new Hash({
          'mode' : 'Inline',
          'title' : spec.attr('title'),
          'element' : spec
        });
      }
    }
    // Spec as string
    else if( $type(spec) == 'string' ) {
      // Spec is url
      if( spec.length < 4000 && (spec.substring(0, 1) == '/' ||
          spec.substring(0, 1) == '.' ||
          spec.substring(0, 4) == 'http' ||
          !spec.match(/[ <>"'{}|^~\[\]`]/)
        )
      ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'url' : spec
        });
      }
      // Spec is a string
      else {
        spec = new Hash({
          'mode' : 'String',
          'bodyText' : spec
        });
      }
    }
    // Spec as object or hash
    else if( $type(spec) == 'object' || $type(spec) == 'hash' ) {
      // Don't do anything?
    }
    // Unknown spec
    else {
      spec = new Hash();
    }
    // Now lets start the fun stuff
    spec.extend(options);
    var mode = spec.get('mode');
    spec.erase('mode');
    if( !mode ) {
      if( spec.has('url') ) {
        //if( spec.get('url').match(/\.(jpe?g|png|gif|bmp)/gi) ) {
          //mode = 'Image';
        //} else {
          mode = 'Iframe';
        //}
      }
      else if( spec.has('element') ) {
        mode = 'Inline';
      }
      else if( spec.has('bodyText') ) {
        mode = 'String';
      }
      else {
        return;
      }
    }
    if( !$type(Smoothbox.Modal[mode]) )
    {
      //mode = 'Iframe';
      return;
    }
    this.instance = new Smoothbox.Modal[mode](spec.getClean());
    this.instance.load(spec);
  }
};

class Modal {
  options =  {
    url : null,
    width : 480,
    height : 320,

    // Do or do not
    transitions : false,
    overlay : true,
    loading : true,
    
    noOverlayClose : false,

    autoResize : true,
    autoFormat : 'smoothbox'

    //useFixed : false
  }

  eventProto = {};

  overlay = false;

  window = false;

  content = false;

  loading = false;
  constructor(options)
  {
    scriptJquery.extend(this.options,options);
    if($type(this.options.url)){
      this.options.url = this.getAbsoluteURL(this.options.url);
    }
  }
  getAbsoluteURL(url){
    let urlObj = new URL(window.location.href);
    if(url.indexOf(urlObj.host) === -1){
      return urlObj.origin+url;
    }  
    return url;    
  }
  close()
  {
    this.onClose();

    if(this.window){
      window.removeEventListener('scroll', this.eventProto.scroll);
      window.removeEventListener('resize', this.eventProto.resize);
    }
    if( this.window ) this.window.remove();
    if( this.overlay ) this.overlay.remove();
    if( this.loading ) this.loading.remove();
    Smoothbox.instance = false;
  }

  load()
  {
    this.create();
    
    // Add Events
    var bind = this;
    this.eventProto.resize = function() {
      bind.positionOverlay();
      bind.positionWindow();
    }

    this.eventProto.scroll = function()
    {
      bind.positionOverlay();
      bind.positionWindow();
    };

    window.addEventListener('resize', this.eventProto.resize);
    window.addEventListener('scroll', this.eventProto.scroll);

    this.position();
    this.showOverlay();
    this.showLoading();
  }

  create()
  {
    this.createOverlay();
    this.createLoading();
    this.createWindow();
  }

  createLoading()
  {
    if( this.loading || !this.options.loading ) {
      return;
    }

    var bind = this;
    
    this.loading = scriptJquery.crtEle('div', {
      id : 'TB_load'
    });
    this.loading.appendTo(document.body);

    var loadingImg = scriptJquery.crtEle('img', {
      src : 'externals/smoothbox/loading.gif' // @todo Move to CSS
    });
    loadingImg.appendTo(this.loading);
  }

  createOverlay()
  {
    if( this.overlay || !this.options.overlay ) {
      return;
    }
    var bind = this;
    this.overlay = scriptJquery.crtEle('div', {
      'id' : 'TB_overlay',
      'style':`'position'='absolute';'top'='0px';'left'='0px';'visibility'='visible`,
      'opacity' : 0
    });
    this.overlay.appendTo(document.body);

    if( !this.options.noOverlayClose ) {
      this.overlay.on('click', function() {
        bind.close();
        scriptJquery('html').removeClass('overflow-hidden');
        scriptJquery('body').removeClass('overflow-hidden');
      }.bind(bind));
    }
  }

  createWindow()
  {
    if( this.window ) {
      return;
    }

    var bind = this;
    
    this.window = scriptJquery.crtEle('div', {
      'id' : 'TB_window',
    }).css('opacity',0);
    this.window.appendTo(scriptJquery(document.body));

    var title = scriptJquery.crtEle('div', {
      id : 'TB_title'
    });
    title.appendTo(this.window);

    var titleText = scriptJquery.crtEle('div', {
      id : 'TB_ajaxWindowTitle',
      html : this.options.title
    });
    titleText.appendTo(title);

    var titleClose = scriptJquery.crtEle('div', {
      id : 'TB_closeAjaxWindow',
    });
    titleClose.on("click",function() {
         bind.close();
    });
    titleClose.appendTo(title);

    var titleCloseLink = scriptJquery.crtEle('a',{
      id : 'TB_title',
      href : 'javascript:void(0);',
      title : 'close',
      html : 'close',
    })
    titleCloseLink.on("click",function() {
         bind.close();
    });
    titleCloseLink.appendTo(titleClose);
  }

  position()
  {
    this.positionOverlay();
    this.positionWindow();
    this.positionLoading();
  }

  positionLoading()
  {
    if(!this.loading)
    {
      return;
    }
    this.loading.css({
        "left": (this.getScroll(window).x + (this.getSize().x - 56) / 2) + 'px',
        "top": (this.getScroll(window).y + ((this.getSize().y - 20) / 2)) + 'px',
        "display": "block"
    });
  }

  positionOverlay()
  {
    if( !this.overlay )
    {
      return;
    }
    this.overlay.css({
        "height" : '0px',
        "width" : '0px'
    });
    
    if( !this.options.noOverlay )
    {
      this.overlay.css({
          "height" : this.getScrollSize().y + 'px',
          "width" : this.getScrollSize().x + 'px'
      }); 
    }
  }

  positionWindow()
  {
    if( !this.window ) {
      return;
    }
    this.window.css({
      "width" : this.options.width + 'px',
      "left" : (this.getScroll(window).x + (this.getSize().x - this.options.width) / 2) + 'px',
      "top" : (this.getScroll(window).y + (this.getSize().y - this.options.height) / 2) + 'px'
    });
  }

  show()
  {
    this.showOverlay();
    this.showLoading();
    this.showWindow();
  }

  showLoading()
  {
    if( !this.loading )
    {
      return;
    }

    if( this.options.transitions )
    {
      //this.loading.tween('opacity', [0, 1]);
    }
    else
    {
      this.loading.css('opacity', 1);
      this.loading.css('visibility', 'visible');
    }
  }
  
  showOverlay()
  {
    if( !this.overlay ) {
      return;
    }

    // if( Browser.Engine.trident /*&& this.overlay.style.visibility == 'hidden'*/ ){
    //   //this.overlay.style.visibility = 'visible';
    //   this.overlay.style.display = '';
    // }

    if( this.options.transitions )
    {
      //this.overlay.tween('opacity', [0, 0.6]);
    }
    else
    {
      this.overlay.css('opacity', 0.6);
      this.overlay.css('visibility', 'visible');
    }
  }

  showWindow()
  {
    if( !this.window )
    {
      return;
    }

    // if( Browser.Engine.trident /* && this.window.style.visibility == 'hidden'*/ ){
    //   //this.window.style.visibility = 'visible';
    //   this.window.style.display = '';
    // }
    // Try to autoresize the window
    if( typeof(this.doAutoResize) == 'function' )
    {
      this.doAutoResize();
    }

    if(this.options.transitions ) {
      //this.window.tween('opacity', [0, 1]);
    } else {
      this.window.css('opacity', 1);
      this.window.css('visibility', 'visible');
    }
  }

  hide()
  {
    this.hideLoading();
    this.hideOverlay();
    this.hideWindow();
  }

  hideLoading()
  {
    if( !this.loading ) {
      return;
    }

    if( this.options.transitions ) {
      //this.loading.tween('opacity', [1, 0]);
    } else {
      this.loading.css('opacity', 0);
    }
  }

  hideOverlay()
  {
    if( !this.overlay )
    {
      return;
    }
    
    if( this.options.transitions ) {
      //this.overlay.tween('opacity', [0.6, 0]);
    } else {
      this.overlay.css('opacity', 0);
    }
  }

  hideWindow()
  {
    if( !this.window )
    {
      return;
    }
    
    if( this.options.transitions ) {
     /* var bind = this;
      this.window.tween('opacity', [1, 0]);
      this.window.get('tween').addEventListener('complete', function() {
        bind.fireEvent('closeafter');
      }); */
    }
    else
    {
      this.window.css('opacity', 0);
    }
  }

  getCoordinates(element){
    return {
      x : element["clientWidth"],
      y : element["clientHeight"]
    }
  }
  getScrollSize(element){
    return {x: Math.max(
        document.body.scrollWidth, document.documentElement.scrollWidth,
        document.body.offsetWidth, document.documentElement.offsetWidth,
        document.body.clientWidth, document.documentElement.clientWidth
      ),
      y: Math.max(
      document.body.scrollHeight, document.documentElement.scrollHeight,
      document.body.offsetHeight, document.documentElement.offsetHeight,
      document.body.clientHeight, document.documentElement.clientHeight
    )};
  }
  getSize(){
    return {x:document.documentElement.clientWidth,y:document.documentElement.clientHeight};
  }
  getScroll(n){
    let m = scriptJquery(n)
    return {x:n.pageXOffset|| m.scrollLeft(),y:n.pageYOffset||m.scrollTop()};
  }
  doAutoResize(element)
  {
    if( !element || !this.options.autoResize )
    {
      return;
    }

    var size = ({x:element.width(),y:element.height()} || this.getScrollSize(element)); 
    var winSize = this.getCoordinates(document.documentElement);
    if( size.x + 70 > winSize.x ) size.x = winSize.x - 70;
    if( size.y + 70 > winSize.y ) size.y = winSize.y - 70;

    this.content.css({
      'width' : (size.x + 20) + 'px',
      'height' : (size.y + 20) + 'px'
    });

    this.options.width = this.content.width();
    this.options.height = this.content.height();
    this.positionWindow();
  }
  // events
  onLoad()
  {
    //this.fireEvent('load', this);
  }

  onOpen()
  {
    //this.fireEvent('open', this);
  }

  onClose()
  {
    //this.fireEvent('close', this);
  }

  onCloseAfter()
  {
    //this.fireEvent('closeafter', this);
  }
}
Smoothbox.Modal = Modal;

class Iframe extends Modal{
  constructor(options){
    super(options);
  }
  load()
  {
    super.load();
    if( this.content ) {
      return;
    }
    var bind = this;
    var loadIsOkay = true;
    var uriSrc = new URL(this.options.url);
    if( this.options.autoFormat ) {
      uriSrc.searchParams.set('format',this.options.autoFormat);
    }
    this.content = scriptJquery.crtEle('iframe',{
      src : uriSrc.toString(),
      id : 'TB_iframeContent',
      name : 'TB_iframeContent',
      frameborder : '0',
      width : this.options.width,
      height : this.options.height,
    });
    this.content.load(function() {
      if( loadIsOkay ) {
        loadIsOkay = false;
        this.hideLoading();
        this.showWindow();
        this.onLoad();
      } else {
        this.doAutoResize();
      }
      if(!scriptJquery('body').hasClass('admin')) {
        scriptJquery('html').addClass('overflow-hidden');
        scriptJquery('body').addClass('overflow-hidden');
      }
    }.bind(bind));
    this.content.appendTo(this.window);
  }
  doAutoResize()
  {
    if(!this.options.autoResize ) {
      return;
    }
    // Check if from same host
    var iframe = this.content;
    var host = (new URL(iframe.attr("src"))).host;
  
    if( !host || host != window.location.host ) {
      return;
    }
    // Try to get element
    if( this.options.autoResize == true ) {
      var element = iframe.contents().find('body').children().eq(0) || iframe.contents().find('body')
       || iframe.contents()[0].documentElement;
      return super.doAutoResize( element );
    }
    else if( $type(this.options.autoResize) == 'element' )
    {
      return super.doAutoResize(this.options.autoResize);
    }
  }
}
Smoothbox.Modal.Iframe = Iframe;
class Inline extends Modal{
  Extends = Smoothbox.Modal
  element = false;
  cloneElement = false;
  load(spec)
  {
    if( this.content )
    {
      return;
    }
    super.load();
    this.content = scriptJquery.crtEle('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height
    });
    this.content.appendTo(this.window);
    this.cloneElement = scriptJquery(spec.element);
    scriptJquery(this.cloneElement).appendTo(this.content);
    // scriptJquery(this.content).append(spec);
        
    this.hideLoading();
   this.showWindow();
    this.onLoad();
  }
  setOptions(options)
  {
    this.element = scriptJquery(options.element);
    this.parent(options);
  }
  doAutoResize()
  {
    super.doAutoResize(this.cloneElement);
  } 
}
Smoothbox.Modal.Inline = Inline;
class Modal_String extends Modal{
  constructor(options){
    super(options);
    this.load();
  }
  load()
  {
    if( this.content )
    {
      return;
    }
    super.load();
    this.content = scriptJquery.crtEle('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height,
    }).html('<div>' + this.options.bodyText + '</div>');
    this.content.appendTo(this.window);
    
    this.hideLoading();
    this.showWindow();
    this.onLoad();
  }
  doAutoResize()
  {
    if( !this.options.autoResize )
    {
      return;
    }
    var bind = this;
    var element = bind.content.children().eq(0);
    return super.doAutoResize( element );
  } 
}
Smoothbox.Modal.String = Modal_String;

window.addEventListener('DOMContentLoaded', function()
{
  Smoothbox.bind();
})

window.addEventListener('load', function()
{
  Smoothbox.bind();
})
})();

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
/* $Id: core.js 9984 2013-03-20 00:00:04Z john $ */

function eventWidgetRequestSend(action, event_id, notification_id, rsvp) {
  var url;
  if( action == 'accept' )
  {
    url = en4.core.baseUrl + 'event/member/accept';
  }
  else if( action == 'reject' )
  {
    url = en4.core.baseUrl + 'event/member/reject';
  }
  else
  {
    return false;
  }

  (scriptJquery.ajax({
    url : url,
    dataType : 'json',
    method : 'post',
    data : {
      event_id : event_id,
      format : 'json',
      rsvp : rsvp
      //'token' : '<?php //echo $this->token() ?>'
    },
    success : function(responseJSON)
    {
      if( !responseJSON.status ) {
        document.getElementById('notifications_' + notification_id).innerHTML = '<div class="request_success">' + responseJSON.error + '</div>';
      } else {
        document.getElementById('notifications_' + notification_id).innerHTML = '<div class="request_success">' + responseJSON.message + '</div>';
      }
    }
  }));
}
