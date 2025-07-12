/* $Id:composer.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

var activityDesignOne = false;
(function() { // START NAMESPACE

Composer = function(element, options){

  this.elements = {};

  this.plugins = {};

  this.options = {
    lang : {},
    overText : true,
    allowEmptyWithoutAttachment : false,
    allowEmptyWithAttachment : true,
    hideSubmitOnBlur : false,
    submitElement : false,
    useContentEditable : true
  };

  this.initialize = function(element, options) {
    this.options = scriptJquery.extend(this.options,options);
    this.elements = new Hash(this.elements);
    this.plugins = new Hash(this.plugins);
    
    this.elements.textarea = scriptJquery("#"+element);
    this.elements.textarea.data('Composer');

    this.attach();
    this.getTray();
    this.getMenu();

    this.pluginReady = false;

    this.getForm().on('submit', function(e) {
      var activatedPlugin = this.getActivePlugin();
      if(activatedPlugin)
        var pluginName = activatedPlugin.getName();
      else
        var pluginName = '';

     //feeling work
      if(pluginName != 'buysell' && pluginName != 'quote' && pluginName != 'prayer' && pluginName != 'wishe' && pluginName != 'thought' && pluginName != 'text' && !scriptJquery('#image_id').val() && !scriptJquery('#reaction_id').val() && !scriptJquery('#tag_location').val() && !scriptJquery('#feeling_activity').val() && !scriptJquery('#feedbgid').val()){
        
        if(typeof musicfeedupload != 'undefined' && musicfeedupload) {
          return;
        }
        if( this.pluginReady ) {
          if( !this.options.allowEmptyWithAttachment && this.getContent() == '' ) {
            e.preventDefault();
             scriptJquery('.activity_post_box').addClass('_blank');

             //scriptJquery('#activity-form').removeClass('feed_background_image');
             scriptJquery('.activity_post_box').css('background-image', 'none');
            return;
          }
        } else {
          if( !this.options.allowEmptyWithoutAttachment && this.getContent() == '' ) {
            e.preventDefault();
             scriptJquery('.activity_post_box').addClass('_blank');

             //scriptJquery('#activity-form').removeClass('feed_background_image');
             scriptJquery('.activity_post_box').css('background-image', 'none');
            return;
          }
        }

         scriptJquery('.activity_post_box').removeClass('_blank');
      }
      this.saveContent();
    }.bind(this));
  };
  this.updateComposer= function(e){
    scriptJquery("#activity_body").mentionsInput("update");
  };
  this.getMenu = function() {
    if( !$type(this.elements.menu) ) {
      
      try {
        this.elements.menu = scriptJquery("#"+this.options.menuElement);
      } catch(err){  console.log(err); }
      if( !$type(this.elements.menu) ) {
        this.elements.menu = scriptJquery.crtEle('div',{
          'id' : 'compose-menu',
          'class' : 'compose-menu'
        }).insertAfter(this.getForm());
      }
    }
    return this.elements.menu;
  };

  this.getTray = function() {
    if( !$type(this.elements.tray) ) {
      try {
        this.elements.tray = scriptJquery(this.options.trayElement);
      } catch(err){  console.log(err); }

      if( !$type(this.elements.tray) || !this.elements.tray.length ) {
        this.elements.tray =  scriptJquery.crtEle('div',{
          'id' : 'compose-tray',
          'class' : 'compose-tray',
          
        }).insertAfter('#composer-tray-container').hide();
      }
    }
    return this.elements.tray;
  }

  this.getInputArea = function() {    
    if( !$type(this.elements.inputarea) ) {
      var form = this.elements.textarea.closest('form');
      this.elements.inputarea = scriptJquery.crtEle('div', {
        'class':'fileupload-cnt',
      }).css({
          'display' : 'none',
      }).appendTo(form);
    }
    return this.elements.inputarea;
  };

  this.getForm = function() {
    return this.elements.textarea.closest('form');
  }

 this.c= function(e){
   scriptJquery("#activity_body").mentionsInput("update");
 };

  // Editor

  this.attach = function() {
   
    // Modify textarea
    this.elements.textarea.addClass('compose-textarea').css('display', 'none');

    // Create container
    this.elements.container = scriptJquery.crtEle('div', {
      'id' : 'compose-container',
      'class' : 'compose-container',
      
    });
    this.elements.textarea.wrap(this.elements.container);


    // Create body
    var supportsContentEditable = this._supportsContentEditable();

    if( supportsContentEditable ) {
      this.elements.body = scriptJquery.crtEle('div', {
        'class' : 'compose-content',
        'styles' : {
          'display' : 'block'
        },
        'events' : {
          'keypress' : function(event) {
            if( event.key == 'a' && event.control ) {
              // FF only
              // if( Browser.Engine.gecko ) {
              //   fix_gecko_select_all_contenteditable_bug(this, event);
              // }
            }
          }
        }
      }).insertBefore(this.elements.textarea);
    } else {
      this.elements.body = this.elements.textarea;
      var parentThis = this;
      scriptJquery(this.elements.body).bind('input', function(e) {
        parentThis.checkPostLength(e);
      });
    }

    // Attach blur event
    var self = this;
    this.elements.body.on('blur', function(e) {
      var curVal;
      if( supportsContentEditable ) {
        curVal = scriptJquery(this).html().replace(/\s/, '').replace(/<[^<>]+?>/ig, '');
      } else {
        curVal = scriptJquery(this).html().replace(/\s/, '').replace(/<[^<>]+?>/ig, '')
      }
      if( '' == curVal ) {
          if( supportsContentEditable ) {
            scriptJquery(this).html('<br />');
          } else {
            scriptJquery(this).html('');
          }
        
        if( self.options.hideSubmitOnBlur ) {
          (function() {
            if( !self.hasActivePlugin() ) {
              self.getMenu().css('display', 'none');
            }
          }).delay(250);
        }
      }
    });

    if( self.options.hideSubmitOnBlur ) {
      this.getMenu().css('display', 'none');
      this.elements.body.addEvent('focus', function(e) {
        self.getMenu().css('display', '');
      });
    }

    if( supportsContentEditable ) {
      this.elements.body.contentEditable = true;
      this.elements.body.designMode = 'On';

      ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown','Paste'].each(function(eventName) {
        var method = (this['editor' + eventName] || function(){}).bind(this);
        this.elements.body.addEvent(eventName.toLowerCase(), method);
      }.bind(this));

      this.setContent(this.elements.textarea.value);

      this.selection = new Composer.Selection(this.elements.body);
    } else {
      this.elements.textarea.css('display', '');
    }

    if( this.options.overText && supportsContentEditable ) {
      new Composer.OverText(this.elements.body, $merge({
        textOverride : this._lang('Post Something...'),
        poll : true,
        isPlainText : !supportsContentEditable,
        positionOptions: {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      }, this.options.overTextOptions));
    }
    
    if(typeof enabledShedulepost != "undefined" && enabledShedulepost){
      this.elements.schedule = scriptJquery.crtEle('span', {
        'class' : 'composer_schedulepost_toggle activity_tooltip',
        'href'  : 'javascript:void(0);',
        'id' : 'activity_shedulepost',
        'title' : en4.core.language.translate("Schedule Post"),
      });
      this.elements.schedule.appendTo(scriptJquery('#compose-menu'));
    }

    //this.fireEvent('attach', this);


       isonCommentBox = false;
       if(!scriptJquery('#activity_body').attr('id'))
        scriptJquery('#activity_body').attr('id',new Date().getTime());

       var data = scriptJquery('#activity_body').val();
       //var data = composeInstance.getContent();

      if(!scriptJquery('#activity_body').val() || isOnEditField || scriptJquery('#hashtagtext').val()){
        if(!scriptJquery('#activity_body').val() )
          EditFieldValue = '';
          scriptJquery('#activity_body').mentionsInput({
              onDataRequest:function (mode, query, callback) {
               scriptJquery.getJSON('activity/ajax/friends/query/'+query, function(responseData) {
                responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
                callback.call('#activity_body', responseData);
              });
            },
            //defaultValue: EditFieldValue,
            onCaret: true
          });
      }

      if(data){
         getDataMentionEdit('#activity_body',data);
      }

      if(!scriptJquery('#activity_body').parent().hasClass('typehead')){
        scriptJquery('#activity_body').hashtags();
      }
      setTimeout(function(){ scriptJquery('#activity_body').mentionsInput("update"); }, 1000);

      try{
        AttachEventListerSE("click",".comment_emotion_container_inner .emoji_contents  ul > li > a",this.updateComposer.bind(this));
      }catch(err){
        console.log(err);
      }
  };

  this.setCaretPos = function(pos) {
    this.lastCaretPos = pos;
    var index = 0, range = document.createRange(), body = this.elements.body[0];
    range.setStart(body, 0);
    range.collapse(true);
    var nodeArray = [body], node, isStart = false, stop = false;

    while (!stop && (node = nodeArray.pop())) {
      if (node.nodeType === 3) {
        var nextIndex = index + node.length;
        if (!isStart && pos >= index && pos <= nextIndex) {
          range.setStart(node, pos - index);
          isStart = true;
        } else if (isStart && pos >= index && pos <= nextIndex) {
          range.setEnd(node, pos - index);
          stop = true;
        }
        index = nextIndex;
      } else {
        var i = node.childNodes.length;
        while (i--) {
          nodeArray.push(node.childNodes[i]);
        }
      }
    }
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);

  }
  this.checkPostLength = function(e) {
    var content = this.getContent();
    content = content.replace(/&nbsp;/g, ' ');
    content = content.replace(/&amp;/g, '&'); 
    content = content.replace(/&lt;/g, '<'); 
    content = content.replace(/&gt;/g, '>');

    if(this.options.postLength && this.options.postLength < content.length){
      content = content.substr(0,this.options.postLength);
      this.setContent(content);
      this.setCaretPos(content.length);
    }
    if(this.options.postLength){
      scriptJquery(".compose-content-counter").css("display","inline-block");
      scriptJquery(".compose-content-counter").html(this.options.postLength-content.length);
      return;
    }
  }
  this.detach = function() {
    this.saveContent();
    this.textarea.css('display', '').removeClass('compose-textarea').insertBefore(this.container);
    this.container.dispose();
    //this.fireEvent('detach', this);
    return this;
  };

  this.focus = function(){
    // needs the delay to get focus working
    (function(){
      this.elements.body.focus();
      //this.fireEvent('focus', this);
    }).bind(this).delay(10);
    return this;
  };



  // Content

  this.getContent = function(){
    return scriptJquery(this.elements.textarea).val();
  };

  this.setContent = function(newContent) {
    //scriptJquery('#activity_body_emojis').val(newContent);
    scriptJquery(this.elements.textarea).val(newContent);
    this.checkPostLength();
    return this;
  };


  this.saveContent = function(){
    if( this._supportsContentEditable() ) {
      scriptJquery(this.elements.textarea).val( this.getContent());
    }
    return this;
  };

  this.cleanup = function(html) {
    // @todo
    return html
      .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
      .replace(/<[^<>]+?>/ig, ' ')
      .replace(/(\r\n?|\n){3,}/ig, "\n\n")
      .trim();
  };



  // Plugins

  this.addPlugin = function(plugin) {
    var key = plugin.getName();
    this.plugins.set(key, plugin);
    plugin.setComposer(this);
    return this;
  };

  this.addPlugins = function(plugins) {
    plugins.each(function(plugin) {
      this.addPlugin(plugin);
    }.bind(this));
  };

  this.getPlugin = function(name) {
    return this.plugins.get(name);
  };

  this.activate = function(name) {
    this.deactivate();
    this.getMenu().css();
    this.plugins.get(name).activate();
  };

  this.deactivate = function() {
    
    Object.entries(this.plugins).forEach(function([key,plugin]) {
      plugin.deactivate();
      scriptJquery('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
    scriptJquery('#fancyalbumuploadfileids').val('');
    scriptJquery('#reaction_id').val('');
    scriptJquery('.fileupload-cnt').html('');
    this.getTray().empty();
  };

  this.signalPluginReady = function(state) {
    this.pluginReady = state;
  };
  this.getActivePlugin = function() {

    var activeplugin = false;
    Object.entries(this.plugins).forEach(function([key,plugin]) {
      if(plugin.active)
        activeplugin = plugin;
    });
    return activeplugin;
  };
  this.hasActivePlugin = function() {
    var active = false;
    Object.entries(this.plugins).forEach(function([key,plugin]) {
      active = active || plugin.active;
    });
    return active;
  };



  // Key events

  this.editorMouseUp = function(e){
    //this.fireEvent('editorMouseUp', e);
  };

  this.editorMouseDown = function(e){
    //this.fireEvent('editorMouseDown', e);
  };

  this.editorContextMenu = function(e){
    //this.fireEvent('editorContextMenu', e);
  };

  this.editorClick = function(e){
    // make images selectable and draggable in Safari
    // if (Browser.Engine.webkit){
    //   var el = e.target;
    //   if (el.get('tag') == 'img'){
    //     this.selection.selectNode(el);
    //   }
    // }

    //this.fireEvent('editorClick', e);
  };

  this.editorDoubleClick = function(e){
    //this.fireEvent('editorDoubleClick', e);
  };

  this.editorKeyPress = function(e){
    this.keyListener(e);
    //this.fireEvent('editorKeyPress', e);
  };

  this.editorKeyUp = function(e){
    //this.fireEvent('editorKeyUp', e);
      setTimeout(function () {
        linkDetection();
      }, 0);
			var str = this.getContent();
			//scriptJquery(this).parent().parent().find(".highlighter").css("width",$(this).css("width"));
			str = str.replace(/\n/g, '<br>');
			if(!str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?#([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?@([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?#([\u0600-\u06FF]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?@([\u0600-\u06FF]+)/g)) {
        if(!str.match(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#/g)) { //arabic support
					str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">#$1</span>');
				}else{
					str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">#$1</span>');
				}
				if(!str.match(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))@/g)) {
					//str = str.replace(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">@$1</span>');
				}else{
					//str = str.replace(/@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))@(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<span class="hashtag">@$1</span>');
				}
			}
			this.setContent(str);

  }
  this.editorPaste = function(e) {
    //this.fireEvent('editorPaste', e);
    setTimeout(function () {
      linkDetection();
    }, 0);
  };
  this.editorKeyDown = function(e){
    //this.fireEvent('editorKeyDown', e);
  };

  this.keyListener = function(e){

  };
  this._lang = function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }

      if( arguments.length <= 1 ) {
        return string;
      }

      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }

      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  },

  this._supportsContentEditable = function() {
    return false;
  }
  this.initialize(element,options);
};

Composer.Selection = function(win){
  this.initialize = function(win){
    this.win = win;
  }

  this.getSelection = function(){
    //this.win.focus();
    return window.getSelection();
  }

  this.getRange = function(){
    var s = this.getSelection();

    if (!s) return null;

    try {
      return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
    } catch(e) {
      // IE bug when used in frameset
      return document.body.createTextRange();
    }
  }

  this.setRange = function(range){
    if (range.select){
      try{
        (function(){
          range.select();
        });
      } catch(err){ console.log(err); }
    } else {
      var s = this.getSelection();
      if (s.addRange){
        s.removeAllRanges();
        s.addRange(range);
      }
    }
  }
  this.selectNode = function(node, collapse){
    var r = this.getRange();
    var s = this.getSelection();
    if (r.moveToElementText){
      try{
        (function(){
          r.moveToElementText(node);
          r.select();
        });
      } catch(err){ console.log(err); }
    } else if (s.addRange){
      collapse ? r.selectNodeContents(node) : r.selectNode(node);
      s.removeAllRanges();
      s.addRange(r);
    } else {
      s.setBaseAndExtent(node, 0, node, 1);
    }

    return node;
  }

  this.isCollapsed = function(){
    var r = this.getRange();
    if (r.item) return false;
    return r.boundingWidth == 0 || this.getSelection().isCollapsed;
  }

  this.collapse = function(toStart){
    var r = this.getRange();
    var s = this.getSelection();
    if (r.select){
      r.collapse(toStart);
      r.select();
    } else {
      toStart ? s.collapseToStart() : s.collapseToEnd();
    }
  }
  this.getContent = function(){
    var r = this.getRange();
    var body = scriptJquery.crtEle('body',{});
    if (this.isCollapsed()) return '';
    if (r.cloneContents){
      body.appendChild(r.cloneContents());
    } else if ($defined(r.item) || $defined(r.htmlText)){
      body.html(r.item ? r.item(0).outerHTML : r.htmlText);
    } else {
      body.html(r.toString());
    }
    var content = body.html();
    return content;
  }
  this.getText = function(){
    var r = this.getRange();
    var s = this.getSelection();
    return this.isCollapsed() ? '' : r.text || s.toString();
  }
  this.getNode = function(){
    var r = this.getRange();
    if (!Browser.Engine.trident){
      var el = null;
      if (r){
        el = r.commonAncestorContainer;
        // Handle selection a image or other control like element such as anchors
        if (!r.collapsed)
          if (r.startContainer == r.endContainer)
            if (r.startOffset - r.endOffset < 2)
              if (r.startContainer.hasChildNodes())
                el = r.startContainer.childNodes[r.startOffset];

        while ($type(el) != 'element') el = el.parentNode;
      }
      return scriptJquery(el);
    }
    return scriptJquery(r.item ? r.item(0) : r.parent());
  }
  this.insertContent = function(content){
    var r = this.getRange();
    if (r.insertNode){
      r.deleteContents();
      r.insertNode(r.createContextualFragment(content));
    } else {
      // Handle text and control range
      (r.pasteHTML) ? r.pasteHTML(content) : r.item(0).outerHTML = content;
    }
  }
  this.initialize(win);
};


class ComposerOverText extends OverText{
  //Extends : OverText,
  constructor(element, options){
    super(element, options);
  }
  test() {
    if( !$type(this.options.isPlainText) || !this.options.isPlainText) {
      return !this.element.html().replace(/\s+/, '').replace(/<br.*?>/, '');
    } else {
      return this.parent();
    }
  }
  hide(suppressFocus, force){
    if (this.text && (this.text.is(":visible") && (!this.element.prop('disabled') || force))){
      this.text.hide();
      //this.fireEvent('textHide', [this.text, this.element]);
      try {
        this.element.trigger('focus');
        this.element.focus();
      } catch(e){} //IE barfs if you call focus on hidden elements

      this.pollingPaused = true;
    }
    return this;
  }
}

Composer.Plugin = {};

Composer.Plugin.Interface = function(options){

this.name = 'interface';
  this.active = false;
  this.composer = false;
  this.options = {
    loadingImage : en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'
  };
  this.elements = {};
  this.persistentElements = ['activator', 'loadingImage','aActivator','sactivator'];
  this.params = {};
  this.initialize = function(options) {
    this.params = new Hash();
    this.elements = new Hash();
    this.reset();
    this.options = scriptJquery.extend(this.options,options);
  }
  this.getName = function() {
    return this.name;
  }
  this.setComposer = function(composer) {
    this.composer = composer;
    this.attach();
    return this;
  }
  this.getComposer = function() {
    if( !this.composer ) throw "No composer defined";
    return this.composer;
  }
  this.attach = function() {
    this.reset();
  }
  this.detach = function() {
    this.reset();
    if( this.elements.activator ) {
      this.elements.activator.remove();
      this.elements.erase('menu');
    }
  }
  this.reset = function() {
    Object.entries(this.elements).forEach(function([key,element]) {
      if(!this.persistentElements.includes(key)) {
        if(scriptJquery(element).length)
          scriptJquery(element).remove();
        this.elements.erase(key);
      }
    }.bind(this));
    this.params = new Hash();
    this.elements = new Hash();
  }

  this.activate = function() {
    if( this.active ) return;
    
    scriptJquery("#feedbg_main_continer").hide();
    //Feed Background image work
    if(document.getElementById('feedbgid') && document.getElementById('feedbgid').value != 0) {
      scriptJquery('#feedbgid_isphoto').val(0);
      scriptJquery('#feedbgid').val(0);
      scriptJquery('.activity_post_box').css('background-image', 'none');
      scriptJquery('#activity-form').removeClass('feed_background_image');
      scriptJquery('#feedbg_main_continer').css('display','none');
      scriptJquery('#hideshowfeedbgcont').css('display','none');
    }
    //Feed Background image work

    this.getComposer().getTray().empty();
    scriptJquery('#fancyalbumuploadfileids').val('');
    scriptJquery('#reaction_id').val('');
    scriptJquery('.fileupload-cnt').html('');
    Object.entries(composeInstance.plugins).forEach(function([key,plugin]) {
      plugin.active = false;
      scriptJquery('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
    scriptJquery('#compose-'+this.getName()+'-activator').parent().addClass('active');
    this.active = true;
    this.reset();
    this.getComposer().getTray().css('display', '');

    this.getComposer().getMenu().css('border', 'none');

    this.getComposer().getMenu().find('.compose-activator').each(function(e) {
      scriptJquery(this).css('display', 'none');
    });

    switch($type(this.options.loadingImage)) {
      case 'object':
        break;
      case 'string':
        this.elements.loadingImage = scriptJquery.crtEle('img', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image',
          'src' : this.options.loadingImage
        });
        break;
      default:
        this.elements.loadingImage = scriptJquery.crtEle('img', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image',
          'src' : 'application/modules/Core/externals/images/loading.gif',
        });
        break;
    }

  }

  this.deactivate = function() {
    if( !this.active ) return;
    this.active = false;

    this.reset();
    this.getComposer().getTray().css('display', 'none');
    this.getComposer().getMenu().css('display', '');
    var submitButtonEl = scriptJquery(this.getComposer().options.submitElement);
    if( submitButtonEl.length) {
      submitButtonEl.css('display', '');
    }
    this.getComposer().getMenu().find('.compose-activator').each(function(e) {
      scriptJquery(this).css('display', '');
    });

    this.getComposer().getMenu().attr('style', '');
    this.getComposer().signalPluginReady(false);
    scriptJquery('#fancyalbumuploadfileids').val('');
    scriptJquery('#reaction_id').val('');
    scriptJquery('.fileupload-cnt').html('');

    //Feed Background Image Work
    if(document.getElementById('feedbgid')) {
      document.getElementById('hideshowfeedbgcont').style.display = 'block';
      scriptJquery('#feedbg_main_continer').css('display','block');
    }
    // scriptJquery('#compose-menu').next().html('');
  }

  this.ready = function() {
    this.getComposer().signalPluginReady(true);
    this.getComposer().getMenu().css('display', '');

    var submitEl = document.getElementById(this.getComposer().options.submitElement);
    if( submitEl ) {
      submitEl.style.display = "";
    }
  },


  // Utility

  this.makeActivator = function() {
    if( !this.elements.activator ) {
      var moreTab = false;
      var spanInsertBefore = 'activity_post_media_options_before';
      if(activityDesign == 1) {
        if(!activityDesignOne){
          var content = '';
          if(scriptJquery('#feedbg_main_continer').length > 0) {
            content = scriptJquery('#feedbg_main_continer')[0].outerHTML;
            scriptJquery('#feedbg_main_continer').remove();
            scriptJquery(content).insertAfter(scriptJquery("#activity_post_box_status"));
          }
          let newcontent = scriptJquery("#activity-menu")[0].outerHTML;
          scriptJquery("#activity-menu").remove();
          scriptJquery(newcontent).insertBefore(scriptJquery("#composer-tray-container"));
          
        }
        activityDesignOne = true;
        this.elements.activator = scriptJquery.crtEle('span', {
          'class': 'activity_post_tool_i tool_i_'+this.getName(),
        });
        
        scriptJquery('#activity-menu').append(this.elements.activator);
        this.elements.aActivator = scriptJquery.crtEle('a', {
          'id' : 'compose-' + this.getName() + '-activator',
          'class' : 'activity_tooltip',
          'data-bs-toggle' : 'tooltip',
          'href' : 'javascript:;',
          'title' : this._lang(this.options.title),
        }).appendTo(this.elements.activator).click((e) => {
          this.activate(this);
        });

        if (this.getName() == 'buysell'){
          scriptJquery('<i><svg viewBox="0 0 24 24"><path d="M23,19H21V17a1,1,0,0,0-2,0v2H17a1,1,0,0,0,0,2h2v2a1,1,0,0,0,2,0V21h2a1,1,0,0,0,0-2Z"/><path d="M21,6H18A6,6,0,0,0,6,6H3A3,3,0,0,0,0,9V19a5.006,5.006,0,0,0,5,5h9a1,1,0,0,0,0-2H5a3,3,0,0,1-3-3V9A1,1,0,0,1,3,8H6v2a1,1,0,0,0,2,0V8h8v2a1,1,0,0,0,2,0V8h3a1,1,0,0,1,1,1v5a1,1,0,0,0,2,0V9A3,3,0,0,0,21,6ZM8,6a4,4,0,0,1,8,0Z"/></svg></i>').appendTo(this.elements.aActivator);
        }else if (this.getName() == 'intopenaiImage'){
          scriptJquery('<i><svg viewBox="0 0 24 24"><path d="m12,21c0,.553-.448,1-1,1h-6c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h12c2.757,0,5,2.243,5,5v6c0,.553-.448,1-1,1s-1-.447-1-1v-6c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v6.959l2.808-2.808c1.532-1.533,4.025-1.533,5.558,0l5.341,5.341c.391.391.391,1.023,0,1.414-.195.195-.451.293-.707.293s-.512-.098-.707-.293l-5.341-5.341c-.752-.751-1.976-.752-2.73,0l-4.222,4.222v2.213c0,1.654,1.346,3,3,3h6c.552,0,1,.447,1,1ZM15,3.5c1.654,0,3,1.346,3,3s-1.346,3-3,3-3-1.346-3-3,1.346-3,3-3Zm0,2c-.551,0-1,.448-1,1s.449,1,1,1,1-.448,1-1-.449-1-1-1Zm8,12.5h-3v-3c0-.553-.448-1-1-1s-1,.447-1,1v3h-3c-.552,0-1,.447-1,1s.448,1,1,1h3v3c0,.553.448,1,1,1s1-.447,1-1v-3h3c.552,0,1-.447,1-1s-.448-1-1-1Z"></svg></i>').appendTo(this.elements.aActivator);
        } else if (this.getName() == 'intopenaiDescription'){
          scriptJquery('<i><svg viewBox="0 0 24 24" width="512" height="512"><path d="M19,2H5C2.24,2,0,4.24,0,7v10c0,2.76,2.24,5,5,5h14c2.76,0,5-2.24,5-5V7c0-2.76-2.24-5-5-5ZM5,4h14c1.65,0,3,1.35,3,3H2c0-1.65,1.35-3,3-3Zm14,16H5c-1.65,0-3-1.35-3-3V9H22v8c0,1.65-1.35,3-3,3ZM10,12c0,.55-.45,1-1,1h-1v4c0,.55-.45,1-1,1s-1-.45-1-1v-4h-1c-.55,0-1-.45-1-1s.45-1,1-1h4c.55,0,1,.45,1,1Zm10,0c0,.55-.45,1-1,1h-6c-.55,0-1-.45-1-1s.45-1,1-1h6c.55,0,1,.45,1,1Zm0,4c0,.55-.45,1-1,1h-6c-.55,0-1-.45-1-1s.45-1,1-1h6c.55,0,1,.45,1,1Z"/></svg></i>').appendTo(this.elements.aActivator);
          
        }
        
        if(!scriptJquery("#activity_post_box_status").find("#composer-close-design1").length){
          var closeComposer = scriptJquery.crtEle('a', {
            'id' : 'composer-close-design1',
            'class' : 'activity_tooltip font_color',
            'data-bs-toggle' : 'tooltip',
            'href' : 'javascript:;',
            'style' : 'display:none;',
            'title' : this._lang("close"),
          });
          scriptJquery("#activity_post_box_status").append(closeComposer);

          scriptJquery(closeComposer).click((e) => {
            this.closeOption(this)
          });;
        }
        scriptJquery('#activity-menu').find('.executed').removeClass('executed');
      }else if(activityDesign == 2){
        
        if(scriptJquery("body").attr("id") != "global_page_messages-messages-compose" && scriptJquery("body").attr("id") != "global_page_messages-messages-view")
        //scriptJquery(".activity_post_media_options").hide();
        
        var displayCI  = 'block';
        if(counterLoopComposerItem == 4) {
          var html = scriptJquery('<span class="activity_post_media_options_icon tool_i_more" style="display:flex;"><a href="javascript:void(0);" title="More" class="activity_tooltip"><i></i></a></span>').insertBefore(scriptJquery('#activity_post_media_options_before'));
        }
        if(counterLoopComposerItem > 3)
           displayCI = 'none';
        counterLoopComposerItem++;

        this.elements.activator = scriptJquery.crtEle('span', {
          'html' :  '',
          'style': 'display:'+displayCI,
          'class': 'activity_post_media_options_icon tool_i_'+this.getName(),
        });

        //Album Work
        try {
          this.elements.aActivator = scriptJquery.crtEle('a', {
              'id' : 'compose-' + this.getName() + '-activator',
              'class' : 'activity_tooltip',
              'href' : 'javascript:;',
              'title' : this._lang(this.options.title),
          }).appendTo(this.elements.activator).click((e) => {
            this.activate(this);
          });
        } catch(err){
          console.log(err);

        }
        if (this.getName() == 'buysell'){
          scriptJquery('<i><svg viewBox="0 0 24 24"><path d="M23,19H21V17a1,1,0,0,0-2,0v2H17a1,1,0,0,0,0,2h2v2a1,1,0,0,0,2,0V21h2a1,1,0,0,0,0-2Z"/><path d="M21,6H18A6,6,0,0,0,6,6H3A3,3,0,0,0,0,9V19a5.006,5.006,0,0,0,5,5h9a1,1,0,0,0,0-2H5a3,3,0,0,1-3-3V9A1,1,0,0,1,3,8H6v2a1,1,0,0,0,2,0V8h8v2a1,1,0,0,0,2,0V8h3a1,1,0,0,1,1,1v5a1,1,0,0,0,2,0V9A3,3,0,0,0,21,6ZM8,6a4,4,0,0,1,8,0Z"/></svg></i>').appendTo(this.elements.aActivator);
        } else if (this.getName() == 'intopenaiImage'){
          scriptJquery('<i><svg viewBox="0 0 24 24"><path d="m12,21c0,.553-.448,1-1,1h-6c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h12c2.757,0,5,2.243,5,5v6c0,.553-.448,1-1,1s-1-.447-1-1v-6c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v6.959l2.808-2.808c1.532-1.533,4.025-1.533,5.558,0l5.341,5.341c.391.391.391,1.023,0,1.414-.195.195-.451.293-.707.293s-.512-.098-.707-.293l-5.341-5.341c-.752-.751-1.976-.752-2.73,0l-4.222,4.222v2.213c0,1.654,1.346,3,3,3h6c.552,0,1,.447,1,1ZM15,3.5c1.654,0,3,1.346,3,3s-1.346,3-3,3-3-1.346-3-3,1.346-3,3-3Zm0,2c-.551,0-1,.448-1,1s.449,1,1,1,1-.448,1-1-.449-1-1-1Zm8,12.5h-3v-3c0-.553-.448-1-1-1s-1,.447-1,1v3h-3c-.552,0-1,.447-1,1s.448,1,1,1h3v3c0,.553.448,1,1,1s1-.447,1-1v-3h3c.552,0,1-.447,1-1s-.448-1-1-1Z"></svg></i>').appendTo(this.elements.aActivator);
        } else if (this.getName() == 'intopenaiDescription'){
          scriptJquery('<i><svg viewBox="0 0 24 24" width="512" height="512"><path d="M19,2H5C2.24,2,0,4.24,0,7v10c0,2.76,2.24,5,5,5h14c2.76,0,5-2.24,5-5V7c0-2.76-2.24-5-5-5ZM5,4h14c1.65,0,3,1.35,3,3H2c0-1.65,1.35-3,3-3Zm14,16H5c-1.65,0-3-1.35-3-3V9H22v8c0,1.65-1.35,3-3,3ZM10,12c0,.55-.45,1-1,1h-1v4c0,.55-.45,1-1,1s-1-.45-1-1v-4h-1c-.55,0-1-.45-1-1s.45-1,1-1h4c.55,0,1,.45,1,1Zm10,0c0,.55-.45,1-1,1h-6c-.55,0-1-.45-1-1s.45-1,1-1h6c.55,0,1,.45,1,1Zm0,4c0,.55-.45,1-1,1h-6c-.55,0-1-.45-1-1s.45-1,1-1h6c.55,0,1,.45,1,1Z"/></svg></i>').appendTo(this.elements.aActivator);
          
        }


        
        this.elements.sactivator = scriptJquery.crtEle('span', {}).html(this._lang(this.options.title)).appendTo(this.elements.aActivator);
      
        this.elements.activator.insertBefore(scriptJquery('#activity_post_media_options_before'));
      }
    }
  };

  this.makeMenu = function() {
    if( !this.elements.menu ) {
      var tray = this.getComposer().getTray();

      this.elements.menu = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-menu',
        'class' : 'compose-menu'
      }).appendTo(tray);

      this.elements.menuTitle = scriptJquery.crtEle('span', {
				'class' : 'compose-menu-head',
      }).html(this._lang(this.options.title)).appendTo(this.elements.menu);

      this.elements.menuClose = scriptJquery.crtEle('a', {
				'class' : 'compose-menu-close fas fa-times',
        'href' : 'javascript:void(0);',
        'title' : this._lang('cancel'),
      }).appendTo(this.elements.menuTitle).click(function(e) {
        //e.stop();
        this.getComposer().deactivate();
        scriptJquery('#compose-tray').hide();
      }.bind(this));

      this.elements.menuTitle.append('');

    }
  }
  this.closeOption = function(){   
    if(scriptJquery("#feedbg_main_continer").length){
      feedbgimage('defaultimage');
    }
    scriptJquery("#feedbg_main_continer").hide();
    scriptJquery("#composer-close-design1").hide();
  }
  this.makeBody = function() {
    if( !this.elements.body ) {
      var tray = this.getComposer().getTray();
      this.elements.body = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-body',
        'class' : 'compose-body'
      }).appendTo(tray);
    }
  }

  this.makeLoading = function(action) {
    if( !this.elements.loading ) {
      if( action == 'empty' ) {
        this.elements.body.empty();
      } else if( action == 'hide' ) {
        this.elements.body.getChildren().each(function(element){ element.css('display', 'none')});
      } else if( action == 'invisible' ) {
        this.elements.body.getChildren().each(function(element){ element.css('height', '0px').css('visibility', 'hidden')});
      }

      this.elements.loading = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-loading',
        'class' : 'compose-loading'
      }).appendTo(this.elements.body);
      var image = this.elements.loadingImage || (scriptJquery.crtEle('img', {
        'id' : 'compose-' + this.getName() + '-loading-image',
        'class' : 'compose-loading-image'
      }));
      image.appendTo(this.elements.loading);
    }
  }

  this.makeError = function(message, action) {
    if( !$type(action) ) action = 'empty';
    message = message || 'An error has occurred';
    message = this._lang(message);
    this.elements.error = scriptJquery.crtEle('div', {
      'id' : 'compose-' + this.getName() + '-error',
      'class' : 'compose-error',
      'html' : message
    }).html(message).appendTo(this.elements.body);
  }
  this.makeFormInputs = function(data) {
    
    this.ready();
    this.getComposer().getInputArea().empty();
    var name = this.getName();
    if(name == 'link')
      name  = 'activitylink';
    data.type = name;
    Object.entries(data).forEach(function([key,value]) {
      this.setFormInputValue(key, value);
    }.bind(this));

  }

  this.setFormInputValue = function(key, value) {
    var elName = 'attachmentForm' + key.replace(/\b[a-z]/g, function(match){
      return match.toUpperCase();
    });
    if( !this.elements.has(elName) ) {
      this.elements.set(elName,scriptJquery.crtEle('input', {
        'type' : 'hidden',
        'name' : 'attachment[' + key + ']',
        'value' : value || ''
      }).appendTo(this.getComposer().getInputArea()));
    }
    this.elements.get(elName).val(value);
  }
  this._lang = function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }
      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }
      if( arguments.length <= 1 ) {
        return string;
      }
      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }
      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  }
  this.initialize(options);
};
})();


AttachEventListerSE('click',function(e) {
  var container = scriptJquery('.activity_post_container');
  var smoothbox = scriptJquery('.ajaxsmoothbox_main');
  var smoothboxIcon = scriptJquery('.ajaxsmoothbox_overlay');
  var smoothboxSE = scriptJquery('#TB_window');
  var smoothboxSEOverlay = scriptJquery('#TB_overlay');
   var notclose = scriptJquery('.notclose');
  if(scriptJquery(e.target).hasClass('notclose') || scriptJquery(e.target).closest('.ui-autocomplete').length > 0 || scriptJquery(e.target).hasClass("compose-form-submit") || scriptJquery(e.target).parent().hasClass('tag') || smoothbox.has(e.target).length || smoothbox.is(e.target) || smoothboxIcon.has(e.target).length || smoothboxIcon.is(e.target) || notclose.has(e.target).length || notclose.is(e.target) || scriptJquery(e.target).hasClass('ajaxsmoothbox_close_btn') || scriptJquery(e.target).hasClass('ajaxsmoothbox_main') || smoothboxSE.has(e.target).length || smoothboxSE.is(e.target) || smoothboxSEOverlay.has(e.target).length || smoothboxSEOverlay.is(e.target) || scriptJquery(e.target).attr('id') == 'TB_overlay' ||  scriptJquery(e.target).hasClass('close') || scriptJquery('.pac-container').has(e.target).length || scriptJquery('.pac-container').is(e.target) || scriptJquery(e.target).attr('id') == 'TB_window' || scriptJquery(e.target).prop("tagName") == 'BODY'){
    return;
  }

  if(scriptJquery(e.target).attr('id') == 'discard_post' || scriptJquery(e.target).attr('id') == 'goto_post'){
    return;
  }

  //Feed Background Image Work
  if(scriptJquery(e.target).hasClass('fa fa-angle-right') || scriptJquery(e.target).hasClass('fa fa-angle-left')){
    return;
  }
  if(scriptJquery("#composer-close-design1").is(e.target)){
    return;
  }
  if ((!container.is(e.target)
      && container.has(e.target).length === 0) || scriptJquery(e.target).hasClass('activity_post_box_close_a'))
  {
    if(scriptJquery('.activity_composer_active').length && !scriptJquery(e.target).hasClass('compose-menu-close')){
      if(scriptJquery(".modal.show").length == 0)
      checkComposerAdv();
    }
  } else {
    if(activityDesign != 2){
      scriptJquery("#composer-close-design1").show();
      scriptJquery('#feedbg_main_continer').show();
      return;
    }
    scriptJquery('.activity_post_container_wrapper').addClass('activity_composer_active');
    scriptJquery(".activity_post_box_close").show();
    scriptJquery('.activity_post_media_options').addClass('activity_post_media_options_active');
    
    scriptJquery(".activity_post_media_options span:gt(3)").show();
    scriptJquery(".activity_post_media_options").children().eq(3).hide();

    // Feed bg work
    var activatedPlugin = composeInstance.getActivePlugin();

    var textlengthBody = scriptJquery('#activity_body').val().length;

    if(document.getElementById('feedbg_main_continer') && !activatedPlugin && textlengthBody <= 120)
      scriptJquery('#feedbg_main_continer').css('display','block');
    
    if(scriptJquery('#activitylng').val()) {
      scriptJquery('#feedbg_main_continer').css('display','none');
    }
    if(activityBodyHeight){
      scriptJquery('#activity_body').height(activityBodyHeight);
    }
    scriptJquery('.activity_post_media_options').show();
    scriptJquery('#compose-menu').show();
  }
});


var activityBodyHeight = 0;
function hideStatusBoxSecond() {
  scriptJquery('.activity_post_container_wrapper').removeClass('activity_composer_active');
  scriptJquery('.activity_post_media_options').removeClass('activity_post_media_options_active');
  
  scriptJquery(".activity_post_media_options span:gt(4)").hide();
  scriptJquery(".activity_post_media_options").children().eq(3).show();
  scriptJquery(".activity_post_media_options").children().eq(2).find("span").show();
  
  scriptJquery(".activity_post_box_close").hide();
  scriptJquery('.activity_shedulepost_overlay').hide();
  scriptJquery('html').removeClass('overflow-hidden')
  scriptJquery('body').removeClass('overflow-hidden');
  //scriptJquery('.activity_post_media_options').hide();

  //Feed Background Image Work
  if(document.getElementById('feedbgid').value) {
    if(document.getElementById('feedbg_main_continer'))
    document.getElementById('feedbg_main_continer').style.display = 'none';
    scriptJquery('.activity_post_box').css('background-image', 'none');
    scriptJquery('#activity-form').removeClass('feed_background_image');
  } else {
    //Feed Background Image Work
    document.getElementById('hideshowfeedbgcont').style.display = 'block';
    scriptJquery('#feedbg_content').css('display','inline-block');
    scriptJquery('#feedbg_main_continer').css('display','none');
    scriptJquery('.activity_post_box').css('background-image', 'none');
    scriptJquery('#activity-form').removeClass('feed_background_image');
  }
  scriptJquery('.activity_post_box').removeClass('_blank');
}


function getConfirmation() {
  if(!scriptJquery('#activity_body').val())
    return;
  var retVal = confirm("Are you sure to discard this post ?");
  if( retVal == true ) {
    resetComposerBoxStatus();
    Object.entries(composeInstance.plugins).forEach(function([key,plugin]) {
      plugin.deactivate();
      scriptJquery('#compose-menu').hide();
      scriptJquery('#compose-'+plugin.getName()+'-activator').parent().removeClass('active');
    });
  }
}

AttachEventListerSE('paste','#activity_body',function(){
   setTimeout(function () {
      linkDetection();
    }, 20);
});

AttachEventListerSE('keyup','#activity_body',function(e) {
    if(e.keyCode != '32')
      return;
    setTimeout(function () {
      linkDetection();
    }, 20);
});
function updateEditVal(that,data){
    EditFieldValue = data;
    scriptJquery(that).mentionsInput("update");
}
var mentiondataarray = [];
AttachEventListerSE('keyup','#activity_body',function(){
    var data = scriptJquery(this).val();
     EditFieldValue = data;
     //scriptJquery(this).mentionsInput("update");
});
function getDataMentionEdit (that,data){
  if (scriptJquery(that).attr('data-mentions-input') === 'true') {
       updateEditVal(that, data);
  }
}
var isOnEditField = isonCommentBox = false;
AttachEventListerSE('focus','#activity_body',function(){
   isonCommentBox = false;
   if(!scriptJquery(this).attr('id'))
    scriptJquery(this).attr('id',new Date().getTime());
   var data = scriptJquery(this).val();
  if(!scriptJquery(this).val() || isOnEditField){
    if(!scriptJquery(this).val() )
      EditFieldValue = '';

    //if(userTagsEnable) {
      scriptJquery(this).mentionsInput({
          onDataRequest:function (mode, query, callback) {
           scriptJquery.getJSON('activity/ajax/friends/query/'+query, function(responseData) {
            responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
            callback.call(this, responseData);
          });
        },
        //defaultValue: EditFieldValue,
        onCaret: true
      });
    //}
  }

  if(data){
     getDataMentionEdit(this,data);
  }
  if(!scriptJquery(this).parent().hasClass('typehead')){
    scriptJquery(this).hashtags();
    scriptJquery(this).focus();
  }
  
  autosize(scriptJquery(this));

});
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
AttachEventListerSE('keydown','#activity_body',function(){
   if(scriptJquery(this).val() != '')
    scriptJquery('.activity_post_box').removeClass('_blank');

});

function checkComposerAdv(){
  hideStatusBoxSecond();
  return;
}
function linkDetection(){
  var html = scriptJquery('#activity_body').val();
  //var html = composeInstance.getContent();
    if(!html || !scriptJquery('#compose-link-activator').length || scriptJquery('#compose-tray').html())
      return false;
    var mystrings = [];
    var valid = false;
    var url = '';
    valid = this.checkUrl(html);
    if(!valid)
      return;
   var pluginlink = composeInstance.getPlugin('link');
   pluginlink.activate();
   //check for youtube video url
   var matches = valid.match(/watch\?v=([a-zA-Z0-9\-_]+)/);
   if (matches)
   {
     if(valid.indexOf('?') < 0)
      valid = valid+'?youtubevideo=1';
     else
      valid = valid+'&youtubevideo=1';
   }else if(parseVimeo(valid)){
     if(valid.indexOf('?') < 0)
      valid = valid+'?vimeovideo=1';
     else
      valid = valid+'&vimeovideo=1';
   }else if(valid.indexOf('https://soundcloud.com') >= 0){
      if(valid.indexOf('?') < 0)
        valid = valid+'?soundcloud=1';
      else
        valid = valid+'&soundcloud=1';
   }
   scriptJquery(pluginlink.elements.formInput).val(valid);
   pluginlink.doAttach();
   pluginlink.active = true;
   scriptJquery('#compose-link-form-submit').trigger('click');
}
function parseVimeo(str) {
    // embed & link: http://vimeo.com/86164897
    var re = /\/\/(?:www\.)?vimeo.com\/([0-9a-z\-_]+)/i;
    var matches = re.exec(str);
    return matches && matches[1];
}
function checkUrl(str){
   var geturl = /(((https?:\/\/)|(www\.))[^\s]+)/g;
   if(str.match(geturl)){
    var length =   str.match(geturl).length
    var urls =   str.match(geturl)

    if(length)
      return urls[0];
   }
    return '';
}

/* $Id:composer_buysell.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

Composer.Plugin.Buysell = function(options){

  this.__proto__ = new Composer.Plugin.Interface(options);

  this.name = 'buysell'

  this.options = {
    title : en4.core.language.translate("Sell Something"),
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    debug : false
  }

  this.initialize = function(options) {
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  }

  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();
    return this;
  }

  this.detach = function() {
    this.__proto__.detach.call(this);
    if( this.interval ) $clear(this.interval);
    return this;
  }

  this.activate = function() {
    if( this.active ) return;
    this.__proto__.activate.call(this);

    this.makeMenu();
    this.makeBody();
    
    var title = '<div class="activity_sell_composer"><div class="activity_sell_composer_title"><input type="text" id="buysell-title" placeholder="'+ en4.core.language.translate("What are you selling?")+'" name="buysell-title"><span id="buysell-title-count" class="font_color_light">100</span></div>';
    var wheretobuy = '<div class="activity_sell_composer_title"><input type="text" id="buy-url" placeholder="'+ en4.core.language.translate("Where to Buy (URL Optional)")+'" name="buy-url"></div>'; 
    var currencyId = 'buysell-currency';
    var price = '<div class="activity_sell_composer_price"><span class="activity_sell_composer_price_currency font_color_light">'+this.__proto__.options.currency+'</span><span class="activity_sell_composer_price_input"><input type="text" id="buysell-price" placeholder="'+ en4.core.language.translate("Add price")+'" name="buysell-price"><input type="hidden" id="'+currencyId+'" name="buysell-currency" value="'+this.__proto__.options.currencySymbol+'"></span></div>';
    if(isEnablegLocation) {
      var location = '<div class="activity_sell_composer_location"><i class="font_color_light fas fa-map-marker-alt"></i><span id="locValuesbuysell-element"></span><span id="buyselllocal"><input type="text" id="buysell-location" placeholder="'+ en4.core.language.translate("Add location (optional)")+'" name="buysell-location"><input type="hidden" name="activitybuyselllng" id="activitybuyselllng"><input type="hidden" name="activitybuyselllat" id="activitybuyselllat"></span></div>';
    } else {
      var location = '';
    }
    var description = '<div class="activity_sell_composer_des"><textarea id="buysell-description" placeholder="'+ en4.core.language.translate("Describe your item (optional)")+'" name="buysell-description"></textarea></div></div>';
    scriptJquery(this.elements.body).html(title+wheretobuy+price+location+description);
    if(this.__proto__.options.photoUpload){
     scriptJquery(this.elements.body).append('<input type="file" accept="image/x-png,image/jpeg" onchange="readImageUrlbuysell(this)" multiple="multiple" id="file_multi" name="file_multi" style="display:none"><div class="activity_compose_photo_container clearfix"><div id="activity_compose_photo_container_inner" class="clearfix"><div id="show_photo"></div><div id="dragandrophandlerbuysell" class="activity_compose_photo_uploader center_item" title="'+ en4.core.language.translate("Choose a file to upload")+'"><i class="fa fa-plus"></i></div></div></div>');
     var byteMB = (post_max_size / (1024 * 1024)) + en4.core.language.translate("MB");
         byteMB =  en4.core.language.translate('Max size ') + byteMB;
         scriptJquery(this.elements.body).append('<span class="font_color_light">('+byteMB+')</span>');
    }
    var input = document.getElementById('buysell-location');
    if(isGoogleKeyEnabled && typeof input != 'undefined') {
      var autocomplete = new google.maps.places.Autocomplete(input);
      google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
          return;
        }
        scriptJquery('#locValuesbuysell-element').html('<span class="tag">'+scriptJquery('#buysell-location').val()+' <a href="javascript:void(0);" class="buysellloc_remove_act">x</a></span>');
        scriptJquery('#locValuesbuysell-element').show();
        scriptJquery('#buyselllocal').hide();
        document.getElementById('activitybuyselllng').value = place.geometry.location.lng();
        document.getElementById('activitybuyselllat').value = place.geometry.location.lat();
      });
    }
    scriptJquery('#buysell-description').hashtags();
  }

  this.deactivate = function() {
    if( !this.active ) return;
    this.active = false;
    this.__proto__.detach.call(this);
    this.request = false;
  }
};

function checkuploadfiletype(input,value){
  var url = input.value;
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  if (input.files && input.files[0] && (ext == "exe" || ext == '.mp3')) {
    scriptJquery('#fileupload-input-type').val('');
    return false;
  }
  if(input.files[0].size > value){
     en4.core.showError("<p>" + en4.core.language.translate("Upload smaller file.") + '</p><button onclick="Smoothbox.close()">'+ en4.core.language.translate("Close")+'</button>');
     scriptJquery('#fileupload-input-type').val('');
    return false;
  }
  var field = '<input type="hidden" name="attachment[type]" value="fileupload">';
  if(!scriptJquery('.fileupload-cnt').length)
    scriptJquery('#activity-form').append('<div style="display:none" class="fileupload-cnt">'+field+'</div>');
  else
    scriptJquery('.fileupload-cnt').html(field);
  var plugin = composeInstance.getPlugin('fileupload');
  plugin.ready();
}


/* $Id:composer_fileupload.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

Composer.Plugin.Fileupload = function(options){

  this.__proto__ = new Composer.Plugin.Interface(options);

  this.name = 'fileupload'

  this.options = {
    title : 'Add File',
    serverLimit : 0,
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    debug : false
  },

  this.initialize = function(options) {
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  },

  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();
    return this;
  }

  this.detach = function() {
    this.__proto__.detach.call(this);
    this.active = false
    if( this.interval ) $clear(this.interval);
    return this;
  }

  this.activate = function() {
    if( this.active ) return;
    this.__proto__.activate.call(this);

    this.makeMenu();
    this.makeBody();   
    var byteMB = (post_max_size / (1024 * 1024)) + en4.core.language.translate("MB"); 
    scriptJquery(this.elements.body).html('<input id="fileupload-input-type" type="file" name="fileupload" value="" onchange="checkuploadfiletype(this,'+this.options.serverLimitDigits+')"><span class="font_color_light">(Max size '+byteMB+')</span>');    
  },

  this.deactivate = function() {
    if( !this.active ) return;
    this.active = false;
    this.__proto__.detach.call(this);
    this.request = false;
  }
};

function checkuploadfiletype(input,value){
  var url = input.value;
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  if (input.files && input.files[0] && (ext == "exe" || ext == '.mp3')) {
    scriptJquery('#fileupload-input-type').val('');
    return false;
  }
  if(input.files[0].size > value){
     en4.core.showError("<p>" + en4.core.language.translate("Upload smaller file.") + '</p><button onclick="Smoothbox.close()">'+en4.core.language.translate("Close")+'</button>');
     scriptJquery('#fileupload-input-type').val('');
    return false;
  }
  var field = '<input type="hidden" name="attachment[type]" value="fileupload">';
  if(!scriptJquery('.fileupload-cnt').length)
    scriptJquery('#activity-form').append('<div style="display:none" class="fileupload-cnt">'+field+'</div>');
  else
    scriptJquery('.fileupload-cnt').html(field);
  var plugin = composeInstance.getPlugin('fileupload');
  plugin.ready();
}


/* $Id:composer_link.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

Composer.Plugin.Link = function(options){
  this.__proto__ = new Composer.Plugin.Interface(options);

  //Extends : Composer.Plugin.Interface,
  this.name = 'link'
  this.options = {
    title : 'Add Link',
    lang : {'Add Link': 'Add Link'},
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 600,
    debug : false
  }

  this.initialize = function(options) {
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  },

  this.attach = function() {
    this.__proto__.attach.call(this);
    // this.parent();
    this.makeActivator();

    // Poll for links
    //this.interval = (function() {
    //  this.poll();
    //}).periodical(250, this);
    this.monitorLastContent = '';
    this.monitorLastMatch = '';
    this.monitorLastKeyPress = $time();
    // this.getComposer().addEvent('editorKeyPress', function() {
    //   this.monitorLastKeyPress = $time();
    // }.bind(this));
    

    return this;
  }

  this.detach = function() {
    this.__proto__.detach.call(this);
    this.active = false
    if( this.interval ) $clear(this.interval);
    return this;
  }

  this.activate = function() {
    if( this.active ) return;
    this.__proto__.activate.call(this);

    this.makeMenu();
    this.makeBody();
    
    // Generate body contents
    // Generate form
    this.elements.formInput = scriptJquery.crtEle('input', {
      'id' : 'compose-link-form-input',
      'class' : 'compose-form-input',
      'type' : 'text'
    }).appendTo(this.elements.body);

    this.elements.formSubmit = scriptJquery.crtEle('button', {
      'id' : 'compose-link-form-submit',
      'class' : 'compose-form-submit',
    }).html(this._lang('Attach')).appendTo(this.elements.body).click(function(e) {
      e.preventDefault();
      this.doAttach();
    }.bind(this));
    this.elements.formInput.focus();
  }

//   this.deactivate = function() {
//     if( !this.active ) return;
//     //this.parent();
//     this.active = false;
//     this.__proto__.detach.call(this);
//     this.request = false;
//   },

  this.deactivate = function() {
    if( !this.active ) return;
    this.__proto__.deactivate.call(this);
    
    this.request = false;
  }

  this.poll = function() {
    // Active plugin, ignore
    if( this.getComposer().hasActivePlugin() ) return;
    // Recent key press, ignore
    if( $time() < this.monitorLastKeyPress + this.options.monitorDelay ) return;
    // Get content and look for links
    var content = this.getComposer().getContent();
    // Same as last body
    if( content == this.monitorLastContent ) return;
    this.monitorLastContent = content;
    // Check for match
    var m = content.match(/http:\/\/([-\w\.]+)+(:\d+)?(\/([-#:\w/_\.]*(\?\S+)?)?)?/);
    if( $type(m) && $type(m[0]) && this.monitorLastMatch != m[0] )
    {
      this.monitorLastMatch = m[0];
      this.activate();
      this.elements.formInput.value = this.monitorLastMatch;
      this.doAttach();
    }
  }

  // Getting into the core stuff now

  this.doAttach = function() {
    var val = this.elements.formInput.val();
    if( !val ) {
      return;
    }
    if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
    {
      val = 'http://' + val;
    }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' ) {
      return;
    }

    // Send request to get attachment
    var options = scriptJquery.extend({
      'dataType': 'json',
      'method': 'post',
      'data' : {
        'format' : 'json',
        'uri' : val
      },
      'success' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);

    // Inject loading
    this.makeLoading('empty');

    // Send request
    scriptJquery.ajax(options);
  }

  this.doProcessResponse = function(responseJSON, responseText) {
    // Handle error
    if( $type(responseJSON) != 'object' ) {
      responseJSON = {
        'status' : false
      };
    }
    this.params.set('uri', responseJSON.url);

    // If google docs then just output Google Document for title and descripton
    var uristr = responseJSON.url;
    if (uristr.substr(0, 23) == 'https://docs.google.com') {
      var title = uristr;
      var description = 'Google Document';
    } else {
      var title = responseJSON.title || responseJSON.url;
      var description = responseJSON.description || responseJSON.title || responseJSON.url;
    }
       
    var images = responseJSON.images || [];
    if(responseJSON.gifUrl)
      title = responseJSON.gifImageUrl;
    this.params.set('title', title);
    this.params.set('description', description);
    this.params.set('images', images);
    this.params.set('loadedImages', []);
    this.params.set('thumb', '');
    this.params.set('isGif', responseJSON.isGif);
    this.params.set('gifUrl',responseJSON.gifUrl);
    this.params.set('isIframe',responseJSON.isIframe);
    this.params.set('gifImageUrl',responseJSON.gifImageUrl);
    if(responseJSON.isGif){
      this.elements.body.empty();
      this.makeFormInputs();
      scriptJquery('#compose-link-menu').hide();
      scriptJquery('#compose-link-body').html('<div class="composer_link_gif_content_wrapper"><div class="composer_link_gif_content"><img src="'+responseJSON.gifImageUrl+'" data-original="'+responseJSON.gifUrl+'" data-still="'+responseJSON.gifImageUrl+'"><a href="javascript:;" class="link_play_activity notclose" title="'+en4.core.language.translate("PLAY")+'"></a><a href="javascript:;" class="link_cancel_activity"><i class="fas fa-times notclose" title="'+en4.core.language.translate("CANCEL")+'"></i></a></div></div>');
    }else if(responseJSON.isIframe){
       this.params.set('thumb', responseJSON.thumb);
       this.elements.body.empty();
       this.makeFormInputs();
       scriptJquery('#compose-link-menu').hide();
      scriptJquery('#compose-link-body').html('<div class="composer_link_video_content_wrapper"><div class="composer_link_gif_content composer_link_iframe_content">'+responseJSON.thumb+'<a href="javascript:;" class="link_cancel_activity"><i class="fas fa-times notclose" title="'+en4.core.language.translate("CANCEL")+'"></i></a></div><div class="composer_link_iframe_content_body"><div class="compose-preview-title"><a target="_blank" href="'+responseJSON.url+'">'+title+'</a></div><div class="compose-preview-description">'+description+'</div></div></div>');
    }else if( images.length > 0 ) {
      this.doLoadImages();
    } else {
      this.doShowPreview();
    }
  }

  // Image loading
  
  this.doLoadImages = function() {
    // Start image load timeout
    var interval = setTimeout(function() {
      // Debugging
      if( this.options.debug ) {
        console.log('Timeout reached');
      }
      //this.doShowPreview();
    }.bind(this),this.options.imageTimeout);

      
    // Load them images
    this.params.loadedImages = [];

    let imgs = []; 
    this.params.get('images').forEach(function(imgSrc){
      let img = scriptJquery.crtEle('img',{
        'src': imgSrc,
        'class' : 'compose-link-image'
      });
      imgs.push(img);
    });
    this.params.loadedImages = this.params.get('images');
    this.params.set('assets',imgs);
    this.doShowPreview();
  }


  // Preview generation
  
  this.doShowPreview = function() {
    var self = this;
    this.elements.body.empty();
    this.makeFormInputs();
    
    // Generate image thingy
    if( this.params.loadedImages.length > 0 ) {
      var tmp = new Array();
      this.elements.previewImages = scriptJquery.crtEle('div', {
        'id' : 'compose-link-preview-images',
        'class' : 'compose-preview-images'
      }).appendTo(this.elements.body);
      this.params.assets.forEach(function(element, index) {
        if( !$type(this.params.loadedImages[index]) ) return;
        element.addClass('compose-preview-image-invisible').appendTo(this.elements.previewImages);
        if(false ) {
          delete this.params.images[index];
          delete this.params.loadedImages[index];
          element.destroy();
        } else {
          element.removeClass('compose-preview-image-invisible').addClass('compose-preview-image-hidden');
          tmp.push(this.params.loadedImages[index]);
         // element.erase('height');
         // element.erase('width');
        }
      }.bind(this));

      this.params.loadedImages = tmp;

      if( this.params.loadedImages.length <= 0 ) {
        this.elements.previewImages.destroy();
      }
    }

    this.elements.previewInfo = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-info',
      'class' : 'compose-preview-info'
    }).appendTo(this.elements.body);
    
    // Generate title and description
    this.elements.previewTitle = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-title',
      'class' : 'compose-preview-title'
    }).appendTo(this.elements.previewInfo);

    this.elements.previewTitleLink = scriptJquery.crtEle('a', {
      'href' : this.params.uri,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditTitle(this);
        }
      }
    }).html(this.params.title).appendTo(this.elements.previewTitle).click((e) => {
      e.preventDefault();
      self.handleEditTitle(e);
    });;

    this.elements.previewDescription = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-description',
      'class' : 'compose-preview-description',
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditDescription(this);
        }
      }
    }).html(this.params.description).appendTo(this.elements.previewInfo).click((e) => {
      e.preventDefault();
      self.handleEditDescription(e);
    });

    // Generate image selector thingy
    if( this.params.loadedImages.length > 0 ) {
      this.elements.previewOptions = scriptJquery.crtEle('div', {
        'id' : 'compose-link-preview-options',
        'class' : 'compose-preview-options'
      }).appendTo(this.elements.previewInfo);

      if( this.params.loadedImages.length > 1 ) {
        this.elements.previewChoose = scriptJquery.crtEle('div', {
          'id' : 'compose-link-preview-options-choose',
          'class' : 'compose-preview-options-choose',
          'html' : '<span>' + this._lang('Choose Image:') + '</span>'
        }).html('<span>' + this._lang('Choose Image:') + '</span>').appendTo(this.elements.previewOptions);

        this.elements.previewPrevious = scriptJquery.crtEle('a', {
          'id' : 'compose-link-preview-options-previous',
          'class' : 'compose-preview-options-previous',
          'href' : 'javascript:void(0);',
          'html' : '&#171; ' + this._lang('Previous'),
          'events' : {
            'click' : this.doSelectImagePrevious.bind(this)
          }
        }).html('&#171; ' + this._lang('Previous')).appendTo(this.elements.previewChoose).click((e) => {
          this.doSelectImagePrevious()
        });

        this.elements.previewCount = scriptJquery.crtEle('span', {
          'id' : 'compose-link-preview-options-count',
          'class' : 'compose-preview-options-count'
        }).appendTo(this.elements.previewChoose);


        this.elements.previewPrevious = scriptJquery.crtEle('a', {
          'id' : 'compose-link-preview-options-next',
          'class' : 'compose-preview-options-next',
          'href' : 'javascript:void(0);',
          'html' : this._lang('Next') + ' &#187;',
          'events' : {
            'click' : this.doSelectImageNext()
          }
        }).html(this._lang('Next') + ' &#187;').appendTo(this.elements.previewChoose).click((e)=>{
          this.doSelectImageNext();
        });
      }

      this.elements.previewNoImage = scriptJquery.crtEle('div', {
        'id' : 'compose-link-preview-options-none',
        'class' : 'compose-preview-options-none'
      }).appendTo(this.elements.previewOptions);

      this.elements.previewNoImageInput = scriptJquery.crtEle('input', {
        'id' : 'compose-link-preview-options-none-input',
        'class' : 'compose-preview-options-none-input',
        'type' : 'checkbox',
        'events' : {
          'click' : this.doToggleNoImage.bind(this)
        }
      }).appendTo(this.elements.previewNoImage).change((e)=>{
        this.doToggleNoImage();
      });

      this.elements.previewNoImageLabel = scriptJquery.crtEle('label', {
        'for' : 'compose-link-preview-options-none-input',
        'html' : this._lang('Don\'t show an image'),
        'events' : {
          //'click' : this.doToggleNoImage.bind(this)
        }
      }).html(this._lang('Don\'t show an image')).appendTo(this.elements.previewNoImage);
      
      // Show first image
      this.setImageThumb(this.elements.previewImages.children().eq(0));
    }
  }

  this.checkImageValid = function(element) {
    var size = element.getSize();
    var sizeAlt = {x:element.get('width'),y:element.get('height')};
    var width = sizeAlt.x || size.x;
    var height = sizeAlt.y || size.y;
    var pixels = width * height;
    var aspect = width / height;
    
    // Debugging
    if( this.options.debug ) {
      console.log(element.get('src'), sizeAlt, size, width, height, pixels, aspect);
    }

    // Check aspect
    if( aspect > this.options.imageMaxAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect greater than max - ', element.get('src'), aspect, this.options.imageMaxAspect);
      }
      return false;
    } else if( aspect < this.options.imageMinAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect less than min - ', element.get('src'), aspect, this.options.imageMinAspect);
      }
      return false;
    }
    // Check min size
    if( width < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width less than min - ', element.get('src'), width, this.options.imageMinSize);
      }
      return false;
    } else if( height < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height less than min - ', element.get('src'), height, this.options.imageMinSize);
      }
      return false;
    }
    // Check max size
    if( width > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width greater than max - ', element.get('src'), width, this.options.imageMaxSize);
      }
      return false;
    } else if( height > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height greater than max - ', element.get('src'), height, this.options.imageMaxSize);
      }
      return false;
    }
    // Check  pixels
    if( pixels < this.options.imageMinPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count less than min - ', element.get('src'), pixels, this.options.imageMinPixels);
      }
      return false;
    } else if( pixels > this.options.imageMaxPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count greater than max - ', element.get('src'), pixels, this.options.imageMaxPixels);
      }
      return false;
    }

    return true;
  }

  this.doSelectImagePrevious = function() {
    let currentIndex = scriptJquery(this.elements.imageThumb).index();
    let totalIndex = scriptJquery(this.elements.imageThumb).parent().children().length;
    let next = null;
    if(currentIndex != 0){
       next = scriptJquery(this.elements.imageThumb).parent().children().eq(currentIndex-1);
    }else{
      next = scriptJquery(this.elements.imageThumb).parent().children().eq(totalIndex-1);
    }
    if( this.elements.imageThumb && next.length > 0 ) {
      this.setImageThumb(next);
    }
  }

  this.doSelectImageNext = function() {
    let currentIndex = scriptJquery(this.elements.imageThumb).index();
    let totalIndex = scriptJquery(this.elements.imageThumb).parent().children().length;
    let next = null;
    if(currentIndex < totalIndex-1){
       next = scriptJquery(this.elements.imageThumb).parent().children().eq(currentIndex+1);
    }else{
      next = scriptJquery(this.elements.imageThumb).parent().children().eq(0);
    }
    if( this.elements.imageThumb && next.length > 0 ) {
      this.setImageThumb(next);
    }
  }
  this.setFormInputValue = function(key,value){
    this.__proto__.setFormInputValue.call(this,key,value);
  }
  this.doToggleNoImage = function() {
    if( !scriptJquery("#compose-link-preview-options-none-input").is(':checked') ) {
      let elementA = scriptJquery(this.elements.imageThumb);
      this.params.thumb = elementA.attr("src");
      this.setFormInputValue('thumb', this.params.thumb);
      this.elements.previewImages.css('display', '');
      if( this.elements.previewChoose ) this.elements.previewChoose.css('display', '');
    } else {
      delete this.params.thumb;
      this.setFormInputValue('thumb', '');
      this.elements.previewImages.css('display', 'none');
      if( this.elements.previewChoose ) this.elements.previewChoose.css('display', 'none');
    }
  }

  this.setImageThumb = function(element) {
    // Hide old thumb
    if( this.elements.imageThumb ) {
      this.elements.imageThumb.addClass('compose-preview-image-hidden');
    }
    if( element ) {
      element.removeClass('compose-preview-image-hidden');
      let elementA = scriptJquery(element);
      this.elements.imageThumb = element;
      this.params.thumb = elementA.attr("src");
      this.setFormInputValue('thumb',elementA.attr("src"));
      if( this.elements.previewCount ) {
        var index = this.params.loadedImages.indexOf(elementA.attr("src"));
        //this.elements.previewCount.set('html', ' | ' + (index + 1) + ' of ' + this.params.loadedImages.length + ' | ');
	    if ( index < 0 ) { index = 0; }
        this.elements.previewCount.html(' | ' + this._lang('%d of %d', index + 1, this.params.loadedImages.length) + ' | ');
    }
    } else {
      this.elements.imageThumb = false;
      delete this.params.thumb;
    }
  }

  this.makeFormInputs = function() {
    this.ready();
    
    this.__proto__.makeFormInputs.call(this,{
      'uri' : this.params.uri,
      'title' : this.params.title,
      'description' : this.params.description,
      'thumb' : this.params.thumb,
      'isGif' : this.params.isGif,
      'isIframe':this.params.isIframe,
      'gifUrl' : this.params.gifUrl,
    });
  }

  this.handleEditTitle = function(elementData) {
    let element = scriptJquery(elementData.target)
    element.css('display', 'none');
    var input = scriptJquery.crtEle('input', {
      'type' : 'text',
      'value' : element.text().trim(),
    }).insertAfter(element).blur(function(e) {
      if( scriptJquery(e.target).val() != '' ) {
        this.params.title = scriptJquery(e.target).val();
        element.text(this.params.title);
        this.setFormInputValue('title', this.params.title);
      }
      element.css('display', '');
      input.remove();
    }.bind(this));
    input.focus();
  }

  this.handleEditDescription = function(elementData) {
    let element = scriptJquery(elementData.target)
    element.css('display', 'none');
    var input = scriptJquery.crtEle('textarea', {}).html(element.text().trim()).insertAfter(element).blur(function(e) {
      if( scriptJquery(e.target).val() != '' ) {
        this.params.description = scriptJquery(e.target).val();
        element.text(this.params.description);
        this.setFormInputValue('description', this.params.description);
      }
      element.css('display', '');
      input.remove();
    }.bind(this));
    input.focus();
  }
  this.initialize(options);
}


AttachEventListerSE('click','.link_play_activity',function(e){
  scriptJquery('.link_play_activity').show();
  //loop over all item and hide
  scriptJquery('.composer_link_gif_content').each(function(i, obj) {
    scriptJquery(obj).find('img').attr('src',scriptJquery(obj).find('img').attr('data-still'));
  });
  scriptJquery(this).closest('.composer_link_gif_content').find('img').attr('src',scriptJquery(this).closest('.composer_link_gif_content').find('img').attr('data-original'));
  scriptJquery(this).hide(); 
  if(!scriptJquery(this).closest('.feed_attachment_core_link').length)
  scriptJquery('.compose-link-menu').hide(); 
});
AttachEventListerSE('click','.composer_link_gif_content > img',function(){
  scriptJquery(this).closest('.composer_link_gif_content').find('.link_play_activity').show();
  scriptJquery(this).closest('.composer_link_gif_content').find('img').attr('src',scriptJquery(this).closest('.composer_link_gif_content').find('img').attr('data-still'));
});
AttachEventListerSE('click','.link_cancel_activity',function(){
  Object.entries(composeInstance.plugins).forEach(function([key,plugin]) {
      plugin.deactivate();
      scriptJquery('#fancyalbumuploadfileids').val('');
   });
   composeInstance.getTray().empty(); 
});

/* $Id:composer_targetpost.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

Composer.Plugin.Activitytargetpost = function(options){

  this.__proto__ = new Composer.Plugin.Interface(options);
  this.name = 'targetpost'
  this.options = {
    title : en4.core.language.translate("Choose Preferred Audience"),
    lang : {
        'Choose Preferred Audience': 'Choose Preferred Audience'
    },
    requestOptions : false
  }
  this.initialize = function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  }
  this.attach = function() {
     var openWindow = '';
     
     this.elements.spanToggle = scriptJquery.crtEle('span', {
      'class' : 'composer_targetpost_toggle activity_tooltip',
      'href'  : 'javascript:void(0);',
      'title' : this.options.lang['Choose Preferred Audience']
    })
    this.elements.formCheckbox = scriptJquery.crtEle('input', {
      'id'    : 'compose-targetpost-form-input',
      'class' : 'compose-form-input',
      'type'  : 'checkbox',
      'name'  : 'post_to_targetpost',
      'style' : 'display:none;'
    });

    this.elements.formCheckbox.appendTo(this.elements.spanToggle)
    //this.elements.spanTooltip.inject(this.elements.spanToggle);
    this.elements.spanToggle.appendTo(scriptJquery('#compose-menu')).click((e) => {
      this.toggle();
    });;;
    //this.parent();
    //this.makeActivator();
    return this;
  }
  this.detach = function() {
    this.__proto__.detach.call(this);
    this.active = false
    if( this.interval ) $clear(this.interval);
    return this;
  }
  this.toggle = function(event) {
    //open target post popup
    openTargetPostPopup();
    composeInstance.plugins['targetpost'].active=true;
    setTimeout(function(){
      composeInstance.plugins['targetpost'].active=false;
    }, 300);
  }
  this.initialize(options);
};


/* $Id:editComposer.js  2017-01-12 00:00:00 SocialEngineSolutions $*/

AttachEventListerSE('click','#activity_location_edit, .seloc_clk_edit',function(e){
  that = scriptJquery(this);
  if(scriptJquery(this).hasClass('.seloc_clk_edit'))
     that = scriptJquery('#activity_location_edit');
   if(scriptJquery(this).hasClass('active')){
     scriptJquery(this).removeClass('active');
     scriptJquery('.activity_post_location_container_edit').hide();
     return;
   }
   scriptJquery('.activity_post_location_container_edit').show();
   scriptJquery(this).addClass('active');
});
AttachEventListerSE('click','#activity_tag_edit, .tag_clk_edit',function(e){
  that = scriptJquery(this);
  if(scriptJquery(this).hasClass('.tag_clk_edit'))
     that = scriptJquery('#activity_tag_edit');
   if(scriptJquery(that).hasClass('active')){
     scriptJquery(that).removeClass('active');
     scriptJquery('.activity_post_tag_cnt_edit').hide();
     return;
   }
   scriptJquery('.activity_post_tag_cnt_edit').show();
   scriptJquery(that).addClass('active');
});


//Feelings Work
AttachEventListerSE('click','#activity_feelings_editspan',function(e){
  that = scriptJquery(this);
  if(scriptJquery(this).hasClass('.seloc_clk_edit'))
     that = scriptJquery('#activity_feelings_editspan');
   if(scriptJquery(this).hasClass('active')){
     scriptJquery(this).removeClass('active');
     scriptJquery('.activity_post_feelingcontent_containeredit').hide();
     scriptJquery('.activity_post_feeling_container_edit').hide();
     return;
   }
  scriptJquery(this).addClass('active');
  scriptJquery('.activity_post_feeling_container_edit').show();
  if(scriptJquery('#feelingactivityidedit').val() == '')
    scriptJquery('.activity_post_feelingcontent_containeredit').show();
});

AttachEventListerSE('click', '#feeling_activityedit', function(e){

  if(scriptJquery('#feelingactivityidedit').val() == '')
    scriptJquery('.activity_post_feelingcontent_containeredit').show();
});

AttachEventListerSE('keyup', '#feeling_activityedit', function(e){
  if (e.which == 8) {
    scriptJquery('#feelingactivityiconidedit').val() = '';
    scriptJquery('#feeling_elem_actedit').html('');
    scriptJquery('#feeling_activityedit').attr("placeholder", "How are you feeling?");
  }
});

function showFeelingContanieredit() {

  if(scriptJquery('#activity_post_feeling_container_edit').css("display") == '' || scriptJquery('#activity_post_feeling_container_edit').css("display") == 'table') {
    scriptJquery('#showFeelingContanieredit').removeClass('active');
    scriptJquery('#activity_post_feeling_container_edit').hide();
  } else {
    scriptJquery('#showFeelingContanieredit').addClass('active');
    scriptJquery('#feeling_activity_remove_actedit').show();
    scriptJquery('#activity_post_feeling_container_edit').show();
  }
}

function feelingactivityremoveactedit() {
  scriptJquery('#feeling_activity_remove_actedit').hide();
  scriptJquery('#feelingActTypeedit').html('');
  scriptJquery('#feelingActTypeedit').hide();
  scriptJquery('.activityfeelingactivity-ul').html('');
  if(scriptJquery('#feelingactivityidedit').val())
  scriptJquery('#feelingactivityidedit').val("");
  scriptJquery('#feeling_activityedit').val('');
  scriptJquery('#feelingactivityiconidedit').val("");
  scriptJquery('#feeling_elem_actedit').html('');
  
}

//Autosuggest feeling work
AttachEventListerSE('click', '.activity_feelingactivitytypeliedit', function(e) {

  scriptJquery('#feelingactivityiconidedit').val(scriptJquery(this).attr('data-rel'));
  scriptJquery('#feelingactivity_resource_typeedit').val(scriptJquery(this).attr('data-type'))
  
  if(!scriptJquery(this).attr('data-rel')) {
    scriptJquery('#feelingactivity_customedit').val(1);
    scriptJquery('#feelingactivity_customtextedit').val(scriptJquery('#feeling_activityedit').val());
  }
  
  if(scriptJquery(this).attr('data-icon')) {
    var finalFeeling = '-- ' + '<img class="feeling_icon" title="'+scriptJquery(this).attr('data-title')+'" src="'+scriptJquery(this).attr('data-icon')+'"><span>' + ' ' +  scriptJquery('#feelingActTypeedit').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanieredit" class="" onclick="showFeelingContanieredit()">'+scriptJquery(this).attr('data-title')+'</a>';
  } else {
    var finalFeeling = '-- ' + '<img class="feeling_icon" title="'+scriptJquery(this).attr('data-title')+'" src="'+scriptJquery(this).find('a').find('img').attr('src')+'"><span>' + ' ' +  scriptJquery('#feelingActTypeedit').html().toLowerCase() + ' ' + '<a href="javascript:;" id="showFeelingContanieredit" class="" onclick="showFeelingContanieredit()">'+scriptJquery(this).attr('data-title')+'</a>';
  }
  
  scriptJquery('#feeling_activityedit').val(scriptJquery(this).attr('data-title'));
  scriptJquery('#feeling_elem_actedit').show();
  scriptJquery('#feeling_elem_actedit').html(finalFeeling);
  scriptJquery('#dash_elem_act_edit').hide();
  scriptJquery('#activity_post_feeling_container_edit').hide();
});
//Autosuggest feeling work

  
AttachEventListerSE('click', '.activity_feelingactivitytypeedit', function(e) {
  
  var feelingsactivity = scriptJquery(this);
  var feelingIdEdit = scriptJquery(this).attr('data-rel');
  var feelingTypeEdit = scriptJquery(this).attr('data-type');
  var feelingTitleEdit = scriptJquery(this).attr('data-title');
  scriptJquery('#feelingActTypeedit').show();
  scriptJquery('#feelingActTypeedit').html(feelingTitleEdit);
  scriptJquery('#feeling_activityedit').attr("placeholder", "How are you feeling?");
  
  document.getElementById('feelingactivityidedit').value = feelingIdEdit;
  
  document.getElementById('feelingactivitytypeedit').value = feelingTypeEdit;
  
  scriptJquery('.activity_post_feelingcontent_containeredit').hide();
  
  scriptJquery('#feeling_activityedit').trigger('change').trigger('keyup').trigger('keydown');
  
//   contentAutocompletefeelingedit.setOptions({
//     'postData': {
//       'feeling_id': document.getElementById('feelingactivityidedit').value,
//       'feeling_type': document.getElementById('feelingactivitytypeedit').value,
//     }
//   });
});

AttachEventListerSE('click','.select_feeling_emoji_advedit > img',function(e){
  
  var feeling_emoji_icon = scriptJquery(this).parent().parent().attr('data-icon');
  var html = scriptJquery('#edit_activity_body').val(); 
  if(html == '<br>')
    scriptJquery('#edit_activity_body').val('');
  scriptJquery('textarea#edit_activity_body').val(scriptJquery('textarea#edit_activity_body').val()+' '+feeling_emoji_icon);
  
  var data = scriptJquery('#edit_activity_body').val();
    EditFieldValue = data;

  scriptJquery('textarea#edit_activity_body').trigger('focus');
//  scriptJquery('#activityfeeling_emoji-edit-a').trigger('click');
});
//Feeling Work End

var requestEmojiA;
AttachEventListerSE('click','#activityemoji-edit-a',function(){
  
    scriptJquery(this).parent().find('.comment_emotion_container').removeClass('from_bottom');
    
    var parentElem = scriptJquery('#ajaxsmoothbox_container');
    var parentLeft = parentElem.css('left').replace('px','');
    var parentTop = parentElem.css('top').replace('px','');

    var topPositionOfParentDiv =  scriptJquery(this).offset().top + 35;
    topPositionOfParentDiv = topPositionOfParentDiv;
    var leftSub = 264;
    var leftPositionOfParentDiv =  scriptJquery(this).offset().left - leftSub;
    leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
    scriptJquery(this).parent().find('.comment_emotion_container').css('right',0);
    //scriptJquery(this).parent().find('.comment_emotion_container').css('top',topPositionOfParentDiv+'px');
    //scriptJquery(this).parent().find('.comment_emotion_container').css('left',leftPositionOfParentDiv).css('z-index',100);
    scriptJquery(this).parent().find('.comment_emotion_container').show();

    if(scriptJquery(this).hasClass('active')){
      scriptJquery(this).removeClass('active');
      scriptJquery('#activityemoji_edit').hide();
      return false;
     }
      scriptJquery(this).addClass('active');
      scriptJquery('#activityemoji_edit').show();
      if(scriptJquery(this).hasClass('complete'))
        return false;
      
       var that = this;
       var url = en4.core.baseUrl + 'activity/ajax/emoji/edit/true';
       requestEmojiA = scriptJquery.ajax({
        url : url,
        data : {
          format : 'html',
        },
        evalScripts : true,
        success : function(responseHTML) {
          scriptJquery('#activityemoji_edit').find('.comment_emotion_container_inner').find('.comment_emotion_holder').html(responseHTML);
          scriptJquery(that).addClass('complete');
          activitytooltip();
        }
      });
});

AttachEventListerSE('click','.select_emoji_advedit > img',function(e){
  var code = scriptJquery(this).parent().parent().attr('rel');
  var html = scriptJquery('#edit_activity_body').val();
  if(html == '<br>')
    scriptJquery('#edit_activity_body').val('');
  scriptJquery('#edit_activity_body').val( scriptJquery('#edit_activity_body').val()+' '+code);
  var data = scriptJquery('#edit_activity_body').val();
  EditFieldValue = data;
  scriptJquery('#activityemoji-edit-a').trigger('click');
});

AttachEventListerSE('click','.adv_privacy_optn_edit li a',function(e){
  e.preventDefault();
  if(!scriptJquery(this).parent().hasClass('multiple')){
    scriptJquery('.adv_privacy_optn_edit > li').removeClass('active');
    var text = scriptJquery(this).text();
    scriptJquery('.activity_privacy_btn_edit').attr('title',text);;
    scriptJquery(this).parent().addClass('active');
    scriptJquery('#adv_pri_option_edit').html(text);
    scriptJquery('#activity_privacy_icon').remove();
    scriptJquery('<i id="activity_privacy_icon" class="'+scriptJquery(this).find('i').attr('class')+'"></i>').insertBefore('#adv_pri_option_edit');
    
    if(scriptJquery(this).parent().hasClass('activity_network_edit'))
      scriptJquery('#privacy_edit').val(scriptJquery(this).parent().attr('data-src')+'_'+scriptJquery(this).parent().attr('data-rel'));
    else if(scriptJquery(this).parent().hasClass('activity_list_edit'))
      scriptJquery('#privacy_edit').val(scriptJquery(this).parent().attr('data-src')+'_'+scriptJquery(this).parent().attr('data-rel'));
   else
    scriptJquery('#privacy_edit').val(scriptJquery(this).parent().attr('data-src'));
  }
  // scriptJquery('.activity_privacy_btn_edit').parent().removeClass('activity_pulldown_active');
});

AttachEventListerSE('click','.mutiselectedit',function(e){
  if(scriptJquery(this).attr('data-rel') == 'network-multi')
    var elem = 'activity_network_edit';
  else
    var elem = 'activity_list_edit';
  var elemens = scriptJquery('.'+elem);
  var html = '';
  for(i=0;i<elemens.length;i++){
    html += '<li><input class="checkbox" type="checkbox" value="'+scriptJquery(elemens[i]).attr('data-rel')+'">'+scriptJquery(elemens[i]).text()+'</li>';
  }
  en4.core.showError('<form id="'+elem+'_select" class="activity_privacyselectpopup"><p>Please select network to display post</p><ul class="clearfix">'+html+'</ul><div class="activity_privacyselectpopup_btns clearfix"><button type="submit">Save</button><button class="close" onclick="Smoothbox.close();return false;">Close</button></div></form>');  
  scriptJquery ('.activity_privacyselectpopup').parent().parent().addClass('activity_privacyselectpopup_wrapper');
  //pre populate
  var valueElem = scriptJquery('#privacy_edit').val();
  if(valueElem && valueElem.indexOf('network_list_') > -1 && elem == 'activity_network_edit'){
    var exploidV =  valueElem.split(',');
    for(i=0;i<exploidV.length;i++){
       var id = exploidV[i].replace('network_list_','');
       scriptJquery('.checkbox[value="'+id+'"]').prop('checked', true);
    }
   }else if(valueElem && valueElem.indexOf('member_list_') > -1 && elem == 'activity_list_edit'){
    var exploidV =  valueElem.split(',');
    for(i=0;i<exploidV.length;i++){
       var id = exploidV[i].replace('member_list_','');
       scriptJquery('.checkbox[value="'+id+'"]').prop('checked', true);
    }
   }
});

AttachEventListerSE('submit','#activity_list_edit_select',function(e){
  e.preventDefault();
  var isChecked = false;
   var activity_list_select = scriptJquery('#activity_list_edit_select').find('[type="checkbox"]');
   var valueL = '';
   for(i=0;i<activity_list_select.length;i++){
    if(!isChecked)
      scriptJquery('.adv_privacy_optn_edit > li').removeClass('active');
    if(scriptJquery(activity_list_select[i]).is(':checked')){
      isChecked = true;
      var el = scriptJquery(activity_list_select[i]).val();
      scriptJquery('.lists[data-rel="'+el+'"]').addClass('active');
      valueL = valueL+'member_list_'+el+',';
    }
   }
   if(isChecked){
     scriptJquery('#privacy_edit').val(valueL);
     scriptJquery('#adv_pri_option_edit').html(en4.core.translate("Multiple Lists"));
     scriptJquery('.activity_privacy_btn_edit').attr('title',en4.core.translate("Multiple Lists"));
    scriptJquery(this).find('.close').trigger('click');
   }
   scriptJquery('#activity_privacy_icon_edit').removeAttr('class').addClass('activity_list');
});
AttachEventListerSE('submit','#activity_network_edit_select',function(e){
  e.preventDefault();
  var isChecked = false;
   var activity_network_select = scriptJquery('#activity_network_edit_select').find('[type="checkbox"]');
   var valueL = '';
   for(i=0;i<activity_network_select.length;i++){
    if(!isChecked)
      scriptJquery('.adv_privacy_optn_edit > li').removeClass('active');
    if(scriptJquery(activity_network_select[i]).is(':checked')){
      isChecked = true;
      var el = scriptJquery(activity_network_select[i]).val();
      scriptJquery('.network[data-rel="'+el+'"]').addClass('active');
      valueL = valueL+'network_list_'+el+',';
    }
   }
   if(isChecked){
     scriptJquery('#privacy_edit').val(valueL);
     scriptJquery('#adv_pri_option_edit').html('Multiple Network');
     scriptJquery('.activity_privacy_btn_edit').attr('title','Multiple Network');;
    scriptJquery(this).find('.close').trigger('click');
   }
   scriptJquery('#activity_privacy_icon_edit').removeAttr('class').addClass('activity_network');
});
 
function tagLocationWorkEdit(){
    if(!scriptJquery('#tag_location_edit').val())
      return;
     scriptJquery('#locValuesEdit-element').html('<span class="tag">'+scriptJquery('#tag_location_edit').val()+' <a href="javascript:void(0);" class="loc_remove_act_edit">x</a></span>');
      scriptJquery('#dash_elem_act_edit').show();
      scriptJquery('#location_elem_act_edit').show();
      scriptJquery('#location_elem_act_edit').html('at <a href="javascript:;" class="seloc_clk_edit">'+scriptJquery('#tag_location_edit').val()+'</a>');
      scriptJquery('#tag_location_edit').hide();  
  }
  
    
  AttachEventListerSE('click','.loc_remove_act_edit',function(e){
    scriptJquery('#activitylngEdit').val('');
    scriptJquery('#activitylatEdit').val('');
    scriptJquery('#tag_location_edit').val('');
    scriptJquery('#locValuesEdit-element').html('');
    scriptJquery('#tag_location_edit').show();
    scriptJquery('#location_elem_act_edit').hide();
    if(!scriptJquery('#toValuesEdit-element').children().length)
       scriptJquery('#dash_elem_act_edit').hide();
  })    
// Populate data
  var maxRecipientsEdit = 50;
  
 function getMentionDataEdit(that,dataBody){
    var data = scriptJquery('#edit_activity_body').val();
    var data_status = scriptJquery(that).attr('data-status');

    if(scriptJquery('#buysell-title-edit').length) {
      if(!scriptJquery('#buysell-title-edit').val())
        return false;
      else if(!scriptJquery('#buysell-price-edit').val())
        return false;
    } 
    //Feeling Work
    else if(!data && data_status == 1 && !scriptJquery('#toValuesEdit').val() && !scriptJquery('#tag_location_edit').val() && !scriptJquery('#feeling_activityedit').val())
      return false;
    
    data = scriptJquery(that).serialize()+'&bodyText='+dataBody;
    var url  = en4.core.baseUrl + 'activity/index/edit-feed-post/userphotoalign/'+userphotoalign;
    scriptJquery(that).find('#compose-submit').attr('disabled',true);
    if(url.indexOf('&') <= 0)
      url = url+'?';
    url = url+'is_ajax=true';
    var that = that;
    scriptJquery(that).find('#compose-submit').html(savingtextActivityPost);
    //scriptJquery('#dots-animation-posting').show();
    //dotsAnimationWhenPostingInterval = setInterval (function() { dotsAnimationWhenPostingFn(sharingPostText)}, 600);
    activityfeedactive2  = scriptJquery.ajax({
        url : url,
        data:data,
        method:"POST",
        success : function( responseHTML){
          try{
            var parseJson = scriptJquery.parseJSON(responseHTML);
            if(parseJson.status){
              scriptJquery('#activity-item-'+parseJson.last_id).replaceWith(parseJson.feed);
              
              scriptJquery('#activity-item-'+parseJson.last_id).fadeOut("slow", function(){
                 scriptJquery('#activity-item-'+parseJson.last_id).replaceWith(parseJson.feed);
                 scriptJquery('#activity-item-'+parseJson.last_id).fadeIn("slow");
                 activitytooltip();
              });
              
              ajaxsmoothboxclose();           
            }else{
               en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
            }
          }catch(e){
            
          }
          scriptJquery(that).find('#compose-submit').html(savingtextActivityPostOriginal);
          scriptJquery(that).find('#compose-submit').removeAttr('disabled');
          
        },
        onError: function(){
          en4.core.showError("<p>" + en4.core.language.translate("An error occured. Please try again after some time.") + '</p><button onclick="Smoothbox.close()">Close</button>');
        },
      });
  }
  //submit form
  AttachEventListerSE('submit','.edit-activity-form',function(e){
    e.preventDefault(); 
    var that = this;
    scriptJquery('textarea#edit_activity_body').mentionsInput('val', function(data) {
       getMentionDataEdit(that,data);
    });
  });
  AttachEventListerSE('click','.composer_targetpost_edit_toggle',function(e){
     openTargetPostPopupEdit(); 
  });

  function setCaretPos(pos) {
    this.lastCaretPos = pos;
    var index = 0, range = document.createRange(), body = scriptJquery('#edit_activity_body')[0];
    range.setStart(body, 0);
    range.collapse(true);
    var nodeArray = [body], node, isStart = false, stop = false;

    while (!stop && (node = nodeArray.pop())) {
      if (node.nodeType === 3) {
        var nextIndex = index + node.length;
        if (!isStart && pos >= index && pos <= nextIndex) {
          range.setStart(node, pos - index);
          isStart = true;
        } else if (isStart && pos >= index && pos <= nextIndex) {
          range.setEnd(node, pos - index);
          stop = true;
        }
        index = nextIndex;
      } else {
        var i = node.childNodes.length;
        while (i--) {
          nodeArray.push(node.childNodes[i]);
        }
      }
    }
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);

  }
  function checkPostLength(e) {
    var content = scriptJquery('#edit_activity_body').val();
    content = content.replace(/&nbsp;/g, ' ');
    content = content.replace(/&amp;/g, '&'); 
    content = content.replace(/&lt;/g, '<'); 
    content = content.replace(/&gt;/g, '>');
    let  activity_feedLimit = scriptJquery("#edit_activity_body").attr("data-length");
    if(parseInt(activity_feedLimit) > 0){
      activity_feedLimit = parseInt(activity_feedLimit);
    }
    if(activity_feedLimit && parseInt(activity_feedLimit) > 0 && activity_feedLimit < content.length){
      content = content.substr(0,activity_feedLimit);
      scriptJquery("#edit_activity_body").html(content);
    }
    if(activity_feedLimit && parseInt(activity_feedLimit) > 0){
      scriptJquery(".compose-content-counter-edit").css("display","inline-block");
      scriptJquery(".compose-content-counter-edit").html(activity_feedLimit-content.length);
      return;
    }
  }
  
  AttachEventListerSE('input','#edit_activity_body',function(e){ 
    checkPostLength(e);
  });

  AttachEventListerSE('focus','#edit_activity_body',function(){ 
if(!scriptJquery(this).attr('id'))
  scriptJquery(this).attr('id',new Date().getTime());
  
  isonCommentBox = true;
  var data = scriptJquery(this).val();
  if(!scriptJquery(this).val() || isOnEditField){
    if(!scriptJquery(this).val() )
      EditFieldValue = '';

    //if(userTagsEnable) {
      scriptJquery(this).mentionsInput({
          onDataRequest:function (mode, query, callback) {
           scriptJquery.getJSON('activity/ajax/friends/query/'+query, function(responseData) {
            responseData = _.filter(responseData, function(item) { return item.name.toLowerCase().indexOf(query.toLowerCase()) > -1 });
            callback.call(this, responseData);
          });
        },
        //defaultValue: EditFieldValue,
        onCaret: true
      });
    //}
  }
  
  if(data){
     getDataMentionEdit(this,data);
  }
  
  if(!scriptJquery(this).parent().hasClass('typehead')){
    scriptJquery(this).hashtags();
    scriptJquery(this).focus();
  }
  autosize(scriptJquery(this));
});
AttachEventListerSE('keyup','#edit_activity_body',function(){ 
    var data = scriptJquery(this).val();
     EditFieldValue = data;
});


/* $Id: composer_music.js 9572 2011-12-27 23:41:06Z john $ */

var musicfeedupload = false;
Composer.Plugin.Music = function(options) {
  
  this.__proto__ = new Composer.Plugin.Interface(options);
  
  this.name = 'music';

  this.options = {
    title : 'Add Music',
    lang : {},
    requestOptions : false,
    fancyUploadEnabled : true,
    fancyUploadOptions : {},
    debug : ('en4' in window && en4.core.environment == 'production' ? false : true )
  };
  
  this.initialize = function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  };
  
  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();
    return this;
  }
  
  this.detach = function() {
    this.__proto__.detach.call(this);
    return this;
  }
  
  this.activate = function() {
    if( this.active ) return;
   this.__proto__.activate.call(this);
   
   this.makeMenu();
   this.makeBody();
   
 
   // Generate form
   var fullUrl = this.options.requestOptions.url;
   this.elements.form = scriptJquery.crtEle('form', {
     'id' : 'compose-music-form',
     'class' : 'compose-form',
     'method' : 'post',
     'action' : fullUrl,
     'enctype' : 'multipart/form-data'
   }).appendTo(this.elements.body);
   
   this.elements.formInput = scriptJquery.crtEle('input', {
     'id' : 'compose-music-form-input',
     'class' : 'compose-form-input',
     'type' : 'file',
     'name' : 'file',
     'accept' : 'audio/*',
   })
   .change(this.doRequest.bind(this))
   .appendTo(this.elements.form);

   var byteMB = (post_max_size / (1024 * 1024)) + en4.core.language.translate("MB");
   byteMB =  en4.core.language.translate('Max size ') + byteMB;
   scriptJquery(this.elements.form).append('<span class="font_color_light">('+byteMB+')</span>');    
  }
    
  this.deactivate = function() {
    if (this.params.song_id)
      scriptJquery.ajax({
        url: en4.core.basePath + 'music/remove-song',
        dataType : 'json',
        method : 'post',
        data: {
          format: 'json',
            song_id: this.params.song_id
        }
      });
    if( !this.active ) return;
    this.__proto__.deactivate.call(this);
  };
    
  this.doRequest = function(that) {
    
    if (this.elements.formInput[0].files.length > 0) {
      var FileSize = this.elements.formInput[0].files[0].size / 1024 / 1024; // in MB
      if(FileSize > post_max_size) {
        alert("The size of the file exceeds the limits set on the server.");
        scriptJquery(this.elements.formInput).val('');
        return;
      }
    }
    
    var submittedForm = false;
    this.elements.iframe = scriptJquery.crtEle('iframe',{
      'name' : 'composeMusicFrame',
      'src' : 'javascript:false;',
    })
    .css({'display' : 'none'})
    .load(function() {
      if( !submittedForm ) {
        return;
      }
      musicfeedupload = false;
      this.doProcessResponse(window._composeMusicResponse);
      window._composeMusicResponse = false;
    }.bind(this))
    .appendTo(this.elements.body);
    
    window._composeMusicResponse = false;
    this.elements.form.attr('target', 'composeMusicFrame');
    
    musicfeedupload = true;
    // Submit and then remove form
    this.elements.form.trigger("submit");
    submittedForm = true;
    this.elements.form.remove();
    
    // Start loading screen
    this.makeLoading();
  }
    
  this.makeLoading = function(action) {
    if( !this.elements.loading ) {
      if( action == 'empty' ) {
        this.elements.body.empty();
      } else if( action == 'hide' ) {
        this.elements.body.children().each(function(e){ scriptJquery(this).css('display', 'none')});
      } else if( action == 'invisible' ) {
        this.elements.body.children().each(function(e){ scriptJquery(this).css('height', '0px').css('visibility', 'hidden')});
      }
      
      this.elements.loading = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-loading',
                                                  'class' : 'compose-loading'
      }).appendTo(this.elements.body);
      
      var image = this.elements.loadingImage || (scriptJquery.crtEle('img', {
        'id' : 'compose-' + this.getName() + '-loading-image',
                                                                     'class' : 'compose-loading-image'
      }));
      
      image.appendTo(this.elements.loading);
      
      scriptJquery.crtEle('span', {}).html(this._lang('Loading song, please wait...')).appendTo(this.elements.loading);
    }
  }
    
  this.doProcessResponse = function(responseJSON) {

    if( typeof responseJSON == 'object' && typeof responseJSON.error != 'undefined' ) {
      if( this.elements.loading ) {
        this.elements.loading.remove();
      }
      return this.makeError(responseJSON.error, 'empty');
    }
    
    // An error occurred
    if ( ($type(responseJSON) != 'object' && $type(responseJSON) != 'hash' )) {
      if( this.elements.loading )
        this.elements.loading.remove();
      this.makeError(this._lang('Unable to upload music. Please click cancel and try again'), 'empty');
      return;
    }
    
    if (  $type(parseInt(responseJSON.id)) != 'number' ) {
      if( this.elements.loading )
        this.elements.loading.remove();
      this.makeError(this._lang('Song got lost in the mail. Please click cancel and try again'), 'empty');
      return;
    }
    // Success
    this.params.set('rawParams',  responseJSON);
    this.params.set('song_id',    responseJSON.id);
    this.params.set('song_title', responseJSON.fileName);
    this.params.set('song_url',   responseJSON.song_url);
    this.elements.preview = scriptJquery.crtEle('a', {
      'href': 'javascript:void(0);',
      'class': 'compose-music-link',
    }).text(responseJSON.song_title);
    // .click(function(event) {
    //   event.preventDefault();
    //   scriptJquery(this).toggleClass('compose-music-link-playing');
    //   scriptJquery(this).toggleClass('compose-music-link');
    //   // var song = (responseJSON.song_url.match(/\.mp3$/)
    //   //   ? soundManager.createSound({id:'s'+responseJSON.id, url:responseJSON.song_url})
    //   // : soundManager.createVideo({id:'s'+responseJSON.id, url:responseJSON.song_url}));
    //   // song.togglePause();
    //   this.blur();
    // });
    this.elements.preview.text(responseJSON.fileName);
    this.doSongLoaded();
  }
    
  this.doSongLoaded = function() {
    if( this.elements.loading )
      this.elements.loading.remove();
    if( this.elements.formFancyContainer )
      this.elements.formFancyContainer.remove();
    if( this.elements.error ) {
      this.elements.error.remove();
    }
    this.elements.preview.appendTo(this.elements.body);
    this.makeFormInputs();
  }
  this.makeFormInputs = function() {
    this.ready();
    this.__proto__.makeFormInputs.call(this,{
      'song_id' : this.params.song_id
    });
  }
  this.initialize(options);
};


/* $Id: composer_albumvideo.js 10258 2014-06-04 16:07:47Z lucas $ */ 

Composer.Plugin.AlbumVideo = function(options){
  this.__proto__ = new Composer.Plugin.Interface(options);

  this.name = 'albumvideo';
  this.options = {
    title : 'Add Photo / Video',
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 250,
    isMessagePage: false,
    albumEnable: 0,
    videoEnable: 0,
  };

  this.initialize = function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,options);
  }

  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();
    return this;
  }
  this.detach = function() {
    this.__proto__.detach.call(this);
    return this;
  }
  
  this.activate = function() {

    if( this.active && scriptJquery("#compose-video-body").length > 0) return;
    this.__proto__.activate.call(this);
    
    this.makeMenu();
    this.makeBody();
    
    // Generate form
    var fullUrl = this.options.requestOptions.url;
    if(this.options.albumEnable == 1 && this.options.videoEnable == 1) {
      var accepttype = 'video/*,image/*';
    } else if(this.options.albumEnable == 1) {
      var accepttype = 'image/*';
    } else if(this.options.videoEnable == 1) {
      var accepttype = 'video/*';
    }
    if(isMessagePage) {
      scriptJquery(this.elements.body).html('<input type="file" accept="'+accepttype+'"  onchange="readVideoImageUrl(this)" id="video_file_multi" name="video_file_multi" style="display:none"><div class="activity_compose_photo_container clearfix"><div id="activity_compose_video_container_inner" class="clearfix"><div id="show_video"></div><div id="videodragandrophandler" class="activity_compose_photo_uploader center_item" title="Choose a file to upload"><i class="fa fa-plus"></i></div></div></div>');
      var byteMB = (post_max_size / (1024 * 1024)) + en4.core.language.translate("MB");
       byteMB =  en4.core.language.translate('Max size ') + byteMB;
       scriptJquery(this.elements.body).append('<span class="font_color_light">('+byteMB+')</span>');
    } else {
      scriptJquery(this.elements.body).html('<input type="file" accept="'+accepttype+'" onchange="readVideoImageUrl(this)" multiple="multiple" id="video_file_multi" name="video_file_multi" style="display:none"><div class="activity_compose_photo_container"><div id="activity_compose_video_container_inner" class="clearfix"><div id="show_video"></div><div id="videodragandrophandler" class="activity_compose_photo_uploader center_item" title="Choose a file to upload"><i class="fa fa-plus"></i></div></div></div>');
      var byteMB = (post_max_size / (1024 * 1024)) + en4.core.language.translate("MB");
      byteMB =  en4.core.language.translate('Max size ') + byteMB;
      scriptJquery(this.elements.body).append('<span class="font_color_light">('+byteMB+')</span>');
    }

    if(scriptJquery('#toValues-wrapper').length > 0 || scriptJquery('#submit-wrapper').length > 0) {
      //scriptJquery('#video_file_multi').removeAttr('multiple');
    }

    if(scriptJquery('#toValues-wrapper').length > 0){
      scriptJquery('#toValues-wrapper').append('<div><input type="hidden" value="1" id="messageAttachment" name="attachment[messageAttachment]"><input type="hidden" name="attachment[multipleupload]" id="multipleupload"><input type="hidden" value="" id="fancyalbumuploadfileidsvideo" name="attachment[video_id]"><input type="hidden" value="video" id="videosealbum" name="attachment[type]"></div>');  
    } else if(scriptJquery('#submit-wrapper').length > 0){
      scriptJquery('#body-wrapper').append('<div><input type="hidden" value="1" id="messageAttachment" name="attachment[messageAttachment]"><input type="hidden" value="video" id="videosealbum" name="attachment[type]"><input type="hidden" name="multipleupload" id="attachment[multipleupload]"><input type="hidden" value="" id="fancyalbumuploadfileidsvideo" name="attachment[video_id]"></div>');    
    } 
  }

  this.deactivate = function() {
    // clean video out if not attached
    if (this.params.video_id)
      scriptJquery.ajax({
        url: en4.core.basePath + 'video/index/delete',
        data: {
          format: 'json',
          video_id: this.params.video_id
        }
      });
    if( !this.active ) return;
    this.__proto__.deactivate.call(this);
  }

  // Getting into the core stuff now
  this.doAttach = function(e) {
    var val = this.elements.formInput.val();
    if( !val && !scriptJquery('#compose-video-upload-file').val())
    {
      return;
    }
    if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
    {
      //val = 'http://' + val;
    }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' && !scriptJquery('#compose-video-upload-file').val()) {
      e.preventDefault();
      return;
    }
     var video_element = document.getElementById("compose-video-form-type");
    var type = video_element.value;
    var formData = new FormData();
    if(scriptJquery('#compose-video-upload-file').length){
      var filesAttach = scriptJquery('#compose-video-upload-file')[0].files[0];  
    }else{
      var filesAttach = "";  
    }
    formData.append('Filedata', filesAttach);
    formData.append('format', 'json');
    formData.append('uri', val);
    formData.append('type', type);
    formData.append('uploadwall', 1);
    if(type == 3)
      var url = this.options.requestOptions.uploadurl;
    else
      var url = this.options.requestOptions.url;
    // Send request to get attachment
    /*var options = $merge({
      'data' : {
        'format' : 'json',
        'uri' : val,
        'type': type,
        Filedata:formData,
      },
      'onComplete' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);
    */
    var that = this;
    scriptJquery.ajax({
      type:'POST',
      url: url,
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data){
          that.doProcessResponse(data);
      },
      error: function(data){
        //silence
      }
    });
    
    // Inject loading
    this.makeLoading('empty');
    // Send request
   // this.request = new Request.JSON(options);
   // this.request;

  }

  this.doProcessResponse = function(responseJSON, responseText) {
    // Handle error
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.video_id)) != 'number' ) {
      //this.elements.body.empty();
      if( this.elements.loading ) this.elements.loading.remove();
      //this.makeaError(responseJSON.message, 'empty');
      this.makeError(responseJSON.message);
      //compose-video-error
      //ignore test
      this.elements.ignoreValidation = scriptJquery.crtEle('a', {
        'href' : this.params.uri,
      }).html(this.params.title)
      .click(function(e) {
        e.preventDefault();
        self.doAttach(this);
      })
      .appendTo(this.elements.previewTitle);
      return;
      //throw "unable to upload image";
    }
    var title = responseJSON.title || this.params.get('uri').replace('http://', '');
    this.params.set('title', responseJSON.title);
    this.params.set('description', responseJSON.description);
    this.params.set('photo_id', responseJSON.photo_id);
    this.params.set('video_id', responseJSON.video_id);
    
    if (responseJSON.src) {
      this.elements.preview = scriptJquery.crtEle('img', {
        'id' : 'compose-video-preview-image',
        'class' : 'compose-preview-image',
        'src' : responseJSON.src,
      }).load(this.doImageLoaded.bind(this));
    } else {
      this.doImageLoaded();
    }
  },
  this.doImageLoaded = function() {
    var self = this;
    if( this.elements.loading.length) this.elements.loading.remove();
    if( this.elements.preview ) {
      this.elements.preview.attr('width','');
      this.elements.preview.attr('height','');
      this.elements.preview.appendTo(this.elements.body);
    }

    this.elements.previewInfo = scriptJquery.crtEle('div', {
      'id' : 'compose-video-preview-info',
      'class' : 'compose-preview-info'
    }).appendTo(this.elements.body);

    this.elements.previewTitle = scriptJquery.crtEle('div', {
      'id' : 'compose-video-preview-title',
      'class' : 'compose-preview-title'
    }).appendTo(this.elements.previewInfo);

    this.elements.previewTitleLink = scriptJquery.crtEle('a', {
      'href' : this.params.uri,
    })
    .html(this.params.title)
    .click(function(e) {
        e.preventDefault();
        self.handleEditTitle(this);
    })
    .appendTo(this.elements.previewTitle);

    this.elements.previewDescription = scriptJquery.crtEle('div', {
      'id' : 'compose-video-preview-description',
      'class' : 'compose-preview-description',
    })
    .html(this.params.description)
    .click(function(e) {
      e.preventDefault();
      self.handleEditDescription(this);
    })
    .appendTo(this.elements.previewInfo);
    this.makeFormInputs();
  }

  this.makeFormInputs = function() {
    this.ready();
    this.__proto__.makeFormInputs.call(this,{
      'photo_id' : this.params.photo_id,
      'video_id' : this.params.video_id,
      'title' : this.params.title,
      'description' : this.params.description
    });
  }
  this.updateVideoFields = function(element) {
    var video_element = document.getElementById("compose-video-form-type");
    var url_element = document.getElementById("compose-video-form-input");
    var post_element = document.getElementById("compose-video-form-submit");
    var upload_element = document.getElementById("compose-video-upload");
    // clear url if input field on change
    scriptJquery('#compose-video-form-input').val("");
    // If video source is empty
    if (video_element.value == 0)
    {
      upload_element.style.display = "none";
      post_element.style.display = "none";
      url_element.style.display = "none";
    }
    
    // if video source is upload
    if (video_element.value == 3) {
      upload_element.style.display = "block";
      post_element.style.display = "block";
      // if(this.options.advancedactvity == 1) 
      //   post_element.style.display = "block";
      // else
      //   post_element.style.display = "none";
      url_element.style.display = "none";
    }
  }
  this.handleEditTitle = function(element) {
    scriptJquery(element).css('display', 'none');
    var input = scriptJquery.crtEle('input', {
      'type' : 'text',
      'value' : htmlspecialchars_decode(scriptJquery(element).html()),
    })
    .blur(function() {
      if(scriptJquery(input).val().trim() != '' ) {
        this.params.title = scriptJquery(input).val();
        scriptJquery(element).html(this.params.title);
        this.setFormInputValue('title', this.params.title);
      }
      scriptJquery(element).css('display', '');
      input.remove();
    }.bind(this))
    .insertAfter(scriptJquery(element), 'after');
    input.focus();
  }
  this.handleEditDescription = function(element) {
    scriptJquery(element).css('display', 'none');
    var input = scriptJquery.crtEle('textarea', {})
    .html(htmlspecialchars_decode(scriptJquery(element).html()))
    .blur(function() {
      if( scriptJquery(input).val().trim() != '' ) {
        this.params.description = scriptJquery(input).val();
        scriptJquery(element).html(this.params.description);
        this.setFormInputValue('description', this.params.description);
      }
      else {
        this.params.description = '';
        scriptJquery(element).html('');
        this.setFormInputValue('description', '');
      }
      scriptJquery(element).css('display', '');
      input.remove();
    }.bind(this))
    .insertAfter(scriptJquery(element), 'after');
    input.focus();
  }
  this.initialize(options);
}
